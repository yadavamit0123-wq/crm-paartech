<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document->document_number }}</title>
    <style>
        @php
            $themeColor = $document->theme_color ?: '#7c3aed';
            $opts = $document->advanced_options ?? [];
            $hidePlaceOfSupply = ! empty($opts['hide_place_of_supply']);
            $showTaxSummary = $opts['show_tax_summary'] ?? true;
            $isGst = (bool) $document->is_gst_applicable;
            $typeLabel = $document->typeLabel();
            $currency = ($document->currency ?? 'INR') === 'USD' ? '$' : '₹';
            $money = function ($n, bool $force2 = false) use ($currency) {
                $n = (float) $n;
                if ($force2 || abs($n - round($n)) > 0.00001) {
                    return $currency.number_format($n, 2);
                }

                return $currency.number_format($n, 0);
            };
        @endphp

        @page {
            size: A4 portrait;
            margin: 30mm 14mm 18mm 14mm;
        }

        * { margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.45;
        }

        /* Repeating header — sample PT00121 style */
        .top-bar {
            position: fixed;
            top: -26mm;
            left: 0;
            right: 0;
            height: 24mm;
            border-bottom: 1px solid #e5e7eb;
        }
        .top-bar table { width: 100%; border-collapse: collapse; }
        .top-bar .lbl {
            font-size: 8px;
            color: #9ca3af;
            line-height: 1.2;
        }
        .top-bar .val {
            font-size: 10px;
            font-weight: bold;
            color: #111827;
            margin-top: 2px;
            line-height: 1.25;
            word-wrap: break-word;
            max-width: 150px;
        }
        .top-bar .disc {
            font-size: 7.5px;
            color: #9ca3af;
            text-align: right;
            line-height: 1.35;
        }

        .hero { padding-top: 2px; margin-bottom: 14px; }
        .hero-title {
            font-size: 32px;
            font-weight: bold;
            color: {{ $themeColor }};
            letter-spacing: -0.6px;
            line-height: 1.1;
            margin-bottom: 4px;
        }
        .hero-project {
            font-size: 15px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 12px;
            line-height: 1.3;
        }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .meta-table td { font-size: 10px; padding: 2px 0; vertical-align: top; }
        .meta-label { color: #6b7280; width: 120px; }
        .meta-value { color: #111827; font-weight: bold; }

        .parties { width: 100%; border-collapse: collapse; margin: 14px 0 18px; }
        .parties td { width: 48%; vertical-align: top; }
        .parties .gap { width: 4%; }
        .party-title {
            font-size: 12px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 8px;
        }
        .party-name {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }
        .party-line {
            font-size: 9.5px;
            color: #374151;
            line-height: 1.55;
            word-wrap: break-word;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .items thead { display: table-header-group; }
        .items th {
            background-color: {{ $themeColor }};
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 9px 8px;
            text-align: left;
            border: none;
        }
        .items th.r { text-align: right; }
        .items td {
            padding: 10px 8px;
            vertical-align: top;
            border-bottom: 1px solid #f3f4f6;
            font-size: 9.5px;
            color: #374151;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        .items td.r { text-align: right; white-space: nowrap; }
        /* Long feature lists must flow across pages like sample — do NOT avoid breaks on rows */
        .items tbody tr { page-break-inside: auto; }
        .item-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #111827;
            line-height: 1.4;
            margin-bottom: 4px;
        }
        .item-body {
            font-size: 9px;
            color: #4b5563;
            line-height: 1.5;
            margin-top: 4px;
        }
        .item-body ul, .item-body ol { margin: 4px 0 4px 16px; padding: 0; }
        .item-body li { margin: 0 0 3px; }
        .item-body p { margin: 0 0 4px; }
        .hsn {
            font-size: 8px;
            color: #9ca3af;
            margin-top: 5px;
        }
        .group {
            font-size: 10px;
            font-weight: bold;
            color: {{ $themeColor }};
            padding: 8px 0 4px;
            border-bottom: 1px dashed #e5e7eb;
            margin-bottom: 2px;
        }

        .w-item { width: 48%; }
        .w-qty { width: 12%; }
        .w-rate { width: 13%; }
        .w-disc { width: 13%; }
        .w-amt { width: 14%; }

        .totals {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .totals .words {
            width: 52%;
            vertical-align: top;
            padding-right: 18px;
            font-size: 10px;
            color: #111827;
            line-height: 1.45;
        }
        .totals .nums { width: 48%; vertical-align: top; }
        .nums-table { width: 100%; border-collapse: collapse; }
        .nums-table td {
            padding: 5px 6px;
            font-size: 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        .nums-table td:first-child { color: #6b7280; text-align: left; }
        .nums-table td:last-child { text-align: right; font-weight: 600; color: #111827; white-space: nowrap; }
        .nums-table .disc td { color: #dc2626; }
        .nums-table .grand td {
            font-size: 13px;
            font-weight: bold;
            color: {{ $themeColor }};
            border-top: 2px solid {{ $themeColor }};
            border-bottom: none;
            padding-top: 9px;
        }

        .notes {
            margin-top: 16px;
            font-size: 9.5px;
            color: #374151;
            line-height: 1.6;
            word-wrap: break-word;
        }

        .pay-box {
            margin-top: 14px;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            page-break-inside: avoid;
        }
        .pay-box .ttl {
            font-size: 10px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 6px;
        }
        .pay-box .line {
            font-size: 9px;
            color: #374151;
            margin-bottom: 3px;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .bank {
            margin-top: 12px;
            padding: 9px 11px;
            background: #f9fafb;
            border-left: 3px solid {{ $themeColor }};
            font-size: 9px;
            color: #374151;
            line-height: 1.5;
            page-break-inside: avoid;
            word-wrap: break-word;
        }

        .sig {
            margin-top: 22px;
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .sig .line {
            border-top: 1px solid #d1d5db;
            padding-top: 6px;
            min-width: 150px;
            display: inline-block;
            text-align: center;
        }
        .foot {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

@php
    $resolveFile = function (?string $path) {
        if (! $path) {
            return null;
        }
        foreach ([public_path('storage/'.$path), storage_path('app/public/'.$path)] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    };
    $logoFile = $resolveFile($document->logo_path);
    $pay = is_array($document->payment_options ?? null) ? $document->payment_options : [];
    $qrFile = $resolveFile($pay['qr_image'] ?? null);
    $sig = is_array($document->signature_data ?? null) ? $document->signature_data : [];
    $sigImage = $resolveFile($sig['signature_image'] ?? null);
    $stampImage = $resolveFile($sig['stamp_image'] ?? null);
@endphp

{{-- Repeating header (every page) — matches sample --}}
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
            <td style="width:28%; vertical-align:top;" class="disc">
                <span style="font-size:8px;color:#6b7280;">&nbsp;</span><br>
                This is an electronically generated document,<br>no signature is required.
            </td>
        </tr>
    </table>
</div>

{{-- Hero --}}
<div class="hero">
    <div class="hero-title">{{ $typeLabel }}</div>
    @if($document->title)
    <div class="hero-project">{{ $document->title }}</div>
    @endif
    <table class="meta-table">
        <tr>
            <td class="meta-label">{{ $typeLabel }} No #</td>
            <td class="meta-value">{{ $document->document_number }}</td>
            <td style="width:24px;"></td>
            <td class="meta-label">{{ $typeLabel }} Date</td>
            <td class="meta-value">{{ $document->issue_date->format('M d, Y') }}</td>
        </tr>
        @if($document->due_date || $document->valid_until)
        <tr>
            @if($document->due_date)
            <td class="meta-label">Due Date</td>
            <td class="meta-value">{{ $document->due_date->format('M d, Y') }}</td>
            <td></td>
            @else
            <td></td><td></td><td></td>
            @endif
            @if($document->valid_until)
            <td class="meta-label">Valid Until</td>
            <td class="meta-value">{{ $document->valid_until->format('M d, Y') }}</td>
            @else
            <td></td><td></td>
            @endif
        </tr>
        @endif
    </table>
</div>

{{-- From / For --}}
<table class="parties">
    <tr>
        <td>
            <div class="party-title">{{ $typeLabel }} From</div>
            @if($logoFile)
            <img src="{{ $logoFile }}" style="max-height:42px;max-width:130px;margin-bottom:6px;" alt="">
            @endif
            <div class="party-name">{{ $tenant->name }}</div>
            <div class="party-line">
                @if($tenant->address){{ rtrim($tenant->address, ',').',' }}<br>@endif
                @php
                    $cityLine = trim(implode(', ', array_filter([
                        $tenant->city,
                        $tenant->state,
                        ($tenant->country ?: 'India').($tenant->pincode ? ' - '.$tenant->pincode : ''),
                    ])));
                @endphp
                @if($cityLine){{ $cityLine }}<br>@endif
                @if($tenant->email)Email: {{ $tenant->email }}<br>@endif
                @if($tenant->phone)Phone: {{ $tenant->phone }}@endif
                @if($isGst && $tenant->gstin)<br>GSTIN: {{ $tenant->gstin }}@endif
            </div>
        </td>
        <td class="gap"></td>
        <td>
            <div class="party-title">{{ $typeLabel }} For</div>
            <div class="party-name">{{ $document->customer_name }}</div>
            <div class="party-line">
                @if($document->customer_address){{ $document->customer_address }}<br>@endif
                @if($document->customer_state){{ $document->customer_state }}@endif
                @if($isGst && $document->customer_gstin)@if($document->customer_state), @endifGSTIN: {{ $document->customer_gstin }}@endif
                @if($document->customer_state || ($isGst && $document->customer_gstin))<br>@endif
                @if($document->customer_email)Email: {{ $document->customer_email }}<br>@endif
                @if($document->customer_phone)Phone: {{ $document->customer_phone }}@endif
            </div>
        </td>
    </tr>
</table>

{{-- Items (header repeats every page) --}}
<table class="items">
    <thead>
        <tr>
            <th class="w-item">Item</th>
            <th class="w-qty r">Quantity</th>
            <th class="w-rate r">Rate</th>
            <th class="w-disc r">Discount</th>
            <th class="w-amt r">Amount</th>
        </tr>
    </thead>
    <tbody>
        @php $prevGroup = ''; $rowNum = 0; @endphp
        @foreach($items as $item)
            @if($item->group_name && $item->group_name !== $prevGroup)
            <tr>
                <td colspan="5"><div class="group">{{ $item->group_name }}</div></td>
            </tr>
            @php $prevGroup = $item->group_name; @endphp
            @endif
            @php
                $rowNum++;
                $disc = (float) ($item->discount_amount ?? 0);
                $discType = $item->discount_type ?? 'fixed';
            @endphp
            <tr>
                <td>
                    <div class="item-title">{{ $rowNum }}. {{ $item->description }}</div>
                    @if($item->long_description)
                    <div class="item-body">{!! strip_tags($item->long_description, '<br><b><strong><i><em><ul><ol><li><p>') !!}</div>
                    @endif
                    @if($isGst && $item->hsn_sac)
                    <div class="hsn">HSN/SAC: {{ $item->hsn_sac }}@if($showTaxSummary && $item->gst_rate) | GST {{ rtrim(rtrim(number_format((float) $item->gst_rate, 2), '0'), '.') }}%@endif</div>
                    @endif
                </td>
                <td class="r">
                    {{ abs($item->quantity - round($item->quantity)) < 0.00001 ? number_format($item->quantity, 0) : number_format($item->quantity, 2) }}
                    @if($item->unit && $item->unit !== 'Nos')
                    <br><span style="font-size:7.5px;color:#9ca3af;">{{ $item->unit }}</span>
                    @endif
                </td>
                <td class="r">{{ $money($item->rate) }}</td>
                <td class="r">
                    @if($discType === 'percent' && (float) $item->discount_percent > 0)
                        {{ abs($item->discount_percent - round($item->discount_percent)) < 0.00001 ? number_format($item->discount_percent, 0) : number_format($item->discount_percent, 2) }}%
                    @elseif($disc > 0)
                        {{ $money($disc) }}
                    @else
                        {{ $money(0) }}
                    @endif
                </td>
                <td class="r" style="font-weight:bold;color:#111827;">{{ $money($item->line_total, true) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals — sample layout: words left, figures right --}}
<table class="totals">
    <tr>
        <td class="words">
            @if($document->total_in_words)
            <strong>Total (in words) :</strong> {{ strtoupper($document->total_in_words) }}
            @endif
        </td>
        <td class="nums">
            <table class="nums-table">
                <tr>
                    <td>Sub Total</td>
                    <td>{{ $money($document->subtotal, true) }}</td>
                </tr>
                @if((float) $document->discount_amount > 0)
                <tr class="disc">
                    <td>Discount</td>
                    <td>({{ $money($document->discount_amount, true) }})</td>
                </tr>
                @endif
                <tr>
                    <td>Discounts</td>
                    <td>{{ $money(0, true) }}</td>
                </tr>
                <tr>
                    <td>Reductions</td>
                    <td>{{ $money(0, true) }}</td>
                </tr>
                @if($isGst && $showTaxSummary && (float) $document->total_tax > 0)
                <tr>
                    <td>Tax (GST)</td>
                    <td>{{ $money($document->total_tax, true) }}</td>
                </tr>
                @endif
                @if(! $hidePlaceOfSupply && $document->place_of_supply)
                <tr>
                    <td>Place of Supply</td>
                    <td>{{ $document->place_of_supply }}</td>
                </tr>
                @endif
                <tr class="grand">
                    <td>Total ({{ $document->currency ?? 'INR' }})</td>
                    <td>{{ $money($document->grand_total, true) }}</td>
                </tr>
                @if($document->exchange_rate && (float) $document->exchange_rate > 0)
                    @if(($document->currency ?? 'INR') === 'USD')
                    <tr><td>INR Equivalent</td><td>₹ {{ number_format($document->grand_total * $document->exchange_rate, 2) }}</td></tr>
                    @else
                    <tr><td>USD Equivalent</td><td>$ {{ number_format($document->grand_total / $document->exchange_rate, 2) }}</td></tr>
                    @endif
                @endif
            </table>
        </td>
    </tr>
</table>

@if($document->terms_conditions)
<div class="notes">{!! nl2br(e($document->terms_conditions)) !!}</div>
@endif

@if($document->additional_info)
<div class="notes"><strong>Additional Info:</strong> {{ $document->additional_info }}</div>
@endif

@if(! empty($pay['link']) || ! empty($pay['upi']) || $qrFile)
<div class="pay-box">
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:{{ $qrFile ? '78%' : '100%' }};vertical-align:top;">
                <div class="ttl">Payment Options</div>
                @if(! empty($pay['link']))
                <div class="line"><strong>Pay Online:</strong> {{ $pay['link'] }}</div>
                @endif
                @if(! empty($pay['upi']))
                <div class="line"><strong>UPI ID:</strong> {{ $pay['upi'] }}</div>
                @endif
                @if($qrFile)
                <div class="line" style="color:#6b7280;">Scan the QR code to pay via any UPI app.</div>
                @endif
            </td>
            @if($qrFile)
            <td style="width:22%;text-align:right;vertical-align:middle;">
                <img src="{{ $qrFile }}" style="width:72px;height:72px;" alt="QR">
            </td>
            @endif
        </tr>
    </table>
</div>
@endif

<div class="bank">
    <strong>Bank Details:</strong>
    {{ $bank['bank_name'] }} &nbsp;|&nbsp; A/C: {{ $bank['account_number'] }} &nbsp;|&nbsp; IFSC: {{ $bank['ifsc'] }} &nbsp;|&nbsp; UPI: {{ $bank['upi_id'] }}
</div>

@if(! empty($sig))
<table class="sig">
    <tr>
        <td style="width:58%;"></td>
        @if($stampImage)
        <td style="width:18%;text-align:center;vertical-align:bottom;">
            <img src="{{ $stampImage }}" style="max-height:60px;max-width:78px;" alt="">
        </td>
        @endif
        <td style="width:{{ $stampImage ? '24%' : '42%' }};text-align:right;vertical-align:bottom;">
            <div style="display:inline-block;text-align:center;">
                @if($sigImage)
                <img src="{{ $sigImage }}" style="max-height:38px;max-width:140px;margin-bottom:4px;" alt=""><br>
                @else
                <div style="height:28px;"></div>
                @endif
                <div class="line">
                    <strong style="font-size:10px;color:#111827;">{{ $sig['name'] ?? '' }}</strong><br>
                    <span style="font-size:8.5px;color:#6b7280;">{{ $sig['title'] ?? 'Authorised Signatory' }}</span>
                </div>
            </div>
        </td>
    </tr>
</table>
@endif

<div class="foot">
    This is a computer generated {{ strtolower($typeLabel) }} and does not require a physical signature or stamp.
</div>

<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_script('
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
            $pdf->text(478, 38, $text, $font, 7.5, array(0.42, 0.45, 0.50));
        ');
    }
</script>

</body>
</html>
