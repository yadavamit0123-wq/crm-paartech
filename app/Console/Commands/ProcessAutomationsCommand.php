<?php

namespace App\Console\Commands;

use App\Services\AutomationService;
use Illuminate\Console\Command;

class ProcessAutomationsCommand extends Command
{
    protected $signature = 'crm:process-automations';

    protected $description = 'Run scheduled automations (no-call, overdue tasks)';

    public function handle(AutomationService $service): int
    {
        $count = $service->processScheduledTriggers();
        $this->info("Processed {$count} automation runs.");

        return self::SUCCESS;
    }
}
