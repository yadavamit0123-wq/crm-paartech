<div>
    @include('layouts.partials.leads-nav')
    <div class="mb-6">
        <a href="{{ route('leads.list') }}" class="text-indigo-600 text-sm">← Back to Leads</a>
        <h1 class="text-2xl font-bold mt-2">Create Lead / नई लीड</h1>
    </div>

    @if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300 max-w-3xl">
        <p class="font-semibold mb-1">Save se pehle ye fix karein:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div wire:loading.flex wire:target="save" class="fixed inset-0 z-40 bg-black/20 items-center justify-center">
        <div class="bg-white dark:bg-gray-800 px-6 py-4 rounded-xl shadow-lg text-sm font-medium">Saving lead...</div>
    </div>

    <form wire:submit="save" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 max-w-3xl">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name *</label>
                <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Phone</label>
                <input type="text" wire:model="phone" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Company</label>
                <input type="text" wire:model="company" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Source</label>
                <select wire:model="source" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    @foreach(['manual','website','meta','google','whatsapp','referral','cold_call'] as $src)
                    <option value="{{ $src }}">{{ ucfirst(str_replace('_',' ',$src)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Stage *</label>
                <select wire:model="lead_stage_id" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    @forelse($stages as $stage)
                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @empty
                    <option value="{{ $lead_stage_id }}">New (auto-created)</option>
                    @endforelse
                </select>
                @error('lead_stage_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Assign To</label>
                <select wire:model="assigned_to" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Priority</label>
                <select wire:model="priority" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    @foreach(['low','medium','high','urgent'] as $p)
                    <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">City</label>
                <input type="text" wire:model="city" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Value (₹)</label>
                <input type="number" wire:model="value" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea wire:model="notes" rows="3" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            </div>
        </div>
        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-60">
            <span wire:loading.remove wire:target="save">Save Lead / सेव करें</span>
            <span wire:loading wire:target="save">Saving...</span>
        </button>
    </form>
</div>
