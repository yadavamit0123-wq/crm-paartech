<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeadIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleAdsWebhookController extends Controller
{
    public function __construct(protected LeadIngestService $ingest) {}

    public function handle(Request $request, string $tenant): JsonResponse
    {
        $tenantModel = $this->ingest->findTenant($tenant);
        if (! $tenantModel) {
            return response()->json(['error' => 'Invalid tenant'], 404);
        }

        $secret = $tenantModel->settings['google_ads_webhook_secret']
            ?? config('services.google_ads.webhook_secret');

        if ($secret && $request->header('X-Webhook-Secret') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $this->ingest->logWebhook($tenantModel, 'google', 'lead_form', $payload, 'received');

        try {
            $columns = $payload['user_column_data'] ?? $payload['columns'] ?? [];
            $mapped = $this->mapColumns($columns);

            if (empty($mapped['name']) && empty($mapped['phone']) && empty($mapped['email'])) {
                $mapped = [
                    'name' => $payload['name'] ?? $payload['full_name'] ?? 'Google Ads Lead',
                    'email' => $payload['email'] ?? null,
                    'phone' => $payload['phone'] ?? $payload['phone_number'] ?? null,
                    'city' => $payload['city'] ?? null,
                    'campaign' => $payload['campaign_name'] ?? $payload['campaign_id'] ?? 'Google Ads',
                ];
            }

            $lead = $this->ingest->ingest($tenantModel, array_merge($mapped, [
                'campaign' => $mapped['campaign'] ?? ($payload['campaign_name'] ?? 'Google Ads Lead Form'),
                'custom_fields' => [
                    'google_lead_id' => $payload['lead_id'] ?? null,
                    'form_id' => $payload['form_id'] ?? null,
                ],
            ]), 'google');

            $this->ingest->logWebhook($tenantModel, 'google', 'lead_form', $payload, 'processed', $lead->id);

            return response()->json(['success' => true, 'lead_id' => $lead->id]);
        } catch (\Exception $e) {
            $this->ingest->logWebhook($tenantModel, 'google', 'lead_form', $payload, 'failed', null, $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function mapColumns(array $columns): array
    {
        $mapped = [];
        $keyMap = [
            'FULL_NAME' => 'name', 'NAME' => 'name',
            'EMAIL' => 'email', 'PHONE_NUMBER' => 'phone', 'PHONE' => 'phone',
            'CITY' => 'city',
        ];

        foreach ($columns as $col) {
            $key = strtoupper($col['column_id'] ?? $col['column_name'] ?? '');
            $val = $col['string_value'] ?? $col['value'] ?? '';
            if (isset($keyMap[$key])) {
                $mapped[$keyMap[$key]] = $val;
            }
        }

        return $mapped;
    }
}
