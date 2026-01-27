@extends('layouts.admin')

@section('title', 'Add Gold Purchase')

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
                        <span class="breadcrumb-current">Add Purchase</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-coin" style="color: #f59e0b;"></i>
                        Add Gold Purchase
                    </h1>
                    <p class="page-subtitle">Record a new gold purchase to add to your stock</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('gold-tracking.purchases.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

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
                            value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weight (grams) <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="weight_grams" id="weight_grams"
                            class="form-control @error('weight_grams') is-invalid @enderror"
                            value="{{ old('weight_grams') }}" step="0.001" min="0.001" placeholder="e.g., 40.500" required>
                        @error('weight_grams') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rate per Gram (₹) <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="rate_per_gram" id="rate_per_gram"
                            class="form-control @error('rate_per_gram') is-invalid @enderror"
                            value="{{ old('rate_per_gram') }}" step="0.01" min="0" placeholder="e.g., 6500.00" required>
                        @error('rate_per_gram') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Amount (Auto-calculated)</label>
                        <div id="total_amount_display"
                            style="padding: 0.75rem; background: rgba(16, 185, 129, 0.1); border-radius: 8px; font-weight: 700; font-size: 1.1rem; color: #10b981;">
                            ₹0.00
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
                                <input type="text" id="supplier_name" name="supplier_name"
                                    class="form-control dropdown-search-input @error('supplier_name') is-invalid @enderror"
                                    value="{{ old('supplier_name') }}" placeholder="Type to search or select..." 
                                    autocomplete="off" required style="padding-right: 35px;">
                                <span class="dropdown-arrow" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6b7280;">
                                    <i class="bi bi-chevron-down"></i>
                                </span>
                            </div>
                            <div class="dropdown-menu-custom" id="supplierDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-height: 250px; overflow-y: auto; margin-top: 4px;">
                                @forelse($suppliers as $supplier)
                                    <div class="dropdown-item-custom" 
                                        data-id="{{ $supplier->id }}"
                                        data-name="{{ $supplier->name }}"
                                        data-phone="{{ $supplier->phone }}"
                                        style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: all 0.15s;">
                                        <div style="font-weight: 500; color: #1e293b;">{{ $supplier->name }}</div>
                                        @if($supplier->phone)
                                            <div style="font-size: 0.8rem; color: #64748b;">{{ $supplier->phone }}</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="dropdown-empty" style="padding: 14px; text-align: center; color: #94a3b8;">
                                        <i class="bi bi-inbox"></i> No suppliers found
                                    </div>
                                @endforelse
                            </div>
                            <input type="hidden" id="party_id" name="party_id" value="{{ old('party_id') }}">
                        </div>
                        @if($suppliers->isEmpty())
                            <small style="color: #f59e0b; margin-top: 0.25rem; display: block;">
                                <i class="bi bi-exclamation-triangle"></i> No Gold Metal parties found. <a href="{{ route('parties.create') }}?category=gold_metal">Add a party</a>
                            </small>
                        @endif
                        @error('supplier_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Supplier Mobile</label>
                        <input type="text" id="supplier_mobile" name="supplier_mobile"
                            class="form-control @error('supplier_mobile') is-invalid @enderror"
                            value="{{ old('supplier_mobile') }}" placeholder="e.g., 9876543210">
                        @error('supplier_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number"
                            class="form-control @error('invoice_number') is-invalid @enderror"
                            value="{{ old('invoice_number') }}" placeholder="e.g., INV-2026-001">
                        @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Invoice Image Upload -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-image" style="color: #6366f1;"></i> Invoice Image
                    <small style="font-weight: 400; color: #64748b;"> (Optional)</small>
                </h3>
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

            <!-- Payment Details -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-credit-card" style="color: #6366f1;"></i> Payment Details
                    <small style="font-weight: 400; color: #64748b;"> (Leave blank for Pending)</small>
                </h3>
                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label">Payment Mode</label>
                    <div class="tracker-payment-toggle" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <label class="tracker-toggle-option" style="cursor: pointer;">
                            <input type="radio" name="payment_mode" value="cash" {{ old('payment_mode') == 'cash' ? 'checked' : '' }} onchange="toggleBankFields()" style="display: none;">
                            <span class="tracker-toggle-btn"><i class="bi bi-cash"></i> Cash</span>
                        </label>
                        <label class="tracker-toggle-option" style="cursor: pointer;">
                            <input type="radio" name="payment_mode" value="bank_transfer" {{ old('payment_mode') == 'bank_transfer' ? 'checked' : '' }} onchange="toggleBankFields()"
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
                                value="{{ old('bank_account_name') }}" placeholder="Account holder name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}"
                                placeholder="Bank name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control"
                                value="{{ old('bank_account_number') }}" placeholder="Account number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" name="bank_ifsc" class="form-control" value="{{ old('bank_ifsc') }}"
                                placeholder="IFSC code">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"
                        placeholder="Additional notes...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="tracker-form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">Cancel</a>
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-lg"></i> Save Purchase
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
            const suppliersData = @json($suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'phone' => $s->phone]));
            const supplierInput = document.getElementById('supplier_name');
            const supplierDropdown = document.getElementById('supplierDropdown');
            const partyIdField = document.getElementById('party_id');
            const mobileField = document.getElementById('supplier_mobile');
            
            // Show dropdown on focus
            supplierInput.addEventListener('focus', function() {
                supplierDropdown.style.display = 'block';
                filterDropdown('');
            });
            
            // Filter dropdown on input
            supplierInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                filterDropdown(searchTerm);
                supplierDropdown.style.display = 'block';
                
                // Check if exact match exists
                const exactMatch = suppliersData.find(s => s.name.toLowerCase() === searchTerm);
                if (!exactMatch) {
                    partyIdField.value = '';
                }
            });
            
            function filterDropdown(searchTerm) {
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
                
                // Show/hide empty message
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
                if (!e.target.closest('.custom-searchable-dropdown')) {
                    supplierDropdown.style.display = 'none';
                }
            });

            document.getElementById('weight_grams').addEventListener('input', calculateTotal);
            document.getElementById('rate_per_gram').addEventListener('input', calculateTotal);
            
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

            // Initialize on page load
            calculateTotal();
            toggleBankFields();
        </script>
    @endpush
@endsection