<div wire:poll.30s="refreshDue">
    @if(count($due))
    <div class="fixed top-0 inset-x-0 z-50 shadow-lg" role="alert">
        <div class="bg-gradient-to-r from-red-600 via-red-500 to-amber-500 text-white px-4 py-3">
            <div class="max-w-5xl mx-auto">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                        </span>
                        🔔 Follow-up due hai! ({{ count($due) }})
                        @if(!$soundEnabled)
                        <span class="text-xs font-normal opacity-80">(sound off — Settings me on karein)</span>
                        @endif
                    </div>
                    <button wire:click="snoozeAll" class="text-xs font-semibold bg-white/20 hover:bg-white/30 rounded-full px-3 py-1">
                        Snooze All 10 min
                    </button>
                </div>
                <div class="mt-2 space-y-1.5">
                    @foreach($due as $item)
                    <div class="flex items-center justify-between gap-2 bg-white/15 rounded-xl px-3 py-2 flex-wrap" wire:key="due-{{ $item['id'] }}">
                        <div class="text-sm min-w-0">
                            <a href="{{ route('leads.show', $item['lead_id']) }}" class="font-semibold underline underline-offset-2 hover:opacity-90">{{ $item['lead_name'] }}</a>
                            <span class="opacity-90">— {{ $item['title'] }}</span>
                            <span class="text-xs opacity-75 ml-1">({{ ucfirst(str_replace('_', ' ', $item['type'])) }} • {{ $item['remind_at'] }})</span>
                        </div>
                        <div class="flex gap-1.5 shrink-0">
                            <button wire:click="snooze({{ $item['id'] }})" class="text-xs font-semibold bg-white/20 hover:bg-white/30 rounded-lg px-2.5 py-1">Snooze 10 min</button>
                            <button wire:click="markDone({{ $item['id'] }})" class="text-xs font-semibold bg-white text-red-600 hover:bg-red-50 rounded-lg px-2.5 py-1">✓ Mark Done</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        // Continuous ring: jab tak banner visible hai, har ~2s me beep pattern bajta rahega.
        let followupRingTimer = null;

        const followupPlay = () => {
            try {
                if (window.crmAudio) window.crmAudio.beep();
            } catch (e) { /* autoplay blocked — first user click ke baad chalega */ }
        };

        const followupStart = () => {
            if (followupRingTimer) return;
            followupPlay();
            followupRingTimer = setInterval(followupPlay, 2000);
        };

        const followupStop = () => {
            if (followupRingTimer) {
                clearInterval(followupRingTimer);
                followupRingTimer = null;
            }
        };

        $wire.on('followup-ring', ({ ring }) => {
            if (ring) { followupStart(); } else { followupStop(); }
        });
    </script>
    @endscript
</div>
