@extends('layouts.admin')

@section('title', 'Purchase Details')

@section('content')
    <div class="diamond-management-container tracker-page">
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('purchases.index') }}" class="breadcrumb-link">Purchases</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Details</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-cart-check"></i>
                        Purchase Details
                    </h1>
                </div>
                <div class="header-right">
                    <a href="{{ route('purchases.edit', $purchase) }}" class="btn-primary-custom">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('purchases.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-gem"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Purchase Information</h5>
                        <p class="section-description">{{ $purchase->purchase_date->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Diamond Type</span>
                        <span class="detail-value"><span
                                class="badge-custom badge-primary">{{ $purchase->diamond_type }}</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Weight</span>
                        <span class="detail-value">{{ number_format($purchase->weight, 2) }} ct</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Per CT Price</span>
                        <span class="detail-value">₹{{ number_format($purchase->per_ct_price, 2) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Discount</span>
                        <span class="detail-value">{{ $purchase->discount_percent }}%</span>
                    </div>
                    <div class="detail-item"
                        style="grid-column: 1 / -1; text-align: center; padding: 1.5rem; background: var(--light-gray); border-radius: 12px;">
                        <span class="detail-label">Total Price</span>
                        <span class="detail-value text-success" style="font-size: 2rem;">
                            ₹{{ number_format($purchase->total_price, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-credit-card"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Payment & Party Details</h5>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Payment Mode</span>
                        <span class="detail-value">
                            <span
                                class="badge-custom {{ $purchase->payment_mode == 'upi' ? 'badge-info' : 'badge-secondary' }}">
                                {{ strtoupper($purchase->payment_mode) }}
                            </span>
                        </span>
                    </div>
                    @if($purchase->payment_mode == 'upi' && $purchase->upi_id)
                        <div class="detail-item">
                            <span class="detail-label">UPI ID</span>
                            <span class="detail-value">{{ $purchase->upi_id }}</span>
                        </div>
                    @endif
                    <div class="detail-item">
                        <span class="detail-label">Party Name</span>
                        <span class="detail-value">{{ $purchase->party_name }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Party Mobile</span>
                        <span class="detail-value">{{ $purchase->party_mobile ?: '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Invoice Number</span>
                        <span class="detail-value">{{ $purchase->invoice_number ?: '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Created By</span>
                        <span class="detail-value">{{ $purchase->admin->name ?? 'N/A' }}</span>
                    </div>
                </div>
                @if($purchase->notes)
                    <div class="detail-notes">
                        <span class="detail-label">Notes</span>
                        <p class="detail-value" style="margin-top: 0.5rem;">{{ $purchase->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection