<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PdfService;
use Illuminate\Http\Request;

class DocumentPdfController extends Controller
{
    public function __construct(protected PdfService $pdfService) {}

    public function download(Request $request, Document $document)
    {
        if (! $request->user()?->hasPermission('documents.view')) {
            abort(403);
        }

        return $this->pdfService->download($document);
    }

    public function stream(Request $request, Document $document)
    {
        if (! $request->user()?->hasPermission('documents.view')) {
            abort(403);
        }

        return $this->pdfService->stream($document);
    }
}
