<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">WhatsApp Bots</h1><p class="text-gray-500 text-sm">Auto-reply & conversation flows</p></div>
        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New Bot</button>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($bots as $bot)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700">
            <div class="flex justify-between items-start mb-2">
                <span class="text-2xl">🤖</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $bot->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100' }}">{{ $bot->is_active ? 'Live' : 'Draft' }}</span>
            </div>
            <h3 class="font-semibold">{{ $bot->name }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $bot->description }}</p>
            @if($bot->trigger_keyword)<p class="text-xs text-indigo-600 mt-2">Keyword: {{ $bot->trigger_keyword }}</p>@endif
            <p class="text-xs text-gray-400 mt-1">{{ $bot->sessions_count }} sessions</p>
            <div class="flex gap-2 mt-4">
                <a href="{{ route('leads.bots.builder', $bot) }}" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Edit Flow</a>
                <button wire:click="toggleActive({{ $bot->id }})" class="px-3 py-1 border rounded text-sm">{{ $bot->is_active ? 'Deactivate' : 'Activate' }}</button>
                <button wire:click="delete({{ $bot->id }})" wire:confirm="Delete?" class="text-red-600 text-sm">Delete</button>
            </div>
        </div>
        @endforeach
    </div>
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-3">
            <h3 class="font-bold">New WhatsApp Bot</h3>
            <input wire:model="name" placeholder="Bot name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <textarea wire:model="description" rows="2" placeholder="Description" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            <input wire:model="triggerKeyword" placeholder="Trigger keyword (optional)" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <div class="flex gap-2"><button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Create & Build</button><button wire:click="$set('showModal', false)" class="flex-1 py-2 border rounded-lg">Cancel</button></div>
        </div>
    </div>
    @endif
</div>
