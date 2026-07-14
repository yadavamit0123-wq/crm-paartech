<div>
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">GST Reports / GST रिपोर्ट</h1>
            <p class="text-gray-500 text-sm">GSTR-1, GSTR-3B & Register exports</p>
        </div>
        <input type="month" wire:model.live="period" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        {{-- GSTR-1 Summary --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">GSTR-1 Summary (Outward Supplies)</h3>
            <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded"><div class="text-gray-500">Invoices</div><div class="font-bold">{{ $gstr1['summary']['total_invoices'] }}</div></div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded"><div class="text-gray-500">Taxable Value</div><div class="font-bold">₹{{ number_format($gstr1['summary']['taxable_value'], 2) }}</div></div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded"><div class="text-gray-500">CGST</div><div class="font-bold">₹{{ number_format($gstr1['summary']['total_cgst'], 2) }}</div></div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded"><div class="text-gray-500">SGST</div><div class="font-bold">₹{{ number_format($gstr1['summary']['total_sgst'], 2) }}</div></div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded"><div class="text-gray-500">IGST</div><div class="font-bold">₹{{ number_format($gstr1['summary']['total_igst'], 2) }}</div></div>
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded"><div class="text-gray-500">Total Tax</div><div class="font-bold text-indigo-600">₹{{ number_format($gstr1['summary']['total_tax'], 2) }}</div></div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('gst.export.gstr1.csv', ['period' => $period]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">📥 GSTR-1 CSV</a>
                <a href="{{ route('gst.export.gstr1.json', ['period' => $period]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">📥 GSTR-1 JSON</a>
                <a href="{{ route('gst.export.sales', ['period' => $period]) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm">📥 Sales Register</a>
            </div>
        </div>

        {{-- GSTR-3B Summary --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">GSTR-3B Summary (Tax Liability)</h3>
            <table class="w-full text-sm mb-4">
                <thead><tr class="border-b dark:border-gray-700">
                    <th class="py-2 text-left"></th><th class="py-2 text-right">CGST</th><th class="py-2 text-right">SGST</th><th class="py-2 text-right">IGST</th>
                </tr></thead>
                <tbody>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">Outward Tax</td>
                        <td class="py-2 text-right">₹{{ number_format($gstr3b['outward_supplies']['cgst'], 2) }}</td>
                        <td class="py-2 text-right">₹{{ number_format($gstr3b['outward_supplies']['sgst'], 2) }}</td>
                        <td class="py-2 text-right">₹{{ number_format($gstr3b['outward_supplies']['igst'], 2) }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">ITC (Purchases)</td>
                        <td class="py-2 text-right text-green-600">-₹{{ number_format($gstr3b['input_tax_credit']['cgst'], 2) }}</td>
                        <td class="py-2 text-right text-green-600">-₹{{ number_format($gstr3b['input_tax_credit']['sgst'], 2) }}</td>
                        <td class="py-2 text-right text-green-600">-₹{{ number_format($gstr3b['input_tax_credit']['igst'], 2) }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td class="py-2">Net Payable</td>
                        <td class="py-2 text-right text-red-600">₹{{ number_format($gstr3b['net_tax_payable']['cgst'], 2) }}</td>
                        <td class="py-2 text-right text-red-600">₹{{ number_format($gstr3b['net_tax_payable']['sgst'], 2) }}</td>
                        <td class="py-2 text-right text-red-600">₹{{ number_format($gstr3b['net_tax_payable']['igst'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-center mb-4">
                <div class="text-sm text-gray-500">Total Net Tax Payable</div>
                <div class="text-2xl font-bold text-red-600">₹{{ number_format($gstr3b['net_tax_payable']['total'], 2) }}</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('gst.export.gstr3b.csv', ['period' => $period]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">📥 GSTR-3B CSV</a>
                <a href="{{ route('gst.export.gstr3b.json', ['period' => $period]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">📥 GSTR-3B JSON</a>
                <a href="{{ route('gst.export.purchase', ['period' => $period]) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm">📥 Purchase Register</a>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
        <h3 class="font-semibold mb-4">Export History</h3>
        @forelse($logs as $log)
        <div class="flex justify-between py-2 border-b dark:border-gray-700 last:border-0 text-sm">
            <span>{{ strtoupper(str_replace('_', '-', $log->return_type)) }} — {{ $log->period }}</span>
            <span class="text-gray-500">{{ $log->exported_at?->format('d M Y H:i') }}</span>
        </div>
        @empty
        <p class="text-gray-500 text-sm">No exports yet</p>
        @endforelse
    </div>

    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-sm text-yellow-800 dark:text-yellow-200">
        <strong>Note:</strong> Export files ko GST Portal par manually upload karein. Auto-filing Phase 4 me aayega.
        GSTIN: {{ auth()->user()->tenant?->gstin ?? 'Not configured' }}
    </div>
</div>
