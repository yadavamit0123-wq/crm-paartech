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
     * Render PDF then stamp sample footer on EVERY page via canvas.
     * (DomPDF CSS position:fixed footer is unreliable / often invisible.)
     */
    public function renderedPdfOutput(Document $document): string
    {
        $pdf = $this->generateDocumentPdf($document);
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $payload = $this->buildViewData($document);
        $this->stampSampleFooter($dompdf, $payload);

        return $dompdf->output();
    }

    /**
     * Sample footer on EVERY page via canvas page_text (CSS fixed footer fails in DomPDF).
     *
     * DomPDF canvas text Y is top-origin (internally flipped to PDF coords).
     * Footer = high Y near page height (e.g. ~h-30), NOT small Y.
     */
    protected function stampSampleFooter(\Dompdf\Dompdf $dompdf, array $payload): void
    {
        $canvas = $dompdf->getCanvas();
        if (! $canvas) {
            return;
        }

        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $bold = $fontMetrics->getFont('DejaVu Sans', 'bold') ?: $font;
        if (! $font) {
            return;
        }

        $typeLabel = (string) ($payload['typeLabel'] ?? 'Document');
        $document = $payload['document'];
        $no = (string) ($document->document_number ?? '');
        $date = $document->issue_date ? $document->issue_date->format('d M Y') : '';
        $for = (string) ($document->customer_name ?? '');
        $showNexpaar = ! empty($payload['showPoweredByNexpaar']);

        if (mb_strlen($for) > 28) {
            $for = mb_substr($for, 0, 27).'...';
        }

        $grey = [0.61, 0.64, 0.69];
        $dark = [0.07, 0.09, 0.15];
        $mute = [0.42, 0.45, 0.50];
        $nexpaarBrand = [0.49, 0.23, 0.93]; // #7c3aed fixed brand

        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $left = 40;
        $right = $w - 40;

        // Distances from bottom of page → convert to DomPDF top-origin Y
        $y = static fn (float $fromBottom) => $h - $fromBottom;

        // Dashed separator
        $canvas->page_text($left, $y(72), str_repeat('- ', 58), $font, 6, $grey);

        // Meta labels (above values)
        $canvas->page_text($left, $y(60), $typeLabel.' No', $font, 6.5, $grey);
        $canvas->page_text($left + 125, $y(60), $typeLabel.' Date', $font, 6.5, $grey);
        $canvas->page_text($left + 250, $y(60), $typeLabel.' For', $font, 6.5, $grey);

        // Meta values
        $canvas->page_text($left, $y(48), $no, $bold, 8, $dark);
        $canvas->page_text($left + 125, $y(48), $date, $bold, 8, $dark);
        $canvas->page_text($left + 250, $y(48), $for, $bold, 8, $dark);

        // Page X of Y (right)
        $canvas->page_text($right - 95, $y(48), 'Page {PAGE_NUM} of {PAGE_COUNT}', $bold, 8, $dark);

        // Disclaimer (left)
        $canvas->page_text(
            $left,
            $y(28),
            'This is an electronically generated document, no signature is required.',
            $font,
            6,
            $mute
        );

        // Powered by Nexpaar (right, fixed brand color)
        if ($showNexpaar) {
            $canvas->page_text($right - 115, $y(28), 'Powered by Nexpaar', $bold, 8, $nexpaarBrand);
        }
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
        $qtySum = 0.0;
        $showHsnColumn = false;
        $showGstColumn = false;
        $showFullDescription = (bool) ($opts['show_description_full_width'] ?? true);
        foreach ($document->items as $item) {
            if ($item->group_name && $item->group_name !== $prevGroup) {
                $rows[] = ['type' => 'group', 'label' => $item->group_name];
                $prevGroup = $item->group_name;
            }
            $rowNum++;
            $qty = (float) $item->quantity;
            $qtySum += $qty;
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

            $hsnValue = trim((string) ($item->hsn_sac ?? ''));
            if ($hsnValue !== '') {
                $showHsnColumn = true;
            }

            $gstRate = (float) ($item->gst_rate ?? 0);
            if ($isGst && $gstRate > 0) {
                $showGstColumn = true;
            }

            $rows[] = [
                'type' => 'item',
                'num' => $rowNum,
                'title' => $item->description,
                'body' => ($showFullDescription && $item->long_description)
                    ? $this->safeHtml((string) $item->long_description)
                    : '',
                'hsn' => $hsnValue,
                'qty' => abs($qty - round($qty)) < 0.00001 ? number_format($qty, 0) : number_format($qty, 2),
                'unit' => ($item->unit && $item->unit !== 'Nos') ? $item->unit : '',
                'rate' => $this->money((float) $item->rate, $currency),
                'discount' => $discLabel,
                'gst' => $gstRate > 0 ? $this->num($gstRate).'%' : '',
                'amount' => $this->money((float) $item->line_total, $currency, true),
            ];
        }

        $colSpan = 5 + ($showHsnColumn ? 1 : 0) + ($showGstColumn ? 1 : 0);
        if ($showHsnColumn && $showGstColumn) {
            $colWidths = ['item' => '36%', 'hsn' => '10%', 'qty' => '10%', 'rate' => '11%', 'disc' => '11%', 'gst' => '9%', 'amt' => '13%'];
        } elseif ($showHsnColumn || $showGstColumn) {
            $colWidths = ['item' => '42%', 'hsn' => '10%', 'qty' => '10%', 'rate' => '12%', 'disc' => '12%', 'gst' => '9%', 'amt' => '14%'];
        } else {
            $colWidths = ['item' => '48%', 'hsn' => '10%', 'qty' => '12%', 'rate' => '13%', 'disc' => '13%', 'gst' => '9%', 'amt' => '14%'];
        }

        $totals = [
            ['label' => 'Sub Total', 'value' => $this->money((float) $document->subtotal, $currency, true), 'class' => ''],
        ];
        if (! empty($opts['summarise_quantity'])) {
            $totals[] = [
                'label' => 'Total Quantity',
                'value' => abs($qtySum - round($qtySum)) < 0.00001 ? number_format($qtySum, 0) : number_format($qtySum, 2),
                'class' => '',
            ];
        }
        if ((float) $document->discount_amount > 0) {
            $totals[] = ['label' => 'Discount', 'value' => '('.$this->money((float) $document->discount_amount, $currency, true).')', 'class' => 'disc'];
        }
        if ($isGst && ($opts['show_tax_summary'] ?? true) && (float) $document->total_tax > 0) {
            $totals[] = ['label' => 'Tax (GST)', 'value' => $this->money((float) $document->total_tax, $currency, true), 'class' => ''];
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
            'themeLight' => $this->lightenColor($themeColor, 0.90),
            'themeLightBorder' => $this->lightenColor($themeColor, 0.78),
            'typeLabel' => $typeLabel,
            'isGst' => $isGst,
            'currency' => $currency,
            'logoFile' => $this->resolveFile($document->logo_path),
            // Nexpaar mark — fixed brand asset (quotation theme se linked nahi)
            'nexpaarMark' => $this->resolveFile(public_path('images/nexpaar-mark-sm.png'))
                ?: $this->resolveFile(public_path('images/nexpaar-mark.png')),
            'qrFile' => $this->resolveFile($pay['qr_image'] ?? null),
            'sig' => $sig,
            'sigImage' => $this->resolveFile($sig['signature_image'] ?? null),
            'stampImage' => $this->resolveFile($sig['stamp_image'] ?? null),
            'pay' => $pay,
            'rows' => $rows,
            'showHsnColumn' => $showHsnColumn,
            'showGstColumn' => $showGstColumn,
            'colSpan' => $colSpan,
            'colWidths' => $colWidths,
            'totals' => $totals,
            'bank' => $this->bankDetails($tenant),
            'showBankDetails' => array_key_exists('show_bank_details', $opts)
                ? (bool) $opts['show_bank_details']
                : true,
            'sellerAddress' => $tenant?->address ? rtrim((string) $tenant->address, ',').',' : '',
            'sellerCityLine' => trim(implode(', ', $cityParts)),
            'words' => $document->total_in_words ? strtoupper((string) $document->total_in_words) : '',
            'showTaxSummary' => (bool) ($opts['show_tax_summary'] ?? true),
            'hidePlaceOfSupply' => ! empty($opts['hide_place_of_supply']),
            // Checkbox: PDF footer me "Powered by Nexpaar" (correct spelling)
            'showPoweredByNexpaar' => $this->showPoweredByNexpaar($opts),
        ];
    }

    /**
     * Advanced option checkbox — supports new Nexpaar key + old Nexpar typo.
     */
    protected function showPoweredByNexpaar(array $opts): bool
    {
        if (array_key_exists('show_powered_by_nexpaar', $opts)) {
            return (bool) $opts['show_powered_by_nexpaar'];
        }
        if (array_key_exists('show_powered_by_nexpar', $opts)) {
            return (bool) $opts['show_powered_by_nexpar'];
        }

        return true;
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

    /**
     * Mix hex/rgb color toward white. $amount 0 = original, 1 = pure white.
     * Used for soft backgrounds / borders derived from theme color.
     */
    protected function lightenColor(string $color, float $amount = 0.90): string
    {
        $amount = max(0.0, min(1.0, $amount));
        $rgb = $this->colorToRgb($color);
        if ($rgb === null) {
            return '#FFF9F5';
        }

        $r = (int) round($rgb[0] + (255 - $rgb[0]) * $amount);
        $g = (int) round($rgb[1] + (255 - $rgb[1]) * $amount);
        $b = (int) round($rgb[2] + (255 - $rgb[2]) * $amount);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * @return array{0:int,1:int,2:int}|null
     */
    protected function colorToRgb(string $color): ?array
    {
        $color = trim($color);
        if (preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $color, $m)) {
            $hex = $m[1];
            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }

            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ];
        }
        if (preg_match('/^rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/', $color, $m)) {
            return [(int) $m[1], (int) $m[2], (int) $m[3]];
        }

        return null;
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
