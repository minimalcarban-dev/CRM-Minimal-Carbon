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

        <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
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
                            <label for="title" class="form-label">Title</label>
                            <input type="text" id="title" name="title" class="form-control"
                                value="{{ old('title', $expense->title) }}">
                        </div>
                        <div class="form-group">
                            <label for="amount" class="form-label">Amount (₹) <span class="required">*</span></label>
                            <input type="number" id="amount" name="amount" step="0.01" class="form-control"
                                value="{{ old('amount', $expense->amount) }}" required
                                style="font-size: 1.125rem; font-weight: 600;">
                        </div>
                        <div class="form-group">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">Select Category</option>
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
                            <label for="paid_to_received_from" class="form-label">Paid To / Received From <span class="required">*</span></label>
                            <div class="d-flex align-items-start gap-2">
                                <div class="custom-searchable-dropdown flex-grow-1" id="partyDropdownContainer">
                                    <div class="dropdown-input-wrapper">
                                        <input type="text" id="paid_to_received_from" name="paid_to_received_from"
                                            class="form-control @error('paid_to_received_from') is-invalid @enderror"
                                            value="{{ old('paid_to_received_from', $expense->paid_to_received_from) }}" 
                                            placeholder="Type to search or enter manually..." autocomplete="off" required>
                                        <i class="bi bi-chevron-down dropdown-arrow"></i>
                                    </div>
                                    <div class="dropdown-menu-custom" id="partyDropdown">
                                        @if(isset($parties) && $parties->count())
                                            @foreach($parties as $party)
                                                <div class="dropdown-item-custom" data-id="{{ $party->id }}" data-name="{{ $party->name }}">
                                                    <span class="party-name">{{ $party->name }}</span>
                                                    <span class="party-category">{{ $party->category_label }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="dropdown-empty">No parties found. Type manually or add a new party.</div>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('parties.create') }}" class="btn-secondary-custom" style="white-space: nowrap; height: 42px; display: flex; align-items: center;">
                                    <i class="bi bi-plus-lg"></i> Add New
                                </a>
                            </div>
                            <input type="hidden" id="party_id" name="party_id" value="{{ old('party_id', $expense->party_id) }}">
                            @error('paid_to_received_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

            <!-- Invoice Image Upload -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon"><i class="bi bi-image"></i></div>
                        <div class="section-text">
                            <h5 class="section-title">Invoice / Receipt Image</h5>
                            <p class="section-description">Upload or update invoice/receipt image (optional)</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    @if($expense->invoice_image_url)
                        <div class="form-group">
                            <label class="form-label">Current Image</label>
                            <div class="current-image-container" style="margin-bottom: 15px;">
                                <img src="{{ $expense->invoice_image_url }}" alt="Current Invoice" 
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
                        <label for="invoice_image" class="form-label">{{ $expense->invoice_image_url ? 'Replace Image' : 'Upload Image' }}</label>
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
                <a href="{{ route('expenses.index') }}" class="btn-secondary-custom"><i class="bi bi-x-lg"></i> Cancel</a>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Update</button>
            </div>
        </form>
    </div>

<style>
    .custom-searchable-dropdown {
        position: relative;
    }
    .form-section-card {
        overflow: visible !important;
    }
    .section-body {
        overflow: visible !important;
    }
    .form-grid {
        overflow: visible !important;
    }
    .form-group {
        overflow: visible !important;
    }
    .dropdown-input-wrapper {
        position: relative;
    }
    .dropdown-input-wrapper .dropdown-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
        transition: transform 0.2s ease;
    }
    .dropdown-input-wrapper input {
        padding-right: 35px;
    }
    .dropdown-menu-custom {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-height: 250px;
        overflow-y: auto;
        z-index: 9999;
        margin-top: 4px;
    }
    .dropdown-item-custom {
        padding: 10px 15px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.15s ease;
    }
    .dropdown-item-custom:hover {
        background-color: #f8f9fa;
    }
    .dropdown-item-custom:active {
        background-color: #e9ecef;
    }
    .dropdown-item-custom .party-name {
        font-weight: 500;
        color: #333;
    }
    .dropdown-item-custom .party-category {
        font-size: 0.85em;
        color: #6c757d;
        background: #f0f0f0;
        padding: 2px 8px;
        border-radius: 4px;
    }
    .dropdown-empty {
        padding: 15px;
        text-align: center;
        color: #6c757d;
        font-style: italic;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom Searchable Dropdown for Party
    const partiesData = @json(isset($parties) ? $parties->map(fn($p) => ['id' => $p->id, 'name' => $p->name]) : []);
    const partyInput = document.getElementById('paid_to_received_from');
    const partyDropdown = document.getElementById('partyDropdown');
    const partyIdField = document.getElementById('party_id');
    const dropdownContainer = document.getElementById('partyDropdownContainer');
    
    // Filter dropdown items
    function filterPartyDropdown() {
        const searchTerm = partyInput.value.toLowerCase();
        const items = partyDropdown.querySelectorAll('.dropdown-item-custom');
        let hasVisible = false;
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = 'flex';
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
    function selectParty(id, name) {
        partyInput.value = name;
        partyIdField.value = id;
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
            selectParty(id, name);
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