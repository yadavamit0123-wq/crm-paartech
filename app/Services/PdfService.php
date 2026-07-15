<?php

namespace App\Services;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PdfService
{
    public function generateDocumentPdf(Document $document): \Barryvdh\DomPDF\PDF
    {
        $document->load(['items', 'customer', 'tenant']);

        $payload = $this->buildViewData($document);

        $pdf = Pdf::loadView('pdf.document', $payload);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isPhpEnabled', false);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('dpi', 96);
        $pdf->setOption('isFontSubsettingEnabled', true);

        return $pdf;
    }

    /**
     * Render + stamp "Page X of Y" like the sample footer (right side).
     */
    public function renderedPdfOutput(Document $document): string
    {
        $pdf = $this->generateDocumentPdf($document);
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        if ($canvas && $font) {
            // A4 points (~595×842); bottom-right near sample footer meta row
            $canvas->page_text(472, 798, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.17, 0.24, 0.31]);
        }

        return $dompdf->output();
    }

    public function download(Document $document)
    {
        try {
            $filename = str_replace('/', '-', (string) $document->document_number).'.pdf';

            return response($this->renderedPdfOutput($document), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (Throwable $e) {
            Log::error('PDF download failed', ['doc' => $document->id, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            throw $e;
        }
    }

    public function stream(Document $document)
    {
        try {
            $filename = str_replace('/', '-', (string) $document->document_number).'.pdf';

            return response($this->renderedPdfOutput($document), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        } catch (Throwable $e) {
            Log::error('PDF stream failed', ['doc' => $document->id, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            throw $e;
        }
    }

    /**
     * All formatting here — blade mein heavy PHP/closures mat rakho (DomPDF 500 ka common reason).
     */
    protected function buildViewData(Document $document): array
    {
        $tenant = $document->tenant;
        $themeColor = $this->safeColor($document->theme_color ?: '#7c3aed');
        $opts = is_array($document->advanced_options) ? $document->advanced_options : [];
        $isGst = (bool) $document->is_gst_applicable;
        $typeLabel = $document->typeLabel();
        $currency = ($document->currency ?? 'INR') === 'USD' ? '$' : 'Rs.';

        $pay = [];
        try {
            if (Schema::hasColumn('documents', 'payment_options')) {
                $rawPay = $document->getAttributes()['payment_options'] ?? null;
                if (is_string($rawPay)) {
                    $decoded = json_decode($rawPay, true);
                    $pay = is_array($decoded) ? $decoded : [];
                } elseif (is_array($document->payment_options)) {
                    $pay = $document->payment_options;
                }
            }
        } catch (Throwable) {
            $pay = [];
        }

        $sig = [];
        try {
            $sig = is_array($document->signature_data) ? $document->signature_data : [];
        } catch (Throwable) {
            $sig = [];
        }

        $rows = [];
        $prevGroup = '';
        $rowNum = 0;
        foreach ($document->items as $item) {
            if ($item->group_name && $item->group_name !== $prevGroup) {
                $rows[] = ['type' => 'group', 'label' => $item->group_name];
                $prevGroup = $item->group_name;
            }
            $rowNum++;
            $qty = (float) $item->quantity;
            $discAmt = (float) ($item->discount_amount ?? 0);
            $discPct = (float) ($item->discount_percent ?? 0);
            $discType = $item->discount_type ?? 'fixed';

            if ($discType === 'percent' && $discPct > 0) {
                $discLabel = $this->num($discPct).'%';
            } elseif ($discAmt > 0) {
                $discLabel = $this->money($discAmt, $currency);
            } else {
                $discLabel = $this->money(0, $currency);
            }

            $rows[] = [
                'type' => 'item',
                'num' => $rowNum,
                'title' => $item->description,
                'body' => $item->long_description
                    ? $this->safeHtml((string) $item->long_description)
                    : '',
                'hsn' => ($isGst && $item->hsn_sac)
                    ? ('HSN/SAC: '.$item->hsn_sac.((! empty($opts['show_tax_summary'] ?? true) && (float) $item->gst_rate > 0)
                        ? ' | GST '.$this->num((float) $item->gst_rate).'%'
                        : ''))
                    : '',
                'qty' => abs($qty - round($qty)) < 0.00001 ? number_format($qty, 0) : number_format($qty, 2),
                'unit' => ($item->unit && $item->unit !== 'Nos') ? $item->unit : '',
                'rate' => $this->money((float) $item->rate, $currency),
                'discount' => $discLabel,
                'amount' => $this->money((float) $item->line_total, $currency, true),
            ];
        }

        $totals = [
            ['label' => 'Sub Total', 'value' => $this->money((float) $document->subtotal, $currency, true), 'class' => ''],
        ];
        if ((float) $document->discount_amount > 0) {
            $totals[] = ['label' => 'Discount', 'value' => '('.$this->money((float) $document->discount_amount, $currency, true).')', 'class' => 'disc'];
        }
        $totals[] = ['label' => 'Discounts', 'value' => $this->money(0, $currency, true), 'class' => ''];
        $totals[] = ['label' => 'Reductions', 'value' => $this->money(0, $currency, true), 'class' => ''];
        if ($isGst && ($opts['show_tax_summary'] ?? true) && (float) $document->total_tax > 0) {
            $totals[] = ['label' => 'Tax (GST)', 'value' => $this->money((float) $document->total_tax, $currency, true), 'class' => ''];
        }
        if (empty($opts['hide_place_of_supply']) && $document->place_of_supply) {
            $totals[] = ['label' => 'Place of Supply', 'value' => $document->place_of_supply, 'class' => ''];
        }
        $totals[] = [
            'label' => 'Total ('.($document->currency ?? 'INR').')',
            'value' => $this->money((float) $document->grand_total, $currency, true),
            'class' => 'grand',
        ];

        $cityParts = array_filter([
            $tenant?->city,
            $tenant?->state,
            trim(($tenant?->country ?: 'India').($tenant?->pincode ? ' - '.$tenant->pincode : '')),
        ]);

        return [
            'document' => $document,
            'tenant' => $tenant,
            'themeColor' => $themeColor,
            'typeLabel' => $typeLabel,
            'isGst' => $isGst,
            'currency' => $currency,
            'logoFile' => $this->resolveFile($document->logo_path),
            'nexparMark' => $this->resolveFile(public_path('images/nexpar-mark.png')),
            'qrFile' => $this->resolveFile($pay['qr_image'] ?? null),
            'sig' => $sig,
            'sigImage' => $this->resolveFile($sig['signature_image'] ?? null),
            'stampImage' => $this->resolveFile($sig['stamp_image'] ?? null),
            'pay' => $pay,
            'rows' => $rows,
            'totals' => $totals,
            'bank' => $this->bankDetails($tenant),
            'sellerAddress' => $tenant?->address ? rtrim((string) $tenant->address, ',').',' : '',
            'sellerCityLine' => trim(implode(', ', $cityParts)),
            'words' => $document->total_in_words ? strtoupper((string) $document->total_in_words) : '',
            'showTaxSummary' => (bool) ($opts['show_tax_summary'] ?? true),
            'hidePlaceOfSupply' => ! empty($opts['hide_place_of_supply']),
            // Checkbox: PDF footer me "Powered by Nexpar" dikhana ya nahi
            'showPoweredByNexpar' => array_key_exists('show_powered_by_nexpar', $opts)
                ? (bool) $opts['show_powered_by_nexpar']
                : true,
        ];
    }

    protected function money(float $n, string $symbol, bool $force2 = false): string
    {
        if ($force2 || abs($n - round($n)) > 0.00001) {
            return $symbol.number_format($n, 2);
        }

        return $symbol.number_format($n, 0);
    }

    protected function num(float $n): string
    {
        return abs($n - round($n)) < 0.00001 ? number_format($n, 0) : rtrim(rtrim(number_format($n, 2), '0'), '.');
    }

    protected function safeHtml(string $html): string
    {
        $html = strip_tags($html, '<br><b><strong><i><em><ul><ol><li><p>');
        // Empty tags / broken markup DomPDF crash avoid
        $html = preg_replace('/<(p|li|ul|ol|br)[^>]*>/i', '<$1>', $html) ?? $html;
        $html = str_replace(['<p>', '</p>'], ['', '<br>'], $html);

        return trim($html);
    }

    protected function resolveFile(?string $path): ?string
    {
        if (! $path || ! is_string($path)) {
            return null;
        }

        // Already a data URI
        if (str_starts_with($path, 'data:image/')) {
            return $path;
        }

        foreach ([public_path('storage/'.$path), storage_path('app/public/'.$path), $path] as $candidate) {
            if (! is_file($candidate) || ! is_readable($candidate)) {
                continue;
            }
            $bytes = @file_get_contents($candidate);
            if ($bytes === false || $bytes === '') {
                continue;
            }
            // Huge logos DomPDF crash kar sakte hain
            if (strlen($bytes) > 1_500_000) {
                continue;
            }
            $mime = @mime_content_type($candidate) ?: 'image/png';
            if (! str_starts_with($mime, 'image/')) {
                $mime = 'image/png';
            }

            return 'data:'.$mime.';base64,'.base64_encode($bytes);
        }

        return null;
    }

    protected function safeColor(string $color): string
    {
        $color = trim($color);
        if (preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $color)) {
            return $color;
        }
        if (preg_match('/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/', $color)) {
            return $color;
        }

        return '#7c3aed';
    }

    protected function bankDetails($tenant): array
    {
        $settings = is_array($tenant?->settings ?? null) ? $tenant->settings : [];

        return [
            'bank_name' => $settings['bank_name'] ?? 'HDFC Bank',
            'account_number' => $settings['bank_account'] ?? 'XXXX XXXX XXXX',
            'ifsc' => $settings['bank_ifsc'] ?? 'HDFC0001234',
            'upi_id' => $settings['upi_id'] ?? 'company@upi',
        ];
    }
}
