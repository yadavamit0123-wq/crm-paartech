<div>
    @include('layouts.partials.leads-nav')
    <div class="mb-4"><a href="{{ route('leads.orders') }}" class="text-indigo-600 text-sm">← Back to Orders</a></div>
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $order->order_number }}</h1>
            <p class="text-gray-500">{{ $order->lead?->name ?? $order->customer?->name ?? 'No customer linked' }}</p>
        </div>
        <select wire:change="updateStatus($event.target.value)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            @foreach(['draft','confirmed','processing','fulfilled','cancelled'] as $s)
            <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-4 py-3 text-left">Item</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3 text-right">Price</th><th class="px-4 py-3 text-right">Total</th></tr></thead>
                <tbody>
                @foreach($order->items as $item)
                <tr class="border-t dark:border-gray-700"><td class="px-4 py-3">{{ $item->name }}</td><td class="px-4 py-3 text-right">{{ $item->quantity }}</td><td class="px-4 py-3 text-right">₹{{ number_format($item->unit_price, 2) }}</td><td class="px-4 py-3 text-right">₹{{ number_format($item->total, 2) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span>Tax</span><span>₹{{ number_format($order->tax_amount, 2) }}</span></div>
                <div class="flex justify-between"><span>Discount</span><span>-₹{{ number_format($order->discount_amount, 2) }}</span></div>
                <div class="flex justify-between font-bold text-lg border-t pt-2"><span>Total</span><span class="text-indigo-600">₹{{ number_format($order->total_amount, 2) }}</span></div>
            </div>
            @if($order->notes)<p class="text-sm text-gray-500 mt-4">{{ $order->notes }}</p>@endif
        </div>
    </div>
</div>
