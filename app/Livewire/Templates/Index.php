<?php

namespace App\Livewire\Templates;

use App\Models\MessageTemplate;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public ?int $editId = null;
    public string $name = '';
    public string $channel = 'whatsapp';
    public string $category = '';
    public string $subject = '';
    public string $body = '';
    public bool $isActive = true;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $t = MessageTemplate::findOrFail($id);
        $this->editId = $t->id;
        $this->name = $t->name;
        $this->channel = $t->channel;
        $this->category = $t->category ?? '';
        $this->subject = $t->subject ?? '';
        $this->body = $t->body;
        $this->isActive = $t->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'channel' => 'required|in:whatsapp,email,sms',
            'body' => 'required|string',
        ]);

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'channel' => $this->channel,
            'category' => $this->category ?: null,
            'subject' => $this->subject ?: null,
            'body' => $this->body,
            'is_active' => $this->isActive,
        ];

        if ($this->editId) {
            MessageTemplate::findOrFail($this->editId)->update($data);
        } else {
            MessageTemplate::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify', message: 'Template saved');
    }

    public function delete(int $id): void
    {
        MessageTemplate::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Template deleted');
    }

    protected function resetForm(): void
    {
        $this->editId = null;
        $this->name = '';
        $this->channel = 'whatsapp';
        $this->category = '';
        $this->subject = '';
        $this->body = '';
        $this->isActive = true;
    }

    public function render()
    {
        $templates = MessageTemplate::latest()->get();

        return view('livewire.templates.index', compact('templates'))
            ->layout('layouts.app');
    }
}
