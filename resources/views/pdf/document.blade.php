<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $document->document_number }}</title>
    <style>
        /* Compact A4: room for repeating footer on every page */
        @@page { size: A4 portrait; margin: 12mm 11mm 16mm 11mm; }
        * { margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
        }

        /* Page outliner — content ke aligned professional border */
        .sheet {
            border: 2px solid #d1d5db;
            padding: 12px 12px 10px;
        }

        /* Footer: har page pe chhota slogan (DomPDF fixed) */
        .page-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -11mm;
            text-align: center;
            font-size: 7px;
            color: #9ca3af;
            line-height: 1.3;
        }

        /* Header: title left + logo right (sample jaisa) */
        .header { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header td { vertical-align: top; }
        .doc-title {
            font-size: 30px;
            font-weight: bold;
            color: {{ $themeColor }};
            letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .doc-project {
            font-size: 13px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-top: 3px;
            margin-bottom: 8px;
        }
        .meta { width: 100%; border-collapse: collapse; }
        .meta td { padding: 1px 0; font-size: 10px; vertical-align: top; }
        .meta-label { color: #6b7280; width: 118px; }
        .meta-value { color: #111827; font-weight: bold; }
        .logo-wrap { text-align: right; }
        .logo-wrap img { max-height: 52px; max-width: 150px; }

        /* From / For — bade columns, soft box, aligned */
        .parties {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 10px 0 12px;
        }
        .parties td.box {
            width: 50%;
            vertical-align: top;
            background: #FFF4EC;
            border: 1px solid #f3e8de;
            padding: 10px 12px;
        }
        .party-title {
            font-size: 13px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 6px;
        }
        .party-name {
            font-size: 12.5px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
            line-height: 1.35;
        }
        .party-line {
            font-size: 10.5px;
            color: #374151;
            line-height: 1.5;
            word-wrap: break-word;
        }

        /* Items table — sample columns, tight gaps */
        .items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .items thead { display: table-header-group; }
        .items th {
            background-color: {{ $themeColor }};
            color: #fff;
            font-size: 9.5px;
            font-weight: bold;
            padding: 8px 7px;
            text-align: left;
            border: none;
        }
        .items th.r { text-align: right; }
        .items th.c { text-align: center; }
        .items td {
            padding: 8px 7px;
            vertical-align: top;
            border-bottom: 1px solid #f3e8de;
            font-size: 10px;
            color: #374151;
            word-wrap: break-word;
            background: #FFF9F5;
        }
        .items td.r { text-align: right; }
        .items td.c { text-align: center; }
        .item-title { font-size: 11px; font-weight: bold; color: #111827; line-height: 1.35; }
        .item-body { font-size: 9px; color: #4b5563; line-height: 1.45; margin-top: 4px; }
        .item-body ul, .item-body ol { margin: 3px 0 3px 14px; padding: 0; }
        .item-body li { margin: 0 0 2px; }
        .hsn { font-size: 8px; color: #9ca3af; margin-top: 4px; }
        .group-row td {
            background-color: {{ $themeColor }} !important;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 7px;
            border-bottom: none;
        }

        .w-item { width: 46%; }
        .w-qty { width: 12%; }
        .w-rate { width: 14%; }
        .w-disc { width: 14%; }
        .w-amt { width: 14%; }

        /* Totals — table width ke saath align */
        .totals {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .totals .words {
            width: 52%;
            vertical-align: top;
            padding-right: 12px;
            font-size: 10px;
            color: #111827;
            word-wrap: break-word;
        }
        .totals .nums { width: 48%; vertical-align: top; }
        .nums-table { width: 100%; border-collapse: collapse; }
        .nums-table td {
            padding: 4px 6px;
            font-size: 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        .nums-table td:first-child { color: #6b7280; text-align: left; }
        .nums-table td:last-child { text-align: right; font-weight: 600; color: #111827; }
        .nums-table .disc td { color: #dc2626; }
        .nums-table .grand td {
            font-size: 12.5px;
            font-weight: bold;
            color: {{ $themeColor }};
            border-top: 2px solid {{ $themeColor }};
            border-bottom: none;
            padding-top: 7px;
        }

        .notes {
            margin-top: 10px;
            font-size: 9.5px;
            color: #374151;
            line-height: 1.5;
            word-wrap: break-word;
            background: #FFF9F5;
            border: 1px solid #f3e8de;
            padding: 8px 10px;
        }
        .pay-box {
            margin-top: 10px;
            border: 1px solid #f3e8de;
            background: #FFF9F5;
            padding: 8px 10px;
            page-break-inside: avoid;
        }
        .pay-box .ttl { font-size: 10.5px; font-weight: bold; color: {{ $themeColor }}; margin-bottom: 5px; }
        .pay-box .line { font-size: 9.5px; color: #374151; margin-bottom: 2px; word-wrap: break-word; }
        .bank {
            margin-top: 8px;
            padding: 7px 10px;
            background: #FFF9F5;
            border-left: 3px solid {{ $themeColor }};
            font-size: 9.5px;
            color: #374151;
            page-break-inside: avoid;
            word-wrap: break-word;
        }
        .sig {
            margin-top: 16px;
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

{{-- Har page pe chhota footer slogan --}}
<div class="page-footer">
    This is a computer generated {{ strtolower($typeLabel) }} and does not require a physical signature or stamp.
</div>

<div class="sheet">

{{-- Compact header: title + meta LEFT, logo RIGHT --}}
<table class="header">
    <tr>
        <td style="width:68%;">
            <div class="doc-title">{{ $typeLabel }}</div>
            @if($document->title)
            <div class="doc-project">{{ $document->title }}</div>
            @else
            <div style="height:6px;"></div>
            @endif
            <table class="meta">
                <tr>
                    <td class="meta-label">{{ $typeLabel }} No #</td>
                    <td class="meta-value">{{ $document->document_number }}</td>
                </tr>
                <tr>
                    <td class="meta-label">{{ $typeLabel }} Date</td>
                    <td class="meta-value">{{ optional($document->issue_date)->format('M d, Y') }}</td>
                </tr>
                @if($document->due_date)
                <tr>
                    <td class="meta-label">Due Date</td>
                    <td class="meta-value">{{ $document->due_date->format('M d, Y') }}</td>
                </tr>
                @endif
                @if($document->valid_until)
                <tr>
                    <td class="meta-label">Valid Until</td>
                    <td class="meta-value">{{ $document->valid_until->format('M d, Y') }}</td>
                </tr>
                @endif
            </table>
        </td>
        <td style="width:32%;" class="logo-wrap">
            @if(!empty($logoFile))
            <img src="{{ $logoFile }}" alt="">
            @else
            <div style="font-size:14px;font-weight:bold;color:{{ $themeColor }};">{{ $tenant->name ?? '' }}</div>
            @endif
        </td>
    </tr>
</table>

{{-- Company / Customer — equal columns, larger text --}}
<table class="parties">
    <tr>
        <td class="box">
            <div class="party-title">{{ $typeLabel }} From</div>
            <div class="party-name">{{ $tenant->name ?? '' }}</div>
            <div class="party-line">
                @if($sellerAddress)
                    {{ $sellerAddress }}<br>
                @endif
                @if($sellerCityLine)
                    {{ $sellerCityLine }}<br>
                @endif
                @if($tenant->email ?? null)
                    Email: {{ $tenant->email }}<br>
                @endif
                @if($tenant->phone ?? null)
                    Phone: {{ $tenant->phone }}
                @endif
                @if($isGst && ($tenant->gstin ?? null))
                    <br>GSTIN: {{ $tenant->gstin }}
                @endif
            </div>
        </td>
        <td class="box">
            <div class="party-title">{{ $typeLabel }} For</div>
            <div class="party-name">{{ $document->customer_name }}</div>
            <div class="party-line">
                @if($document->customer_address)
                    {{ $document->customer_address }}<br>
                @endif
                @if($document->customer_state)
                    {{ $document->customer_state }}
                @endif
                @if($isGst && $document->customer_gstin)
                    @if($document->customer_state), @endif
                    GSTIN: {{ $document->customer_gstin }}
                @endif
                @if($document->customer_state || ($isGst && $document->customer_gstin))
                    <br>
                @endif
                @if($document->customer_email)
                    Email: {{ $document->customer_email }}<br>
                @endif
                @if($document->customer_phone)
                    Phone: {{ $document->customer_phone }}
                @endif
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th class="w-item">Item</th>
            <th class="w-qty c">Quantity</th>
            <th class="w-rate r">Rate</th>
            <th class="w-disc r">Discount</th>
            <th class="w-amt r">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            @if($row['type'] === 'group')
            <tr class="group-row"><td colspan="5">{{ $row['label'] }}</td></tr>
            @else
            <tr>
                <td>
                    <div class="item-title">{{ $row['num'] }}. {{ $row['title'] }}</div>
                    @if($row['body'] !== '')
                    <div class="item-body">{!! $row['body'] !!}</div>
                    @endif
                    @if($row['hsn'] !== '')
                    <div class="hsn">{{ $row['hsn'] }}</div>
                    @endif
                </td>
                <td class="c">
                    {{ $row['qty'] }}
                    @if($row['unit'] !== '')
                    <br><span style="font-size:7.5px;color:#9ca3af;">{{ $row['unit'] }}</span>
                    @endif
                </td>
                <td class="r">{{ $row['rate'] }}</td>
                <td class="r">{{ $row['discount'] }}</td>
                <td class="r" style="font-weight:bold;color:#111827;">{{ $row['amount'] }}</td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td class="words">
            @if($words !== '')
            <strong>Total (in words) :</strong> {{ $words }}
            @endif
        </td>
        <td class="nums">
            <table class="nums-table">
                @foreach($totals as $t)
                <tr class="{{ $t['class'] }}">
                    <td>{{ $t['label'] }}</td>
                    <td>{{ $t['value'] }}</td>
                </tr>
                @endforeach
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

@if(!empty($pay['link']) || !empty($pay['upi']) || !empty($qrFile))
<div class="pay-box">
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:{{ !empty($qrFile) ? '78%' : '100%' }};vertical-align:top;">
                <div class="ttl">Payment Options</div>
                @if(!empty($pay['link']))
                <div class="line"><strong>Pay Online:</strong> {{ $pay['link'] }}</div>
                @endif
                @if(!empty($pay['upi']))
                <div class="line"><strong>UPI ID:</strong> {{ $pay['upi'] }}</div>
                @endif
                @if(!empty($qrFile))
                <div class="line" style="color:#6b7280;">Scan the QR code to pay via any UPI app.</div>
                @endif
            </td>
            @if(!empty($qrFile))
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
    {{ $bank['bank_name'] }} | A/C: {{ $bank['account_number'] }} | IFSC: {{ $bank['ifsc'] }} | UPI: {{ $bank['upi_id'] }}
</div>

@if(!empty($sig))
<table class="sig">
    <tr>
        <td style="width:58%;"></td>
        @if(!empty($stampImage))
        <td style="width:18%;text-align:center;vertical-align:bottom;">
            <img src="{{ $stampImage }}" style="max-height:60px;max-width:78px;" alt="">
        </td>
        @endif
        <td style="width:{{ !empty($stampImage) ? '24%' : '42%' }};text-align:right;vertical-align:bottom;">
            <table style="width:160px;margin-left:auto;border-collapse:collapse;">
                <tr>
                    <td style="text-align:center;height:40px;vertical-align:bottom;">
                        @if(!empty($sigImage))
                        <img src="{{ $sigImage }}" style="max-height:38px;max-width:140px;" alt="">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;border-top:1px solid #d1d5db;padding-top:6px;">
                        <strong style="font-size:10.5px;color:#111827;">{{ $sig['name'] ?? '' }}</strong><br>
                        <span style="font-size:8.5px;color:#6b7280;">{{ $sig['title'] ?? 'Authorised Signatory' }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

</div>{{-- /.sheet --}}

</body>
</html>
