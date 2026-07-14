<div>
    @include('layouts.partials.leads-nav')

    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">Tasks</h1><p class="text-gray-500 text-sm">Follow-ups & to-dos</p></div>
        <div class="flex gap-2">
            <button wire:click="$toggle('dayView')" class="px-3 py-2 border rounded-lg text-sm {{ $dayView ? 'bg-indigo-600 text-white' : '' }}">
                Day View <span class="text-xs bg-green-500 text-white px-1.5 py-0.5 rounded ml-1">NEW</span>
            </button>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Filter tasks..." class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
            <button wire:click="$refresh" class="px-3 py-2 border rounded-lg text-sm">🔄 Refresh</button>
        </div>
    </div>

    <div class="flex gap-2 mb-4 flex-wrap">
        @foreach(['today'=>'Today','upcoming'=>'Upcoming','overdue'=>'Overdue','done'=>'Done'] as $k=>$label)
        <button wire:click="$set('tab', '{{ $k }}')" class="px-4 py-2 rounded-full text-sm {{ $tab === $k ? 'bg-indigo-600 text-white' : 'border bg-white dark:bg-gray-800' }}">{{ $label }} ({{ $counts[$k] ?? 0 }})</button>
        @endforeach
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden mb-16">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Lead Name</th>
                    <th class="px-4 py-3 text-left">Task Owner</th>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('due_at')">Due Time {{ $sortField === 'due_at' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Assigned To</th>
                    <th class="px-4 py-3 text-left">Note</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($tasks as $task)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 {{ $task->isOverdue() ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                    <td class="px-4 py-3">
                        @if($task->lead)<a href="{{ route('leads.show', $task->lead) }}" class="text-indigo-600 font-medium">{{ $task->lead->name }}</a>@else<span class="text-gray-400">—</span>@endif
                    </td>
                    <td class="px-4 py-3">{{ $task->assignee?->name ?? '—' }}</td>
                    <td class="px-4 py-3 {{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">{{ $task->due_at?->format('d M Y H:i') ?? 'No due date' }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-700">{{ $taskTypes[$task->task_type ?? 'follow_up'] ?? ucfirst($task->task_type ?? 'follow_up') }}</span></td>
                    <td class="px-4 py-3">{{ $task->assignee?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 max-w-[200px] truncate">{{ $task->description ?? $task->title }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            @if($task->status === 'pending')<button wire:click="complete({{ $task->id }})" class="text-green-600 text-sm">Done</button>@endif
                            <button wire:click="delete({{ $task->id }})" wire:confirm="Delete?" class="text-red-600 text-sm">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No tasks in this tab</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Floating Add Task --}}
    <button wire:click="openCreate" class="fixed bottom-8 right-8 w-14 h-14 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 flex items-center justify-center text-2xl z-40" title="Add Task">+</button>

    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-3">
            <h3 class="font-bold">New Task</h3>
            <input wire:model="title" placeholder="Task title" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <textarea wire:model="description" rows="2" placeholder="Note / description" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            <input type="datetime-local" wire:model="dueAt" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <select wire:model="taskType" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">@foreach($taskTypes as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
            <select wire:model="assigneeId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select>
            <select wire:model="leadId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"><option value="">Link lead (optional)</option>@foreach($leads as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select>
            <select wire:model="priority" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option></select>
            <div class="flex gap-2"><button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Create</button><button wire:click="$set('showModal', false)" class="flex-1 py-2 border rounded-lg">Cancel</button></div>
        </div>
    </div>
    @endif
</div>
