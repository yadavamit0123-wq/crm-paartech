<div>
    @include('layouts.partials.leads-nav')

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Leads Dashboard</h1>
            <p class="text-gray-500 text-sm">Business reports, trends & pipeline analytics</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="$refresh" class="px-3 py-2 border rounded-lg text-sm">🔄 Refresh</button>
            @php $dailyReportsOn = ! empty(auth()->user()->tenant->settings['daily_email_reports']); @endphp
            <button wire:click="toggleDailyReports" class="px-3 py-2 border rounded-lg text-sm {{ $dailyReportsOn ? 'border-green-300 bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800' : 'hover:bg-gray-50' }}">
                📧 Daily Email Reports: {{ $dailyReportsOn ? 'Enabled' : 'Disabled' }}
            </button>
            @if(auth()->user()->hasPermission('leads.create'))
            <a href="{{ route('leads.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add Lead</a>
            @endif
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <select wire:model.live="dateRange" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="last_7_days">Last 7 Days</option>
            <option value="last_30_days">Last 30 Days</option>
            <option value="this_month">This Month</option>
        </select>
        <select wire:model.live="teamMember" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="">All Team Members</option>
            @foreach($employees as $emp)
            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label' => 'Leads', 'value' => $analytics['leads'], 'icon' => '👥', 'color' => 'text-indigo-600'],
            ['label' => 'Calls', 'value' => $analytics['calls'], 'icon' => '📞', 'color' => 'text-purple-600'],
            ['label' => 'Tasks', 'value' => $analytics['tasks'], 'icon' => '✅', 'color' => 'text-amber-600'],
            ['label' => 'Sales (₹)', 'value' => '₹'.number_format($analytics['sales'], 0), 'icon' => '💰', 'color' => 'text-green-600'],
        ] as $card)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700">
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1"><span>{{ $card['icon'] }}</span>{{ $card['label'] }}</div>
            <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Status Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        @foreach([
            'created' => ['CREATED', 'bg-blue-50 text-blue-700'],
            'assigned' => ['ASSIGNED', 'bg-indigo-50 text-indigo-700'],
            'untouched' => ['UNTOUCHED', 'bg-amber-50 text-amber-700'],
            'no_task' => ['NO TASK', 'bg-orange-50 text-orange-700'],
            'stale' => ['STALE', 'bg-red-50 text-red-700'],
        ] as $key => [$label, $class])
        <div class="rounded-xl p-4 {{ $class }} border border-current/10">
            <div class="text-xs font-semibold opacity-70">{{ $label }}</div>
            <div class="text-2xl font-bold mt-1">{{ $statusCards[$key] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Business Reports / Trends --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6" x-data="{ trendTab: 'leads' }">
        <h3 class="font-semibold mb-4">Business Reports — Trends & Analytics</h3>
        <div class="flex gap-1 mb-4 border-b dark:border-gray-700 overflow-x-auto">
            @foreach(['leads' => 'Leads Trend', 'calls' => 'Calls Trend', 'tasks' => 'Activity Trend', 'sales' => 'Sales Trend'] as $k => $label)
            <button @click="trendTab='{{ $k }}'" :class="trendTab==='{{ $k }}' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm border-b-2 whitespace-nowrap">{{ $label }}</button>
            @endforeach
        </div>
        <div class="h-40 flex items-end gap-1">
            @php $maxVal = max(1, max($trendData['leads']), max($trendData['calls']), max($trendData['tasks']), max(array_map('intval', $trendData['sales']))); @endphp
            @foreach($trendData['labels'] as $i => $label)
            <div class="flex-1 flex flex-col items-center gap-1 group" title="Leads: {{ $trendData['leads'][$i] }}, Calls: {{ $trendData['calls'][$i] }}">
                <div class="w-full flex flex-col justify-end h-32 gap-0.5">
                    <div class="w-full bg-indigo-400 rounded-t" style="height: {{ max(2, ($trendData['leads'][$i] / $maxVal) * 100) }}%"></div>
                </div>
                <span class="text-[10px] text-gray-400">{{ $label }}</span>
            </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 mt-2">Bar height = leads per day. Switch tabs above for calls, tasks & sales trends.</p>
    </div>

    {{-- Quick Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        @foreach([
            ['label' => 'Total Leads', 'value' => $stats['total_leads']],
            ['label' => 'Overdue Tasks', 'value' => $stats['overdue_tasks']],
            ['label' => 'Unread Inbox', 'value' => $stats['unread_inbox']],
            ['label' => 'Calls Today', 'value' => $stats['calls_today']],
            ['label' => 'Automations', 'value' => $stats['active_automations']],
        ] as $stat)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 shadow-sm border dark:border-gray-700 text-center">
            <div class="text-xs text-gray-500">{{ $stat['label'] }}</div>
            <div class="text-xl font-bold text-indigo-600">{{ $stat['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Module Grid --}}
    <h3 class="font-semibold mb-3">All Modules</h3>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 mb-6">
        @foreach($modules as $mod)
        <a href="{{ route($mod['route']) }}" class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 hover:border-indigo-500 hover:shadow-md transition group">
            <div class="text-2xl mb-2">{{ $mod['icon'] }}</div>
            <div class="font-semibold text-sm group-hover:text-indigo-600">{{ $mod['label'] }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $mod['desc'] }}</div>
        </a>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">Pipeline</h3>
            @foreach($stageStats as $stage)
            <div class="flex items-center gap-3 mb-2">
                <span class="w-3 h-3 rounded-full" style="background: {{ $stage->color }}"></span>
                <span class="flex-1 text-sm">{{ $stage->name }}</span>
                <span class="font-medium text-sm">{{ $stage->leads_count }}</span>
            </div>
            @endforeach
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold">Recent Leads</h3>
                <a href="{{ route('leads.list') }}" class="text-indigo-600 text-sm">View all →</a>
            </div>
            @forelse($recentLeads as $lead)
            <a href="{{ route('leads.show', $lead) }}" class="flex justify-between py-2 border-b dark:border-gray-700 last:border-0 text-sm hover:text-indigo-600">
                <span>{{ $lead->name }} <span class="text-gray-400">• {{ $lead->stage?->name }}</span></span>
                <span class="text-gray-400">{{ $lead->created_at->diffForHumans() }}</span>
            </a>
            @empty
            <p class="text-gray-500 text-sm">No leads yet</p>
            @endforelse
        </div>
    </div>
</div>
