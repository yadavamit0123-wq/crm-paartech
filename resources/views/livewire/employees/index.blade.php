<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Employees / कर्मचारी</h1>
        <button wire:click="$set('showForm', true)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add Employee</button>
    </div>

    @if($showForm)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">Add New Employee</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <input type="text" wire:model="name" placeholder="Full Name" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="email" wire:model="email" placeholder="Email" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="text" wire:model="phone" placeholder="Phone" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="password" wire:model="password" placeholder="Password" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <select wire:model="role_id" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <option value="">Select Role</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        <div class="flex gap-2 mt-4">
            <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Save</button>
            <button wire:click="$set('showForm', false)" class="px-4 py-2 border rounded-lg">Cancel</button>
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @foreach($employees as $employee)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $employee->name }}</td>
                    <td class="px-4 py-3">{{ $employee->email }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded text-xs">{{ $employee->role?->name }}</span></td>
                    <td class="px-4 py-3">
                        <span class="{{ $employee->is_active ? 'text-green-500' : 'text-red-500' }}">{{ $employee->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($employee->id !== auth()->id())
                        <button wire:click="toggleActive({{ $employee->id }})" class="text-sm text-indigo-600">{{ $employee->is_active ? 'Deactivate' : 'Activate' }}</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $employees->links() }}</div>
    </div>
</div>
