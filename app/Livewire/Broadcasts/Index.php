<?php

namespace App\Livewire\Broadcasts;

use App\Models\Broadcast;
use App\Models\Lead;
use App\Models\MessageTemplate;
use Livewire\Component;

class Index extends Component
{
    public string $tab = 'current';
    public string $search = '';
    public bool $showWizard = false;
    public int $wizardStep = 1;
    public bool $showLimitsModal = false;

    public string $name = '';
    public string $channel = 'whatsapp';
    public string $sendFromPhone = '';
    public string $replyBot = '';
    public ?int $templateId = null;
    public string $message = '';
    public string $scheduleAt = '';
    public string $recipientFilter = 'all';

    public function openCreate(): void
    {
        $this->reset(['name', 'channel', 'templateId', 'message', 'scheduleAt', 'sendFromPhone', 'replyBot', 'recipientFilter']);
        $this->showWizard = true;
        $this->wizardStep = 1;
    }

    public function nextStep(): void
    {
        if ($this->wizardStep === 1) {
            $this->validate(['name' => 'required|string|max:100']);
        }
        $this->wizardStep = min(4, $this->wizardStep + 1);
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|string|max:100']);

        Broadcast::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            'name' => $this->name,
            'channel' => $this->channel,
            'template_id' => $this->templateId,
            'message' => $this->message ?: null,
            'status' => $this->scheduleAt ? 'scheduled' : 'draft',
            'scheduled_at' => $this->scheduleAt ?: null,
            'total_recipients' => Lead::whereNotNull('phone')->where('phone', '!=', '')->count(),
        ]);

        $this->showWizard = false;
        $this->wizardStep = 1;
        $this->dispatch('notify', message: 'Campaign created');
    }

    public function sendNow(int $id): void
    {
        $broadcast = Broadcast::findOrFail($id);
        $broadcast->update([
            'status' => 'sent',
            'sent_at' => now(),
            'delivered_count' => $broadcast->total_recipients,
            'opened_count' => 0,
        ]);
        $this->dispatch('notify', message: 'Broadcast queued (simulated send) — '.$broadcast->total_recipients.' recipients');
    }

    public function render()
    {
        $query = Broadcast::with('template')->latest();

        if ($this->tab === 'scheduled') {
            $query->where('status', 'scheduled');
        } else {
            $query->whereIn('status', ['draft', 'sent', 'cancelled']);
        }

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        $broadcasts = $query->get();
        $templates = MessageTemplate::where('is_active', true)->get();

        $metrics = [
            'emails_sent' => Broadcast::where('channel', 'email')->where('status', 'sent')->sum('total_recipients'),
            'emails_delivered' => Broadcast::where('channel', 'email')->where('status', 'sent')->sum('delivered_count'),
            'whatsapp_sent' => Broadcast::where('channel', 'whatsapp')->where('status', 'sent')->sum('total_recipients'),
            'last_7_days' => Broadcast::where('status', 'sent')->where('sent_at', '>=', now()->subDays(7))->count(),
        ];

        return view('livewire.broadcasts.index', compact('broadcasts', 'templates', 'metrics'))
            ->layout('layouts.app');
    }
}
