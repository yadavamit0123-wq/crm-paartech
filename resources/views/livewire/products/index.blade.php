<div>
    @include('layouts.partials.leads-nav')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Products ({{ $products->count() }} items)</h1>
            <p class="text-gray-500 text-sm">Product catalog for orders, quotes & invoices</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search products..." class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
            <button wire:click="openImport" class="px-3 py-2 border rounded-lg text-sm">📤 Bulk Import</button>
            <select wire:model.live="filterCategory" class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                <option value="">Filter</option>
                @foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
            </select>
            <button wire:click="$refresh" class="px-3 py-2 border rounded-lg text-sm">🔄 Refresh</button>
            <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add Product</button>
        </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($products as $p)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden hover:shadow-md transition">
            <div class="h-36 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                @if($p->image_path)
                <img src="{{ asset('storage/'.$p->image_path) }}" alt="{{ $p->name }}" class="h-full w-full object-cover">
                @else
                <span class="text-4xl text-gray-400">📦</span>
                @endif
            </div>
            <div class="p-4">
                <h3 class="font-semibold">{{ $p->name }}</h3>
                @if($p->category)<p class="text-xs text-gray-500 mt-0.5">{{ $p->category }}</p>@endif
                <p class="text-lg font-bold text-indigo-600 mt-2">₹{{ number_format($p->price, 2) }}</p>
                <p class="text-xs text-gray-500">per {{ $p->unit ?? 'Nos' }} • GST {{ $p->tax_rate }}%</p>
                <div class="flex gap-2 mt-3">
                    <button wire:click="edit({{ $p->id }})" class="text-sm text-indigo-600">Edit</button>
                    <button wire:click="delete({{ $p->id }})" wire:confirm="Delete?" class="text-sm text-red-600">Delete</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-lg space-y-3 max-h-[90vh] overflow-y-auto">
            <h3 class="font-bold">{{ $editId ? 'Edit' : 'Add' }} Product</h3>
            <input wire:model="name" placeholder="Product name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="category" placeholder="Category" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="unit" placeholder="Unit (Nos, Hrs...)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="sku" placeholder="SKU" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="price" type="number" placeholder="Price ₹" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="taxRate" placeholder="Tax %" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="hsnSac" placeholder="HSN/SAC" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <textarea wire:model="description" rows="2" placeholder="Description" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            <input type="file" wire:model="image" accept="image/*" class="text-sm">
            <div class="flex gap-2"><button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Save</button><button wire:click="$set('showModal', false)" class="flex-1 py-2 border rounded-lg">Cancel</button></div>
        </div>
    </div>
    @endif

    @if($showImportModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-lg space-y-3 max-h-[90vh] overflow-y-auto">
            <h3 class="font-bold">Bulk Import Products</h3>
            <p class="text-sm text-gray-500">Upload a CSV with headers: <code class="text-xs">name, sku, price, gst_rate, hsn_sac, category, unit, description</code></p>
            <input type="file" wire:model="importFile" accept=".csv,.txt" class="text-sm">
            @error('importFile')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            @if($importedCount > 0)
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-sm text-green-700 dark:text-green-400">{{ $importedCount }} products imported</div>
            @endif
            @if(count($importErrors))
            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg max-h-40 overflow-y-auto">
                @foreach($importErrors as $err)
                <p class="text-xs text-red-600 dark:text-red-400">{{ $err }}</p>
                @endforeach
            </div>
            @endif
            <div class="flex gap-2">
                <button wire:click="import" wire:loading.attr="disabled" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Import</button>
                <button wire:click="$set('showImportModal', false)" class="flex-1 py-2 border rounded-lg">Close</button>
            </div>
        </div>
    </div>
    @endif
</div>
