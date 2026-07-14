<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Alpine is bundled with Livewire 3 — do NOT load a second Alpine CDN (breaks wire:click & uploads) --}}
    @livewireStyles
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .kanban-col { min-height: 400px; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        @include('layouts.partials.sidebar')
        <div class="flex-1 flex flex-col lg:ml-64">
            @include('layouts.partials.navbar')
            <main class="flex-1 p-4 lg:p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg">{{ session('success') }}</div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <audio id="notification-sound" preload="auto">
        <source src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAf16AAABAAgAZGF0YUtvT19XQVZFZm10IBAAAAABAAEAQB8AAEAf16AAABAAgAZGF0YU" type="audio/wav">
    </audio>

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', ({ message, type = 'success' }) => {
                const toast = document.createElement('div');
                toast.className = `p-4 rounded-lg shadow-lg text-white ${type === 'error' ? 'bg-red-500' : 'bg-green-500'}`;
                toast.textContent = message;
                document.getElementById('toast-container').appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
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

        setInterval(() => {
            if (window.Livewire) {
                Livewire.dispatch('refreshNotifications');
            }
        }, 60000);
    </script>
</body>
</html>
