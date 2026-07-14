<?php

namespace App\Livewire\LeadStages;

use App\Models\LeadStage;
use Livewire\Component;

class Index extends Component
{
    public string $name = '';
    public string $color = '#6366f1';
    public ?int $editingId = null;
    public string $editName = '';
    public string $editColor = '#6366f1';

    public function save(): void
    {
        if (! auth()->user()->hasPermission('stages.manage')) {
            abort(403);
        }

        $this->validate(['name' => 'required|string|max:100', 'color' => 'required|string|max:20']);

        $slug = strtolower(str_replace(' ', '-', $this->name));
        $maxOrder = LeadStage::max('sort_order') ?? 0;

        LeadStage::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'slug' => $slug.'-'.time(),
            'color' => $this->color,
            'sort_order' => $maxOrder + 1,
        ]);

        $this->name = '';
        $this->color = '#6366f1';
        $this->dispatch('notify', message: 'Stage created / Stage ban gaya');
    }

    public function edit(int $id): void
    {
        $stage = LeadStage::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $stage->name;
        $this->editColor = $stage->color;
    }

    public function update(): void
    {
        if (! $this->editingId) {
            return;
        }

        LeadStage::findOrFail($this->editingId)->update([
            'name' => $this->editName,
            'color' => $this->editColor,
        ]);

        $this->editingId = null;
        $this->dispatch('notify', message: 'Stage updated');
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            LeadStage::where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    public function delete(int $id): void
    {
        $stage = LeadStage::findOrFail($id);
        if ($stage->leads()->count() > 0) {
            $this->dispatch('notify', message: 'Cannot delete stage with leads / Is stage me leads hain', type: 'error');

            return;
        }
        $stage->delete();
        $this->dispatch('notify', message: 'Stage deleted');
    }

    public function render()
    {
        $stages = LeadStage::withCount('leads')->orderBy('sort_order')->get();

        return view('livewire.lead-stages.index', compact('stages'))->layout('layouts.app');
    }
}
