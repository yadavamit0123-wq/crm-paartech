<?php

namespace App\Livewire\Leads;

use Livewire\Component;

class AiAssistant extends Component
{
    public string $prompt = '';
    public array $messages = [];
    public string $context = 'whatsapp';

    public function mount(): void
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => 'Namaste! Main aapki CRM AI assistant hoon. Lead ke liye WhatsApp message, email draft, ya follow-up script likh sakti hoon. Kya chahiye?',
        ];
    }

    public function send(): void
    {
        if (! trim($this->prompt)) {
            return;
        }

        $userPrompt = trim($this->prompt);
        $this->messages[] = ['role' => 'user', 'content' => $userPrompt];
        $this->prompt = '';

        $response = $this->generateResponse($userPrompt, $this->context);
        $this->messages[] = ['role' => 'assistant', 'content' => $response];
    }

    protected function generateResponse(string $prompt, string $context): string
    {
        $templates = [
            'whatsapp' => "Hi {{name}},\n\nThank you for your interest! Based on your inquiry about \"{$prompt}\", I'd love to schedule a quick call to discuss how we can help.\n\nWhen would be a good time?\n\nBest regards",
            'email' => "Subject: Following up on your inquiry\n\nDear {{name}},\n\nThank you for reaching out regarding {$prompt}. We have helped many businesses like yours achieve great results.\n\nI'd be happy to share a customized proposal. Would you be available for a 15-minute call this week?\n\nWarm regards",
            'follow_up' => "Follow-up script:\n1. Greet warmly\n2. Reference: {$prompt}\n3. Ask about timeline\n4. Offer demo/callback\n5. Set next reminder",
        ];

        if (str_contains(strtolower($prompt), 'follow')) {
            return $templates['follow_up'];
        }

        return $templates[$context] ?? $templates['whatsapp'];
    }

    public function render()
    {
        return view('livewire.leads.ai-assistant')
            ->layout('layouts.app');
    }
}
