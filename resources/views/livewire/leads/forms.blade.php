<div>
    @include('layouts.partials.leads-nav')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">User Forms</h1>
            <p class="text-gray-500 text-sm">Capture leads via custom forms — auto-save to lead lists</p>
        </div>
        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Create New Form</button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Form Name</th>
                    <th class="px-4 py-3 text-left">Leads Count</th>
                    <th class="px-4 py-3 text-left">Created On</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($forms as $form)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $form->name }}</div>
                        @if($form->description)<div class="text-xs text-gray-500">{{ Str::limit($form->description, 50) }}</div>@endif
                        @if($form->leadList)<div class="text-xs text-indigo-600 mt-0.5">→ {{ $form->leadList->name }}</div>@endif
                    </td>
                    <td class="px-4 py-3 font-semibold">{{ $form->leads_count }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $form->created_at->format('d-m-Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $form->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($form->status) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button wire:click="toggleStatus({{ $form->id }})" class="text-sm text-indigo-600">{{ $form->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                            <button onclick="navigator.clipboard.writeText('{{ url('/api/leads/capture') }}?form={{ $form->slug }}')" class="text-sm text-gray-600">Copy Link</button>
                            <button wire:click="delete({{ $form->id }})" wire:confirm="Delete form?" class="text-sm text-red-600">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No forms yet. Create your first form.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md p-6">
            <h3 class="font-bold text-lg mb-4">Create New Form</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500">Form name *</label>
                    <input wire:model="name" placeholder="e.g. Website Contact Form" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500">Save to list</label>
                    <select wire:model="leadListId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Default lead list</option>
                        @foreach($leadLists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Description</label>
                    <textarea wire:model="description" rows="2" placeholder="Optional description" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
                <button wire:click="save" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Create Form</button>
            </div>
        </div>
    </div>
    @endif
</div>
