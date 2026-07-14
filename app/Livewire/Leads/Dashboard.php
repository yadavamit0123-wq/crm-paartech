<?php

namespace App\Livewire\Leads;

use App\Models\Automation;
use App\Models\Broadcast;
use App\Models\CallLog;
use App\Models\CrmTask;
use App\Models\Lead;
use App\Models\LeadReminder;
use App\Models\LeadStage;
use App\Models\Order;
use App\Models\User;
use App\Models\WhatsappConversation;
use Livewire\Component;

class Dashboard extends Component
{
    public string $dateRange = 'last_7_days';
    public string $teamMember = '';

    public function toggleDailyReports(): void
    {
        $tenant = auth()->user()->tenant;
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
        [$start, $end] = $this->getDateBounds();

        $leadQuery = Lead::query();
        if (! $user->hasPermission('leads.view_all') && $user->hasPermission('leads.view_own')) {
            $leadQuery->where('assigned_to', $user->id);
        }
        if ($this->teamMember) {
            $leadQuery->where('assigned_to', $this->teamMember);
        }

        $taskQuery = CrmTask::query()->whereBetween('created_at', [$start, $end]);
        $callQuery = CallLog::query()->whereBetween('called_at', [$start, $end]);
        $orderQuery = Order::query()->whereBetween('created_at', [$start, $end]);

        if ($this->teamMember) {
            $taskQuery->where('user_id', $this->teamMember);
            $callQuery->where('user_id', $this->teamMember);
            $orderQuery->where('created_by', $this->teamMember);
        }

        $analytics = [
            'leads' => (clone $leadQuery)->whereBetween('created_at', [$start, $end])->count(),
            'calls' => (clone $callQuery)->count(),
            'tasks' => (clone $taskQuery)->count(),
            'sales' => (clone $orderQuery)->whereIn('status', ['confirmed', 'fulfilled', 'processing'])->sum('total_amount'),
        ];

        $statusCards = [
            'created' => (clone $leadQuery)->whereBetween('created_at', [$start, $end])->count(),
            'assigned' => (clone $leadQuery)->whereNotNull('assigned_to')->whereBetween('created_at', [$start, $end])->count(),
            'untouched' => (clone $leadQuery)->whereNull('last_contacted_at')->count(),
            'no_task' => (clone $leadQuery)->whereDoesntHave('tasks', fn ($q) => $q->where('status', 'pending'))->count(),
            'stale' => (clone $leadQuery)->where('created_at', '<', now()->subDays(30))
                ->where(function ($q) {
                    $q->whereNull('last_contacted_at')
                        ->orWhere('last_contacted_at', '<', now()->subDays(30));
                })->count(),
        ];

        $trendData = $this->buildTrendData($leadQuery, $callQuery, $taskQuery, $orderQuery, $start, $end);

        $stats = [
            'total_leads' => (clone $leadQuery)->count(),
            'new_leads' => (clone $leadQuery)->whereHas('stage', fn ($q) => $q->where('slug', 'new'))->count(),
            'won_leads' => (clone $leadQuery)->whereHas('stage', fn ($q) => $q->where('is_won', true))->count(),
            'follow_ups_today' => (clone $leadQuery)->whereDate('next_follow_up_at', today())->count(),
            'overdue_tasks' => CrmTask::where('status', 'pending')->where('due_at', '<', now())->count(),
            'unread_inbox' => WhatsappConversation::sum('unread_count'),
            'calls_today' => CallLog::whereDate('called_at', today())->count(),
            'orders_month' => Order::where('created_at', '>=', now()->startOfMonth())->count(),
            'active_automations' => Automation::where('is_active', true)->count(),
            'pending_reminders' => LeadReminder::where('user_id', $user->id)->where('is_completed', false)->where('remind_at', '<=', now()->addDay())->count(),
        ];

        $recentLeads = (clone $leadQuery)->with(['stage', 'label', 'assignee'])->latest()->limit(5)->get();
        $stageStats = LeadStage::withCount('leads')->orderBy('sort_order')->get();
        $modules = $this->getModuleCards($user);
        $employees = User::where('tenant_id', $user->tenant_id)->where('is_active', true)->get();
        $activeTrendTab = 'leads';

        return view('livewire.leads.dashboard', compact(
            'stats', 'analytics', 'statusCards', 'trendData', 'recentLeads', 'stageStats', 'modules', 'employees', 'activeTrendTab'
        ))->layout('layouts.app');
    }

    protected function buildTrendData($leadQuery, $callQuery, $taskQuery, $orderQuery, $start, $end): array
    {
        $days = min($start->diffInDays($end) + 1, 30);
        $labels = [];
        $leads = [];
        $calls = [];
        $tasks = [];
        $sales = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            $leads[] = (clone $leadQuery)->whereDate('created_at', $date)->count();
            $calls[] = (clone $callQuery)->whereDate('called_at', $date)->count();
            $tasks[] = (clone $taskQuery)->whereDate('created_at', $date)->count();
            $sales[] = (clone $orderQuery)->whereDate('created_at', $date)->sum('total_amount');
        }

        return compact('labels', 'leads', 'calls', 'tasks', 'sales');
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
