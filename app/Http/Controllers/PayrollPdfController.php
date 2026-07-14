<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;
use App\Services\PdfService;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollPdfController extends Controller
{
    public function download(PayrollRun $payrollRun)
    {
        if (! auth()->user()?->hasPermission('payroll.manage')) {
            abort(403);
        }

        $payrollRun->load(['entries.user', 'tenant']);

        $pdf = Pdf::loadView('pdf.payslip-batch', [
            'run' => $payrollRun,
            'tenant' => auth()->user()->tenant,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Payroll_{$payrollRun->year}_{$payrollRun->month}.pdf");
    }
}
