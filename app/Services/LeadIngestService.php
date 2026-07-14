<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Tenant;
use App\Models\WebhookLog;

class LeadIngestService
{
    public function findTenant(string $key): ?Tenant
    {
        return Tenant::where('is_active', true)
            ->where(function ($q) use ($key) {
                $q->where('subdomain', $key);
                if (is_numeric($key)) {
                    $q->orWhere('id', (int) $key);
                }
            })
            ->first();
    }

    public function ingest(Tenant $tenant, array $data, string $source): Lead
    {
        $phone = $this->normalizePhone($data['phone'] ?? null);
        $email = $data['email'] ?? null;

        $existing = $this->findDuplicate($tenant->id, $phone, $email);

        if ($existing) {
            $existing->update(array_filter([
                'name' => $data['name'] ?? $existing->name,
                'company' => $data['company'] ?? $existing->company,
                'campaign' => $data['campaign'] ?? $existing->campaign,
                'city' => $data['city'] ?? $existing->city,
                'notes' => isset($data['notes']) ? ($existing->notes."\n".$data['notes']) : null,
            ]));

            $this->logActivity($existing, $source, 'Duplicate lead updated from '.$source, $data);

            return $existing;
        }

        $stage = LeadStage::ensureDefault($tenant->id);

        $lead = Lead::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'lead_stage_id' => $stage->id,
            'name' => $data['name'] ?? 'Unknown',
            'email' => $email,
            'phone' => $phone,
            'company' => $data['company'] ?? null,
            'source' => $source,
            'campaign' => $data['campaign'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'notes' => $data['notes'] ?? $data['message'] ?? null,
            'custom_fields' => $data['custom_fields'] ?? null,
        ]);

        $this->logActivity($lead, $source, 'Lead captured from '.$source, $data);

        return $lead;
    }

    public function logWebhook(Tenant $tenant, string $source, ?string $event, array $payload, string $status, ?int $leadId = null, ?string $error = null): WebhookLog
    {
        return WebhookLog::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'source' => $source,
            'event' => $event,
            'payload' => $payload,
            'status' => $status,
            'lead_id' => $leadId,
            'error_message' => $error,
        ]);
    }

    protected function findDuplicate(int $tenantId, ?string $phone, ?string $email): ?Lead
    {
        $query = Lead::withoutGlobalScopes()->where('tenant_id', $tenantId);

        if ($phone) {
            $match = (clone $query)->where('phone', $phone)->first();
            if ($match) {
                return $match;
            }
        }

        if ($email) {
            return (clone $query)->where('email', $email)->first();
        }

        return null;
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($digits) === 10) {
            return $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        return $digits ?: $phone;
    }

    protected function logActivity(Lead $lead, string $source, string $title, array $data): void
    {
        $lead->activities()->create([
            'tenant_id' => $lead->tenant_id,
            'user_id' => auth()->id(),
            'type' => 'webhook',
            'title' => $title,
            'description' => $data['notes'] ?? $data['message'] ?? null,
            'meta' => ['source' => $source, 'data' => $data],
        ]);
    }
}
