<?php

namespace App\Livewire\Orders;

use App\Models\Lead;
use App\Models\Order;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public function render()
    {
        $orders = Order::with(['lead', 'customer', 'creator'])
            ->when($this->search, fn ($q) => $q->where('order_number', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(20);

        return view('livewire.orders.index', compact('orders'))
            ->layout('layouts.app');
    }
}
