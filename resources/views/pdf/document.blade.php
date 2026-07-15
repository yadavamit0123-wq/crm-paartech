<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $document->document_number }}</title>
    <style>
        /*
         * Sample Quotation layout (A4).
         * Footer is stamped via PdfService canvas (DomPDF CSS fixed unreliable).
         */
        @@page {
            size: A4 portrait;
            /* Extra bottom room for canvas-stamped sample footer */
            margin: 14mm 14mm 30mm 14mm;
        }

        * { margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
        }

        /* Content outliner — sample jaisa thin grey border */
        .sheet {
            border: 1px solid #9ca3af;
            padding: 14px 14px 12px;
        }

        /* ========== HEADER ========== */
        .hdr {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .hdr td { vertical-align: top; }
        .doc-title {
            font-size: 32px;
            font-weight: bold;
            color: {{ $themeColor }};
            letter-spacing: -0.6px;
            line-height: 1.05;
        }
        .doc-project {
            font-size: 13px;
            color: #6b7280;
            margin: 4px 0 10px;
            font-weight: normal;
        }
        .meta-lines { width: auto; border-collapse: collapse; }
        .meta-lines td {
            padding: 1px 0;
            font-size: 10px;
            vertical-align: top;
        }
        .meta-lines .k { color: #4b5563; padding-right: 10px; white-space: nowrap; }
        .meta-lines .v { color: #111827; font-weight: bold; }

        /* Company logo — clear square (not tiny) */
        .logo-box {
            width: 110px;
            height: 110px;
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
            background: #ffffff;
        }
        .logo-box img {
            max-height: 94px;
            max-width: 94px;
            width: auto;
            height: auto;
        }
        .logo-fallback {
            font-size: 12px;
            font-weight: bold;
            color: {{ $themeColor }};
            line-height: 1.25;
            padding-top: 28px;
        }

        /* ========== FROM / FOR (peach boxes) ========== */
        .parties {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 12px;
        }
        .parties td.box {
            width: 49%;
            vertical-align: top;
            background-color: #FFF4EC;
            border: 1px solid #F3E0D0;
            padding: 10px 12px;
        }
        .parties td.gap { width: 2%; }
        .party-h {
            font-size: 12px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 6px;
        }
        .party-name {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
            line-height: 1.35;
        }
        .party-body {
            font-size: 10px;
            color: #374151;
            line-height: 1.5;
            word-wrap: break-word;
        }

        /* ========== ITEMS TABLE ========== */
        .items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .items thead { display: table-header-group; }
        .items th {
            background-color: {{ $themeColor }};
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            padding: 8px 8px;
            text-align: left;
            border: none;
        }
        .items th.r { text-align: right; }
        .items th.c { text-align: center; }
        .items td {
            background-color: #FFF9F5;
            padding: 8px 8px;
            vertical-align: top;
            border-bottom: 1px solid #F3E0D0;
            font-size: 10px;
            color: #374151;
            word-wrap: break-word;
        }
        .items td.r { text-align: right; }
        .items td.c { text-align: center; }
        .item-title {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
            line-height: 1.35;
        }
        .item-body {
            font-size: 9px;
            color: #4b5563;
            line-height: 1.45;
            margin-top: 5px;
        }
        .item-body ul, .item-body ol {
            margin: 3px 0 3px 14px;
            padding: 0;
        }
        .item-body li { margin: 0 0 2px; }
        .hsn {
            font-size: 8px;
            color: #9ca3af;
            margin-top: 4px;
        }
        .group td {
            background-color: {{ $themeColor }};
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            padding: 7px 8px;
            border-bottom: none;
        }

        .col-item { width: 48%; }
        .col-qty { width: 12%; }
        .col-rate { width: 13%; }
        .col-disc { width: 13%; }
        .col-amt { width: 14%; }

        /* ========== TOTALS ========== */
        .totals {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            page-break-inside: avoid;
        }
        .totals .words {
            width: 52%;
            vertical-align: top;
            padding-right: 14px;
            font-size: 10px;
            color: #111827;
            word-wrap: break-word;
        }
        .totals .nums { width: 48%; vertical-align: top; }
        .nums-table { width: 100%; border-collapse: collapse; }
        .nums-table td {
            padding: 4px 4px;
            font-size: 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        .nums-table td:first-child { color: #6b7280; text-align: left; }
        .nums-table td:last-child { text-align: right; font-weight: 600; color: #111827; }
        .nums-table .disc td { color: #dc2626; }
        .nums-table .grand td {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            border-top: 1.5px solid #111827;
            border-bottom: 1.5px solid #111827;
            padding: 7px 4px;
        }

        .block {
            margin-top: 10px;
            background: #FFF9F5;
            border: 1px solid #F3E0D0;
            padding: 8px 10px;
            font-size: 9.5px;
            color: #374151;
            line-height: 1.5;
            word-wrap: break-word;
            page-break-inside: avoid;
        }
        .block .ttl {
            font-size: 10.5px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 4px;
        }
        .bank {
            margin-top: 8px;
            padding: 7px 10px;
            background: #FFF9F5;
            border-left: 3px solid {{ $themeColor }};
            font-size: 9.5px;
            color: #374151;
            word-wrap: break-word;
            page-break-inside: avoid;
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

<div class="sheet">

{{-- ========== HEADER: title+meta LEFT | logo box RIGHT ========== --}}
<table class="hdr">
    <tr>
        <td style="width:70%;">
            <div class="doc-title">{{ $typeLabel }}</div>
            @if($document->title)
            <div class="doc-project">{{ $document->title }}</div>
            @else
            <div style="height:8px;"></div>
            @endif
            <table class="meta-lines">
                <tr>
                    <td class="k">{{ $typeLabel }} No #</td>
                    <td class="v">{{ $document->document_number }}</td>
                </tr>
                <tr>
                    <td class="k">{{ $typeLabel }} Date</td>
                    <td class="v">{{ optional($document->issue_date)->format('M d, Y') }}</td>
                </tr>
                @if($document->due_date)
                <tr>
                    <td class="k">Due Date</td>
                    <td class="v">{{ $document->due_date->format('M d, Y') }}</td>
                </tr>
                @endif
                @if($document->valid_until)
                <tr>
                    <td class="k">Valid Until</td>
                    <td class="v">{{ $document->valid_until->format('M d, Y') }}</td>
                </tr>
                @endif
            </table>
        </td>
        <td style="width:32%;text-align:right;">
            <table style="width:110px;margin-left:auto;border-collapse:collapse;">
                <tr>
                    <td class="logo-box">
                        @if(!empty($logoFile))
                        <img src="{{ $logoFile }}" alt="">
                        @else
                        <div class="logo-fallback">{{ $tenant->name ?? '' }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ========== FROM / FOR peach boxes ========== --}}
<table class="parties">
    <tr>
        <td class="box">
            <div class="party-h">{{ $typeLabel }} From</div>
            <div class="party-name">{{ $tenant->name ?? '' }}</div>
            <div class="party-body">
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
        <td class="gap"></td>
        <td class="box">
            <div class="party-h">{{ $typeLabel }} For</div>
            <div class="party-name">{{ $document->customer_name }}</div>
            <div class="party-body">
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

{{-- ========== ITEMS (header repeats on every page) ========== --}}
<table class="items">
    <thead>
        <tr>
            <th class="col-item">Item</th>
            <th class="col-qty c">Quantity</th>
            <th class="col-rate r">Rate</th>
            <th class="col-disc r">Discount</th>
            <th class="col-amt r">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            @if($row['type'] === 'group')
            <tr class="group"><td colspan="5">{{ $row['label'] }}</td></tr>
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

{{-- ========== TOTALS ========== --}}
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
<div class="block">{!! nl2br(e($document->terms_conditions)) !!}</div>
@endif

@if($document->additional_info)
<div class="block"><strong>Additional Info:</strong> {{ $document->additional_info }}</div>
@endif

@if(!empty($pay['link']) || !empty($pay['upi']) || !empty($qrFile))
<div class="block">
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:{{ !empty($qrFile) ? '78%' : '100%' }};vertical-align:top;">
                <div class="ttl">Payment Options</div>
                @if(!empty($pay['link']))
                <div><strong>Pay Online:</strong> {{ $pay['link'] }}</div>
                @endif
                @if(!empty($pay['upi']))
                <div><strong>UPI ID:</strong> {{ $pay['upi'] }}</div>
                @endif
                @if(!empty($qrFile))
                <div style="color:#6b7280;">Scan the QR code to pay via any UPI app.</div>
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

@if(!empty($showBankDetails))
<div class="bank">
    <strong>Bank Details:</strong>
    {{ $bank['bank_name'] }} | A/C: {{ $bank['account_number'] }} | IFSC: {{ $bank['ifsc'] }} | UPI: {{ $bank['upi_id'] }}
</div>
@endif

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
