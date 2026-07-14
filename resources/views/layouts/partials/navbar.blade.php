<header class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h2 class="text-lg font-semibold hidden sm:block">@yield('title', 'CRM')</h2>
        </div>
        <div class="flex items-center gap-3">
            @if(!auth()->user()?->isSuperAdmin())
                @livewire('notification-bell')
            @endif
            <button @click="dark = !dark; localStorage.setItem('theme', dark ? 'dark' : 'light')" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <span x-show="!dark">🌙</span>
                <span x-show="dark">☀️</span>
            </button>
            <div class="flex items-center gap-2">
                <span class="text-sm hidden sm:block">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>
