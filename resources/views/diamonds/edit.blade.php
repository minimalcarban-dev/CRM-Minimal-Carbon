@extends('layouts.admin')

@section('title', 'Edit Diamond')

@section('content')
    <div class="diamond-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('diamond.index') }}" class="breadcrumb-link">
                            Diamonds
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Edit Diamond #{{ $diamond->lot_no }}</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-pencil-square"></i>
                        Edit Diamond
                    </h1>
                    <p class="page-subtitle">Update the diamond information below (SKU: {{ $diamond->sku }})</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('diamond.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('diamond.update', $diamond) }}" method="POST" id="diamondForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Basic Identification -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-upc-scan"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Basic Identification</h5>
                            <p class="section-description">Essential identifiers and diamond type</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="lot_no" class="form-label">
                                Lot No <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="lot_no" name="lot_no"
                                value="{{ old('lot_no', $diamond->lot_no) }}" placeholder="e.g., L0010078" required>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Unique lot number for the diamond
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="sku" class="form-label">
                                SKU No <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="sku" name="sku"
                                value="{{ old('sku', $diamond->sku) }}" required>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Unique SKU (will be used in barcode)
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="diamond_type" class="form-label">Diamond Type</label>
                            <input type="text" class="form-control" id="diamond_type" name="diamond_type"
                                value="{{ old('diamond_type', $diamond->diamond_type) }}"
                                placeholder="e.g., Natural, Lab-created">
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Type of diamond (Natural/Lab-created)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Diamond Specifications -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Diamond Specifications</h5>
                            <p class="section-description">The 4 C's and physical characteristics</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="shape" class="form-label">Diamond Shape</label>
                            <div class="input-with-icon">
                                <i class="bi bi-diamond input-icon"></i>
                                <select id="shape" name="shape" class="form-control themed-select">
                                    <option value="">-- Select Stone Shape --</option>
                                    @foreach(($stoneShapes ?? []) as $sshape)
                                        <option value="{{ $sshape->name }}" {{ old('shape', $diamond->shape) == $sshape->name ? 'selected' : '' }}>
                                            {{ $sshape->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Shapes from stone shape list
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cut" class="form-label">Diamond Cut</label>
                            <div class="input-with-icon">
                                <i class="bi bi-scissors input-icon"></i>
                                <select id="cut" name="cut" class="form-control themed-select">
                                    <option value="">-- Select Diamond Cut --</option>
                                    @foreach(($diamondCuts ?? []) as $dcut)
                                        <option value="{{ $dcut->name }}" {{ old('cut', $diamond->cut) == $dcut->name ? 'selected' : '' }}>
                                            {{ $dcut->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Cuts from diamond cut list
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="color" class="form-label">Diamond Color</label>
                            <select id="color" name="color" class="form-control themed-select">
                                <option value="">-- Select Stone Color --</option>
                                @foreach(($stoneColors ?? []) as $scolor)
                                    <option value="{{ $scolor->name }}" {{ old('color', $diamond->color) == $scolor->name ? 'selected' : '' }}>
                                        {{ $scolor->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Colors from stone color list
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="clarity" class="form-label">Diamond Clarity</label>
                            <select id="clarity" name="clarity" class="form-control themed-select">
                                <option value="">-- Select Diamond Clarity --</option>
                                @foreach(($diamondClarities ?? []) as $dclar)
                                    <option value="{{ $dclar->name }}" {{ old('clarity', $diamond->clarity) == $dclar->name ? 'selected' : '' }}>
                                        {{ $dclar->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Clarities from diamond clarity list
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="measurement" class="form-label">Diamond Measurement</label>
                            <div class="input-with-icon">
                                <i class="bi bi-rulers input-icon"></i>
                                <input type="text" class="form-control" id="measurement" name="measurement"
                                    value="{{ old('measurement', $diamond->measurement) }}"
                                    placeholder="e.g., 6.5 x 6.3 x 4.1 mm">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Dimensions in millimeters
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Weight -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Pricing & Weight</h5>
                            <p class="section-description">Weight, per carat pricing, and cost information</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="per_ct" class="form-label">Price Per Ct</label>
                            <div class="input-with-icon">
                                <i class="bi bi-currency-dollar input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="per_ct" name="per_ct"
                                    value="{{ old('per_ct', $diamond->per_ct) }}">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Price per carat
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="weight" class="form-label">Diamond Weight</label>
                            <div class="input-with-icon">
                                <i class="bi bi-gem input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="weight" name="weight"
                                    value="{{ old('weight', $diamond->weight) }}" placeholder="Carats">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Weight in carats
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="purchase_price" class="form-label">Purchase Price</label>
                            <div class="input-with-icon">
                                <i class="bi bi-currency-dollar input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="purchase_price"
                                    name="purchase_price" value="{{ old('purchase_price', $diamond->purchase_price) }}"
                                    readonly>
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Auto-calculated: Price per Ct × Weight
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="margin" class="form-label">
                                Margin <span class="required">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-percent input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="margin" name="margin"
                                    value="{{ old('margin', $diamond->margin) }}" required placeholder="Margin percentage">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Profit margin percentage
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="listing_price" class="form-label">Listing Price</label>
                            <div class="input-with-icon">
                                <i class="bi bi-tag input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="listing_price"
                                    name="listing_price" value="{{ old('listing_price', $diamond->listing_price) }}">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Final selling price
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="offer_calculation" class="form-label">Offer Calculation (%)</label>
                            <div class="input-with-icon">
                                <i class="bi bi-percent input-icon"></i>
                                <input type="number" step="0.01" min="0" max="100" class="form-control"
                                    id="offer_calculation" name="offer_calculation"
                                    value="{{ old('offer_calculation', $diamond->offer_calculation) }}"
                                    placeholder="Discount %">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Discount percentage (0-100%)
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="actual_listing_price" class="form-label">Actual Listing Price</label>
                            <div class="input-with-icon">
                                <i class="bi bi-cash input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="actual_listing_price"
                                    name="actual_listing_price"
                                    value="{{ old('actual_listing_price', $diamond->actual_listing_price) }}" readonly>
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Auto-calculated: Listing Price - (Listing Price × Offer %)
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="shipping_price" class="form-label">Shipping Price</label>
                            <div class="input-with-icon">
                                <i class="bi bi-truck input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="shipping_price"
                                    name="shipping_price" value="{{ old('shipping_price', $diamond->shipping_price) }}">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Shipping/delivery cost
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lifecycle & Status -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Lifecycle & Status</h5>
                            <p class="section-description">Track dates, status, and financial performance</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                                value="{{ old('purchase_date', $diamond->purchase_date_formatted) }}">
                        </div>

                        <div class="form-group">
                            <label for="sold_out_date" class="form-label">Sold Out Date</label>
                            <input type="date" class="form-control" id="sold_out_date" name="sold_out_date"
                                value="{{ old('sold_out_date', $diamond->sold_out_date_formatted) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <div class="form-value"
                                style="padding: 8px 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                                @if($diamond->sold_out_date)
                                    <span class="status-pill status-sold">Sold Out</span>
                                @else
                                    <span class="status-pill status-instock">In Stock</span>
                                @endif
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Auto-determined from Sold Out Date
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="duration_days" class="form-label">Duration Days</label>
                            <input type="number" class="form-control" id="duration_days" name="duration_days"
                                value="{{ old('duration_days', $diamond->duration_days) }}" readonly>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Days between purchase and sold out
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="duration_price" class="form-label">Duration Price</label>
                            <input type="number" step="0.01" class="form-control" id="duration_price" name="duration_price"
                                value="{{ old('duration_price', $diamond->duration_price) }}" readonly>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                purchase_price × (1 + 0.0005)^days
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="sold_out_price" class="form-label">Sold Out Price</label>
                            <input type="number" step="0.01" class="form-control" id="sold_out_price" name="sold_out_price"
                                value="{{ old('sold_out_price', $diamond->sold_out_price) }}">
                        </div>

                        <div class="form-group">
                            <label for="profit" class="form-label">Profit</label>
                            <input type="number" step="0.01" class="form-control" id="profit" name="profit"
                                value="{{ old('profit', $diamond->profit) }}" readonly>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Sold out price - Purchase price - Shipping price
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Sold Out Month</label>
                            <input type="text" class="form-control" id="sold_out_month" name="sold_out_month"
                                value="{{ old('sold_out_month', $diamond->sold_out_month) }}" readonly
                                placeholder="YYYY-MM">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Additional Details</h5>
                            <p class="section-description">Images, description, notes, and admin assignment</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="multi_img_upload" class="form-label">Upload Additional Images</label>
                            <input type="file" class="form-control" id="multi_img_upload" name="multi_img_upload[]" multiple
                                accept="image/*">
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Upload multiple diamond images (JPEG, PNG, GIF)
                            </div>
                            @if($diamond->multi_img_upload && is_array($diamond->multi_img_upload))
                                <div style="margin-top: 12px;">
                                    <p class="text-semibold" style="margin-bottom: 8px;">Existing Images:</p>
                                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                        @foreach($diamond->multi_img_upload as $img)
                                            <img src="{{ $img }}" alt="Diamond image"
                                                style="max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="form-group full-width">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="3">{{ old('description', $diamond->description) }}</textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="note" class="form-label">Note</label>
                            <textarea class="form-control" id="note" name="note"
                                rows="3">{{ old('note', $diamond->note) }}</textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="admin_id" class="form-label">Assign To Admin</label>
                            <select class="form-control" id="admin_id" name="admin_id">
                                <option value="">-- Select Admin --</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ old('admin_id', $diamond->admin_id) == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Select one admin to assign this diamond
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Footer -->
            <div class="action-footer">
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-circle"></i>
                    <span>Update Diamond</span>
                </button>
                <button type="button" class="btn-secondary-custom" onclick="window.history.back()">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancel</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const purchaseDateEl = document.getElementById('purchase_date');
                const durationDaysEl = document.getElementById('duration_days');
                const durationPriceEl = document.getElementById('duration_price');
                const purchasePriceEl = document.getElementById('purchase_price');
                const soldOutDateEl = document.getElementById('sold_out_date');
                const soldOutMonthEl = document.getElementById('sold_out_month');
                const soldOutPriceEl = document.getElementById('sold_out_price');
                const shippingPriceEl = document.getElementById('shipping_price');
                const profitEl = document.getElementById('profit');
                const perCtEl = document.getElementById('per_ct');
                const weightEl = document.getElementById('weight');

                const DAILY_RATE = 0.0005; // 0.05% per day

                // --- PURCHASE PRICE CALCULATION ---
                // Formula: per_ct × weight
                function computePurchasePrice() {
                    const perCt = parseFloat(perCtEl.value || '0');
                    const weight = parseFloat(weightEl.value || '0');
                    if (perCt > 0 && weight > 0) {
                        purchasePriceEl.value = (perCt * weight).toFixed(2);
                    }
                    // After updating purchase price, recalculate all dependent values
                    computeDerived();
                }

                function computeDerived() {
                    const today = new Date();
                    const purchaseDateVal = purchaseDateEl.value;
                    const endDate = soldOutDateEl.value ? new Date(soldOutDateEl.value) : today;

                    if (purchaseDateVal) {
                        const pd = new Date(purchaseDateVal);
                        const ms = endDate - pd;
                        const days = Math.max(0, Math.floor(ms / (1000 * 60 * 60 * 24)));
                        durationDaysEl.value = days;
                        const base = parseFloat(purchasePriceEl.value || '0');
                        // Formula: purchase_price × (1 + 0.0005)^days (0.05% daily)
                        const durationPrice = base * Math.pow(1 + DAILY_RATE, days);
                        durationPriceEl.value = durationPrice.toFixed(2);
                    } else {
                        durationDaysEl.value = 0;
                        durationPriceEl.value = 0;
                    }

                    // --- SOLD OUT MONTH ---
                    if (soldOutDateEl.value) {
                        const d = new Date(soldOutDateEl.value);
                        soldOutMonthEl.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
                    } else {
                        soldOutMonthEl.value = '';
                    }

                    // --- PROFIT CALC ---
                    const pp = parseFloat(purchasePriceEl.value || '0');
                    const sop = parseFloat(soldOutPriceEl.value || '');
                    const sp = parseFloat(shippingPriceEl.value || '0');
                    if (!isNaN(sop) && pp > 0) {
                        profitEl.value = (sop - pp - sp).toFixed(2);
                    } else {
                        profitEl.value = '';
                    }

                    // --- ACTUAL LISTING PRICE CALC ---
                    computeActualListingPrice();
                }

                // --- ACTUAL LISTING PRICE CALCULATION ---
                // Formula: listing_price - (listing_price × offer_calculation / 100)
                function computeActualListingPrice() {
                    const listingPriceEl = document.getElementById('listing_price');
                    const offerCalculationEl = document.getElementById('offer_calculation');
                    const actualListingPriceEl = document.getElementById('actual_listing_price');

                    const listingPrice = parseFloat(listingPriceEl?.value || '0');
                    const offerCalculation = parseFloat(offerCalculationEl?.value || '0');

                    if (listingPrice > 0) {
                        const discount = listingPrice * (offerCalculation / 100);
                        actualListingPriceEl.value = (listingPrice - discount).toFixed(2);
                    } else {
                        actualListingPriceEl.value = '';
                    }
                }

                // Listen for per_ct and weight changes to auto-calculate purchase_price
                ['input', 'change'].forEach(evt => {
                    [perCtEl, weightEl].forEach(el => el && el.addEventListener(evt, computePurchasePrice));
                });

                // Listen for other fields that affect derived calculations
                ['input', 'change'].forEach(evt => {
                    [purchaseDateEl, soldOutDateEl, purchasePriceEl, soldOutPriceEl, shippingPriceEl].forEach(el => el && el.addEventListener(evt, computeDerived));
                });

                // Listen for listing_price and offer_calculation changes
                ['input', 'change'].forEach(evt => {
                    const listingPriceEl = document.getElementById('listing_price');
                    const offerCalculationEl = document.getElementById('offer_calculation');
                    [listingPriceEl, offerCalculationEl].forEach(el => el && el.addEventListener(evt, computeActualListingPrice));
                });

                computeDerived();
                computeActualListingPrice();
            });
        </script>
    @endpush
@endsection