<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeadIngestService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class MetaWebhookController extends Controller
{
    public function __construct(protected LeadIngestService $ingest) {}

    public function verify(Request $request, string $tenant): Response
    {
        $tenantModel = $this->ingest->findTenant($tenant);
        if (! $tenantModel) {
            abort(404);
        }

        $verifyToken = $tenantModel->settings['meta_verify_token']
            ?? config('services.meta.verify_token');

        if ($request->get('hub_mode') === 'subscribe'
            && $request->get('hub_verify_token') === $verifyToken) {
            return response($request->get('hub_challenge'), 200);
        }

        abort(403);
    }

    public function handle(Request $request, string $tenant): Response
    {
        $tenantModel = $this->ingest->findTenant($tenant);
        if (! $tenantModel) {
            return response('Tenant not found', 404);
        }

        $payload = $request->all();

        if (! $this->verifySignature($request, $tenantModel)) {
            return response('Invalid signature', 403);
        }

        $this->ingest->logWebhook($tenantModel, 'meta', 'leadgen', $payload, 'received');

        try {
            foreach ($payload['entry'] ?? [] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    if (($change['field'] ?? '') !== 'leadgen') {
                        continue;
                    }

                    $leadgenId = $change['value']['leadgen_id'] ?? null;
                    $formId = $change['value']['form_id'] ?? null;
                    $pageId = $change['value']['page_id'] ?? null;

                    $leadData = $this->fetchLeadFromGraph($tenantModel, $leadgenId)
                        ?? $this->parseInlineLeadData($change['value'] ?? []);

                    $lead = $this->ingest->ingest($tenantModel, [
                        'name' => $leadData['name'] ?? 'Meta Lead',
                        'email' => $leadData['email'] ?? null,
                        'phone' => $leadData['phone'] ?? null,
                        'city' => $leadData['city'] ?? null,
                        'notes' => $leadData['notes'] ?? null,
                        'campaign' => "Meta Form {$formId}",
                        'custom_fields' => ['leadgen_id' => $leadgenId, 'page_id' => $pageId, 'form_id' => $formId],
                    ], 'meta');

                    $this->ingest->logWebhook($tenantModel, 'meta', 'leadgen', $payload, 'processed', $lead->id);
                }
            }
        } catch (\Exception $e) {
            $this->ingest->logWebhook($tenantModel, 'meta', 'leadgen', $payload, 'failed', null, $e->getMessage());

            return response('Error', 500);
        }

        return response('OK', 200);
    }

    protected function verifySignature(Request $request, $tenant): bool
    {
        $secret = $tenant->settings['meta_app_secret'] ?? config('services.meta.app_secret');
        if (! $secret) {
            return true;
        }

        $signature = $request->header('X-Hub-Signature-256');
        if (! $signature) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    protected function fetchLeadFromGraph($tenant, ?string $leadgenId): ?array
    {
        if (! $leadgenId) {
            return null;
        }

        $token = $tenant->settings['meta_page_token'] ?? config('services.meta.page_token');
        if (! $token) {
            return null;
        }

        $response = Http::get("https://graph.facebook.com/v18.0/{$leadgenId}", [
            'access_token' => $token,
            'fields' => 'field_data,campaign_name,ad_name,created_time',
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $this->mapFieldData($response->json('field_data') ?? [], $response->json());
    }

    protected function parseInlineLeadData(array $value): array
    {
        return $this->mapFieldData($value['field_data'] ?? [], $value);
    }

    protected function mapFieldData(array $fieldData, array $meta = []): array
    {
        $mapped = ['notes' => null];
        $keyMap = [
            'full_name' => 'name', 'first_name' => 'name', 'name' => 'name',
            'email' => 'email', 'phone_number' => 'phone', 'phone' => 'phone',
            'city' => 'city',
        ];

        foreach ($fieldData as $field) {
            $key = strtolower($field['name'] ?? '');
            $val = $field['values'][0] ?? '';
            if (isset($keyMap[$key])) {
                $mapped[$keyMap[$key]] = $val;
            } else {
                $mapped['notes'] = trim(($mapped['notes'] ?? '')."\n{$key}: {$val}");
            }
        }

        if (! empty($meta['campaign_name'])) {
            $mapped['campaign'] = $meta['campaign_name'];
        }

        return $mapped;
    }
}
