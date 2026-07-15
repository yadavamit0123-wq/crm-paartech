<div x-data="{ view: 'pdf' }">
    @include('layouts.partials.leads-nav')
    <div class="mb-4 flex flex-wrap justify-between items-start gap-4">
        <div>
            <a href="{{ route('leads.documents') }}" class="text-sm font-medium" style="color: {{ $accent }}">← Back to Quotes</a>
            <div class="flex items-center gap-3 mt-1.5">
                <h1 class="text-2xl font-bold">{{ $document->typeLabel() }}</h1>
                @php
                    $statusColors = ['draft' => 'bg-gray-100 text-gray-700', 'sent' => 'bg-blue-100 text-blue-700', 'accepted' => 'bg-indigo-100 text-indigo-700', 'paid' => 'bg-emerald-100 text-emerald-700'];
                @endphp
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$document->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($document->status) }}</span>
            </div>
            <p class="text-gray-500 text-sm mt-0.5">{{ $document->document_number }} • {{ $document->customer_name }}@if($document->title) • <span style="color: {{ $accent }}">{{ $document->title }}</span>@endif</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('leads.documents.download', $document) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-white rounded-lg text-sm font-medium shadow-sm" style="background: {{ $accent }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download PDF
            </a>
            @if(auth()->user()->hasPermission('documents.create'))
            <a href="{{ route('leads.documents.edit', $document) }}" class="px-4 py-2 border rounded-lg text-sm font-medium dark:border-gray-600" style="border-color: {{ $accent }}; color: {{ $accent }}">Edit</a>
            @endif
            <button wire:click="openEmailModal" class="px-4 py-2 border rounded-lg text-sm font-medium dark:border-gray-600">Email</button>
            <button wire:click="openWhatsAppModal" class="px-4 py-2 border rounded-lg text-sm font-medium dark:border-gray-600 text-green-600">WhatsApp</button>

            @if($canConvertProforma)
            <button wire:click="convertTo('proforma')" class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium">→ Proforma</button>
            @endif
            @if($canConvertInvoice)
            <button wire:click="convertTo('invoice')" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium">→ Tax Invoice</button>
            @endif

            <div x-data="{ more: false }" class="relative">
                <button @click="more = !more" class="px-3 py-2 border rounded-lg text-sm font-medium dark:border-gray-600">More ▾</button>
                <div x-show="more" x-cloak @click.away="more = false" class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl shadow-xl py-1.5 z-40">
                    <a href="{{ route('leads.documents.pdf', $document) }}" target="_blank" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Open PDF in new tab</a>
                    @if(auth()->user()->hasPermission('documents.create'))
                    <button wire:click="duplicate" @click="more = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Duplicate</button>
                    @endif
                    @if($document->status === 'draft')
                    <button wire:click="markSent" @click="more = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Mark as Sent</button>
                    @endif
                    @if(in_array($document->status, ['sent','draft']) && $document->type === 'quotation')
                    <button wire:click="markAccepted" @click="more = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Mark as Accepted</button>
                    @endif
                    @if(in_array($document->status, ['sent', 'accepted']))
                    <button wire:click="markPaid" @click="more = false" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Mark as Paid</button>
                    @endif
                    @if(auth()->user()->hasPermission('documents.create'))
                    <div class="border-t dark:border-gray-700 my-1"></div>
                    <button wire:click="deleteDocument" wire:confirm="Delete this document?" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Delete</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Workflow trail --}}
    @if(count($document->workflowSteps()))
    <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4 mb-4">
        <h3 class="font-semibold text-sm mb-3">Document Workflow</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($document->workflowSteps() as $step)
            <span class="px-3 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-700">
                @if(($step['action'] ?? '') === 'from_lead') Lead #{{ $step['lead_id'] ?? '' }}
                @elseif(($step['action'] ?? '') === 'from_document') From {{ ucfirst($step['document_type'] ?? '') }} {{ $step['document_number'] ?? '' }}
                @elseif(($step['action'] ?? '') === 'converted_to') {{ ucfirst($step['from_type'] ?? '') }} → {{ ucfirst($step['to_type'] ?? '') }}
                @elseif(($step['action'] ?? '') === 'duplicated_from') Duplicated from {{ $step['from_number'] ?? '' }}
                @elseif(($step['action'] ?? '') === 'created') Created {{ ucfirst($step['type'] ?? $document->type) }}
                @else {{ ucfirst($step['action'] ?? 'step') }}
                @endif
            </span>
            @endforeach
        </div>
        @if($document->referenceDocument)
        <p class="text-xs text-gray-500 mt-2">Converted from: <a href="{{ route('leads.documents.show', $document->referenceDocument) }}" class="underline">{{ $document->referenceDocument->document_number }}</a></p>
        @endif
        @if($document->childDocuments->count())
        <p class="text-xs text-gray-500 mt-1">Child documents:
            @foreach($document->childDocuments as $child)
            <a href="{{ route('leads.documents.show', $child) }}" class="underline ml-1">{{ $child->document_number }} ({{ $child->typeLabel() }})</a>
            @endforeach
        </p>
        @endif
    </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            {{-- View toggle: exact PDF (same as download) vs details --}}
            <div class="flex items-center gap-1 mb-3 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-1 w-fit">
                <button @click="view = 'pdf'" :class="view === 'pdf' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300'" class="px-4 py-1.5 rounded-md text-sm font-medium transition">PDF Preview</button>
                <button @click="view = 'details'" :class="view === 'details' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300'" class="px-4 py-1.5 rounded-md text-sm font-medium transition">Details</button>
            </div>

            {{-- Preview: margin sirf is box ke andar — PDF/A4 par alag margin nahi --}}
            <div x-show="view === 'pdf'" class="bg-gray-100 dark:bg-gray-900 rounded-xl border dark:border-gray-700 overflow-hidden">
                <div style="padding: 20mm;">
                    <div class="mx-auto max-w-4xl bg-white shadow-md rounded-sm overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700">
                        <iframe src="{{ route('leads.documents.pdf', $document) }}#toolbar=0&view=FitH" class="w-full bg-white block" style="height: 72vh; border: 0;" title="PDF Preview"></iframe>
                    </div>
                </div>
            </div>

            <div x-show="view === 'details'" x-cloak class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <div class="flex justify-between mb-4">
                <div class="flex items-center gap-3">
                    @if($document->logo_path)
                    <img src="{{ asset('storage/'.$document->logo_path) }}" alt="Logo" class="h-12 w-auto object-contain">
                    @endif
                    <span class="px-3 py-1 rounded-full text-sm {{ $document->is_gst_applicable ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $document->is_gst_applicable ? 'With GST' : 'Without GST' }}
                    </span>
                </div>
                <span class="px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700">{{ ucfirst($document->status) }}</span>
            </div>

            @php $prevGroup = ''; @endphp
            <table class="w-full text-sm mb-4">
                <thead><tr class="text-white" style="background: {{ $accent }}">
                    <th class="py-2 px-2 text-left rounded-tl-lg">#</th>
                    <th class="py-2 px-2 text-left">Item</th>
                    @if($document->is_gst_applicable)<th class="py-2 px-2">HSN</th>@endif
                    <th class="py-2 px-2 text-right">Qty</th><th class="py-2 px-2 text-right">Rate</th><th class="py-2 px-2 text-right rounded-tr-lg">Amount</th>
                </tr></thead>
                <tbody>
                    @foreach($document->items as $i => $item)
                    @if($item->group_name && $item->group_name !== $prevGroup)
                    <tr><td colspan="{{ $document->is_gst_applicable ? 6 : 5 }}" class="py-2 px-2 font-semibold text-white" style="background: {{ $accent }}80">📁 {{ $item->group_name }}</td></tr>
                    @php $prevGroup = $item->group_name; @endphp
                    @endif
                    <tr class="border-b dark:border-gray-700 align-top">
                        <td class="py-2 px-2">{{ $i+1 }}</td>
                        <td class="py-2 px-2">
                            <div class="flex gap-2 items-start">
                                @if($item->image_path)
                                <img src="{{ asset('storage/'.$item->image_path) }}" alt="" class="w-10 h-10 object-cover rounded border shrink-0">
                                @endif
                                <div>
                                    <div class="font-medium">{{ $item->description }}</div>
                                    @if($item->long_description)<div class="text-xs text-gray-500 mt-1 prose prose-sm max-w-none">{!! $item->long_description !!}</div>@endif
                                </div>
                            </div>
                        </td>
                        @if($document->is_gst_applicable)<td class="py-2 px-2">{{ $item->hsn_sac }}</td>@endif
                        <td class="py-2 px-2 text-right">{{ $item->quantity }} {{ $item->unit }}</td>
                        <td class="py-2 px-2 text-right">{{ $docSymbol }}{{ number_format($item->rate, 2) }}</td>
                        <td class="py-2 px-2 text-right font-medium">{{ $docSymbol }}{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-right space-y-1 text-sm">
                <div>Subtotal: {{ $docSymbol }}{{ number_format($document->subtotal, 2) }}</div>
                @if($document->discount_amount > 0)<div>Discount: {{ $docSymbol }}{{ number_format($document->discount_amount, 2) }}</div>@endif
                <div>Taxable: {{ $docSymbol }}{{ number_format($document->taxable_amount, 2) }}</div>
                @if($document->is_gst_applicable && $document->total_tax > 0)
                <div>Tax: {{ $docSymbol }}{{ number_format($document->total_tax, 2) }}</div>
                @endif
                <div class="text-xl font-bold" style="color: {{ $accent }}">Grand Total: {{ $docSymbol }}{{ number_format($document->grand_total, 2) }}</div>
                @if($convertedTotal)<div class="text-blue-600">{{ $convertedLabel }}: {{ $convertedSymbol }}{{ number_format($convertedTotal, 2) }} (Rate: {{ $document->exchange_rate }})</div>@endif
                @if($document->total_in_words)<p class="text-xs text-gray-500 italic">{{ $document->total_in_words }}</p>@endif
            </div>

            @if($document->additional_info)
            <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-sm">
                <strong>Additional Info:</strong> {{ $document->additional_info }}
            </div>
            @endif

            @if($document->signature_data)
            <div class="mt-6 pt-4 border-t dark:border-gray-600">
                <p class="text-sm font-semibold">{{ $document->signature_data['name'] ?? '' }}</p>
                <p class="text-xs text-gray-500">{{ $document->signature_data['title'] ?? '' }}</p>
                <p class="text-xs text-gray-400 mt-2">Authorized Signatory</p>
            </div>
            @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-sm">
                <h3 class="font-semibold mb-2">Details</h3>
                <p><strong>Issue:</strong> {{ $document->issue_date->format('d M Y') }}</p>
                @if($document->due_date)<p><strong>Due:</strong> {{ $document->due_date->format('d M Y') }}</p>@endif
                @if($document->valid_until)<p><strong>Valid Until:</strong> {{ $document->valid_until->format('d M Y') }}</p>@endif
                @if($document->customer_gstin)<p><strong>GSTIN:</strong> {{ $document->customer_gstin }}</p>@endif
                @if($document->customer_phone)<p><strong>Phone:</strong> {{ $document->customer_phone }}</p>@endif
                @if($document->customer_email)<p><strong>Email:</strong> {{ $document->customer_email }}</p>@endif
                <p><strong>Template:</strong> {{ config('document-templates.'.$document->template_key.'.name', $document->template_key) }}</p>
                @if($document->currency && $document->currency !== 'INR')<p><strong>Currency:</strong> {{ $document->currency }}</p>@endif
            </div>

            @if($document->contact_details)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-sm">
                <h3 class="font-semibold mb-2">Contact Details</h3>
                @if($document->contact_details['person'] ?? null)<p><strong>Person:</strong> {{ $document->contact_details['person'] }}</p>@endif
                @if($document->contact_details['phone'] ?? null)<p><strong>Phone:</strong> {{ $document->contact_details['phone'] }}</p>@endif
                @if($document->contact_details['email'] ?? null)<p><strong>Email:</strong> {{ $document->contact_details['email'] }}</p>@endif
            </div>
            @endif

            @if($document->attachments && count($document->attachments))
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-sm">
                <h3 class="font-semibold mb-2">Attachments</h3>
                <ul class="space-y-1">
                    @foreach($document->attachments as $att)
                    <li>
                        <a href="{{ Storage::url($att['path'] ?? '') }}" target="_blank" class="text-indigo-600 hover:underline">
                            📎 {{ $att['name'] ?? basename($att['path'] ?? 'file') }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($document->lead)
            <a href="{{ route('leads.show', $document->lead) }}" class="block rounded-xl p-4 text-sm hover:underline" style="background: {{ $accent }}15; color: {{ $accent }}">
                View Lead: {{ $document->lead->name }} →
            </a>
            @endif
            @if($document->customer)
            <a href="{{ route('leads.customers.show', $document->customer) }}" class="block bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4 text-indigo-600 text-sm hover:underline">View Customer →</a>
            @endif
            @if($document->terms_conditions)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border dark:border-gray-700 text-xs whitespace-pre-line">{{ $document->terms_conditions }}</div>
            @endif
        </div>
    </div>

    {{-- Email Modal --}}
    @if($showEmailModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeEmailModal">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-lg shadow-xl mx-4">
            <h3 class="font-semibold text-lg mb-4">Send via Email</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500">To</label>
                    <input type="email" wire:model="emailTo" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                    @error('emailTo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500">Subject</label>
                    <input type="text" wire:model="emailSubject" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="text-xs text-gray-500">Message</label>
                    <textarea wire:model="emailMessage" rows="5" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="closeEmailModal" class="px-4 py-2 border rounded-lg text-sm dark:border-gray-600">Cancel</button>
                <button wire:click="sendEmail" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Send Email</button>
            </div>
        </div>
    </div>
    @endif

    {{-- WhatsApp Modal --}}
    @if($showWhatsAppModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeWhatsAppModal">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md shadow-xl mx-4">
            <h3 class="font-semibold text-lg mb-4">Send via WhatsApp</h3>
            <div>
                <label class="text-xs text-gray-500">Phone (with country code)</label>
                <input type="text" wire:model="whatsappPhone" placeholder="919887766555" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                @error('whatsappPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="closeWhatsAppModal" class="px-4 py-2 border rounded-lg text-sm dark:border-gray-600">Cancel</button>
                <button wire:click="sendWhatsApp" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Open WhatsApp</button>
            </div>
        </div>
    </div>
    @endif
</div>
