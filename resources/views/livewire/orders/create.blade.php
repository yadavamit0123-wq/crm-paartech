<div>
    @include('layouts.partials.leads-nav')
    <div class="mb-4"><a href="{{ route('leads.orders') }}" class="text-indigo-600 text-sm">← Back to Orders</a></div>
    <h1 class="text-2xl font-bold mb-6">Create Order</h1>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            @foreach($lines as $i => $line)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border dark:border-gray-700">
                <div class="flex justify-between mb-2"><span class="text-sm font-medium">Line {{ $i + 1 }}</span>@if(count($lines) > 1)<button wire:click="removeLine({{ $i }})" class="text-red-500 text-xs">Remove</button>@endif</div>
                <input wire:model="lines.{{ $i }}.name" placeholder="Item name" class="w-full px-3 py-2 border rounded-lg mb-2 dark:bg-gray-700 dark:border-gray-600 text-sm">
                <div class="grid grid-cols-3 gap-2">
                    <input wire:model="lines.{{ $i }}.quantity" type="number" placeholder="Qty" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                    <input wire:model="lines.{{ $i }}.unit_price" type="number" placeholder="Price" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                    <input wire:model="lines.{{ $i }}.tax_rate" placeholder="Tax %" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                </div>
            </div>
            @endforeach
            <button wire:click="addLine" class="px-4 py-2 border rounded-lg text-sm">+ Add Line Item</button>
        </div>
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border dark:border-gray-700">
                <h3 class="font-semibold mb-3">Quick Add Product</h3>
                <input wire:model.live.debounce.300ms="productSearch" placeholder="Search products..." class="w-full px-3 py-2 border rounded-lg mb-2 dark:bg-gray-700 dark:border-gray-600 text-sm">
                @foreach($products as $p)
                <button wire:click="selectProduct({{ count($lines) - 1 }}, {{ $p->id }})" class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded text-sm">{{ $p->name }} — ₹{{ $p->price }}</button>
                @endforeach
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border dark:border-gray-700 space-y-3">
                <select wire:model="leadId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm"><option value="">Link to lead (optional)</option>@foreach($leads as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select>
                <input wire:model="discount" type="number" placeholder="Discount amount" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                <textarea wire:model="notes" rows="2" placeholder="Notes" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm"></textarea>
                <button wire:click="save" class="w-full py-2 bg-indigo-600 text-white rounded-lg">Create Order</button>
            </div>
        </div>
    </div>
</div>
