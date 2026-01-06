<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #1e293b;
            line-height: 1.4;
            background: #f8fafc;
        }

        /* Print Toolbar - Hidden when printing */
        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.06);
            border-bottom: 1px solid #e2e8f0;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f1f5f9;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .btn-back svg {
            width: 18px;
            height: 18px;
        }

        .toolbar-divider {
            width: 1px;
            height: 32px;
            background: #e2e8f0;
        }

        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toolbar-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toolbar-icon svg {
            width: 20px;
            height: 20px;
            color: #6366f1;
        }

        .toolbar-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .toolbar-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .toolbar-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }

        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .toolbar-btn svg {
            width: 18px;
            height: 18px;
        }

        .btn-download {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        .btn-print {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        }

        /* Add padding to body for toolbar */
        .page-wrapper {
            padding-top: 84px;
            padding-bottom: 30px;
        }

        @media print {
            .print-toolbar {
                display: none !important;
            }

            .page-wrapper {
                padding-top: 0;
                padding-bottom: 0;
            }

            body {
                background: white;
            }
        }

        .page {
            width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #000;
            border-radius: 8px;
            background: white;
        }

        /* Header Styles */
        .invoice-header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 3px solid #000;
        }

        .invoice-type {
            font-weight: 700;
            font-size: 20px;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .invoice-subtitle {
            font-size: 10px;
            color: #64748b;
        }

        /* Company & Invoice Info Section */
        .top-section {
            display: flex;
            gap: 15px;
            border: 2px solid #e2e8f0;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 6px;
            background: linear-gradient(to bottom, #f8fafc, #ffffff);
        }

        .company-section {
            width: 62%;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .logo-container {
            width: auto;
            flex-shrink: 0;
        }

        .logo-container img {
            max-width: 120px;
            max-height: 120px;
            object-fit: contain;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px;
        }

        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .company-details {
            flex: 1;
            font-size: 11px;
        }

        .company-name {
            font-weight: 700;
            font-size: 14px;
            color: #000;
            margin-bottom: 6px;
        }

        .company-info {
            margin-top: 4px;
            color: #475569;
        }

        .company-info strong {
            color: #1e293b;
        }

        .invoice-section {
            width: 38%;
            font-size: 11px;
        }

        .invoice-meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            padding: 4px 6px;
            background: #f8fafc;
            border-radius: 4px;
        }

        .invoice-meta-row strong {
            color: #000;
        }

        .copy-checkbox-section {
            margin-top: 10px;
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 10px;
            border-radius: 4px;
            background: #fefce8;
        }

        .checkbox-item {
            display: flex;
            gap: 6px;
            align-items: center;
            margin-bottom: 4px;
        }

        .checkbox-item:last-child {
            margin-bottom: 0;
        }

        .checkbox {
            width: 16px;
            height: 16px;
            border: 2px solid #475569;
            border-radius: 3px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            background: #fff;
        }

        .checkbox.checked {
            background: #dbeafe;
            border-color: #1e40af;
        }

        .payment-terms-box {
            margin-top: 10px;
            font-size: 10px;
            border: 1px solid #e2e8f0;
            padding: 6px;
            border-radius: 4px;
            background: #f8fafc;
        }

        .payment-terms-box strong {
            color: #000;
            display: block;
            margin-bottom: 4px;
        }

        .amount-box {
            margin-top: 10px;
            font-size: 11px;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            border-radius: 6px;
            background: linear-gradient(135deg, #eef2ff, #ffffff);
        }

        .amount-box-label {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .amount-box-value {
            font-weight: 700;
            font-size: 16px;
            color: #000;
        }

        /* Address Section */
        .addresses-section {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }

        .address-box {
            flex: 1;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .address-header {
            background: #eee;
            color: #000;
            padding: 8px 10px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .address-content {
            padding: 10px;
            font-size: 11px;
            background: #fafafa;
        }

        .address-name {
            font-weight: 700;
            font-size: 12px;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .address-field {
            margin-top: 6px;
            color: #475569;
        }

        .address-field strong {
            color: #1e293b;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            border: 1px solid #000;
            margin-bottom: 0px;
        }

        .items-table thead {
            background: #eee;
            color: #000;
        }

        .items-table thead th {
            padding: 0px 5px;
            border: 1px solid #000000;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .items-table tbody td {
            padding: 0px 5px;
            border: 1px solid #000;
            background: white;
        }

        .items-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        /* Totals Section */
        .totals-section {
            width: 100%;
            float: right;
            border: 1px solid #000000;
            padding: 10px;
            background: linear-gradient(to bottom, #ffffff, #f8fafc);
            border-radius: 0 0 6px 6px;
            margin-bottom: 12px;
            border-top: none;
        }

        .totals-table {
            width: 100%;
            font-size: 11px;
        }

        .totals-table td {
            padding: 2px 0px;
            border-bottom: 1px solid #e2e8f0;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .taxable-row td {
            font-weight: 700;
            font-size: 12px;
            background: #f1f5f9;
            color: #1e293b;
        }

        .total-row td {
            font-weight: 700;
            font-size: 14px;
            border-top: 2px solid #000;
            padding-top: 8px;
            color: #000;
        }

        /* Amount in Words */
        .amount-words {
            clear: both;
            /* margin-top: 12px; */
            border-top: 1px solid #000;
            /* padding-top: 10px; */
            font-size: 11px;
            background: #f8fafc;
            padding: 7px 7px;
            /* border-radius: 6px; */
        }

        .amount-words strong {
            color: #000;
        }

        /* Bank Details */
        .bank-details {
            margin-top: 12px;
            font-size: 11px;
            padding: 10px;
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 6px;
        }

        .bank-details strong {
            color: #854d0e;
        }

        /* Declarations Section */
        .declarations {
            /* margin-top: 12px; */
            font-size: 10px;
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
        }

        .declaration-section {
            margin-bottom: 12px;
        }

        .declaration-title {
            font-weight: 700;
            color: #000;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .declaration-text {
            color: #475569;
            line-height: 1.5;
            text-align: justify;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 12px;
            border-top: 2px solid #e2e8f0;
            padding-top: 12px;
            font-size: 10px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .receiver-acknowledgment {
            flex: 1;
        }

        .receiver-acknowledgment strong {
            color: #1e293b;
            display: block;
            margin-top: 8px;
        }

        .authorized-signature {
            width: 260px;
            text-align: center;
        }

        .signature-image {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            background: white;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-image img {
            max-width: 220px;
            max-height: 80px;
            object-fit: contain;
        }

        .signature-placeholder {
            border: 2px dashed #cbd5e1;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 11px;
        }

        .signature-info {
            margin-top: 8px;
            font-size: 9px;
            color: #64748b;
            line-height: 1.4;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            font-size: 10px;
            margin-top: 12px;
            color: #64748b;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        /* Print Styles */
        @media print {
            .page {
                margin: 0;
                width: auto;
                border: none;
            }
        }
    </style>
</head>

<body>
    <!-- Print Toolbar -->
    <div class="print-toolbar">
        <div class="toolbar-left">
            <a href="{{ route('invoices.index') }}" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Invoices
            </a>
            <div class="toolbar-divider"></div>
            <div class="toolbar-info">
                <div class="toolbar-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="toolbar-text">
                    <span class="toolbar-label">Invoice Preview</span>
                    <span class="toolbar-title">#{{ $invoice->invoice_no }}</span>
                </div>
            </div>
        </div>
        <div class="toolbar-right">
            <button onclick="window.print()" class="toolbar-btn btn-download">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download PDF
            </button>
            <!-- <button onclick="window.print()" class="toolbar-btn btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button> -->
        </div>
    </div>

    <div class="page-wrapper">
        <div class="page">
            @php
                $billed = $invoice->billedTo;
                $shipped = $invoice->shippedTo;
                $placeOfSupply = $invoice->place_of_supply ?? ($invoice->company->state ?? '-');
            @endphp

            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="invoice-type">
                    {{ $invoice->invoice_type == 'proforma' ? 'Proforma Invoice' : 'Tax Invoice' }}
                </div>
                @if($invoice->invoice_type == 'proforma')
                    <div class="invoice-subtitle">Invoice issued under Section 31 of Central Goods and Service Tax Act, 2017
                    </div>
                @endif
            </div>

            <!-- Top Section: Company & Invoice Info -->
            <div class="top-section">
                <div class="company-section">
                    <div class="logo-container">
                        @php
                            $logoUrl = null;
                            $logo = $invoice->company->logo ?? null;
                            try {
                                if (!empty($logo)) {
                                    if (Str::startsWith($logo, ['http://', 'https://'])) {
                                        $logoUrl = $logo;
                                    } elseif (file_exists(public_path('storage/' . $logo))) {
                                        $logoUrl = asset('storage/' . $logo);
                                    } elseif (file_exists(public_path($logo))) {
                                        $logoUrl = asset($logo);
                                    } elseif (file_exists(storage_path('app/public/' . $logo))) {
                                        $logoUrl = asset('storage/' . $logo);
                                    }
                                }
                            } catch (\Exception $e) {
                                $logoUrl = null;
                            }
                        @endphp
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Company Logo">
                        @else
                            <div class="logo-placeholder">LOGO</div>
                        @endif
                    </div>
                    <div class="company-details">
                        <div class="company-name">{{ strtoupper($invoice->company->name ?? '') }}</div>
                        <div class="company-info">{{ $invoice->company->address ?? '' }}</div>
                        <div class="company-info" style="margin-top:6px"><strong>GSTIN:</strong>
                            {{ $invoice->company->gst_no ?? ($invoice->company->tax_id ?? '-') }}</div>
                        <div class="company-info"><strong>State Code:</strong>
                            {{ $invoice->company->state_code ?? '-' }}
                        </div>
                        @if(!empty($invoice->company->phone))
                            <div class="company-info"><strong>Tel:</strong> {{ $invoice->company->phone }}</div>
                        @endif
                    </div>
                </div>

                <div class="invoice-section">
                    <div class="invoice-meta-row">
                        <div><strong>Invoice No.</strong></div>
                        <div>{{ $invoice->invoice_no }}</div>
                    </div>
                    <div class="invoice-meta-row">
                        <div><strong>Date</strong></div>
                        <div>{{ $invoice->invoice_date }}</div>
                    </div>

                    <div class="copy-checkbox-section">
                        <div class="checkbox-item">
                            <div class="checkbox {{ $invoice->copy_type == 'original' ? 'checked' : '' }}">
                                @if($invoice->copy_type == 'original')✓@endif
                            </div>
                            <div>Original - Recipient</div>
                        </div>
                        <div class="checkbox-item">
                            <div class="checkbox {{ $invoice->copy_type == 'duplicate' ? 'checked' : '' }}">
                                @if($invoice->copy_type == 'duplicate')✓@endif
                            </div>
                            <div>Duplicate - Transporter</div>
                        </div>
                    </div>

                    <!-- <div class="payment-terms-box">
                    <strong>Terms of Delivery & Payment</strong>
                    <div>{{ $invoice->payment_terms ?? '-' }}</div>
                </div> -->

                    <!-- <div class="amount-box">
                    <div class="amount-box-label">Amount in Rs.</div>
                    <div class="amount-box-value">{{ number_format($invoice->total_invoice_value ?? 0, 2) }}</div>
                </div> -->
                </div>
            </div>

            <!-- Addresses Section -->
            <div class="addresses-section">
                <div class="address-box">
                    <div class="address-header">DETAILS OF RECEIVER (BILLED TO)</div>
                    <div class="address-content">
                        <div class="address-name">{{ optional($invoice->billedTo)->name ?? '-' }}</div>
                        <div>{{ optional($invoice->billedTo)->address ?? '' }}</div>
                        <div class="address-field"><strong>GSTIN:</strong>
                            {{ optional($invoice->billedTo)->gst_no ?? '-' }}
                            &nbsp;|&nbsp; <strong>PAN:</strong> {{ optional($invoice->billedTo)->pan_no ?? '-' }}</div>
                        <div class="address-field"><strong>State Code:</strong>
                            {{ optional($invoice->billedTo)->state_code ?? '-' }} &nbsp;|&nbsp; <strong>Place of
                                Supply:</strong> {{ $placeOfSupply ?? '-' }}</div>
                        <div class="address-field"><strong>Email:</strong>
                            {{ optional($invoice->billedTo)->email ?? '-' }}
                            &nbsp;|&nbsp; <strong>Tel:</strong> {{ optional($invoice->billedTo)->phone ?? '-' }}</div>
                    </div>
                </div>

                <div class="address-box">
                    <div class="address-header">DETAILS OF CONSIGNEE (SHIPPED TO)</div>
                    <div class="address-content">
                        <div class="address-name">{{ optional($invoice->shippedTo)->name ?? '-' }}</div>
                        <div>{{ optional($invoice->shippedTo)->address ?? '' }}</div>
                        <div class="address-field"><strong>GSTIN:</strong>
                            {{ optional($invoice->shippedTo)->gst_no ?? '-' }} &nbsp;|&nbsp; <strong>PAN:</strong>
                            {{ optional($invoice->shippedTo)->pan_no ?? '-' }}</div>
                        <div class="address-field"><strong>State Code:</strong>
                            {{ optional($invoice->shippedTo)->state_code ?? '-' }} &nbsp;|&nbsp; <strong>Place of
                                Supply:</strong> {{ $placeOfSupply ?? '-' }}</div>
                        <div class="address-field"><strong>Email:</strong>
                            {{ optional($invoice->shippedTo)->email ?? '-' }}
                            &nbsp;|&nbsp; <strong>Tel:</strong> {{ optional($invoice->shippedTo)->phone ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Description of Goods</th>
                        <th style="width:80px">HSN</th>
                        <th style="width:70px">Pieces</th>
                        <th style="width:80px">Carats</th>
                        <th style="width:120px" class="text-right">Rate</th>
                        <th style="width:120px" class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $k => $it)
                        <tr>
                            <td>{{ $k + 1 }}</td>
                            <td>{{ $it->description_of_goods }}</td>
                            <td>{{ $it->hsn_code }}</td>
                            <td>{{ $it->pieces }}</td>
                            <td class="text-right">{{ $it->carats }}</td>
                            <td class="text-right">{{ number_format($it->rate, 2) }}</td>
                            <td class="text-right">{{ number_format($it->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals Section -->
            <div class="totals-section">
                <table class="totals-table">
                    <tr class="taxable-row">
                        <td>Total Taxable Amount Rs</td>
                        <td class="text-right">{{ number_format($invoice->taxable_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>IGST{{ isset($invoice->igst_rate) ? ' ' . number_format($invoice->igst_rate, 2) . "%" : '' }}
                        </td>
                        <td class="text-right">{{ number_format($invoice->igst_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>CGST{{ isset($invoice->cgst_rate) ? ' ' . number_format($invoice->cgst_rate, 2) . "%" : '' }}
                        </td>
                        <td class="text-right">{{ number_format($invoice->cgst_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>SGST{{ isset($invoice->sgst_rate) ? ' ' . number_format($invoice->sgst_rate, 2) . "%" : '' }}
                        </td>
                        <td class="text-right">{{ number_format($invoice->sgst_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="text-right">{{ number_format($invoice->total_invoice_value ?? 0, 2) }}</td>
                    </tr>
                </table>
            </div>

            @php
                if (!function_exists('in_words')) {
                    function _two_digits($n)
                    {
                        $words = [0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'];
                        $tens = [2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty', 6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'];
                        if ($n < 20)
                            return $words[$n];
                        $d = intval($n / 10);
                        $r = $n % 10;
                        return $tens[$d] . ($r ? ' ' . $words[$r] : '');
                    }

                    function _three_digits($n)
                    {
                        $words = [0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine'];
                        $str = '';
                        if ($n >= 100) {
                            $h = intval($n / 100);
                            $str .= $words[$h] . ' Hundred';
                            $r = $n % 100;
                            if ($r)
                                $str .= ' ' . _two_digits($r);
                        } else {
                            $str .= _two_digits($n);
                        }
                        return $str;
                    }

                    function in_words($amount)
                    {
                        $amount = number_format((float) $amount, 2, '.', '');
                        list($rupees, $paise) = explode('.', $amount);
                        $rupees = intval($rupees);
                        $paise = intval($paise);

                        if ($rupees == 0)
                            $rupees_words = 'Zero';

                        $parts = [];
                        $crore = intval($rupees / 10000000);
                        if ($crore) {
                            $parts[] = _three_digits($crore) . ' Crore';
                            $rupees = $rupees % 10000000;
                        }
                        $lakh = intval($rupees / 100000);
                        if ($lakh) {
                            $parts[] = _three_digits($lakh) . ' Lakh';
                            $rupees = $rupees % 100000;
                        }
                        $thousand = intval($rupees / 1000);
                        if ($thousand) {
                            $parts[] = _three_digits($thousand) . ' Thousand';
                            $rupees = $rupees % 1000;
                        }
                        if ($rupees) {
                            $parts[] = _three_digits($rupees);
                        }

                        $rupees_words = implode(' ', $parts);
                        if (trim($rupees_words) == '')
                            $rupees_words = 'Zero';

                        $paise_words = $paise ? _two_digits($paise) : 'Zero';

                        return ucfirst(strtolower(trim($rupees_words))) . ' Rupees And ' . ucfirst(strtolower($paise_words)) . ' Paise Only.';
                    }
                }
            @endphp

            <div style="clear:both"></div>

            <!-- Amount in Words -->
            <div class="amount-words">
                <strong>Total Invoice Value (In Words):</strong> Total Rs.
                {{ number_format($invoice->total_invoice_value ?? 0, 2) }} -
                {{ in_words($invoice->total_invoice_value ?? 0) }}
            </div>

            <!-- Bank Details -->
            <!-- @if(!empty($invoice->company->bank_name) || !empty($invoice->company->ifsc_code))
            <div class="bank-details">
                <strong>Bank Details:</strong>
                {{ $invoice->company->bank_name ?? '' }} — A/C: {{ $invoice->company->account_no ?? '' }} — IFSC:
                {{ $invoice->company->ifsc_code ?? '' }}
            </div>
        @endif -->

            <!-- Declarations Section -->
            <div class="declarations">
                <!-- <div class="declaration-section">
                <div class="declaration-title">Certified Statement</div>
                <div class="declaration-text">
                    Certified that the particulars above are true and correct and the amount indicated represents the
                    price actually charged and that there is no additional favour, consideration directly or indirectly
                    from the buyer or from any person on behalf of the buyer.
                </div>
            </div> -->

                <div class="declaration-section">
                    <div class="declaration-title">Payment Instruction</div>
                    <div class="declaration-text">
                        {{ $invoice->company->bank_name ?? 'STATE BANK OF INDIA (DIAMOND BRANCH - MUMBAI)' }}<br>
                        ACCOUNT NO: {{ $invoice->company->account_no ?? '30257052826' }}<br>
                        RTGS / NEFT (IFSC): {{ $invoice->company->ifsc_code ?? 'SBIN0009276' }}
                    </div>
                </div>

                <!-- <div class="declaration-section">
                <div class="declaration-title">Disclaimer</div>
                <div class="declaration-text">
                    {{ $invoice->company->name ?? 'OM GEMS PRIVATE LIMITED' }} EXPRESSLY DISCLAIMS ANY OBLIGATION OR
                    LIABILITY FOR ANY FRAUDULENT EMAIL OR VERBAL COMMUNICATION FOR ANY PAYMENT INSTRUCTIONS. PLEASE SEND
                    US PAYMENTS AS PER PAYMENT INSTRUCTIONS SPECIFIED ON ORIGINAL HARD COPY OF INVOICE DULY SIGNED BY
                    AUTHORISED PERSON. FOR ANY OTHER EXPENSE, LOSS OR DAMAGE OF WHATSOEVER KIND OF NATURE, WHETHER
                    DIRECT, INCIDENTAL OR CONSEQUENTIAL IN CONNECTION WITH THE PAYMENT IS NOT BINDING ON THE COMPANY.
                </div>
            </div> -->

                <!-- <div class="declaration-section">
                <div class="declaration-title">Declaration</div>
                <div class="declaration-text">
                    The diamonds here in invoiced have been purchased from legitimate sources not involved in funding
                    conflict, in compliance with United Nations Resolutions and corresponding national laws. The seller
                    hereby guarantees that these diamonds are conflict free and confirm adherence to the WDC SOW
                    Guidelines.<br><br>
                    To the best of our knowledge and/or written assurances from our Suppliers, we state that "Diamonds
                    herein invoiced have not been obtained in violation of applicable National laws and/or sanctions by
                    the U.S. Department of Treasury's Office of Foreign Assets Control (OFAC) and have not originated
                    from the Mbada and Marange Resources of Zimbabwe".
                </div>
            </div> -->
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="receiver-acknowledgment">
                    <div>We hereby acknowledge receipt of the goods in good condition.</div>
                    <strong>{{ optional($invoice->billedTo)->name ?? '-' }}</strong>
                </div>

                <div class="authorized-signature">
                    <div class="signature-image">
                        @if(!empty($invoice->signed_image) && (Str::startsWith($invoice->signed_image, ['http://', 'https://']) || file_exists(public_path('storage/' . $invoice->signed_image)) || file_exists(public_path($invoice->signed_image))))
                            @php
                                $sig = $invoice->signed_image;
                                if (!Str::startsWith($sig, ['http://', 'https://']) && file_exists(storage_path('app/public/' . $sig))) {
                                    $sig = asset('storage/' . $sig);
                                } elseif (!Str::startsWith($sig, ['http://', 'https://']) && file_exists(public_path($sig))) {
                                    $sig = asset($sig);
                                }
                            @endphp
                            <img src="{{ $sig }}" alt="Signature">
                        @else
                            <div class="signature-placeholder">Signature</div>
                        @endif
                    </div>
                    <div class="signature-info">
                        Signature valid.<br>
                        Digitally signed by
                        {{ $invoice->signed_by ?? ($invoice->company->authorized_person ?? 'Authorised Signatory') }}<br>
                        Date: {{ $invoice->signed_at ?? now()->format('Y-m-d H:i:s') }}
                    </div>
                </div>
            </div>

            <!-- Footer Note -->
            @if(!empty($invoice->notes))
                <div style="margin-top: 12px; font-size: 10px; padding: 8px; background: #f8fafc; border-radius: 4px;">
                    <strong>Note:</strong> {{ $invoice->notes }}
                </div>
            @endif

            <!-- Page Footer -->
            <div class="page-footer">
                Page 1 | Generated on {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div> <!-- End page-wrapper -->
</body>

</html>