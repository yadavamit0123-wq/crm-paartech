<div>
    <div class="mb-6">
        <a href="{{ route('expenses.index') }}" class="text-indigo-600 text-sm">← Back</a>
        <h1 class="text-2xl font-bold mt-2">Add Expense / खर्च दर्ज करें</h1>
    </div>

    <form wire:submit="save" class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Vendor Name *</label>
                        <input type="text" wire:model="vendor_name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        @error('vendor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Vendor GSTIN</label>
                        <input type="text" wire:model="vendor_gstin" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Vendor State</label>
                        <input type="text" wire:model.live="vendor_state" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Karnataka">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Invoice Number</label>
                        <input type="text" wire:model="invoice_number" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Invoice Date *</label>
                        <input type="date" wire:model="invoice_date" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Category</label>
                        <select wire:model="category" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                            @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea wire:model="description" rows="2" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Taxable Amount (₹) *</label>
                        <input type="number" wire:model.live="taxable_amount" step="0.01" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">GST Rate (%)</label>
                        <input type="number" wire:model.live="gst_rate" step="0.01" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" {{ !$is_gst_applicable ? 'disabled' : '' }}>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="is_gst_applicable" class="rounded">
                            <span class="text-sm">GST Applicable</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Payment Status</label>
                        <select wire:model="payment_status" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Payment Method</label>
                        <select wire:model="payment_method" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                            <option value="bank">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Bill Upload</label>
                        <input type="file" wire:model="billFile" accept="image/*,.pdf" class="text-sm">
                    </div>
                </div>
            </div>

            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Save Expense</button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 h-fit sticky top-4">
            <h3 class="font-semibold mb-4">GST Preview</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Taxable</span><span>₹{{ number_format($preview['taxable_amount'], 2) }}</span></div>
                @if($is_gst_applicable)
                <div class="flex justify-between"><span>CGST</span><span>₹{{ number_format($preview['cgst_amount'], 2) }}</span></div>
                <div class="flex justify-between"><span>SGST</span><span>₹{{ number_format($preview['sgst_amount'], 2) }}</span></div>
                <div class="flex justify-between"><span>IGST</span><span>₹{{ number_format($preview['igst_amount'], 2) }}</span></div>
                @endif
                <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2"><span>Total</span><span>₹{{ number_format($preview['total_amount'], 2) }}</span></div>
            </div>
        </div>
    </form>
</div>
