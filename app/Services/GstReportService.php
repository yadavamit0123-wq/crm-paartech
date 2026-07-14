<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Expense;
use App\Models\GstReturnLog;
use App\Models\Tenant;
use Carbon\Carbon;

class GstReportService
{
    public function periodRange(string $period): array
    {
        // period format: YYYY-MM
        [$year, $month] = explode('-', $period);
        $start = Carbon::create((int) $year, (int) $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return [$start, $end];
    }

    public function getSalesInvoices(int $tenantId, string $period)
    {
        [$start, $end] = $this->periodRange($period);

        return Document::where('tenant_id', $tenantId)
            ->where('type', 'invoice')
            ->whereBetween('issue_date', [$start, $end])
            ->with('items')
            ->orderBy('issue_date')
            ->get();
    }

    public function getPurchases(int $tenantId, string $period)
    {
        [$start, $end] = $this->periodRange($period);

        return Expense::where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$start, $end])
            ->orderBy('invoice_date')
            ->get();
    }

    public function buildGstr1Data(int $tenantId, string $period): array
    {
        $invoices = $this->getSalesInvoices($tenantId, $period);
        $tenant = Tenant::find($tenantId);

        $b2b = [];
        $b2c = [];

        foreach ($invoices as $inv) {
            if (! $inv->is_gst_applicable) {
                continue;
            }

            $row = [
                'invoice_number' => $inv->document_number,
                'invoice_date' => $inv->issue_date->format('d-m-Y'),
                'customer_name' => $inv->customer_name,
                'customer_gstin' => $inv->customer_gstin,
                'place_of_supply' => $inv->place_of_supply,
                'taxable_value' => (float) $inv->taxable_amount,
                'cgst' => (float) $inv->cgst_amount,
                'sgst' => (float) $inv->sgst_amount,
                'igst' => (float) $inv->igst_amount,
                'total_tax' => (float) $inv->total_tax,
                'invoice_value' => (float) $inv->grand_total,
            ];

            if ($inv->customer_gstin) {
                $b2b[] = $row;
            } else {
                $b2c[] = $row;
            }
        }

        return [
            'gstin' => $tenant->gstin,
            'legal_name' => $tenant->name,
            'period' => $period,
            'b2b_invoices' => $b2b,
            'b2c_invoices' => $b2c,
            'summary' => [
                'total_invoices' => $invoices->count(),
                'taxable_value' => round($invoices->where('is_gst_applicable', true)->sum('taxable_amount'), 2),
                'total_cgst' => round($invoices->sum('cgst_amount'), 2),
                'total_sgst' => round($invoices->sum('sgst_amount'), 2),
                'total_igst' => round($invoices->sum('igst_amount'), 2),
                'total_tax' => round($invoices->sum('total_tax'), 2),
                'total_value' => round($invoices->sum('grand_total'), 2),
            ],
        ];
    }

    public function buildGstr3bData(int $tenantId, string $period): array
    {
        $invoices = $this->getSalesInvoices($tenantId, $period);
        $expenses = $this->getPurchases($tenantId, $period);
        $tenant = Tenant::find($tenantId);

        $outwardTaxable = $invoices->where('is_gst_applicable', true)->sum('taxable_amount');
        $outwardCgst = $invoices->sum('cgst_amount');
        $outwardSgst = $invoices->sum('sgst_amount');
        $outwardIgst = $invoices->sum('igst_amount');

        $itcCgst = $expenses->where('is_gst_applicable', true)->sum('cgst_amount');
        $itcSgst = $expenses->where('is_gst_applicable', true)->sum('sgst_amount');
        $itcIgst = $expenses->where('is_gst_applicable', true)->sum('igst_amount');

        $netCgst = max(0, $outwardCgst - $itcCgst);
        $netSgst = max(0, $outwardSgst - $itcSgst);
        $netIgst = max(0, $outwardIgst - $itcIgst);

        return [
            'gstin' => $tenant->gstin,
            'legal_name' => $tenant->name,
            'period' => $period,
            'outward_supplies' => [
                'taxable_value' => round($outwardTaxable, 2),
                'cgst' => round($outwardCgst, 2),
                'sgst' => round($outwardSgst, 2),
                'igst' => round($outwardIgst, 2),
            ],
            'input_tax_credit' => [
                'cgst' => round($itcCgst, 2),
                'sgst' => round($itcSgst, 2),
                'igst' => round($itcIgst, 2),
                'total_expenses' => $expenses->count(),
                'total_purchase_value' => round($expenses->sum('total_amount'), 2),
            ],
            'net_tax_payable' => [
                'cgst' => round($netCgst, 2),
                'sgst' => round($netSgst, 2),
                'igst' => round($netIgst, 2),
                'total' => round($netCgst + $netSgst + $netIgst, 2),
            ],
        ];
    }

    public function buildSalesRegister(int $tenantId, string $period): array
    {
        $invoices = $this->getSalesInvoices($tenantId, $period);

        return $invoices->map(fn ($inv) => [
            'date' => $inv->issue_date->format('d-m-Y'),
            'invoice_no' => $inv->document_number,
            'customer' => $inv->customer_name,
            'gstin' => $inv->customer_gstin ?? 'Unregistered',
            'taxable' => (float) $inv->taxable_amount,
            'cgst' => (float) $inv->cgst_amount,
            'sgst' => (float) $inv->sgst_amount,
            'igst' => (float) $inv->igst_amount,
            'total' => (float) $inv->grand_total,
            'gst_applicable' => $inv->is_gst_applicable ? 'Yes' : 'No',
        ])->toArray();
    }

    public function buildPurchaseRegister(int $tenantId, string $period): array
    {
        $expenses = $this->getPurchases($tenantId, $period);

        return $expenses->map(fn ($exp) => [
            'date' => $exp->invoice_date->format('d-m-Y'),
            'invoice_no' => $exp->invoice_number ?? '-',
            'vendor' => $exp->vendor_name,
            'gstin' => $exp->vendor_gstin ?? 'Unregistered',
            'category' => $exp->category,
            'taxable' => (float) $exp->taxable_amount,
            'cgst' => (float) $exp->cgst_amount,
            'sgst' => (float) $exp->sgst_amount,
            'igst' => (float) $exp->igst_amount,
            'total' => (float) $exp->total_amount,
            'gst_applicable' => $exp->is_gst_applicable ? 'Yes' : 'No',
        ])->toArray();
    }

    public function logExport(int $tenantId, string $type, string $period, array $summary): GstReturnLog
    {
        return GstReturnLog::create([
            'tenant_id' => $tenantId,
            'return_type' => $type,
            'period' => $period,
            'status' => 'exported',
            'summary' => $summary,
            'exported_by' => auth()->id(),
            'exported_at' => now(),
        ]);
    }

    public function toCsv(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);

        foreach ($rows as $row) {
            fputcsv($output, is_array($row) ? array_values($row) : [$row]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
