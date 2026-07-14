<?php

namespace App\Livewire\CustomFields;

use App\Models\CustomField;
use Illuminate\Support\Str;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public string $label = '';
    public string $fieldType = 'text';
    public string $entityType = 'lead';
    public bool $isRequired = false;
    public string $options = '';

    public function openCreate(): void
    {
        $this->reset(['label', 'options']);
        $this->fieldType = 'text';
        $this->entityType = 'lead';
        $this->isRequired = false;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['label' => 'required|string|max:100']);

        CustomField::create([
            'tenant_id' => auth()->user()->tenant_id,
            'entity_type' => $this->entityType,
            'label' => $this->label,
            'field_key' => Str::slug($this->label, '_'),
            'field_type' => $this->fieldType,
            'options' => $this->options ? array_map('trim', explode(',', $this->options)) : null,
            'is_required' => $this->isRequired,
        ]);

        $this->showModal = false;
        $this->dispatch('notify', message: 'Custom field created');
    }

    public function delete(int $id): void
    {
        CustomField::findOrFail($id)->delete();
    }

    public function render()
    {
        $fields = CustomField::orderBy('sort_order')->get();

        return view('livewire.custom-fields.index', compact('fields'))
            ->layout('layouts.app');
    }
}
