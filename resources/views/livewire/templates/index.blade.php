<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">Templates</h1><p class="text-gray-500 text-sm">WhatsApp, Email & SMS templates</p></div>
        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New Template</button>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($templates as $t)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700">
            <div class="flex justify-between items-start mb-2">
                <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700">{{ ucfirst($t->channel) }}</span>
                <span class="text-xs {{ $t->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <h3 class="font-semibold">{{ $t->name }}</h3>
            <p class="text-sm text-gray-500 mt-2 line-clamp-3">{{ $t->body }}</p>
            <div class="flex gap-2 mt-4">
                <button wire:click="edit({{ $t->id }})" class="text-sm text-indigo-600">Edit</button>
                <button wire:click="delete({{ $t->id }})" wire:confirm="Delete?" class="text-sm text-red-600">Delete</button>
            </div>
        </div>
        @endforeach
    </div>
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-lg">
            <h3 class="font-bold mb-4">{{ $editId ? 'Edit' : 'New' }} Template</h3>
            <div class="space-y-3">
                <input wire:model="name" placeholder="Template name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <select wire:model="channel" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="whatsapp">WhatsApp</option><option value="email">Email</option><option value="sms">SMS</option>
                </select>
                @if($channel === 'email')<input wire:model="subject" placeholder="Email subject" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">@endif
                <textarea wire:model="body" rows="5" placeholder="Message body — use @{{name}} for variables" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="isActive"> Active</label>
            </div>
            <div class="flex gap-2 mt-4">
                <button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Save</button>
                <button wire:click="$set('showModal', false)" class="flex-1 py-2 border rounded-lg">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
