<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Throwable;

class DocumentPdfController extends Controller
{
    public function __construct(protected PdfService $pdfService) {}

    public function download(Request $request, Document $document)
    {
        if (! $request->user()?->hasPermission('documents.view')) {
            abort(403);
        }

        try {
            return $this->pdfService->download($document);
        } catch (Throwable $e) {
            return $this->pdfError($request, $e);
        }
    }

    public function stream(Request $request, Document $document)
    {
        if (! $request->user()?->hasPermission('documents.view')) {
            abort(403);
        }

        try {
            return $this->pdfService->stream($document);
        } catch (Throwable $e) {
            return $this->pdfError($request, $e);
        }
    }

    protected function pdfError(Request $request, Throwable $e)
    {
        report($e);

        // Temporary visible diagnosis — iframe me text dikhega (blank 500 ki jagah)
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>PDF Error</title></head><body style="font-family:sans-serif;padding:24px;color:#991b1b;background:#fef2f2">'
            .'<h2 style="margin:0 0 8px">PDF generate nahi ho paya</h2>'
            .'<p style="color:#7f1d1d">Neeche error copy karke support ko bhejein / repair chalao:</p>'
            .'<pre style="white-space:pre-wrap;background:#fff;border:1px solid #fecaca;padding:12px;border-radius:8px;font-size:12px;color:#111">'
            .e($e->getMessage()."\n\n".$e->getFile().':'.$e->getLine())
            .'</pre>'
            .'<p style="font-size:12px;color:#6b7280">cPanel Terminal: <code>cd ~/crm.paartech.in &amp;&amp; php artisan view:clear &amp;&amp; php artisan migrate --force</code></p>'
            .'</body></html>';

        return response($html, 500)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
