<?php

namespace Bilgi42\AutoFollowTags\Console;

use Flarum\Console\AbstractCommand;
use Flarum\Tags\Tag;
use Flarum\User\User;
use Illuminate\Database\ConnectionInterface;
use Flarum\Settings\SettingsRepositoryInterface;

class SubscribeExistingUsersCommand extends AbstractCommand
{
    protected $db;
    protected $settings;

    public function __construct(ConnectionInterface $db, SettingsRepositoryInterface $settings)
    {
        parent::__construct();
        $this->db = $db;
        $this->settings = $settings;
    }

    protected function configure()
    {
        $this
            ->setName('tags:subscribe-existing')
            ->setDescription('Subscribe all existing users to the configured auto-subscribe tags');
    }

    protected function fire(): int
    {
        $tagIds = json_decode($this->settings->get('bilgi42-autofollow-tags.tag_ids', '[]'), true);

        if (empty($tagIds)) {
            $this->error('No tags configured for auto-subscription!');
            $this->info('Please configure tags in the admin panel first.');
            return 1;
        }

        $tags = Tag::whereIn('id', $tagIds)->get();

        if ($tags->isEmpty()) {
            $this->error('No valid tags found!');
            return 1;
        }

        $this->info('Tags to subscribe users to:');
        foreach ($tags as $tag) {
            $this->info("  - {$tag->name} (ID: {$tag->id})");
        }

        $totalSubscribed = 0;

        foreach ($tags as $tag) {
            $this->info("\nProcessing tag: {$tag->name}");

            // Get all users who aren't already subscribed to this tag
            $users = User::whereNotExists(function ($query) use ($tag) {
                $query->select($this->db->raw(1))
                      ->from('tag_user')
                      ->whereRaw('tag_user.user_id = users.id')
                      ->where('tag_user.tag_id', $tag->id);
            })->get();

            $count = $users->count();

            if ($count === 0) {
                $this->info("  All users already subscribed!");
                continue;
            }

            $this->info("  Found {$count} users to subscribe...");

            // Subscribe users in batch
            $subscribed = 0;
            foreach ($users as $user) {
                // Use direct database insertion instead of tagState()
                $this->db->table('tag_user')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'tag_id' => $tag->id,
                    ],
                    [
                        'subscription' => 'follow',
                    ]
                );
                $subscribed++;

                // Show progress every 10 users
                if ($subscribed % 10 === 0 || $subscribed === $count) {
                    $this->info("    Progress: {$subscribed}/{$count} users subscribed");
                }
            }

            $this->info("  ✓ Subscribed {$count} users");
            $totalSubscribed += $count;
        }

        $this->info("\n✓ Successfully completed! Total subscriptions created: {$totalSubscribed}");

        return 0;
    }
}
