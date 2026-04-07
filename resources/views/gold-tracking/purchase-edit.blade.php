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

        <form id="goldPurchaseEditForm" action="{{ route('gold-tracking.purchases.update', $purchase) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="confirm_outlier_rate" id="confirm_outlier_rate" value="{{ old('confirm_outlier_rate') }}">

            <!-- Purchase Details -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-receipt" style="color: #6366f1;"></i> Purchase Details
                </h3>
                <div class="form-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Purchase Date <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="purchase_date" name="purchase_date"
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
                        <label class="form-label">Rate per Gram (&#8377;) <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="rate_per_gram" id="rate_per_gram"
                            class="form-control @error('rate_per_gram') is-invalid @enderror"
                            value="{{ old('rate_per_gram', $purchase->rate_per_gram) }}" step="0.01" min="0" required>
                        @error('rate_per_gram') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @error('confirm_outlier_rate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <small id="rate_source_info" style="display:block; margin-top:0.35rem; color:#64748b;">Checking selected date rate...</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Amount (Auto-calculated)</label>
                        <div id="total_amount_display"
                            style="padding: 0.75rem; background: rgba(16, 185, 129, 0.1); border-radius: 8px; font-weight: 700; font-size: 1.1rem; color: #10b981;">
                            &#8377;{{ number_format($purchase->total_amount, 2) }}
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
                        <label class="form-label">Supplier Name <span style="color: #ef4444;">*</span>
                            <a href="{{ route('parties.create') }}?category=gold_metal&redirect={{ urlencode(request()->url()) }}" 
                               style="margin-left: 10px; font-size: 0.85rem; font-weight: 500;" title="Add New Gold Metal Party">
                                <i class="bi bi-plus-lg"></i> Add New
                            </a>
                        </label>
                        <div class="custom-searchable-dropdown" id="supplierDropdownContainer" style="position: relative; width: 100%;">
                            <div class="dropdown-input-wrapper" style="position: relative; width: 100%;">
                                <input type="text" name="supplier_name" id="supplier_name"
                                    class="form-control dropdown-search-input @error('supplier_name') is-invalid @enderror"
                                    value="{{ old('supplier_name', $purchase->supplier_name) }}" 
                                    placeholder="Type to search or select..." autocomplete="off" required style="padding-right: 35px;">
                                <span class="dropdown-arrow" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6b7280;">
                                    <i class="bi bi-chevron-down"></i>
                                </span>
                            </div>
                            <div class="dropdown-menu-custom" id="supplierDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-height: 250px; overflow-y: auto; margin-top: 4px;">
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
                                        <i class="bi bi-inbox"></i> No suppliers found
                                    </div>
                                @endif
                            </div>
                            <input type="hidden" id="party_id" name="party_id" value="{{ old('party_id', $purchase->party_id) }}">
                        </div>
                        @if(!isset($parties) || $parties->isEmpty())
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                No Gold Metal parties found. <a href="{{ route('parties.create') }}?category=gold_metal">Add a party</a>
                            </small>
                        @endif
                        @error('supplier_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Supplier Mobile</label>
                        <input type="text" name="supplier_mobile" id="supplier_mobile"
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

            <!-- Invoice Image Upload -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-image" style="color: #6366f1;"></i> Invoice Image
                </h3>
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
                    <label class="form-label">{{ $purchase->invoice_image_url ? 'Replace Image' : 'Upload Image' }}</label>
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
            
            // Custom Searchable Dropdown for Supplier
            const suppliersData = @json(isset($parties) ? $parties->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'phone' => $p->mobile]) : []);
            const supplierInput = document.getElementById('supplier_name');
            const supplierDropdown = document.getElementById('supplierDropdown');
            const partyIdField = document.getElementById('party_id');
            const mobileField = document.getElementById('supplier_mobile');
            
            if (supplierInput && supplierDropdown) {
                // Show dropdown on focus
                supplierInput.addEventListener('focus', function() {
                    supplierDropdown.style.display = 'block';
                    filterSupplierDropdown('');
                });
                
                // Filter dropdown on input
                supplierInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    filterSupplierDropdown(searchTerm);
                    supplierDropdown.style.display = 'block';
                    
                    // Check if exact match exists
                    const exactMatch = suppliersData.find(s => s.name.toLowerCase() === searchTerm);
                    if (!exactMatch) {
                        partyIdField.value = '';
                    }
                });
                
                function filterSupplierDropdown(searchTerm) {
                    const items = supplierDropdown.querySelectorAll('.dropdown-item-custom');
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
                    
                    const emptyMsg = supplierDropdown.querySelector('.dropdown-empty');
                    if (emptyMsg) {
                        emptyMsg.style.display = hasVisible ? 'none' : 'block';
                    }
                }
                
                // Handle item click
                supplierDropdown.querySelectorAll('.dropdown-item-custom').forEach(item => {
                    item.addEventListener('click', function() {
                        supplierInput.value = this.dataset.name;
                        partyIdField.value = this.dataset.id;
                        if (this.dataset.phone) {
                            mobileField.value = this.dataset.phone;
                        }
                        supplierDropdown.style.display = 'none';
                    });
                    
                    item.addEventListener('mouseenter', function() {
                        this.style.background = '#f1f5f9';
                    });
                    item.addEventListener('mouseleave', function() {
                        this.style.background = '#fff';
                    });
                });
                
                // Close dropdown on outside click
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('#supplierDropdownContainer')) {
                        supplierDropdown.style.display = 'none';
                    }
                });
            }

            document.getElementById('weight_grams').addEventListener('input', calculateTotal);
            document.getElementById('rate_per_gram').addEventListener('input', calculateTotal);

            // Initialize on page load
            toggleBankFields();
            
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
        </script>
        <script>
            (function () {
                const OUTLIER_MIN_FACTOR = 0.70;
                const OUTLIER_MAX_FACTOR = 1.30;
                const goldRateEndpoint = "{{ route('gold-tracking.rate') }}";

                const purchaseForm = document.getElementById('goldPurchaseEditForm');
                const purchaseDateInput = document.getElementById('purchase_date');
                const rateInput = document.getElementById('rate_per_gram');
                const confirmOutlierInput = document.getElementById('confirm_outlier_rate');
                const rateSourceInfo = document.getElementById('rate_source_info');
                const totalAmountDisplay = document.getElementById('total_amount_display');
                const weightInput = document.getElementById('weight_grams');

                if (!purchaseForm || !purchaseDateInput || !rateInput || !confirmOutlierInput || !rateSourceInfo) {
                    return;
                }

                let latestRatePayload = null;
                let hasManualRateOverride = !!(rateInput.value && rateInput.value.trim() !== '');

                const updateTotalAmount = () => {
                    const weight = parseFloat(weightInput?.value || '0') || 0;
                    const rate = parseFloat(rateInput.value || '0') || 0;
                    const total = weight * rate;
                    if (totalAmountDisplay) {
                        totalAmountDisplay.textContent = '₹' + total.toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                };

                const fetchRateForDate = async (date, shouldAutofill = true) => {
                    latestRatePayload = null;

                    if (!date) {
                        rateSourceInfo.style.color = '#64748b';
                        rateSourceInfo.textContent = 'Select purchase date to load suggested rate.';
                        return;
                    }

                    rateSourceInfo.style.color = '#64748b';
                    rateSourceInfo.textContent = 'Fetching rate for selected date...';

                    try {
                        const response = await fetch(`${goldRateEndpoint}?date=${encodeURIComponent(date)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const payload = await response.json();
                        latestRatePayload = payload;

                        if (payload.success && payload.is_available) {
                            const suggested = parseFloat(payload.rate_inr_per_gram || 0);
                            if (shouldAutofill && !hasManualRateOverride && suggested > 0) {
                                rateInput.value = suggested.toFixed(2);
                                updateTotalAmount();
                            }

                            rateSourceInfo.style.color = '#0f766e';
                            rateSourceInfo.textContent =
                                `Suggested for ${payload.date}: ₹${suggested.toFixed(2)}/gm (${payload.source}${payload.is_live ? ', live' : ''})`;
                            return;
                        }

                        if (shouldAutofill && !hasManualRateOverride) {
                            rateInput.value = '';
                            updateTotalAmount();
                        }

                        rateSourceInfo.style.color = '#b45309';
                        rateSourceInfo.textContent = payload.message || 'No stored rate for selected date. Enter manually.';
                    } catch (error) {
                        if (shouldAutofill && !hasManualRateOverride) {
                            rateInput.value = '';
                            updateTotalAmount();
                        }

                        rateSourceInfo.style.color = '#dc2626';
                        rateSourceInfo.textContent = 'Unable to fetch rate right now. Enter rate manually.';
                    }
                };

                weightInput?.addEventListener('input', updateTotalAmount);
                rateInput.addEventListener('input', () => {
                    hasManualRateOverride = true;
                    updateTotalAmount();
                });

                purchaseDateInput.addEventListener('change', () => {
                    hasManualRateOverride = false;
                    fetchRateForDate(purchaseDateInput.value, true);
                });

                purchaseForm.addEventListener('submit', function (e) {
                    confirmOutlierInput.value = '';

                    const enteredRate = parseFloat(rateInput.value || '0') || 0;
                    const expectedRate = parseFloat(latestRatePayload?.rate_inr_per_gram || '0') || 0;

                    if (!latestRatePayload?.is_available || enteredRate <= 0 || expectedRate <= 0) {
                        return;
                    }

                    const isOutlier = enteredRate < (expectedRate * OUTLIER_MIN_FACTOR)
                        || enteredRate > (expectedRate * OUTLIER_MAX_FACTOR);

                    if (!isOutlier) {
                        return;
                    }

                    e.preventDefault();
                    const msg = `Entered rate ₹${enteredRate.toFixed(2)}/gm differs from expected ₹${expectedRate.toFixed(2)}/gm for selected date. Continue?`;
                    if (window.confirm(msg)) {
                        confirmOutlierInput.value = '1';
                        purchaseForm.submit();
                    }
                });

                updateTotalAmount();
                fetchRateForDate(purchaseDateInput.value, !hasManualRateOverride);
            })();
        </script>
    @endpush
@endsection
