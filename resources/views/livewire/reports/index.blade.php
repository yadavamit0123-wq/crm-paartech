<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">Reports & Analytics</h1><p class="text-gray-500 text-sm">Team performance & call analytics</p></div>
        <select wire:model.live="period" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="week">This Week</option><option value="month">This Month</option><option value="year">This Year</option>
        </select>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach(['leads'=>'Leads','calls'=>'Calls','orders'=>'Orders','revenue'=>'Revenue','tasks_done'=>'Tasks Done'] as $k=>$label)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">{{ $label }}</div>
            <div class="text-2xl font-bold text-indigo-600">{{ $k === 'revenue' ? '₹'.number_format($stats[$k], 0) : $stats[$k] }}</div>
        </div>
        @endforeach
    </div>
    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">Call Breakdown</h3>
            @foreach($callBreakdown as $type => $count)
            <div class="flex justify-between py-2 border-b dark:border-gray-700 text-sm"><span class="capitalize">{{ $type }}</span><span class="font-medium">{{ $count }}</span></div>
            @endforeach
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">Team Performance</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500"><th class="pb-2">Member</th><th>Leads</th><th>Calls</th><th>Orders</th></tr></thead>
                <tbody>
                @foreach($teamPerformance as $row)
                <tr class="border-t dark:border-gray-700"><td class="py-2">{{ $row['user']->name }}</td><td>{{ $row['leads'] }}</td><td>{{ $row['calls'] }}</td><td>{{ $row['orders'] }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('leads.call-logs') }}" class="inline-block px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">View Call Logs Report →</a>
</div>
