<?php

namespace App\Livewire\Accounting;

use App\Models\Document;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public string $period;

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        [$year, $month] = explode('-', $this->period);

        $salesTotal = Document::where('tenant_id', $tenantId)
            ->where('type', 'invoice')
            ->whereYear('issue_date', $year)
            ->whereMonth('issue_date', $month)
            ->sum('grand_total');

        $salesTax = Document::where('tenant_id', $tenantId)
            ->where('type', 'invoice')
            ->whereYear('issue_date', $year)
            ->whereMonth('issue_date', $month)
            ->sum('total_tax');

        $purchaseTotal = Expense::where('tenant_id', $tenantId)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->sum('total_amount');

        $purchaseTax = Expense::where('tenant_id', $tenantId)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->where('is_gst_applicable', true)
            ->sum(DB::raw('cgst_amount + sgst_amount + igst_amount'));

        $paymentsReceived = Payment::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->sum('amount');

        $expenseCount = Expense::where('tenant_id', $tenantId)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->count();

        $invoiceCount = Document::where('tenant_id', $tenantId)
            ->where('type', 'invoice')
            ->whereYear('issue_date', $year)
            ->whereMonth('issue_date', $month)
            ->count();

        return view('livewire.accounting.dashboard', compact(
            'salesTotal', 'salesTax', 'purchaseTotal', 'purchaseTax',
            'paymentsReceived', 'expenseCount', 'invoiceCount'
        ))->layout('layouts.app');
    }
}
