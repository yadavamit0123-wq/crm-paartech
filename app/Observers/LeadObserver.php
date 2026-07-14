<?php

namespace App\Observers;

use App\Models\Lead;
use App\Services\AutomationService;

class LeadObserver
{
    public function created(Lead $lead): void
    {
        app(AutomationService::class)->runTrigger('lead_created', $lead);
    }

    public function updated(Lead $lead): void
    {
        if ($lead->wasChanged('lead_stage_id')) {
            app(AutomationService::class)->runTrigger('stage_changed', $lead);
        }
    }
}
