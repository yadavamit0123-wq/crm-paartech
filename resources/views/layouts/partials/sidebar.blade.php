@php
    $user = auth()->user();
    $navItem = 'group flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-colors';
    $navActive = 'bg-indigo-600/90 text-white shadow-sm';
    $navIdle = 'text-slate-300 hover:bg-white/10 hover:text-white';
@endphp
<aside class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 transform transition-transform overflow-y-auto flex flex-col"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="px-5 pt-5 pb-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-indigo-900/40">
                {{ strtoupper(substr(config('app.name'), 0, 2)) }}
            </div>
            <div class="min-w-0">
                <h1 class="text-[15px] font-bold text-white leading-tight truncate">{{ config('app.name') }}</h1>
                @if($user?->tenant)
                <p class="text-[11px] text-slate-400 truncate">{{ $user->tenant->name }}</p>
                @endif
            </div>
        </div>
    </div>

    <nav class="flex-1 p-3 space-y-0.5">
        @if($user?->isSuperAdmin())
            <a href="{{ route('super-admin.dashboard') }}" class="{{ $navItem }} {{ request()->routeIs('super-admin.*') ? $navActive : $navIdle }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                Super Admin
            </a>
        @else
            <p class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Workspace</p>

            @if($user?->hasPermission('dashboard.view'))
            <a href="{{ route('dashboard') }}" class="{{ $navItem }} {{ request()->routeIs('dashboard') && !request()->routeIs('leads.*') ? $navActive : $navIdle }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                Dashboard
            </a>
            @endif

            @if($user?->hasPermission('leads.view_own') || $user?->hasPermission('leads.view_all'))
            <a href="{{ route('leads.dashboard') }}" class="{{ $navItem }} {{ request()->routeIs('leads.*') ? $navActive : $navIdle }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                Leads CRM
                <span class="ml-auto text-[10px] font-semibold bg-indigo-400/20 text-indigo-200 px-1.5 py-0.5 rounded-md">Hub</span>
            </a>
            @endif

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Business</p>

            @if($user?->hasPermission('accounting.view'))
            <a href="{{ route('accounting.dashboard') }}" class="{{ $navItem }} {{ request()->routeIs('accounting.*') || request()->routeIs('expenses.*') || request()->routeIs('gst-reports.*') || request()->routeIs('payroll.*') ? $navActive : $navIdle }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Accounting
            </a>
            @endif

            @if($user?->hasPermission('marketing.view'))
            <a href="{{ route('marketing.dashboard') }}" class="{{ $navItem }} {{ request()->routeIs('marketing.*') || request()->routeIs('social-posts.*') || request()->routeIs('seo-audit.*') || request()->routeIs('ad-campaigns.*') ? $navActive : $navIdle }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46"/></svg>
                Marketing
            </a>
            @endif

            @if($user?->hasPermission('integrations.manage'))
            <a href="{{ route('integrations.index') }}" class="{{ $navItem }} {{ request()->routeIs('integrations.*') ? $navActive : $navIdle }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                Integrations
            </a>
            @endif
        @endif
    </nav>

    <div class="p-3 border-t border-white/10">
        <div class="flex items-center gap-3 px-2 py-2">
            <div class="w-8 h-8 rounded-full bg-indigo-500/30 text-indigo-200 flex items-center justify-center text-xs font-bold shrink-0">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[13px] font-medium text-white truncate">{{ $user->name }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ $user->role->name ?? ($user->isSuperAdmin() ? 'Super Admin' : 'Member') }}</p>
            </div>
        </div>
    </div>
</aside>
