<?php

namespace App\Livewire;

use App\Models\LeadReminder;
use Livewire\Component;

class NotificationBell extends Component
{
    public array $reminders = [];
    public int $count = 0;

    public function mount(): void
    {
        $this->loadReminders();
    }

    public function loadReminders(): void
    {
        $this->reminders = LeadReminder::with('lead')
            ->where('user_id', auth()->id())
            ->where('is_completed', false)
            ->where('remind_at', '<=', now())
            ->orderBy('remind_at')
            ->limit(10)
            ->get()
            ->toArray();

        $this->count = count($this->reminders);

        if ($this->count > 0) {
            $this->dispatch('play-notification-sound');
        }
    }

    public function complete(int $reminderId): void
    {
        LeadReminder::where('id', $reminderId)->where('user_id', auth()->id())
            ->update(['is_completed' => true]);
        $this->loadReminders();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
