<?php

namespace App\Livewire\Expenses;

use App\Services\ExpenseService;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $vendor_name = '';
    public string $vendor_gstin = '';
    public string $vendor_state = '';
    public string $invoice_number = '';
    public string $invoice_date = '';
    public string $category = 'general';
    public string $description = '';
    public bool $is_gst_applicable = true;
    public float $taxable_amount = 0;
    public float $gst_rate = 18;
    public string $payment_status = 'paid';
    public string $payment_method = 'bank';
    public $billFile;

    public function mount(): void
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->vendor_state = auth()->user()->tenant?->state ?? '';
    }

    public function save(ExpenseService $expenseService)
    {
        if (! auth()->user()->hasPermission('expenses.manage')) {
            abort(403);
        }

        $this->validate([
            'vendor_name' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'taxable_amount' => 'required|numeric|min:0',
            'category' => 'required|string',
        ]);

        $expense = $expenseService->create([
            'vendor_name' => $this->vendor_name,
            'vendor_gstin' => $this->vendor_gstin ?: null,
            'vendor_state' => $this->vendor_state ?: null,
            'invoice_number' => $this->invoice_number ?: null,
            'invoice_date' => $this->invoice_date,
            'category' => $this->category,
            'description' => $this->description ?: null,
            'is_gst_applicable' => $this->is_gst_applicable,
            'taxable_amount' => $this->taxable_amount,
            'gst_rate' => $this->gst_rate,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
        ], auth()->user()->tenant);

        if ($this->billFile) {
            $path = $this->billFile->store('bills/'.auth()->user()->tenant_id, 'public');
            $expense->update(['bill_path' => $path]);
        }

        session()->flash('success', 'Expense saved / खर्च save ho gaya');

        return redirect()->route('expenses.index');
    }

    public function render()
    {
        $categories = \App\Models\Expense::categories();
        $preview = app(ExpenseService::class)->calculateGst(
            $this->taxable_amount,
            $this->gst_rate,
            $this->is_gst_applicable,
            app(ExpenseService::class)->isInterState(auth()->user()->tenant, $this->vendor_state)
        );

        return view('livewire.expenses.create', compact('categories', 'preview'))
            ->layout('layouts.app');
    }
}
