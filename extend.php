<?php

namespace Bilgi42\AutoFollowTags;

use Flarum\Extend;
use Flarum\User\Event\Registered;
use Flarum\Tags\Tag;
use Flarum\Settings\SettingsRepositoryInterface;

return [
    (new Extend\Event)
        ->listen(Registered::class, function (Registered $event) {
            try {
                $user = $event->user;
                $settings = resolve(SettingsRepositoryInterface::class);
                $logger = resolve('log');

                // Get the selected tag IDs from settings
                $settingValue = $settings->get('bilgi42-autofollow-tags.tag_ids', '[]');
                $tagIds = json_decode($settingValue, true);

                // Debug logging
                $logger->info('[AutoFollowTags] User registered', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'setting_value' => $settingValue,
                    'tag_ids' => $tagIds,
                ]);

                if (!empty($tagIds) && is_array($tagIds)) {
                    // Convert tag IDs to integers to ensure proper matching
                    $tagIds = array_map('intval', $tagIds);

                    // Get the actual tags to verify they exist
                    $tags = Tag::whereIn('id', $tagIds)->get();

                    $logger->info('[AutoFollowTags] Found tags', [
                        'requested_ids' => $tagIds,
                        'found_count' => $tags->count(),
                    ]);

                    if ($tags->isNotEmpty()) {
                        // Use direct database insertion as tagState() might not be available yet
                        $db = resolve(\Illuminate\Database\ConnectionInterface::class);

                        foreach ($tags as $tag) {
                            try {
                                // Check if subscription column exists (added by flarum/subscriptions)
                                $columns = \Illuminate\Support\Facades\Schema::getColumnListing('tag_user');
                                $hasSubscriptionColumn = in_array('subscription', $columns);

                                $data = [
                                    'user_id' => $user->id,
                                    'tag_id' => $tag->id,
                                ];

                                if ($hasSubscriptionColumn) {
                                    $data['subscription'] = 'follow';
                                }

                                // Insert or update the tag subscription
                                $db->table('tag_user')->updateOrInsert(
                                    [
                                        'user_id' => $user->id,
                                        'tag_id' => $tag->id,
                                    ],
                                    $data
                                );
                                $logger->info("[AutoFollowTags] Subscribed user {$user->id} to tag {$tag->name} ({$tag->id})");
                            } catch (\Exception $e) {
                                $logger->error("[AutoFollowTags] Failed to subscribe user {$user->id} to tag {$tag->id}: {$e->getMessage()}");
                            }
                        }
                    } else {
                        $logger->warning('[AutoFollowTags] No valid tags found for IDs: ' . implode(', ', $tagIds));
                    }
                } else {
                    $logger->info('[AutoFollowTags] No tags configured or invalid data', [
                        'setting_value' => $settingValue,
                    ]);
                }
            } catch (\Exception $e) {
                $logger = resolve('log');
                $logger->error('[AutoFollowTags] Exception in event listener: ' . $e->getMessage());
                $logger->error('[AutoFollowTags] Stack trace: ' . $e->getTraceAsString());
            }
        }),

    (new Extend\Console)
        ->command(Console\SubscribeExistingUsersCommand::class),

    (new Extend\Settings)
        ->serializeToForum('autoFollowTags.tagIds', 'bilgi42-autofollow-tags.tag_ids', function ($value) {
            return json_decode($value, true) ?? [];
        }),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/dist/admin.js'),
];
