<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Expenses / खर्च</h1>
            <p class="text-gray-500 text-sm">Purchase register for GST ITC</p>
        </div>
        @if(auth()->user()->hasPermission('expenses.manage'))
        <a href="{{ route('expenses.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add Expense</a>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 mb-4 shadow-sm border dark:border-gray-700 flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search vendor..." class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm flex-1 min-w-[180px]">
        <select wire:model.live="filterCategory" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Vendor</th>
                    <th class="px-4 py-3 text-left">Invoice</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-right">Taxable</th>
                    <th class="px-4 py-3 text-right">GST</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($expenses as $expense)
                <tr>
                    <td class="px-4 py-3">{{ $expense->invoice_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $expense->vendor_name }}</div>
                        @if($expense->vendor_gstin)<div class="text-xs text-gray-500">{{ $expense->vendor_gstin }}</div>@endif
                    </td>
                    <td class="px-4 py-3">{{ $expense->invoice_number ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $categories[$expense->category] ?? $expense->category }}</td>
                    <td class="px-4 py-3 text-right">₹{{ number_format($expense->taxable_amount, 2) }}</td>
                    <td class="px-4 py-3 text-right">₹{{ number_format($expense->cgst_amount + $expense->sgst_amount + $expense->igst_amount, 2) }}</td>
                    <td class="px-4 py-3 text-right font-semibold">₹{{ number_format($expense->total_amount, 2) }}</td>
                    <td class="px-4 py-3">
                        @if(auth()->user()->hasPermission('expenses.manage'))
                        <button wire:click="delete({{ $expense->id }})" wire:confirm="Delete?" class="text-red-500 text-xs">Delete</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No expenses yet</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $expenses->links() }}</div>
    </div>
</div>
