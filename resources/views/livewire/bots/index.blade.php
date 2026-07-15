<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">WhatsApp Bots</h1><p class="text-gray-500 text-sm">Auto-reply & conversation flows</p></div>
        <div class="flex gap-2">
            <button wire:click="$toggle('showGuide')" class="px-3 py-2 border rounded-lg text-sm {{ $showGuide ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-400 text-indigo-700 dark:text-indigo-300' : '' }}">📖 How bots work</button>
            <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New Bot</button>
        </div>
    </div>

    {{-- How bots work guide --}}
    @if($showGuide)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-lg">📖 How bots work — full guide</h2>
            <button wire:click="$set('showGuide', false)" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="font-semibold text-sm mb-1">1️⃣ Keyword matching</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Har bot ka ek trigger keyword hota hai (jaise "hi", "price", "order"). Jab lead ke inbound WhatsApp message me woh keyword aata hai (kahin bhi, case-insensitive), bot activate ho jata hai.</p>
            </div>
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="font-semibold text-sm mb-1">2️⃣ Flow node by node</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Builder me flow upar se neeche chalta hai: Start → Message → Question/Input → Message. Har node ek step hai. Abhi bot keyword match par flow ka pehla "Send Message" node bhejta hai; poora step-by-step conversation WhatsApp API connect hone ke baad enable hoga.</p>
            </div>
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="font-semibold text-sm mb-1">3️⃣ Activate karna</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Bot card par "Activate" dabao ya Builder me Activate button use karo. Sirf active (Live) bots hi reply karte hain. Draft bots safe hote hain — edit karte raho, koi reply nahi jayega.</p>
            </div>
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="font-semibold text-sm mb-1">4️⃣ Test kaise kare</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Inbox kholo, kisi conversation me test/simulate message bhejo jisme bot ka keyword ho — bot ka auto-reply usi chat me dikhega aur lead ki timeline me "Bot auto-reply" entry ban jayegi. Builder ka "Preview Bot" bhi messages dikhata hai.</p>
            </div>
        </div>
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
            <div class="font-semibold text-sm mb-1 text-amber-800 dark:text-amber-300">⚠️ Limitations — honest note</div>
            <p class="text-xs text-amber-700 dark:text-amber-200">Abhi WhatsApp connection simulated hai — jab tak official WhatsApp Business API configure nahi hoti (Integrations page se), bots real customer phones par message nahi bhejte. Test replies Inbox ke andar simulate hoti hain. Input nodes (text/email/phone) abhi flow design ke liye hain; live data capture API connect hone ke baad kaam karega. Ek message me multiple bots ka keyword match ho toh pehla active bot reply karta hai.</p>
        </div>
    </div>
    @endif

    {{-- Bot Templates gallery --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 mb-6">
        <button wire:click="$toggle('showTemplates')" class="w-full flex justify-between items-center px-6 py-4 text-left">
            <div>
                <h2 class="font-bold text-lg">🤖 Bot Templates</h2>
                <p class="text-gray-500 text-sm">Ready-made bot flows — install karo, Builder me customise karo</p>
            </div>
            <span class="text-gray-400 text-sm">{{ $showTemplates ? '▲ Hide' : '▼ Show' }}</span>
        </button>
        @if($showTemplates)
        <div class="px-6 pb-6 grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($templates as $t)
            @php $installedBot = $installed->get($t['name']); @endphp
            <div class="border dark:border-gray-700 rounded-xl p-4 flex flex-col {{ $installedBot ? 'bg-green-50/50 dark:bg-green-900/10 border-green-200 dark:border-green-800' : 'hover:border-indigo-400 dark:hover:border-indigo-500' }} transition">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xl">{{ $t['icon'] }}</span>
                    @if($installedBot)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">Installed ✓</span>
                    @endif
                </div>
                <h3 class="font-semibold text-sm">{{ $t['name'] }}</h3>
                <p class="text-xs text-gray-500 mt-1 flex-1">{{ $t['desc'] }}</p>
                <div class="mt-2 text-xs">
                    <span class="text-indigo-600 dark:text-indigo-400">Keyword: "{{ $t['keyword'] }}"</span>
                    <span class="text-gray-400 ml-2">{{ count($t['nodes']) - 1 }} steps</span>
                </div>
                <div class="mt-3">
                    @if($installedBot)
                    <a href="{{ route('leads.bots.builder', $installedBot) }}" class="block text-center w-full py-1.5 border border-indigo-300 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs">Open in Builder</a>
                    @else
                    <button wire:click="installTemplate('{{ $t['key'] }}')" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium">Install</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($bots as $bot)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700">
            <div class="flex justify-between items-start mb-2">
                <span class="text-2xl">🤖</span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $bot->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100' }}">{{ $bot->is_active ? 'Live' : 'Draft' }}</span>
            </div>
            <h3 class="font-semibold">{{ $bot->name }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $bot->description }}</p>
            @if($bot->trigger_keyword)<p class="text-xs text-indigo-600 mt-2">Keyword: {{ $bot->trigger_keyword }}</p>@endif
            <p class="text-xs text-gray-400 mt-1">{{ $bot->sessions_count }} sessions</p>
            <div class="flex gap-2 mt-4">
                <a href="{{ route('leads.bots.builder', $bot) }}" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Edit Flow</a>
                <button wire:click="toggleActive({{ $bot->id }})" class="px-3 py-1 border rounded text-sm">{{ $bot->is_active ? 'Deactivate' : 'Activate' }}</button>
                <button wire:click="delete({{ $bot->id }})" wire:confirm="Delete?" class="text-red-600 text-sm">Delete</button>
            </div>
        </div>
        @endforeach
    </div>
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-3">
            <h3 class="font-bold">New WhatsApp Bot</h3>
            <input wire:model="name" placeholder="Bot name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <textarea wire:model="description" rows="2" placeholder="Description" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            <input wire:model="triggerKeyword" placeholder="Trigger keyword (optional)" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <div class="flex gap-2"><button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Create & Build</button><button wire:click="$set('showModal', false)" class="flex-1 py-2 border rounded-lg">Cancel</button></div>
        </div>
    </div>
    @endif
</div>
