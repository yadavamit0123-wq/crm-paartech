<?php

namespace App\Services;

use App\Models\GoogleReview;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReviewReplyService
{
    public function detectSentiment(int $rating, ?string $text): string
    {
        if ($rating >= 4) {
            return 'positive';
        }
        if ($rating === 3) {
            return 'neutral';
        }

        return 'negative';
    }

    public function generateReply(GoogleReview $review, Tenant $tenant): string
    {
        $apiKey = config('services.openai.key');

        if ($apiKey && ($tenant->settings['auto_reply_openai'] ?? true)) {
            $aiReply = $this->generateOpenAiReply($review, $tenant, $apiKey);
            if ($aiReply) {
                return $aiReply;
            }
        }

        return $this->generateRuleBasedReply($review, $tenant);
    }

    public function processNewReview(GoogleReview $review): GoogleReview
    {
        $tenant = $review->tenant ?? Tenant::find($review->tenant_id);

        if ($review->rating >= 4 || ($tenant->settings['auto_reply_reviews'] ?? true)) {
            $reply = $this->generateReply($review, $tenant);
            $review->update([
                'reply_text' => $reply,
                'auto_replied' => true,
                'reply_sent_at' => now(),
                'sentiment' => $this->detectSentiment($review->rating, $review->review_text),
            ]);
        } else {
            $review->update([
                'sentiment' => $this->detectSentiment($review->rating, $review->review_text),
            ]);
        }

        return $review->fresh();
    }

    public function getReviewLink(Tenant $tenant): string
    {
        return $tenant->settings['google_review_link']
            ?? 'https://search.google.com/local/writereview?placeid='.($tenant->settings['google_place_id'] ?? '');
    }

    protected function generateOpenAiReply(GoogleReview $review, Tenant $tenant, string $apiKey): ?string
    {
        try {
            $response = Http::timeout(15)->withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You write short, warm, professional Google review replies for {$tenant->name}. Always positive tone. Max 2 sentences. Mix Hindi-English if review is in Hindi. Never be negative.",
                    ],
                    [
                        'role' => 'user',
                        'content' => "Customer {$review->reviewer_name} gave {$review->rating} stars and wrote: \"{$review->review_text}\". Write a thank-you reply.",
                    ],
                ],
                'max_tokens' => 150,
            ]);

            if ($response->successful()) {
                return trim($response->json('choices.0.message.content'));
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    protected function generateRuleBasedReply(GoogleReview $review, Tenant $tenant): string
    {
        $name = Str::before($review->reviewer_name, ' ');
        $company = $tenant->name;

        $templates = [
            5 => "Thank you so much, {$name}! 🙏 We're thrilled you had a great experience with {$company}. Your kind words motivate our entire team!",
            4 => "Thank you, {$name}! We appreciate your feedback and are glad we could serve you well at {$company}. Looking forward to working with you again!",
            3 => "Thank you for your feedback, {$name}. We appreciate you choosing {$company} and will keep improving our service.",
            2 => "Thank you for sharing your experience, {$name}. We take your feedback seriously and would love to make things right. Please reach out to us directly.",
            1 => "We're sorry to hear about your experience, {$name}. Please contact {$company} directly so we can address your concerns immediately.",
        ];

        $base = $templates[$review->rating] ?? $templates[5];

        if ($review->review_text && $review->rating >= 4) {
            if (Str::contains(strtolower($review->review_text), ['service', 'सर्विस'])) {
                $base = "Thank you, {$name}! Delighted that you loved our service at {$company}. Your support means everything to us! 🙏";
            } elseif (Str::contains(strtolower($review->review_text), ['team', 'staff', 'टीम'])) {
                $base = "Thank you, {$name}! Our team will be so happy to hear this. We truly appreciate your trust in {$company}!";
            }
        }

        return $base;
    }
}
