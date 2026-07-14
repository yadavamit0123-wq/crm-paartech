<?php

namespace App\Livewire;

use App\Models\Lead;
use App\Models\LeadReminder;
use App\Models\LeadStage;
use App\Models\User;
use App\Services\TenantService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $tenantId = $user->isSuperAdmin() ? null : ($user->tenant_id ?? TenantService::id());

        $leadQuery = Lead::query();
        if ($tenantId) {
            $leadQuery->where('tenant_id', $tenantId);
        }
        if (! $user->hasPermission('leads.view_all') && $user->hasPermission('leads.view_own')) {
            $leadQuery->where('assigned_to', $user->id);
        }

        $stats = [
            'total_leads' => (clone $leadQuery)->count(),
            'new_leads' => (clone $leadQuery)->whereHas('stage', fn ($q) => $q->where('slug', 'new'))->count(),
            'won_leads' => (clone $leadQuery)->whereHas('stage', fn ($q) => $q->where('is_won', true))->count(),
            'follow_ups_today' => (clone $leadQuery)->whereDate('next_follow_up_at', today())->count(),
            'overdue' => LeadReminder::where('user_id', $user->id)
                ->where('is_completed', false)
                ->where('remind_at', '<', now())
                ->count(),
        ];

        $recentLeads = (clone $leadQuery)->with(['stage', 'assignee'])->latest()->limit(8)->get();
        $stageStats = LeadStage::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->withCount('leads')
            ->orderBy('sort_order')
            ->get();

        return view('livewire.dashboard', compact('stats', 'recentLeads', 'stageStats'))
            ->layout('layouts.app');
    }
}
