<?php

namespace App\Livewire\Notifications;

use App\Models\LeadReminder;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class FollowupAlert extends Component
{
    public array $due = [];
    public bool $soundEnabled = true;

    public function mount(): void
    {
        $this->refreshDue();
    }

    public function refreshDue(): void
    {
        $this->soundEnabled = $this->resolveSoundPreference();
        $this->due = $this->loadDue();
        $this->dispatch('followup-ring', ring: count($this->due) > 0 && $this->soundEnabled);
    }

    protected function resolveSoundPreference(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if (Schema::hasColumn('users', 'settings')) {
            $userSettings = $user->settings ?? [];
            if (array_key_exists('followup_sound', $userSettings)) {
                return (bool) $userSettings['followup_sound'];
            }
        }

        return (bool) ($user->tenant?->settings['followup_sound'] ?? true);
    }

    protected function loadDue(): array
    {
        if (! Schema::hasTable('lead_reminders')) {
            return [];
        }

        return LeadReminder::with('lead:id,name')
            ->where('user_id', auth()->id())
            ->where('is_completed', false)
            ->where('remind_at', '<=', now())
            ->orderBy('remind_at')
            ->limit(5)
            ->get()
            ->map(fn ($reminder) => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'type' => $reminder->type ?? 'follow_up',
                'remind_at' => $reminder->remind_at->format('d M, h:i A'),
                'lead_id' => $reminder->lead_id,
                'lead_name' => $reminder->lead?->name ?? 'Lead',
            ])
            ->toArray();
    }

    public function snooze(int $reminderId): void
    {
        LeadReminder::where('id', $reminderId)
            ->where('user_id', auth()->id())
            ->update(['remind_at' => now()->addMinutes(10)]);

        $this->refreshDue();
        $this->dispatch('notify', message: 'Snoozed — 10 min baad phir yaad dilayenge');
    }

    public function markDone(int $reminderId): void
    {
        LeadReminder::where('id', $reminderId)
            ->where('user_id', auth()->id())
            ->update(['is_completed' => true]);

        $this->refreshDue();
        $this->dispatch('notify', message: 'Follow-up marked done');
    }

    public function snoozeAll(): void
    {
        LeadReminder::where('user_id', auth()->id())
            ->where('is_completed', false)
            ->where('remind_at', '<=', now())
            ->update(['remind_at' => now()->addMinutes(10)]);

        $this->refreshDue();
        $this->dispatch('notify', message: 'Sab follow-ups 10 min ke liye snooze ho gaye');
    }

    public function render()
    {
        return view('livewire.notifications.followup-alert');
    }
}
