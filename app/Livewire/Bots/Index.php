<?php

namespace App\Livewire\Bots;

use App\Models\WhatsappBot;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public string $description = '';
    public string $triggerKeyword = '';

    public function openCreate(): void
    {
        $this->reset(['name', 'description', 'triggerKeyword']);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|string|max:100']);

        $bot = WhatsappBot::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'trigger_keyword' => $this->triggerKeyword ?: null,
            'flow_data' => [
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Start'],
                    ['id' => 'welcome', 'type' => 'message', 'label' => 'Welcome Message', 'text' => 'Hi! How can we help you?'],
                ],
                'edges' => [['from' => 'start', 'to' => 'welcome']],
            ],
        ]);

        $this->showModal = false;
        $this->redirect(route('leads.bots.builder', $bot), navigate: true);
    }

    public function toggleActive(int $id): void
    {
        $bot = WhatsappBot::findOrFail($id);
        $bot->update(['is_active' => ! $bot->is_active]);
    }

    public function delete(int $id): void
    {
        WhatsappBot::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Bot deleted');
    }

    public function render()
    {
        $bots = WhatsappBot::latest()->get();

        return view('livewire.bots.index', compact('bots'))
            ->layout('layouts.app');
    }
}
