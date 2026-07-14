<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">Digital Visiting Cards</h1><p class="text-gray-500 text-sm">Shareable digital business cards</p></div>
        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Create Card</button>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($cards as $card)
        <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl p-6 text-white shadow-lg">
            <h3 class="text-xl font-bold">{{ $card->name }}</h3>
            <p class="text-indigo-200 text-sm">{{ $card->designation }}</p>
            <div class="mt-4 space-y-1 text-sm">
                @if($card->phone)<p>📞 {{ $card->phone }}</p>@endif
                @if($card->email)<p>✉️ {{ $card->email }}</p>@endif
                @if($card->website)<p>🌐 {{ $card->website }}</p>@endif
            </div>
            <div class="flex gap-2 mt-4">
                <a href="{{ $card->publicUrl() }}" target="_blank" class="px-3 py-1 bg-white/20 rounded text-sm">View Public</a>
                <button wire:click="delete({{ $card->id }})" wire:confirm="Delete?" class="px-3 py-1 bg-red-500/50 rounded text-sm">Delete</button>
            </div>
        </div>
        @endforeach
    </div>
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-3">
            <h3 class="font-bold">Create Visiting Card</h3>
            <input wire:model="name" placeholder="Name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input wire:model="designation" placeholder="Designation" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input wire:model="phone" placeholder="Phone" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input wire:model="email" placeholder="Email" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input wire:model="website" placeholder="Website" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <div class="flex gap-2"><button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Create</button><button wire:click="$set('showModal', false)" class="flex-1 py-2 border rounded-lg">Cancel</button></div>
        </div>
    </div>
    @endif
</div>
