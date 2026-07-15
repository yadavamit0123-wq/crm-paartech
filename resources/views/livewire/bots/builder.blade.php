<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-4">
        <div>
            <a href="{{ route('leads.bots') }}" class="text-indigo-600 text-sm">← Back to Bots</a>
            <h1 class="text-2xl font-bold mt-1">{{ $bot->name }} — Bot Builder</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <label class="flex items-center gap-2 text-sm px-3 py-2 border rounded-lg cursor-pointer">
                <input type="checkbox" wire:model="newLeadsOnly" class="rounded"> New Leads Only
            </label>
            <button wire:click="$toggle('showGuide')" class="px-3 py-2 border rounded-lg text-sm {{ $showGuide ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-400 text-indigo-700 dark:text-indigo-300' : '' }}">📖 How it works</button>
            <button wire:click="$toggle('showPreview')" class="px-3 py-2 border rounded-lg text-sm">Preview Bot</button>
            <button wire:click="saveFlow" class="px-4 py-2 border rounded-lg text-sm">Save Bot</button>
            <button wire:click="toggleActive" class="px-4 py-2 {{ $bot->is_active ? 'bg-amber-500' : 'bg-green-600' }} text-white rounded-lg text-sm">
                {{ $bot->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </div>
    </div>

    {{-- How bots work guide --}}
    @if($showGuide)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 p-5 mb-4">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold">📖 How this bot works</h3>
            <button wire:click="$set('showGuide', false)" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <ol class="text-sm text-gray-600 dark:text-gray-300 space-y-2 list-decimal list-inside">
            <li><span class="font-medium">Keyword match:</span> lead ke inbound WhatsApp message me trigger keyword aate hi bot fire hota hai (case-insensitive, message me kahin bhi ho).</li>
            <li><span class="font-medium">Flow node by node:</span> yahan canvas par flow upar se neeche chalta hai — Start ke baad har node ek step hai. Left palette se node choose karo, label + content likho, "+ Add Node" dabao; naya node automatically pichle node se connect ho jata hai.</li>
            <li><span class="font-medium">Activate:</span> upar "Activate" button dabao — sirf active bot hi reply karta hai. Save Bot se changes store hote hain bina activate kiye.</li>
            <li><span class="font-medium">Test:</span> Inbox me jaake koi test message bhejo jisme keyword ho — bot ka reply usi chat me aayega aur lead timeline me "Bot auto-reply" entry banegi. "Preview Bot" se messages yahin dekh sakte ho.</li>
            <li><span class="font-medium">Limitation (honest):</span> jab tak WhatsApp Business API configure nahi hoti, connection simulated hai — bot keyword match par flow ka pehla Send Message node bhejta hai, aur input nodes (text/email/phone) design-only hain. API connect hone par poora step-by-step flow live hoga.</li>
        </ol>
    </div>
    @endif

    <div class="grid lg:grid-cols-4 gap-4">
        {{-- Node Palette --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-3 text-sm">Node Palette</h3>
            <div class="space-y-1 max-h-[400px] overflow-y-auto">
                @foreach($nodeTypes as $k => $v)
                <button wire:click="$set('newNodeType', '{{ $k }}')" class="w-full text-left px-3 py-2 rounded-lg text-xs {{ $newNodeType === $k ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }}">{{ $v }}</button>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t dark:border-gray-600">
                <input wire:model="newNodeLabel" placeholder="Node label" class="w-full px-2 py-1.5 border rounded text-sm mb-2 dark:bg-gray-700 dark:border-gray-600">
                <textarea wire:model="newNodeText" rows="2" placeholder="Content" class="w-full px-2 py-1.5 border rounded text-sm mb-2 dark:bg-gray-700 dark:border-gray-600"></textarea>
                <button wire:click="addNode" class="w-full py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add Node</button>
            </div>
        </div>

        {{-- Flow Canvas --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 min-h-[500px]">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold">Visual Flow</h3>
            </div>

            {{-- START node --}}
            <div class="mb-4 p-4 rounded-xl border-2 border-green-400 bg-green-50 dark:bg-green-900/20">
                <div class="text-xs font-bold text-green-700 uppercase">START</div>
                <div class="text-sm mt-1">Trigger: 
                    <select wire:model="triggerType" class="text-xs border rounded px-2 py-0.5 dark:bg-gray-700">
                        <option value="keyword">Keyword Reply</option>
                        <option value="button">Button Reply</option>
                        <option value="any">Any Message</option>
                    </select>
                </div>
                @if($bot->trigger_keyword)<div class="text-xs text-gray-500 mt-1">Keyword: "{{ $bot->trigger_keyword }}"</div>@endif
            </div>

            <div class="space-y-2">
                @foreach($nodes as $i => $node)
                @if($node['type'] !== 'start')
                <div class="flex items-center gap-2">
                    <div class="text-gray-400 text-xs">↓</div>
                    <div class="flex-1 p-3 rounded-xl border-2 border-indigo-300 bg-indigo-50 dark:bg-indigo-900/20">
                        <div class="text-xs font-medium text-indigo-600 uppercase">{{ $nodeTypes[$node['type']] ?? $node['type'] }}</div>
                        <div class="font-semibold text-sm">{{ $node['label'] }}</div>
                        @if(!empty($node['text']))<p class="text-xs text-gray-600 mt-1">{{ $node['text'] }}</p>@endif
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Side Panel --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
                <h3 class="font-semibold text-sm mb-3">Field Mapping</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between"><span>Name</span><span class="text-indigo-600">lead.name</span></div>
                    <div class="flex justify-between"><span>Phone</span><span class="text-indigo-600">lead.phone</span></div>
                    <div class="flex justify-between"><span>Email</span><span class="text-indigo-600">lead.email</span></div>
                </div>
            </div>

            @if($showPreview)
            <div class="bg-[#e5ddd5] rounded-xl p-4">
                <h3 class="font-semibold mb-3 text-sm">WhatsApp Preview</h3>
                @foreach($nodes as $node)
                    @if($node['type'] !== 'start' && !empty($node['text']))
                    <div class="bg-white rounded-lg p-3 mb-2 text-sm shadow max-w-[90%]">{{ $node['text'] }}</div>
                    @endif
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
