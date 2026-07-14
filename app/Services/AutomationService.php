<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\CrmTask;
use App\Models\Lead;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AutomationService
{
    public function runTrigger(string $triggerType, Lead $lead, array $context = []): int
    {
        $automations = Automation::where('trigger_type', $triggerType)
            ->where('is_active', true)
            ->get();

        $ran = 0;
        foreach ($automations as $automation) {
            if ($this->execute($automation, $lead, $context)) {
                $ran++;
                $automation->increment('runs_count');
            }
        }

        return $ran;
    }

    public function execute(Automation $automation, Lead $lead, array $context = []): bool
    {
        $actions = $automation->actions ?? [];
        if (empty($actions)) {
            return false;
        }

        foreach ($actions as $action) {
            $this->runAction($action, $lead, $context);
        }

        return true;
    }

    protected function runAction(array $action, Lead $lead, array $context = []): void
    {
        $type = $action['type'] ?? '';
        $message = $action['message'] ?? '';

        match ($type) {
            'send_whatsapp' => $this->actionWhatsapp($lead, $message),
            'send_email' => $this->actionEmail($lead, $message),
            'create_task' => $this->actionTask($lead, $message ?: 'Automation follow-up'),
            'assign_user' => $this->actionAssign($lead, $action['user_id'] ?? null),
            'change_stage' => $this->actionStage($lead, $action['stage_id'] ?? null),
            default => null,
        };
    }

    protected function actionWhatsapp(Lead $lead, string $message): void
    {
        $text = str_replace('{{name}}', $lead->name, $message);
        $lead->update(['last_contacted_at' => now()]);
        $lead->logActivity('whatsapp', 'Automation: WhatsApp sent', $text);
    }

    protected function actionEmail(Lead $lead, string $message): void
    {
        $text = str_replace('{{name}}', $lead->name, $message);
        $lead->update(['last_contacted_at' => now()]);
        $lead->logActivity('email', 'Automation: Email queued', $text);
    }

    protected function actionTask(Lead $lead, string $title): void
    {
        CrmTask::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => $lead->assigned_to ?? auth()->id(),
            'created_by' => auth()->id(),
            'title' => $title,
            'due_at' => now()->addDay(),
            'priority' => 'medium',
        ]);
        $lead->logActivity('task', 'Automation: Task created', $title);
    }

    protected function actionAssign(Lead $lead, ?int $userId): void
    {
        if (! $userId) {
            return;
        }
        $user = User::find($userId);
        if (! $user) {
            return;
        }
        $lead->update(['assigned_to' => $userId]);
        $lead->logActivity('assigned', "Automation: Assigned to {$user->name}");
    }

    protected function actionStage(Lead $lead, ?int $stageId): void
    {
        if (! $stageId) {
            return;
        }
        $old = $lead->stage?->name;
        $lead->update(['lead_stage_id' => $stageId]);
        $lead->refresh();
        $lead->logActivity('stage_change', "Automation: {$old} → {$lead->stage?->name}");
    }

    public function processScheduledTriggers(): int
    {
        $count = 0;

        Lead::whereNull('last_call_at')
            ->where('created_at', '<', now()->subDay())
            ->where('created_at', '>', now()->subDays(2))
            ->chunk(50, function ($leads) use (&$count) {
                foreach ($leads as $lead) {
                    $count += $this->runTrigger('no_call_24h', $lead);
                }
            });

        CrmTask::where('status', 'pending')
            ->where('due_at', '<', now())
            ->with('lead')
            ->chunk(50, function ($tasks) use (&$count) {
                foreach ($tasks as $task) {
                    if ($task->lead) {
                        $count += $this->runTrigger('task_overdue', $task->lead, ['task_id' => $task->id]);
                    }
                }
            });

        return $count;
    }

    public function matchBotReply(string $message, int $tenantId): ?string
    {
        $keyword = strtolower(trim($message));
        $bots = \App\Models\WhatsappBot::where('is_active', true)->get();

        foreach ($bots as $bot) {
            if ($bot->trigger_keyword && str_contains($keyword, strtolower($bot->trigger_keyword))) {
                $nodes = $bot->flow_data['nodes'] ?? [];
                foreach ($nodes as $node) {
                    if (($node['type'] ?? '') === 'message' && ! empty($node['text'])) {
                        $bot->increment('sessions_count');

                        return $node['text'];
                    }
                }
            }
        }

        return null;
    }
}
