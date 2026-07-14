<?php

namespace App\Services;

use App\Models\ReviewRequest;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;

class ReviewRequestService
{
    public function __construct(protected ReviewReplyService $replyService) {}

    public function send(Tenant $tenant, ?int $leadId, ?int $customerId, string $name, ?string $phone, ?string $email, string $channel = 'whatsapp'): ReviewRequest
    {
        $reviewLink = $this->replyService->getReviewLink($tenant);
        $message = "Hi {$name}! Thank you for choosing {$tenant->name}. We'd love your feedback on Google ⭐:\n{$reviewLink}";

        $request = ReviewRequest::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $leadId,
            'customer_id' => $customerId,
            'channel' => $channel,
            'status' => 'sent',
            'review_link' => $reviewLink,
            'sent_at' => now(),
            'sent_by' => auth()->id(),
        ]);

        if ($channel === 'whatsapp' && $phone) {
            $this->sendWhatsApp($tenant, $phone, $message);
        }

        return $request;
    }

    protected function sendWhatsApp(Tenant $tenant, string $phone, string $message): void
    {
        $token = $tenant->settings['whatsapp_token'] ?? config('services.whatsapp.token');
        $phoneId = $tenant->settings['whatsapp_phone_number_id'] ?? config('services.whatsapp.phone_number_id');

        if (! $token || ! $phoneId) {
            return;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 10) {
            $digits = '91'.$digits;
        }

        Http::withToken($token)->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $digits,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);
    }
}
