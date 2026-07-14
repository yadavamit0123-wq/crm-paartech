<div>
    @include('layouts.partials.leads-nav')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Quotes & Documents</h1>
            <p class="text-gray-500 text-sm">Quotation → Proforma → Invoice workflow</p>
        </div>
        @if(auth()->user()->hasPermission('documents.create'))
        <div class="flex gap-2">
            <a href="{{ route('leads.documents.create', ['type' => 'quotation']) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Quotation</a>
            <a href="{{ route('leads.documents.create', ['type' => 'proforma']) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm">+ Proforma</a>
            <a href="{{ route('leads.documents.create', ['type' => 'invoice']) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">+ Invoice</a>
        </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 mb-4 shadow-sm border dark:border-gray-700 flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm flex-1 min-w-[180px]">
        <select wire:model.live="filterType" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="">All Types</option>
            <option value="quotation">Quotation</option>
            <option value="proforma">Proforma</option>
            <option value="invoice">Invoice</option>
        </select>
        <select wire:model.live="filterStatus" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="">All Status</option>
            @foreach(['draft','sent','accepted','paid','cancelled'] as $s)
            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Number</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">GST</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Workflow</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($documents as $doc)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-medium">{{ $doc->document_number }}</td>
                    <td class="px-4 py-3">{{ $doc->typeLabel() }}</td>
                    <td class="px-4 py-3">{{ $doc->customer_name }}</td>
                    <td class="px-4 py-3">{{ $doc->is_gst_applicable ? 'Yes' : 'No' }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $doc->currency === 'USD' ? '$' : '₹' }}{{ number_format($doc->grand_total, 2) }}</td>
                    @php
                        $docStatusColors = ['draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300', 'sent' => 'bg-blue-100 text-blue-700', 'accepted' => 'bg-indigo-100 text-indigo-700', 'paid' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-red-100 text-red-700'];
                    @endphp
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $docStatusColors[$doc->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($doc->status) }}</span></td>
                    <td class="px-4 py-3">{{ $doc->issue_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if($doc->lead_id) Lead @endif
                        @if($doc->referenceDocument) ← {{ $doc->referenceDocument->document_number }} @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('leads.documents.show', $doc) }}" class="text-indigo-600 hover:underline">View</a>
                        @if(auth()->user()->hasPermission('documents.create'))
                        <a href="{{ route('leads.documents.edit', $doc) }}" class="text-violet-600 hover:underline ml-2">Edit</a>
                        @endif
                        <a href="{{ route('leads.documents.pdf', $doc) }}" target="_blank" class="text-red-600 hover:underline ml-2">PDF</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No documents yet</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $documents->links() }}</div>
    </div>
</div>
