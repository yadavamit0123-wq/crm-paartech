<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">Custom Fields</h1><p class="text-gray-500 text-sm">Form builder for leads & customers</p></div>
        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add Field</button>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-4 py-3 text-left">Label</th><th class="px-4 py-3 text-left">Entity</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Required</th><th class="px-4 py-3 text-left">Actions</th></tr></thead>
            <tbody>
            @foreach($fields as $f)
            <tr class="border-t dark:border-gray-700">
                <td class="px-4 py-3 font-medium">{{ $f->label }}</td>
                <td class="px-4 py-3 capitalize">{{ $f->entity_type }}</td>
                <td class="px-4 py-3 capitalize">{{ $f->field_type }}</td>
                <td class="px-4 py-3">{{ $f->is_required ? 'Yes' : 'No' }}</td>
                <td class="px-4 py-3"><button wire:click="delete({{ $f->id }})" wire:confirm="Delete?" class="text-red-600 text-sm">Delete</button></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-3">
            <h3 class="font-bold">New Custom Field</h3>
            <input wire:model="label" placeholder="Field label" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <select wire:model="entityType" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"><option value="lead">Lead</option><option value="customer">Customer</option></select>
            <select wire:model="fieldType" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"><option value="text">Text</option><option value="number">Number</option><option value="date">Date</option><option value="select">Dropdown</option><option value="phone">Phone</option><option value="email">Email</option></select>
            @if($fieldType === 'select')<input wire:model="options" placeholder="Options (comma separated)" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">@endif
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="isRequired"> Required</label>
            <div class="flex gap-2"><button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Create</button><button wire:click="$set('showModal', false)" class="flex-1 py-2 border rounded-lg">Cancel</button></div>
        </div>
    </div>
    @endif
</div>
