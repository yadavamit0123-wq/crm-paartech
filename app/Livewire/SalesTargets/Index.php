<?php

namespace App\Livewire\SalesTargets;

use App\Models\SalesTarget;
use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public int $month;
    public int $year;
    public bool $showModal = false;
    public ?int $userId = null;
    public string $metricType = 'sales_amount';
    public string $targetValue = '';

    public array $metrics = [
        'sales_amount' => 'Sales Amount',
        'calls' => 'Calls',
        'quotations' => 'Quotations',
        'orders' => 'Orders',
        'invoices' => 'Invoices',
        'leads_created' => 'Leads Created',
    ];

    public function mount(): void
    {
        $this->month = (int) now()->format('n');
        $this->year = (int) now()->format('Y');
    }

    public function openCreate(): void
    {
        $this->reset(['userId', 'targetValue']);
        $this->metricType = 'sales_amount';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'userId' => 'required|exists:users,id',
            'targetValue' => 'required|numeric|min:0',
        ]);

        SalesTarget::updateOrCreate(
            [
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => $this->userId,
                'metric_type' => $this->metricType,
                'month' => $this->month,
                'year' => $this->year,
            ],
            ['target_value' => $this->targetValue]
        );

        $this->showModal = false;
        $this->dispatch('notify', message: 'Target saved');
    }

    public function render()
    {
        $targets = SalesTarget::with('user')
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get()
            ->groupBy('user_id');

        $employees = User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();

        return view('livewire.sales-targets.index', compact('targets', 'employees'))
            ->layout('layouts.app');
    }
}
