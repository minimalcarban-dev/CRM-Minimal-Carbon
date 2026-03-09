@extends('layouts.admin')

@section('title', 'Transaction Details')

@section('content')
    <div class="diamond-management-container tracker-page expense-page">
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

        <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
            <h3
                style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b; display: flex; align-items: center; gap: 0.75rem;">
                <div
                    style="background: {{ $expense->transaction_type == 'in' ? 'linear-gradient(135deg, var(--success), var(--success-dark))' : 'linear-gradient(135deg, var(--danger), var(--danger-dark))' }}; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="bi bi-{{ $expense->transaction_type == 'in' ? 'arrow-down' : 'arrow-up' }}"></i>
                </div>
                <div>
                    <div>{{ $expense->transaction_type == 'in' ? 'Money In' : 'Money Out' }}</div>
                    <div style="font-size: 0.85rem; font-weight: 400; color: #64748b;">{{ $expense->date->format('d M Y') }}
                    </div>
                </div>
            </h3>
            <div>
                <div class="detail-grid">
                    <div class="detail-item"
                        style="grid-column: 1 / -1; text-align: center; padding: 1.5rem; background: var(--light-gray); border-radius: 12px;">
                        <span class="detail-label">Amount</span>
                        <span
                            class="detail-value {{ $expense->transaction_type == 'in' ? 'text-success' : 'text-danger' }}"
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
                @if ($expense->notes)
                    <div class="detail-notes" style="margin-bottom: 1.5rem;">
                        <span class="detail-label">Notes</span>
                        <p class="detail-value" style="margin-top: 0.5rem;">{{ $expense->notes }}</p>
                    </div>
                @endif

                @if ($expense->invoice_image)
                    <div class="detail-attachment" style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                        <span class="detail-label" style="display: block; margin-bottom: 1rem;">
                            <i class="bi bi-paperclip"></i> Attachment / Invoice
                        </span>

                        <div class="attachment-preview"
                            style="background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1; padding: 1rem; display: inline-block;">
                            @if ($expense->isInvoicePdf())
                                <div class="pdf-placeholder"
                                    style="display: flex; align-items: center; gap: 1rem; padding: 0.5rem 1rem;">
                                    <div
                                        style="background: #fee2e2; color: #dc2626; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </div>
                                    <div style="text-align: left;">
                                        <div style="font-weight: 500; font-size: 0.95rem;">
                                            {{ $expense->invoice_image['original_name'] ?? 'Invoice.pdf' }}</div>
                                        <div style="font-size: 0.8rem; color: #64748b;">PDF Document</div>
                                    </div>
                                    <a href="{{ $expense->invoice_image_url }}" target="_blank" class="btn-primary-custom"
                                        style="padding: 0.4rem 1rem; font-size: 0.85rem; margin-left: 1rem;">
                                        <i class="bi bi-eye"></i> View PDF
                                    </a>
                                </div>
                            @else
                                <div style="position: relative; line-height: 0;">
                                    <img src="{{ $expense->invoice_image_url }}" alt="Invoice"
                                        style="max-width: 100%; max-height: 400px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                        <a href="{{ $expense->invoice_image_url }}" target="_blank"
                                            class="btn-secondary-custom" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                            <i class="bi bi-arrows-fullscreen"></i> Full View
                                        </a>
                                        <a href="{{ $expense->invoice_image_url }}" download class="btn-secondary-custom"
                                            style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
