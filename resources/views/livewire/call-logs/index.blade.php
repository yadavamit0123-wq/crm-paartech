<div>
    @include('layouts.partials.leads-nav')
    <h1 class="text-2xl font-bold mb-6">Call Logs Report</h1>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach($stats as $label => $count)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 border-t-4 {{ match($label){ 'incoming'=>'border-t-green-500','outgoing'=>'border-t-blue-500','missed'=>'border-t-red-500','rejected'=>'border-t-gray-500',default=>'border-t-indigo-500'} }}">
            <div class="text-xs text-gray-500 capitalize">{{ $label === 'total' ? 'Total' : $label }}</div>
            <div class="text-2xl font-bold">{{ $count }}</div>
        </div>
        @endforeach
    </div>
    <select wire:model.live="filterDirection" class="mb-4 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
        <option value="">All Directions</option>
        @foreach(['incoming','outgoing','missed','rejected'] as $d)<option value="{{ $d }}">{{ ucfirst($d) }}</option>@endforeach
    </select>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-4 py-3 text-left">Phone</th><th class="px-4 py-3 text-left">Lead</th><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3 text-left">Direction</th><th class="px-4 py-3 text-left">Duration</th><th class="px-4 py-3 text-left">Time</th></tr></thead>
            <tbody>
            @foreach($logs as $log)
            <tr class="border-t dark:border-gray-700">
                <td class="px-4 py-3">{{ $log->phone }}</td>
                <td class="px-4 py-3">@if($log->lead)<a href="{{ route('leads.show', $log->lead) }}" class="text-indigo-600">{{ $log->lead->name }}</a>@else — @endif</td>
                <td class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td>
                <td class="px-4 py-3 capitalize">{{ $log->direction }}</td>
                <td class="px-4 py-3">{{ gmdate('i:s', $log->duration_seconds) }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $log->called_at->format('d M H:i') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $logs->links() }}</div>
    </div>
</div>
