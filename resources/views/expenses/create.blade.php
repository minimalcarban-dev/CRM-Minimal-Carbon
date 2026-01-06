@extends('layouts.admin')

@section('title', 'Add Transaction')

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
                    <a href="{{ route('expenses.index') }}" class="breadcrumb-link">Expenses</a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Add New</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-plus-circle"></i>
                    Add Transaction
                </h1>
            </div>
            <div class="header-right">
                <a href="{{ route('expenses.index') }}" class="btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('expenses.store') }}" method="POST" id="expenseForm">
        @csrf

        <!-- Transaction Type -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-arrow-down-up"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Transaction Type</h5>
                        <p class="section-description">Select if money is coming in or going out</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="transaction-type-toggle">
                    <label class="type-option type-in">
                        <input type="radio" name="transaction_type" value="in" {{ old('transaction_type', 'in') == 'in' ? 'checked' : '' }}>
                        <span class="type-btn">
                            <i class="bi bi-arrow-down-circle"></i>
                            <strong>Money In</strong>
                            <small>Income / Payment Received</small>
                        </span>
                    </label>
                    <label class="type-option type-out">
                        <input type="radio" name="transaction_type" value="out" {{ old('transaction_type') == 'out' ? 'checked' : '' }}>
                        <span class="type-btn">
                            <i class="bi bi-arrow-up-circle"></i>
                            <strong>Money Out</strong>
                            <small>Expense / Payment Made</small>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-card-text"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Transaction Details</h5>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="date" class="form-label">Date <span class="required">*</span></label>
                        <input type="date" id="date" name="date" class="form-control @error('date') is-invalid @enderror"
                            value="{{ old('date', date('Y-m-d')) }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="title" class="form-label">Title / Purpose <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" placeholder="e.g., Electricity Bill, Customer Payment" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="amount" class="form-label">Amount (₹) <span class="required">*</span></label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount') }}" placeholder="0.00" required
                            style="font-size: 1.125rem; font-weight: 600;">
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Category <span class="required">*</span></label>
                        <select id="category" name="category" class="form-control @error('category') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            <optgroup label="Income" id="incomeCategories">
                                @foreach($incomeCategories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Expense" id="expenseCategories" style="display:none;">
                                @foreach($expenseCategories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="payment_method" class="form-label">Payment Method <span class="required">*</span></label>
                        <select id="payment_method" name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}" {{ old('payment_method', 'cash') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="paid_to_received_from" class="form-label">Paid To / Received From</label>
                        <input type="text" id="paid_to_received_from" name="paid_to_received_from" class="form-control"
                            value="{{ old('paid_to_received_from') }}" placeholder="Person or Company name">
                    </div>

                    <div class="form-group">
                        <label for="reference_number" class="form-label">Reference / Receipt No.</label>
                        <input type="text" id="reference_number" name="reference_number" class="form-control"
                            value="{{ old('reference_number') }}" placeholder="Optional">
                    </div>

                    <div class="form-group form-group-full">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Additional details...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
            <a href="{{ route('expenses.index') }}" class="btn-secondary-custom">
                <i class="bi bi-x-lg"></i> Cancel
            </a>
            <button type="submit" class="btn-primary-custom">
                <i class="bi bi-check-lg"></i> Save Transaction
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="transaction_type"]');
    const incomeGroup = document.getElementById('incomeCategories');
    const expenseGroup = document.getElementById('expenseCategories');
    const categorySelect = document.getElementById('category');

    function updateCategories() {
        const selected = document.querySelector('input[name="transaction_type"]:checked');
        if (selected && selected.value === 'in') {
            incomeGroup.style.display = '';
            expenseGroup.style.display = 'none';
            const currentVal = categorySelect.value;
            if (currentVal && (currentVal.includes('_out') || currentVal.includes('bill') || currentVal.includes('expense'))) {
                categorySelect.value = '';
            }
        } else {
            incomeGroup.style.display = 'none';
            expenseGroup.style.display = '';
            const currentVal = categorySelect.value;
            if (currentVal && (currentVal.includes('_in') || currentVal.includes('income'))) {
                categorySelect.value = '';
            }
        }
    }

    typeRadios.forEach(r => r.addEventListener('change', updateCategories));
    updateCategories();
});
</script>
@endsection
