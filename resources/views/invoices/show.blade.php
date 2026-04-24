@extends('layouts.admin')
@section('title', 'Invoice #' . $invoice->invoice_no)
@section('content')
    @php
        // Option C: GST hidden when foreign party OR region ≠ India
        $isForeignInvoice = ($invoice->invoice_region && $invoice->invoice_region !== 'IN')
            || ($invoice->billedTo && $invoice->billedTo->is_foreign);

        $regionData = \App\Models\Invoice::REGIONS[$invoice->invoice_region ?? 'IN'] ?? \App\Models\Invoice::REGIONS['IN'];
        $currencySymbol = $regionData['symbol'];
    @endphp
    <div class="diamond-management-container tracker-page invoice-page">
        <!-- Top Navigation & Page Header -->
        <div class="page-header" style="margin-bottom: 1.5rem;">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('invoices.index') }}" class="breadcrumb-link">
                            <i class="bi bi-arrow-left"></i> Back to Invoices
                        </a>
                    </div>
                </div>
                <div class="header-right" style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn-primary-custom"
                        style="background:var(--warning); color: #fff; text-decoration: none;">
                        <i class="bi bi-pencil"></i>
                        <span>Edit Invoice</span>
                    </a>
                    <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn-primary-custom"
                        style="text-decoration: none;">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Export PDF</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Invoice Content -->
        <div class="form-section-card" style="margin-top: 2rem;">
            <!-- Invoice Header -->
            <div class="section-header" style="justify-content: space-between;">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Invoice #{{ $invoice->invoice_no }}</h5>
                        <p class="section-description">
                            <span class="tracker-badge badge-{{ strtolower($invoice->invoice_type) }}">
                                {{ ucfirst($invoice->invoice_type) }}
                            </span>
                            <span class="tracker-badge badge-{{ strtolower($invoice->status) }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="invoice-date" style="font-weight: 600; color: var(--gray-700);">
                        <i class="bi bi-calendar-event"></i>
                        <span>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Party Information -->
            <div class="section-body">
                <div class="detail-grid">
                    <div class="detail-item" style="border-left: 4px solid var(--primary); padding-left: 1rem;">
                        <span class="detail-label" style="margin-bottom: 0.5rem;"><i class="bi bi-building"></i> Company
                            Details</span>
                        <span class="detail-value"
                            style="font-size: 1.1rem; margin-bottom: 0.25rem;">{{ $invoice->company->name ?? '-' }}</span>
                        <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem; line-height: 1.4;">
                            {{ $invoice->company->address ?? '-' }}
                        </div>
                        <div style="font-size: 0.85rem; color: var(--gray-700);">
                            <strong>GST No:</strong> {{ $invoice->company->gst_no ?? '-' }}
                        </div>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label" style="margin-bottom: 0.5rem;"><i class="bi bi-person"></i> Bill
                            To</span>
                        <span class="detail-value"
                            style="font-size: 1.1rem; margin-bottom: 0.25rem;">{{ $invoice->billedTo->name ?? '-' }}</span>
                        <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem; line-height: 1.4;">
                            {{ $invoice->billedTo->address ?? '-' }}
                        </div>
                        @if(!$isForeignInvoice)
                            @if(!empty($invoice->billedTo->gst_no))
                                <div style="font-size: 0.85rem; color: var(--gray-700);"><strong>GST No:</strong>
                                    {{ $invoice->billedTo->gst_no }}</div>
                            @endif
                            @if(!empty($invoice->billedTo->pan_no))
                                <div style="font-size: 0.85rem; color: var(--gray-700); margin-top: 0.25rem;"><strong>PAN
                                        No:</strong> {{ $invoice->billedTo->pan_no }}</div>
                            @endif
                        @endif
                    </div>

                    <div class="detail-item">
                        <span class="detail-label" style="margin-bottom: 0.5rem;"><i class="bi bi-truck"></i> Ship To</span>
                        <span class="detail-value"
                            style="font-size: 1.1rem; margin-bottom: 0.25rem;">{{ $invoice->shippedTo->name ?? '-' }}</span>
                        <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem; line-height: 1.4;">
                            {{ $invoice->shippedTo->address ?? '-' }}
                        </div>
                        @if(!$isForeignInvoice)
                            @if(!empty($invoice->shippedTo->gst_no))
                                <div style="font-size: 0.85rem; color: var(--gray-700);"><strong>GST No:</strong>
                                    {{ $invoice->shippedTo->gst_no }}</div>
                            @endif
                            @if(!empty($invoice->shippedTo->pan_no))
                                <div style="font-size: 0.85rem; color: var(--gray-700); margin-top: 0.25rem;"><strong>PAN
                                        No:</strong> {{ $invoice->shippedTo->pan_no }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="section-header" style="border-top: 1px solid var(--border); margin-top: 1rem;">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-list-ul"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Invoice Items</h5>
                    </div>
                </div>
            </div>
            <div class="section-body p-0" style="overflow-x: auto;">
                <table class="tracker-table" style="min-width: 800px; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Description of Goods</th>
                            <th style="width: 120px;">HSN Code</th>
                            <th style="width: 90px; text-align: center;">Pieces</th>
                            <th style="width: 90px; text-align: center;">Carats</th>
                            <th style="width: 130px; text-align: right;">Rate</th>
                            <th style="width: 150px; text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $k => $it)
                            <tr class="table-row">
                                <td class="text-muted" style="font-weight: 600;">{{ $k + 1 }}</td>
                                <td style="font-weight: 500; color: var(--gray-900);">{{ $it->description_of_goods }}</td>
                                <td style="font-family: monospace;">{{ $it->hsn_code }}</td>
                                <td style="text-align: center;">{{ $it->pieces }}</td>
                                <td style="text-align: center;">{{ (float) $it->carats }}</td>
                                <td style="text-align: right;">
                                    {{ $currencySymbol }}{{ number_format($it->rate, 2) }}
                                </td>
                                <td style="text-align: right; font-weight: 700; color: var(--gray-900);">
                                    {{ $currencySymbol }}{{ number_format($it->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Summary -->
            <div class="section-body"
                style="background: var(--light-gray); border-top: 1px solid var(--border); border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <div class="form-grid">
                    <div class="detail-item"
                        style="background: white; border-radius: 10px; padding: 1.5rem; border: 1px solid var(--border);">
                        <span class="detail-label" style="margin-bottom: 1rem;"><i class="bi bi-info-circle"></i> Additional
                            Details</span>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <div
                                style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                <span class="text-muted">Payment Terms</span>
                                <span style="font-weight: 600;">{{ $invoice->payment_terms ?? '-' }}</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                <span class="text-muted">Place of Supply</span>
                                <span style="font-weight: 600;">{{ $invoice->place_of_supply ?? '-' }}</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                <span class="text-muted">Terms & Conditions</span>
                                <span style="font-weight: 600;">{{ $invoice->include_terms_conditions ? 'Included' : 'Not Included' }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span class="text-muted">Copy Type</span>
                                <span style="font-weight: 600;">{{ ucfirst($invoice->copy_type ?? '-') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-item"
                        style="background: white; border-radius: 10px; padding: 1.5rem; border: 1px solid var(--border);">
                        <span class="detail-label" style="margin-bottom: 1rem;"><i class="bi bi-calculator"></i> Financial
                            Summary</span>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <div
                                style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                <span class="text-muted">Taxable Amount</span>
                                <span
                                    style="font-weight: 600;">{{ $currencySymbol }}{{ number_format($invoice->taxable_amount, 2) }}</span>
                            </div>
                            @if(!$isForeignInvoice)
                                <div
                                    style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                    <span class="text-muted">IGST</span>
                                    <span
                                        style="font-weight: 600;">{{ $currencySymbol }}{{ number_format($invoice->igst_amount, 2) }}</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                    <span class="text-muted">CGST</span>
                                    <span
                                        style="font-weight: 600;">{{ $currencySymbol }}{{ number_format($invoice->cgst_amount, 2) }}</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                    <span class="text-muted">SGST</span>
                                    <span
                                        style="font-weight: 600;">{{ $currencySymbol }}{{ number_format($invoice->sgst_amount, 2) }}</span>
                                </div>
                            @endif
                            @if(isset($invoice->express_shipping) && $invoice->express_shipping > 0)
                                <div
                                    style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                                    <span class="text-muted">Express Shipping</span>
                                    <span
                                        style="font-weight: 600;">{{ $currencySymbol }}{{ number_format($invoice->express_shipping, 2) }}</span>
                                </div>
                            @endif
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 1rem; border-top: 2px dashed var(--border);">
                                <span style="font-weight: 700; color: var(--primary);">Total Value</span>
                                <span
                                    style="font-weight: 800; font-size: 1.25rem; color: var(--primary);">{{ $currencySymbol }}{{ number_format($invoice->total_invoice_value, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
