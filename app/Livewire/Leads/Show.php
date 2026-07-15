<?php

namespace App\Livewire\Leads;

use App\Models\CustomField;
use App\Models\Demo;
use App\Models\Lead;
use App\Models\LeadChatMessage;
use App\Models\LeadForward;
use App\Models\LeadLabel;
use App\Models\LeadNote;
use App\Models\LeadRecording;
use App\Models\LeadReminder;
use App\Models\LeadStage;
use App\Models\LeadTask;
use App\Models\Product;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\MeetingService;
use App\Services\ReviewRequestService;
use App\Support\MeetingTemplates;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;
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

    // Create Quotation quick action
    public bool $showQuotationModal = false;
    public array $quoteProducts = [];

    // Send Demo
    public bool $showDemoModal = false;

    // Schedule Meeting
    public bool $showMeetingModal = false;
    public string $meetingPlatform = 'google_meet';
    public string $meetingMode = 'instant';
    public string $meetingAt = '';
    public bool $meetingShareWhatsapp = false;
    public bool $meetingShareEmail = false;
    public string $meetingLink = '';

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
    public array $customFieldValues = [];

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
            'activities.user', 'leadNotes.user', 'reminders', 'recordings',
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
        $this->customFieldValues = $lead->custom_fields ?? [];
        $this->reminderAt = now()->addHour()->format('Y-m-d\TH:i');
        $this->taskDueAt = now()->addDay()->format('Y-m-d\TH:i');
        $this->meetingAt = now()->addHour()->format('Y-m-d\TH:i');
        $this->meetingShareWhatsapp = (bool) $lead->phone;
        $this->meetingShareEmail = ! $lead->phone && (bool) $lead->email;

        if (request()->has('tab')) {
            $this->activeTab = request('tab');
        }

        // Deep-links from leads list actions menu: ?action=...
        match (request('action')) {
            'edit' => $this->showEditModal = true,
            'followup' => $this->activeTab = 'task',
            'transfer' => $this->activeTab = 'forward',
            'quotation' => $this->showQuotationModal = true,
            'demo' => $this->showDemoModal = true,
            'meeting' => $this->showMeetingModal = true,
            default => null,
        };
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

    public function createQuotation()
    {
        if (! auth()->user()->hasPermission('documents.create')) {
            $this->dispatch('notify', message: 'Documents create permission nahi hai', type: 'error');

            return;
        }

        $params = ['lead_id' => $this->lead->id, 'type' => 'quotation'];
        $ids = array_filter(array_map('intval', $this->quoteProducts));
        if ($ids) {
            $params['products'] = implode(',', $ids);
        }

        return $this->redirect(route('leads.documents.create', $params));
    }

    public function sendDemo(int $demoId, string $channel = 'whatsapp'): void
    {
        if (! Schema::hasTable('demos')) {
            return;
        }

        $demo = Demo::where('is_active', true)->find($demoId);
        if (! $demo) {
            $this->dispatch('notify', message: 'Demo template nahi mila', type: 'error');

            return;
        }

        $message = $demo->message ?: "Hello {name}, humara product demo yahan dekhein: {link}";
        $message = str_replace(['{name}', '{link}'], [$this->lead->name, $demo->url], $message);
        if (! str_contains($demo->message ?? '', '{link}') && $demo->message) {
            $message .= "\n\nDemo Link: ".$demo->url;
        }

        if ($channel === 'email') {
            if (! $this->lead->email) {
                $this->dispatch('notify', message: 'Lead ka email nahi hai', type: 'error');

                return;
            }
            $subject = 'Product Demo - '.$demo->name;
            $url = 'mailto:'.$this->lead->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($message);
        } else {
            if (! $this->lead->phone) {
                $this->dispatch('notify', message: 'Lead ka phone number nahi hai', type: 'error');

                return;
            }
            $phone = preg_replace('/[^0-9]/', '', $this->lead->phone);
            $url = 'https://wa.me/91'.$phone.'?text='.urlencode($message);
        }

        $this->lead->update(['last_contacted_at' => now()]);
        $this->lead->logActivity('demo', "Demo sent: {$demo->name} (via ".ucfirst($channel).')', $message);
        $this->showDemoModal = false;
        $this->lead->refresh();
        $this->dispatch('notify', message: 'Demo '.($channel === 'email' ? 'email' : 'WhatsApp').' open ho raha hai — timeline me logged');
        $this->dispatch('open-url', url: $url);
    }

    public function createMeeting(MeetingService $meetings): void
    {
        if ($this->meetingMode === 'scheduled') {
            $this->validate(['meetingAt' => 'required|date']);
        }

        $when = $this->meetingMode === 'scheduled' ? Carbon::parse($this->meetingAt) : now();
        $tenant = auth()->user()->tenant;

        $result = $meetings->create(
            $tenant,
            $this->lead,
            $this->meetingPlatform,
            $this->meetingMode,
            $when
        );

        $this->meetingLink = $result['link'];
        $modeLabel = ($result['mode'] ?? 'test') === 'live' ? 'Live API' : 'Free Test Mode';
        $this->dispatch('notify', message: "Meeting link ready ({$modeLabel})");

        // Scheduled Google test mode: optional calendar template open
        if (($result['platform'] ?? '') === 'google_meet'
            && ($result['mode'] ?? '') === 'test'
            && $this->meetingMode === 'scheduled'
            && ! empty($result['raw']['calendar_hint'])) {
            $this->dispatch('open-url', url: $result['raw']['calendar_hint']);
        }
    }

    public function launchMeetingPlatform(): void
    {
        // Manual open helpers (optional) — createMeeting preferred
        if ($this->meetingPlatform === 'zoom') {
            $url = filled($this->meetingLink)
                ? $this->meetingLink
                : (auth()->user()->tenant->settings['zoom_personal_link'] ?? 'https://zoom.us/start/videomeeting');
        } elseif ($this->meetingMode === 'scheduled' && $this->meetingAt) {
            $start = Carbon::parse($this->meetingAt);
            $end = $start->copy()->addMinutes(45);
            $query = http_build_query([
                'action' => 'TEMPLATE',
                'text' => 'Meeting: '.$this->lead->name.' x '.auth()->user()->tenant->name,
                'dates' => $start->format('Ymd\THis').'/'.$end->format('Ymd\THis'),
                'details' => filled($this->meetingLink) ? 'Join: '.$this->meetingLink : 'Scheduled from CRM',
                'add' => $this->lead->email ?? '',
            ]);
            $url = 'https://calendar.google.com/calendar/render?'.$query;
        } else {
            $url = filled($this->meetingLink) ? $this->meetingLink : 'https://meet.google.com/new';
        }

        $this->dispatch('open-url', url: $url);
    }

    public function shareMeeting(MeetingService $meetings): void
    {
        if (! trim($this->meetingLink)) {
            // Auto-create link if user skipped the generate step
            $this->createMeeting($meetings);
        }

        if (! trim($this->meetingLink)) {
            $this->dispatch('notify', message: 'Meeting link generate nahi hua', type: 'error');

            return;
        }

        if ($this->meetingMode === 'scheduled') {
            $this->validate(['meetingAt' => 'required|date']);
        }

        if (! $this->meetingShareWhatsapp && ! $this->meetingShareEmail) {
            $this->dispatch('notify', message: 'Share ke liye WhatsApp ya Email select karein', type: 'error');

            return;
        }

        $tenant = auth()->user()->tenant;
        $when = $this->meetingMode === 'scheduled' ? Carbon::parse($this->meetingAt) : now();
        $vars = [
            'name' => $this->lead->name,
            'date' => $when->format('d M Y'),
            'time' => $this->meetingMode === 'scheduled' ? $when->format('h:i A') : 'Abhi (Instant meeting)',
            'link' => trim($this->meetingLink),
            'company' => $tenant->name,
        ];

        $templates = MeetingTemplates::forTenant($tenant);
        $prefix = $this->meetingPlatform === 'zoom' ? 'zoom' : 'google_meet';
        $platformLabel = $this->meetingPlatform === 'zoom' ? 'Zoom' : 'Google Meet';

        if ($this->meetingShareWhatsapp && $this->lead->phone) {
            $message = MeetingTemplates::fill($templates[$prefix.'_whatsapp'], $vars);
            $phone = preg_replace('/[^0-9]/', '', $this->lead->phone);
            $this->dispatch('open-url', url: 'https://wa.me/91'.$phone.'?text='.urlencode($message));
        }

        if ($this->meetingShareEmail && $this->lead->email) {
            $subject = MeetingTemplates::fill($templates[$prefix.'_email_subject'], $vars);
            $body = MeetingTemplates::fill($templates[$prefix.'_email'], $vars);
            $this->dispatch('open-url', url: 'mailto:'.$this->lead->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($body));
        }

        $this->lead->update(['last_contacted_at' => now()]);
        $this->lead->logActivity(
            'meeting',
            $this->meetingMode === 'scheduled'
                ? "{$platformLabel} meeting scheduled: ".$when->format('d M Y, h:i A')
                : "{$platformLabel} instant meeting started",
            trim($this->meetingLink)
        );

        if ($this->meetingMode === 'scheduled') {
            LeadReminder::create([
                'tenant_id' => $this->lead->tenant_id,
                'lead_id' => $this->lead->id,
                'user_id' => auth()->id(),
                'title' => "Meeting: {$this->lead->name} ({$platformLabel})",
                'description' => trim($this->meetingLink),
                'remind_at' => $when,
                'type' => 'meeting',
            ]);
            $this->lead->update(['next_follow_up_at' => $when]);
        }

        $this->showMeetingModal = false;
        $this->meetingLink = '';
        $this->lead->refresh();
        $this->dispatch('notify', message: 'Meeting invite share ho gaya — timeline me logged');
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
            $this->dispatch('notify', message: 'Edit permission nahi hai', type: 'error');

            return;
        }

        if (! $this->editStageId) {
            $this->editStageId = LeadStage::ensureDefault()->id;
        }

        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => 'nullable|email|max:255',
            'editPhone' => 'nullable|string|max:20',
            'editStageId' => [
                'required',
                Rule::exists('lead_stages', 'id')->where('tenant_id', auth()->user()->tenant_id),
            ],
            'editAssignedTo' => 'nullable|exists:users,id',
        ]);

        try {
            $oldStage = $this->lead->stage?->name;

            $customFields = collect($this->customFieldValues)
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all();

            $this->lead->update([
                'name' => $this->editName,
                'email' => $this->editEmail ?: null,
                'phone' => $this->editPhone ?: null,
                'company' => $this->editCompany ?: null,
                'lead_stage_id' => $this->editStageId,
                'lead_label_id' => $this->editLabelId ?: null,
                'assigned_to' => $this->editAssignedTo,
                'service_type' => $this->editServiceType ?: null,
                'custom_fields' => $customFields ?: null,
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
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', message: 'Lead save nahi ho paya', type: 'error');
        }
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

    protected function getCustomFields()
    {
        if (! Schema::hasTable('custom_fields')) {
            return collect();
        }

        return CustomField::where('entity_type', 'lead')->orderBy('sort_order')->get();
    }

    public function render()
    {
        $employees = User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();
        $stages = LeadStage::orderBy('sort_order')->get();
        $labels = LeadLabel::orderBy('name')->get();
        $timeline = $this->lead->activities()->with('user')->latest()->get();
        $openTasks = $this->lead->tasks()->where('status', 'pending')->get();
        $whatsappTemplates = $this->getWhatsappTemplates();
        // Blade mein literal {{name}} echo nahi kar sakte (compile error), isliye preview yahin banate hain
        $whatsappPreviews = array_map(
            fn ($t) => str_replace('{{name}}', $this->lead->name, $t),
            $whatsappTemplates
        );
        $customFields = $this->getCustomFields();

        $demos = Schema::hasTable('demos')
            ? Demo::where('is_active', true)->latest()->get()
            : collect();
        $products = Schema::hasTable('products')
            ? Product::where('is_active', true)->orderBy('name')->get()
            : collect();
        $meetingStatus = app(MeetingService::class)->status(auth()->user()->tenant);

        return view('livewire.leads.show', compact('employees', 'stages', 'labels', 'timeline', 'openTasks', 'whatsappTemplates', 'whatsappPreviews', 'customFields', 'demos', 'products', 'meetingStatus'))
            ->layout('layouts.app');
    }
}
