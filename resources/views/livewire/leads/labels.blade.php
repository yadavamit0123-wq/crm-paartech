<div>
    @include('layouts.partials.leads-nav')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Lead Labels</h1>
            <p class="text-gray-500 text-sm">3Sigma-style color labels — Hot, Warm, Cold, VIP & custom</p>
        </div>
        @if(auth()->user()->hasPermission('leads.edit'))
        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm shadow-sm">+ New Label</button>
        @endif
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($labels as $label)
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border-2 dark:border-gray-700 hover:shadow-md transition" style="border-color: {{ $label->color }}40">
            <div class="flex items-start justify-between mb-3">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold shadow-sm" style="background: linear-gradient(135deg, {{ $label->color }}22, {{ $label->color }}44); color: {{ $label->color }}; border: 2px solid {{ $label->color }}">
                    <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $label->color }}"></span>
                    {{ $label->name }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mb-3">{{ $label->leads_count }} leads tagged</p>
            <div class="flex gap-2">
                @if(auth()->user()->hasPermission('leads.edit'))
                <button wire:click="edit({{ $label->id }})" class="text-sm text-indigo-600 font-medium">Edit</button>
                <button wire:click="delete({{ $label->id }})" wire:confirm="Delete this label?" class="text-sm text-red-600">Delete</button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md p-6 shadow-xl">
            <h3 class="font-bold text-lg mb-4">{{ $editId ? 'Edit Label' : 'Create Label' }}</h3>
            <input wire:model="name" placeholder="Label name (e.g. Hot, VIP)" class="w-full px-4 py-3 border-2 rounded-xl dark:bg-gray-700 dark:border-gray-600 mb-4 text-sm focus:border-indigo-500 outline-none">
            <p class="text-xs text-gray-500 mb-2">Pick color</p>
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($presets as $preset)
                <button type="button" wire:click="$set('color', '{{ $preset }}')" class="w-8 h-8 rounded-full border-2 transition {{ $color === $preset ? 'border-gray-900 scale-110' : 'border-transparent' }}" style="background: {{ $preset }}"></button>
                @endforeach
            </div>
            <div class="flex items-center gap-3 mb-4 p-3 rounded-xl bg-gray-50 dark:bg-gray-700">
                <span class="text-sm text-gray-500">Preview:</span>
                <span class="px-4 py-1.5 rounded-full text-sm font-bold" style="background: {{ $color }}22; color: {{ $color }}; border: 2px solid {{ $color }}">{{ $name ?: 'Label' }}</span>
            </div>
            @if($editId)<p class="text-xs text-gray-400 mb-3">Used on {{ $leadCount }} leads</p>@endif
            <div class="flex gap-2">
                <button wire:click="$set('showModal', false)" class="flex-1 py-2.5 border rounded-xl text-sm">Cancel</button>
                <button wire:click="save" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium">Save Label</button>
            </div>
        </div>
    </div>
    @endif
</div>
