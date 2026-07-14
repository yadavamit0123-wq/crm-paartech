<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadChatMessage;
use App\Models\LeadForward;
use App\Models\LeadLabel;
use App\Models\LeadNote;
use App\Models\LeadRecording;
use App\Models\LeadReminder;
use App\Models\LeadStage;
use App\Models\LeadTask;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\ReviewRequestService;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Lead $lead;

    public string $activeTab = 'timeline';
    public string $noteContent = '';
    public string $noteColor = '#fef08a';
    public string $chatMessage = '';
    public string $forwardNote = '';
    public ?int $forwardTo = null;
    public string $reminderTitle = '';
    public string $reminderDescription = '';
    public string $reminderType = 'follow_up';
    public string $reminderAt = '';
    public $recordingFile;
    public string $recordingTitle = '';
    public bool $showEditModal = false;
    public bool $showActivityModal = false;
    public bool $showWhatsappModal = false;
    public string $activityTitle = '';
    public string $activityDescription = '';

    public string $taskTitle = '';
    public string $taskDescription = '';
    public string $taskDueAt = '';
    public string $taskPriority = 'medium';

    public string $editName = '';
    public string $editEmail = '';
    public string $editPhone = '';
    public string $editCompany = '';
    public ?int $editStageId = null;
    public ?int $editLabelId = null;
    public ?int $editAssignedTo = null;
    public string $editServiceType = '';
    public string $companyProfile = '';

    public function mount(Lead $lead): void
    {
        $user = auth()->user();
        if (! $user->hasPermission('leads.view_all') && $user->hasPermission('leads.view_own')) {
            if ($lead->assigned_to !== $user->id && $lead->created_by !== $user->id) {
                abort(403);
            }
        }

        $this->lead = $lead->load([
            'stage', 'label', 'assignee', 'customer',
            'activities.user', 'notes.user', 'reminders', 'recordings',
            'chatMessages.user', 'forwards.fromUser', 'forwards.toUser', 'tasks.assignee',
        ]);

        $this->editName = $lead->name;
        $this->editEmail = $lead->email ?? '';
        $this->editPhone = $lead->phone ?? '';
        $this->editCompany = $lead->company ?? '';
        $this->editStageId = $lead->lead_stage_id;
        $this->editLabelId = $lead->lead_label_id;
        $this->editAssignedTo = $lead->assigned_to;
        $this->editServiceType = $lead->service_type ?? '';
        $this->companyProfile = $lead->company_profile ?? '';
        $this->reminderAt = now()->addHour()->format('Y-m-d\TH:i');
        $this->taskDueAt = now()->addDay()->format('Y-m-d\TH:i');

        if (request()->has('tab')) {
            $this->activeTab = request('tab');
        }
    }

    public function setLabel($labelId): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            return;
        }
        $labelId = ($labelId === '' || $labelId === null) ? null : (int) $labelId;
        $this->lead->update(['lead_label_id' => $labelId]);
        $name = $labelId ? LeadLabel::find($labelId)?->name : 'None';
        $this->lead->logActivity('label', "Label updated to {$name}");
        $this->lead->refresh();
        $this->dispatch('notify', message: 'Label updated');
    }

    public function setStage($stageId): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            return;
        }
        $stageId = (int) $stageId;
        $old = $this->lead->stage?->name;
        $this->lead->update(['lead_stage_id' => $stageId]);
        $new = LeadStage::find($stageId)?->name;
        $this->lead->logActivity('stage_change', "Status: {$old} → {$new}");
        $this->lead->refresh();
        $this->dispatch('notify', message: 'Status updated');
    }

    public function getWhatsappTemplates(): array
    {
        $settings = auth()->user()->tenant->settings ?? [];

        return $settings['whatsapp_templates'] ?? [
            'Hi {{name}}, thank you for your inquiry! How can we help you today?',
            'Hello {{name}}, this is a reminder about your pending follow-up with us.',
            'Dear {{name}}, we have a special offer for you. Reply to know more!',
        ];
    }

    public function getWhatsappUrl(string $template): string
    {
        $text = str_replace('{{name}}', $this->lead->name, $template);
        $phone = preg_replace('/[^0-9]/', '', $this->lead->phone ?? '');

        return 'https://wa.me/91'.$phone.'?text='.urlencode($text);
    }

    public function sendWhatsappTemplate(int $index): void
    {
        $templates = $this->getWhatsappTemplates();
        $template = $templates[$index] ?? '';
        if (! $template) {
            return;
        }

        $this->lead->update(['last_contacted_at' => now()]);
        $preview = str_replace('{{name}}', $this->lead->name, $template);
        $this->lead->logActivity('whatsapp', 'WhatsApp template sent', $preview);
        $this->showWhatsappModal = false;
        $this->lead->refresh();
        $this->dispatch('notify', message: 'WhatsApp template logged');
        $this->dispatch('open-whatsapp', url: $this->getWhatsappUrl($template));
    }

    public function addNote(): void
    {
        $this->validate(['noteContent' => 'required|string|max:2000']);

        LeadNote::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'user_id' => auth()->id(),
            'content' => $this->noteContent,
            'color' => $this->noteColor,
            'is_sticky' => true,
        ]);

        $this->lead->logActivity('note', 'Note added', $this->noteContent);
        $this->noteContent = '';
        $this->lead->refresh();
    }

    public function addTask(): void
    {
        $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskDueAt' => 'nullable|date',
            'taskPriority' => 'in:low,medium,high',
        ]);

        LeadTask::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'due_at' => $this->taskDueAt ?: null,
            'priority' => $this->taskPriority,
        ]);

        $this->lead->logActivity('task', "Task created: {$this->taskTitle}", $this->taskDescription);
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->lead->refresh();
        $this->dispatch('notify', message: 'Task created');
    }

    public function completeTask(int $taskId): void
    {
        $task = LeadTask::where('lead_id', $this->lead->id)->findOrFail($taskId);
        $task->update(['status' => 'completed', 'completed_at' => now()]);
        $this->lead->logActivity('task', "Task completed: {$task->title}");
        $this->lead->refresh();
    }

    public function addCustomActivity(): void
    {
        $this->validate(['activityTitle' => 'required|string|max:255']);
        $this->lead->logActivity('custom', $this->activityTitle, $this->activityDescription ?: null);
        $this->activityTitle = '';
        $this->activityDescription = '';
        $this->showActivityModal = false;
        $this->lead->refresh();
        $this->dispatch('notify', message: 'Activity added');
    }

    public function sendChat(): void
    {
        $this->validate(['chatMessage' => 'required|string|max:2000']);

        LeadChatMessage::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'user_id' => auth()->id(),
            'message' => $this->chatMessage,
        ]);

        $this->lead->logActivity('chat', 'Team chat message', $this->chatMessage);
        $this->chatMessage = '';
        $this->lead->refresh();
    }

    public function forwardLead(): void
    {
        if (! auth()->user()->hasPermission('leads.forward')) {
            return;
        }

        $this->validate([
            'forwardTo' => 'required|exists:users,id',
            'forwardNote' => 'nullable|string|max:1000',
        ]);

        LeadForward::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'from_user_id' => auth()->id(),
            'to_user_id' => $this->forwardTo,
            'note' => $this->forwardNote,
        ]);

        $toUser = User::find($this->forwardTo);
        $this->lead->update(['assigned_to' => $this->forwardTo]);
        $this->lead->logActivity('forward', "Forwarded to {$toUser->name}", $this->forwardNote);

        $this->forwardTo = null;
        $this->forwardNote = '';
        $this->lead->refresh();
        $this->dispatch('notify', message: "Forwarded to {$toUser->name}");
    }

    public function setReminder(): void
    {
        $this->validate([
            'reminderTitle' => 'required|string|max:255',
            'reminderAt' => 'required|date',
            'reminderType' => 'required|in:call,email,whatsapp,meeting,follow_up,custom',
        ]);

        LeadReminder::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'user_id' => auth()->id(),
            'title' => $this->reminderTitle,
            'description' => $this->reminderDescription,
            'remind_at' => $this->reminderAt,
            'type' => $this->reminderType,
        ]);

        $this->lead->update(['next_follow_up_at' => $this->reminderAt]);
        $this->lead->logActivity('reminder', "Follow-up: {$this->reminderTitle}", $this->reminderDescription);

        $this->reminderTitle = '';
        $this->reminderDescription = '';
        $this->dispatch('notify', message: 'Reminder set');
        $this->lead->refresh();
    }

    public function completeReminder(int $reminderId): void
    {
        LeadReminder::where('lead_id', $this->lead->id)->where('id', $reminderId)
            ->update(['is_completed' => true]);
        $this->lead->refresh();
    }

    public function uploadRecording(): void
    {
        $this->validate([
            'recordingFile' => 'required|file|mimes:mp3,wav,m4a,ogg|max:20480',
            'recordingTitle' => 'nullable|string|max:255',
        ]);

        $path = $this->recordingFile->store('recordings/'.$this->lead->tenant_id, 'public');

        LeadRecording::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'user_id' => auth()->id(),
            'title' => $this->recordingTitle ?: 'Call Recording',
            'file_path' => $path,
            'file_size' => $this->recordingFile->getSize(),
        ]);

        $this->lead->logActivity('recording', 'Call recording uploaded', $this->recordingTitle);
        $this->recordingFile = null;
        $this->recordingTitle = '';
        $this->lead->refresh();
    }

    public function logAction(string $action): void
    {
        $titles = [
            'call' => 'Call initiated',
            'whatsapp' => 'WhatsApp message sent',
            'email' => 'Email sent',
            'sms' => 'SMS sent',
            'meeting' => 'Meeting scheduled',
            'demo' => 'Demo scheduled',
        ];

        $updates = ['last_contacted_at' => now()];
        if ($action === 'call') {
            $updates['last_call_at'] = now();
        }

        $this->lead->update($updates);
        $this->lead->logActivity($action, $titles[$action] ?? ucfirst($action));
        $this->lead->refresh();
        $this->dispatch('notify', message: ucfirst($action).' logged');
    }

    public function saveCompanyProfile(): void
    {
        $this->lead->update(['company_profile' => $this->companyProfile]);
        $this->lead->logActivity('profile', 'Company profile updated');
        $this->dispatch('notify', message: 'Profile saved');
    }

    public function updateLead(): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            return;
        }

        $oldStage = $this->lead->stage?->name;

        $this->lead->update([
            'name' => $this->editName,
            'email' => $this->editEmail,
            'phone' => $this->editPhone,
            'company' => $this->editCompany,
            'lead_stage_id' => $this->editStageId,
            'lead_label_id' => $this->editLabelId,
            'assigned_to' => $this->editAssignedTo,
            'service_type' => $this->editServiceType ?: null,
        ]);

        $newStage = LeadStage::find($this->editStageId)?->name;
        if ($oldStage !== $newStage) {
            $this->lead->logActivity('stage_change', "Status: {$oldStage} → {$newStage}");
        } else {
            $this->lead->logActivity('updated', 'Lead info updated');
        }

        $this->showEditModal = false;
        $this->lead->refresh();
        $this->dispatch('notify', message: 'Lead updated');
    }

    public function deleteLead(): void
    {
        if (! auth()->user()->hasPermission('leads.delete')) {
            return;
        }

        $this->lead->logActivity('deleted', 'Lead deleted');
        $this->lead->delete();

        $this->redirect(route('leads.list'), navigate: true);
    }

    public function sendReviewRequest(ReviewRequestService $reviewService): void
    {
        if (! auth()->user()->hasPermission('reviews.manage')) {
            abort(403);
        }

        $tenant = auth()->user()->tenant;
        $reviewService->send(
            $tenant,
            $this->lead->id,
            $this->lead->customer?->id,
            $this->lead->name,
            $this->lead->phone,
            $this->lead->email,
            'whatsapp'
        );

        $this->lead->logActivity('review', 'Google review request sent');
        $this->dispatch('notify', message: 'Review request sent');
    }

    public function convertToCustomer(CustomerService $customerService)
    {
        if (! auth()->user()->hasPermission('customers.manage')) {
            abort(403);
        }

        if ($this->lead->is_customer && $this->lead->customer) {
            return $this->redirect(route('leads.customers.show', $this->lead->customer), navigate: true);
        }

        $customer = $customerService->convertFromLead($this->lead);
        $this->lead->refresh();
        $this->dispatch('notify', message: 'Converted to customer!');

        return $this->redirect(route('leads.customers.show', $customer), navigate: true);
    }

    public function render()
    {
        $employees = User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();
        $stages = LeadStage::orderBy('sort_order')->get();
        $labels = LeadLabel::orderBy('name')->get();
        $timeline = $this->lead->activities()->with('user')->latest()->get();
        $openTasks = $this->lead->tasks()->where('status', 'pending')->get();
        $whatsappTemplates = $this->getWhatsappTemplates();

        return view('livewire.leads.show', compact('employees', 'stages', 'labels', 'timeline', 'openTasks', 'whatsappTemplates'))
            ->layout('layouts.app');
    }
}
