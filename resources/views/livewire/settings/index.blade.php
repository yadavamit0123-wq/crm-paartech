<div>
    @include('layouts.partials.leads-nav')
    <h1 class="text-2xl font-bold mb-6">Organisation Settings</h1>
    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-4">
            <h3 class="font-semibold">Company Profile</h3>
            <input wire:model="orgName" placeholder="Organisation name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="email" placeholder="Email" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="phone" placeholder="Phone" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <input wire:model="gstin" placeholder="GSTIN" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <textarea wire:model="address" rows="2" placeholder="Address" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="city" placeholder="City" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="state" placeholder="State" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
        </div>
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3">
                <h3 class="font-semibold">CRM Controls</h3>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="duplicateCheck"> Duplicate lead check</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="callLogRestrict"> Restrict call log visibility</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="leadEditLock"> Lead edit lock after assign</label>
                <input wire:model="leadPrefix" placeholder="Lead ID prefix" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3">
                <h3 class="font-semibold">WhatsApp Settings</h3>
                <input wire:model="whatsappNumber" placeholder="WhatsApp Business Number" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <div class="space-y-2">
                    @foreach($whatsappTemplates as $i => $tpl)
                    <div class="flex gap-2 text-sm"><span class="flex-1 p-2 bg-gray-50 dark:bg-gray-700 rounded">{{ $tpl }}</span><button wire:click="removeTemplate({{ $i }})" class="text-red-600">×</button></div>
                    @endforeach
                    <div class="flex gap-2"><input wire:model="newTemplate" placeholder="Add template" class="flex-1 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm"><button wire:click="addTemplate" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm">Add</button></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifications + Meeting Settings --}}
    <div class="grid lg:grid-cols-2 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-4">
            <h3 class="font-semibold">🔔 Notifications</h3>
            <label class="flex items-center justify-between gap-3 cursor-pointer">
                <span class="text-sm">
                    <span class="font-medium block">Follow-up alarm sound</span>
                    <span class="text-xs text-gray-500">Follow-up due hone par continuous ring bajegi (banner hamesha dikhega, sound sirf on hone par)</span>
                </span>
                <input type="checkbox" wire:model="followupSound" class="sr-only peer">
                <span class="shrink-0 w-11 h-6 rounded-full bg-gray-300 dark:bg-gray-600 peer-checked:bg-indigo-600 relative transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
            </label>
            <p class="text-xs text-gray-400">Note: browser pehli user click ke baad hi sound allow karta hai (autoplay policy).</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">📅 Meeting Settings</h3>
                <button wire:click="resetMeetingTemplates" class="text-xs text-indigo-600 hover:underline">Reset defaults</button>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="px-2 py-1 rounded-full {{ ($meetingStatus['google_meet']['mode'] ?? '') === 'live' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800' }}">
                    Google Meet: {{ $meetingStatus['google_meet']['label'] ?? 'Free Test Mode' }}
                </span>
                <span class="px-2 py-1 rounded-full {{ ($meetingStatus['zoom']['mode'] ?? '') === 'live' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800' }}">
                    Zoom: {{ $meetingStatus['zoom']['label'] ?? 'Free Test Mode' }}
                </span>
            </div>
            <p class="text-xs text-gray-500">Abhi <strong>Free Test Mode</strong> chal raha hai — meeting link CRM khud generate karega. Live API chahiye toh neeche credentials paste karke Save karo.</p>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Zoom personal meeting link (test fallback / optional)</label>
                <input wire:model="zoomPersonalLink" placeholder="https://zoom.us/j/1234567890" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            </div>
            <p class="text-xs text-gray-500">Invite templates — placeholders: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{name}</code> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{date}</code> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{time}</code> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{link}</code> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{company}</code></p>
            <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                @foreach($templateLabels as $key => $label)
                <div>
                    <label class="text-xs text-gray-500 block mb-1">{{ $label }}</label>
                    @if(str_ends_with($key, '_subject'))
                    <input wire:model="meetingTemplates.{{ $key }}" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                    @else
                    <textarea wire:model="meetingTemplates.{{ $key }}" rows="4" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm"></textarea>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Meeting API Credentials (Live) --}}
    <div class="grid lg:grid-cols-2 gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold">🔵 Zoom API Credentials (Live)</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($meetingStatus['zoom']['mode'] ?? '') === 'live' ? 'bg-green-100 text-green-700' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}">
                    {{ ($meetingStatus['zoom']['mode'] ?? '') === 'live' ? 'CONNECTED' : 'EMPTY — Test Mode' }}
                </span>
            </div>
            <p class="text-xs text-gray-500">Zoom Marketplace → Build App → <strong>Server-to-Server OAuth</strong>. Scope: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">meeting:write:admin</code>. Teeno fields bharte hi Live Zoom meetings banegi.</p>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Account ID</label>
                <input wire:model="zoomAccountId" placeholder="Zoom Account ID" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Client ID</label>
                <input wire:model="zoomClientId" placeholder="Zoom Client ID" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Client Secret</label>
                <input type="password" wire:model="zoomClientSecret" placeholder="Zoom Client Secret" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono" autocomplete="new-password">
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold">🟢 Google Meet / Calendar Credentials (Live)</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($meetingStatus['google_meet']['mode'] ?? '') === 'live' ? 'bg-green-100 text-green-700' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}">
                    {{ ($meetingStatus['google_meet']['mode'] ?? '') === 'live' ? 'CONNECTED' : 'EMPTY — Test Mode' }}
                </span>
            </div>
            <p class="text-xs text-gray-500">Google Cloud Console → enable <strong>Google Calendar API</strong> → OAuth Client (Web). Phir OAuth Playground / app se Refresh Token generate karke yahan paste karo. Teeno fields bharte hi Live Meet link Calendar se banegi.</p>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Client ID</label>
                <input wire:model="googleClientId" placeholder="xxxx.apps.googleusercontent.com" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Client Secret</label>
                <input type="password" wire:model="googleClientSecret" placeholder="Google Client Secret" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono" autocomplete="new-password">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Refresh Token</label>
                <textarea wire:model="googleRefreshToken" rows="2" placeholder="1//0gxxxxxxxx..." class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono"></textarea>
            </div>
            <p class="text-[11px] text-gray-400">Tip: credentials empty chhodoge toh Free Test Mode automatically chalti rahegi — CRM meeting link khud generate karega.</p>
        </div>
    </div>

    {{-- Manage Demos --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mt-6">
        <h3 class="font-semibold mb-1">🖥️ Manage Demos</h3>
        <p class="text-xs text-gray-500 mb-4">Demo templates banayein — lead ko WhatsApp/Email se ek click me demo link bhejein. Message me <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{name}</code> lead ke naam se aur <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{link}</code> demo URL se replace hoga.</p>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="space-y-3">
                <input wire:model="demoName" placeholder="Demo name (e.g. CRM Product Demo) *" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                @error('demoName') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                <input wire:model="demoUrl" placeholder="Demo link URL (https://...) *" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                @error('demoUrl') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                <textarea wire:model="demoMessage" rows="4" placeholder="Message template — e.g. Hello {name}, humara product demo yahan dekhein: {link}" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm"></textarea>
                @error('demoMessage') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                <div class="flex gap-2">
                    <button wire:click="saveDemo" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">{{ $editingDemoId ? 'Update Demo' : '+ Add Demo' }}</button>
                    @if($editingDemoId)
                    <button wire:click="cancelDemoEdit" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
                    @endif
                </div>
            </div>

            <div class="space-y-2">
                @forelse($demos as $demo)
                <div class="flex items-start justify-between gap-2 p-3 rounded-xl border dark:border-gray-700 {{ $demo->is_active ? '' : 'opacity-60' }}" wire:key="demo-{{ $demo->id }}">
                    <div class="min-w-0">
                        <div class="font-medium text-sm flex items-center gap-2">
                            {{ $demo->name }}
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $demo->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}">{{ $demo->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <a href="{{ $demo->url }}" target="_blank" class="text-xs text-indigo-600 hover:underline break-all">{{ Str::limit($demo->url, 48) }}</a>
                        @if($demo->message)<p class="text-xs text-gray-500 mt-1">{{ Str::limit($demo->message, 80) }}</p>@endif
                    </div>
                    <div class="flex gap-1 shrink-0 text-xs">
                        <button wire:click="toggleDemo({{ $demo->id }})" class="px-2 py-1 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700" title="Toggle active">{{ $demo->is_active ? 'Off' : 'On' }}</button>
                        <button wire:click="editDemo({{ $demo->id }})" class="px-2 py-1 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Edit</button>
                        <button wire:click="deleteDemo({{ $demo->id }})" wire:confirm="Ye demo delete karna hai?" class="px-2 py-1 border border-red-200 text-red-600 rounded-lg hover:bg-red-50">Del</button>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-6">Abhi koi demo template nahi hai — left side se add karein.</p>
                @endforelse
            </div>
        </div>
    </div>

    <button wire:click="save" class="mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg">Save Settings</button>
</div>
