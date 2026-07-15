<div id="lead-detail">
    <div class="mb-4">
        <a href="{{ route('leads.list') }}" class="text-indigo-600 text-sm">← Back to Leads</a>
    </div>

    {{-- 3sigma-style Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-4">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $lead->name }}</h1>
                <p class="text-gray-500 mt-1">{{ $lead->phone }} @if($lead->email) • {{ $lead->email }} @endif</p>
                <div class="flex flex-wrap gap-2 mt-3">
                    <div class="text-xs">
                        <span class="text-gray-400 block mb-1">STATUS</span>
                        @if(auth()->user()->hasPermission('leads.edit'))
                        <select wire:change="setStage($event.target.value)" class="text-sm px-2 py-1 rounded-full text-white font-medium border-0 cursor-pointer" style="background:{{ $lead->stage?->color ?? '#6366f1' }}">
                            @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ $lead->lead_stage_id == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                            @endforeach
                        </select>
                        @else
                        <span class="px-3 py-1 rounded-full text-white text-sm font-medium" style="background:{{ $lead->stage?->color ?? '#6366f1' }}">{{ $lead->stage?->name ?? 'New' }}</span>
                        @endif
                    </div>
                    <div class="text-xs">
                        <span class="text-gray-400 block mb-1">LABEL</span>
                        @if(auth()->user()->hasPermission('leads.edit'))
                        <div class="flex flex-wrap gap-1.5">
                            <button wire:click="setLabel('')" class="px-2.5 py-1 rounded-full text-xs border {{ !$lead->lead_label_id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}">None</button>
                            @foreach($labels as $lbl)
                            <button wire:click="setLabel({{ $lbl->id }})" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold transition {{ $lead->lead_label_id == $lbl->id ? 'ring-2 ring-offset-1 shadow-sm' : 'opacity-80 hover:opacity-100' }}" style="background: linear-gradient(135deg, {{ $lbl->color }}33, {{ $lbl->color }}66); color: {{ $lbl->color }}; border: 1.5px solid {{ $lbl->color }}; {{ $lead->lead_label_id == $lbl->id ? 'ring-color:'.$lbl->color : '' }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $lbl->color }}"></span>{{ $lbl->name }}
                            </button>
                            @endforeach
                            <a href="{{ route('leads.labels') }}" class="px-2 py-1 text-xs text-indigo-600">+ Manage</a>
                        </div>
                        @elseif($lead->label)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold" style="background: linear-gradient(135deg, {{ $lead->label->color }}33, {{ $lead->label->color }}55); color: {{ $lead->label->color }}; border: 1.5px solid {{ $lead->label->color }}">{{ $lead->label->name }}</span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Circular quick actions --}}
            <div class="flex flex-wrap gap-2 items-start">
                @if($lead->phone)
                <a href="tel:{{ $lead->phone }}" wire:click="logAction('call')" class="w-11 h-11 rounded-full bg-blue-500 text-white flex items-center justify-center hover:opacity-90 shadow" title="Call">📞</a>
                <button type="button" wire:click="$set('showWhatsappModal', true)" class="w-11 h-11 rounded-full bg-green-500 text-white flex items-center justify-center hover:opacity-90 shadow" title="WhatsApp">💬</button>
                @endif
                @if($lead->email)
                <a href="mailto:{{ $lead->email }}" wire:click="logAction('email')" class="w-11 h-11 rounded-full bg-gray-500 text-white flex items-center justify-center hover:opacity-90 shadow" title="Email">✉️</a>
                @endif
                @if($lead->phone)
                <a href="sms:{{ $lead->phone }}" wire:click="logAction('sms')" class="w-11 h-11 rounded-full bg-sky-400 text-white flex items-center justify-center hover:opacity-90 shadow" title="SMS">📱</a>
                @endif
                @if(auth()->user()->hasPermission('documents.create'))
                <button type="button" wire:click="$set('showQuotationModal', true)" class="w-11 h-11 rounded-full bg-violet-600 text-white flex items-center justify-center hover:opacity-90 shadow" title="Create Quotation">🧾</button>
                @endif
                <button type="button" wire:click="$set('showDemoModal', true)" class="w-11 h-11 rounded-full bg-cyan-600 text-white flex items-center justify-center hover:opacity-90 shadow" title="Send Demo">🖥️</button>
                <button type="button" wire:click="$set('showMeetingModal', true)" class="w-11 h-11 rounded-full bg-amber-500 text-white flex items-center justify-center hover:opacity-90 shadow" title="Schedule Meeting">📅</button>
                <button wire:click="$set('showEditModal', true)" class="w-11 h-11 rounded-full bg-gray-600 text-white flex items-center justify-center hover:opacity-90 shadow" title="Edit">✏️</button>
            </div>
        </div>
    </div>

    {{-- Tabs: Timeline | Task | Notes | Info --}}
    <div class="flex gap-1 mb-4 border-b dark:border-gray-700 overflow-x-auto" id="timeline">
        @foreach([
            'timeline' => 'Timeline ('.$timeline->count().')',
            'task' => 'Task ('.$openTasks->count().')',
            'notes' => 'Notes ('.$lead->leadNotes->count().')',
            'info' => 'Info',
            'chat' => 'Chat',
            'forward' => 'Forward',
            'recording' => 'Recording',
        ] as $key => $label)
        <button wire:click="$set('activeTab', '{{ $key }}')" class="px-4 py-2 text-sm whitespace-nowrap border-b-2 {{ $activeTab === $key ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">

            @if($activeTab === 'timeline')
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold">Timeline / Activity</h3>
                    <button wire:click="$set('showActivityModal', true)" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm">+ Add Activity</button>
                </div>
                <div class="space-y-3">
                    @forelse($timeline as $activity)
                    <div class="flex gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-l-4 border-orange-400">
                        <div class="text-orange-400 text-lg">🔗</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm">{{ $activity->title }}</div>
                            @if($activity->description)<p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $activity->description }}</p>@endif
                            <p class="text-xs text-gray-400 mt-2">Created by: {{ $activity->user?->name ?? 'System' }} • {{ $activity->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 bg-gray-200 dark:bg-gray-600 rounded h-fit">{{ $activity->type }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm text-center py-8">No activity yet — call, WhatsApp ya note add karein</p>
                    @endforelse
                </div>
            </div>
            @endif

            @if($activeTab === 'task')
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700" id="notes">
                <h3 class="font-semibold mb-4">Tasks / Follow-ups</h3>
                <div class="grid md:grid-cols-2 gap-3 mb-4">
                    <input type="text" wire:model="taskTitle" placeholder="Task title *" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <input type="datetime-local" wire:model="taskDueAt" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <select wire:model="taskPriority" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        @foreach(['low','medium','high'] as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
                    </select>
                    <textarea wire:model="taskDescription" rows="2" placeholder="Description..." class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>
                <button wire:click="addTask" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm mb-6">+ Create Task</button>

                <div class="space-y-2">
                    @foreach($lead->tasks as $task)
                    <div class="flex justify-between items-center p-3 rounded-lg {{ $task->status === 'completed' ? 'bg-green-50 dark:bg-green-900/20' : ($task->isOverdue() ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-gray-700') }}">
                        <div>
                            <div class="font-medium text-sm {{ $task->status === 'completed' ? 'line-through text-gray-400' : '' }}">{{ $task->title }}</div>
                            @if($task->due_at)<div class="text-xs text-gray-500">Due: {{ $task->due_at->format('d M Y H:i') }}</div>@endif
                        </div>
                        @if($task->status === 'pending')
                        <button wire:click="completeTask({{ $task->id }})" class="text-xs text-green-600 font-medium">✓ Done</button>
                        @else
                        <span class="text-xs text-green-600">Completed</span>
                        @endif
                    </div>
                    @endforeach
                </div>

                <h4 class="font-medium mt-6 mb-2 text-sm">Reminders</h4>
                @foreach($lead->reminders as $rem)
                <div class="flex justify-between p-2 text-sm border-b dark:border-gray-700">
                    <span>{{ $rem->title }} — {{ $rem->remind_at->format('d M H:i') }}</span>
                    @if(!$rem->is_completed)
                    <button wire:click="completeReminder({{ $rem->id }})" class="text-green-600 text-xs">Complete</button>
                    @else<span class="text-green-500 text-xs">Done</span>@endif
                </div>
                @endforeach
                <div class="mt-4 grid md:grid-cols-2 gap-2">
                    <input type="text" wire:model="reminderTitle" placeholder="Reminder title" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                    <input type="datetime-local" wire:model="reminderAt" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                </div>
                <button wire:click="setReminder" class="mt-2 px-3 py-1.5 bg-yellow-500 text-white rounded-lg text-sm">Set Reminder</button>
            </div>
            @endif

            @if($activeTab === 'notes')
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <h3 class="font-semibold mb-4">Notes</h3>
                <div class="flex gap-2 mb-4">
                    <textarea wire:model="noteContent" rows="2" class="flex-1 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Add note..."></textarea>
                    <input type="color" wire:model="noteColor" class="w-12 h-10 rounded cursor-pointer">
                    <button wire:click="addNote" class="px-4 py-2 bg-yellow-400 text-gray-800 rounded-lg">Add</button>
                </div>
                <div class="grid md:grid-cols-2 gap-3">
                    @foreach($lead->leadNotes as $note)
                    <div class="p-4 rounded-lg" style="background:{{ $note->color }}">
                        <p class="text-sm">{{ $note->content }}</p>
                        <p class="text-xs text-gray-600 mt-2">{{ $note->user?->name ?? 'System' }} • {{ $note->created_at->diffForHumans() }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($activeTab === 'info')
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <h3 class="font-semibold mb-4">Lead Info</h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Company:</span> {{ $lead->company ?? '—' }}</div>
                    <div><span class="text-gray-500">Service Type:</span> {{ $lead->service_type ?? '—' }}</div>
                    <div><span class="text-gray-500">Source:</span> {{ $lead->source }}</div>
                    <div><span class="text-gray-500">Campaign:</span> {{ $lead->campaign ?? '—' }}</div>
                    <div><span class="text-gray-500">City:</span> {{ $lead->city ?? '—' }}</div>
                    <div><span class="text-gray-500">Priority:</span> {{ ucfirst($lead->priority) }}</div>
                    <div><span class="text-gray-500">Value:</span> ₹{{ number_format($lead->value ?? 0) }}</div>
                    <div><span class="text-gray-500">Assigned:</span> {{ $lead->assignee?->name ?? 'Unassigned' }}</div>
                    <div><span class="text-gray-500">Last Contact:</span> {{ $lead->last_contacted_at?->diffForHumans() ?? 'Never' }}</div>
                    <div><span class="text-gray-500">Last Call:</span> {{ $lead->last_call_at?->diffForHumans() ?? 'Never' }}</div>
                    <div class="md:col-span-2"><span class="text-gray-500">Address:</span> {{ $lead->address ?? '—' }}</div>
                    <div class="md:col-span-2"><span class="text-gray-500">Notes:</span> {{ $lead->notes ?? '—' }}</div>
                </div>
                @if($customFields->count())
                <div class="mt-4 border-t dark:border-gray-700 pt-4">
                    <h4 class="font-medium text-sm mb-2">Custom Fields</h4>
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        @foreach($customFields as $field)
                        @php $cfValue = $lead->custom_fields[$field->field_key] ?? null; @endphp
                        <div>
                            <span class="text-gray-500">{{ $field->label }}:</span>
                            @if($field->field_type === 'checkbox')
                            {{ $cfValue ? 'Yes' : 'No' }}
                            @else
                            {{ ($cfValue !== null && $cfValue !== '') ? $cfValue : '—' }}
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="mt-4 flex flex-wrap gap-2">
                    @if(auth()->user()->hasPermission('customers.manage'))
                        @if($lead->is_customer && $lead->customer)
                        <a href="{{ route('leads.customers.show', $lead->customer) }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm">View Customer</a>
                        @else
                        <button wire:click="convertToCustomer" wire:confirm="Convert to customer?" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm">Convert to Customer</button>
                        @endif
                    @endif
                    @if(auth()->user()->hasPermission('documents.create'))
                    <a href="{{ route('leads.documents.create', ['lead_id' => $lead->id, 'type' => 'quotation']) }}" class="px-3 py-1.5 bg-violet-600 text-white rounded-lg text-sm">+ Quotation</a>
                    <a href="{{ route('leads.documents.create', ['lead_id' => $lead->id, 'type' => 'proforma']) }}" class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm">+ Proforma</a>
                    <a href="{{ route('leads.documents.create', ['lead_id' => $lead->id, 'type' => 'invoice']) }}" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm">+ Invoice</a>
                    @endif
                </div>
                @php $leadDocuments = $lead->documents()->latest()->limit(10)->get(); @endphp
                @if($leadDocuments->count())
                <div class="mt-4 border-t dark:border-gray-700 pt-4">
                    <h4 class="font-medium text-sm mb-2">Quotes & Invoices</h4>
                    <div class="space-y-2">
                        @foreach($leadDocuments as $doc)
                        <div class="flex items-center justify-between text-sm p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <div>
                                <span class="font-medium">{{ $doc->document_number }}</span>
                                <span class="text-gray-500 ml-2">{{ $doc->typeLabel() }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-600 ml-1">{{ ucfirst($doc->status) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold">₹{{ number_format($doc->grand_total, 2) }}</span>
                                <a href="{{ route('leads.documents.show', $doc) }}" class="text-indigo-600 text-xs">View →</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <h3 class="font-semibold mb-4">Company Profile</h3>
                <textarea wire:model="companyProfile" rows="4" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Company background, products, requirements..."></textarea>
                <button wire:click="saveCompanyProfile" class="mt-3 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Save Profile</button>
            </div>
            @endif

            @if($activeTab === 'chat')
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <h3 class="font-semibold mb-4">Internal Team Chat</h3>
                <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                    @foreach($lead->chatMessages as $msg)
                    <div class="p-3 rounded-lg {{ $msg->user_id === auth()->id() ? 'bg-indigo-100 dark:bg-indigo-900/30 ml-8' : 'bg-gray-100 dark:bg-gray-700 mr-8' }}">
                        <div class="text-xs font-semibold">{{ $msg->user->name }}</div>
                        <div class="text-sm">{{ $msg->message }}</div>
                    </div>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <input type="text" wire:model="chatMessage" wire:keydown.enter="sendChat" class="flex-1 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Team message...">
                    <button wire:click="sendChat" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Send</button>
                </div>
            </div>
            @endif

            @if($activeTab === 'forward')
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <h3 class="font-semibold mb-4">Forward Lead</h3>
                <select wire:model="forwardTo" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mb-3">
                    <option value="">Select team member...</option>
                    @foreach($employees as $emp)
                    @if($emp->id !== auth()->id())<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endif
                    @endforeach
                </select>
                <textarea wire:model="forwardNote" rows="3" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mb-3" placeholder="Note..."></textarea>
                <button wire:click="forwardLead" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Forward</button>
                @if($lead->forwards->count())
                <h4 class="font-medium mt-6 mb-2">Forward History</h4>
                @foreach($lead->forwards as $fwd)
                <div class="text-sm p-2 border-b dark:border-gray-700">{{ $fwd->fromUser->name }} → {{ $fwd->toUser->name }} • {{ $fwd->created_at->diffForHumans() }}</div>
                @endforeach
                @endif
            </div>
            @endif

            @if($activeTab === 'recording')
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
                <h3 class="font-semibold mb-4">Call Recordings</h3>
                <div class="flex gap-3 mb-4 flex-wrap">
                    <input type="file" wire:model="recordingFile" accept="audio/*" class="text-sm">
                    <input type="text" wire:model="recordingTitle" placeholder="Title" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <button wire:click="uploadRecording" class="px-4 py-2 bg-red-500 text-white rounded-lg">Upload</button>
                </div>
                @foreach($lead->recordings as $rec)
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg mb-2">
                    <div class="font-medium text-sm">{{ $rec->title }}</div>
                    <audio controls class="w-full mt-2" src="{{ asset('storage/'.$rec->file_path) }}"></audio>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
                <h4 class="font-semibold text-sm mb-3">Quick Labels</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($labels as $lbl)
                    <button wire:click="setLabel({{ $lbl->id }})" class="px-3 py-1 rounded-full text-xs font-medium {{ $lead->lead_label_id == $lbl->id ? 'ring-2 ring-offset-1' : '' }}" style="background:{{ $lbl->color }}22;color:{{ $lbl->color }};{{ $lead->lead_label_id == $lbl->id ? 'ring-color:'.$lbl->color : '' }}">
                        {{ $lbl->name }}
                    </button>
                    @endforeach
                </div>
            </div>
            @foreach($lead->stickyNotes->take(3) as $note)
            <div class="p-4 rounded-lg shadow-sm rotate-1" style="background:{{ $note->color }}">
                <p class="text-sm">{{ $note->content }}</p>
            </div>
            @endforeach
            @if(auth()->user()->hasPermission('leads.delete'))
            <button wire:click="deleteLead" wire:confirm="Delete this lead?" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm">Delete Lead</button>
            @endif
        </div>
    </div>

    @if($showWhatsappModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold">WhatsApp Templates</h3>
                <button wire:click="$set('showWhatsappModal', false)" class="text-gray-400">✕</button>
            </div>
            <p class="text-xs text-gray-500 mb-4">Template select karein — wa.me pe pre-filled message khulega aur timeline me log hoga.</p>
            <div class="space-y-2">
                @foreach($whatsappPreviews as $i => $preview)
                <button wire:click="sendWhatsappTemplate({{ $i }})" class="w-full text-left p-3 border rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 text-sm">
                    {{ $preview }}
                </button>
                @endforeach
            </div>
            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/','',$lead->phone) }}" target="_blank" wire:click="logAction('whatsapp')" class="block mt-4 text-center text-sm text-green-600 hover:underline">Direct WhatsApp (no template)</a>
        </div>
    </div>
    @endif

    @script
    <script>
        $wire.on('open-whatsapp', ({ url }) => { window.open(url, '_blank'); });
    </script>
    @endscript

    @if($showActivityModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md">
            <h3 class="font-semibold mb-4">Add Activity</h3>
            <input type="text" wire:model="activityTitle" placeholder="Activity title *" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mb-3">
            <textarea wire:model="activityDescription" rows="3" placeholder="Details..." class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mb-4"></textarea>
            <div class="flex gap-2">
                <button wire:click="$set('showActivityModal', false)" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button wire:click="addCustomActivity" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg">Save</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Create Quotation modal --}}
    @if($showQuotationModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-semibold">Create Quotation — {{ $lead->name }}</h3>
                <button wire:click="$set('showQuotationModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <p class="text-xs text-gray-500 mb-3">Products select karein — quotation pre-filled items ke saath khulega.</p>
            @if($products->count())
            <div class="space-y-1.5 max-h-56 overflow-y-auto mb-3">
                @foreach($products as $product)
                <label class="flex items-center justify-between gap-2 p-2.5 border dark:border-gray-600 rounded-lg cursor-pointer hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10" wire:key="qp-{{ $product->id }}">
                    <span class="flex items-center gap-2 text-sm min-w-0">
                        <input type="checkbox" wire:model="quoteProducts" value="{{ $product->id }}" class="rounded">
                        <span class="truncate">{{ $product->name }}</span>
                    </span>
                    <span class="text-xs text-gray-500 shrink-0">₹{{ number_format($product->price, 2) }} • GST {{ (float) $product->tax_rate }}%</span>
                </label>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500 text-center py-4 mb-2">Koi active product nahi mila.</p>
            @endif
            @if(auth()->user()->hasPermission('products.manage'))
            <a href="{{ route('leads.products') }}" class="block text-xs text-indigo-600 hover:underline mb-4">💡 Products pehle se Products tab me create kar ke rakhein →</a>
            @else
            <p class="text-xs text-gray-400 mb-4">💡 Products pehle se Products tab me create kar ke rakhein</p>
            @endif
            <div class="flex gap-2">
                <button wire:click="$set('showQuotationModal', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
                <button wire:click="createQuotation" class="flex-1 px-4 py-2 bg-violet-600 text-white rounded-lg text-sm">Create Quotation →</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Send Demo modal --}}
    @if($showDemoModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-semibold">Send Demo — {{ $lead->name }}</h3>
                <button wire:click="$set('showDemoModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <p class="text-xs text-gray-500 mb-3">Demo select karein — WhatsApp/Email pre-filled message ke saath khulega aur timeline me log hoga.</p>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($demos as $demo)
                <div class="p-3 border dark:border-gray-600 rounded-xl" wire:key="demo-{{ $demo->id }}">
                    <div class="font-medium text-sm">{{ $demo->name }}</div>
                    <div class="text-xs text-gray-500 break-all mb-2">{{ Str::limit($demo->url, 50) }}</div>
                    <div class="flex gap-2">
                        @if($lead->phone)
                        <button wire:click="sendDemo({{ $demo->id }}, 'whatsapp')" class="flex-1 px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs">💬 WhatsApp</button>
                        @endif
                        @if($lead->email)
                        <button wire:click="sendDemo({{ $demo->id }}, 'email')" class="flex-1 px-3 py-1.5 bg-gray-600 text-white rounded-lg text-xs">✉️ Email</button>
                        @endif
                        @if(!$lead->phone && !$lead->email)
                        <span class="text-xs text-red-500">Lead ka phone/email nahi hai</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-6">Koi active demo nahi hai. Settings → Manage Demos me demo templates add karein.</p>
                @endforelse
            </div>
            @if(auth()->user()->hasPermission('settings.manage'))
            <a href="{{ route('leads.settings') }}" class="block text-xs text-indigo-600 hover:underline mt-3">⚙ Manage Demos in Settings →</a>
            @endif
        </div>
    </div>
    @endif

    {{-- Schedule Meeting modal --}}
    @if($showMeetingModal)
    @php
        $meetModeKey = $meetingPlatform === 'zoom' ? 'zoom' : 'google_meet';
        $meetModeInfo = $meetingStatus[$meetModeKey] ?? ['mode' => 'test', 'label' => 'Free Test Mode'];
    @endphp
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-semibold">Schedule Meeting — {{ $lead->name }}</h3>
                <button wire:click="$set('showMeetingModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="flex flex-wrap gap-2 text-xs mb-4">
                <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700">📱 {{ $lead->phone ?? 'No phone' }}</span>
                <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700">✉️ {{ $lead->email ?? 'No email' }}</span>
                <span class="px-2 py-1 rounded-full {{ ($meetModeInfo['mode'] ?? '') === 'live' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800' }}">
                    {{ $meetModeInfo['label'] ?? 'Free Test Mode' }}
                </span>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Platform</label>
                    <select wire:model.live="meetingPlatform" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                        <option value="google_meet">Google Meet</option>
                        <option value="zoom">Zoom</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Meeting mode</label>
                    <div class="flex gap-2">
                        <label class="flex-1 flex items-center gap-2 p-2.5 border dark:border-gray-600 rounded-lg cursor-pointer text-sm {{ $meetingMode === 'instant' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                            <input type="radio" wire:model.live="meetingMode" value="instant"> ⚡ Instant meeting
                        </label>
                        <label class="flex-1 flex items-center gap-2 p-2.5 border dark:border-gray-600 rounded-lg cursor-pointer text-sm {{ $meetingMode === 'scheduled' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                            <input type="radio" wire:model.live="meetingMode" value="scheduled"> 🗓 Scheduled
                        </label>
                    </div>
                </div>
                @if($meetingMode === 'scheduled')
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Date & time</label>
                    <input type="datetime-local" wire:model="meetingAt" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                    @error('meetingAt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 border dark:border-gray-600 space-y-2">
                    <p class="text-xs text-gray-500">
                        @if(($meetModeInfo['mode'] ?? '') === 'live')
                        <span class="font-semibold text-green-700">Live API connected</span> — ek click me real {{ $meetingPlatform === 'zoom' ? 'Zoom' : 'Google Meet' }} meeting banegi.
                        @else
                        <span class="font-semibold text-amber-700">Free Test Mode</span> — CRM abhi free test link generate karega (paste nahi karna). Live chahiye toh Settings → Meeting API Credentials me keys save karo.
                        @endif
                    </p>
                    <button wire:click="createMeeting" wire:loading.attr="disabled" class="w-full px-3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                        <span wire:loading.remove wire:target="createMeeting">✨ Create Meeting Link</span>
                        <span wire:loading wire:target="createMeeting">Creating…</span>
                    </button>
                    @if($meetingLink)
                    <div class="rounded-lg border dark:border-gray-600 bg-white dark:bg-gray-800 p-2">
                        <p class="text-[11px] text-gray-500 mb-1">Meeting link ready:</p>
                        <a href="{{ $meetingLink }}" target="_blank" class="text-sm text-indigo-600 break-all hover:underline">{{ $meetingLink }}</a>
                        <button type="button" wire:click="launchMeetingPlatform" class="mt-2 w-full px-3 py-1.5 border rounded-lg text-xs">Open link</button>
                    </div>
                    @endif
                    <p class="text-[11px] text-gray-400">Manual override (optional):</p>
                    <input type="url" wire:model="meetingLink" placeholder="https://meet.google.com/abc-defg-hij" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                </div>

                <div>
                    <label class="text-xs text-gray-500 block mb-1">Share via</label>
                    <div class="flex gap-4 text-sm">
                        <label class="flex items-center gap-2 {{ $lead->phone ? 'cursor-pointer' : 'opacity-50' }}">
                            <input type="checkbox" wire:model="meetingShareWhatsapp" class="rounded" @disabled(!$lead->phone)> 💬 WhatsApp
                        </label>
                        <label class="flex items-center gap-2 {{ $lead->email ? 'cursor-pointer' : 'opacity-50' }}">
                            <input type="checkbox" wire:model="meetingShareEmail" class="rounded" @disabled(!$lead->email)> ✉️ Email
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button wire:click="$set('showMeetingModal', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
                <button wire:click="shareMeeting" class="flex-1 px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-medium">Share Invite & Log</button>
            </div>
            <p class="text-xs text-gray-400 mt-2">Link empty ho toh Share pe click karte hi auto-create ho jayega. Live API keys + poori guide: <a href="{{ route('leads.settings') }}" class="text-indigo-600 underline">Settings → Meeting API Credentials</a>.</p>
        </div>
    </div>
    @endif

    @if($showEditModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-lg">
            <h3 class="font-semibold mb-4">Edit Lead</h3>
            <div class="space-y-3">
                <input type="text" wire:model="editName" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Name *">
                @error('editName') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                <input type="email" wire:model="editEmail" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Email">
                @error('editEmail') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                <input type="text" wire:model="editPhone" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Phone">
                @error('editPhone') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                <input type="text" wire:model="editCompany" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Company">
                <input type="text" wire:model="editServiceType" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="Service Type">
                <select wire:model="editStageId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    @foreach($stages as $stage)<option value="{{ $stage->id }}">{{ $stage->name }}</option>@endforeach
                </select>
                @error('editStageId') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                <select wire:model="editLabelId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="">No label</option>
                    @foreach($labels as $lbl)<option value="{{ $lbl->id }}">{{ $lbl->name }}</option>@endforeach
                </select>
                <select wire:model="editAssignedTo" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                </select>
                @foreach($customFields as $field)
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ $field->label }}</label>
                    @if($field->field_type === 'select')
                    <select wire:model="customFieldValues.{{ $field->field_key }}" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Select...</option>
                        @foreach($field->options ?? [] as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    @elseif($field->field_type === 'checkbox')
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="customFieldValues.{{ $field->field_key }}" class="rounded border-gray-300 dark:border-gray-600">
                        <span>Yes</span>
                    </label>
                    @else
                    <input type="{{ match($field->field_type) { 'number' => 'number', 'date' => 'date', 'email' => 'email', 'phone' => 'tel', default => 'text' } }}" wire:model="customFieldValues.{{ $field->field_key }}" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600" placeholder="{{ $field->label }}">
                    @endif
                </div>
                @endforeach
            </div>
            <div class="flex gap-2 mt-4">
                <button wire:click="updateLead" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Save</button>
                <button wire:click="$set('showEditModal', false)" class="px-4 py-2 border rounded-lg">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
