<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items.product', 'lead', 'customer', 'creator']);
    }

    public function updateStatus(string $status): void
    {
        $this->order->update(['status' => $status]);
        $this->order->refresh();
        $this->dispatch('notify', message: 'Order status updated');
    }

    public function render()
    {
        return view('livewire.orders.show')
            ->layout('layouts.app');
    }
}
