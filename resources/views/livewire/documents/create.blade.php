<div>
    @include('layouts.partials.leads-nav')

    @php
        $accent = $theme_color ?? '#7c3aed';
        $currencySymbol = $currency === 'USD' ? '$' : '₹';
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('leads.documents') }}" class="text-sm" style="color: {{ $accent }}">← Back to Quotes</a>
            <h1 class="text-2xl font-bold mt-1">{{ $documentId ? 'Edit' : 'Create' }} {{ $typeLabels[$type] ?? 'Document' }}</h1>
            @if($lastNumber)<p class="text-xs text-gray-500">Last No: {{ $lastNumber }}</p>@endif
        </div>
        <button type="button" wire:click="save" class="px-5 py-2.5 rounded-lg text-white font-semibold text-sm" style="background: {{ $accent }}">
            Save & Continue
        </button>
    </div>

    {{-- Document Number --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4 mb-4 shadow-sm">
        <label class="text-xs font-medium text-gray-500 uppercase">Document Number</label>
        <div class="flex flex-wrap items-center gap-3 mt-1">
            <input type="text" wire:model="document_number" placeholder="e.g. PT00121 or leave blank for auto" class="flex-1 min-w-[200px] px-3 py-2 border rounded-lg text-sm font-mono dark:bg-gray-700 dark:border-gray-600">
            <span class="text-xs text-gray-500">Suggested: <button type="button" wire:click="$set('document_number', '{{ $suggestedNumber }}')" class="font-mono underline" style="color: {{ $accent }}">{{ $suggestedNumber }}</button></span>
        </div>
        @error('document_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    {{-- Type + GST + Template --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4 mb-4 shadow-sm">
        <div class="grid md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Document Type</label>
                <select wire:model.live="type" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                    <option value="quotation">Quotation</option>
                    <option value="proforma">Proforma Invoice</option>
                    <option value="invoice">Tax Invoice</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">GST Mode</label>
                <select wire:model.live="is_gst_applicable" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                    <option value="1">With GST</option>
                    <option value="0">Without GST</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Currency</label>
                <select wire:model.live="currency" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                    <option value="INR">Indian Rupee (INR, ₹)</option>
                    <option value="USD">US Dollar (USD, $)</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Project / Title</label>
                <input type="text" wire:model="title" placeholder="e.g. Taxi Booking" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
            </div>
        </div>

        @if($currency === 'USD')
        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <label class="text-xs font-medium text-gray-500 uppercase">Exchange Rate (1 USD = ? INR) *</label>
            <input type="number" step="0.000001" wire:model.live="exchange_rate" placeholder="e.g. 83.50" class="w-full max-w-xs mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
            @error('exchange_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        @endif

        {{-- Company Logo --}}
        <div class="mb-4 p-3 border rounded-lg dark:border-gray-600">
            <label class="text-xs font-medium text-gray-500 uppercase mb-2 block">Company Logo</label>
            <div class="flex flex-wrap items-center gap-4">
                @if($logo_path)
                <div class="relative">
                    <img src="{{ Storage::url($logo_path) }}" alt="Logo" class="h-16 w-auto object-contain border rounded">
                    <button type="button" wire:click="removeLogo" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs">✕</button>
                </div>
                @endif
                <label class="cursor-pointer px-4 py-2 border-2 border-dashed rounded-lg text-sm" style="border-color: {{ $accent }}; color: {{ $accent }}">
                    <input type="file" wire:model="logoUpload" accept="image/*" class="hidden">
                    {{ $logo_path ? 'Change Logo' : 'Upload Logo' }}
                </label>
                <div wire:loading wire:target="logoUpload" class="text-xs text-gray-500">Uploading...</div>
            </div>
        </div>

        <label class="text-xs font-medium text-gray-500 uppercase mb-2 block">Choose Format / Template</label>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
            @foreach($templates as $key => $tpl)
            <button type="button" wire:click="setTemplate('{{ $key }}')"
                class="rounded-lg border-2 p-3 text-left transition {{ $template_key === $key ? 'ring-2 ring-offset-1' : 'border-gray-200 dark:border-gray-600' }}"
                style="{{ $template_key === $key ? 'border-color:'.$tpl['color'].'; --tw-ring-color:'.$tpl['color'] : '' }}">
                <div class="h-2 rounded mb-2" style="background: {{ $tpl['color'] }}"></div>
                <div class="text-xs font-semibold">{{ $tpl['name'] }}</div>
            </button>
            @endforeach
        </div>
        <div class="mt-3 flex items-center gap-2">
            <label class="text-xs text-gray-500">Custom colour:</label>
            <input type="color" wire:model.live="theme_color" class="w-10 h-8 rounded cursor-pointer border-0">
            <span class="text-xs text-gray-400">{{ $theme_color }}</span>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            {{-- Dates --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4 grid md:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs text-gray-500">Issue Date *</label>
                    <input type="date" wire:model="issue_date" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="text-xs text-gray-500">Due Date</label>
                    <input type="date" wire:model="due_date" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                </div>
                @if($type === 'quotation')
                <div>
                    <label class="text-xs text-gray-500">Valid Until</label>
                    <input type="date" wire:model="valid_until" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                </div>
                @endif
            </div>

            {{-- From / To --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold text-sm">{{ $typeLabels[$type] ?? 'Document' }} From</h3>
                        <span class="text-xs" style="color: {{ $accent }}">Your Details</span>
                    </div>
                    <p class="text-sm font-medium">{{ auth()->user()->tenant->name }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->tenant->address }}, {{ auth()->user()->tenant->city }}, {{ auth()->user()->tenant->state }}</p>
                    @if($is_gst_applicable && auth()->user()->tenant->gstin)
                    <p class="text-xs mt-1">GSTIN: {{ auth()->user()->tenant->gstin }}</p>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold text-sm">{{ $typeLabels[$type] ?? 'Document' }} For</h3>
                        <span class="text-xs" style="color: {{ $accent }}">Client Details</span>
                    </div>
                    <select wire:model.live="customer_id" class="w-full mb-2 px-2 py-1.5 border rounded text-xs dark:bg-gray-700 dark:border-gray-600">
                        <option value="">— Manual entry / Link lead —</option>
                        @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                    <input type="text" wire:model="customer_name" placeholder="Client name *" class="w-full mb-2 px-2 py-1.5 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                    @error('customer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <textarea wire:model="customer_address" rows="2" placeholder="Address" class="w-full mb-2 px-2 py-1.5 border rounded text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" wire:model="customer_state" placeholder="State" class="px-2 py-1.5 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                        <input type="text" wire:model="customer_phone" placeholder="Phone" class="px-2 py-1.5 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    @if($is_gst_applicable)
                    <input type="text" wire:model="customer_gstin" placeholder="Client GSTIN" class="w-full mt-2 px-2 py-1.5 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                    @endif
                    <input type="email" wire:model="customer_email" placeholder="Email" class="w-full mt-2 px-2 py-1.5 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                    @if($lead_id)<p class="text-xs text-green-600 mt-2">✓ Linked to Lead #{{ $lead_id }}</p>@endif
                    @if($reference_document_id)<p class="text-xs text-indigo-600 mt-1">↳ Converting from document #{{ $reference_document_id }}</p>@endif
                </div>
            </div>

            {{-- Contact Details --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer mb-3">
                    <input type="checkbox" wire:model.live="showContactDetails" class="rounded"> Add Contact Details (Person / Phone / Email)
                </label>
                @if($showContactDetails)
                <div class="grid md:grid-cols-3 gap-3">
                    <input type="text" wire:model="contact_person" placeholder="Contact Person" class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                    <input type="text" wire:model="contact_phone" placeholder="Contact Phone" class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                    <input type="email" wire:model="contact_email" placeholder="Contact Email" class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                </div>
                @endif
            </div>

            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" wire:model.live="showShipping" class="rounded"> Add Shipping Details
            </label>
            @if($showShipping)
            <textarea wire:model="shipping_address" rows="2" placeholder="Shipping address..." class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
            @endif

            {{-- Line items --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 text-white text-sm font-semibold flex justify-between items-center" style="background: {{ $accent }}">
                    <span class="grid grid-cols-12 gap-2 flex-1">
                        <span class="col-span-5">Item</span>
                        <span class="col-span-1 text-center">Qty</span>
                        <span class="col-span-2 text-right">Rate</span>
                        <span class="col-span-2 text-right">Discount</span>
                        <span class="col-span-2 text-right">Amount</span>
                    </span>
                    <button type="button" wire:click="addGroup" class="ml-3 px-3 py-1 bg-white/20 rounded text-xs whitespace-nowrap">+ Add New Group</button>
                </div>

                <div class="p-4 space-y-4">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="🔍 Search & add products from catalog..." class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                        @if($productSearch && $products->count())
                        <div class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-lg max-h-40 overflow-y-auto">
                            @foreach($products as $product)
                            <button type="button" wire:click="addProduct({{ $product->id }})" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                                {{ $product->name }} — ₹{{ number_format($product->price, 2) }}
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    @foreach($items as $index => $item)
                    @php
                        $prevGroup = $index > 0 ? ($items[$index - 1]['group_name'] ?? '') : '';
                        $currentGroup = $item['group_name'] ?? '';
                        $gross = ($item['quantity'] ?? 0) * ($item['rate'] ?? 0);
                        $disc = ($item['discount_type'] ?? 'fixed') === 'percent'
                            ? $gross * (($item['discount_percent'] ?? 0) / 100)
                            : ($item['discount_amount'] ?? 0);
                        $lineAmt = max($gross - $disc, 0);
                    @endphp

                    @if($currentGroup && $currentGroup !== $prevGroup)
                    <div class="px-3 py-2 rounded-lg text-sm font-semibold text-white" style="background: {{ $accent }}80">
                        📁 {{ $currentGroup }}
                    </div>
                    @endif

                    <div class="border rounded-lg dark:border-gray-600" wire:key="item-{{ $index }}">
                        <div class="grid grid-cols-12 gap-2 p-3 items-center">
                            <div class="col-span-5 flex gap-2 items-start">
                                @if(!empty($item['image_path']))
                                <div class="relative shrink-0">
                                    <img src="{{ Storage::url($item['image_path']) }}" alt="" class="w-10 h-10 object-cover rounded border">
                                    <button type="button" wire:click="removeItemImage({{ $index }})" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-[10px]">✕</button>
                                </div>
                                @endif
                                <input type="text" wire:model="items.{{ $index }}.description" placeholder="Item name *" class="flex-1 px-2 py-1.5 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="col-span-1">
                                <input type="number" step="0.01" wire:model.live="items.{{ $index }}.quantity" class="w-full px-1 py-1.5 border rounded text-sm text-center dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="col-span-2">
                                <input type="number" step="0.01" wire:model.live="items.{{ $index }}.rate" class="w-full px-2 py-1.5 border rounded text-sm text-right dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="col-span-2 flex gap-1">
                                <select wire:model.live="items.{{ $index }}.discount_type" class="w-14 px-1 py-1.5 border rounded text-xs dark:bg-gray-700 dark:border-gray-600">
                                    <option value="fixed">₹</option>
                                    <option value="percent">%</option>
                                </select>
                                <input type="number" step="0.01" wire:model.live="items.{{ $index }}.{{ ($item['discount_type'] ?? 'fixed') === 'percent' ? 'discount_percent' : 'discount_amount' }}" class="flex-1 px-1 py-1.5 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="col-span-2 flex items-center justify-between">
                                <span class="text-sm font-semibold">{{ $currencySymbol }}{{ number_format($lineAmt, 2) }}</span>
                                <div class="flex gap-0.5">
                                    <button type="button" wire:click="moveItemUp({{ $index }})" class="text-gray-400 hover:text-gray-600 text-xs">↑</button>
                                    <button type="button" wire:click="moveItemDown({{ $index }})" class="text-gray-400 hover:text-gray-600 text-xs">↓</button>
                                    <button type="button" wire:click="duplicateItem({{ $index }})" class="text-gray-400 hover:text-gray-600 text-xs">⧉</button>
                                    <button type="button" wire:click="toggleItemExpanded({{ $index }})" class="text-xs" style="color: {{ $accent }}">✎</button>
                                    @if(count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 text-xs">✕</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($item['expanded'] ?? false)
                        <div class="px-3 pb-3 border-t dark:border-gray-600 pt-2">
                            {{-- Rich text description --}}
                            <div x-data="{ syncDesc() { $wire.set('items.{{ $index }}.long_description', $refs.editor.innerHTML) } }">
                                <label class="text-xs text-gray-500 mb-1 block">Detailed Description</label>
                                <div class="flex gap-1 mb-1">
                                    <button type="button" @click="document.execCommand('bold'); syncDesc()" class="px-2 py-1 border rounded text-xs font-bold dark:border-gray-600">B</button>
                                    <button type="button" @click="document.execCommand('italic'); syncDesc()" class="px-2 py-1 border rounded text-xs italic dark:border-gray-600">I</button>
                                    <button type="button" @click="document.execCommand('insertUnorderedList'); syncDesc()" class="px-2 py-1 border rounded text-xs dark:border-gray-600">• List</button>
                                </div>
                                <div x-ref="editor" contenteditable="true"
                                     @blur="syncDesc()"
                                     class="w-full min-h-[80px] px-2 py-2 border rounded text-sm dark:bg-gray-700 dark:border-gray-600 focus:outline-none"
                                     wire:ignore>{!! $item['long_description'] ?? '' !!}</div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 mt-2">
                                @if($is_gst_applicable)
                                <input type="text" wire:model="items.{{ $index }}.hsn_sac" placeholder="HSN/SAC" class="px-2 py-1 border rounded text-xs dark:bg-gray-700 dark:border-gray-600">
                                <input type="number" wire:model="items.{{ $index }}.gst_rate" placeholder="GST %" class="px-2 py-1 border rounded text-xs dark:bg-gray-700 dark:border-gray-600">
                                @endif
                                <input type="text" wire:model="items.{{ $index }}.unit" placeholder="Unit" class="px-2 py-1 border rounded text-xs dark:bg-gray-700 dark:border-gray-600">
                            </div>

                            {{-- Item image upload --}}
                            <div class="mt-2">
                                <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 border rounded text-xs dark:border-gray-600">
                                    <input type="file" wire:model="itemImageUploads.{{ $index }}" accept="image/*" class="hidden">
                                    📷 {{ !empty($item['image_path']) ? 'Change Image' : 'Add Item Image' }}
                                </label>
                                <span wire:loading wire:target="itemImageUploads.{{ $index }}" class="text-xs text-gray-500 ml-2">Uploading...</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach

                    <div class="flex gap-2">
                        <button type="button" wire:click="addItem" class="flex-1 py-2 border-2 border-dashed rounded-lg text-sm font-medium" style="border-color: {{ $accent }}; color: {{ $accent }}">
                            + Add New Line
                        </button>
                        <button type="button" wire:click="addGroup" class="px-4 py-2 border-2 border-dashed rounded-lg text-sm font-medium" style="border-color: {{ $accent }}; color: {{ $accent }}">
                            + Group
                        </button>
                    </div>
                </div>
            </div>

            {{-- Additional Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4">
                <label class="text-xs font-medium text-gray-500">Additional Info</label>
                <textarea wire:model="additional_info" rows="3" placeholder="Any extra information for the client..." class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
            </div>

            {{-- Attachments --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4">
                <label class="text-xs font-medium text-gray-500 uppercase mb-2 block">Attachments</label>
                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border-2 border-dashed rounded-lg text-sm" style="border-color: {{ $accent }}; color: {{ $accent }}">
                    <input type="file" wire:model="attachmentUploads" multiple class="hidden">
                    📎 Upload Files
                </label>
                <div wire:loading wire:target="attachmentUploads" class="text-xs text-gray-500 mt-1">Uploading...</div>
                @if(count($savedAttachments))
                <ul class="mt-3 space-y-1">
                    @foreach($savedAttachments as $ai => $att)
                    <li class="flex items-center justify-between text-sm px-2 py-1 bg-gray-50 dark:bg-gray-700 rounded">
                        <span>{{ $att['name'] ?? basename($att['path'] ?? '') }}</span>
                        <button type="button" wire:click="removeAttachment({{ $ai }})" class="text-red-500 text-xs">Remove</button>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

            {{-- Signature --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer mb-3">
                    <input type="checkbox" wire:model.live="showSignature" class="rounded"> Add Signature Block
                </label>
                @if($showSignature)
                <div class="grid md:grid-cols-2 gap-3">
                    <input type="text" wire:model="signature_name" placeholder="Signatory Name" class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                    <input type="text" wire:model="signature_title" placeholder="Designation / Title" class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                </div>
                @endif
            </div>

            {{-- Terms & Notes --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4 space-y-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">Terms & Conditions</label>
                    <textarea wire:model="terms_conditions" rows="4" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Notes</label>
                    <textarea wire:model="notes" rows="2" class="w-full mt-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>
            </div>

            {{-- Advanced --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4">
                <h3 class="font-semibold text-sm mb-3">Advanced Options</h3>
                <div class="grid md:grid-cols-2 gap-2 text-sm">
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="advanced_options.show_description_full_width" class="rounded"> Show description in full width</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="advanced_options.hide_place_of_supply" class="rounded"> Hide place of supply</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="advanced_options.show_tax_summary" class="rounded"> Show tax summary in PDF</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="advanced_options.show_sku" class="rounded"> Show SKU in line items</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="advanced_options.summarise_quantity" class="rounded"> Summarise quantity totals</label>
                </div>
            </div>
        </div>

        {{-- Totals sidebar --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4 sticky top-4 shadow-sm">
                <h3 class="font-semibold mb-3">Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Sub Total</span><span>{{ $currencySymbol }}{{ number_format($totals['subtotal'] ?? 0, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Line Discounts</span><span>{{ $currencySymbol }}{{ number_format(($totals['discount_amount'] ?? 0) - ($doc_discount_value && $doc_discount_type === 'fixed' ? $doc_discount_value : 0), 2) }}</span></div>
                    <div class="border-t pt-2 dark:border-gray-600">
                        <label class="text-xs text-gray-500">Document Discount</label>
                        <div class="flex gap-1 mt-1">
                            <select wire:model.live="doc_discount_type" class="w-16 px-1 py-1 border rounded text-xs dark:bg-gray-700 dark:border-gray-600">
                                <option value="percent">%</option>
                                <option value="fixed">₹</option>
                            </select>
                            <input type="number" step="0.01" wire:model.live="doc_discount_value" class="flex-1 px-2 py-1 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                        </div>
                    </div>
                    @if($is_gst_applicable && ($totals['total_tax'] ?? 0) > 0)
                    <div class="flex justify-between"><span class="text-gray-500">Tax (GST)</span><span>{{ $currencySymbol }}{{ number_format($totals['total_tax'], 2) }}</span></div>
                    @endif
                    @foreach($additional_charges as $ci => $charge)
                    <div class="flex gap-1 items-center">
                        <input type="text" wire:model="additional_charges.{{ $ci }}.label" class="flex-1 px-2 py-1 border rounded text-xs dark:bg-gray-700 dark:border-gray-600">
                        <input type="number" wire:model.live="additional_charges.{{ $ci }}.amount" class="w-20 px-2 py-1 border rounded text-xs dark:bg-gray-700 dark:border-gray-600">
                        <button type="button" wire:click="removeCharge({{ $ci }})" class="text-red-500 text-xs">✕</button>
                    </div>
                    @endforeach
                    <button type="button" wire:click="addCharge" class="text-xs" style="color: {{ $accent }}">+ Add Charge</button>
                    <div class="border-t pt-2 dark:border-gray-600 mt-2">
                        <label class="text-xs text-gray-500">USD Exchange Rate (optional)</label>
                        <input type="number" step="0.000001" wire:model.live="exchange_rate" placeholder="1 USD = ? INR" class="w-full mt-1 px-2 py-1 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div class="border-t pt-3 dark:border-gray-600 flex justify-between items-center">
                        <span class="font-bold">Total ({{ $currency }})</span>
                        <span class="text-xl font-bold" style="color: {{ $accent }}">{{ $currencySymbol }}{{ number_format($totals['grand_total'] ?? 0, 2) }}</span>
                    </div>
                    @if($exchange_rate && $exchange_rate > 0)
                    <div class="flex justify-between text-blue-600">
                        <span class="text-gray-500">USD Equivalent</span>
                        <span>${{ number_format(($totals['grand_total'] ?? 0) / $exchange_rate, 2) }}</span>
                    </div>
                    @endif
                    <p class="text-xs text-gray-500 italic">{{ $totals['total_in_words'] ?? '' }}</p>
                </div>
                <button type="button" wire:click="save" class="w-full mt-4 py-2.5 rounded-lg text-white font-semibold text-sm" style="background: {{ $accent }}">
                    Save & Continue
                </button>
            </div>
        </div>
    </div>
</div>
