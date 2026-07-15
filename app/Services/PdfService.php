<?php

namespace App\Services;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PdfService
{
    public function generateDocumentPdf(Document $document): \Barryvdh\DomPDF\PDF
    {
        $document->load(['items', 'customer', 'tenant']);

        // Avoid crashing if payment_options migration not yet run on server
        if (! Schema::hasColumn('documents', 'payment_options')) {
            $document->offsetUnset('payment_options');
        }

        try {
            $pdf = Pdf::loadView('pdf.document', [
                'document' => $document,
                'tenant' => $document->tenant,
                'items' => $document->items,
                'bank' => $this->bankDetails($document->tenant),
            ]);

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isPhpEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'DejaVu Sans');
            $pdf->setOption('dpi', 96);

            return $pdf;
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
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
