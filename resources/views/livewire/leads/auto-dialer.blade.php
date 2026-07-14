<div>
    @include('layouts.partials.leads-nav')

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Auto Dialer</h1>
        <p class="text-gray-500 text-sm">Click-to-call — auto logs every call to lead timeline</p>
    </div>

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search leads by name or phone..." class="w-full max-w-md px-3 py-2 border rounded-lg mb-4 dark:bg-gray-700 dark:border-gray-600 text-sm">

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Lead</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Stage</th>
                    <th class="px-4 py-3 text-left">Last Call</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($leads as $lead)
            <tr class="border-t dark:border-gray-700 {{ $activeLeadId === $lead->id ? 'bg-green-50 dark:bg-green-900/10' : '' }}">
                <td class="px-4 py-3">
                    <a href="{{ route('leads.show', $lead) }}" class="font-medium text-indigo-600">{{ $lead->name }}</a>
                </td>
                <td class="px-4 py-3">{{ $lead->phone }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs" style="background: {{ $lead->stage?->color }}20; color: {{ $lead->stage?->color }}">{{ $lead->stage?->name }}</span></td>
                <td class="px-4 py-3 text-gray-500">{{ $lead->last_call_at?->diffForHumans() ?? 'Never' }}</td>
                <td class="px-4 py-3">
                    <a href="tel:{{ $lead->phone }}" wire:click="startCall({{ $lead->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm">
                        📞 Call
                    </a>
                    <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 border rounded-lg text-sm ml-1">💬</a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $leads->links() }}</div>
    </div>
</div>
