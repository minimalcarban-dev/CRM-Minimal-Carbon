@extends('layouts.admin')
@section('title', 'Edit Jewellery Stock')

@section('content')
    <div class="tracker-page">
        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('jewellery-stock.index') }}" class="breadcrumb-link">Jewellery Stock</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Edit: {{ $jewelleryStock->name }}</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-pencil-square" style="color: #8b5cf6;"></i>
                        Edit Jewellery Item
                    </h1>
                    <p class="page-subtitle">Update the details for <strong>{{ $jewelleryStock->sku }}</strong></p>
                </div>
                <div class="header-right">
                    <a href="{{ route('jewellery-stock.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('jewellery-stock.update', $jewelleryStock) }}" method="POST" enctype="multipart/form-data"
            id="jewelleryForm">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
                {{-- Left Column: Basics & Metal --}}
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    {{-- Basic Information --}}
                    <div class="tracker-table-card" style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-gem" style="color: #8b5cf6;"></i> Basic Information
                        </h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div class="form-group">
                                <label class="form-label">SKU <span style="color: #ef4444;">*</span></label>
                                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                    placeholder="e.g. JW-RING-001" value="{{ old('sku', $jewelleryStock->sku) }}" required>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Category <span style="color: #ef4444;">*</span></label>
                                <select name="type" id="categorySelect"
                                    class="form-control themed-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    <option value="ring"
                                        {{ old('type', $jewelleryStock->type) == 'ring' ? 'selected' : '' }}>Ring</option>
                                    <option value="earrings"
                                        {{ old('type', $jewelleryStock->type) == 'earrings' ? 'selected' : '' }}>Earrings
                                    </option>
                                    <option value="necklace"
                                        {{ old('type', $jewelleryStock->type) == 'necklace' ? 'selected' : '' }}>Necklace
                                    </option>
                                    <option value="pendant"
                                        {{ old('type', $jewelleryStock->type) == 'pendant' ? 'selected' : '' }}>Pendant
                                    </option>
                                    <option value="bracelet"
                                        {{ old('type', $jewelleryStock->type) == 'bracelet' ? 'selected' : '' }}>Bracelet
                                    </option>
                                    <option value="bangle"
                                        {{ old('type', $jewelleryStock->type) == 'bangle' ? 'selected' : '' }}>Bangle
                                    </option>
                                    <option value="brooch"
                                        {{ old('type', $jewelleryStock->type) == 'brooch' ? 'selected' : '' }}>Brooch
                                    </option>
                                    <option value="other"
                                        {{ old('type', $jewelleryStock->type) == 'other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label">Item Name <span style="color: #ef4444;">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="e.g. 18K Gold Diamond Pendant"
                                    value="{{ old('name', $jewelleryStock->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Metal Details --}}
                    <div class="tracker-table-card" style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-palette" style="color: #6366f1;"></i> Metal & Core Details
                        </h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div class="form-group">
                                <label class="form-label">Metal Type <span style="color: #ef4444;">*</span></label>
                                <select name="metal_type_id"
                                    class="form-control themed-select @error('metal_type_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Metal</option>
                                    @foreach ($metalTypes as $metal)
                                        <option value="{{ $metal->id }}"
                                            {{ old('metal_type_id', $jewelleryStock->metal_type_id) == $metal->id ? 'selected' : '' }}>
                                            {{ $metal->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Metal Purity</label>
                                <input type="text" name="metal_purity" class="form-control"
                                    placeholder="e.g. 18K, 14K, 950 Plat"
                                    value="{{ old('metal_purity', $jewelleryStock->metal_purity) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Gross Weight (g)</label>
                                <input type="number" name="weight" class="form-control" step="0.001"
                                    placeholder="0.000" value="{{ old('weight', $jewelleryStock->weight) }}">
                            </div>
                            <div class="form-group" id="closureSection">
                                <label class="form-label">Closure/Backing</label>
                                <select name="closure_type_id" class="form-control themed-select">
                                    <option value="">Select Closure</option>
                                    @foreach ($closureTypes as $closure)
                                        <option value="{{ $closure->id }}"
                                            {{ old('closure_type_id', $jewelleryStock->closure_type_id) == $closure->id ? 'selected' : '' }}>
                                            {{ $closure->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Physical Dimensions (Dynamic) --}}
                    <div class="tracker-table-card" style="padding: 1.5rem;" id="dimensionsCard">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-rulers" style="color: #6366f1;"></i> Physical Dimensions
                        </h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div class="form-group" id="ringSizeSection">
                                <label class="form-label">Ring Size</label>
                                <select name="ring_size_id" class="form-control themed-select">
                                    <option value="">Select Size</option>
                                    @foreach ($ringSizes as $size)
                                        <option value="{{ $size->id }}"
                                            {{ old('ring_size_id', $jewelleryStock->ring_size_id) == $size->id ? 'selected' : '' }}>
                                            {{ $size->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" id="lengthSection">
                                <label class="form-label">Length (inch/cm)</label>
                                <input type="number" name="length" class="form-control" step="0.1"
                                    value="{{ old('length', $jewelleryStock->length) }}" placeholder="0.0">
                            </div>
                            <div class="form-group" id="widthSection">
                                <label class="form-label">Width (mm)</label>
                                <input type="number" name="width" class="form-control" step="0.1"
                                    value="{{ old('width', $jewelleryStock->width) }}" placeholder="0.0">
                            </div>
                            <div class="form-group" id="diameterSection">
                                <label class="form-label">Diameter (mm)</label>
                                <input type="number" name="diameter" class="form-control" step="0.1"
                                    value="{{ old('diameter', $jewelleryStock->diameter) }}" placeholder="0.0">
                            </div>
                            <div class="form-group" id="baleSection">
                                <label class="form-label">Bale Size (mm)</label>
                                <input type="number" name="bale_size" class="form-control" step="0.1"
                                    value="{{ old('bale_size', $jewelleryStock->bale_size) }}" placeholder="0.0">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Stones & Pricing --}}
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    {{-- Stone Details --}}
                    <div class="tracker-table-card" style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-diamond-half" style="color: #6366f1;"></i> Gemstone Details
                        </h3>

                        <div
                            style="background: rgba(99, 102, 241, 0.05); border-radius: 12px; padding: 1rem; margin-bottom: 1rem;">
                            <h4 style="font-size: 0.9rem; color: #4f46e5; margin: 0 0 1rem;">Primary Stone</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Stone Type</label>
                                    <select name="primary_stone_type_id" class="form-control themed-select">
                                        <option value="">Select Stone</option>
                                        @foreach ($stoneTypes as $stone)
                                            <option value="{{ $stone->id }}"
                                                {{ old('primary_stone_type_id', $jewelleryStock->primary_stone_type_id) == $stone->id ? 'selected' : '' }}>
                                                {{ $stone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Main Stone Carat Weight</label>
                                    <input type="number" name="primary_stone_weight" id="primaryStoneWeight"
                                        class="form-control"
                                        step="0.001" placeholder="0.000 cts"
                                        value="{{ old('primary_stone_weight', $jewelleryStock->primary_stone_weight) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Shape</label>
                                    <select name="primary_stone_shape_id" class="form-control themed-select">
                                        <option value="">Select Shape</option>
                                        @foreach ($stoneShapes as $shape)
                                            <option value="{{ $shape->id }}"
                                                {{ old('primary_stone_shape_id', $jewelleryStock->primary_stone_shape_id) == $shape->id ? 'selected' : '' }}>
                                                {{ $shape->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Cut Grade</label>
                                    <select name="primary_stone_cut_id" class="form-control themed-select">
                                        <option value="">Select Cut</option>
                                        @foreach ($diamondCuts as $cut)
                                            <option value="{{ $cut->id }}"
                                                {{ old('primary_stone_cut_id', $jewelleryStock->primary_stone_cut_id) == $cut->id ? 'selected' : '' }}>
                                                {{ $cut->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Color</label>
                                    <select name="primary_stone_color_id" class="form-control themed-select">
                                        <option value="">Select Color</option>
                                        @foreach ($stoneColors as $color)
                                            <option value="{{ $color->id }}"
                                                {{ old('primary_stone_color_id', $jewelleryStock->primary_stone_color_id) == $color->id ? 'selected' : '' }}>
                                                {{ $color->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Clarity</label>
                                    <select name="primary_stone_clarity_id" class="form-control themed-select">
                                        <option value="">Select Clarity</option>
                                        @foreach ($diamondClarities as $clarity)
                                            <option value="{{ $clarity->id }}"
                                                {{ old('primary_stone_clarity_id', $jewelleryStock->primary_stone_clarity_id) == $clarity->id ? 'selected' : '' }}>
                                                {{ $clarity->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div style="background: rgba(99, 102, 241, 0.05); border-radius: 12px; padding: 1rem;">
                            <h4 style="font-size: 0.9rem; color: #4f46e5; margin: 0 0 1rem;">Side Stones</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Stone Type</label>
                                    <select name="side_stone_type_id" class="form-control themed-select">
                                        <option value="">Select Type</option>
                                        @foreach ($stoneTypes as $stone)
                                            <option value="{{ $stone->id }}"
                                                {{ old('side_stone_type_id', $jewelleryStock->side_stone_type_id) == $stone->id ? 'selected' : '' }}>
                                                {{ $stone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group" style="display: flex; gap: 0.5rem;">
                                    <div style="flex: 1;">
                                        <label class="form-label">Side Stone Carat Weight</label>
                                        <input type="number" name="side_stone_weight" id="sideStoneWeight"
                                            class="form-control" step="0.001"
                                            value="{{ old('side_stone_weight', $jewelleryStock->side_stone_weight) }}">
                                    </div>
                                    <div style="flex: 1;">
                                        <label class="form-label">Total Carat Weight</label>
                                        <input type="number" name="total_stone_weight" id="totalStoneWeight"
                                            class="form-control" step="0.001"
                                            value="{{ old('total_stone_weight', $jewelleryStock->total_stone_weight) }}"
                                            placeholder="Auto-calculated" readonly>
                                    </div>
                                    <div style="width: 70px;">
                                        <label class="form-label">Count</label>
                                        <input type="number" name="side_stone_count" class="form-control"
                                            value="{{ old('side_stone_count', $jewelleryStock->side_stone_count) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Stock & Pricing --}}
                    <div class="tracker-table-card" style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-cash-stack" style="color: #10b981;"></i> Stock & Pricing
                        </h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div class="form-group">
                                <label class="form-label">Current Quantity <span style="color: #ef4444;">*</span></label>
                                <input type="number" name="quantity" class="form-control"
                                    value="{{ old('quantity', $jewelleryStock->quantity) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Stock Threshold</label>
                                <input type="number" name="low_stock_threshold" class="form-control"
                                    value="{{ old('low_stock_threshold', $jewelleryStock->low_stock_threshold) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Purchase Price ($)</label>
                                <input type="number" name="purchase_price" class="form-control" step="0.01"
                                    value="{{ old('purchase_price', $jewelleryStock->purchase_price) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Selling Price ($)</label>
                                <input type="number" name="selling_price" class="form-control" step="0.01"
                                    value="{{ old('selling_price', $jewelleryStock->selling_price) }}">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <div id="margin_display"
                                    style="padding: 0.75rem; background: rgba(16, 185, 129, 0.1); border-radius: 8px; font-weight: 700; text-align: center; color: #10b981;">
                                    Margin: $0.00 (0%)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Additional & Image --}}
            <div class="tracker-table-card" style="padding: 1.5rem; margin-top: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-card-checklist" style="color: #6366f1;"></i> Certification & Media
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label">Certificate Details</label>
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <select name="certificate_type" class="form-control themed-select" style="width: 100px;">
                                    <option value="">Type</option>
                                    <option value="GIA"
                                        {{ old('certificate_type', $jewelleryStock->certificate_type) == 'GIA' ? 'selected' : '' }}>
                                        GIA</option>
                                    <option value="IGI"
                                        {{ old('certificate_type', $jewelleryStock->certificate_type) == 'IGI' ? 'selected' : '' }}>
                                        IGI</option>
                                    <option value="HRD"
                                        {{ old('certificate_type', $jewelleryStock->certificate_type) == 'HRD' ? 'selected' : '' }}>
                                        HRD</option>
                                    <option value="Self"
                                        {{ old('certificate_type', $jewelleryStock->certificate_type) == 'Self' ? 'selected' : '' }}>
                                        Self</option>
                                </select>
                                <input type="text" name="certificate_number" class="form-control"
                                    placeholder="Certificate Number"
                                    value="{{ old('certificate_number', $jewelleryStock->certificate_number) }}">
                            </div>
                            <input type="url" name="certificate_url" class="form-control"
                                placeholder="Certificate URL Link"
                                value="{{ old('certificate_url', $jewelleryStock->certificate_url) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Item Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter detailed specifications...">{{ old('description', $jewelleryStock->description) }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Images</label>
                        
                        {{-- Existing Images Gallery --}}
                        <div id="existing_images_container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-bottom: 1.5rem;">
                            @if($jewelleryStock->images && count($jewelleryStock->images) > 0)
                                @foreach($jewelleryStock->images as $image)
                                    <div class="existing-image-item" style="position: relative; aspect-ratio: 1; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                        <img src="{{ $image['url'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        <button type="button" 
                                            onclick="markForRemoval('{{ $image['url'] }}', this)" 
                                            style="position: absolute; top: 4px; right: 4px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; font-size: 14px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid white;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @elseif($jewelleryStock->image_url)
                                {{-- Fallback for legacy items with only image_url --}}
                                <div class="existing-image-item" style="position: relative; aspect-ratio: 1; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                    <img src="{{ $jewelleryStock->image_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    <button type="button" 
                                        onclick="markForRemoval('{{ $jewelleryStock->image_url }}', this)" 
                                        style="position: absolute; top: 4px; right: 4px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; font-size: 14px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid white;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div id="removal_input_container"></div>

                        <div id="image_upload_section"
                            style="border: 2px dashed #e2e8f0; border-radius: 12px; padding: 2rem; text-align: center; cursor: pointer;"
                            onclick="document.getElementById('images').click()">
                            <input type="file" name="images[]" id="images" class="d-none" accept="image/*"
                                multiple onchange="previewUploads(this)">
                            <i class="bi bi-images" style="font-size: 2rem; color: #6366f1;"></i>
                            <p style="margin: 0.5rem 0 0; color: #64748b;">Click to upload more images</p>
                            <small style="color: #94a3b8;">Max: 10MB per image (JPEG, PNG, WEBP, AVIF, HEIC)</small>
                        </div>
                        <div id="image_preview_container"
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 1rem;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="tracker-form-actions"
                style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('jewellery-stock.index') }}" class="btn-secondary-custom">Cancel</a>
                <button type="submit" class="btn-primary-custom" style="padding-left: 2rem; padding-right: 2rem;">
                    <i class="bi bi-save"></i> Update Catalogue Item
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function updateCategoryFields() {
            const category = document.getElementById('categorySelect').value;
            const ringSize = document.getElementById('ringSizeSection');
            const length = document.getElementById('lengthSection');
            const width = document.getElementById('widthSection');
            const diameter = document.getElementById('diameterSection');
            const bale = document.getElementById('baleSection');
            const closure = document.getElementById('closureSection');

            // Reset all
            [ringSize, length, width, diameter, bale, closure].forEach(el => {
                if (el) el.style.display = 'none';
            });

            switch (category) {
                case 'ring':
                    if (ringSize) ringSize.style.display = 'block';
                    if (width) width.style.display = 'block';
                    break;
                case 'earrings':
                    if (closure) closure.style.display = 'block';
                    if (width) width.style.display = 'block';
                    if (length) length.style.display = 'block';
                    break;
                case 'necklace':
                case 'bracelet':
                    if (length) length.style.display = 'block';
                    if (width) width.style.display = 'block';
                    if (closure) closure.style.display = 'block';
                    break;
                case 'pendant':
                    if (width) width.style.display = 'block';
                    if (length) length.style.display = 'block';
                    if (bale) bale.style.display = 'block';
                    break;
                case 'bangle':
                    if (diameter) diameter.style.display = 'block';
                    if (width) width.style.display = 'block';
                    break;
                default:
                    if (width) width.style.display = 'block';
                    if (length) length.style.display = 'block';
            }
        }

        function calculateMargin() {
            const purchase = parseFloat(document.querySelector('input[name="purchase_price"]').value) || 0;
            const selling = parseFloat(document.querySelector('input[name="selling_price"]').value) || 0;
            const display = document.getElementById('margin_display');

            if (selling > 0) {
                const margin = selling - purchase;
                const percentage = (margin / selling) * 100;
                display.innerText = `Margin: $${margin.toFixed(2)} (${percentage.toFixed(1)}%)`;
                display.style.background = margin >= 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
                display.style.color = margin >= 0 ? '#10b981' : '#ef4444';
            } else {
                display.innerText = 'Margin: $0.00 (0%)';
                display.style.background = 'rgba(16, 185, 129, 0.1)';
                display.style.color = '#10b981';
            }
        }

        function calculateTotalStoneWeight() {
            const primaryInput = document.getElementById('primaryStoneWeight');
            const sideInput = document.getElementById('sideStoneWeight');
            const totalInput = document.getElementById('totalStoneWeight');

            if (!totalInput) return;

            const primaryRaw = primaryInput?.value?.trim() ?? '';
            const sideRaw = sideInput?.value?.trim() ?? '';
            const hasWeightValue = primaryRaw !== '' || sideRaw !== '';

            if (!hasWeightValue) {
                totalInput.value = '';
                return;
            }

            const primary = parseFloat(primaryRaw) || 0;
            const side = parseFloat(sideRaw) || 0;
            totalInput.value = (primary + side).toFixed(3);
        }

        function markForRemoval(url, btn) {
            const container = document.getElementById('removal_input_container');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'removed_images[]';
            input.value = url;
            container.appendChild(input);
            
            // Visual feedback
            const item = btn.closest('.existing-image-item');
            item.style.opacity = '0.3';
            item.style.pointerEvents = 'none';
            btn.remove();
        }

        function previewUploads(input) {
            const container = document.getElementById('image_preview_container');
            container.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.style.position = 'relative';
                        div.style.aspectRatio = '1';
                        div.innerHTML = `
                            <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                        `;
                        container.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }

        document.getElementById('categorySelect')?.addEventListener('change', updateCategoryFields);
        document.querySelector('input[name="purchase_price"]')?.addEventListener('input', calculateMargin);
        document.querySelector('input[name="selling_price"]')?.addEventListener('input', calculateMargin);
        document.getElementById('primaryStoneWeight')?.addEventListener('input', calculateTotalStoneWeight);
        document.getElementById('sideStoneWeight')?.addEventListener('input', calculateTotalStoneWeight);

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            updateCategoryFields();
            calculateMargin();
            calculateTotalStoneWeight();
        });
    </script>
@endpush
