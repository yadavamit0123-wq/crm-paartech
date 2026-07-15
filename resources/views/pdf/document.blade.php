<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $document->document_number }}</title>
    <style>
        @@page { size: A4 portrait; margin: 20mm; }
        * { margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.45;
        }

        .top-bar {
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .top-bar table { width: 100%; border-collapse: collapse; }
        .top-bar .lbl { font-size: 8px; color: #9ca3af; }
        .top-bar .val { font-size: 10px; font-weight: bold; color: #111827; margin-top: 2px; word-wrap: break-word; }
        .top-bar .disc { font-size: 7.5px; color: #9ca3af; text-align: right; line-height: 1.35; }

        .hero { margin-bottom: 14px; padding-top: 2px; }
        .hero-title { font-size: 32px; font-weight: bold; color: {{ $themeColor }}; letter-spacing: -0.6px; margin-bottom: 4px; }
        .hero-project { font-size: 15px; font-weight: bold; color: {{ $themeColor }}; margin-bottom: 12px; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { font-size: 10px; padding: 2px 0; vertical-align: top; }
        .meta-label { color: #6b7280; width: 120px; }
        .meta-value { color: #111827; font-weight: bold; }

        .parties { width: 100%; border-collapse: collapse; margin: 14px 0 18px; }
        .parties td { width: 48%; vertical-align: top; }
        .parties .gap { width: 4%; }
        .party-title { font-size: 12px; font-weight: bold; color: {{ $themeColor }}; margin-bottom: 8px; }
        .party-name { font-size: 11px; font-weight: bold; color: #111827; margin-bottom: 4px; }
        .party-line { font-size: 9.5px; color: #374151; line-height: 1.55; word-wrap: break-word; }

        .items { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .items thead { display: table-header-group; }
        .items th {
            background-color: {{ $themeColor }}; color: #fff; font-size: 9px; font-weight: bold;
            padding: 9px 8px; text-align: left; border: none;
        }
        .items th.r { text-align: right; }
        .items td {
            padding: 10px 8px; vertical-align: top; border-bottom: 1px solid #f3f4f6;
            font-size: 9.5px; color: #374151; word-wrap: break-word;
        }
        .items td.r { text-align: right; }
        .item-title { font-size: 10.5px; font-weight: bold; color: #111827; line-height: 1.4; margin-bottom: 4px; }
        .item-body { font-size: 9px; color: #4b5563; line-height: 1.5; margin-top: 4px; }
        .item-body ul, .item-body ol { margin: 4px 0 4px 16px; padding: 0; }
        .item-body li { margin: 0 0 3px; }
        .hsn { font-size: 8px; color: #9ca3af; margin-top: 5px; }
        .group { font-size: 10px; font-weight: bold; color: {{ $themeColor }}; padding: 8px 0 4px; border-bottom: 1px dashed #e5e7eb; }

        .w-item { width: 48%; } .w-qty { width: 12%; } .w-rate { width: 13%; } .w-disc { width: 13%; } .w-amt { width: 14%; }

        .totals { width: 100%; margin-top: 16px; border-collapse: collapse; page-break-inside: avoid; }
        .totals .words { width: 52%; vertical-align: top; padding-right: 18px; font-size: 10px; color: #111827; word-wrap: break-word; }
        .totals .nums { width: 48%; vertical-align: top; }
        .nums-table { width: 100%; border-collapse: collapse; }
        .nums-table td { padding: 5px 6px; font-size: 10px; border-bottom: 1px solid #f3f4f6; }
        .nums-table td:first-child { color: #6b7280; text-align: left; }
        .nums-table td:last-child { text-align: right; font-weight: 600; color: #111827; }
        .nums-table .disc td { color: #dc2626; }
        .nums-table .grand td {
            font-size: 13px; font-weight: bold; color: {{ $themeColor }};
            border-top: 2px solid {{ $themeColor }}; border-bottom: none; padding-top: 9px;
        }

        .notes { margin-top: 16px; font-size: 9.5px; color: #374151; line-height: 1.6; word-wrap: break-word; }
        .pay-box { margin-top: 14px; border: 1px solid #e5e7eb; padding: 10px 12px; page-break-inside: avoid; }
        .pay-box .ttl { font-size: 10px; font-weight: bold; color: {{ $themeColor }}; margin-bottom: 6px; }
        .pay-box .line { font-size: 9px; color: #374151; margin-bottom: 3px; word-wrap: break-word; }
        .bank {
            margin-top: 12px; padding: 9px 11px; background: #f9fafb;
            border-left: 3px solid {{ $themeColor }}; font-size: 9px; color: #374151;
            page-break-inside: avoid; word-wrap: break-word;
        }
        .sig { margin-top: 22px; width: 100%; border-collapse: collapse; page-break-inside: avoid; }
        .foot {
            margin-top: 18px; padding-top: 8px; border-top: 1px solid #e5e7eb;
            font-size: 8px; color: #9ca3af; text-align: center; page-break-inside: avoid;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <table>
        <tr>
            <td style="width:22%;vertical-align:top;">
                <div class="lbl">{{ $typeLabel }} No</div>
                <div class="val">{{ $document->document_number }}</div>
            </td>
            <td style="width:22%;vertical-align:top;">
                <div class="lbl">{{ $typeLabel }} Date</div>
                <div class="val">{{ optional($document->issue_date)->format('d M Y') }}</div>
            </td>
            <td style="width:28%;vertical-align:top;">
                <div class="lbl">{{ $typeLabel }} For</div>
                <div class="val">{{ $document->customer_name }}</div>
            </td>
            <td style="width:28%;vertical-align:top;" class="disc">
                <span style="font-size:8px;color:#6b7280;">&nbsp;</span><br>
                This is an electronically generated document,<br>no signature is required.
            </td>
        </tr>
    </table>
</div>

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
            <td class="meta-value">{{ optional($document->issue_date)->format('M d, Y') }}</td>
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

<table class="parties">
    <tr>
        <td>
            <div class="party-title">{{ $typeLabel }} From</div>
            @if(!empty($logoFile))
            <img src="{{ $logoFile }}" style="max-height:42px;max-width:130px;margin-bottom:6px;" alt="">
            @endif
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
        <td class="gap"></td>
        <td>
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
            <th class="w-qty r">Quantity</th>
            <th class="w-rate r">Rate</th>
            <th class="w-disc r">Discount</th>
            <th class="w-amt r">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            @if($row['type'] === 'group')
            <tr><td colspan="5"><div class="group">{{ $row['label'] }}</div></td></tr>
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
                <td class="r">
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
                        <strong style="font-size:10px;color:#111827;">{{ $sig['name'] ?? '' }}</strong><br>
                        <span style="font-size:8.5px;color:#6b7280;">{{ $sig['title'] ?? 'Authorised Signatory' }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

<div class="foot">
    This is a computer generated {{ strtolower($typeLabel) }} and does not require a physical signature or stamp.
</div>

</body>
</html>
