<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document->document_number }}</title>
    <style>
        @php
            $themeColor = $document->theme_color ?? '#7c3aed';
            $opts = $document->advanced_options ?? [];
            $hidePlaceOfSupply = $opts['hide_place_of_supply'] ?? false;
            $showTaxSummary = $opts['show_tax_summary'] ?? true;
            $fullWidthDesc = $opts['show_description_full_width'] ?? true;
        @endphp
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { border-bottom: 3px solid {{ $themeColor }}; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table { width: 100%; }
        .company-name { font-size: 22px; font-weight: bold; color: {{ $themeColor }}; }
        .doc-title { font-size: 18px; font-weight: bold; text-align: right; color: #333; text-transform: uppercase; }
        .doc-number { text-align: right; color: #666; margin-top: 5px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-box { background: #f8fafc; padding: 12px; border-radius: 4px; vertical-align: top; width: 48%; }
        .info-label { font-size: 9px; color: #888; text-transform: uppercase; margin-bottom: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: {{ $themeColor }}; color: white; padding: 8px 6px; text-align: left; font-size: 10px; }
        .items-table td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; }
        .items-table tr:nth-child(even) { background: #f9fafb; }
        .group-row td { background: {{ $themeColor }}33; font-weight: bold; color: #333; padding: 6px 8px; }
        .text-right { text-align: right; }
        .totals-table { width: 280px; margin-left: auto; }
        .totals-table td { padding: 4px 8px; }
        .grand-total { font-size: 14px; font-weight: bold; background: {{ $themeColor }}; color: white; }
        .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 15px; }
        .bank-box { background: #f0fdf4; padding: 12px; border: 1px solid #bbf7d0; margin-top: 15px; }
        .terms { font-size: 9px; color: #666; margin-top: 15px; }
        .gst-badge { display: inline-block; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-size: 9px; }
        .non-gst { background: #e0e7ff; color: #3730a3; }
        .item-img { width: 40px; height: 40px; object-fit: cover; border-radius: 3px; margin-right: 6px; vertical-align: top; }
        .logo-img { max-height: 60px; max-width: 150px; margin-bottom: 8px; }
        .signature-block { margin-top: 30px; text-align: right; }
        .contact-box { background: #eff6ff; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    @if($document->logo_path)
                    <img src="{{ public_path('storage/'.$document->logo_path) }}" class="logo-img" alt="Logo">
                    @endif
                    <div class="company-name">{{ $tenant->name }}</div>
                    <div style="margin-top:5px;color:#666;">{{ $tenant->address }}</div>
                    <div style="color:#666;">{{ $tenant->city }}, {{ $tenant->state }} - {{ $tenant->pincode }}</div>
                    @if($tenant->gstin && $document->is_gst_applicable)
                    <div style="margin-top:5px;"><strong>GSTIN:</strong> {{ $tenant->gstin }}</div>
                    @endif
                    <div style="color:#666;">📞 {{ $tenant->phone }} | ✉ {{ $tenant->email }}</div>
                </td>
                <td style="text-align:right;vertical-align:top;">
                    <div class="doc-title">{{ $document->typeLabel() }}</div>
                    <div class="doc-number"># {{ $document->document_number }}</div>
                    @if($document->title)<div style="margin-top:4px;color:{{ $themeColor }};">{{ $document->title }}</div>@endif
                    <div style="margin-top:8px;">
                        @if($document->is_gst_applicable)
                        <span class="gst-badge">GST Applicable</span>
                        @else
                        <span class="gst-badge non-gst">Non-GST Document</span>
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

    <table class="info-table">
        <tr>
            <td class="info-box">
                <div class="info-label">Bill To / ग्राहक</div>
                <strong>{{ $document->customer_name }}</strong><br>
                @if($document->customer_address){{ $document->customer_address }}<br>@endif
                @if($document->customer_gstin && $document->is_gst_applicable)<strong>GSTIN:</strong> {{ $document->customer_gstin }}@endif
            </td>
            <td style="width:4%;"></td>
            <td class="info-box">
                <div class="info-label">Document Details</div>
                <strong>Issue Date:</strong> {{ $document->issue_date->format('d M Y') }}<br>
                @if($document->due_date)<strong>Due Date:</strong> {{ $document->due_date->format('d M Y') }}<br>@endif
                @if($document->valid_until)<strong>Valid Until:</strong> {{ $document->valid_until->format('d M Y') }}<br>@endif
                @if(!$hidePlaceOfSupply)
                <strong>Place of Supply:</strong> {{ $document->place_of_supply ?? $tenant->state }}
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                @if($document->is_gst_applicable)<th>HSN/SAC</th>@endif
                <th class="text-right">Qty</th>
                <th class="text-right">Rate (₹)</th>
                @if($document->is_gst_applicable && $showTaxSummary)<th class="text-right">Tax</th>@endif
                <th class="text-right">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $prevGroup = ''; $rowNum = 0; @endphp
            @foreach($items as $item)
            @if($item->group_name && $item->group_name !== $prevGroup)
            <tr class="group-row"><td colspan="{{ $document->is_gst_applicable ? ($showTaxSummary ? 7 : 6) : 5 }}">📁 {{ $item->group_name }}</td></tr>
            @php $prevGroup = $item->group_name; @endphp
            @endif
            @php $rowNum++; @endphp
            <tr>
                <td>{{ $rowNum }}</td>
                <td>
                    @if($item->image_path)
                    <img src="{{ public_path('storage/'.$item->image_path) }}" class="item-img" alt="">
                    @endif
                    <strong>{{ $item->description }}</strong>
                    @if($item->long_description)
                    <br><span style="font-size:9px;color:#666;">{!! $fullWidthDesc ? $item->long_description : strip_tags($item->long_description) !!}</span>
                    @endif
                </td>
                @if($document->is_gst_applicable)<td>{{ $item->hsn_sac }}</td>@endif
                <td class="text-right">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                @if($document->is_gst_applicable && $showTaxSummary)
                <td class="text-right">
                    @if($item->igst_amount > 0)
                        IGST {{ $item->igst_rate }}%
                    @else
                        CGST {{ $item->cgst_rate }}% + SGST {{ $item->sgst_rate }}%
                    @endif
                </td>
                @endif
                <td class="text-right">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr><td>Subtotal</td><td class="text-right">₹ {{ number_format($document->subtotal, 2) }}</td></tr>
        @if($document->discount_amount > 0)
        <tr><td>Discount</td><td class="text-right">- ₹ {{ number_format($document->discount_amount, 2) }}</td></tr>
        @endif
        <tr><td>Taxable Amount</td><td class="text-right">₹ {{ number_format($document->taxable_amount, 2) }}</td></tr>
        @if($document->is_gst_applicable && $showTaxSummary)
            @if($document->cgst_amount > 0)
            <tr><td>CGST</td><td class="text-right">₹ {{ number_format($document->cgst_amount, 2) }}</td></tr>
            <tr><td>SGST</td><td class="text-right">₹ {{ number_format($document->sgst_amount, 2) }}</td></tr>
            @endif
            @if($document->igst_amount > 0)
            <tr><td>IGST</td><td class="text-right">₹ {{ number_format($document->igst_amount, 2) }}</td></tr>
            @endif
        @endif
        <tr class="grand-total"><td><strong>Grand Total</strong></td><td class="text-right"><strong>₹ {{ number_format($document->grand_total, 2) }}</strong></td></tr>
        @if($document->exchange_rate && $document->exchange_rate > 0)
        <tr><td>USD Equivalent</td><td class="text-right">$ {{ number_format($document->grand_total / $document->exchange_rate, 2) }}</td></tr>
        @endif
    </table>
    @if($document->total_in_words)
    <div style="margin-top:10px;font-style:italic;color:#666;">Amount in words: {{ $document->total_in_words }}</div>
    @endif

    @if($document->additional_info)
    <div style="margin-top:15px;padding:10px;background:#f8fafc;border-radius:4px;"><strong>Additional Info:</strong> {{ $document->additional_info }}</div>
    @endif

    @if($document->notes)
    <div style="margin-top:20px;"><strong>Notes:</strong> {{ $document->notes }}</div>
    @endif

    @if($document->signature_data)
    <div class="signature-block">
        <div style="border-top:1px solid #ccc;width:200px;margin-left:auto;padding-top:8px;">
            <strong>{{ $document->signature_data['name'] ?? '' }}</strong><br>
            <span style="color:#666;">{{ $document->signature_data['title'] ?? '' }}</span><br>
            <span style="font-size:9px;color:#999;">Authorized Signatory</span>
        </div>
    </div>
    @endif

    <div class="footer">
        <div class="bank-box">
            <strong>Bank Details / भुगतान विवरण</strong><br>
            Bank: {{ $bank['bank_name'] }} | A/C: {{ $bank['account_number'] }} | IFSC: {{ $bank['ifsc'] }}<br>
            UPI: {{ $bank['upi_id'] }}
        </div>
        @if($document->terms_conditions)
        <div class="terms"><strong>Terms & Conditions:</strong><br>{!! nl2br(e($document->terms_conditions)) !!}</div>
        @endif
        <div style="text-align:center;margin-top:20px;color:#999;font-size:9px;">
            This is a computer generated document. | {{ $tenant->name }}
        </div>
    </div>
</body>
</html>
