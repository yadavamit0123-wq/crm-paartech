<div>
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Accounting / लेखांकन</h1>
            <p class="text-gray-500 text-sm">Sales, purchases & tax summary</p>
        </div>
        <input type="month" wire:model.live="period" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Sales (Invoices)</div>
            <div class="text-xl font-bold text-green-600">₹{{ number_format($salesTotal, 0) }}</div>
            <div class="text-xs text-gray-400">{{ $invoiceCount }} invoices</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Output GST</div>
            <div class="text-xl font-bold text-indigo-600">₹{{ number_format($salesTax, 0) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Purchases</div>
            <div class="text-xl font-bold text-red-600">₹{{ number_format($purchaseTotal, 0) }}</div>
            <div class="text-xs text-gray-400">{{ $expenseCount }} expenses</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Input GST (ITC)</div>
            <div class="text-xl font-bold text-purple-600">₹{{ number_format($purchaseTax, 0) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Net GST Liability</div>
            <div class="text-xl font-bold text-orange-600">₹{{ number_format(max(0, $salesTax - $purchaseTax), 0) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Payments Received</div>
            <div class="text-xl font-bold">₹{{ number_format($paymentsReceived, 0) }}</div>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        @if(auth()->user()->hasPermission('expenses.manage'))
        <a href="{{ route('expenses.create') }}" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 hover:border-indigo-500 transition">
            <div class="text-2xl mb-2">💸</div>
            <div class="font-semibold">Add Expense</div>
            <div class="text-sm text-gray-500">Purchase entry with GST</div>
        </a>
        @endif
        @if(auth()->user()->hasPermission('gst.export'))
        <a href="{{ route('gst-reports.index') }}" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 hover:border-indigo-500 transition">
            <div class="text-2xl mb-2">📊</div>
            <div class="font-semibold">GST Reports</div>
            <div class="text-sm text-gray-500">GSTR-1, GSTR-3B export</div>
        </a>
        @endif
        @if(auth()->user()->hasPermission('payroll.manage'))
        <a href="{{ route('payroll.index') }}" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 hover:border-indigo-500 transition">
            <div class="text-2xl mb-2">💰</div>
            <div class="font-semibold">Payroll</div>
            <div class="text-sm text-gray-500">Process monthly salary</div>
        </a>
        @endif
    </div>
</div>
