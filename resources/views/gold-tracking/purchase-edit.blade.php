@extends('layouts.admin')

@section('title', 'Edit Gold Purchase')

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
                        <span class="breadcrumb-current">Edit Purchase</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-coin" style="color: #f59e0b;"></i>
                        Edit Gold Purchase
                    </h1>
                    <p class="page-subtitle">Update purchase details</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        @if($purchase->isPending())
            <div
                style="background: rgba(245, 158, 11, 0.1); border: 2px solid #f59e0b; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="bi bi-exclamation-triangle" style="font-size: 1.5rem; color: #f59e0b;"></i>
                <div>
                    <strong style="color: #b45309;">Pending Payment</strong>
                    <span style="color: #92400e;">- Add payment details below to complete this purchase.</span>
                </div>
            </div>
        @endif

        <form action="{{ route('gold-tracking.purchases.update', $purchase) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Purchase Details -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-receipt" style="color: #6366f1;"></i> Purchase Details
                </h3>
                <div class="form-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Purchase Date <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="purchase_date"
                            class="form-control @error('purchase_date') is-invalid @enderror"
                            value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
                        @error('purchase_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weight (grams) <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="weight_grams" id="weight_grams"
                            class="form-control @error('weight_grams') is-invalid @enderror"
                            value="{{ old('weight_grams', $purchase->weight_grams) }}" step="0.001" min="0.001" required>
                        @error('weight_grams') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rate per Gram (₹) <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="rate_per_gram" id="rate_per_gram"
                            class="form-control @error('rate_per_gram') is-invalid @enderror"
                            value="{{ old('rate_per_gram', $purchase->rate_per_gram) }}" step="0.01" min="0" required>
                        @error('rate_per_gram') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Amount (Auto-calculated)</label>
                        <div id="total_amount_display"
                            style="padding: 0.75rem; background: rgba(16, 185, 129, 0.1); border-radius: 8px; font-weight: 700; font-size: 1.1rem; color: #10b981;">
                            ₹{{ number_format($purchase->total_amount, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Details -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-person" style="color: #6366f1;"></i> Supplier Details
                </h3>
                <div class="form-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Supplier Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="supplier_name"
                            class="form-control @error('supplier_name') is-invalid @enderror"
                            value="{{ old('supplier_name', $purchase->supplier_name) }}" required>
                        @error('supplier_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Supplier Mobile</label>
                        <input type="text" name="supplier_mobile"
                            class="form-control @error('supplier_mobile') is-invalid @enderror"
                            value="{{ old('supplier_mobile', $purchase->supplier_mobile) }}">
                        @error('supplier_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number"
                            class="form-control @error('invoice_number') is-invalid @enderror"
                            value="{{ old('invoice_number', $purchase->invoice_number) }}">
                        @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-credit-card" style="color: #6366f1;"></i> Payment Details
                    @if($purchase->isPending())
                        <small style="font-weight: 400; color: #ef4444;"> (Required to complete purchase)</small>
                    @endif
                </h3>
                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label">Payment Mode</label>
                    <div class="tracker-payment-toggle" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <label class="tracker-toggle-option" style="cursor: pointer;">
                            <input type="radio" name="payment_mode" value="cash" {{ old('payment_mode', $purchase->payment_mode) == 'cash' ? 'checked' : '' }} onchange="toggleBankFields()"
                                style="display: none;">
                            <span class="tracker-toggle-btn"><i class="bi bi-cash"></i> Cash</span>
                        </label>
                        <label class="tracker-toggle-option" style="cursor: pointer;">
                            <input type="radio" name="payment_mode" value="bank_transfer" {{ old('payment_mode', $purchase->payment_mode) == 'bank_transfer' ? 'checked' : '' }} onchange="toggleBankFields()"
                                style="display: none;">
                            <span class="tracker-toggle-btn"><i class="bi bi-bank"></i> Bank Transfer</span>
                        </label>
                    </div>
                </div>

                <div id="bankFields" style="display: none;">
                    <div class="form-grid"
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Account Holder Name</label>
                            <input type="text" name="bank_account_name" class="form-control"
                                value="{{ old('bank_account_name', $purchase->bank_account_name) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control"
                                value="{{ old('bank_name', $purchase->bank_name) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control"
                                value="{{ old('bank_account_number', $purchase->bank_account_number) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" name="bank_ifsc" class="form-control"
                                value="{{ old('bank_ifsc', $purchase->bank_ifsc) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $purchase->notes) }}</textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="tracker-form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">Cancel</a>
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-lg"></i> Update Purchase
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function calculateTotal() {
                const weight = parseFloat(document.getElementById('weight_grams').value) || 0;
                const rate = parseFloat(document.getElementById('rate_per_gram').value) || 0;
                const total = weight * rate;
                document.getElementById('total_amount_display').textContent = '₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function toggleBankFields() {
                const paymentMode = document.querySelector('input[name="payment_mode"]:checked')?.value;
                document.getElementById('bankFields').style.display = paymentMode === 'bank_transfer' ? 'block' : 'none';
            }

            document.getElementById('weight_grams').addEventListener('input', calculateTotal);
            document.getElementById('rate_per_gram').addEventListener('input', calculateTotal);

            // Initialize on page load
            toggleBankFields();
        </script>
    @endpush
@endsection