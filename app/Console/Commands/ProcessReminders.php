<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ProcessReminders extends Command
{
    protected $signature = 'crm:process-reminders';

    protected $description = 'Mark due reminders for notification polling';

    public function handle(): int
    {
        $count = DB::table('lead_reminders')
            ->where('is_completed', false)
            ->where('remind_at', '<=', now())
            ->whereNull('notified_at')
            ->update(['notified_at' => now()]);

        $this->info("Processed {$count} reminders.");

        return self::SUCCESS;
    }
}
