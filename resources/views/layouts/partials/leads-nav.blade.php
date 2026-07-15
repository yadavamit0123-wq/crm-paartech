@php
    $user = auth()->user();
    $tabs = [
        ['route' => 'leads.dashboard', 'label' => 'Dashboard', 'match' => 'leads.dashboard'],
        ['route' => 'leads.list', 'label' => 'Lead List', 'match' => 'leads.list|leads.create|leads.show|leads.bulk-upload'],
        ['route' => 'leads.forms', 'label' => 'Forms', 'match' => 'leads.forms'],
        ['route' => 'leads.inbox', 'label' => 'Inbox', 'match' => 'leads.inbox', 'permission' => 'inbox.view'],
        ['route' => 'leads.tasks', 'label' => 'Tasks', 'match' => 'leads.tasks'],
        ['route' => 'leads.auto-dialer', 'label' => 'Dialer', 'match' => 'leads.auto-dialer'],
        ['route' => 'leads.products', 'label' => 'Products', 'match' => 'leads.products', 'permission' => 'products.manage'],
        ['route' => 'leads.orders', 'label' => 'Orders', 'match' => 'leads.orders*'],
        ['route' => 'leads.documents', 'label' => 'Quotes', 'match' => 'leads.documents*', 'permission' => 'documents.view'],
        ['route' => 'leads.templates', 'label' => 'Templates', 'match' => 'leads.templates'],
        ['route' => 'leads.broadcasts', 'label' => 'Broadcasts', 'match' => 'leads.broadcasts'],
        ['route' => 'leads.automations', 'label' => 'Automation', 'match' => 'leads.automations', 'permission' => 'automations.manage'],
        ['route' => 'leads.bots', 'label' => 'Bots', 'match' => 'leads.bots*', 'permission' => 'bots.manage'],
        ['route' => 'leads.reports', 'label' => 'Reports', 'match' => 'leads.reports|leads.call-logs'],
        ['route' => 'leads.customers', 'label' => 'Customers', 'match' => 'leads.customers*'],
        ['route' => 'leads.lead-sources', 'label' => 'Sources', 'match' => 'leads.lead-sources'],
        ['route' => 'leads.settings', 'label' => 'Settings', 'match' => 'leads.settings|leads.custom-fields|leads.stages|leads.team|leads.labels'],
    ];
@endphp
<div class="mb-5 -mx-4 lg:-mx-6 px-4 lg:px-6 pt-1 pb-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-[49px] z-20">
    <nav class="flex gap-0.5 overflow-x-auto scrollbar-thin" aria-label="Leads CRM sections">
        @foreach($tabs as $tab)
            @if(Route::has($tab['route']) && (empty($tab['permission']) || $user?->hasPermission($tab['permission'])))
            @php $active = request()->routeIs($tab['match']); @endphp
            <a href="{{ route($tab['route']) }}"
               class="whitespace-nowrap px-3.5 py-2.5 text-[13px] font-medium border-b-2 transition-colors {{ $active ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300' }}">
                {{ $tab['label'] }}
            </a>
            @endif
        @endforeach
    </nav>
</div>
