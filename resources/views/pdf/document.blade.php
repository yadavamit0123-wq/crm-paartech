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
            $colCount = $isGst ? ($showTaxSummary ? 7 : 6) : 5;
            $currency = $document->currency === 'USD' ? '$' : '₹';
        @endphp

        @page {
            size: A4 portrait;
            margin: 12mm 14mm 16mm 14mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.45;
        }

        table { border-collapse: collapse; }
        .w-full { width: 100%; }

        /* Header */
        .doc-header {
            border-bottom: 2.5px solid {{ $themeColor }};
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: {{ $themeColor }};
            margin-bottom: 4px;
        }
        .company-meta { font-size: 9px; color: #4b5563; line-height: 1.5; }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            text-align: right;
        }
        .doc-number { font-size: 11px; color: #6b7280; text-align: right; margin-top: 4px; }
        .doc-subtitle { font-size: 10px; color: {{ $themeColor }}; text-align: right; margin-top: 3px; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            margin-top: 6px;
        }
        .badge-gst { background: #fef3c7; color: #92400e; }
        .badge-nogst { background: #e0e7ff; color: #3730a3; }
        .logo-img { max-height: 52px; max-width: 130px; margin-bottom: 6px; }

        /* Info boxes */
        .info-table { width: 100%; margin-bottom: 14px; }
        .info-box {
            width: 48%;
            vertical-align: top;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
        }
        .info-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.4px;
            margin-bottom: 5px;
        }
        .info-box strong { font-size: 10px; color: #111827; }
        .info-gap { width: 4%; }

        .contact-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 9px;
        }

        /* Items table — header repeats on each page */
        .items-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 12px;
            border: 1px solid #d1d5db;
        }
        .items-table thead { display: table-header-group; }
        .items-table tfoot { display: table-footer-group; }
        .items-table th {
            background: {{ $themeColor }};
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            padding: 7px 5px;
            text-align: left;
            border: 1px solid {{ $themeColor }};
        }
        .items-table th.right,
        .items-table td.right { text-align: right; }
        .items-table th.center,
        .items-table td.center { text-align: center; }
        .items-table td {
            padding: 7px 5px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 9px;
            word-wrap: break-word;
            word-break: break-word;
        }
        .items-table tbody tr:nth-child(even) td { background: #f9fafb; }
        .items-table tbody tr { page-break-inside: avoid; }
        .group-row td {
            background: {{ $themeColor }}22 !important;
            font-weight: bold;
            font-size: 9px;
            color: #374151;
            padding: 5px 8px;
            border: 1px solid #d1d5db;
        }
        .item-title { font-weight: bold; font-size: 9.5px; color: #111827; }
        .item-desc { font-size: 8px; color: #6b7280; margin-top: 3px; line-height: 1.35; }
        .item-img {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            margin-right: 6px;
            float: left;
        }

        .col-num { width: 4%; }
        .col-desc { width: {{ $isGst ? '32%' : '40%' }}; }
        .col-hsn { width: 9%; }
        .col-qty { width: 10%; }
        .col-rate { width: 11%; }
        .col-tax { width: 14%; }
        .col-amt { width: 12%; }

        /* Totals — keep together */
        .totals-section { page-break-inside: avoid; margin-top: 4px; }
        .totals-table {
            width: 260px;
            margin-left: auto;
            border: 1px solid #d1d5db;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 9px;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-table tr:last-child td { border-bottom: none; }
        .totals-table .grand-total td {
            background: {{ $themeColor }};
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 8px;
        }
        .amount-words {
            margin-top: 8px;
            font-size: 9px;
            font-style: italic;
            color: #6b7280;
            text-align: right;
            page-break-inside: avoid;
        }

        /* Footer blocks */
        .footer-section { page-break-inside: avoid; margin-top: 16px; }
        .bank-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 10px 12px;
            font-size: 9px;
            margin-bottom: 10px;
        }
        .terms-box {
            font-size: 8.5px;
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        .signature-block {
            page-break-inside: avoid;
            margin-top: 20px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #9ca3af;
            width: 180px;
            margin-left: auto;
            padding-top: 6px;
            font-size: 9px;
        }
        .pdf-footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
        }
        .page-num:after {
            content: counter(page);
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="doc-header">
        <table class="w-full">
            <tr>
                <td style="width:58%; vertical-align:top;">
                    @if($document->logo_path && file_exists(public_path('storage/'.$document->logo_path)))
                    <img src="{{ public_path('storage/'.$document->logo_path) }}" class="logo-img" alt="">
                    @endif
                    <div class="company-name">{{ $tenant->name }}</div>
                    <div class="company-meta">
                        @if($tenant->address){{ $tenant->address }}<br>@endif
                        @if($tenant->city || $tenant->state || $tenant->pincode)
                        {{ trim(implode(', ', array_filter([$tenant->city, $tenant->state]))) }}@if($tenant->pincode) - {{ $tenant->pincode }}@endif<br>
                        @endif
                        @if($tenant->gstin && $isGst)<strong>GSTIN:</strong> {{ $tenant->gstin }}<br>@endif
                        @if($tenant->phone)Phone: {{ $tenant->phone }}@endif
                        @if($tenant->email) | Email: {{ $tenant->email }}@endif
                    </div>
                </td>
                <td style="width:42%; vertical-align:top;">
                    <div class="doc-title">{{ $document->typeLabel() }}</div>
                    <div class="doc-number">No. {{ $document->document_number }}</div>
                    @if($document->title)<div class="doc-subtitle">{{ $document->title }}</div>@endif
                    <div style="text-align:right;">
                        @if($isGst)
                        <span class="badge badge-gst">WITH GST</span>
                        @else
                        <span class="badge badge-nogst">WITHOUT GST</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if($document->contact_details)
    <div class="contact-box">
        <strong>Contact:</strong>
        {{ $document->contact_details['person'] ?? '' }}
        @if($document->contact_details['phone'] ?? null) | {{ $document->contact_details['phone'] }} @endif
        @if($document->contact_details['email'] ?? null) | {{ $document->contact_details['email'] }} @endif
    </div>
    @endif

    {{-- BILL TO / DETAILS --}}
    <table class="info-table">
        <tr>
            <td class="info-box">
                <div class="info-label">Bill To</div>
                <strong>{{ $document->customer_name }}</strong><br>
                @if($document->customer_address)<span style="font-size:9px;">{{ $document->customer_address }}</span><br>@endif
                @if($document->customer_phone)<span style="font-size:9px;">Phone: {{ $document->customer_phone }}</span><br>@endif
                @if($document->customer_email)<span style="font-size:9px;">Email: {{ $document->customer_email }}</span><br>@endif
                @if($document->customer_gstin && $isGst)<span style="font-size:9px;"><strong>GSTIN:</strong> {{ $document->customer_gstin }}</span>@endif
            </td>
            <td class="info-gap"></td>
            <td class="info-box">
                <div class="info-label">Document Details</div>
                <table style="width:100%; font-size:9px;">
                    <tr><td style="width:40%; padding:2px 0;"><strong>Issue Date</strong></td><td>{{ $document->issue_date->format('d M Y') }}</td></tr>
                    @if($document->due_date)
                    <tr><td style="padding:2px 0;"><strong>Due Date</strong></td><td>{{ $document->due_date->format('d M Y') }}</td></tr>
                    @endif
                    @if($document->valid_until)
                    <tr><td style="padding:2px 0;"><strong>Valid Until</strong></td><td>{{ $document->valid_until->format('d M Y') }}</td></tr>
                    @endif
                    @if(!$hidePlaceOfSupply)
                    <tr><td style="padding:2px 0;"><strong>Place of Supply</strong></td><td>{{ $document->place_of_supply ?? $tenant->state }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($document->shipping_details && !empty($document->shipping_details['address']))
    <div style="margin-bottom:12px; padding:8px 10px; background:#fffbeb; border:1px solid #fde68a; font-size:9px;">
        <strong>Ship To:</strong> {{ $document->shipping_details['address'] }}
    </div>
    @endif

    {{-- LINE ITEMS (auto page break + header repeat) --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-num center">#</th>
                <th class="col-desc">Description</th>
                @if($isGst)<th class="col-hsn center">HSN/SAC</th>@endif
                <th class="col-qty right">Qty</th>
                <th class="col-rate right">Rate</th>
                @if($isGst && $showTaxSummary)<th class="col-tax right">Tax</th>@endif
                <th class="col-amt right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $prevGroup = ''; $rowNum = 0; @endphp
            @foreach($items as $item)
            @if($item->group_name && $item->group_name !== $prevGroup)
            <tr class="group-row">
                <td colspan="{{ $colCount }}">{{ $item->group_name }}</td>
            </tr>
            @php $prevGroup = $item->group_name; @endphp
            @endif
            @php $rowNum++; @endphp
            <tr>
                <td class="center">{{ $rowNum }}</td>
                <td>
                    @if($item->image_path && file_exists(public_path('storage/'.$item->image_path)))
                    <img src="{{ public_path('storage/'.$item->image_path) }}" class="item-img" alt="">
                    @endif
                    <div class="item-title">{{ $item->description }}</div>
                    @if($item->long_description)
                    <div class="item-desc">{!! strip_tags($item->long_description, '<br><b><strong><i><em><ul><ol><li><p>') !!}</div>
                    @endif
                </td>
                @if($isGst)<td class="center" style="font-size:8px;">{{ $item->hsn_sac }}</td>@endif
                <td class="right">{{ number_format($item->quantity, 2) }}<br><span style="font-size:7px;color:#9ca3af;">{{ $item->unit }}</span></td>
                <td class="right">{{ $currency }}{{ number_format($item->rate, 2) }}</td>
                @if($isGst && $showTaxSummary)
                <td class="right" style="font-size:8px;">
                    @if($item->igst_amount > 0)
                        IGST {{ number_format($item->igst_rate, 1) }}%<br>{{ $currency }}{{ number_format($item->igst_amount, 2) }}
                    @else
                        CGST {{ number_format($item->cgst_rate, 1) }}%<br>SGST {{ number_format($item->sgst_rate, 1) }}%
                    @endif
                </td>
                @endif
                <td class="right"><strong>{{ $currency }}{{ number_format($item->line_total, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTALS --}}
    <div class="totals-section">
        <table class="totals-table">
            <tr><td>Subtotal</td><td class="right">{{ $currency }} {{ number_format($document->subtotal, 2) }}</td></tr>
            @if($document->discount_amount > 0)
            <tr><td>Discount</td><td class="right">- {{ $currency }} {{ number_format($document->discount_amount, 2) }}</td></tr>
            @endif
            <tr><td>Taxable Amount</td><td class="right">{{ $currency }} {{ number_format($document->taxable_amount, 2) }}</td></tr>
            @if($isGst && $showTaxSummary)
                @if($document->cgst_amount > 0)
                <tr><td>CGST</td><td class="right">{{ $currency }} {{ number_format($document->cgst_amount, 2) }}</td></tr>
                <tr><td>SGST</td><td class="right">{{ $currency }} {{ number_format($document->sgst_amount, 2) }}</td></tr>
                @endif
                @if($document->igst_amount > 0)
                <tr><td>IGST</td><td class="right">{{ $currency }} {{ number_format($document->igst_amount, 2) }}</td></tr>
                @endif
            @endif
            <tr class="grand-total">
                <td>Grand Total</td>
                <td class="right">{{ $currency }} {{ number_format($document->grand_total, 2) }}</td>
            </tr>
            @if($document->exchange_rate && $document->exchange_rate > 0)
            <tr><td>USD Equivalent</td><td class="right">$ {{ number_format($document->grand_total / $document->exchange_rate, 2) }}</td></tr>
            @endif
        </table>
        @if($document->total_in_words)
        <div class="amount-words">Amount in words: {{ $document->total_in_words }}</div>
        @endif
    </div>

    @if($document->additional_info)
    <div style="margin-top:12px; padding:8px 10px; background:#f9fafb; border:1px solid #e5e7eb; font-size:9px; page-break-inside:avoid;">
        <strong>Additional Info:</strong> {{ $document->additional_info }}
    </div>
    @endif

    @if($document->notes)
    <div style="margin-top:10px; font-size:9px; page-break-inside:avoid;">
        <strong>Notes:</strong> {{ $document->notes }}
    </div>
    @endif

    @if($document->signature_data)
    <div class="signature-block">
        <div class="signature-line">
            <strong>{{ $document->signature_data['name'] ?? '' }}</strong><br>
            {{ $document->signature_data['title'] ?? '' }}<br>
            <span style="font-size:8px;color:#9ca3af;">Authorized Signatory</span>
        </div>
    </div>
    @endif

    <div class="footer-section">
        <div class="bank-box">
            <strong>Bank Details</strong><br>
            Bank: {{ $bank['bank_name'] }} &nbsp;|&nbsp; A/C: {{ $bank['account_number'] }} &nbsp;|&nbsp; IFSC: {{ $bank['ifsc'] }}<br>
            UPI: {{ $bank['upi_id'] }}
        </div>
        @if($document->terms_conditions)
        <div class="terms-box">
            <strong>Terms &amp; Conditions</strong><br>
            {!! nl2br(e($document->terms_conditions)) !!}
        </div>
        @endif
        <div class="pdf-footer">
            This is a computer-generated document. | {{ $tenant->name }} | {{ $document->document_number }}
        </div>
    </div>

</body>
</html>
