<div>
    @include('layouts.partials.leads-nav')

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Lead Sources</h1>
        <p class="text-gray-500 text-sm">Auto-sync leads from 15+ sources — no Excel downloads needed</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($sources as $source)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700 flex flex-col" x-data="{ guide: false }" wire:key="source-{{ $source['key'] }}">
            <div class="flex justify-between items-start mb-2">
                <span class="text-2xl">{{ $source['icon'] }}</span>
                @if($source['status'] === 'connected')
                <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Connected
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ $source['status'] === 'disconnected' ? 'Disconnected' : 'Available' }}
                </span>
                @endif
            </div>

            <h3 class="font-semibold">{{ $source['name'] }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $source['desc'] }}</p>

            @if($source['status'] === 'connected')
            <p class="text-xs mt-1 {{ $source['sync_enabled'] ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                {{ $source['sync_enabled'] ? 'Sync ON' : 'Sync paused' }}
                @if($source['last_synced_at'])
                • last sync {{ \Illuminate\Support\Carbon::parse($source['last_synced_at'])->diffForHumans() }}
                @endif
            </p>
            @endif

            <button type="button" x-on:click="guide = !guide" class="mt-3 text-xs font-medium text-indigo-600 dark:text-indigo-400 flex items-center gap-1 self-start">
                <svg class="w-3 h-3 transition-transform" :class="guide ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                How to connect
            </button>

            <div x-show="guide" x-cloak class="mt-2 p-3 bg-gray-50 dark:bg-gray-900/40 rounded-lg text-xs space-y-2">
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-300">Kya chahiye (requirements):</p>
                    <ul class="list-disc ml-4 mt-1 text-gray-600 dark:text-gray-400 space-y-0.5">
                        @foreach($source['requirements'] as $req)
                        <li>{{ $req }}</li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-300">Steps:</p>
                    <ol class="list-decimal ml-4 mt-1 text-gray-600 dark:text-gray-400 space-y-0.5">
                        @foreach($source['steps'] as $step)
                        <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <div class="flex gap-2 mt-auto pt-3 items-center flex-wrap">
                @if($source['status'] === 'connected')
                <button wire:click="openManage('{{ $source['key'] }}')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">Manage</button>
                @if(!empty($source['route']))
                <a href="{{ route($source['route']) }}" class="px-3 py-1.5 border rounded-lg text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Open</a>
                @endif
                <button wire:click="disconnect('{{ $source['key'] }}')"
                        wire:confirm="Disconnect {{ $source['name'] }}? Lead sync band ho jayega."
                        class="px-3 py-1.5 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 dark:border-red-800 dark:hover:bg-red-900/20">
                    Disconnect
                </button>
                @else
                <button wire:click="openWizard('{{ $source['key'] }}')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">
                    {{ $source['status'] === 'disconnected' ? 'Reconnect' : 'Connect' }}
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl text-sm">
        <strong>API Webhook:</strong> POST leads to <code class="bg-white dark:bg-gray-800 px-2 py-0.5 rounded">{{ url('/api/leads/capture') }}</code>
        — see Integrations for full setup.
    </div>

    {{-- ── Connect wizard modal ────────────────────────────────────── --}}
    @if($wizard)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:key="wizard-{{ $wizard['key'] }}">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">{{ $wizard['icon'] }}</span>
                    <div>
                        <h3 class="font-bold text-lg">Connect {{ $wizard['name'] }}</h3>
                        <p class="text-xs text-gray-500">{{ $wizard['desc'] }}</p>
                    </div>
                </div>
                <button wire:click="closeWizard" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            {{-- Step indicator --}}
            <div class="flex items-center gap-2 mb-5 text-xs">
                @foreach([1 => 'Guide', 2 => 'Details', 3 => 'Done'] as $step => $label)
                <div class="flex items-center gap-1.5 {{ $wizardStep >= $step ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-400' }}">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] {{ $wizardStep >= $step ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                        {{ $wizardStep > $step ? '✓' : $step }}
                    </span>
                    {{ $label }}
                </div>
                @if($step < 3)
                <div class="flex-1 h-px {{ $wizardStep > $step ? 'bg-indigo-400' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                @endif
                @endforeach
            </div>

            @if($wizardStep === 1)
            <div class="space-y-4 text-sm">
                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                    <p class="font-semibold text-amber-800 dark:text-amber-300 text-xs mb-1.5">Connect karne se pehle yeh ready rakhein:</p>
                    <ul class="space-y-1">
                        @foreach($wizard['requirements'] as $req)
                        <li class="flex items-start gap-2 text-xs text-amber-800 dark:text-amber-200">
                            <span class="mt-0.5">☑</span> {{ $req }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-300 text-xs mb-1.5">{{ $wizard['name'] }} side par steps:</p>
                    <ol class="space-y-2">
                        @foreach($wizard['steps'] as $step)
                        <li class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <span class="w-5 h-5 shrink-0 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-semibold text-[10px]">{{ $loop->iteration }}</span>
                            <span class="pt-0.5">{{ $step }}</span>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button wire:click="closeWizard" class="px-4 py-2 border rounded-lg text-sm dark:border-gray-600">Cancel</button>
                <button wire:click="wizardNext" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">
                    Sab ready hai — Next &rarr;
                </button>
            </div>

            @elseif($wizardStep === 2)
            <div class="space-y-3">
                @forelse($wizard['fields'] as $field)
                <div>
                    <label class="text-xs text-gray-500">{{ $field['label'] }} {{ ($field['required'] ?? false) ? '*' : '' }}</label>
                    <input wire:model="configForm.{{ $field['key'] }}" placeholder="{{ $field['placeholder'] ?? '' }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                </div>
                @empty
                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-xs text-green-700 dark:text-green-300">
                    Koi API key nahi chahiye — bas niche diya webhook URL {{ $wizard['name'] }} me paste karein.
                </div>
                @endforelse

                <div class="p-3 bg-gray-50 dark:bg-gray-900/40 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Aapka webhook URL ({{ $wizard['name'] }} me paste karein):</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 text-xs bg-white dark:bg-gray-800 px-2 py-1.5 rounded border dark:border-gray-700 break-all">{{ $wizard['webhook_url'] }}</code>
                        <button type="button" x-data="{ copied: false }" data-url="{{ $wizard['webhook_url'] }}"
                                x-on:click="navigator.clipboard.writeText($el.dataset.url); copied = true; setTimeout(() => copied = false, 1500)"
                                class="px-2 py-1.5 border rounded-lg text-xs shrink-0 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
                                x-text="copied ? 'Copied!' : 'Copy'">Copy</button>
                    </div>
                </div>

                @if($configError)
                <p class="text-red-500 text-xs">{{ $configError }}</p>
                @endif
            </div>
            <div class="flex gap-2 mt-6">
                <button wire:click="wizardBack" class="px-4 py-2 border rounded-lg text-sm dark:border-gray-600">&larr; Back</button>
                <button wire:click="wizardConnect" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">
                    <span wire:loading.remove wire:target="wizardConnect">Connect {{ $wizard['name'] }}</span>
                    <span wire:loading wire:target="wizardConnect">Connecting…</span>
                </button>
            </div>

            @else
            <div class="text-center py-4">
                <div class="w-14 h-14 mx-auto rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center text-3xl mb-3">✅</div>
                <h4 class="font-bold text-lg">{{ $wizard['name'] }} Connected!</h4>
                <p class="text-sm text-gray-500 mt-1">Auto-sync ON hai — naye leads apne aap CRM me aayenge.</p>
            </div>
            <div class="flex gap-2 mt-4">
                <button wire:click="closeWizard" class="px-4 py-2 border rounded-lg text-sm dark:border-gray-600">Done</button>
                <button wire:click="manageFromWizard" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">Open Manage Panel &rarr;</button>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Manage panel modal ──────────────────────────────────────── --}}
    @if($manage)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:key="manage-{{ $manage['key'] }}">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">{{ $manage['icon'] }}</span>
                    <div>
                        <h3 class="font-bold text-lg">{{ $manage['name'] }}</h3>
                        <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Connected
                        </span>
                    </div>
                </div>
                <button wire:click="closeManage" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <div class="space-y-4">
                {{-- Sync toggle + last sync --}}
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/40 rounded-lg">
                    <div>
                        <p class="text-sm font-medium">Auto Sync</p>
                        <p class="text-xs text-gray-500">
                            Last sync:
                            {{ $manage['last_synced_at'] ? \Illuminate\Support\Carbon::parse($manage['last_synced_at'])->diffForHumans() : 'kabhi nahi (never)' }}
                            @if($manage['connected_at'])
                            • connected {{ \Illuminate\Support\Carbon::parse($manage['connected_at'])->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    <button wire:click="toggleSync('{{ $manage['key'] }}')"
                            class="relative w-11 h-6 rounded-full transition-colors {{ $manage['sync_enabled'] ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                        <span class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-all {{ $manage['sync_enabled'] ? 'left-[22px]' : 'left-0.5' }}"></span>
                    </button>
                </div>

                {{-- Webhook URL --}}
                <div class="p-3 bg-gray-50 dark:bg-gray-900/40 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Webhook URL ({{ $manage['name'] }} side par paste karein):</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 text-xs bg-white dark:bg-gray-800 px-2 py-1.5 rounded border dark:border-gray-700 break-all">{{ $manage['webhook_url'] }}</code>
                        <button type="button" x-data="{ copied: false }" data-url="{{ $manage['webhook_url'] }}"
                                x-on:click="navigator.clipboard.writeText($el.dataset.url); copied = true; setTimeout(() => copied = false, 1500)"
                                class="px-2 py-1.5 border rounded-lg text-xs shrink-0 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
                                x-text="copied ? 'Copied!' : 'Copy'">Copy</button>
                    </div>
                </div>

                {{-- Connection config --}}
                @if(count($manage['fields']) > 0)
                <div class="p-3 border dark:border-gray-700 rounded-lg space-y-3">
                    <p class="text-sm font-medium">Connection Config</p>
                    @foreach($manage['fields'] as $field)
                    <div>
                        <label class="text-xs text-gray-500">{{ $field['label'] }} {{ ($field['required'] ?? false) ? '*' : '' }}</label>
                        <input wire:model="configForm.{{ $field['key'] }}" placeholder="{{ $field['placeholder'] ?? '' }}"
                               class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    @endforeach
                    @if($configError)
                    <p class="text-red-500 text-xs">{{ $configError }}</p>
                    @endif
                    <button wire:click="saveConfig" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">
                        <span wire:loading.remove wire:target="saveConfig">Save Config</span>
                        <span wire:loading wire:target="saveConfig">Saving…</span>
                    </button>
                </div>
                @endif

                {{-- Test lead --}}
                <div class="p-3 border dark:border-gray-700 rounded-lg">
                    <p class="text-sm font-medium">Test Connection</p>
                    <p class="text-xs text-gray-500 mt-0.5 mb-2">Ek test lead create hoga — check karein ki sab sahi chal raha hai.</p>
                    <button wire:click="sendTestLead('{{ $manage['key'] }}')"
                            class="px-3 py-1.5 border border-indigo-200 text-indigo-600 dark:border-indigo-800 dark:text-indigo-400 rounded-lg text-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                        <span wire:loading.remove wire:target="sendTestLead">Send Test Lead</span>
                        <span wire:loading wire:target="sendTestLead">Sending…</span>
                    </button>
                    @if($lastTestLeadId)
                    <div class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 rounded-lg text-xs text-green-700 dark:text-green-300 flex items-center justify-between gap-2">
                        <span>✓ Test lead created!</span>
                        <a href="{{ route('leads.show', $lastTestLeadId) }}" class="font-semibold underline">View Lead &rarr;</a>
                    </div>
                    @endif
                </div>

                @if(!empty($manage['route']))
                <a href="{{ route($manage['route']) }}" class="block text-center px-4 py-2 border rounded-lg text-sm dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Open {{ $manage['name'] }} page &rarr;
                </a>
                @endif

                {{-- Danger zone --}}
                <div class="p-3 border border-red-200 dark:border-red-900/50 rounded-lg flex items-center justify-between gap-2">
                    <div>
                        <p class="text-sm font-medium text-red-600">Disconnect</p>
                        <p class="text-xs text-gray-500">Sync band ho jayega. Config saved rahegi — reconnect ek click me.</p>
                    </div>
                    <button wire:click="disconnect('{{ $manage['key'] }}')"
                            wire:confirm="Disconnect {{ $manage['name'] }}? Lead sync band ho jayega."
                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm shrink-0">
                        Disconnect
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
