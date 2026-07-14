<div>
    @include('layouts.partials.leads-nav')

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-4">
        <div>
            <h1 class="text-2xl font-bold">Leads / लीड्स</h1>
            <p class="text-gray-500 text-sm">Lead list ({{ $stats['total'] }}) — 3sigma style pipeline</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->hasPermission('leads.export'))
            <button wire:click="exportCsv" class="px-3 py-2 border rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">⬇ Export CSV</button>
            @endif
            @if(auth()->user()->hasPermission('leads.create'))
            <a href="{{ route('leads.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add new Lead</a>
            @endif
            @if(auth()->user()->hasPermission('leads.bulk_upload'))
            <a href="{{ route('leads.bulk-upload') }}" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm">📤 Import</a>
            @endif
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 mb-3 shadow-sm border dark:border-gray-700">
        <div class="flex flex-wrap gap-2 items-center">
            <button wire:click="$set('showFilterModal', true)" class="px-3 py-2 border rounded-lg text-sm flex items-center gap-1 hover:bg-gray-50 dark:hover:bg-gray-700">
                🔽 Filters
            </button>

            {{-- Lead list dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-3 py-2 border rounded-lg text-sm flex items-center gap-1 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Lead list ({{ $stats['total'] }}) ▾
                </button>
                <div x-show="open" @click.outside="open = false" class="absolute z-20 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-600 rounded-lg shadow-lg py-1 min-w-[200px]">
                    <button wire:click="selectLeadList(null)" @click="open=false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 {{ !$activeLeadListId ? 'bg-indigo-50 text-indigo-700' : '' }}">All leads</button>
                    @foreach($leadLists as $list)
                    <button wire:click="selectLeadList({{ $list->id }})" @click="open=false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 {{ $activeLeadListId == $list->id ? 'bg-indigo-50 text-indigo-700' : '' }}">
                        {{ $list->name }} @if($list->is_default)<span class="text-xs text-gray-400">(default)</span>@endif
                    </button>
                    @endforeach
                    <div class="border-t dark:border-gray-600 mt-1 pt-1">
                        <button wire:click="$set('showLeadListModal', true)" @click="open=false" class="block w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">+ Add new list</button>
                    </div>
                </div>
            </div>

            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search leads by name, email or phone..." class="flex-1 min-w-[220px] px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">

            {{-- Bulk Actions dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-3 py-2 border rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">⚡ Bulk Actions</button>
                <div x-show="open" @click.outside="open = false" class="absolute z-20 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-600 rounded-lg shadow-lg py-1 min-w-[160px]">
                    <button wire:click="openBulk('stage')" @click="open=false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Update Status</button>
                    <button wire:click="openBulk('label')" @click="open=false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Set Label</button>
                    <button wire:click="openBulk('assign')" @click="open=false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Assign User</button>
                    @if(auth()->user()->hasPermission('leads.delete'))
                    <button wire:click="openBulk('delete')" @click="open=false" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                    @endif
                </div>
            </div>

            @if(count($selected))
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">{{ count($selected) }} selected</span>
            @endif

            <button wire:click="$refresh" class="px-3 py-2 border rounded-lg text-sm">🔄 Refresh</button>
            <button wire:click="$set('showColumnsModal', true)" class="px-3 py-2 border rounded-lg text-sm">⚙ Edit Columns</button>

            {{-- Add new leads dropdown --}}
            @if(auth()->user()->hasPermission('leads.create') || auth()->user()->hasPermission('leads.bulk_upload'))
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add new leads ▾</button>
                <div x-show="open" @click.outside="open = false" class="absolute right-0 z-20 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-600 rounded-lg shadow-lg py-1 min-w-[160px]">
                    @if(auth()->user()->hasPermission('leads.create'))
                    <a href="{{ route('leads.create') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Single lead</a>
                    @endif
                    @if(auth()->user()->hasPermission('leads.bulk_upload'))
                    <a href="{{ route('leads.bulk-upload') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Bulk import</a>
                    @endif
                </div>
            </div>
            @endif

            <div class="flex rounded-lg border dark:border-gray-600 overflow-hidden ml-auto">
                <button wire:click="$set('view', 'list')" class="px-3 py-2 text-sm {{ $view === 'list' ? 'bg-indigo-600 text-white' : '' }}">List</button>
                <button wire:click="$set('view', 'kanban')" class="px-3 py-2 text-sm {{ $view === 'kanban' ? 'bg-indigo-600 text-white' : '' }}">Kanban</button>
            </div>
        </div>
    </div>

    {{-- Quick Filters + Dropdowns --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button wire:click="$set('showSimpleFilterModal', true)" class="px-3 py-1.5 rounded-full text-xs border bg-white dark:bg-gray-800 dark:border-gray-600 hover:border-indigo-400">
            💾 Saved Filters @if(count($savedFilters))<span class="ml-1 bg-indigo-100 text-indigo-700 px-1.5 rounded-full">{{ count($savedFilters) }}</span>@endif
        </button>
        @foreach([
            'untouched' => '👋 Untouched',
            'no_call' => '📵 No call',
            'unassigned' => '👤 Unassigned',
            'no_task' => '📋 No task',
            'stale' => '⏳ Stale',
            'duplicates' => '🔁 Duplicates',
        ] as $key => $label)
        <button wire:click="applyQuickFilter('{{ $key }}')" class="px-3 py-1.5 rounded-full text-xs border {{ $quickFilter === $key ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-800 dark:border-gray-600 hover:border-indigo-400' }}">
            {{ $label }}
        </button>
        @endforeach

        <input type="date" wire:model.live="filterDateFrom" class="px-3 py-1.5 rounded-full text-xs border dark:bg-gray-800 dark:border-gray-600" title="Date from">
        <input type="date" wire:model.live="filterDateTo" class="px-3 py-1.5 rounded-full text-xs border dark:bg-gray-800 dark:border-gray-600" title="Date to">

        <select wire:model.live="filterStage" class="px-3 py-1.5 rounded-full text-xs border dark:bg-gray-800 dark:border-gray-600">
            <option value="">Status</option>
            @foreach($stages as $stage)
            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterLabel" class="px-3 py-1.5 rounded-full text-xs border dark:bg-gray-800 dark:border-gray-600">
            <option value="">Labels</option>
            @foreach($labels as $label)
            <option value="{{ $label->id }}">{{ $label->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterAssignee" class="px-3 py-1.5 rounded-full text-xs border dark:bg-gray-800 dark:border-gray-600">
            <option value="">Assigned</option>
            @foreach($employees as $emp)
            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterCreatedBy" class="px-3 py-1.5 rounded-full text-xs border dark:bg-gray-800 dark:border-gray-600">
            <option value="">Users</option>
            @foreach($employees as $emp)
            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
            @endforeach
        </select>
        @if($quickFilter || $filterStage || $filterLabel || $filterAssignee || $filterCreatedBy || $filterDateFrom || $filterDateTo || $search)
        <button wire:click="resetFilters" class="px-3 py-1.5 rounded-full text-xs text-red-600 border border-red-200">✕ Clear</button>
        @endif
    </div>

    @if($view === 'kanban')
    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach($stages as $stage)
        <div class="flex-shrink-0 w-72">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-3 h-3 rounded-full" style="background: {{ $stage->color }}"></div>
                <h3 class="font-semibold text-sm">{{ $stage->name }}</h3>
                <span class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ ($leads[$stage->id] ?? collect())->count() }}</span>
            </div>
            <div class="space-y-3">
                @foreach($leads[$stage->id] ?? [] as $lead)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border dark:border-gray-700">
                    <a href="{{ route('leads.show', $lead) }}" class="block">
                        <div class="font-medium">{{ $lead->name }}</div>
                        <div class="text-xs text-gray-500">{{ $lead->phone }}</div>
                        @if($lead->label)<span class="text-xs px-2 py-0.5 rounded mt-1 inline-block" style="background:{{ $lead->label->color }}22;color:{{ $lead->label->color }}">{{ $lead->label->name }}</span>@endif
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @else
    {{-- 3sigma-style List Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-sm min-w-[1000px]">
            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-3 py-3 w-10"><input type="checkbox" wire:model.live="selectAll" class="rounded"></th>
                    @if(in_array('client', $visibleColumns))<th class="px-3 py-3 text-left">Client Name</th>@endif
                    @if(in_array('label', $visibleColumns))<th class="px-3 py-3 text-left">Label</th>@endif
                    @if(in_array('status', $visibleColumns))<th class="px-3 py-3 text-left">Status</th>@endif
                    @if(in_array('actions', $visibleColumns))<th class="px-3 py-3 text-center">Actions</th>@endif
                    @if(in_array('source', $visibleColumns))<th class="px-3 py-3 text-left">Source</th>@endif
                    @if(in_array('created', $visibleColumns))<th class="px-3 py-3 text-left">Created</th>@endif
                    @if(in_array('phone', $visibleColumns))<th class="px-3 py-3 text-left">Phone</th>@endif
                    @if(in_array('assigned', $visibleColumns))<th class="px-3 py-3 text-left">Assigned</th>@endif
                    @if(in_array('service', $visibleColumns))<th class="px-3 py-3 text-left">Service Type</th>@endif
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($leads as $lead)
                @php $isDuplicate = $lead->phone && $duplicatePhones->contains($lead->phone); @endphp
                <tr class="hover:bg-blue-50/50 dark:hover:bg-gray-700/40 {{ in_array((string)$lead->id, $selected) ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                    <td class="px-3 py-3"><input type="checkbox" wire:model.live="selected" value="{{ $lead->id }}" class="rounded"></td>
                    @if(in_array('client', $visibleColumns))
                    <td class="px-3 py-3">
                        <a href="{{ route('leads.show', $lead) }}" class="text-indigo-600 font-medium hover:underline">
                            <span class="text-gray-400 text-xs">{{ $lead->created_at->format('d-m-Y') }}</span><br>
                            {{ Str::limit($lead->name, 22) }}
                            @if($isDuplicate)<span class="ml-1 text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded" title="Duplicate phone">🔁 Dup</span>@endif
                        </a>
                    </td>
                    @endif
                    @if(in_array('label', $visibleColumns))
                    <td class="px-3 py-3">
                        @if(auth()->user()->hasPermission('leads.edit'))
                        <select wire:change="setLeadLabel({{ $lead->id }}, $event.target.value)" class="text-xs rounded-full px-2 py-1 border-0 cursor-pointer font-semibold" style="background:{{ $lead->label?->color ?? '#94a3b8' }}22;color:{{ $lead->label?->color ?? '#64748b' }}">
                            <option value="" @selected(!$lead->label)>No label</option>
                            @foreach($labels as $lbl)
                            <option value="{{ $lbl->id }}" @selected($lead->lead_label_id == $lbl->id)>{{ $lbl->name }}</option>
                            @endforeach
                        </select>
                        @elseif($lead->label)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold shadow-sm" style="background: linear-gradient(135deg, {{ $lead->label->color }}33, {{ $lead->label->color }}55); color: {{ $lead->label->color }}; border: 1.5px solid {{ $lead->label->color }}">
                            <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $lead->label->color }}"></span>{{ $lead->label->name }}
                        </span>
                        @else<span class="text-gray-400">—</span>@endif
                    </td>
                    @endif
                    @if(in_array('status', $visibleColumns))
                    <td class="px-3 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium text-white" style="background:{{ $lead->stage?->color ?? '#6366f1' }}">{{ $lead->stage?->name ?? 'New' }}</span>
                    </td>
                    @endif
                    @if(in_array('actions', $visibleColumns))
                    <td class="px-3 py-3">
                        <div class="flex items-center justify-center gap-1">
                            @if($lead->phone)
                            <a href="tel:{{ $lead->phone }}" wire:click="logQuickCall({{ $lead->id }})" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200" title="Call">📞</a>
                            <a href="mailto:{{ $lead->email }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" title="Email">✉️</a>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/','',$lead->phone) }}" target="_blank" wire:click="logQuickWhatsapp({{ $lead->id }})" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 text-green-600 hover:bg-green-200" title="WhatsApp">💬</a>
                            @endif
                            <a href="{{ route('leads.show', $lead) }}?tab=notes" class="w-8 h-8 flex items-center justify-center rounded-full bg-orange-100 text-orange-600 hover:bg-orange-200" title="Notes">📝</a>
                            <a href="{{ route('leads.show', $lead) }}?tab=timeline" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200" title="Timeline">📋</a>
                        </div>
                    </td>
                    @endif
                    @if(in_array('source', $visibleColumns))
                    <td class="px-3 py-3">
                        @php
                            $sourceIcons = ['phone_book' => '📒', 'whatsapp' => '💬', 'meta' => '📘', 'google' => '🔍', 'website' => '🌐', 'manual' => '✏️', 'bulk_upload' => '📤', 'referral' => '🤝'];
                            $icon = $sourceIcons[$lead->source] ?? '📋';
                        @endphp
                        <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded inline-flex items-center gap-1">{{ $icon }} {{ ucfirst(str_replace('_', ' ', $lead->source ?? 'manual')) }}</span>
                    </td>
                    @endif
                    @if(in_array('created', $visibleColumns))
                    <td class="px-3 py-3 text-gray-500 text-xs">{{ $lead->created_at->diffForHumans() }}</td>
                    @endif
                    @if(in_array('phone', $visibleColumns))
                    <td class="px-3 py-3">
                        @if($lead->phone)
                        <div class="flex items-center gap-1">
                            <span class="font-mono text-xs">{{ $lead->phone }}</span>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $lead->phone }}')" class="text-gray-400 hover:text-indigo-600 text-xs" title="Copy">📋</button>
                        </div>
                        @else — @endif
                    </td>
                    @endif
                    @if(in_array('assigned', $visibleColumns))
                    <td class="px-3 py-3 text-xs">{{ $lead->assignee?->name ?? '—' }}</td>
                    @endif
                    @if(in_array('service', $visibleColumns))
                    <td class="px-3 py-3 text-xs">{{ $lead->service_type ?? '—' }}</td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="10" class="px-4 py-12 text-center text-gray-500">No leads found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="flex flex-wrap justify-between items-center p-4 border-t dark:border-gray-700 gap-3">
            <div class="flex items-center gap-2 text-sm">
                <span>Page Size:</span>
                <select wire:model.live="perPage" class="px-2 py-1 border rounded dark:bg-gray-700 dark:border-gray-600">
                    @foreach([25,50,100] as $size)
                    <option value="{{ $size }}">{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div>{{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }}</div>
            <div>{{ $leads->links() }}</div>
        </div>
    </div>
    @endif

    {{-- Advanced Filter Modal --}}
    @if($showFilterModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Advanced Filters</h3>
                <button wire:click="$set('showFilterModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            {{-- Category tabs --}}
            <div class="flex gap-1 mb-4 border-b dark:border-gray-700 overflow-x-auto">
                @foreach([
                    'basic' => 'Basic Filters',
                    'team' => 'Team & Assignment',
                    'call' => 'Call & Activity',
                    'tasks' => 'Tasks & Custom Fields',
                    'products' => 'Products',
                ] as $cat => $catLabel)
                <button wire:click="$set('filterCategory', '{{ $cat }}')" class="px-3 py-2 text-xs whitespace-nowrap border-b-2 {{ $filterCategory === $cat ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500' }}">
                    {{ $catLabel }}
                </button>
                @endforeach
            </div>

            @if($filterCategory === 'basic')
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500">Sort by</label>
                    <select wire:model="sortBy" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="latest">Newest first</option>
                        <option value="oldest">Oldest first</option>
                        <option value="name">Name A-Z</option>
                        <option value="value">Highest value</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="text-xs text-gray-500">Date from</label><input type="date" wire:model="filterDateFrom" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></div>
                    <div><label class="text-xs text-gray-500">Date to</label><input type="date" wire:model="filterDateTo" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></div>
                </div>
                <div><label class="text-xs text-gray-500">Source</label>
                    <select wire:model="filterSource" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">All</option>
                        @foreach(['manual','website','meta','google','whatsapp','referral','bulk_upload','phone_book'] as $src)
                        <option value="{{ $src }}">{{ ucfirst(str_replace('_',' ',$src)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="text-xs text-gray-500">Status</label>
                    <select wire:model="filterStage" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">All</option>
                        @foreach($stages as $stage)<option value="{{ $stage->id }}">{{ $stage->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="text-xs text-gray-500">Label</label>
                    <select wire:model="filterLabel" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">All</option>
                        @foreach($labels as $label)<option value="{{ $label->id }}">{{ $label->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            @endif

            @if($filterCategory === 'team')
            <div class="space-y-3">
                <div><label class="text-xs text-gray-500">Assigned to</label>
                    <select wire:model="filterAssignee" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">All</option>
                        @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="text-xs text-gray-500">Created by</label>
                    <select wire:model="filterCreatedBy" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">All</option>
                        @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            @endif

            @if($filterCategory === 'call')
            <div class="space-y-3">
                <div><label class="text-xs text-gray-500">Has call logged?</label>
                    <select wire:model="filterHasCall" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Any</option>
                        <option value="yes">Yes — call done</option>
                        <option value="no">No — never called</option>
                    </select>
                </div>
            </div>
            @endif

            @if($filterCategory === 'tasks')
            <div class="space-y-3">
                <div><label class="text-xs text-gray-500">Has pending task?</label>
                    <select wire:model="filterHasTask" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Any</option>
                        <option value="yes">Yes — has task</option>
                        <option value="no">No — no task</option>
                    </select>
                </div>
                <div><label class="text-xs text-gray-500">Service Type</label>
                    <input type="text" wire:model="filterServiceType" placeholder="e.g. GST, Website..." class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div><label class="text-xs text-gray-500">Priority</label>
                    <select wire:model="filterPriority" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        <option value="">Any</option>
                        @foreach(['low','medium','high'] as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
                    </select>
                </div>
            </div>
            @endif

            @if($filterCategory === 'products')
            <div class="space-y-3">
                <div><label class="text-xs text-gray-500">Service / Product Type</label>
                    <input type="text" wire:model="filterServiceType" placeholder="e.g. GST Filing, Website..." class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            @endif

            <div class="flex gap-2 mt-6">
                <button wire:click="resetFilters" class="px-4 py-2 border rounded-lg text-sm">Reset</button>
                <input type="text" wire:model="savedFilterName" placeholder="Save as..." class="flex-1 px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
                <button wire:click="saveCurrentFilter" class="px-4 py-2 border rounded-lg text-sm">Save Filter</button>
                <button wire:click="$set('showFilterModal', false)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Apply Filters</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit Columns Modal --}}
    @if($showColumnsModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-sm p-6">
            <h3 class="font-bold mb-4">Edit Columns</h3>
            <div class="space-y-2">
                @foreach($columnOptions as $key => $label)
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" wire:model="visibleColumns" value="{{ $key }}" class="rounded">
                    {{ $label }}
                </label>
                @endforeach
            </div>
            <div class="flex gap-2 mt-4">
                <button wire:click="$set('showColumnsModal', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
                <button wire:click="saveColumns" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Save</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Create Label Modal --}}
    @if($showLabelModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-sm p-6">
            <h3 class="font-bold mb-4">Manage Labels</h3>
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($labels as $lbl)
                <span class="px-3 py-1 rounded-full text-xs font-medium" style="background:{{ $lbl->color }}22;color:{{ $lbl->color }}">{{ $lbl->name }}</span>
                @endforeach
            </div>
            <div class="space-y-3">
                <input type="text" wire:model="newLabelName" placeholder="New label name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input type="color" wire:model="newLabelColor" class="w-full h-10 rounded cursor-pointer">
            </div>
            <div class="flex gap-2 mt-4">
                <button wire:click="$set('showLabelModal', false)" class="px-4 py-2 border rounded-lg text-sm">Close</button>
                <button wire:click="createLabel" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add Label</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Simple Saved Filters popup --}}
    @if($showSimpleFilterModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold">Saved Filters</h3>
                <button wire:click="$set('showSimpleFilterModal', false)" class="text-gray-400">✕</button>
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500">Condition</label>
                <select wire:model="simpleFilterCondition" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                    <option value="and">Match all conditions (AND)</option>
                    <option value="or">Match any condition (OR)</option>
                </select>
            </div>
            <div class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                @forelse($savedFilters as $i => $sf)
                <button wire:click="applySavedFilter({{ $i }})" class="w-full text-left px-3 py-2 border rounded-lg text-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/20">{{ $sf['name'] }}</button>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">No saved filters. Use Advanced Filters → Save Filter.</p>
                @endforelse
            </div>
            <div class="flex gap-2">
                <button wire:click="resetFilters" class="px-4 py-2 border rounded-lg text-sm">Clear</button>
                <button wire:click="$set('showSimpleFilterModal', false)" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Apply</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Add Lead List Modal --}}
    @if($showLeadListModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-sm p-6">
            <h3 class="font-bold mb-4">Add new list</h3>
            <input wire:model="newLeadListName" placeholder="List name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mb-4">
            <div class="flex gap-2">
                <button wire:click="$set('showLeadListModal', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
                <button wire:click="createLeadList" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Create</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Onboarding: Make It Yours --}}
    @if($showOnboarding)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md p-6 text-center">
            <div class="text-4xl mb-3">🎯</div>
            <h3 class="font-bold text-xl mb-2">Make It Yours</h3>
            <p class="text-sm text-gray-500 mb-4">Customize your lead list columns, save filters, and organize leads into lists — just like 3Sigma CRM.</p>
            <div class="flex gap-2">
                <button wire:click="dismissOnboarding" class="flex-1 px-4 py-2 border rounded-lg text-sm">Got it</button>
                <button wire:click="$set('showColumnsModal', true); dismissOnboarding()" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Edit Columns</button>
            </div>
            <label class="flex items-center justify-center gap-2 mt-3 text-xs text-gray-500 cursor-pointer">
                <input type="checkbox" wire:click="dismissOnboarding" class="rounded"> Don't show again
            </label>
        </div>
    </div>
    @endif

    {{-- Bulk Modal --}}
    @if($showBulkModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md p-6">
            <h3 class="font-bold mb-4">Bulk Action — {{ count($selected) }} leads</h3>
            @if($bulkAction === 'stage')
            <select wire:model="bulkStageId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mb-4">
                <option value="">Select stage...</option>
                @foreach($stages as $stage)<option value="{{ $stage->id }}">{{ $stage->name }}</option>@endforeach
            </select>
            @elseif($bulkAction === 'label')
            <select wire:model="bulkLabelId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mb-4">
                <option value="">No label</option>
                @foreach($labels as $label)<option value="{{ $label->id }}">{{ $label->name }}</option>@endforeach
            </select>
            @elseif($bulkAction === 'assign')
            <select wire:model="bulkAssigneeId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 mb-4">
                <option value="">Select user...</option>
                @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
            </select>
            @elseif($bulkAction === 'delete')
            <p class="text-red-600 text-sm mb-4">Selected leads will be permanently deleted.</p>
            @endif
            <div class="flex gap-2">
                <button wire:click="$set('showBulkModal', false)" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button wire:click="runBulkAction" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg">Apply</button>
            </div>
        </div>
    </div>
    @endif
</div>
