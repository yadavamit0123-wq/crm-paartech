<div wire:poll.60s="loadReminders">
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            🔔
            @if($count > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ $count }}</span>
            @endif
        </button>
        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border dark:border-gray-700 z-50">
            <div class="p-3 border-b dark:border-gray-700 font-semibold text-sm">Reminders / याद-dasht</div>
            <div class="max-h-64 overflow-y-auto">
                @forelse($reminders as $reminder)
                <div class="p-3 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div class="font-medium text-sm">{{ $reminder['title'] }}</div>
                    <div class="text-xs text-gray-500">{{ $reminder['lead']['name'] ?? '' }} • {{ \Carbon\Carbon::parse($reminder['remind_at'])->diffForHumans() }}</div>
                    <button wire:click="complete({{ $reminder['id'] }})" class="text-xs text-green-600 mt-1">✓ Done</button>
                </div>
                @empty
                <div class="p-4 text-sm text-gray-500 text-center">No pending reminders</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
