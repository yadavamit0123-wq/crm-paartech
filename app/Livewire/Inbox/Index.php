<?php

namespace App\Livewire\Inbox;

use App\Models\Lead;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\AutomationService;
use Livewire\Component;

class Index extends Component
{
    public string $view = 'inbox';
    public ?int $activeConversationId = null;
    public string $replyMessage = '';
    public string $search = '';

    public function selectConversation(int $id): void
    {
        $this->activeConversationId = $id;
        WhatsappConversation::where('id', $id)->update(['unread_count' => 0]);
    }

    public function togglePin(int $id): void
    {
        $conv = WhatsappConversation::findOrFail($id);
        $conv->update(['is_pinned' => ! $conv->is_pinned]);
    }

    public function sendReply(): void
    {
        if (! $this->activeConversationId || ! $this->replyMessage) {
            return;
        }

        $conv = WhatsappConversation::findOrFail($this->activeConversationId);

        WhatsappMessage::create([
            'tenant_id' => auth()->user()->tenant_id,
            'conversation_id' => $conv->id,
            'user_id' => auth()->id(),
            'direction' => 'outbound',
            'body' => $this->replyMessage,
        ]);

        $conv->update(['last_message_at' => now()]);
        if ($conv->lead) {
            $conv->lead->update(['last_contacted_at' => now()]);
            $conv->lead->logActivity('whatsapp', 'Inbox reply sent', $this->replyMessage);
        }

        $this->replyMessage = '';
        $this->dispatch('notify', message: 'Message sent');
    }

    public function simulateInbound(int $conversationId, string $body): void
    {
        $conv = WhatsappConversation::findOrFail($conversationId);

        WhatsappMessage::create([
            'tenant_id' => auth()->user()->tenant_id,
            'conversation_id' => $conv->id,
            'direction' => 'inbound',
            'body' => $body,
        ]);

        $conv->update(['last_message_at' => now(), 'unread_count' => 0]);

        $botReply = app(AutomationService::class)->matchBotReply($body, auth()->user()->tenant_id);
        if ($botReply) {
            WhatsappMessage::create([
                'tenant_id' => auth()->user()->tenant_id,
                'conversation_id' => $conv->id,
                'direction' => 'outbound',
                'body' => $botReply,
            ]);
            if ($conv->lead) {
                $conv->lead->logActivity('whatsapp', 'Bot auto-reply', $botReply);
                app(AutomationService::class)->runTrigger('whatsapp_received', $conv->lead, ['message' => $body]);
            }
        }
    }

    public function connectChannel(string $channel): void
    {
        if (! in_array($channel, ['whatsapp', 'instagram', 'messenger'])) {
            return;
        }

        $tenant = auth()->user()->tenant;
        $settings = $tenant->settings ?? [];
        $settings["{$channel}_connected"] = true;
        $tenant->update(['settings' => $settings]);

        if ($channel === 'whatsapp') {
            $this->view = 'inbox';
            $this->dispatch('notify', message: 'WhatsApp Business API connected');
        } else {
            $this->dispatch('notify', message: ucfirst($channel).' connected');
        }
    }

    public function render()
    {
        $tenant = auth()->user()->tenant;
        $whatsappConnected = ! empty($tenant->settings['whatsapp_connected']);
        $instagramConnected = ! empty($tenant->settings['instagram_connected']);
        $messengerConnected = ! empty($tenant->settings['messenger_connected']);

        $conversations = WhatsappConversation::with(['lead', 'assignee'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('contact_name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_message_at')
            ->get();

        $active = $this->activeConversationId
            ? WhatsappConversation::with(['messages.user', 'lead', 'assignee'])->find($this->activeConversationId)
            : null;

        return view('livewire.inbox.index', compact('conversations', 'active', 'whatsappConnected', 'instagramConnected', 'messengerConnected'))
            ->layout('layouts.app');
    }
}
