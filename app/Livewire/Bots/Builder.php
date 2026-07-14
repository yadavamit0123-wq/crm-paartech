<?php

namespace App\Livewire\Bots;

use App\Models\WhatsappBot;
use Livewire\Component;

class Builder extends Component
{
    public WhatsappBot $bot;
    public array $nodes = [];
    public array $edges = [];
    public string $newNodeType = 'message';
    public string $newNodeLabel = '';
    public string $newNodeText = '';
    public bool $showPreview = false;
    public bool $newLeadsOnly = false;
    public string $triggerType = 'keyword';

    public array $nodeTypes = [
        'start' => 'START',
        'button_question' => 'Button Question',
        'list' => 'List',
        'text_input' => 'Text Input',
        'email_input' => 'Email Input',
        'phone_input' => 'Phone Input',
        'number_input' => 'Number Input',
        'location' => 'Location',
        'image' => 'Image',
        'template' => 'Template Message',
        'message' => 'Send Message',
    ];

    public function mount(WhatsappBot $bot): void
    {
        $this->bot = $bot;
        $flow = $bot->flow_data ?? ['nodes' => [], 'edges' => []];
        $this->nodes = $flow['nodes'] ?? [];
        $this->edges = $flow['edges'] ?? [];
        $this->newLeadsOnly = $bot->new_leads_only ?? false;
        $this->triggerType = $flow['trigger_type'] ?? 'keyword';
    }

    public function addNode(): void
    {
        $this->validate(['newNodeLabel' => 'required|string|max:100']);

        $id = 'node_'.(count($this->nodes) + 1);
        $this->nodes[] = [
            'id' => $id,
            'type' => $this->newNodeType,
            'label' => $this->newNodeLabel,
            'text' => $this->newNodeText,
        ];

        if (count($this->nodes) > 1) {
            $prev = $this->nodes[count($this->nodes) - 2]['id'];
            $this->edges[] = ['from' => $prev, 'to' => $id];
        }

        $this->newNodeLabel = '';
        $this->newNodeText = '';
    }

    public function saveFlow(): void
    {
        $this->bot->update([
            'flow_data' => [
                'nodes' => $this->nodes,
                'edges' => $this->edges,
                'trigger_type' => $this->triggerType,
            ],
            'new_leads_only' => $this->newLeadsOnly,
        ]);
        $this->dispatch('notify', message: 'Bot flow saved');
    }

    public function toggleActive(): void
    {
        $this->saveFlow();
        $this->bot->update(['is_active' => ! $this->bot->is_active]);
        $this->dispatch('notify', message: $this->bot->is_active ? 'Bot activated' : 'Bot deactivated');
    }

    public function render()
    {
        return view('livewire.bots.builder')
            ->layout('layouts.app');
    }
}
