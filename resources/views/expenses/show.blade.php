@extends('layouts.admin')

@section('title', 'Transaction Details')

@section('content')
    <div class="diamond-management-container tracker-page">
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link"><i class="bi bi-house-door"></i>
                            Dashboard</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('expenses.index') }}" class="breadcrumb-link">Expenses</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Details</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-{{ $expense->transaction_type == 'in' ? 'arrow-down-circle' : 'arrow-up-circle' }}"
                            style="color: {{ $expense->transaction_type == 'in' ? 'var(--success)' : 'var(--danger)' }};"></i>
                        Transaction Details
                    </h1>
                </div>
                <div class="header-right">
                    <a href="{{ route('expenses.edit', $expense) }}" class="btn-primary-custom"><i class="bi bi-pencil"></i>
                        Edit</a>
                    <a href="{{ route('expenses.index') }}" class="btn-secondary-custom"><i class="bi bi-arrow-left"></i>
                        Back</a>
                </div>
            </div>
        </div>

        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"
                        style="background: {{ $expense->transaction_type == 'in' ? 'linear-gradient(135deg, var(--success), var(--success-dark))' : 'linear-gradient(135deg, var(--danger), var(--danger-dark))' }};">
                        <i class="bi bi-{{ $expense->transaction_type == 'in' ? 'arrow-down' : 'arrow-up' }}"></i>
                    </div>
                    <div class="section-text">
                        <h5 class="section-title">{{ $expense->transaction_type == 'in' ? 'Money In' : 'Money Out' }}</h5>
                        <p class="section-description">{{ $expense->date->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="detail-grid">
                    <div class="detail-item"
                        style="grid-column: 1 / -1; text-align: center; padding: 1.5rem; background: var(--light-gray); border-radius: 12px;">
                        <span class="detail-label">Amount</span>
                        <span class="detail-value {{ $expense->transaction_type == 'in' ? 'text-success' : 'text-danger' }}"
                            style="font-size: 2rem;">
                            ₹{{ number_format($expense->amount, 2) }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Title</span>
                        <span class="detail-value">{{ $expense->title }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Category</span>
                        <span class="detail-value"><span
                                class="badge-custom badge-secondary">{{ $expense->category_name }}</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Payment Method</span>
                        <span class="detail-value"><span
                                class="badge-custom badge-info">{{ \App\Models\Expense::PAYMENT_METHODS[$expense->payment_method] ?? $expense->payment_method }}</span></span>
                    </div>
                    <div class="detail-item">
                        <span
                            class="detail-label">{{ $expense->transaction_type == 'in' ? 'Received From' : 'Paid To' }}</span>
                        <span class="detail-value">{{ $expense->paid_to_received_from ?: '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Reference No.</span>
                        <span class="detail-value">{{ $expense->reference_number ?: '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Created By</span>
                        <span class="detail-value">{{ $expense->admin->name ?? 'N/A' }}</span>
                    </div>
                </div>
                @if($expense->notes)
                    <div class="detail-notes">
                        <span class="detail-label">Notes</span>
                        <p class="detail-value" style="margin-top: 0.5rem;">{{ $expense->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection