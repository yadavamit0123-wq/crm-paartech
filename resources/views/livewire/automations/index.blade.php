<div>
    @include('layouts.partials.leads-nav')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div><h1 class="text-2xl font-bold">Automation</h1><p class="text-gray-500 text-sm">Trigger-based workflows & drip sequences</p></div>
        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search rules..." class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
            <button wire:click="$toggle('showGuide')" class="px-3 py-2 border rounded-lg text-sm {{ $showGuide ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-400 text-indigo-700 dark:text-indigo-300' : '' }}">📖 How it works</button>
            <button class="px-3 py-2 border rounded-lg text-sm" onclick="window.open('https://www.youtube.com/results?search_query=crm+automation', '_blank')">❓ Help</button>
            <button class="px-3 py-2 border rounded-lg text-sm" onclick="window.open('https://www.youtube.com/results?search_query=3sigma+crm+automation', '_blank')">🎬 Video</button>
            <button wire:click="$refresh" class="px-3 py-2 border rounded-lg text-sm">🔄 Refresh</button>
            <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New rule</button>
        </div>
    </div>

    {{-- How automation works — full guide --}}
    @if($showGuide)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-lg">📖 How automation works — full guide</h2>
            <button wire:click="$set('showGuide', false)" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="grid md:grid-cols-5 gap-3 mb-6">
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="text-lg mb-1">1️⃣</div>
                <div class="font-semibold text-sm mb-1">Trigger kya hai?</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Trigger = woh event jo automation ko start karta hai. Jaise "New lead created" — jab bhi naya lead aata hai, rule turant chal jata hai. Aapko kuch manually karna nahi padta.</p>
            </div>
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="text-lg mb-1">2️⃣</div>
                <div class="font-semibold text-sm mb-1">Action kya hoga?</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Trigger fire hone par system action perform karta hai — WhatsApp message, email, ya follow-up task. Message me @{{name}} likho toh lead ka naam automatically replace ho jata hai.</p>
            </div>
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="text-lg mb-1">3️⃣</div>
                <div class="font-semibold text-sm mb-1">Enable / Disable</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Table me Status column dekho — Active matlab rule chal raha hai. "Pause" dabao toh rule ruk jata hai (delete nahi hota). Wapas "Activate" karo, dobara chalu.</p>
            </div>
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="text-lg mb-1">4️⃣</div>
                <div class="font-semibold text-sm mb-1">Test kaise kare?</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Har rule ke saamne "Run Now" button hai — woh aapke latest lead par rule chala deta hai. Ya ek dummy test lead create karo, "New lead created" wale rules apne aap fire honge.</p>
            </div>
            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                <div class="text-lg mb-1">5️⃣</div>
                <div class="font-semibold text-sm mb-1">Verify in timeline</div>
                <p class="text-xs text-gray-600 dark:text-gray-300">Lead kholo → Activity Timeline dekho. Har automation action wahan "Automation: WhatsApp sent" jaise entry ke saath dikhta hai. Yahan table me Runs / Completed count bhi badhta hai.</p>
            </div>
        </div>
        <h3 class="font-semibold text-sm mb-2">Trigger reference — kaunsa trigger kab fire hota hai</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-xs border dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 uppercase">
                    <tr>
                        <th class="px-3 py-2 text-left">Trigger</th>
                        <th class="px-3 py-2 text-left">Kab chalta hai</th>
                        <th class="px-3 py-2 text-left">Best use</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    <tr><td class="px-3 py-2 font-medium">New lead created</td><td class="px-3 py-2">Jaise hi naya lead system me aata hai (manual add, import, form — kahin se bhi). Instantly fire hota hai.</td><td class="px-3 py-2">Welcome message, intro email, first-call task</td></tr>
                    <tr><td class="px-3 py-2 font-medium">Status updated</td><td class="px-3 py-2">Jab lead ka stage/status change hota hai (pipeline me move karne par). Instantly fire hota hai.</td><td class="px-3 py-2">Next-step message, won/lost follow-up</td></tr>
                    <tr><td class="px-3 py-2 font-medium">No call in 24 hours</td><td class="px-3 py-2">Scheduled check: agar lead create hue 24+ ghante ho gaye aur koi call log nahi hui. Background me periodically chalta hai.</td><td class="px-3 py-2">Follow-up reminder, re-engagement message</td></tr>
                    <tr><td class="px-3 py-2 font-medium">Task overdue</td><td class="px-3 py-2">Scheduled check: jab lead ka koi pending task due date cross kar leta hai.</td><td class="px-3 py-2">Escalation task, manager alert</td></tr>
                    <tr><td class="px-3 py-2 font-medium">WhatsApp message received</td><td class="px-3 py-2">Jab lead ka inbound WhatsApp message aata hai aur bot reply match hota hai (Inbox me).</td><td class="px-3 py-2">Instant acknowledgment, reply task</td></tr>
                    <tr><td class="px-3 py-2 font-medium">Lead edited / Label updated / List added / Time-based</td><td class="px-3 py-2">Yeh triggers abhi limited hain — rule ban jayega, lekin auto-fire tabhi hoga jab yeh events system me enable ho. Abhi ke liye "Run Now" se manually chala sakte ho.</td><td class="px-3 py-2">Advanced workflows (coming soon)</td></tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-3">💡 Pro tip: Pehle Recipe Library se 2–3 recipes install karo, ek test lead banao, phir lead ki timeline me result verify karo. Automation samajhne ka sabse fast tareeka yahi hai.</p>
    </div>
    @endif

    {{-- Recipe Library --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 mb-6">
        <button wire:click="$toggle('showRecipes')" class="w-full flex justify-between items-center px-6 py-4 text-left">
            <div>
                <h2 class="font-bold text-lg">⚡ Recipe Library</h2>
                <p class="text-gray-500 text-sm">One-click prebuilt automations — install karo aur leads autopilot par chalao</p>
            </div>
            <span class="text-gray-400 text-sm">{{ $showRecipes ? '▲ Hide' : '▼ Show' }}</span>
        </button>
        @if($showRecipes)
        <div class="px-6 pb-6 grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($recipes as $recipe)
            @php $installedRow = $installed->get($recipe['name']); @endphp
            <div class="border dark:border-gray-700 rounded-xl p-4 flex flex-col {{ $installedRow ? 'bg-green-50/50 dark:bg-green-900/10 border-green-200 dark:border-green-800' : 'hover:border-indigo-400 dark:hover:border-indigo-500' }} transition">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xl">{{ $recipe['icon'] }}</span>
                    @if($installedRow)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">Installed ✓</span>
                    @endif
                </div>
                <h3 class="font-semibold text-sm">{{ $recipe['name'] }}</h3>
                <p class="text-xs text-gray-500 mt-1 flex-1">{{ $recipe['desc'] }}</p>
                <div class="mt-3 space-y-1">
                    <div class="text-xs"><span class="font-medium text-gray-400 uppercase">Trigger:</span> <span class="text-indigo-600 dark:text-indigo-400">{{ $triggers[$recipe['trigger']] ?? $recipe['trigger'] }}</span></div>
                    <div class="text-xs">
                        <span class="font-medium text-gray-400 uppercase">Actions:</span>
                        @foreach($recipe['actions'] as $act)
                        <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 mr-1">{{ str_replace('_', ' ', $act['type']) }}</span>
                        @endforeach
                        @if(count($recipe['day_actions']) > 1)
                        <span class="inline-block px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">{{ count($recipe['day_actions']) }}-day drip</span>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    @if($installedRow)
                    <button wire:click="toggleActive({{ $installedRow->id }})" class="w-full py-1.5 border rounded-lg text-xs {{ $installedRow->is_active ? 'text-amber-600 border-amber-300' : 'text-green-600 border-green-300' }}">
                        {{ $installedRow->is_active ? 'Pause' : 'Activate' }}
                    </button>
                    @else
                    <button wire:click="installRecipe('{{ $recipe['key'] }}')" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium">Install</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Rule Name</th>
                    <th class="px-4 py-3 text-left">Last Run</th>
                    <th class="px-4 py-3 text-left">No of Runs</th>
                    <th class="px-4 py-3 text-left">Completed</th>
                    <th class="px-4 py-3 text-left">Errors</th>
                    <th class="px-4 py-3 text-left">Leads</th>
                    <th class="px-4 py-3 text-left">Worked</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($automations as $a)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="px-4 py-3 font-medium">{{ $a->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $a->last_run_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="px-4 py-3">{{ $a->runs_count }}</td>
                    <td class="px-4 py-3 text-green-600">{{ $a->completed_count ?? 0 }}</td>
                    <td class="px-4 py-3 text-red-600">{{ $a->error_count ?? 0 }}</td>
                    <td class="px-4 py-3">{{ $a->leads_affected ?? 0 }}</td>
                    <td class="px-4 py-3">{{ $a->completed_count ?? 0 }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $a->is_active ? 'bg-green-100 text-green-700' : ($a->is_draft ? 'bg-amber-100 text-amber-700' : 'bg-gray-100') }}">
                            {{ $a->is_draft ? 'Draft' : ($a->is_active ? 'Active' : 'Paused') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button wire:click="runNow({{ $a->id }})" class="text-green-600 text-sm">Run Now</button>
                            <button wire:click="edit({{ $a->id }})" class="text-indigo-600 text-sm">Edit</button>
                            <button wire:click="toggleActive({{ $a->id }})" class="text-sm">{{ $a->is_active ? 'Pause' : 'Activate' }}</button>
                            <button wire:click="delete({{ $a->id }})" wire:confirm="Delete?" class="text-red-600 text-sm">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-gray-500">No automation rules yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showWizard)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            @if($wizardStep === 1)
            <h3 class="font-bold text-lg mb-4">Choose Automation Trigger</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach($triggers as $k => $v)
                <button wire:click="selectTrigger('{{ $k }}')" class="p-4 border rounded-xl text-left hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                    <div class="font-medium text-sm">{{ $v }}</div>
                </button>
                @endforeach
            </div>
            @elseif($wizardStep === 2)
            <h3 class="font-bold text-lg mb-4">Choose Action</h3>
            @foreach($actionGroups as $group => $actions)
            <div class="mb-4">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $group }}</div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($actions as $k => $v)
                    <button wire:click="selectAction('{{ $k }}')" class="p-3 border rounded-lg text-left text-sm hover:border-indigo-500">{{ $v }}</button>
                    @endforeach
                </div>
            </div>
            @endforeach
            @else
            <h3 class="font-bold text-lg mb-4">Day-based Workflow Builder</h3>
            <input wire:model="name" placeholder="Rule name *" class="w-full px-3 py-2 border rounded-lg mb-4 dark:bg-gray-700 dark:border-gray-600">
            @foreach($dayActions as $i => $day)
            <div class="border rounded-xl p-4 mb-3 dark:border-gray-600">
                <div class="font-semibold text-sm mb-2">Day {{ $day['day'] }} actions</div>
                <textarea wire:model="dayActions.{{ $i }}.message" rows="2" placeholder="Action message for day {{ $day['day'] }}" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600"></textarea>
            </div>
            @endforeach
            <button wire:click="addDay" class="text-sm text-indigo-600 mb-4">+ day delay → ADD DAY</button>
            <textarea wire:model="actionMessage" rows="2" placeholder="Default action message" class="w-full px-3 py-2 border rounded-lg mb-4 dark:bg-gray-700 dark:border-gray-600"></textarea>
            <div class="flex gap-2">
                <button wire:click="save(true)" class="px-4 py-2 border rounded-lg text-sm">Save as Draft</button>
                <button wire:click="save(false)" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Preview & Create</button>
                <button wire:click="$set('showWizard', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
            </div>
            @endif
            @if($wizardStep < 3)
            <button wire:click="$set('showWizard', false)" class="mt-4 text-sm text-gray-500">Cancel</button>
            @endif
        </div>
    </div>
    @endif
</div>
