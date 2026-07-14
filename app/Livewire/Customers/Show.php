<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Payment;
use App\Services\PaymentService;
use Livewire\Component;

class Show extends Component
{
    public Customer $customer;

    public string $planTitle = '';
    public float $planTotal = 0;
    public string $planDescription = '';
    public array $milestones = [];
    public bool $showPlanForm = false;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer->load(['lead', 'paymentPlans.milestones', 'documents', 'payments']);
        $this->resetMilestones();
    }

    public function resetMilestones(): void
    {
        $this->milestones = [
            ['name' => 'Advance', 'percentage' => 60, 'trigger_event' => 'On confirmation', 'due_date' => ''],
            ['name' => 'After Delivery', 'percentage' => 30, 'trigger_event' => 'After APK/App delivery', 'due_date' => ''],
            ['name' => 'Final', 'percentage' => 10, 'trigger_event' => 'After go-live', 'due_date' => ''],
        ];
    }

    public function addMilestone(): void
    {
        $this->milestones[] = ['name' => '', 'percentage' => 0, 'trigger_event' => '', 'due_date' => ''];
    }

    public function removeMilestone(int $index): void
    {
        unset($this->milestones[$index]);
        $this->milestones = array_values($this->milestones);
    }

    public function createPaymentPlan(PaymentService $paymentService): void
    {
        if (! auth()->user()->hasPermission('payments.manage')) {
            abort(403);
        }

        $this->validate([
            'planTitle' => 'required|string|max:255',
            'planTotal' => 'required|numeric|min:1',
            'milestones' => 'required|array|min:1',
            'milestones.*.name' => 'required|string|max:255',
            'milestones.*.percentage' => 'required|numeric|min:0.01|max:100',
        ]);

        try {
            $paymentService->createPlanWithMilestones([
                'tenant_id' => auth()->user()->tenant_id,
                'customer_id' => $this->customer->id,
                'lead_id' => $this->customer->lead_id,
                'title' => $this->planTitle,
                'total_amount' => $this->planTotal,
                'description' => $this->planDescription,
            ], $this->milestones);

            $this->showPlanForm = false;
            $this->planTitle = '';
            $this->planTotal = 0;
            $this->planDescription = '';
            $this->resetMilestones();
            $this->customer->refresh();
            $this->dispatch('notify', message: 'Payment plan created / Payment plan ban gaya');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function approveMilestone(int $milestoneId, PaymentService $paymentService): void
    {
        $milestone = $this->customer->paymentPlans->flatMap->milestones->firstWhere('id', $milestoneId);
        if ($milestone) {
            $paymentService->approveMilestone($milestone);
            $this->customer->refresh();
            $this->dispatch('notify', message: 'Milestone approved — ab payment link generate karein');
        }
    }

    public function generateLink(int $milestoneId, PaymentService $paymentService): void
    {
        $milestone = $this->customer->paymentPlans->flatMap->milestones->firstWhere('id', $milestoneId);
        if (! $milestone) {
            return;
        }

        try {
            $paymentService->generatePaymentLink($milestone);
            $this->customer->refresh();
            $this->dispatch('notify', message: 'Payment link + QR generated!');
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function recordPayment(int $milestoneId, string $method, PaymentService $paymentService): void
    {
        $milestone = $this->customer->paymentPlans->flatMap->milestones->firstWhere('id', $milestoneId);
        if ($milestone) {
            $paymentService->recordManualPayment($milestone, $method);
            $this->customer->refresh();
            $this->dispatch('notify', message: 'Payment recorded / Payment save ho gaya');
        }
    }

    public function render()
    {
        $documents = Document::where('customer_id', $this->customer->id)->latest()->get();
        $payments = Payment::where('customer_id', $this->customer->id)->latest()->get();

        return view('livewire.customers.show', compact('documents', 'payments'))
            ->layout('layouts.app');
    }
}
