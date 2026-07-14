<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeadIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadCaptureController extends Controller
{
    public function __construct(protected LeadIngestService $ingest) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_key' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:50',
        ]);

        $tenant = $this->ingest->findTenant($request->tenant_key);

        if (! $tenant) {
            return response()->json(['error' => 'Invalid tenant'], 404);
        }

        $this->ingest->logWebhook($tenant, 'website', 'form_submit', $request->all(), 'received');

        try {
            $lead = $this->ingest->ingest($tenant, [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company' => $request->company,
                'city' => $request->city,
                'state' => $request->state,
                'notes' => $request->message,
                'message' => $request->message,
                'campaign' => $request->campaign ?? $request->utm_campaign,
            ], $request->source ?? 'website');

            $this->ingest->logWebhook($tenant, 'website', 'form_submit', $request->all(), 'processed', $lead->id);

            return response()->json(['success' => true, 'lead_id' => $lead->id], 201);
        } catch (\Exception $e) {
            $this->ingest->logWebhook($tenant, 'website', 'form_submit', $request->all(), 'failed', null, $e->getMessage());

            return response()->json(['error' => 'Failed to save lead'], 500);
        }
    }
}
