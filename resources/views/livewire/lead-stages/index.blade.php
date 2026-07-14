<div>
    <h1 class="text-2xl font-bold mb-6">Lead Stages / लीड स्टेज</h1>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">Create New Stage</h3>
        <div class="flex gap-3">
            <input type="text" wire:model="name" placeholder="Stage name" class="flex-1 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="color" wire:model="color" class="w-12 h-10 rounded cursor-pointer">
            <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Add Stage</button>
        </div>
    </div>

    <div class="space-y-3">
        @foreach($stages as $stage)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-4 h-4 rounded-full" style="background: {{ $stage->color }}"></div>
                @if($editingId === $stage->id)
                <input type="text" wire:model="editName" class="px-2 py-1 border rounded dark:bg-gray-700 dark:border-gray-600">
                <input type="color" wire:model="editColor" class="w-10 h-8 rounded cursor-pointer">
                <button wire:click="update" class="text-sm text-green-600">Save</button>
                @else
                <span class="font-medium">{{ $stage->name }}</span>
                <span class="text-xs text-gray-500">({{ $stage->leads_count }} leads)</span>
                @if($stage->is_won)<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Won</span>@endif
                @if($stage->is_lost)<span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Lost</span>@endif
                @endif
            </div>
            <div class="flex gap-2">
                <button wire:click="edit({{ $stage->id }})" class="text-sm text-indigo-600">Edit</button>
                @if(!$stage->is_system)
                <button wire:click="delete({{ $stage->id }})" wire:confirm="Delete this stage?" class="text-sm text-red-500">Delete</button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
