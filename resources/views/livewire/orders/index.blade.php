<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">Orders</h1><p class="text-gray-500 text-sm">Manage sales orders</p></div>
        <a href="{{ route('leads.orders.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New Order</a>
    </div>
    <div class="flex gap-2 mb-4">
        <input wire:model.live.debounce.300ms="search" placeholder="Search order number..." class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
        <select wire:model.live="filterStatus" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="">All Status</option>
            @foreach(['draft','confirmed','processing','fulfilled','cancelled'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
        </select>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700"><tr>
                <th class="px-4 py-3 text-left">Order #</th><th class="px-4 py-3 text-left">Lead/Customer</th>
                <th class="px-4 py-3 text-left">Total</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Date</th>
            </tr></thead>
            <tbody>
            @foreach($orders as $order)
            <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" onclick="window.location='{{ route('leads.orders.show', $order) }}'">
                <td class="px-4 py-3 font-medium text-indigo-600">{{ $order->order_number }}</td>
                <td class="px-4 py-3">{{ $order->lead?->name ?? $order->customer?->name ?? '—' }}</td>
                <td class="px-4 py-3">₹{{ number_format($order->total_amount, 2) }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100">{{ ucfirst($order->status) }}</span></td>
                <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $orders->links() }}</div>
    </div>
</div>
