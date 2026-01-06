@extends('layouts.admin')

@section('title', 'Edit Transaction')

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
                        <span class="breadcrumb-current">Edit</span>
                    </div>
                    <h1 class="page-title"><i class="bi bi-pencil"></i> Edit Transaction</h1>
                </div>
                <div class="header-right">
                    <a href="{{ route('expenses.index') }}" class="btn-secondary-custom"><i class="bi bi-arrow-left"></i>
                        Back</a>
                </div>
            </div>
        </div>

        <form action="{{ route('expenses.update', $expense) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon"><i class="bi bi-arrow-down-up"></i></div>
                        <div class="section-text">
                            <h5 class="section-title">Transaction Type</h5>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="transaction-type-toggle">
                        <label class="type-option type-in">
                            <input type="radio" name="transaction_type" value="in" {{ old('transaction_type', $expense->transaction_type) == 'in' ? 'checked' : '' }}>
                            <span class="type-btn">
                                <i class="bi bi-arrow-down-circle"></i>
                                <strong>Money In</strong>
                            </span>
                        </label>
                        <label class="type-option type-out">
                            <input type="radio" name="transaction_type" value="out" {{ old('transaction_type', $expense->transaction_type) == 'out' ? 'checked' : '' }}>
                            <span class="type-btn">
                                <i class="bi bi-arrow-up-circle"></i>
                                <strong>Money Out</strong>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

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
                            <input type="date" id="date" name="date" class="form-control"
                                value="{{ old('date', $expense->date_formatted) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="title" class="form-label">Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" class="form-control"
                                value="{{ old('title', $expense->title) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="amount" class="form-label">Amount (₹) <span class="required">*</span></label>
                            <input type="number" id="amount" name="amount" step="0.01" class="form-control"
                                value="{{ old('amount', $expense->amount) }}" required
                                style="font-size: 1.125rem; font-weight: 600;">
                        </div>
                        <div class="form-group">
                            <label for="category" class="form-label">Category <span class="required">*</span></label>
                            <select id="category" name="category" class="form-control" required>
                                <optgroup label="Income" id="incomeCategories">
                                    @foreach($incomeCategories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Expense" id="expenseCategories">
                                    @foreach($expenseCategories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment_method" class="form-label">Payment Method <span
                                    class="required">*</span></label>
                            <select id="payment_method" name="payment_method" class="form-control" required>
                                @foreach($paymentMethods as $key => $label)
                                    <option value="{{ $key }}" {{ old('payment_method', $expense->payment_method) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="paid_to_received_from" class="form-label">Paid To / Received From</label>
                            <input type="text" id="paid_to_received_from" name="paid_to_received_from" class="form-control"
                                value="{{ old('paid_to_received_from', $expense->paid_to_received_from) }}">
                        </div>
                        <div class="form-group">
                            <label for="reference_number" class="form-label">Reference No.</label>
                            <input type="text" id="reference_number" name="reference_number" class="form-control"
                                value="{{ old('reference_number', $expense->reference_number) }}">
                        </div>
                        <div class="form-group form-group-full">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes" name="notes" class="form-control"
                                rows="2">{{ old('notes', $expense->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions" style="justify-content: flex-end;">
                <a href="{{ route('expenses.index') }}" class="btn-secondary-custom"><i class="bi bi-x-lg"></i> Cancel</a>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Update</button>
            </div>
        </form>
    </div>
@endsection