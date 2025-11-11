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

            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();

            foreach ($users as $user) {
                $user->tagState()->attach($tag->id, [
                    'subscription' => 'follow'
                ]);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->info("\n  ✓ Subscribed {$count} users");
            $totalSubscribed += $count;
        }

        $this->info("\n✓ Successfully completed! Total subscriptions created: {$totalSubscribed}");

        return 0;
    }
}
