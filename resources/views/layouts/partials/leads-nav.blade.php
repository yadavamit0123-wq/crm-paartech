@php
    $tabs = [
        ['route' => 'leads.dashboard', 'label' => 'Dashboard', 'match' => 'leads.dashboard'],
        ['route' => 'leads.list', 'label' => 'Lead List', 'match' => 'leads.list|leads.create|leads.show|leads.bulk-upload|leads.forms'],
        ['route' => 'leads.forms', 'label' => 'Forms', 'match' => 'leads.forms'],
        ['route' => 'leads.inbox', 'label' => 'Inbox', 'match' => 'leads.inbox'],
        ['route' => 'leads.tasks', 'label' => 'Tasks', 'match' => 'leads.tasks'],
        ['route' => 'leads.auto-dialer', 'label' => 'Dialer', 'match' => 'leads.auto-dialer'],
        ['route' => 'leads.products', 'label' => 'Products', 'match' => 'leads.products'],
        ['route' => 'leads.orders', 'label' => 'Orders', 'match' => 'leads.orders*'],
        ['route' => 'leads.documents', 'label' => 'Quotes', 'match' => 'leads.documents*'],
        ['route' => 'leads.templates', 'label' => 'Templates', 'match' => 'leads.templates'],
        ['route' => 'leads.broadcasts', 'label' => 'Broadcasts', 'match' => 'leads.broadcasts'],
        ['route' => 'leads.automations', 'label' => 'Automation', 'match' => 'leads.automations'],
        ['route' => 'leads.bots', 'label' => 'Bots', 'match' => 'leads.bots*'],
        ['route' => 'leads.reports', 'label' => 'Reports', 'match' => 'leads.reports|leads.call-logs'],
        ['route' => 'leads.customers', 'label' => 'Customers', 'match' => 'leads.customers*'],
        ['route' => 'leads.lead-sources', 'label' => 'Sources', 'match' => 'leads.lead-sources'],
        ['route' => 'leads.settings', 'label' => 'Settings', 'match' => 'leads.settings|leads.custom-fields|leads.stages|leads.team'],
    ];
@endphp
<div class="mb-4 -mx-1">
    <div class="flex items-center gap-2 mb-3">
        <h2 class="text-lg font-bold text-indigo-600">Leads CRM</h2>
        <span class="text-xs text-gray-400">3Sigma-style hub</span>
    </div>
    <div class="flex gap-1 overflow-x-auto pb-2 scrollbar-thin">
        @foreach($tabs as $tab)
            @if(Route::has($tab['route']))
            <a href="{{ route($tab['route']) }}"
               class="whitespace-nowrap px-3 py-1.5 rounded-lg text-sm transition {{ request()->routeIs($tab['match']) ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'bg-white dark:bg-gray-800 border dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' }}">
                {{ $tab['label'] }}
            </a>
            @endif
        @endforeach
    </div>
</div>
