<?php

namespace App\Livewire\Automations;

use App\Models\Automation;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';
    public bool $showRecipes = true;
    public bool $showGuide = false;
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
        'task_overdue' => 'Task overdue',
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

    /**
     * Prebuilt recipes. Only uses trigger types actually dispatched by
     * LeadObserver / AutomationService::processScheduledTriggers / Inbox,
     * and only action types executed by AutomationService::runAction
     * (send_whatsapp, send_email, create_task).
     */
    public function recipes(): array
    {
        return [
            [
                'key' => 'welcome_whatsapp',
                'icon' => '👋',
                'name' => 'Instant WhatsApp Welcome',
                'desc' => 'Naya lead aate hi turant welcome message — lead ko lagta hai aap active ho.',
                'trigger' => 'lead_created',
                'actions' => [['type' => 'send_whatsapp', 'message' => 'Hi {{name}}! 🙏 Thank you for your interest. Humari team ne aapki enquiry receive kar li hai — hum aapse jaldi hi contact karenge. Koi urgent baat ho toh isi number par message kar dijiye.']],
                'day_actions' => [],
            ],
            [
                'key' => 'new_lead_assign_task',
                'icon' => '⚡',
                'name' => 'New Lead Auto-Assign Task',
                'desc' => 'Har naye lead par team ke liye "assign & call in 1 hour" task ban jata hai (round-robin note ke saath).',
                'trigger' => 'lead_created',
                'actions' => [['type' => 'create_task', 'message' => 'New lead aaya hai — round-robin se assign karo aur 1 hour ke andar first call karo']],
                'day_actions' => [],
            ],
            [
                'key' => 'welcome_email',
                'icon' => '📧',
                'name' => 'Welcome Email Intro',
                'desc' => 'Naye lead ko intro email — company details aur next steps ke saath.',
                'trigger' => 'lead_created',
                'actions' => [['type' => 'send_email', 'message' => 'Hi {{name}}, thank you for reaching out! Here is a quick intro to our services and what happens next. Our team will call you shortly to understand your requirement.']],
                'day_actions' => [],
            ],
            [
                'key' => 'nurture_drip_3day',
                'icon' => '💧',
                'name' => '3-Day Nurture Drip',
                'desc' => 'Day 1 intro, Day 2 case study, Day 3 offer — naya lead 3 din tak warm rehta hai.',
                'trigger' => 'lead_created',
                'actions' => [['type' => 'send_whatsapp', 'message' => 'Hi {{name}}! (Day 1) Great to connect. Yeh raha humara quick intro — hum aapki requirement ke liye best solution provide karte hain. Kal aapko ek useful case study bhejenge.']],
                'day_actions' => [
                    ['day' => 1, 'action' => 'send_whatsapp', 'message' => 'Hi {{name}}! (Day 1) Great to connect. Yeh raha humara quick intro — hum aapki requirement ke liye best solution provide karte hain.'],
                    ['day' => 2, 'action' => 'send_whatsapp', 'message' => 'Hi {{name}}! (Day 2) Dekhiye kaise humne ek client ki problem solve ki — yeh case study aapke kaam aayegi. Koi sawal ho toh reply kariye.'],
                    ['day' => 3, 'action' => 'send_whatsapp', 'message' => 'Hi {{name}}! (Day 3) Sirf aapke liye ek special offer 🎁 — is week book karne par extra benefit milega. Interested? Reply YES.'],
                ],
            ],
            [
                'key' => 'no_call_24h_task',
                'icon' => '⏰',
                'name' => '24h No-Contact Reminder',
                'desc' => 'Agar lead ko 24 ghante me call nahi hui toh team ko follow-up task milta hai.',
                'trigger' => 'no_call_24h',
                'actions' => [['type' => 'create_task', 'message' => 'Urgent follow-up: is lead ko 24 hours me koi call nahi hui — aaj hi call karo']],
                'day_actions' => [],
            ],
            [
                'key' => 'cold_reengage',
                'icon' => '🔥',
                'name' => 'Cold Lead Re-Engagement',
                'desc' => 'Bina call ke pade hue lead ko WhatsApp par wapas engage karo — "still interested?" message.',
                'trigger' => 'no_call_24h',
                'actions' => [['type' => 'send_whatsapp', 'message' => 'Hi {{name}}! Humne notice kiya aapse baat nahi ho payi. Kya aap abhi bhi interested hain? Reply kijiye — hum turant call arrange kar denge. 😊']],
                'day_actions' => [],
            ],
            [
                'key' => 'stage_next_step',
                'icon' => '🎯',
                'name' => 'Stage-Change Next-Step Message',
                'desc' => 'Jab bhi lead ka status update ho, lead ko congratulation + next step WhatsApp jata hai.',
                'trigger' => 'stage_changed',
                'actions' => [['type' => 'send_whatsapp', 'message' => 'Hi {{name}}! Good news — aapki enquiry next stage par pahunch gayi hai. 🎉 Humari team next step ke liye aapse jaldi contact karegi.']],
                'day_actions' => [],
            ],
            [
                'key' => 'won_review_onboarding',
                'icon' => '🏆',
                'name' => 'Won Lead → Review + Onboarding',
                'desc' => 'Status update par review request + onboarding message. Note: yeh har status change par chalta hai — sirf Won pipeline wale board me use karo.',
                'trigger' => 'stage_changed',
                'actions' => [
                    ['type' => 'send_whatsapp', 'message' => 'Congratulations {{name}}! 🎉 Welcome aboard. Aapka onboarding shuru ho gaya hai — next steps hum WhatsApp par share karenge. Agar experience accha laga toh humein ek review zaroor dijiye: aapka feedback humare liye bahut valuable hai. ⭐'],
                    ['type' => 'create_task', 'message' => 'Won lead — onboarding call schedule karo aur Google review link bhejo'],
                ],
                'day_actions' => [],
            ],
            [
                'key' => 'lost_feedback',
                'icon' => '📝',
                'name' => 'Lost Lead Feedback Ask',
                'desc' => 'Status update par polite feedback message. Note: har status change par chalta hai — lost-only pipeline me use karo.',
                'trigger' => 'stage_changed',
                'actions' => [['type' => 'send_whatsapp', 'message' => 'Hi {{name}}, koi baat nahi — shayad abhi sahi time nahi tha. Kya aap 10 second me bata sakte hain hum kahan improve karein? Aapka feedback humein behtar banayega. Future me kabhi zaroorat ho toh hum yahin hain. 🙏']],
                'day_actions' => [],
            ],
            [
                'key' => 'task_overdue_escalation',
                'icon' => '🚨',
                'name' => 'Overdue Task Escalation',
                'desc' => 'Follow-up task overdue hone par escalation task ban jata hai — koi lead miss nahi hota.',
                'trigger' => 'task_overdue',
                'actions' => [['type' => 'create_task', 'message' => 'ESCALATION: is lead ka follow-up task overdue hai — turant action lo ya manager ko inform karo']],
                'day_actions' => [],
            ],
            [
                'key' => 'whatsapp_instant_ack',
                'icon' => '💬',
                'name' => 'WhatsApp Instant Acknowledgment',
                'desc' => 'Lead ka WhatsApp message aate hi acknowledgment + follow-up task — reply kabhi late nahi hota.',
                'trigger' => 'whatsapp_received',
                'actions' => [
                    ['type' => 'create_task', 'message' => 'Lead ne WhatsApp message bheja hai — personally reply karo'],
                ],
                'day_actions' => [],
            ],
        ];
    }

    public function installRecipe(string $key): void
    {
        $recipe = collect($this->recipes())->firstWhere('key', $key);
        if (! $recipe) {
            return;
        }

        if (Automation::where('name', $recipe['name'])->exists()) {
            $this->dispatch('notify', message: 'Already installed', type: 'error');

            return;
        }

        Automation::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $recipe['name'],
            'trigger_type' => $recipe['trigger'],
            'trigger_config' => [],
            'actions' => $recipe['actions'],
            'day_actions' => $recipe['day_actions'],
            'is_active' => true,
            'is_draft' => false,
        ]);

        $this->dispatch('notify', message: $recipe['name'].' installed & active 🎉');
    }

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

        $recipes = $this->recipes();
        $installed = Automation::query()->get(['id', 'name', 'is_active'])->keyBy('name');

        return view('livewire.automations.index', compact('automations', 'recipes', 'installed'))
            ->layout('layouts.app');
    }
}
