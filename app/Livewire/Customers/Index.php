<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = Customer::with(['lead', 'creator'])->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('company', 'like', "%{$this->search}%");
            });
        }

        $customers = $query->paginate(15);

        return view('livewire.customers.index', compact('customers'))
            ->layout('layouts.app');
    }
}
