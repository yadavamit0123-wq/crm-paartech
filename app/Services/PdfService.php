<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Lead;
use App\Models\LeadStage;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function generateDocumentPdf(Document $document): \Barryvdh\DomPDF\PDF
    {
        $document->load(['items', 'customer', 'tenant']);

        $pdf = Pdf::loadView('pdf.document', [
            'document' => $document,
            'tenant' => $document->tenant,
            'items' => $document->items,
            'bank' => $this->bankDetails($document->tenant),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isPhpEnabled', false);
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('dpi', 120);
        $pdf->setOption('enable_font_subsetting', true);

        return $pdf;
    }

    public function download(Document $document)
    {
        $filename = str_replace('/', '-', $document->document_number).'.pdf';

        return $this->generateDocumentPdf($document)->download($filename);
    }

    public function stream(Document $document)
    {
        return $this->generateDocumentPdf($document)->stream(
            str_replace('/', '-', $document->document_number).'.pdf'
        );
    }

    protected function bankDetails($tenant): array
    {
        $settings = $tenant->settings ?? [];

        return [
            'bank_name' => $settings['bank_name'] ?? 'HDFC Bank',
            'account_number' => $settings['bank_account'] ?? 'XXXX XXXX XXXX',
            'ifsc' => $settings['bank_ifsc'] ?? 'HDFC0001234',
            'upi_id' => $settings['upi_id'] ?? 'company@upi',
        ];
    }
}
