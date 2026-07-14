<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('theme') === 'dark', sidebarOpen: false }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Alpine is bundled with Livewire 3 — do NOT load a second Alpine CDN (breaks wire:click & uploads) --}}
    @livewireStyles
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        primary: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .kanban-col { min-height: 400px; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .scrollbar-thin::-webkit-scrollbar { height: 5px; }
        @keyframes toast-in { from { opacity: 0; transform: translateX(24px); } to { opacity: 1; transform: translateX(0); } }
        .toast-item { animation: toast-in .25s ease-out; }

        /* Click feedback: har clickable element press hone par dabta hua dikhe */
        a, button, [wire\:click], [role="button"] { transition: transform .08s ease, opacity .08s ease; }
        a:active, button:active, [wire\:click]:active, [role="button"]:active { transform: scale(.96); opacity: .8; }

        /* Global top progress bar (page navigation + livewire requests) */
        #page-progress {
            position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999;
            pointer-events: none; opacity: 0; transition: opacity .2s;
        }
        #page-progress.active { opacity: 1; }
        #page-progress .bar {
            height: 100%; width: 40%;
            background: linear-gradient(90deg, #6366f1, #8b5cf6, #6366f1);
            border-radius: 0 3px 3px 0;
            animation: progress-slide 1s ease-in-out infinite;
        }
        @keyframes progress-slide {
            0% { margin-left: -40%; }
            100% { margin-left: 100%; }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen font-sans antialiased">
    <div class="flex min-h-screen">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

        @include('layouts.partials.sidebar')
        <div class="flex-1 flex flex-col lg:ml-64 min-w-0">
            @include('layouts.partials.navbar')
            <main class="flex-1 p-4 lg:p-6">
                @if(session('success'))
                    <div class="mb-4 flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="toast-container" class="fixed top-4 right-4 z-[60] space-y-2 w-80 max-w-[calc(100vw-2rem)]"></div>
    <div id="page-progress"><div class="bar"></div></div>

    <audio id="notification-sound" preload="auto">
        <source src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAf16AAABAAgAZGF0YUtvT19XQVZFZm10IBAAAAABAAEAQB8AAEAf16AAABAAgAZGF0YU" type="audio/wav">
    </audio>

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', ({ message, type = 'success' }) => {
                const isError = type === 'error';
                const toast = document.createElement('div');
                toast.className = 'toast-item flex items-start gap-3 p-4 rounded-xl shadow-xl text-sm font-medium text-white ' + (isError ? 'bg-red-600' : 'bg-slate-900 dark:bg-slate-700');
                toast.innerHTML = (isError
                    ? '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>'
                    : '<svg class="w-5 h-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>')
                    + '<span></span>';
                toast.querySelector('span').textContent = message;
                document.getElementById('toast-container').appendChild(toast);
                setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(() => toast.remove(), 300); }, 4000);
            });

            Livewire.on('play-notification-sound', () => {
                const audio = document.getElementById('notification-sound');
                if (audio) { audio.currentTime = 0; audio.play().catch(() => {}); }
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('CRM Reminder', { body: 'You have pending follow-ups!' });
                }
            });

            Livewire.on('open-url', ({ url }) => {
                if (url) window.open(url, '_blank');
            });
        });

        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // ── Click/tap feedback: progress bar on navigation + livewire actions ──
        (function () {
            const progress = document.getElementById('page-progress');
            let hideTimer = null;
            const show = () => { clearTimeout(hideTimer); progress.classList.add('active'); };
            const hide = () => { hideTimer = setTimeout(() => progress.classList.remove('active'), 150); };

            // Full page navigations (normal links)
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a[href]');
                if (!link) return;
                const href = link.getAttribute('href') || '';
                if (href.startsWith('#') || link.target === '_blank' || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('sms:') || href.startsWith('javascript')) return;
                show();
            });
            window.addEventListener('pageshow', () => progress.classList.remove('active'));

            // Livewire request lifecycle (wire:click, wire:model updates, navigate)
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ succeed, fail }) => {
                    show();
                    succeed(hide);
                    fail(hide);
                });
            });
            document.addEventListener('livewire:navigating', show);
            document.addEventListener('livewire:navigated', () => progress.classList.remove('active'));
        })();
    </script>
</body>
</html>
