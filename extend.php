<?php

namespace Bilgi42\AutoFollowTags;

use Flarum\Extend;
use Flarum\User\Event\Registered;
use Flarum\Tags\Tag;
use Flarum\Settings\SettingsRepositoryInterface;

return [
    (new Extend\Event)
        ->listen(Registered::class, function (Registered $event) {
            $user = $event->user;
            $settings = resolve(SettingsRepositoryInterface::class);

            // Get the selected tag IDs from settings
            $settingValue = $settings->get('bilgi42-autofollow-tags.tag_ids', '[]');
            $tagIds = json_decode($settingValue, true);

            // Debug logging
            \Illuminate\Support\Facades\Log::info('AutoFollowTags: User registered', [
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

                \Illuminate\Support\Facades\Log::info('AutoFollowTags: Found tags', [
                    'requested_ids' => $tagIds,
                    'found_tags' => $tags->pluck('name', 'id')->toArray(),
                ]);

                if ($tags->isNotEmpty()) {
                    foreach ($tags as $tag) {
                        try {
                            $user->tagState()->attach($tag->id, [
                                'subscription' => 'follow'
                            ]);
                            \Illuminate\Support\Facades\Log::info("AutoFollowTags: Subscribed user {$user->id} to tag {$tag->name} ({$tag->id})");
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("AutoFollowTags: Failed to subscribe user {$user->id} to tag {$tag->id}", [
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning('AutoFollowTags: No valid tags found for IDs', [
                        'tag_ids' => $tagIds
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::info('AutoFollowTags: No tags configured or invalid data', [
                    'setting_value' => $settingValue,
                    'parsed_value' => $tagIds,
                ]);
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
