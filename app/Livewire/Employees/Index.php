<?php

namespace App\Livewire\Employees;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public ?int $role_id = null;
    public bool $showForm = false;

    public function save(): void
    {
        if (! auth()->user()->hasPermission('employees.manage')) {
            abort(403);
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            'tenant_id' => auth()->user()->tenant_id,
            'role_id' => $this->role_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'is_active' => true,
        ]);

        $this->reset(['name', 'email', 'phone', 'password', 'role_id', 'showForm']);
        $this->dispatch('notify', message: 'Employee added / Employee add ho gaya');
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);
        if ($user->id === auth()->id()) {
            return;
        }
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function render()
    {
        $employees = User::with('role')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_super_admin', false)
            ->paginate(15);

        $roles = Role::where('tenant_id', auth()->user()->tenant_id)->get();

        return view('livewire.employees.index', compact('employees', 'roles'))
            ->layout('layouts.app');
    }
}
