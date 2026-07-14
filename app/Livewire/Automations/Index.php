<?php

namespace App\Livewire\Automations;

use App\Models\Automation;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';
    public bool $showWizard = false;
    public int $wizardStep = 1;
    public ?int $editId = null;
    public string $name = '';
    public string $triggerType = '';
    public string $actionType = '';
    public string $actionMessage = '';
    public bool $isActive = true;
    public bool $isDraft = false;
    public array $dayActions = [];

    public array $triggers = [
        'lead_created' => 'New lead created',
        'lead_edited' => 'Lead edited',
        'stage_changed' => 'Status updated',
        'label_updated' => 'Label updated',
        'lead_list_added' => 'New lead added to list',
        'time_based' => 'Time-based triggers',
        'no_call_24h' => 'No call in 24 hours',
        'whatsapp_received' => 'WhatsApp message received',
    ];

    public array $actionGroups = [
        'Organize & Qualify' => [
            'change_stage' => 'Change status',
            'change_label' => 'Change label',
            'assign_user' => 'Assign user',
        ],
        'Communicate' => [
            'send_whatsapp' => 'Send WhatsApp',
            'send_email' => 'Send email',
            'push_notification' => 'Push notification',
        ],
        'Tasks' => [
            'create_task' => 'Create task',
        ],
    ];

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showWizard = true;
        $this->wizardStep = 1;
    }

    public function selectTrigger(string $type): void
    {
        $this->triggerType = $type;
        $this->wizardStep = 2;
    }

    public function selectAction(string $type): void
    {
        $this->actionType = $type;
        $this->wizardStep = 3;
        if (empty($this->dayActions)) {
            $this->dayActions = [['day' => 1, 'action' => $type, 'message' => '']];
        }
    }

    public function addDay(): void
    {
        $lastDay = end($this->dayActions)['day'] ?? 1;
        $this->dayActions[] = ['day' => $lastDay + 1, 'action' => $this->actionType ?: 'send_whatsapp', 'message' => ''];
    }

    public function save(bool $asDraft = false): void
    {
        $this->validate(['name' => 'required|string|max:100', 'triggerType' => 'required']);

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'trigger_type' => $this->triggerType,
            'trigger_config' => [],
            'actions' => [['type' => $this->actionType ?: 'send_whatsapp', 'message' => $this->actionMessage]],
            'day_actions' => $this->dayActions,
            'is_active' => ! $asDraft,
            'is_draft' => $asDraft,
        ];

        if ($this->editId) {
            Automation::findOrFail($this->editId)->update($data);
        } else {
            Automation::create($data);
        }

        $this->showWizard = false;
        $this->resetForm();
        $this->dispatch('notify', message: $asDraft ? 'Saved as draft' : 'Automation created');
    }

    public function edit(int $id): void
    {
        $a = Automation::findOrFail($id);
        $this->editId = $a->id;
        $this->name = $a->name;
        $this->triggerType = $a->trigger_type;
        $this->actionType = $a->actions[0]['type'] ?? '';
        $this->actionMessage = $a->actions[0]['message'] ?? '';
        $this->dayActions = $a->day_actions ?? [];
        $this->isActive = $a->is_active;
        $this->showWizard = true;
        $this->wizardStep = 3;
    }

    public function toggleActive(int $id): void
    {
        $a = Automation::findOrFail($id);
        $a->update(['is_active' => ! $a->is_active, 'is_draft' => false]);
    }

    public function runNow(int $id): void
    {
        $automation = Automation::findOrFail($id);
        $lead = \App\Models\Lead::latest()->first();
        if (! $lead) {
            $this->dispatch('notify', message: 'No leads to test with', type: 'error');

            return;
        }
        app(\App\Services\AutomationService::class)->execute($automation, $lead);
        $automation->update([
            'runs_count' => $automation->runs_count + 1,
            'last_run_at' => now(),
            'completed_count' => $automation->completed_count + 1,
            'leads_affected' => $automation->leads_affected + 1,
        ]);
        $this->dispatch('notify', message: 'Automation ran on '.$lead->name);
    }

    public function delete(int $id): void
    {
        Automation::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Automation deleted');
    }

    protected function resetForm(): void
    {
        $this->editId = null;
        $this->name = '';
        $this->triggerType = '';
        $this->actionType = '';
        $this->actionMessage = '';
        $this->dayActions = [];
        $this->isActive = true;
        $this->wizardStep = 1;
    }

    public function render()
    {
        $automations = Automation::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->get();

        return view('livewire.automations.index', compact('automations'))
            ->layout('layouts.app');
    }
}
