<?php

namespace App\Livewire\Leads;

use App\Models\Automation;
use App\Models\CallLog;
use App\Models\CrmTask;
use App\Models\Lead;
use App\Models\LeadReminder;
use App\Models\LeadStage;
use App\Models\Order;
use App\Models\User;
use App\Models\WhatsappConversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Dashboard extends Component
{
    public string $dateRange = 'last_7_days';
    public string $teamMember = '';

    public function toggleDailyReports(): void
    {
        $tenant = auth()->user()->tenant;
        if (! $tenant) {
            return;
        }

        $settings = $tenant->settings ?? [];
        $settings['daily_email_reports'] = empty($settings['daily_email_reports']);
        $tenant->update(['settings' => $settings]);

        $this->dispatch('notify', message: $settings['daily_email_reports']
            ? 'Daily email reports enabled'
            : 'Daily email reports disabled');
    }

    public function getDateBounds(): array
    {
        return match ($this->dateRange) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'last_7_days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'last_30_days' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            default => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
        };
    }

    public function render()
    {
        $user = auth()->user();
        $user->loadMissing('role.permissions', 'tenant');
        [$start, $end] = $this->getDateBounds();

        $leadQuery = Lead::query();
        if (! $user->hasPermission('leads.view_all') && $user->hasPermission('leads.view_own')) {
            $leadQuery->where('assigned_to', $user->id);
        }
        if ($this->teamMember) {
            $leadQuery->where('assigned_to', $this->teamMember);
        }

        $taskQuery = $this->tableQuery('crm_tasks', fn () => CrmTask::query()->whereBetween('created_at', [$start, $end]));
        $callQuery = $this->tableQuery('call_logs', fn () => CallLog::query()->whereBetween('called_at', [$start, $end]));
        $orderQuery = $this->tableQuery('orders', fn () => Order::query()->whereBetween('created_at', [$start, $end]));

        if ($this->teamMember) {
            $taskQuery?->where('user_id', $this->teamMember);
            $callQuery?->where('user_id', $this->teamMember);
            $orderQuery?->where('created_by', $this->teamMember);
        }

        $analytics = [
            'leads' => (clone $leadQuery)->whereBetween('created_at', [$start, $end])->count(),
            'calls' => $callQuery ? (clone $callQuery)->count() : 0,
            'tasks' => $taskQuery ? (clone $taskQuery)->count() : 0,
            'sales' => $orderQuery
                ? (clone $orderQuery)->whereIn('status', ['confirmed', 'fulfilled', 'processing'])->sum('total_amount')
                : 0,
        ];

        $statusCards = [
            'created' => (clone $leadQuery)->whereBetween('created_at', [$start, $end])->count(),
            'assigned' => (clone $leadQuery)->whereNotNull('assigned_to')->whereBetween('created_at', [$start, $end])->count(),
            'untouched' => (clone $leadQuery)->whereNull('last_contacted_at')->count(),
            'no_task' => Schema::hasTable('lead_tasks')
                ? (clone $leadQuery)->whereDoesntHave('openTasks')->count()
                : 0,
            'stale' => (clone $leadQuery)->where('created_at', '<', now()->subDays(30))
                ->where(function ($q) {
                    $q->whereNull('last_contacted_at')
                        ->orWhere('last_contacted_at', '<', now()->subDays(30));
                })->count(),
        ];

        $trendData = $this->buildTrendData($leadQuery, $callQuery, $taskQuery, $orderQuery, $start, $end);

        $newStageIds = Schema::hasTable('lead_stages')
            ? LeadStage::where('slug', 'new')->pluck('id')
            : collect();
        $wonStageIds = Schema::hasTable('lead_stages')
            ? LeadStage::where('is_won', true)->pluck('id')
            : collect();

        $stats = [
            'total_leads' => (clone $leadQuery)->count(),
            'new_leads' => $newStageIds->isEmpty() ? 0 : (clone $leadQuery)->whereIn('lead_stage_id', $newStageIds)->count(),
            'won_leads' => $wonStageIds->isEmpty() ? 0 : (clone $leadQuery)->whereIn('lead_stage_id', $wonStageIds)->count(),
            'follow_ups_today' => (clone $leadQuery)->whereDate('next_follow_up_at', today())->count(),
            'overdue_tasks' => Schema::hasTable('crm_tasks')
                ? CrmTask::where('status', 'pending')->where('due_at', '<', now())->count()
                : 0,
            'unread_inbox' => Schema::hasTable('whatsapp_conversations')
                ? WhatsappConversation::sum('unread_count')
                : 0,
            'calls_today' => Schema::hasTable('call_logs')
                ? CallLog::whereDate('called_at', today())->count()
                : 0,
            'orders_month' => Schema::hasTable('orders')
                ? Order::where('created_at', '>=', now()->startOfMonth())->count()
                : 0,
            'active_automations' => Schema::hasTable('automations')
                ? Automation::where('is_active', true)->count()
                : 0,
            'pending_reminders' => Schema::hasTable('lead_reminders')
                ? LeadReminder::where('user_id', $user->id)->where('is_completed', false)->where('remind_at', '<=', now()->addDay())->count()
                : 0,
        ];

        $recentLeads = (clone $leadQuery)->with(['stage', 'label', 'assignee'])->latest()->limit(5)->get();
        $stageStats = Schema::hasTable('lead_stages')
            ? LeadStage::withCount('leads')->orderBy('sort_order')->get()
            : collect();
        $modules = $this->getModuleCards($user);
        $employees = User::where('tenant_id', $user->tenant_id)->where('is_active', true)->get(['id', 'name']);
        $activeTrendTab = 'leads';

        return view('livewire.leads.dashboard', compact(
            'stats', 'analytics', 'statusCards', 'trendData', 'recentLeads', 'stageStats', 'modules', 'employees', 'activeTrendTab'
        ))->layout('layouts.app');
    }

    protected function tableQuery(string $table, callable $make): ?Builder
    {
        return Schema::hasTable($table) ? $make() : null;
    }

    protected function buildTrendData($leadQuery, $callQuery, $taskQuery, $orderQuery, $start, $end): array
    {
        $days = max(1, min((int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1, 30));

        $leadsByDay = $this->aggregateByDay((clone $leadQuery), 'created_at', $start, $end);
        $callsByDay = $callQuery ? $this->aggregateByDay((clone $callQuery), 'called_at', $start, $end) : [];
        $tasksByDay = $taskQuery ? $this->aggregateByDay((clone $taskQuery), 'created_at', $start, $end) : [];
        $salesByDay = $orderQuery ? $this->aggregateByDay((clone $orderQuery), 'created_at', $start, $end, 'SUM(total_amount)') : [];

        $labels = [];
        $leads = [];
        $calls = [];
        $tasks = [];
        $sales = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d M');
            $leads[] = (int) ($leadsByDay[$key] ?? 0);
            $calls[] = (int) ($callsByDay[$key] ?? 0);
            $tasks[] = (int) ($tasksByDay[$key] ?? 0);
            $sales[] = (float) ($salesByDay[$key] ?? 0);
        }

        return compact('labels', 'leads', 'calls', 'tasks', 'sales');
    }

    protected function aggregateByDay(Builder $query, string $column, $start, $end, string $aggregate = 'COUNT(*)'): array
    {
        $dateExpr = $query->getConnection()->getDriverName() === 'sqlite'
            ? "date({$column})"
            : "DATE({$column})";

        return $query
            ->whereBetween($column, [$start, $end])
            ->selectRaw("{$dateExpr} as day_key, {$aggregate} as day_value")
            ->groupByRaw($dateExpr)
            ->pluck('day_value', 'day_key')
            ->all();
    }

    protected function getModuleCards($user): array
    {
        $cards = [
            ['route' => 'leads.list', 'icon' => '👥', 'label' => 'Lead List', 'desc' => 'Filters, bulk, kanban', 'perm' => ['leads.view_own', 'leads.view_all']],
            ['route' => 'leads.forms', 'icon' => '📋', 'label' => 'User Forms', 'desc' => 'Lead capture forms', 'perm' => ['leads.view_own', 'leads.view_all']],
            ['route' => 'leads.labels', 'icon' => '🏷', 'label' => 'Labels', 'desc' => 'Hot, Warm, VIP tags', 'perm' => 'leads.edit'],
            ['route' => 'leads.inbox', 'icon' => '💬', 'label' => 'WhatsApp Inbox', 'desc' => 'Team shared inbox', 'perm' => 'inbox.view'],
            ['route' => 'leads.tasks', 'icon' => '✅', 'label' => 'Tasks', 'desc' => 'Today, overdue, done', 'perm' => 'tasks.view'],
            ['route' => 'leads.auto-dialer', 'icon' => '📞', 'label' => 'Auto Dialer', 'desc' => 'Click-to-call leads', 'perm' => ['leads.view_own', 'leads.view_all']],
            ['route' => 'leads.products', 'icon' => '📦', 'label' => 'Products', 'desc' => 'Catalog & pricing', 'perm' => 'products.manage'],
            ['route' => 'leads.orders', 'icon' => '🛒', 'label' => 'Orders', 'desc' => 'Sales orders', 'perm' => 'orders.view'],
            ['route' => 'leads.documents', 'icon' => '📄', 'label' => 'Quotes & Invoices', 'desc' => 'GST documents', 'perm' => 'documents.view'],
            ['route' => 'leads.customers', 'icon' => '🤝', 'label' => 'Customers', 'desc' => 'Converted leads', 'perm' => 'customers.view'],
            ['route' => 'leads.templates', 'icon' => '📝', 'label' => 'Templates', 'desc' => 'WhatsApp & email', 'perm' => 'templates.manage'],
            ['route' => 'leads.broadcasts', 'icon' => '📢', 'label' => 'Broadcasts', 'desc' => 'Bulk campaigns', 'perm' => 'broadcasts.manage'],
            ['route' => 'leads.automations', 'icon' => '⚡', 'label' => 'Automation', 'desc' => 'Triggers & drips', 'perm' => 'automations.manage'],
            ['route' => 'leads.bots', 'icon' => '🤖', 'label' => 'WhatsApp Bots', 'desc' => 'Auto-reply flows', 'perm' => 'bots.manage'],
            ['route' => 'leads.reports', 'icon' => '📈', 'label' => 'Analytics', 'desc' => 'Team performance', 'perm' => 'reports.view'],
            ['route' => 'leads.call-logs', 'icon' => '📋', 'label' => 'Call Logs', 'desc' => 'Incoming/outgoing', 'perm' => 'reports.view'],
            ['route' => 'leads.sales-targets', 'icon' => '🎯', 'label' => 'Sales Targets', 'desc' => 'Goals & progress', 'perm' => 'targets.manage'],
            ['route' => 'leads.lead-sources', 'icon' => '🔗', 'label' => 'Lead Sources', 'desc' => '15+ auto sync', 'perm' => 'integrations.manage'],
            ['route' => 'leads.visiting-cards', 'icon' => '💳', 'label' => 'Visiting Cards', 'desc' => 'Digital business card', 'perm' => 'visiting_cards.manage'],
            ['route' => 'leads.custom-fields', 'icon' => '📋', 'label' => 'Custom Fields', 'desc' => 'Form builder', 'perm' => 'settings.manage'],
            ['route' => 'leads.ai-assistant', 'icon' => '✨', 'label' => 'AI Assistant', 'desc' => 'Draft messages fast', 'perm' => ['leads.view_own', 'leads.view_all']],
            ['route' => 'leads.stages', 'icon' => '📊', 'label' => 'Pipeline Stages', 'desc' => 'Customize stages', 'perm' => 'stages.manage'],
            ['route' => 'leads.team', 'icon' => '👤', 'label' => 'Team', 'desc' => 'Employees & roles', 'perm' => 'employees.manage'],
            ['route' => 'leads.settings', 'icon' => '⚙️', 'label' => 'CRM Settings', 'desc' => 'Org & WhatsApp', 'perm' => 'settings.manage'],
            ['route' => 'leads.reviews', 'icon' => '⭐', 'label' => 'Google Reviews', 'desc' => 'Review requests', 'perm' => 'reviews.manage'],
            ['route' => 'leads.bulk-upload', 'icon' => '📤', 'label' => 'Import Leads', 'desc' => 'CSV bulk upload', 'perm' => 'leads.bulk_upload'],
        ];

        return array_values(array_filter($cards, function ($card) use ($user) {
            $perms = (array) $card['perm'];
            foreach ($perms as $perm) {
                if ($user->hasPermission($perm)) {
                    return true;
                }
            }

            return false;
        }));
    }
}
