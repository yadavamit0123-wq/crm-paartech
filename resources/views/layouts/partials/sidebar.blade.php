<aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform -translate-x-full lg:translate-x-0 transition-transform overflow-y-auto">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h1 class="text-xl font-bold text-primary-600">{{ config('app.name') }}</h1>
        @if(auth()->user()?->tenant)
            <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->tenant->name }}</p>
        @endif
    </div>
    <nav class="p-3 space-y-1 text-sm">
        @if(auth()->user()?->isSuperAdmin())
            <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('super-admin.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 font-medium' : '' }}">
                <span>🏢</span> Super Admin
            </a>
        @else
            @if(auth()->user()?->hasPermission('dashboard.view'))
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('dashboard') && !request()->routeIs('leads.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 font-medium' : '' }}">
                <span>📊</span> Dashboard
            </a>
            @endif

            @if(auth()->user()?->hasPermission('leads.view_own') || auth()->user()?->hasPermission('leads.view_all'))
            <a href="{{ route('leads.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('leads.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 font-medium' : '' }}">
                <span>👥</span> Leads CRM
                <span class="ml-auto text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded">Hub</span>
            </a>
            @endif

            @if(auth()->user()?->hasPermission('accounting.view'))
            <a href="{{ route('accounting.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('accounting.*') || request()->routeIs('expenses.*') || request()->routeIs('gst-reports.*') || request()->routeIs('payroll.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 font-medium' : '' }}">
                <span>📒</span> Accounting
            </a>
            @endif

            @if(auth()->user()?->hasPermission('marketing.view'))
            <a href="{{ route('marketing.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('marketing.*') || request()->routeIs('social-posts.*') || request()->routeIs('seo-audit.*') || request()->routeIs('ad-campaigns.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 font-medium' : '' }}">
                <span>📣</span> Marketing
            </a>
            @endif

            @if(auth()->user()?->hasPermission('integrations.manage'))
            <a href="{{ route('integrations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('integrations.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 font-medium' : '' }}">
                <span>🔗</span> Integrations
            </a>
            @endif
        @endif
    </nav>
</aside>
