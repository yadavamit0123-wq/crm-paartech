<div>
    @include('layouts.partials.leads-nav')

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Lead Sources</h1>
        <p class="text-gray-500 text-sm">Auto-sync leads from 15+ sources — no Excel downloads needed</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($sources as $source)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700">
            <div class="flex justify-between items-start mb-2">
                <span class="text-2xl">{{ $source['icon'] }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $source['status'] === 'connected' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($source['status']) }}
                </span>
            </div>
            <h3 class="font-semibold">{{ $source['name'] }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $source['desc'] }}</p>
            @if($source['status'] !== 'connected')
            <button wire:click="connect('{{ $source['name'] }}')" class="mt-3 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm">Connect</button>
            @else
            <div class="flex gap-2 mt-3 items-center">
                <span class="text-green-600 text-sm">✓</span>
                <button wire:click="testSync('{{ $source['name'] }}')" class="px-3 py-1.5 border rounded-lg text-sm hover:bg-gray-50">Manage</button>
                @if(!empty($source['route']))
                <a href="{{ route($source['route']) }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm">Open</a>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl text-sm">
        <strong>API Webhook:</strong> POST leads to <code class="bg-white dark:bg-gray-800 px-2 py-0.5 rounded">{{ url('/api/leads/capture') }}</code>
        — see Integrations for full setup.
    </div>
</div>
