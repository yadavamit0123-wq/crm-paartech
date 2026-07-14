<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Tenant;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public function render()
    {
        $stats = [
            'tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_users' => User::where('is_super_admin', false)->count(),
        ];

        $tenants = Tenant::with('subscription.plan')->latest()->paginate(10);

        return view('livewire.super-admin.dashboard', compact('stats', 'tenants'))
            ->layout('layouts.app');
    }
}
