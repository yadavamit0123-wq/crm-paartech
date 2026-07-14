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

        return Pdf::loadView('pdf.document', [
            'document' => $document,
            'tenant' => $document->tenant,
            'items' => $document->items,
            'bank' => $this->bankDetails($document->tenant),
        ])->setPaper('a4');
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
