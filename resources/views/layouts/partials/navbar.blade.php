<header class="sticky top-0 z-30 bg-white/90 dark:bg-gray-800/90 backdrop-blur border-b border-gray-200 dark:border-gray-700 px-4 py-2.5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
            <h2 class="text-[15px] font-semibold hidden sm:block">@yield('title', 'CRM')</h2>
        </div>
        <div class="flex items-center gap-1.5">
            @if(!auth()->user()?->isSuperAdmin())
                @livewire('notification-bell')
            @endif
            <button @click="dark = !dark; localStorage.setItem('theme', dark ? 'dark' : 'light')" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Toggle theme">
                <svg x-show="!dark" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                <svg x-show="dark" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
            </button>
            <div x-data="{ userMenu: false }" class="relative ml-1">
                <button @click="userMenu = !userMenu" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-medium hidden sm:block">{{ auth()->user()->name }}</span>
                    <svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="userMenu" x-cloak @click.away="userMenu = false" class="absolute right-0 mt-2 w-52 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl shadow-xl py-1.5 z-50">
                    <div class="px-4 py-2 border-b dark:border-gray-700">
                        <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    @if(Route::has('leads.settings') && auth()->user()->hasPermission('settings.manage'))
                    <a href="{{ route('leads.settings') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700">Settings</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
