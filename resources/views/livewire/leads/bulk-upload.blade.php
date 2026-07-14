<div>
    @include('layouts.partials.leads-nav')
    <div class="mb-6">
        <a href="{{ route('leads.list') }}" class="text-indigo-600 text-sm">← Back to Leads</a>
        <h1 class="text-2xl font-bold mt-2">Bulk Upload Leads / बल्क अपलोड</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 max-w-2xl">
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm">
            <p class="font-semibold mb-2">CSV Format (headers required):</p>
            <code class="text-xs">name, email, phone, company, source, city, state, priority</code>
            <p class="mt-2 text-gray-500">Only <strong>name</strong> is required.</p>
        </div>

        <input type="file" wire:model="csvFile" accept=".csv,.txt" class="mb-4">
        @error('csvFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <div wire:loading wire:target="csvFile,processUpload" class="text-sm text-gray-500 mb-4">Processing...</div>

        @if($csvFile)
        <button wire:click="processUpload" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Upload & Import</button>
        @endif

        @if($imported > 0)
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="font-semibold text-green-700 dark:text-green-300">✅ {{ $imported }} leads imported successfully!</p>
        </div>
        @endif

        @if(count($importErrors) > 0)
        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <p class="font-semibold text-red-700 mb-2">Errors:</p>
            @foreach($importErrors as $error)
            <p class="text-sm text-red-600">{{ $error }}</p>
            @endforeach
        </div>
        @endif
    </div>
</div>
