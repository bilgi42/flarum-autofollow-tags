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
            $tagIds = json_decode($settings->get('bilgi42-autofollow-tags.tag_ids', '[]'), true);

            if (!empty($tagIds) && is_array($tagIds)) {
                $subscriptions = [];
                foreach ($tagIds as $tagId) {
                    $subscriptions[$tagId] = ['subscription' => 'follow'];
                }

                if (!empty($subscriptions)) {
                    $user->tagState()->syncWithoutDetaching($subscriptions);
                }
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
