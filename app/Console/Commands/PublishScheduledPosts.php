<?php

namespace App\Console\Commands;

use App\Services\MarketingService;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature = 'crm:publish-scheduled-posts';

    protected $description = 'Publish social media posts that are due';

    public function handle(MarketingService $marketing): int
    {
        $count = $marketing->publishDuePosts();
        $this->info("Published {$count} scheduled posts.");

        return self::SUCCESS;
    }
}
