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
