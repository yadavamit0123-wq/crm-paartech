<?php

namespace App\Livewire\Reports;

use App\Models\CallLog;
use App\Models\CrmTask;
use App\Models\Lead;
use App\Models\Order;
use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public string $period = 'month';

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        $start = match ($this->period) {
            'week' => now()->startOfWeek(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $stats = [
            'leads' => Lead::where('created_at', '>=', $start)->count(),
            'calls' => CallLog::where('called_at', '>=', $start)->count(),
            'orders' => Order::where('created_at', '>=', $start)->count(),
            'revenue' => Order::where('created_at', '>=', $start)->where('status', 'fulfilled')->sum('total_amount'),
            'tasks_done' => CrmTask::where('status', 'completed')->where('completed_at', '>=', $start)->count(),
        ];

        $teamPerformance = User::where('tenant_id', $tenantId)->where('is_active', true)
            ->get()
            ->map(fn ($u) => [
                'user' => $u,
                'leads' => Lead::where('assigned_to', $u->id)->where('created_at', '>=', $start)->count(),
                'calls' => CallLog::where('user_id', $u->id)->where('called_at', '>=', $start)->count(),
                'orders' => Order::where('created_by', $u->id)->where('created_at', '>=', $start)->count(),
            ]);

        $callBreakdown = [
            'incoming' => CallLog::where('called_at', '>=', $start)->where('direction', 'incoming')->count(),
            'outgoing' => CallLog::where('called_at', '>=', $start)->where('direction', 'outgoing')->count(),
            'missed' => CallLog::where('called_at', '>=', $start)->where('direction', 'missed')->count(),
            'rejected' => CallLog::where('called_at', '>=', $start)->where('direction', 'rejected')->count(),
        ];

        return view('livewire.reports.index', compact('stats', 'teamPerformance', 'callBreakdown'))
            ->layout('layouts.app');
    }
}
