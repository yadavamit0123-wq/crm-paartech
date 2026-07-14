<?php

namespace App\Livewire\Leads;

use App\Models\LeadForm;
use App\Models\LeadList;
use Illuminate\Support\Str;
use Livewire\Component;

class Forms extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public string $description = '';
    public ?int $leadListId = null;

    public function openCreate(): void
    {
        $this->reset(['name', 'description', 'leadListId']);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|string|max:100']);

        LeadForm::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            'name' => $this->name,
            'description' => $this->description ?: null,
            'lead_list_id' => $this->leadListId,
            'slug' => Str::slug($this->name).'-'.Str::random(4),
            'status' => 'active',
        ]);

        $this->showModal = false;
        $this->dispatch('notify', message: 'Form created');
    }

    public function toggleStatus(int $id): void
    {
        $form = LeadForm::findOrFail($id);
        $form->update(['status' => $form->status === 'active' ? 'inactive' : 'active']);
    }

    public function delete(int $id): void
    {
        LeadForm::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Form deleted');
    }

    public function render()
    {
        $forms = LeadForm::with('leadList')->latest()->get();
        $leadLists = LeadList::orderBy('name')->get();

        return view('livewire.leads.forms', compact('forms', 'leadLists'))
            ->layout('layouts.app');
    }
}
