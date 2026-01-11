@extends('layouts.admin')

@section('title', 'Add New Purchase')

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
                    <a href="{{ route('purchases.index') }}" class="breadcrumb-link">Purchases</a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Add New</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-cart-plus"></i>
                    Add New Purchase
                </h1>
            </div>
            <div class="header-right">
                <a href="{{ route('purchases.index') }}" class="btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
        @csrf

        <!-- Purchase Details Section -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-gem"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Purchase Details</h5>
                        <p class="section-description">Diamond information and pricing</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="purchase_date" class="form-label">
                            Purchase Date <span class="required">*</span>
                        </label>
                        <input type="date" id="purchase_date" name="purchase_date" 
                            class="form-control @error('purchase_date') is-invalid @enderror"
                            value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="diamond_type" class="form-label">
                            Diamond Type <span class="required">*</span>
                        </label>
                        <input type="text" id="diamond_type" name="diamond_type" 
                            class="form-control @error('diamond_type') is-invalid @enderror"
                            value="{{ old('diamond_type') }}" 
                            placeholder="e.g., CVD, HPHT, Natural" required>
                        @error('diamond_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="per_ct_price" class="form-label">
                            Per CT Price (₹) <span class="required">*</span>
                        </label>
                        <input type="number" id="per_ct_price" name="per_ct_price" step="0.01" min="0"
                            class="form-control @error('per_ct_price') is-invalid @enderror"
                            value="{{ old('per_ct_price') }}" placeholder="0.00" required>
                        @error('per_ct_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="weight" class="form-label">
                            Weight (Carat) <span class="required">*</span>
                        </label>
                        <input type="number" id="weight" name="weight" step="0.01" min="0.01"
                            class="form-control @error('weight') is-invalid @enderror"
                            value="{{ old('weight') }}" placeholder="0.00" required>
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="discount_percent" class="form-label">
                            Discount (%)
                        </label>
                        <input type="number" id="discount_percent" name="discount_percent" step="0.01" min="0" max="100"
                            class="form-control @error('discount_percent') is-invalid @enderror"
                            value="{{ old('discount_percent', 0) }}" placeholder="0">
                        @error('discount_percent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Total Price (₹)</label>
                        <div class="form-control" id="totalPriceDisplay" style="background: var(--light-gray); font-weight: 700; color: var(--success);">
                            ₹0.00
                        </div>
                        <div class="form-hint">
                            <i class="bi bi-info-circle"></i> Auto-calculated: (Per CT × Weight) - Discount
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment & Party Info -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-credit-card"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Payment & Party Info</h5>
                        <p class="section-description">Payment method and vendor details</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Payment Mode <span class="required">*</span></label>
                        <div class="payment-toggle">
                            <label class="toggle-option">
                                <input type="radio" name="payment_mode" value="upi" {{ old('payment_mode', 'upi') == 'upi' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i class="bi bi-phone"></i> UPI</span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="payment_mode" value="cash" {{ old('payment_mode') == 'cash' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i class="bi bi-cash"></i> Cash</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="upiIdField">
                        <label for="upi_id" class="form-label">UPI ID</label>
                        <input type="text" id="upi_id" name="upi_id" 
                            class="form-control @error('upi_id') is-invalid @enderror"
                            value="{{ old('upi_id') }}" placeholder="example@upi">
                        @error('upi_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="party_name" class="form-label">
                            Party Name <span class="required">*</span>
                        </label>
                        <input type="text" id="party_name" name="party_name" 
                            class="form-control @error('party_name') is-invalid @enderror"
                            value="{{ old('party_name') }}" placeholder="Vendor/Seller name" required>
                        @error('party_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="party_mobile" class="form-label">Party Mobile</label>
                        <input type="text" id="party_mobile" name="party_mobile" 
                            class="form-control @error('party_mobile') is-invalid @enderror"
                            value="{{ old('party_mobile') }}" placeholder="+91 XXXXXXXXXX">
                        @error('party_mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="invoice_number" class="form-label">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number" 
                            class="form-control @error('invoice_number') is-invalid @enderror"
                            value="{{ old('invoice_number') }}" placeholder="Optional">
                        @error('invoice_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group form-group-full">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3" 
                            placeholder="Additional notes...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-actions" style="justify-content: flex-end;">
            <a href="{{ route('purchases.index') }}" class="btn-secondary-custom">
                <i class="bi bi-x-lg"></i> Cancel
            </a>
            <button type="submit" class="btn-primary-custom">
                <i class="bi bi-check-lg"></i> Save Purchase
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const perCt = document.getElementById('per_ct_price');
    const weight = document.getElementById('weight');
    const discount = document.getElementById('discount_percent');
    const totalDisplay = document.getElementById('totalPriceDisplay');

    function calculateTotal() {
        const p = parseFloat(perCt.value) || 0;
        const w = parseFloat(weight.value) || 0;
        const d = parseFloat(discount.value) || 0;
        const subtotal = p * w;
        const discountAmt = (subtotal * d) / 100;
        const total = subtotal - discountAmt;
        totalDisplay.textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    perCt.addEventListener('input', calculateTotal);
    weight.addEventListener('input', calculateTotal);
    discount.addEventListener('input', calculateTotal);
    calculateTotal();

    // Toggle UPI field
    const paymentModes = document.querySelectorAll('input[name="payment_mode"]');
    const upiField = document.getElementById('upiIdField');
    
    function toggleUpiField() {
        const selected = document.querySelector('input[name="payment_mode"]:checked');
        upiField.style.display = selected && selected.value === 'upi' ? 'block' : 'none';
    }
    
    paymentModes.forEach(r => r.addEventListener('change', toggleUpiField));
    toggleUpiField();
});
</script>
@endsection
