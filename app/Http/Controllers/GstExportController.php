<?php

namespace App\Http\Controllers;

use App\Services\GstReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GstExportController extends Controller
{
    public function __construct(protected GstReportService $gstService) {}

    public function gstr1Json(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);
        $period = $request->get('period', now()->format('Y-m'));
        $tenantId = auth()->user()->tenant_id;

        $data = $this->gstService->buildGstr1Data($tenantId, $period);
        $this->gstService->logExport($tenantId, 'gstr1', $period, $data['summary']);

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, "GSTR1_{$period}.json", ['Content-Type' => 'application/json']);
    }

    public function gstr1Csv(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);
        $period = $request->get('period', now()->format('Y-m'));
        $tenantId = auth()->user()->tenant_id;

        $data = $this->gstService->buildGstr1Data($tenantId, $period);
        $this->gstService->logExport($tenantId, 'gstr1', $period, $data['summary']);

        $rows = array_merge($data['b2b_invoices'], $data['b2c_invoices']);
        $headers = ['Invoice No', 'Date', 'Customer', 'GSTIN', 'Place', 'Taxable', 'CGST', 'SGST', 'IGST', 'Total Tax', 'Invoice Value'];

        $csvRows = array_map(fn ($r) => [
            $r['invoice_number'], $r['invoice_date'], $r['customer_name'],
            $r['customer_gstin'] ?? '', $r['place_of_supply'], $r['taxable_value'],
            $r['cgst'], $r['sgst'], $r['igst'], $r['total_tax'], $r['invoice_value'],
        ], $rows);

        $csv = $this->gstService->toCsv($headers, $csvRows);

        return response()->streamDownload(fn () => print($csv), "GSTR1_{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    public function gstr3bJson(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);
        $period = $request->get('period', now()->format('Y-m'));
        $tenantId = auth()->user()->tenant_id;

        $data = $this->gstService->buildGstr3bData($tenantId, $period);
        $this->gstService->logExport($tenantId, 'gstr3b', $period, $data['net_tax_payable']);

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, "GSTR3B_{$period}.json", ['Content-Type' => 'application/json']);
    }

    public function gstr3bCsv(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);
        $period = $request->get('period', now()->format('Y-m'));
        $tenantId = auth()->user()->tenant_id;

        $data = $this->gstService->buildGstr3bData($tenantId, $period);
        $this->gstService->logExport($tenantId, 'gstr3b', $period, $data['net_tax_payable']);

        $headers = ['Section', 'CGST', 'SGST', 'IGST', 'Taxable/Total'];
        $rows = [
            ['Outward Supplies', $data['outward_supplies']['cgst'], $data['outward_supplies']['sgst'], $data['outward_supplies']['igst'], $data['outward_supplies']['taxable_value']],
            ['Input Tax Credit', $data['input_tax_credit']['cgst'], $data['input_tax_credit']['sgst'], $data['input_tax_credit']['igst'], $data['input_tax_credit']['total_purchase_value']],
            ['Net Tax Payable', $data['net_tax_payable']['cgst'], $data['net_tax_payable']['sgst'], $data['net_tax_payable']['igst'], $data['net_tax_payable']['total']],
        ];

        $csv = $this->gstService->toCsv($headers, $rows);

        return response()->streamDownload(fn () => print($csv), "GSTR3B_{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    public function salesRegister(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);
        $period = $request->get('period', now()->format('Y-m'));
        $tenantId = auth()->user()->tenant_id;

        $rows = $this->gstService->buildSalesRegister($tenantId, $period);
        $this->gstService->logExport($tenantId, 'sales_register', $period, ['count' => count($rows)]);

        $headers = ['Date', 'Invoice No', 'Customer', 'GSTIN', 'Taxable', 'CGST', 'SGST', 'IGST', 'Total', 'GST Applicable'];
        $csvRows = array_map(fn ($r) => array_values($r), $rows);
        $csv = $this->gstService->toCsv($headers, $csvRows);

        return response()->streamDownload(fn () => print($csv), "SalesRegister_{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    public function purchaseRegister(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);
        $period = $request->get('period', now()->format('Y-m'));
        $tenantId = auth()->user()->tenant_id;

        $rows = $this->gstService->buildPurchaseRegister($tenantId, $period);
        $this->gstService->logExport($tenantId, 'purchase_register', $period, ['count' => count($rows)]);

        $headers = ['Date', 'Invoice No', 'Vendor', 'GSTIN', 'Category', 'Taxable', 'CGST', 'SGST', 'IGST', 'Total', 'GST Applicable'];
        $csvRows = array_map(fn ($r) => array_values($r), $rows);
        $csv = $this->gstService->toCsv($headers, $csvRows);

        return response()->streamDownload(fn () => print($csv), "PurchaseRegister_{$period}.csv", ['Content-Type' => 'text/csv']);
    }

    protected function authorizeExport(Request $request): void
    {
        if (! auth()->user()?->hasPermission('gst.export')) {
            abort(403);
        }
    }
}
