<?php

namespace App\Livewire\Tasks;

use App\Models\CrmTask;
use App\Models\Lead;
use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public string $tab = 'today';
    public string $sortField = 'due_at';
    public string $sortDir = 'asc';
    public string $search = '';
    public bool $dayView = false;
    public bool $showModal = false;
    public string $title = '';
    public string $description = '';
    public string $dueAt = '';
    public string $priority = 'medium';
    public string $taskType = 'follow_up';
    public ?int $assigneeId = null;
    public ?int $leadId = null;

    public function openCreate(): void
    {
        $this->reset(['title', 'description', 'leadId']);
        $this->dueAt = now()->addDay()->format('Y-m-d\TH:i');
        $this->assigneeId = auth()->id();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['title' => 'required|string|max:255']);

        CrmTask::create([
            'tenant_id' => auth()->user()->tenant_id,
            'lead_id' => $this->leadId,
            'user_id' => $this->assigneeId ?? auth()->id(),
            'created_by' => auth()->id(),
            'title' => $this->title,
            'task_type' => $this->taskType,
            'description' => $this->description ?: null,
            'due_at' => $this->dueAt ?: null,
            'priority' => $this->priority,
        ]);

        $this->showModal = false;
        $this->dispatch('notify', message: 'Task created');
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'asc';
        }
    }

    public function complete(int $id): void
    {
        CrmTask::findOrFail($id)->update(['status' => 'completed', 'completed_at' => now()]);
        $this->dispatch('notify', message: 'Task completed');
    }

    public function delete(int $id): void
    {
        CrmTask::findOrFail($id)->delete();
    }

    protected function taskQuery()
    {
        $q = CrmTask::with(['lead', 'assignee']);

        if ($this->search) {
            $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhereHas('lead', fn ($lq) => $lq->where('name', 'like', "%{$this->search}%"));
            });
        }

        if ($this->dayView) {
            return $q->whereDate('due_at', today())->orderBy($this->sortField, $this->sortDir);
        }

        $query = match ($this->tab) {
            'today' => $q->where('status', 'pending')->whereDate('due_at', today()),
            'upcoming' => $q->where('status', 'pending')->where('due_at', '>', now()),
            'overdue' => $q->where('status', 'pending')->where('due_at', '<', now()),
            'done' => $q->where('status', 'completed'),
            default => $q,
        };

        return $query->orderBy($this->sortField, $this->sortDir);
    }

    public function render()
    {
        $tasks = $this->taskQuery()->get();
        $counts = [
            'today' => CrmTask::where('status', 'pending')->whereDate('due_at', today())->count(),
            'upcoming' => CrmTask::where('status', 'pending')->where('due_at', '>', now())->count(),
            'overdue' => CrmTask::where('status', 'pending')->where('due_at', '<', now())->count(),
            'done' => CrmTask::where('status', 'completed')->count(),
        ];
        $employees = User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();
        $leads = Lead::latest()->limit(30)->get(['id', 'name']);
        $taskTypes = ['call' => 'Call', 'follow_up' => 'Follow-up', 'meeting' => 'Meeting', 'email' => 'Email', 'whatsapp' => 'WhatsApp'];

        return view('livewire.tasks.index', compact('tasks', 'counts', 'employees', 'leads', 'taskTypes'))
            ->layout('layouts.app');
    }
}
