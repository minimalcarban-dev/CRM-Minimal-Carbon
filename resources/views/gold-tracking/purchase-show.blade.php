@extends('layouts.admin')

@section('title', 'Gold Purchase Details')

@section('content')
    <div class="diamond-management-container tracker-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('gold-tracking.index') }}" class="breadcrumb-link">Gold Tracking</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Purchase #{{ $purchase->id }}</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-coin" style="color: #f59e0b;"></i>
                        Gold Purchase Details
                    </h1>
                    <p class="page-subtitle">{{ $purchase->purchase_date->format('d M, Y') }}</p>
                </div>
                <div class="header-right">
                    <div class="tracker-actions-row">
                        <a href="{{ route('gold-tracking.purchases.edit', $purchase) }}" class="btn-secondary-custom">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div style="margin-bottom: 1.5rem;">
            @if($purchase->isPending())
                <span class="tracker-badge"
                    style="background: rgba(245, 158, 11, 0.1); color: #b45309; padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="bi bi-hourglass-split"></i> Pending Payment
                </span>
            @else
                <span class="tracker-badge"
                    style="background: rgba(16, 185, 129, 0.1); color: #065f46; padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="bi bi-check-circle"></i> Completed
                </span>
            @endif
        </div>

        <!-- Purchase Info Card -->
        <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
            <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                <i class="bi bi-receipt" style="color: #6366f1;"></i> Purchase Information
            </h3>
            <div class="detail-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div class="detail-item">
                    <div class="detail-label"
                        style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Purchase
                        Date</div>
                    <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                        {{ $purchase->purchase_date->format('d M, Y') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"
                        style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Weight</div>
                    <div class="detail-value" style="font-size: 1.25rem; color: #f59e0b; font-weight: 700;">
                        {{ number_format($purchase->weight_grams, 3) }} gm</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"
                        style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Rate per
                        Gram</div>
                    <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                        ₹{{ number_format($purchase->rate_per_gram, 2) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"
                        style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Total Amount
                    </div>
                    <div class="detail-value" style="font-size: 1.25rem; color: #10b981; font-weight: 700;">
                        ₹{{ number_format($purchase->total_amount, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Supplier Info Card -->
        <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
            <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                <i class="bi bi-person" style="color: #6366f1;"></i> Supplier Information
            </h3>
            <div class="detail-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div class="detail-item">
                    <div class="detail-label"
                        style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Supplier
                        Name</div>
                    <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 600;">
                        {{ $purchase->supplier_name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"
                        style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Mobile</div>
                    <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                        {{ $purchase->supplier_mobile ?? '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"
                        style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Invoice
                        Number</div>
                    <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                        {{ $purchase->invoice_number ?? '—' }}</div>
                </div>
            </div>
        </div>

        <!-- Payment Info Card -->
        @if($purchase->isCompleted())
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-credit-card" style="color: #6366f1;"></i> Payment Information
                </h3>
                <div class="detail-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div class="detail-item">
                        <div class="detail-label"
                            style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Payment Mode
                        </div>
                        <div class="detail-value">
                            <span class="tracker-badge tracker-badge-info">{{ $purchase->payment_mode_label }}</span>
                        </div>
                    </div>
                    @if($purchase->payment_mode === 'bank_transfer')
                        <div class="detail-item">
                            <div class="detail-label"
                                style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Account
                                Holder</div>
                            <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                                {{ $purchase->bank_account_name ?? '—' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"
                                style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Bank Name
                            </div>
                            <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                                {{ $purchase->bank_name ?? '—' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"
                                style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Account
                                Number</div>
                            <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                                {{ $purchase->bank_account_number ?? '—' }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($purchase->notes)
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-sticky" style="color: #6366f1;"></i> Notes
                </h3>
                <p style="color: #475569; line-height: 1.6; margin: 0;">{{ $purchase->notes }}</p>
            </div>
        @endif

        <!-- Linked Expense Card -->
        @if($purchase->expense)
            <div class="tracker-table-card" style="padding: 1.5rem;">
                <h3 style="margin: 0 0 1rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-link-45deg" style="color: #6366f1;"></i> Linked Expense
                </h3>
                <p style="color: #475569; margin: 0;">
                    This purchase is linked to expense entry
                    <a href="{{ route('expenses.show', $purchase->expense) }}" style="color: #6366f1; font-weight: 600;">
                        #{{ $purchase->expense->id }}
                    </a>
                    ({{ $purchase->expense->title }})
                </p>
            </div>
        @endif

        <!-- Meta Info -->
        <div
            style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; font-size: 0.85rem; color: #64748b;">
            <strong>Created by:</strong> {{ $purchase->admin->name ?? 'Unknown' }} •
            <strong>Created at:</strong> {{ $purchase->created_at->format('d M, Y h:i A') }}
            @if($purchase->updated_at->ne($purchase->created_at))
                • <strong>Last updated:</strong> {{ $purchase->updated_at->format('d M, Y h:i A') }}
            @endif
        </div>
    </div>
@endsection