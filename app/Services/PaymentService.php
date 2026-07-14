<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentMilestone;
use App\Models\PaymentPlan;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    public function createPlanWithMilestones(array $planData, array $milestones): PaymentPlan
    {
        $totalAmount = (float) $planData['total_amount'];
        $percentSum = collect($milestones)->sum('percentage');

        if (abs($percentSum - 100) > 0.01) {
            throw new \InvalidArgumentException('Milestone percentages must total 100%');
        }

        $plan = PaymentPlan::create([
            'tenant_id' => $planData['tenant_id'],
            'customer_id' => $planData['customer_id'],
            'lead_id' => $planData['lead_id'] ?? null,
            'title' => $planData['title'],
            'total_amount' => $totalAmount,
            'description' => $planData['description'] ?? null,
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        foreach ($milestones as $idx => $m) {
            $plan->milestones()->create([
                'name' => $m['name'],
                'percentage' => $m['percentage'],
                'amount' => round($totalAmount * ($m['percentage'] / 100), 2),
                'trigger_event' => $m['trigger_event'] ?? null,
                'sort_order' => $idx + 1,
                'status' => 'pending',
                'due_date' => $m['due_date'] ?? null,
            ]);
        }

        return $plan->load('milestones');
    }

    public function approveMilestone(PaymentMilestone $milestone): PaymentMilestone
    {
        $milestone->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return $milestone;
    }

    public function generatePaymentLink(PaymentMilestone $milestone): PaymentMilestone
    {
        if ($milestone->status === 'pending') {
            throw new \RuntimeException('Milestone must be approved before generating payment link.');
        }

        $plan = $milestone->plan()->with('customer')->first();
        $amount = (float) $milestone->amount;
        $amountPaise = (int) round($amount * 100);

        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if ($key && $secret) {
            $response = Http::withBasicAuth($key, $secret)->post('https://api.razorpay.com/v1/payment_links', [
                'amount' => $amountPaise,
                'currency' => 'INR',
                'description' => "{$plan->title} - {$milestone->name}",
                'customer' => [
                    'name' => $plan->customer->name,
                    'email' => $plan->customer->email ?? 'customer@example.com',
                    'contact' => $plan->customer->phone ?? '9999999999',
                ],
                'notify' => ['sms' => true, 'email' => true],
                'reminder_enable' => true,
                'notes' => [
                    'milestone_id' => (string) $milestone->id,
                    'plan_id' => (string) $plan->id,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $paymentLink = $data['short_url'] ?? $data['url'] ?? null;

                $milestone->update([
                    'payment_link' => $paymentLink,
                    'payment_qr_url' => $this->qrCodeUrl($paymentLink),
                    'razorpay_payment_link_id' => $data['id'] ?? null,
                    'status' => 'link_sent',
                ]);

                return $milestone->fresh();
            }
        }

        $paymentLink = url("/pay/{$milestone->id}");

        $milestone->update([
            'payment_link' => $paymentLink,
            'payment_qr_url' => $this->qrCodeUrl($paymentLink),
            'status' => 'link_sent',
        ]);

        return $milestone->fresh();
    }

    public function recordManualPayment(PaymentMilestone $milestone, string $method, ?string $reference = null, ?string $notes = null): Payment
    {
        $plan = $milestone->plan;

        $payment = Payment::create([
            'tenant_id' => $plan->tenant_id,
            'customer_id' => $plan->customer_id,
            'payment_plan_id' => $plan->id,
            'payment_milestone_id' => $milestone->id,
            'amount' => $milestone->amount,
            'method' => $method,
            'status' => 'completed',
            'reference_number' => $reference,
            'paid_at' => now(),
            'notes' => $notes,
            'recorded_by' => auth()->id(),
        ]);

        $milestone->update(['status' => 'paid']);

        $this->checkPlanCompletion($plan);

        return $payment;
    }

    public function qrCodeUrl(?string $data): ?string
    {
        if (! $data) {
            return null;
        }

        return 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data='.urlencode($data);
    }

    protected function checkPlanCompletion(PaymentPlan $plan): void
    {
        $allPaid = $plan->milestones()->where('status', '!=', 'paid')->count() === 0;

        if ($allPaid) {
            $plan->update(['status' => 'completed']);
        }
    }
}
