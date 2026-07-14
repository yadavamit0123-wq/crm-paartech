<?php

namespace App\Livewire\Leads;

use App\Models\LeadLabel;
use Illuminate\Support\Str;
use Livewire\Component;

class Labels extends Component
{
    public bool $showModal = false;
    public ?int $editId = null;
    public string $name = '';
    public string $color = '#6366f1';
    public int $leadCount = 0;

    public array $presets = [
        '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
        '#22c55e', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1',
        '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#64748b',
    ];

    public function openCreate(): void
    {
        $this->editId = null;
        $this->name = '';
        $this->color = '#6366f1';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $label = LeadLabel::findOrFail($id);
        $this->editId = $label->id;
        $this->name = $label->name;
        $this->color = $label->color;
        $this->leadCount = $label->leads()->count();
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            abort(403);
        }

        $this->validate(['name' => 'required|string|max:50']);

        $slug = Str::slug($this->name);
        $tenantId = auth()->user()->tenant_id;

        if ($this->editId) {
            LeadLabel::findOrFail($this->editId)->update([
                'name' => $this->name,
                'color' => $this->color,
            ]);
            $this->dispatch('notify', message: 'Label updated');
        } else {
            $base = $slug;
            $i = 1;
            while (LeadLabel::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            LeadLabel::create([
                'tenant_id' => $tenantId,
                'name' => $this->name,
                'slug' => $slug,
                'color' => $this->color,
            ]);
            $this->dispatch('notify', message: 'Label created');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            return;
        }
        LeadLabel::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Label deleted');
    }

    public function render()
    {
        $labels = LeadLabel::withCount('leads')->orderBy('name')->get();

        return view('livewire.leads.labels', compact('labels'))
            ->layout('layouts.app');
    }
}
