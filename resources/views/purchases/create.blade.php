@extends('layouts.admin')

@section('title', 'Add New Purchase')

@section('content')
<div class="diamond-management-container tracker-page purchase-page">
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

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" enctype="multipart/form-data">
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
                    {{-- Payment Mode - Full Width --}}
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Payment Mode <span style="color: #9ca3af; font-weight: 400;">(optional - leave empty for Pending)</span></label>
                        <div class="payment-toggle">
                            <label class="toggle-option">
                                <input type="radio" name="payment_mode" value="upi" {{ old('payment_mode') == 'upi' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i class="bi bi-phone"></i> UPI</span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="payment_mode" value="cash" {{ old('payment_mode') == 'cash' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i class="bi bi-cash"></i> Cash</span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="payment_mode" value="bank_transfer" {{ old('payment_mode') == 'bank_transfer' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i class="bi bi-bank"></i> Bank Transfer</span>
                            </label>
                        </div>
                        <small style="display: block; margin-top: 0.5rem; color: #6b7280;"><i class="bi bi-info-circle"></i> If no payment mode selected, purchase will be saved as Pending</small>
                    </div>

                    {{-- UPI ID Field - Full Width --}}
                    <div class="form-group" id="upiIdField" style="display: none; grid-column: 1 / -1;">
                        <label for="upi_id" class="form-label">UPI ID</label>
                        <input type="text" id="upi_id" name="upi_id"
                            class="form-control @error('upi_id') is-invalid @enderror"
                            value="{{ old('upi_id') }}" placeholder="example@upi" style="max-width: 400px;">
                        @error('upi_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Bank Fields - Full Width --}}
                    <div id="bankFields" style="display: none; grid-column: 1 / -1;">
                        <div class="form-grid" style="margin-bottom: 0;">
                            <div class="form-group">
                                <label for="bank_account_name" class="form-label">Account Holder Name</label>
                                <input type="text" id="bank_account_name" name="bank_account_name"
                                    class="form-control @error('bank_account_name') is-invalid @enderror"
                                    value="{{ old('bank_account_name') }}" placeholder="Account holder name">
                                @error('bank_account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" id="bank_name" name="bank_name"
                                    class="form-control @error('bank_name') is-invalid @enderror"
                                    value="{{ old('bank_name') }}" placeholder="Bank name">
                                @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bank_account_number" class="form-label">Account Number</label>
                                <input type="text" id="bank_account_number" name="bank_account_number"
                                    class="form-control @error('bank_account_number') is-invalid @enderror"
                                    value="{{ old('bank_account_number') }}" placeholder="Account number">
                                @error('bank_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bank_ifsc" class="form-label">IFSC Code</label>
                                <input type="text" id="bank_ifsc" name="bank_ifsc"
                                    class="form-control @error('bank_ifsc') is-invalid @enderror"
                                    value="{{ old('bank_ifsc') }}" placeholder="IFSC code">
                                @error('bank_ifsc')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Party Selection with Custom Dropdown --}}
                    <div class="form-group">
                        <label for="party_name" class="form-label">
                            Party Name <span class="required">*</span>
                            <a href="{{ route('parties.create') }}?category=diamond_gemstone&redirect={{ urlencode(request()->url()) }}"
                                style="margin-left: 10px; font-size: 0.85rem; font-weight: 500;" title="Add New Diamond & Gemstone Party">
                                <i class="bi bi-plus-lg"></i> Add New
                            </a>
                        </label>
                        <div class="custom-searchable-dropdown" id="partyDropdownContainer" style="position: relative; width: 100%;">
                            <div class="dropdown-input-wrapper" style="position: relative; width: 100%;">
                                <input type="text" id="party_name" name="party_name"
                                    class="form-control dropdown-search-input @error('party_name') is-invalid @enderror"
                                    value="{{ old('party_name') }}" placeholder="Type to search or select..."
                                    autocomplete="off" required style="padding-right: 35px;">
                                <span class="dropdown-arrow" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6b7280;">
                                    <i class="bi bi-chevron-down"></i>
                                </span>
                            </div>
                            <div class="dropdown-menu-custom" id="partyDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-height: 250px; overflow-y: auto; margin-top: 4px;">
                                @forelse($parties as $party)
                                <div class="dropdown-item-custom"
                                    data-id="{{ $party->id }}"
                                    data-name="{{ $party->name }}"
                                    data-phone="{{ $party->phone }}"
                                    style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: all 0.15s;">
                                    <div style="font-weight: 500; color: #1e293b;">{{ $party->name }}</div>
                                    @if($party->phone)
                                    <div style="font-size: 0.8rem; color: #64748b;">{{ $party->phone }}</div>
                                    @endif
                                </div>
                                @empty
                                <div class="dropdown-empty" style="padding: 14px; text-align: center; color: #94a3b8;">
                                    <i class="bi bi-inbox"></i> No parties found
                                </div>
                                @endforelse
                            </div>
                            <input type="hidden" id="party_id" name="party_id" value="{{ old('party_id') }}">
                        </div>
                        @if($parties->isEmpty())
                        <small style="color: #f59e0b; margin-top: 0.25rem; display: block;">
                            <i class="bi bi-exclamation-triangle"></i> No Diamond & Gemstone parties found. <a href="{{ route('parties.create') }}?category=diamond_gemstone">Add a party</a>
                        </small>
                        @endif
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

        <!-- Invoice Image Upload - NEW -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-image"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Invoice Image</h5>
                        <p class="section-description">Upload invoice image or PDF (Optional)</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label class="form-label">Upload Invoice Image/PDF</label>
                    <input type="file" name="invoice_image" id="invoice_image"
                        class="form-control @error('invoice_image') is-invalid @enderror"
                        accept="image/jpeg,image/jpg,image/png,application/pdf">
                    <small style="color: #64748b; margin-top: 0.25rem; display: block;">
                        Supported: JPG, PNG, PDF (Max 5MB)
                    </small>
                    @error('invoice_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div id="imagePreview" style="margin-top: 1rem; display: none;">
                        <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #e2e8f0;">
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
            totalDisplay.textContent = '₹' + total.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        perCt.addEventListener('input', calculateTotal);
        weight.addEventListener('input', calculateTotal);
        discount.addEventListener('input', calculateTotal);
        calculateTotal();

        // Toggle Payment Mode Fields
        const paymentModes = document.querySelectorAll('input[name="payment_mode"]');
        const upiField = document.getElementById('upiIdField');
        const bankFields = document.getElementById('bankFields');

        function togglePaymentFields() {
            const selected = document.querySelector('input[name="payment_mode"]:checked');
            upiField.style.display = selected && selected.value === 'upi' ? 'block' : 'none';
            bankFields.style.display = selected && selected.value === 'bank_transfer' ? 'block' : 'none';
        }

        paymentModes.forEach(r => r.addEventListener('change', togglePaymentFields));
        togglePaymentFields();

        // Custom Searchable Dropdown for Party
        const partiesData = @json($parties->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'phone' => $p->phone]));
        const partyInput = document.getElementById('party_name');
        const partyDropdown = document.getElementById('partyDropdown');
        const partyIdField = document.getElementById('party_id');
        const mobileField = document.getElementById('party_mobile');

        // Show dropdown on focus
        partyInput.addEventListener('focus', function() {
            partyDropdown.style.display = 'block';
            filterPartyDropdown('');
        });

        // Filter dropdown on input
        partyInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterPartyDropdown(searchTerm);
            partyDropdown.style.display = 'block';

            // Check if exact match exists
            const exactMatch = partiesData.find(p => p.name.toLowerCase() === searchTerm);
            if (!exactMatch) {
                partyIdField.value = '';
            }
        });

        function filterPartyDropdown(searchTerm) {
            const items = partyDropdown.querySelectorAll('.dropdown-item-custom');
            let hasVisible = false;

            items.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const phone = (item.dataset.phone || '').toLowerCase();
                if (name.includes(searchTerm) || phone.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasVisible = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide empty message
            const emptyMsg = partyDropdown.querySelector('.dropdown-empty');
            if (emptyMsg) {
                emptyMsg.style.display = hasVisible ? 'none' : 'block';
            }
        }

        // Handle item click
        partyDropdown.querySelectorAll('.dropdown-item-custom').forEach(item => {
            item.addEventListener('click', function() {
                partyInput.value = this.dataset.name;
                partyIdField.value = this.dataset.id;
                if (this.dataset.phone) {
                    mobileField.value = this.dataset.phone;
                }
                partyDropdown.style.display = 'none';
            });

            // Hover effect
            item.addEventListener('mouseenter', function() {
                this.style.background = '#f1f5f9';
            });
            item.addEventListener('mouseleave', function() {
                this.style.background = '#fff';
            });
        });

        // Close dropdown on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#partyDropdownContainer')) {
                partyDropdown.style.display = 'none';
            }
        });

        // Invoice image preview
        document.getElementById('invoice_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    });
</script>
@endsection
