@extends('layouts.admin')

@section('title', 'Add Transaction')

@section('content')
    <div class="diamond-management-container tracker-page expense-page">
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

        <form action="{{ route('expenses.store') }}" method="POST" id="expenseForm" enctype="multipart/form-data">
            @csrf

            <!-- Transaction Type -->
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-arrow-down-up" style="color: #6366f1;"></i> Transaction Type
                    <small style="font-weight: 400; color: #64748b;"> (Select if money is coming in or going out)</small>
                </h3>
                <div>
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
    <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
            <i class="bi bi-card-text" style="color: #6366f1;"></i> Transaction Details
        </h3>
        <div>
            <div class="form-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="date" class="form-label">Date <span class="required">*</span></label>
                    <input type="date" id="date" name="date" class="form-control @error('date') is-invalid @enderror"
                        value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="title" class="form-label">Title / Purpose</label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}" placeholder="e.g., Electricity Bill, Customer Payment">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="amount" class="form-label">Amount (₹) <span class="required">*</span></label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                        class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}"
                        placeholder="0.00" required style="font-size: 1.125rem; font-weight: 600;">
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control @error('category') is-invalid @enderror">
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
                    <select id="payment_method" name="payment_method"
                        class="form-control @error('payment_method') is-invalid @enderror" required>
                        @foreach($paymentMethods as $key => $label)
                            <option value="{{ $key }}" {{ old('payment_method', 'cash') == $key ? 'selected' : '' }}>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="paid_to_received_from" class="form-label">Paid To / Received From <span
                            class="required">*</span></label>
                    <div class="custom-searchable-dropdown" id="expensePartyDropdownContainer" style="position: relative;">
                        <div class="dropdown-input-wrapper" style="position: relative;">
                            <input type="text" id="paid_to_received_from" name="paid_to_received_from"
                                class="form-control dropdown-search-input @error('paid_to_received_from') is-invalid @enderror"
                                value="{{ old('paid_to_received_from') }}" placeholder="Type to search or select..."
                                autocomplete="off" required>
                            <span class="dropdown-arrow"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6b7280;">
                                <i class="bi bi-chevron-down"></i>
                            </span>
                        </div>
                        <div class="dropdown-menu-custom" id="expensePartyDropdown"
                            style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-height: 250px; overflow-y: auto; margin-top: 4px;">
                            @if(isset($parties) && $parties->count())
                                @foreach($parties as $party)
                                    <div class="dropdown-item-custom" data-id="{{ $party->id }}" data-name="{{ $party->name }}"
                                        data-category="{{ $party->category_label }}"
                                        style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: all 0.15s;">
                                        <div style="font-weight: 500; color: #1e293b;">{{ $party->name }}</div>
                                        <div style="font-size: 0.8rem; color: #64748b;">{{ $party->category_label }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="dropdown-empty" style="padding: 14px; text-align: center; color: #94a3b8;">
                                    <i class="bi bi-inbox"></i> No parties found
                                </div>
                            @endif
                        </div>
                        <input type="hidden" id="party_id" name="party_id" value="{{ old('party_id') }}">
                    </div>
                    @if(!isset($parties) || $parties->isEmpty())
                        <small class="text-muted" style="margin-top: 0.25rem; display: block;">
                            <i class="bi bi-info-circle"></i>
                            No parties found. <a href="{{ route('parties.create') }}">Add a party</a>
                        </small>
                    @endif
                    @error('paid_to_received_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="reference_number" class="form-label">Reference / Receipt No.</label>
                    <input type="text" id="reference_number" name="reference_number" class="form-control"
                        value="{{ old('reference_number') }}" placeholder="Optional">
                </div>

                <div class="form-group form-group-full">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2"
                        placeholder="Additional details...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Image Upload -->
    <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
            <i class="bi bi-image" style="color: #6366f1;"></i> Invoice / Receipt Image
            <small style="font-weight: 400; color: #64748b;"> (Optional)</small>
        </h3>
        <div>
            <div class="form-group">
                <label for="invoice_image" class="form-label">Upload Image</label>
                <input type="file" id="invoice_image" name="invoice_image"
                    class="form-control @error('invoice_image') is-invalid @enderror" accept="image/*">
                <small class="text-muted">Accepted formats: JPG, PNG, GIF. Max size: 5MB</small>
                @error('invoice_image')<div class="invalid-feedback">{{ $message }}</div>@enderror

                <div id="imagePreview" class="mt-3" style="display: none;">
                    <img id="previewImg" src="" alt="Preview"
                        style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #dee2e6;">
                </div>
            </div>
        </div>
    </div>

    <div class="tracker-form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
        <a href="{{ route('expenses.index') }}" class="btn-secondary-custom">Cancel</a>
        <button type="submit" class="btn-primary-custom">
            <i class="bi bi-check-lg"></i> Save Transaction
        </button>
    </div>
    </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

            // Custom Searchable Dropdown for Party
            const partiesData = @json(isset($parties) ? $parties->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'category' => $p->category_label]) : []);
            const paidToInput = document.getElementById('paid_to_received_from');
            const expensePartyDropdown = document.getElementById('expensePartyDropdown');
            const partyIdField = document.getElementById('party_id');

            if (paidToInput && expensePartyDropdown) {
                // Show dropdown on focus
                paidToInput.addEventListener('focus', function () {
                    expensePartyDropdown.style.display = 'block';
                    filterExpensePartyDropdown('');
                });

                // Filter dropdown on input
                paidToInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase();
                    filterExpensePartyDropdown(searchTerm);
                    expensePartyDropdown.style.display = 'block';

                    // Check if exact match exists
                    const exactMatch = partiesData.find(p => p.name.toLowerCase() === searchTerm);
                    if (!exactMatch) {
                        partyIdField.value = '';
                    }
                });

                function filterExpensePartyDropdown(searchTerm) {
                    const items = expensePartyDropdown.querySelectorAll('.dropdown-item-custom');
                    let hasVisible = false;

                    items.forEach(item => {
                        const name = item.dataset.name.toLowerCase();
                        const category = (item.dataset.category || '').toLowerCase();
                        if (name.includes(searchTerm) || category.includes(searchTerm)) {
                            item.style.display = 'block';
                            hasVisible = true;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Show/hide empty message
                    const emptyMsg = expensePartyDropdown.querySelector('.dropdown-empty');
                    if (emptyMsg) {
                        emptyMsg.style.display = hasVisible ? 'none' : 'block';
                    }
                }

                // Handle item click
                expensePartyDropdown.querySelectorAll('.dropdown-item-custom').forEach(item => {
                    item.addEventListener('click', function () {
                        paidToInput.value = this.dataset.name;
                        partyIdField.value = this.dataset.id;
                        expensePartyDropdown.style.display = 'none';
                    });

                    // Hover effect
                    item.addEventListener('mouseenter', function () {
                        this.style.background = '#f1f5f9';
                    });
                    item.addEventListener('mouseleave', function () {
                        this.style.background = '#fff';
                    });
                });

                // Close dropdown on outside click
                document.addEventListener('click', function (e) {
                    if (!e.target.closest('#expensePartyDropdownContainer')) {
                        expensePartyDropdown.style.display = 'none';
                    }
                });
            }

            // Image preview
            const imageInput = document.getElementById('invoice_image');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');

            if (imageInput) {
                imageInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
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