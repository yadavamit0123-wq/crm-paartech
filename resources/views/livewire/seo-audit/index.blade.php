<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">SEO Audit / SEO जांच</h1>
        <p class="text-gray-500 text-sm">Enter website URL for instant SEO analysis</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" wire:model="url" placeholder="https://yourwebsite.com" class="flex-1 px-4 py-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <button wire:click="runAudit" wire:loading.attr="disabled" class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold disabled:opacity-50">
                <span wire:loading.remove wire:target="runAudit">🔍 Run Audit</span>
                <span wire:loading wire:target="runAudit">Analyzing...</span>
            </button>
        </div>
    </div>

    @if($latestAudit)
    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 text-center">
            <div class="text-5xl font-bold mb-2 {{ $latestAudit->score >= 80 ? 'text-green-500' : ($latestAudit->score >= 50 ? 'text-yellow-500' : 'text-red-500') }}">{{ $latestAudit->score }}</div>
            <div class="text-gray-500">SEO Score / 100</div>
            <div class="text-xs text-gray-400 mt-2">{{ $latestAudit->url }}</div>
            <div class="text-xs text-gray-400">{{ $latestAudit->created_at->format('d M Y H:i') }}</div>
        </div>
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">Checks</h3>
            <div class="space-y-2">
                @foreach($latestAudit->checks ?? [] as $check)
                <div class="flex items-center justify-between p-2 rounded {{ $check['passed'] ? 'bg-green-50 dark:bg-green-900/10' : 'bg-red-50 dark:bg-red-900/10' }}">
                    <div class="flex items-center gap-2">
                        <span>{{ $check['passed'] ? '✅' : '❌' }}</span>
                        <span class="text-sm font-medium">{{ $check['name'] }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ $check['detail'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @if(count($latestAudit->recommendations ?? []) > 0)
    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-6 border border-yellow-200 dark:border-yellow-800 mb-6">
        <h3 class="font-semibold mb-3 text-yellow-800 dark:text-yellow-200">Recommendations / सुझाव</h3>
        <ul class="space-y-2">
            @foreach($latestAudit->recommendations as $rec)
            <li class="text-sm text-yellow-900 dark:text-yellow-100 flex gap-2"><span>→</span>{{ $rec }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    @endif

    @if($history->count())
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
        <h3 class="font-semibold mb-4">Audit History</h3>
        @foreach($history as $audit)
        <button wire:click="loadAudit({{ $audit->id }})" class="w-full flex justify-between py-2 border-b dark:border-gray-700 last:border-0 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 text-left px-2 rounded">
            <span>{{ $audit->url }}</span>
            <span class="font-bold {{ $audit->score >= 80 ? 'text-green-600' : ($audit->score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $audit->score }}/100</span>
        </button>
        @endforeach
    </div>
    @endif
</div>
