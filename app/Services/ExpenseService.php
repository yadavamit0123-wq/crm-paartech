<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Tenant;

class ExpenseService
{
    public function calculateGst(float $taxable, float $gstRate, bool $isGst, bool $isInterState): array
    {
        if (! $isGst) {
            return [
                'taxable_amount' => round($taxable, 2),
                'cgst_amount' => 0,
                'sgst_amount' => 0,
                'igst_amount' => 0,
                'total_amount' => round($taxable, 2),
            ];
        }

        if ($isInterState) {
            $igst = round($taxable * ($gstRate / 100), 2);

            return [
                'taxable_amount' => round($taxable, 2),
                'cgst_amount' => 0,
                'sgst_amount' => 0,
                'igst_amount' => $igst,
                'total_amount' => round($taxable + $igst, 2),
            ];
        }

        $halfRate = $gstRate / 2;
        $cgst = round($taxable * ($halfRate / 100), 2);
        $sgst = $cgst;

        return [
            'taxable_amount' => round($taxable, 2),
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => 0,
            'total_amount' => round($taxable + $cgst + $sgst, 2),
        ];
    }

    public function isInterState(Tenant $tenant, ?string $vendorState = null): bool
    {
        $tenantState = strtolower(trim($tenant->state ?? ''));
        $vendorSt = strtolower(trim($vendorState ?? ''));

        if (empty($tenantState) || empty($vendorSt)) {
            return false;
        }

        return $tenantState !== $vendorSt;
    }

    public function create(array $data, Tenant $tenant): Expense
    {
        $taxable = (float) ($data['taxable_amount'] ?? 0);
        $gstRate = (float) ($data['gst_rate'] ?? 18);
        $isGst = (bool) ($data['is_gst_applicable'] ?? true);
        $isInterState = $this->isInterState($tenant, $data['vendor_state'] ?? null);

        $amounts = $this->calculateGst($taxable, $gstRate, $isGst, $isInterState);

        return Expense::create([
            'tenant_id' => $tenant->id,
            'vendor_name' => $data['vendor_name'],
            'vendor_gstin' => $data['vendor_gstin'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'category' => $data['category'] ?? 'general',
            'description' => $data['description'] ?? null,
            'is_gst_applicable' => $isGst,
            'gst_rate' => $gstRate,
            'payment_status' => $data['payment_status'] ?? 'paid',
            'payment_method' => $data['payment_method'] ?? 'bank',
            'created_by' => auth()->id(),
            ...$amounts,
        ]);
    }
}
