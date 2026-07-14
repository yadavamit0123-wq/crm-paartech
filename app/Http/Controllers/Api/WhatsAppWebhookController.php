<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeadIngestService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppWebhookController extends Controller
{
    public function __construct(protected LeadIngestService $ingest) {}

    public function verify(Request $request, string $tenant): Response
    {
        $tenantModel = $this->ingest->findTenant($tenant);
        if (! $tenantModel) {
            abort(404);
        }

        $verifyToken = $tenantModel->settings['whatsapp_verify_token']
            ?? config('services.whatsapp.verify_token');

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
        $this->ingest->logWebhook($tenantModel, 'whatsapp', 'message', $payload, 'received');

        try {
            $entries = $payload['entry'] ?? [];

            foreach ($entries as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    $value = $change['value'] ?? [];
                    $messages = $value['messages'] ?? [];

                    foreach ($messages as $message) {
                        if (($message['type'] ?? '') !== 'text') {
                            continue;
                        }

                        $from = $message['from'] ?? '';
                        $text = $message['text']['body'] ?? '';
                        $contactName = $value['contacts'][0]['profile']['name'] ?? 'WhatsApp User';

                        $lead = $this->ingest->ingest($tenantModel, [
                            'name' => $contactName,
                            'phone' => $from,
                            'notes' => $text,
                            'message' => $text,
                            'campaign' => 'WhatsApp Inbound',
                        ], 'whatsapp');

                        $this->ingest->logWebhook($tenantModel, 'whatsapp', 'message', $payload, 'processed', $lead->id);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->ingest->logWebhook($tenantModel, 'whatsapp', 'message', $payload, 'failed', null, $e->getMessage());

            return response('Error', 500);
        }

        return response('OK', 200);
    }
}
