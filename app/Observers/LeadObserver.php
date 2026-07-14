<?php

namespace App\Observers;

use App\Models\Lead;
use App\Services\AutomationService;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LeadObserver
{
    public function created(Lead $lead): void
    {
        $this->runAutomation('lead_created', $lead);
    }

    public function updated(Lead $lead): void
    {
        if ($lead->wasChanged('lead_stage_id')) {
            $this->runAutomation('stage_changed', $lead);
        }
    }

    protected function runAutomation(string $trigger, Lead $lead): void
    {
        try {
            if (! Schema::hasTable('automations')) {
                return;
            }

            app(AutomationService::class)->runTrigger($trigger, $lead);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
