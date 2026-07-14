<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document->document_number }}</title>
    <style>
        @php
            $themeColor = $document->theme_color ?? '#7c3aed';
            $opts = $document->advanced_options ?? [];
            $hidePlaceOfSupply = $opts['hide_place_of_supply'] ?? false;
            $showTaxSummary = $opts['show_tax_summary'] ?? true;
            $isGst = $document->is_gst_applicable;
            $typeLabel = $document->typeLabel();
            $currency = $document->currency === 'USD' ? '$' : '₹';
            $hasDiscountCol = true;
        @endphp

        @page {
            size: A4 portrait;
            margin: 28mm 12mm 20mm 12mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #374151;
            line-height: 1.5;
        }

        /* ── Repeating top bar (fixed = every page) ── */
        .top-bar {
            position: fixed;
            top: -26mm;
            left: 0;
            right: 0;
            height: 22mm;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
        }
        .top-bar table { width: 100%; border-collapse: collapse; }
        .top-bar .lbl { font-size: 7px; color: #9ca3af; text-transform: capitalize; }
        .top-bar .val { font-size: 9px; font-weight: bold; color: #111827; margin-top: 2px; }
        .top-bar .disclaimer { font-size: 6.5px; color: #9ca3af; text-align: right; line-height: 1.3; }

        /* ── Hero (page 1 only feel) ── */
        .hero { margin-bottom: 16px; padding-top: 4px; }
        .hero-title {
            font-size: 28px;
            font-weight: bold;
            color: {{ $themeColor }};
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }
        .hero-project {
            font-size: 14px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 10px;
        }
        .hero-meta table { width: 100%; }
        .hero-meta td { font-size: 9.5px; padding: 2px 0; vertical-align: top; }
        .hero-meta .label { color: #6b7280; width: 110px; }
        .hero-meta .value { color: #111827; font-weight: bold; }

        /* ── From / For ── */
        .parties { width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 10px 0; }
        .party-box {
            width: 48%;
            vertical-align: top;
            padding: 0;
        }
        .party-title {
            font-size: 11px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 8px;
        }
        .party-name { font-size: 11px; font-weight: bold; color: #111827; margin-bottom: 4px; }
        .party-line { font-size: 9px; color: #4b5563; line-height: 1.55; }

        /* ── Items table ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 0;
        }
        .items-table thead { display: table-header-group; }
        .items-table th {
            background: {{ $themeColor }};
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            border: none;
        }
        .items-table th.right { text-align: right; }
        .items-table td {
            padding: 10px 6px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
            font-size: 9px;
            word-wrap: break-word;
        }
        .items-table tbody tr { page-break-inside: avoid; }
        .item-num { font-weight: bold; color: #111827; font-size: 10px; }
        .item-name { font-weight: bold; color: #111827; font-size: 10px; line-height: 1.4; }
        .item-body { font-size: 8.5px; color: #4b5563; margin-top: 6px; line-height: 1.45; }
        .item-body ul { margin: 4px 0 4px 14px; padding: 0; }
        .item-body li { margin-bottom: 2px; }
        .item-body p { margin-bottom: 3px; }
        .group-label {
            font-size: 9px;
            font-weight: bold;
            color: {{ $themeColor }};
            padding: 6px 0 4px;
            border-bottom: 1px dashed #e5e7eb;
            margin-bottom: 4px;
        }
        .col-item { width: 52%; }
        .col-qty { width: 10%; }
        .col-rate { width: 12%; }
        .col-disc { width: 12%; }
        .col-amt { width: 14%; }

        /* ── Totals block (last page) ── */
        .totals-wrap {
            page-break-inside: avoid;
            margin-top: 12px;
            width: 100%;
        }
        .totals-wrap table { width: 100%; border-collapse: collapse; }
        .words-cell {
            vertical-align: top;
            width: 55%;
            padding-right: 16px;
            font-size: 9px;
            color: #374151;
        }
        .words-cell strong { color: #111827; }
        .nums-cell { vertical-align: top; width: 45%; }
        .nums-table { width: 100%; border-collapse: collapse; }
        .nums-table td {
            padding: 5px 8px;
            font-size: 9px;
            border-bottom: 1px solid #f3f4f6;
        }
        .nums-table td:first-child { color: #6b7280; text-align: left; }
        .nums-table td:last-child { text-align: right; font-weight: 600; color: #111827; }
        .nums-table .total-row td {
            font-size: 12px;
            font-weight: bold;
            color: {{ $themeColor }};
            border-top: 2px solid {{ $themeColor }};
            border-bottom: none;
            padding-top: 8px;
        }
        .nums-table .discount-row td { color: #dc2626; }

        /* ── Footer notes ── */
        .footer-notes {
            margin-top: 14px;
            font-size: 8.5px;
            color: #4b5563;
            line-height: 1.55;
            page-break-inside: avoid;
        }
        .bank-strip {
            margin-top: 12px;
            padding: 8px 10px;
            background: #f9fafb;
            border-left: 3px solid {{ $themeColor }};
            font-size: 8.5px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    {{-- ═══ REPEATING HEADER (every page) ═══ --}}
    <div class="top-bar">
        <table>
            <tr>
                <td style="width:22%; vertical-align:top;">
                    <div class="lbl">{{ $typeLabel }} No</div>
                    <div class="val">{{ $document->document_number }}</div>
                </td>
                <td style="width:22%; vertical-align:top;">
                    <div class="lbl">{{ $typeLabel }} Date</div>
                    <div class="val">{{ $document->issue_date->format('d M Y') }}</div>
                </td>
                <td style="width:28%; vertical-align:top;">
                    <div class="lbl">{{ $typeLabel }} For</div>
                    <div class="val">{{ $document->customer_name }}</div>
                </td>
                <td style="width:28%; vertical-align:top;" class="disclaimer">
                    <span class="page-number"></span><br>
                    This is an electronically generated document,<br>no signature is required.
                </td>
            </tr>
        </table>
    </div>

    {{-- ═══ HERO TITLE ═══ --}}
    <div class="hero">
        <div class="hero-title">{{ $typeLabel }}</div>
        @if($document->title)
        <div class="hero-project">{{ $document->title }}</div>
        @endif
        <div class="hero-meta">
            <table>
                <tr>
                    <td class="label">{{ $typeLabel }} No #</td>
                    <td class="value">{{ $document->document_number }}</td>
                    <td style="width:30px;"></td>
                    <td class="label">{{ $typeLabel }} Date</td>
                    <td class="value">{{ $document->issue_date->format('M d, Y') }}</td>
                </tr>
                @if($document->due_date || $document->valid_until)
                <tr>
                    @if($document->due_date)
                    <td class="label">Due Date</td>
                    <td class="value">{{ $document->due_date->format('M d, Y') }}</td>
                    <td></td>
                    @else
                    <td></td><td></td><td></td>
                    @endif
                    @if($document->valid_until)
                    <td class="label">Valid Until</td>
                    <td class="value">{{ $document->valid_until->format('M d, Y') }}</td>
                    @else
                    <td></td><td></td>
                    @endif
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══ FROM / FOR ═══ --}}
    <table class="parties">
        <tr>
            <td class="party-box">
                <div class="party-title">{{ $typeLabel }} From</div>
                @php
                    // storage:link missing hone par bhi logo dikhe — direct storage path fallback
                    $logoFile = null;
                    if ($document->logo_path) {
                        foreach ([public_path('storage/'.$document->logo_path), storage_path('app/public/'.$document->logo_path)] as $candidate) {
                            if (file_exists($candidate)) { $logoFile = $candidate; break; }
                        }
                    }
                @endphp
                @if($logoFile)
                <img src="{{ $logoFile }}" style="max-height:44px;max-width:120px;margin-bottom:6px;" alt="">
                @endif
                <div class="party-name">{{ $tenant->name }}</div>
                <div class="party-line">
                    @if($tenant->address){{ $tenant->address }},<br>@endif
                    @if($tenant->city || $tenant->state)
                    {{ trim(implode(', ', array_filter([$tenant->city, $tenant->state]))) }}@if($tenant->pincode), India - {{ $tenant->pincode }}@endif<br>
                    @endif
                    @if($tenant->email)Email: {{ $tenant->email }}<br>@endif
                    @if($tenant->phone)Phone: {{ $tenant->phone }}@endif
                    @if($tenant->gstin && $isGst)<br>GSTIN: {{ $tenant->gstin }}@endif
                </div>
            </td>
            <td style="width:4%;"></td>
            <td class="party-box">
                <div class="party-title">{{ $typeLabel }} For</div>
                <div class="party-name">{{ $document->customer_name }}</div>
                <div class="party-line">
                    @if($document->customer_address){{ $document->customer_address }}<br>@endif
                    @if($document->customer_state){{ $document->customer_state }}@if($document->customer_gstin && $isGst), @endif @endif
                    @if($document->customer_gstin && $isGst)GSTIN: {{ $document->customer_gstin }}<br>@endif
                    @if($document->customer_email)Email: {{ $document->customer_email }}<br>@endif
                    @if($document->customer_phone)Phone: {{ $document->customer_phone }}@endif
                </div>
            </td>
        </tr>
    </table>

    {{-- ═══ LINE ITEMS ═══ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-item">Item</th>
                <th class="col-qty right">Quantity</th>
                <th class="col-rate right">Rate</th>
                @if($hasDiscountCol)<th class="col-disc right">Discount</th>@endif
                <th class="col-amt right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $prevGroup = ''; $rowNum = 0; @endphp
            @foreach($items as $item)
            @if($item->group_name && $item->group_name !== $prevGroup)
            <tr>
                <td colspan="{{ $hasDiscountCol ? 5 : 4 }}">
                    <div class="group-label">{{ $item->group_name }}</div>
                </td>
            </tr>
            @php $prevGroup = $item->group_name; @endphp
            @endif
            @php
                $rowNum++;
                $gross = $item->quantity * $item->rate;
                $disc = $item->discount_amount > 0 ? $item->discount_amount : 0;
            @endphp
            <tr>
                <td>
                    <div class="item-name">{{ $rowNum }}. {{ $item->description }}</div>
                    @if($item->long_description)
                    <div class="item-body">{!! strip_tags($item->long_description, '<br><b><strong><i><em><ul><ol><li><p>') !!}</div>
                    @endif
                    @if($isGst && $item->hsn_sac)
                    <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;">HSN/SAC: {{ $item->hsn_sac }}@if($showTaxSummary && $item->gst_rate) | GST {{ $item->gst_rate }}%@endif</div>
                    @endif
                </td>
                <td class="right" style="vertical-align:top;padding-top:12px;">
                    {{ number_format($item->quantity, 0) == $item->quantity ? number_format($item->quantity, 0) : number_format($item->quantity, 2) }}
                    @if($item->unit && $item->unit !== 'Nos')<br><span style="font-size:7px;color:#9ca3af;">{{ $item->unit }}</span>@endif
                </td>
                <td class="right" style="vertical-align:top;padding-top:12px;">{{ $currency }}{{ number_format($item->rate, 0) == $item->rate ? number_format($item->rate, 0) : number_format($item->rate, 2) }}</td>
                @if($hasDiscountCol)
                <td class="right" style="vertical-align:top;padding-top:12px;">
                    @if(($item->discount_type ?? 'fixed') === 'percent')
                        {{ number_format($item->discount_percent, 0) == $item->discount_percent ? number_format($item->discount_percent, 0) : number_format($item->discount_percent, 2) }}%
                    @elseif($disc > 0)
                        {{ $currency }}{{ number_format($disc, 0) == $disc ? number_format($disc, 0) : number_format($disc, 2) }}
                    @else
                        {{ $currency }}0
                    @endif
                </td>
                @endif
                <td class="right" style="vertical-align:top;padding-top:12px;font-weight:bold;">{{ $currency }}{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══ TOTALS (reference style: words left, numbers right) ═══ --}}
    <div class="totals-wrap">
        <table>
            <tr>
                <td class="words-cell">
                    @if($document->total_in_words)
                    <strong>Total (in words):</strong> {{ strtoupper($document->total_in_words) }}
                    @endif
                </td>
                <td class="nums-cell">
                    <table class="nums-table">
                        <tr><td>Sub Total</td><td>{{ $currency }}{{ number_format($document->subtotal, 2) }}</td></tr>
                        @if($document->discount_amount > 0)
                        <tr class="discount-row"><td>Discount</td><td>({{ $currency }}{{ number_format($document->discount_amount, 2) }})</td></tr>
                        @endif
                        @if($isGst && $showTaxSummary && $document->total_tax > 0)
                        <tr><td>Tax (GST)</td><td>{{ $currency }}{{ number_format($document->total_tax, 2) }}</td></tr>
                        @endif
                        @if(!$hidePlaceOfSupply && $document->place_of_supply)
                        <tr><td>Place of Supply</td><td>{{ $document->place_of_supply }}</td></tr>
                        @endif
                        <tr class="total-row">
                            <td>Total ({{ $document->currency ?? 'INR' }})</td>
                            <td>{{ $currency }}{{ number_format($document->grand_total, 2) }}</td>
                        </tr>
                        @if($document->exchange_rate && $document->exchange_rate > 0)
                        @if($document->currency === 'USD')
                        <tr><td>INR Equivalent</td><td>₹ {{ number_format($document->grand_total * $document->exchange_rate, 2) }}</td></tr>
                        @else
                        <tr><td>USD Equivalent</td><td>$ {{ number_format($document->grand_total / $document->exchange_rate, 2) }}</td></tr>
                        @endif
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Terms & Notes --}}
    @if($document->terms_conditions)
    <div class="footer-notes">
        {!! nl2br(e($document->terms_conditions)) !!}
    </div>
    @endif

    @if($document->additional_info)
    <div class="footer-notes" style="margin-top:8px;">
        <strong>Additional Info:</strong> {{ $document->additional_info }}
    </div>
    @endif

    @if($document->signature_data)
    <div style="margin-top:20px;text-align:right;page-break-inside:avoid;">
        <div style="display:inline-block;text-align:left;border-top:1px solid #d1d5db;padding-top:6px;min-width:160px;">
            <strong>{{ $document->signature_data['name'] ?? '' }}</strong><br>
            <span style="font-size:8px;color:#6b7280;">{{ $document->signature_data['title'] ?? '' }}</span>
        </div>
    </div>
    @endif

    <div class="bank-strip">
        <strong>Bank Details:</strong>
        {{ $bank['bank_name'] }} | A/C: {{ $bank['account_number'] }} | IFSC: {{ $bank['ifsc'] }} | UPI: {{ $bank['upi_id'] }}
    </div>

    {{-- Page numbers --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $fontBold = $fontMetrics->getFont('DejaVu Sans', 'bold');
            $size = 7;
            $color = array(0.55, 0.55, 0.55);

            $pdf->page_script('
                $font = $fontMetrics->getFont("DejaVu Sans", "normal");
                $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
                $pdf->text(480, 42, $text, $font, 7, array(0.45, 0.45, 0.45));
            ');
        }
    </script>

</body>
</html>
