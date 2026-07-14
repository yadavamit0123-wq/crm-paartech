<?php

namespace App\Livewire\Orders;

use App\Models\Lead;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Livewire\Component;

class Create extends Component
{
    public ?int $leadId = null;
    public string $notes = '';
    public string $discount = '0';
    public array $lines = [];
    public string $productSearch = '';

    public function mount(?int $lead_id = null): void
    {
        $this->leadId = $lead_id;
        $this->addLine();
    }

    public function addLine(): void
    {
        $this->lines[] = ['product_id' => null, 'name' => '', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 18];
    }

    public function selectProduct(int $lineIndex, int $productId): void
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }
        $this->lines[$lineIndex] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 1,
            'unit_price' => (float) $product->price,
            'tax_rate' => (float) $product->tax_rate,
        ];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function save(): void
    {
        $this->validate(['lines' => 'required|array|min:1']);

        $tenantId = auth()->user()->tenant_id;
        $count = Order::where('tenant_id', $tenantId)->count() + 1;
        $orderNumber = 'ORD-'.date('Y').'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            'tenant_id' => $tenantId,
            'lead_id' => $this->leadId,
            'created_by' => auth()->id(),
            'order_number' => $orderNumber,
            'status' => 'draft',
            'discount_amount' => $this->discount,
            'notes' => $this->notes ?: null,
        ]);

        foreach ($this->lines as $line) {
            if (empty($line['name'])) {
                continue;
            }
            $total = $line['quantity'] * $line['unit_price'];
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $line['product_id'],
                'name' => $line['name'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'tax_rate' => $line['tax_rate'],
                'total' => $total,
            ]);
        }

        $order->load('items');
        $order->recalculateTotals();

        $this->redirect(route('leads.orders.show', $order), navigate: true);
    }

    public function render()
    {
        $products = Product::where('is_active', true)
            ->when($this->productSearch, fn ($q) => $q->where('name', 'like', "%{$this->productSearch}%"))
            ->limit(10)->get();
        $leads = Lead::latest()->limit(50)->get(['id', 'name', 'phone']);

        return view('livewire.orders.create', compact('products', 'leads'))
            ->layout('layouts.app');
    }
}
