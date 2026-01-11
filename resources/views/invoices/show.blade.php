@extends('layouts.admin')
@section('title', 'Invoice #' . $invoice->invoice_no)
@section('content')
    <div class="invoice-wrapper">
        <!-- Top Navigation -->
        <nav class="invoice-navbar no-print">
            <div class="nav-container">
                <a href="{{ route('invoices.index') }}" class="btn-back">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    <span>Back to Invoices</span>
                </a>
                <div class="nav-actions">
                    <button class="btn btn-outline" onclick="window.location='{{ route('invoices.edit', $invoice->id) }}'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit Invoice
                    </button>
                    <button class="btn btn-primary" onclick="window.location='{{ route('invoices.pdf', $invoice->id) }}'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10 9 9 9 8 9" />
                        </svg>
                        Export PDF
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Invoice Content -->
        <div class="invoice-content">
            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="header-left">
                    <div class="invoice-number">{{ $invoice->invoice_no }}</div>
                    <div class="invoice-meta">
                        <span class="badge badge-{{ $invoice->invoice_type }}">
                            {{ ucfirst($invoice->invoice_type) }}
                        </span>
                        <span class="badge badge-{{ strtolower($invoice->status) }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="invoice-date">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Party Information -->
            <div class="party-section">
                <div class="party-card company-card">
                    <div class="card-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                        <span>Company Details</span>
                    </div>
                    <div class="card-body">
                        <h3>{{ $invoice->company->name ?? '-' }}</h3>
                        <p class="address">{{ $invoice->company->address ?? '-' }}</p>
                        <div class="gst-info">
                            <span class="label">GST No.</span>
                            <span class="value">{{ $invoice->company->gst_no ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="party-card">
                    <div class="card-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span>Bill To</span>
                    </div>
                    <div class="card-body">
                        <h3>{{ $invoice->billedTo->name ?? '-' }}</h3>
                        <p class="address">{{ $invoice->billedTo->address ?? '-' }}</p>
                    </div>
                </div>

                <div class="party-card">
                    <div class="card-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z" />
                            <path d="M2 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z" />
                            <path d="M7 21h10" />
                            <path d="M12 3v18" />
                            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2" />
                        </svg>
                        <span>Ship To</span>
                    </div>
                    <div class="card-body">
                        <h3>{{ $invoice->shippedTo->name ?? '-' }}</h3>
                        <p class="address">{{ $invoice->shippedTo->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="items-section">
                <div class="section-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="8" y1="6" x2="21" y2="6" />
                        <line x1="8" y1="12" x2="21" y2="12" />
                        <line x1="8" y1="18" x2="21" y2="18" />
                        <line x1="3" y1="6" x2="3.01" y2="6" />
                        <line x1="3" y1="12" x2="3.01" y2="12" />
                        <line x1="3" y1="18" x2="3.01" y2="18" />
                    </svg>
                    <span>Invoice Items</span>
                </div>
                <div class="table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Description of Goods</th>
                                <th style="width: 130px;">HSN Code</th>
                                <th style="width: 110px;" class="text-center">Pieces</th>
                                <th style="width: 110px;" class="text-center">Carats</th>
                                <th style="width: 140px;" class="text-right">Rate</th>
                                <th style="width: 160px;" class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $k => $it)
                                <tr>
                                    <td class="item-number">{{ $k + 1 }}</td>
                                    <td class="item-desc">{{ $it->description_of_goods }}</td>
                                    <td class="hsn-code">{{ $it->hsn_code }}</td>
                                    <td class="text-center">{{ $it->pieces }}</td>
                                    <td class="text-center">{{ $it->carats }}</td>
                                    <td class="text-right">
                                        {{ $invoice->company->currency_symbol ?? '₹' }}{{ number_format($it->rate, 2) }}</td>
                                    <td class="text-right item-amount">
                                        {{ $invoice->company->currency_symbol ?? '₹' }}{{ number_format($it->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>                    
                    </table>
                </div>
            </div>

            <!-- Footer Summary -->
            <div class="footer-section">
                <div class="details-card">
                    <div class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                        <span>Additional Details</span>
                    </div>
                    <div class="details-list">
                        <div class="detail-row">
                            <span class="detail-label">Payment Terms</span>
                            <span class="detail-value">{{ $invoice->payment_terms ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Place of Supply</span>
                            <span class="detail-value">{{ $invoice->place_of_supply ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Invoice Type</span>
                            <span class="detail-value">{{ ucfirst($invoice->invoice_type) }}</span>
                        </div>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                        <span>Financial Summary</span>
                    </div>
                    <div class="summary-rows">
                        <div class="summary-row">
                            <span>Taxable Amount</span>
                            <span>{{ $invoice->company->currency_symbol ?? '₹' }}{{ number_format($invoice->taxable_amount, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>IGST</span>
                            <span>{{ $invoice->company->currency_symbol ?? '₹' }}{{ number_format($invoice->igst_amount, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>CGST</span>
                            <span>{{ $invoice->company->currency_symbol ?? '₹' }}{{ number_format($invoice->cgst_amount, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>SGST</span>
                            <span>{{ $invoice->company->currency_symbol ?? '₹' }}{{ number_format($invoice->sgst_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="summary-total">
                        <span>Total Invoice Value</span>
                        <span>{{ $invoice->company->currency_symbol ?? '₹' }}{{ number_format($invoice->total_invoice_value, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #3b82f6;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 10px;
            --radius-lg: 16px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        .invoice-wrapper {
            min-height: 100vh;
            padding: 2rem;
        }

        /* Navigation */
        .invoice-navbar {
            margin-bottom: 2rem;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.625rem 1rem;
            border-radius: var(--radius);
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            color: var(--primary);
            background: var(--gray-100);
        }

        .nav-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-outline {
            background: var(--white);
            color: var(--gray-700);
            border: 1.5px solid var(--gray-300);
            box-shadow: var(--shadow-sm);
        }

        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            box-shadow: var(--shadow);
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        /* Main Content */
        .invoice-content {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* Invoice Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 2.5rem 3rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--gray-200);
        }

        .invoice-number {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }

        .invoice-meta {
            display: flex;
            gap: 0.625rem;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-proforma {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-tax {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-export {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-draft {
            background: #f3f4f6;
            color: #4b5563;
        }

        .invoice-date {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            color: var(--gray-600);
            font-weight: 500;
            font-size: 1rem;
            padding: 0.75rem 1.25rem;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        /* Party Section */
        .party-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            padding: 2.5rem 3rem;
            background: var(--white);
        }

        .party-card {
            background: var(--gray-50);
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .party-card:hover {
            border-color: var(--gray-300);
            box-shadow: var(--shadow);
        }

        .company-card {
            border-left: 4px solid var(--primary);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 1rem 1.25rem;
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--gray-600);
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 1.5rem 1.25rem;
        }

        .card-body h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.625rem;
        }

        .address {
            font-size: 0.9rem;
            color: var(--gray-600);
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .gst-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: var(--white);
            border-radius: 6px;
            border: 1px solid var(--gray-200);
        }

        .gst-info .label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .gst-info .value {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        /* Items Section */
        .items-section {
            padding: 0 3rem 2.5rem 3rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .table-container {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }

        .items-table thead {
            background: linear-gradient(to bottom, var(--gray-50), var(--gray-100));
        }

        .items-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--gray-700);
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-300);
        }

        .items-table td {
            padding: 1.125rem 1.25rem;
            font-size: 0.95rem;
            color: var(--gray-700);
            border-bottom: 1px solid var(--gray-200);
        }

        .items-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .items-table tbody tr:hover {
            background: var(--gray-50);
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .item-number {
            color: var(--gray-500);
            font-weight: 600;
        }

        .item-desc {
            font-weight: 500;
            color: var(--gray-900);
        }

        .hsn-code {
            font-family: 'Courier New', monospace;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .item-amount {
            font-weight: 700;
            color: var(--gray-900);
        }

        /* Footer Section */
        .footer-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            padding: 2.5rem 3rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
        }

        .details-card,
        .summary-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 1.25rem 1.5rem;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .details-list,
        .summary-rows {
            padding: 1.5rem;
        }

        .detail-row,
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.875rem 0;
            border-bottom: 1px dashed var(--gray-200);
        }

        .detail-row:last-child,
        .summary-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-label,
        .summary-row span:first-child {
            font-size: 0.9rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .detail-value,
        .summary-row span:last-child {
            font-size: 0.95rem;
            color: var(--gray-900);
            font-weight: 600;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            font-weight: 700;
            font-size: 1.125rem;
        }

        /* Utilities */
        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .invoice-wrapper {
                padding: 1.5rem;
            }

            .invoice-header,
            .party-section,
            .items-section,
            .footer-section {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }

        @media (max-width: 768px) {
            .invoice-wrapper {
                padding: 1rem;
            }

            .nav-container {
                flex-direction: column;
                align-items: stretch;
            }

            .nav-actions {
                flex-direction: column;
            }

            .invoice-header {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1.5rem;
            }

            .party-section {
                grid-template-columns: 1fr;
                padding: 1.5rem;
            }

            .items-section {
                padding: 1.5rem;
            }

            .footer-section {
                grid-template-columns: 1fr;
                padding: 1.5rem;
            }

            .items-table {
                font-size: 0.85rem;
            }

            .items-table th,
            .items-table td {
                padding: 0.875rem 0.75rem;
            }

            .invoice-number {
                font-size: 1.75rem;
            }
        }

        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }

            .invoice-wrapper {
                padding: 0;
            }

            .invoice-content {
                box-shadow: none;
                border: 1px solid var(--gray-300);
            }

            .party-card,
            .details-card,
            .summary-card {
                box-shadow: none;
            }

            .items-table tbody tr:hover {
                background: transparent;
            }
        }
    </style>
@endsection