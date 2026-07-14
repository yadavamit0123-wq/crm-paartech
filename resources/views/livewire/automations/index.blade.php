<div>
    @include('layouts.partials.leads-nav')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div><h1 class="text-2xl font-bold">Automation</h1><p class="text-gray-500 text-sm">Trigger-based workflows & drip sequences</p></div>
        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search rules..." class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
            <button class="px-3 py-2 border rounded-lg text-sm" onclick="window.open('https://www.youtube.com/results?search_query=crm+automation', '_blank')">❓ Help</button>
            <button class="px-3 py-2 border rounded-lg text-sm" onclick="window.open('https://www.youtube.com/results?search_query=3sigma+crm+automation', '_blank')">🎬 Video</button>
            <button wire:click="$refresh" class="px-3 py-2 border rounded-lg text-sm">🔄 Refresh</button>
            <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New rule</button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Rule Name</th>
                    <th class="px-4 py-3 text-left">Last Run</th>
                    <th class="px-4 py-3 text-left">No of Runs</th>
                    <th class="px-4 py-3 text-left">Completed</th>
                    <th class="px-4 py-3 text-left">Errors</th>
                    <th class="px-4 py-3 text-left">Leads</th>
                    <th class="px-4 py-3 text-left">Worked</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($automations as $a)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="px-4 py-3 font-medium">{{ $a->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $a->last_run_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="px-4 py-3">{{ $a->runs_count }}</td>
                    <td class="px-4 py-3 text-green-600">{{ $a->completed_count ?? 0 }}</td>
                    <td class="px-4 py-3 text-red-600">{{ $a->error_count ?? 0 }}</td>
                    <td class="px-4 py-3">{{ $a->leads_affected ?? 0 }}</td>
                    <td class="px-4 py-3">{{ $a->completed_count ?? 0 }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $a->is_active ? 'bg-green-100 text-green-700' : ($a->is_draft ? 'bg-amber-100 text-amber-700' : 'bg-gray-100') }}">
                            {{ $a->is_draft ? 'Draft' : ($a->is_active ? 'Active' : 'Paused') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button wire:click="runNow({{ $a->id }})" class="text-green-600 text-sm">Run Now</button>
                            <button wire:click="edit({{ $a->id }})" class="text-indigo-600 text-sm">Edit</button>
                            <button wire:click="toggleActive({{ $a->id }})" class="text-sm">{{ $a->is_active ? 'Pause' : 'Activate' }}</button>
                            <button wire:click="delete({{ $a->id }})" wire:confirm="Delete?" class="text-red-600 text-sm">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-gray-500">No automation rules yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showWizard)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            @if($wizardStep === 1)
            <h3 class="font-bold text-lg mb-4">Choose Automation Trigger</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach($triggers as $k => $v)
                <button wire:click="selectTrigger('{{ $k }}')" class="p-4 border rounded-xl text-left hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                    <div class="font-medium text-sm">{{ $v }}</div>
                </button>
                @endforeach
            </div>
            @elseif($wizardStep === 2)
            <h3 class="font-bold text-lg mb-4">Choose Action</h3>
            @foreach($actionGroups as $group => $actions)
            <div class="mb-4">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $group }}</div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($actions as $k => $v)
                    <button wire:click="selectAction('{{ $k }}')" class="p-3 border rounded-lg text-left text-sm hover:border-indigo-500">{{ $v }}</button>
                    @endforeach
                </div>
            </div>
            @endforeach
            @else
            <h3 class="font-bold text-lg mb-4">Day-based Workflow Builder</h3>
            <input wire:model="name" placeholder="Rule name *" class="w-full px-3 py-2 border rounded-lg mb-4 dark:bg-gray-700 dark:border-gray-600">
            @foreach($dayActions as $i => $day)
            <div class="border rounded-xl p-4 mb-3 dark:border-gray-600">
                <div class="font-semibold text-sm mb-2">Day {{ $day['day'] }} actions</div>
                <textarea wire:model="dayActions.{{ $i }}.message" rows="2" placeholder="Action message for day {{ $day['day'] }}" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
            </div>
            @endforeach
            <button wire:click="addDay" class="text-sm text-indigo-600 mb-4">+ day delay → ADD DAY</button>
            <textarea wire:model="actionMessage" rows="2" placeholder="Default action message" class="w-full px-3 py-2 border rounded-lg mb-4 dark:bg-gray-700 dark:border-gray-600"></textarea>
            <div class="flex gap-2">
                <button wire:click="save(true)" class="px-4 py-2 border rounded-lg text-sm">Save as Draft</button>
                <button wire:click="save(false)" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Preview & Create</button>
                <button wire:click="$set('showWizard', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
            </div>
            @endif
            @if($wizardStep < 3)
            <button wire:click="$set('showWizard', false)" class="mt-4 text-sm text-gray-500">Cancel</button>
            @endif
        </div>
    </div>
    @endif
</div>
