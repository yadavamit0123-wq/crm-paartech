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

            <div class="pt-3 border-t dark:border-gray-600 space-y-3">
                <div>
                    <h3 class="font-semibold">Bank Details</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Ye details quotation / invoice / proforma PDF pe dikhte hain.</p>
                </div>
                <input wire:model="bankName" placeholder="Bank name (e.g. HDFC Bank)" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="bankAccount" placeholder="Account number" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 font-mono">
                <div class="grid grid-cols-2 gap-2">
                    <input wire:model="bankIfsc" placeholder="IFSC code" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 font-mono uppercase">
                    <input wire:model="upiId" placeholder="UPI ID (e.g. company@upi)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
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

    {{-- Meeting API Credentials (Live) + full how-to guides --}}
    <div class="mt-6 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 text-sm">
        <p class="font-semibold text-amber-900 dark:text-amber-200 mb-1">Abhi Free Test Mode ON hai — koi keys ki zarurat nahi</p>
        <p class="text-xs text-amber-800 dark:text-amber-300">Lead → Schedule Meeting → <strong>Create Meeting Link</strong> se invite share ho jayega. Jab real Zoom / Google Meet rooms chahiye, neeche guide follow karke credentials paste + <strong>Save Settings</strong> dabao — badge “CONNECTED / Live API” ho jayega.</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mt-4">
        {{-- ZOOM --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3" x-data="{ guide: true }">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold">🔵 Zoom API Credentials (Live)</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($meetingStatus['zoom']['mode'] ?? '') === 'live' ? 'bg-green-100 text-green-700' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}">
                    {{ ($meetingStatus['zoom']['mode'] ?? '') === 'live' ? 'CONNECTED' : 'EMPTY — Test Mode' }}
                </span>
            </div>

            <button type="button" @click="guide = !guide" class="w-full flex items-center justify-between text-left text-sm font-medium px-3 py-2 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                <span>📖 Zoom Live setup — kahan se create karein? (step-by-step)</span>
                <span x-text="guide ? '▲' : '▼'"></span>
            </button>

            <div x-show="guide" x-cloak class="text-xs text-gray-600 dark:text-gray-300 space-y-3 leading-relaxed border dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-gray-900/40">
                <p><strong>Kya chahiye:</strong> free Zoom account (Pro optional) + Server-to-Server OAuth app. Login redirect nahi — 3 values paste karo, bas.</p>
                <ol class="list-decimal pl-4 space-y-2">
                    <li>
                        Browser me kholo:
                        <a href="https://marketplace.zoom.us/" target="_blank" rel="noopener" class="text-indigo-600 underline font-medium">marketplace.zoom.us</a>
                        → apne Zoom account se <strong>Sign In</strong>.
                    </li>
                    <li>
                        Top-right <strong>Develop</strong> → <strong>Build App</strong>.
                    </li>
                    <li>
                        App type select karo: <strong>Server-to-Server OAuth</strong> (dusra type mat lo — Webhook/JWT purana hai).
                        <br>App name likho e.g. <code class="bg-white dark:bg-gray-700 px-1 rounded">CRM Paartech Meetings</code> → Create.
                    </li>
                    <li>
                        <strong>App Credentials</strong> tab me ye 3 cheezein dikhegi — copy karo:
                        <ul class="list-disc pl-4 mt-1 space-y-0.5">
                            <li><strong>Account ID</strong> → neeche pehla box</li>
                            <li><strong>Client ID</strong> → dusra box</li>
                            <li><strong>Client Secret</strong> → teesra box (secret reveal karke copy)</li>
                        </ul>
                    </li>
                    <li>
                        Left menu <strong>Scopes</strong> → <strong>Add Scopes</strong> → search:
                        <code class="bg-white dark:bg-gray-700 px-1 rounded">meeting:write:admin</code>
                        (ya <code class="bg-white dark:bg-gray-700 px-1 rounded">meeting:write</code>) → Add → Done.
                        Optional: <code class="bg-white dark:bg-gray-700 px-1 rounded">user:read:admin</code> bhi add kar sakte ho.
                    </li>
                    <li>
                        <strong>Activation</strong> tab → app <strong>Activate</strong> karo (pehle scopes save hona zaroori).
                    </li>
                    <li>
                        Teeno values yahan paste karo → page ke neeche <strong>Save Settings</strong> → Zoom badge <span class="text-green-700 font-semibold">CONNECTED</span> ho jana chahiye.
                    </li>
                    <li>
                        Test: kisi lead pe Schedule Meeting → Zoom → <strong>Create Meeting Link</strong> — asli <code class="bg-white dark:bg-gray-700 px-1 rounded">zoom.us/j/...</code> link aayega.
                    </li>
                </ol>
                <p class="text-[11px] text-gray-500 border-t dark:border-gray-700 pt-2">
                    Help docs: <a href="https://developers.zoom.us/docs/internal-apps/create/" target="_blank" rel="noopener" class="text-indigo-600 underline">Zoom S2S create guide</a>.
                    Secret leak ho jaye toh Zoom pe Secret rotate karke yahan naya paste kar dena.
                </p>
            </div>

            <div>
                <label class="text-xs text-gray-500 block mb-1">Account ID <span class="text-gray-400">(App Credentials se)</span></label>
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

        {{-- GOOGLE --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3" x-data="{ guide: true }">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-semibold">🟢 Google Meet / Calendar Credentials (Live)</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($meetingStatus['google_meet']['mode'] ?? '') === 'live' ? 'bg-green-100 text-green-700' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}">
                    {{ ($meetingStatus['google_meet']['mode'] ?? '') === 'live' ? 'CONNECTED' : 'EMPTY — Test Mode' }}
                </span>
            </div>

            <button type="button" @click="guide = !guide" class="w-full flex items-center justify-between text-left text-sm font-medium px-3 py-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800">
                <span>📖 Google Meet Live setup — kahan se create karein? (step-by-step)</span>
                <span x-text="guide ? '▲' : '▼'"></span>
            </button>

            <div x-show="guide" x-cloak class="text-xs text-gray-600 dark:text-gray-300 space-y-3 leading-relaxed border dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-gray-900/40">
                <p><strong>Kya chahiye:</strong> Google account + free Google Cloud project. Meet link Calendar event ke saath banega (Google ka standard tarika).</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">A) Client ID + Client Secret banana</p>
                <ol class="list-decimal pl-4 space-y-2">
                    <li>
                        Kholo:
                        <a href="https://console.cloud.google.com/" target="_blank" rel="noopener" class="text-indigo-600 underline font-medium">console.cloud.google.com</a>
                        → apne Google account se login.
                    </li>
                    <li>
                        Top pe project dropdown → <strong>New Project</strong> → name e.g. <code class="bg-white dark:bg-gray-700 px-1 rounded">CRM Paartech</code> → Create → us project ko select karo.
                    </li>
                    <li>
                        Left menu <strong>APIs &amp; Services</strong> → <strong>Library</strong> → search <strong>Google Calendar API</strong> → <strong>Enable</strong>.
                    </li>
                    <li>
                        <strong>APIs &amp; Services</strong> → <strong>OAuth consent screen</strong>:
                        <ul class="list-disc pl-4 mt-1 space-y-0.5">
                            <li>User Type: <strong>External</strong> (personal Gmail) ya Internal (Workspace)</li>
                            <li>App name, User support email, Developer contact — bharo → Save</li>
                            <li>Scopes: <code class="bg-white dark:bg-gray-700 px-1 rounded">https://www.googleapis.com/auth/calendar.events</code> add karo</li>
                            <li>Test users: apna Gmail add karo (External + Testing mode me zaroori)</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Credentials</strong> → <strong>+ Create Credentials</strong> → <strong>OAuth client ID</strong>:
                        <ul class="list-disc pl-4 mt-1 space-y-0.5">
                            <li>Application type: <strong>Web application</strong></li>
                            <li>Name: e.g. CRM Meet</li>
                            <li>Authorized redirect URIs me ye add karo (exact):
                                <br><code class="bg-white dark:bg-gray-700 px-1 rounded break-all">https://developers.google.com/oauthplayground</code>
                                <br>(Refresh token generate karne ke liye Playground use karenge)
                            </li>
                            <li>Create → <strong>Client ID</strong> aur <strong>Client Secret</strong> copy → neeche boxes me paste</li>
                        </ul>
                    </li>
                </ol>

                <p class="font-semibold text-gray-800 dark:text-gray-100">B) Refresh Token banana (ek baar)</p>
                <ol class="list-decimal pl-4 space-y-2" start="6">
                    <li>
                        Kholo:
                        <a href="https://developers.google.com/oauthplayground" target="_blank" rel="noopener" class="text-indigo-600 underline font-medium">developers.google.com/oauthplayground</a>
                    </li>
                    <li>
                        Top-right ⚙️ <strong>OAuth 2.0 configuration</strong> → tick <strong>Use your own OAuth credentials</strong>
                        → wahi Client ID + Client Secret paste → Close.
                    </li>
                    <li>
                        Left list me Step 1 → search / select:
                        <br><code class="bg-white dark:bg-gray-700 px-1 rounded break-all">https://www.googleapis.com/auth/calendar.events</code>
                        → <strong>Authorize APIs</strong> → apna Google account choose → Allow.
                    </li>
                    <li>
                        Step 2 → <strong>Exchange authorization code for tokens</strong> dabao.
                        <br>Jo <strong>Refresh token</strong> dikhe → poora copy → neeche Refresh Token box me paste.
                    </li>
                    <li>
                        Is CRM page pe <strong>Save Settings</strong> → Google badge <span class="text-green-700 font-semibold">CONNECTED</span>.
                    </li>
                    <li>
                        Test: Lead → Schedule Meeting → Google Meet → Create Meeting Link — Calendar pe Meet wali asli link aayegi; lead ka email guest bhi add ho sakta hai.
                    </li>
                </ol>
                <p class="text-[11px] text-gray-500 border-t dark:border-gray-700 pt-2">
                    Note: Publishing status “Testing” me sirf Test users hi allow hote hain — pehle apna email add kar lena.
                    Production company-wide ke liye consent screen Publish + verification (Google process) later kar sakte ho.
                </p>
            </div>

            <div>
                <label class="text-xs text-gray-500 block mb-1">Client ID <span class="text-gray-400">(Google Cloud → Credentials)</span></label>
                <input wire:model="googleClientId" placeholder="xxxx.apps.googleusercontent.com" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Client Secret</label>
                <input type="password" wire:model="googleClientSecret" placeholder="Google Client Secret" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono" autocomplete="new-password">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Refresh Token <span class="text-gray-400">(OAuth Playground se — Part B)</span></label>
                <textarea wire:model="googleRefreshToken" rows="2" placeholder="1//0gxxxxxxxx..." class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm font-mono"></textarea>
            </div>
            <p class="text-[11px] text-gray-400">Fields khali chhodoge toh Free Test Mode automatically chalti rahegi.</p>
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
