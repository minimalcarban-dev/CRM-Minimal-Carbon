@extends('layouts.admin')

@section('title', 'Edit Purchase')

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
                    <span class="breadcrumb-current">Edit</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-pencil"></i>
                    Edit Purchase
                    @if($purchase->isPending())
                    <span
                        style="display: inline-flex; align-items: center; gap: 0.5rem; margin-left: 0.75rem; padding: 0.35rem 0.75rem; background: #fef3c7; color: #b45309; border-radius: 20px; font-size: 0.85rem; font-weight: 600;"><i
                            class="bi bi-hourglass-split"></i> Pending</span>
                    @else
                    <span
                        style="display: inline-flex; align-items: center; gap: 0.5rem; margin-left: 0.75rem; padding: 0.35rem 0.75rem; background: #d1fae5; color: #065f46; border-radius: 20px; font-size: 0.85rem; font-weight: 600;"><i
                            class="bi bi-check-circle"></i> Completed</span>
                    @endif
                </h1>
            </div>
            <div class="header-right">
                <a href="{{ route('purchases.index') }}" class="btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('purchases.update', $purchase) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                        <label for="purchase_date" class="form-label">Purchase Date <span
                                class="required">*</span></label>
                        <input type="date" id="purchase_date" name="purchase_date" class="form-control"
                            value="{{ old('purchase_date', $purchase->purchase_date_formatted) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="diamond_type" class="form-label">Diamond Type <span
                                class="required">*</span></label>
                        <input type="text" id="diamond_type" name="diamond_type" class="form-control"
                            value="{{ old('diamond_type', $purchase->diamond_type) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="per_ct_price" class="form-label">Per CT Price (₹) <span
                                class="required">*</span></label>
                        <input type="number" id="per_ct_price" name="per_ct_price" step="0.01" class="form-control"
                            value="{{ old('per_ct_price', $purchase->per_ct_price) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="weight" class="form-label">Weight (Carat) <span class="required">*</span></label>
                        <input type="number" id="weight" name="weight" step="0.01" class="form-control"
                            value="{{ old('weight', $purchase->weight) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="discount_percent" class="form-label">Discount (%)</label>
                        <input type="number" id="discount_percent" name="discount_percent" step="0.01"
                            class="form-control" value="{{ old('discount_percent', $purchase->discount_percent) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Price</label>
                        <div class="form-control" id="totalPriceDisplay"
                            style="background: var(--light-gray); font-weight: 700; color: var(--success);">
                            ₹{{ number_format($purchase->total_price, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-credit-card"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Payment & Party Info</h5>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    {{-- Payment Mode - Full Width --}}
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Payment Mode <span style="color: #9ca3af; font-weight: 400;">(optional
                                - leave empty for Pending)</span></label>
                        <div class="payment-toggle" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <label class="toggle-option">
                                <input type="radio" name="payment_mode" value="upi" {{ old('payment_mode', $purchase->payment_mode) == 'upi' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i class="bi bi-phone"></i> UPI</span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="payment_mode" value="cash" {{ old('payment_mode', $purchase->payment_mode) == 'cash' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i class="bi bi-cash"></i> Cash</span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="payment_mode" value="bank_transfer" {{ old('payment_mode', $purchase->payment_mode) == 'bank_transfer' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i class="bi bi-bank"></i> Bank Transfer</span>
                            </label>
                        </div>
                        @if($purchase->isPending())
                        <small style="display: block; margin-top: 0.5rem; color: #b45309;"><i
                                class="bi bi-exclamation-circle"></i> Select payment mode to complete this
                            purchase</small>
                        @endif
                    </div>

                    {{-- UPI ID Field - Full Width --}}
                    <div class="form-group" id="upiIdField" style="display: none; grid-column: 1 / -1;">
                        <label for="upi_id" class="form-label">UPI ID</label>
                        <input type="text" id="upi_id" name="upi_id" class="form-control"
                            value="{{ old('upi_id', $purchase->upi_id) }}" style="max-width: 400px;">
                    </div>

                    <div id="bankFields" style="display: none; grid-column: 1 / -1;">
                        <div class="form-grid" style="margin-bottom: 0;">
                            <div class="form-group">
                                <label for="bank_account_name" class="form-label">Account Holder Name</label>
                                <input type="text" id="bank_account_name" name="bank_account_name" class="form-control"
                                    value="{{ old('bank_account_name', $purchase->bank_account_name) }}">
                            </div>
                            <div class="form-group">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" id="bank_name" name="bank_name" class="form-control"
                                    value="{{ old('bank_name', $purchase->bank_name) }}">
                            </div>
                            <div class="form-group">
                                <label for="bank_account_number" class="form-label">Account Number</label>
                                <input type="text" id="bank_account_number" name="bank_account_number"
                                    class="form-control"
                                    value="{{ old('bank_account_number', $purchase->bank_account_number) }}">
                            </div>
                            <div class="form-group">
                                <label for="bank_ifsc" class="form-label">IFSC Code</label>
                                <input type="text" id="bank_ifsc" name="bank_ifsc" class="form-control"
                                    value="{{ old('bank_ifsc', $purchase->bank_ifsc) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="party_name" class="form-label">Party Name <span class="required">*</span></label>
                        <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                            <div class="custom-searchable-dropdown" id="partyDropdownContainer" style="flex: 1; position: relative;">
                                <div class="dropdown-input-wrapper" style="position: relative;">
                                    <input type="text" id="party_name" name="party_name"
                                        class="form-control dropdown-search-input @error('party_name') is-invalid @enderror"
                                        value="{{ old('party_name', $purchase->party_name) }}"
                                        placeholder="Type to search or select..." autocomplete="off" required>
                                    <span class="dropdown-arrow" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6b7280;">
                                        <i class="bi bi-chevron-down"></i>
                                    </span>
                                </div>
                                <div class="dropdown-menu-custom" id="partyDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-height: 250px; overflow-y: auto; margin-top: 4px;">
                                    @if(isset($parties) && $parties->count())
                                    @foreach($parties as $party)
                                    <div class="dropdown-item-custom"
                                        data-id="{{ $party->id }}"
                                        data-name="{{ $party->name }}"
                                        data-phone="{{ $party->mobile }}"
                                        style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: all 0.15s;">
                                        <div style="font-weight: 500; color: #1e293b;">{{ $party->name }}</div>
                                        @if($party->mobile)
                                        <div style="font-size: 0.8rem; color: #64748b;">{{ $party->mobile }}</div>
                                        @endif
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="dropdown-empty" style="padding: 14px; text-align: center; color: #94a3b8;">
                                        <i class="bi bi-inbox"></i> No parties found
                                    </div>
                                    @endif
                                </div>
                                <input type="hidden" id="party_id" name="party_id" value="{{ old('party_id', $purchase->party_id) }}">
                            </div>
                            <a href="{{ route('parties.create') }}?category=diamond_gemstone&redirect={{ urlencode(request()->url()) }}"
                                class="btn-secondary-custom" style="white-space: nowrap; height: 42px; display: flex; align-items: center;" title="Add New Diamond & Gemstone Party">
                                <i class="bi bi-plus-lg"></i> Add New
                            </a>
                        </div>
                        @if(!isset($parties) || $parties->isEmpty())
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            No Diamond & Gemstone parties found. <a href="{{ route('parties.create') }}?category=diamond_gemstone">Add a party</a>
                        </small>
                        @endif
                        @error('party_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="party_mobile" class="form-label">Party Mobile</label>
                        <input type="text" id="party_mobile" name="party_mobile" class="form-control"
                            value="{{ old('party_mobile', $purchase->party_mobile) }}">
                    </div>
                    <div class="form-group">
                        <label for="invoice_number" class="form-label">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number" class="form-control"
                            value="{{ old('invoice_number', $purchase->invoice_number) }}">
                    </div>
                    <div class="form-group form-group-full">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control"
                            rows="3">{{ old('notes', $purchase->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Image Upload -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-image"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Invoice Image</h5>
                        <p class="section-description">Upload or update invoice image (optional)</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                @if($purchase->invoice_image_url)
                <div class="form-group">
                    <label class="form-label">Current Image</label>
                    <div class="current-image-container" style="margin-bottom: 15px;">
                        <img src="{{ $purchase->invoice_image_url }}" alt="Current Invoice"
                            style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #dee2e6;">
                        <div class="mt-2">
                            <label class="text-danger" style="cursor: pointer;">
                                <input type="checkbox" name="remove_invoice_image" value="1">
                                <i class="bi bi-trash"></i> Remove current image
                            </label>
                        </div>
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <label for="invoice_image" class="form-label">{{ $purchase->invoice_image_url ? 'Replace Image' : 'Upload Image' }}</label>
                    <input type="file" id="invoice_image" name="invoice_image"
                        class="form-control @error('invoice_image') is-invalid @enderror"
                        accept="image/*">
                    <small class="text-muted">Accepted formats: JPG, PNG, GIF. Max size: 5MB</small>
                    @error('invoice_image')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #dee2e6;">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
            <a href="{{ route('purchases.index') }}" class="btn-secondary-custom">
                <i class="bi bi-x-lg"></i> Cancel
            </a>
            <button type="submit" class="btn-primary-custom">
                <i class="bi bi-check-lg"></i> Update Purchase
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
        const partiesData = @json(isset($parties) ? $parties - > map(fn($p) => ['id' => $p - > id, 'name' => $p - > name, 'phone' => $p - > mobile]) : []);
        const partyInput = document.getElementById('party_name');
        const partyDropdown = document.getElementById('partyDropdown');
        const partyIdField = document.getElementById('party_id');
        const mobileField = document.getElementById('party_mobile');
        const dropdownContainer = document.getElementById('partyDropdownContainer');

        // Filter dropdown items
        function filterPartyDropdown() {
            const searchTerm = partyInput.value.toLowerCase();
            const items = partyDropdown.querySelectorAll('.dropdown-item-custom');
            let hasVisible = false;

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasVisible = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Clear party_id if input doesn't match any party
            const matchedParty = partiesData.find(p => p.name.toLowerCase() === searchTerm);
            if (!matchedParty) {
                partyIdField.value = '';
            }

            return hasVisible;
        }

        // Show dropdown
        function showPartyDropdown() {
            filterPartyDropdown();
            partyDropdown.style.display = 'block';
        }

        // Hide dropdown
        function hidePartyDropdown() {
            partyDropdown.style.display = 'none';
        }

        // Select party from dropdown
        function selectParty(id, name, phone) {
            partyInput.value = name;
            partyIdField.value = id;
            if (phone && !mobileField.value) {
                mobileField.value = phone;
            }
            hidePartyDropdown();
        }

        // Event listeners for party dropdown
        partyInput.addEventListener('focus', showPartyDropdown);
        partyInput.addEventListener('input', function() {
            filterPartyDropdown();
            partyDropdown.style.display = 'block';
        });

        // Click on dropdown items
        partyDropdown.querySelectorAll('.dropdown-item-custom').forEach(item => {
            item.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const phone = this.dataset.phone || '';
                selectParty(id, name, phone);
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownContainer.contains(e.target)) {
                hidePartyDropdown();
            }
        });

        // Image preview
        const imageInput = document.getElementById('invoice_image');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                }
            });
        }
    });
</script>
@endsection