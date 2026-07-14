<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Dashboard / डैशबोर्ड</h1>
        <p class="text-gray-500">Welcome, {{ auth()->user()->name }}</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        @foreach([
            ['label' => 'Total Leads', 'value' => $stats['total_leads'], 'icon' => '👥', 'color' => 'indigo'],
            ['label' => 'New Leads', 'value' => $stats['new_leads'], 'icon' => '🆕', 'color' => 'blue'],
            ['label' => 'Won', 'value' => $stats['won_leads'], 'icon' => '✅', 'color' => 'green'],
            ['label' => 'Follow-ups Today', 'value' => $stats['follow_ups_today'], 'icon' => '📅', 'color' => 'yellow'],
            ['label' => 'Overdue', 'value' => $stats['overdue'], 'icon' => '⚠️', 'color' => 'red'],
        ] as $stat)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="text-2xl mb-1">{{ $stat['icon'] }}</div>
            <div class="text-2xl font-bold">{{ $stat['value'] }}</div>
            <div class="text-xs text-gray-500">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold mb-4">Pipeline / Pipeline</h2>
            <div class="space-y-3">
                @foreach($stageStats as $stage)
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full" style="background: {{ $stage->color }}"></div>
                    <span class="flex-1 text-sm">{{ $stage->name }}</span>
                    <span class="font-semibold">{{ $stage->leads_count }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Recent Leads</h2>
                <a href="{{ route('leads.dashboard') }}" class="text-sm text-indigo-600">Leads CRM →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentLeads as $lead)
                <a href="{{ route('leads.show', $lead) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div>
                        <div class="font-medium">{{ $lead->name }}</div>
                        <div class="text-xs text-gray-500">{{ $lead->company ?? $lead->phone }}</div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full" style="background: {{ $lead->stage?->color }}20; color: {{ $lead->stage?->color }}">{{ $lead->stage?->name }}</span>
                </a>
                @empty
                <p class="text-gray-500 text-sm">No leads yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
