@extends('layouts.admin')
@section('title', 'Edit Jewellery Stock')

@section('content')
    <div class="tracker-page">
        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link"><i class="bi bi-house-door"></i>
                            Dashboard</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('jewellery-stock.index') }}" class="breadcrumb-link">Jewellery Stock</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Edit: {{ $jewelleryStock->name }}</span>
                    </div>
                    <h1 class="page-title"><i class="bi bi-pencil-square" style="color:#8b5cf6;"></i> Edit Jewellery Item
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
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
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

            {{-- ── ROW 1: Basic · Metal · Dimensions ── --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem;align-items:start;margin-bottom:1.5rem;">

                {{-- Basic Information --}}
                <div class="form-section-card">
                    <div class="section-header">
                        <div class="section-info">
                            <div class="section-icon"><i class="bi bi-gem"></i></div>
                            <div>
                                <h3 class="section-title">Basic Information</h3>
                                <p class="section-description">SKU, category &amp; item name</p>
                            </div>
                        </div>
                    </div>
                    <div class="section-body" style="display:flex;flex-direction:column;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">SKU <span class="required">*</span></label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                placeholder="e.g. JW-RING-001" value="{{ old('sku', $jewelleryStock->sku) }}" required>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category <span class="required">*</span></label>
                            <select name="type" id="categorySelect"
                                class="form-control themed-select @error('type') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach (['ring' => 'Ring', 'earrings' => 'Earrings', 'necklace' => 'Necklace', 'pendant' => 'Pendant', 'bracelet' => 'Bracelet', 'bangle' => 'Bangle', 'brooch' => 'Brooch', 'other' => 'Other'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('type', $jewelleryStock->type) == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Item Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. 18K Gold Diamond Pendant"
                                value="{{ old('name', $jewelleryStock->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Metal & Core Details --}}
                <div class="form-section-card">
                    <div class="section-header">
                        <div class="section-info">
                            <div class="section-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i
                                    class="bi bi-palette"></i></div>
                            <div>
                                <h3 class="section-title">Metal &amp; Core</h3>
                                <p class="section-description">Type, purity &amp; weight</p>
                            </div>
                        </div>
                    </div>
                    <div class="section-body" style="display:flex;flex-direction:column;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Metal Type <span class="required">*</span></label>
                            <select name="metal_type_id"
                                class="form-control themed-select @error('metal_type_id') is-invalid @enderror" required>
                                <option value="">Select Metal</option>
                                @foreach ($metalTypes as $metal)
                                    <option value="{{ $metal->id }}"
                                        {{ old('metal_type_id', $jewelleryStock->metal_type_id) == $metal->id ? 'selected' : '' }}>
                                        {{ $metal->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Net Weight (g)</label>
                            <input type="number" name="weight" class="form-control" step="0.001" placeholder="0.000"
                                value="{{ old('weight', $jewelleryStock->weight) }}">
                        </div>
                        <div class="form-group" id="closureSection">
                            <label class="form-label">Closure / Backing</label>
                            <select name="closure_type_id" class="form-control themed-select">
                                <option value="">Select Closure</option>
                                @foreach ($closureTypes as $closure)
                                    <option value="{{ $closure->id }}"
                                        {{ old('closure_type_id', $jewelleryStock->closure_type_id) == $closure->id ? 'selected' : '' }}>
                                        {{ $closure->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Physical Dimensions --}}
                <div class="form-section-card" id="dimensionsCard">
                    <div class="section-header">
                        <div class="section-info">
                            <div class="section-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);"><i
                                    class="bi bi-rulers"></i></div>
                            <div>
                                <h3 class="section-title">Dimensions</h3>
                                <p class="section-description">Physical size attributes</p>
                            </div>
                        </div>
                    </div>
                    <div class="section-body" style="display:flex;flex-direction:column;gap:1rem;">
                        <div class="form-group" id="ringSizeSection">
                            <label class="form-label">Ring Size</label>
                            <select name="ring_size_id" class="form-control themed-select">
                                <option value="">Select Size</option>
                                @foreach ($ringSizes as $size)
                                    <option value="{{ $size->id }}"
                                        {{ old('ring_size_id', $jewelleryStock->ring_size_id) == $size->id ? 'selected' : '' }}>
                                        {{ $size->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="thicknessSection" style="display: none;">
                            <label class="form-label">Thickness</label>
                            <input type="text" name="thickness" class="form-control"
                                value="{{ old('thickness', $jewelleryStock->thickness) }}" placeholder="e.g. 1.5mm">
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

            {{-- ── ROW 2: Gemstone Details (full width) ── --}}
            <div class="form-section-card" style="margin-bottom:1.5rem;">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);"><i
                                class="bi bi-diamond-half"></i></div>
                        <div>
                            <h3 class="section-title">Gemstone Details</h3>
                            <p class="section-description">Primary and side stone specifications</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">

                    <div
                        style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: 2px solid rgba(139, 92, 246, 0.1); padding-bottom: 1rem;">
                        <div>
                            <h4
                                style="margin: 0; font-size: 0.95rem; color: #6d28d9; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                                Total Carat Weight & Stone Cost</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem;">Combined weight
                                & price of primary and all secondary stones</p>
                        </div>
                        <div style="display: flex; gap: 2rem; align-items: center;">
                            <div style="text-align: center;">
                                <div style="font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total Weight</div>
                                <span id="total_carat_weight_display"
                                    style="font-size: 1.5rem; font-weight: 800; color: #6d28d9;">0.000 cts</span>
                            </div>
                            <div style="width: 1px; height: 2rem; background: rgba(139, 92, 246, 0.2);"></div>
                            <div style="text-align: center;">
                                <div style="font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total Price</div>
                                <span id="total_stone_price_display"
                                    style="font-size: 1.5rem; font-weight: 800; color: #10b981;">$0.00</span>
                            </div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;">
                        {{-- Primary Stone --}}
                        <div>
                            <p
                                style="font-size:0.8rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem;">
                                <i class="bi bi-circle-fill"
                                    style="font-size:.5rem;vertical-align:middle;margin-right:.4rem;"></i>Primary Stone
                            </p>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                                <div class="form-group">
                                    <label class="form-label">Stone Type</label>
                                    <select name="primary_stone_type_id" class="form-control themed-select">
                                        <option value="">Select Stone</option>
                                        @foreach ($stoneTypes as $stone)
                                            <option value="{{ $stone->id }}"
                                                {{ old('primary_stone_type_id', $jewelleryStock->primary_stone_type_id) == $stone->id ? 'selected' : '' }}>
                                                {{ $stone->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Carat Weight</label>
                                    <input type="number" name="primary_stone_weight" class="form-control"
                                        step="0.001" placeholder="0.000 cts"
                                        value="{{ old('primary_stone_weight', $jewelleryStock->primary_stone_weight) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Price/Ct ($)</label>
                                    <input type="number" name="primary_stone_price" class="form-control"
                                        step="0.01" placeholder="0.00"
                                        value="{{ old('primary_stone_price', $jewelleryStock->primary_stone_price) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Shape</label>
                                    <select name="primary_stone_shape_id" class="form-control themed-select">
                                        <option value="">Select Shape</option>
                                        @foreach ($stoneShapes as $shape)
                                            <option value="{{ $shape->id }}"
                                                {{ old('primary_stone_shape_id', $jewelleryStock->primary_stone_shape_id) == $shape->id ? 'selected' : '' }}>
                                                {{ $shape->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Measurement</label>
                                    <input type="text" name="primary_stone_measurement" class="form-control"
                                        placeholder="e.g. 5x3 mm"
                                        value="{{ old('primary_stone_measurement', $jewelleryStock->primary_stone_measurement) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Cut Grade</label>
                                    <select name="primary_stone_cut_id" class="form-control themed-select">
                                        <option value="">Select Cut</option>
                                        @foreach ($diamondCuts as $cut)
                                            <option value="{{ $cut->id }}"
                                                {{ old('primary_stone_cut_id', $jewelleryStock->primary_stone_cut_id) == $cut->id ? 'selected' : '' }}>
                                                {{ $cut->name }}</option>
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
                                                {{ $color->name }}</option>
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
                                                {{ $clarity->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Primary Stone Subtotal --}}
                            <div style="margin-top: 1rem; padding: 0.5rem 0; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(99, 102, 241, 0.1);">
                                <span style="font-size: 0.8rem; font-weight: 700; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.04em;"><i class="bi bi-gem" style="margin-right: 0.3rem;"></i>Primary Stone Total</span>
                                <div style="display: flex; gap: 1.5rem; align-items: center;">
                                    <span style="font-size: 0.85rem; color: #4f46e5; font-weight: 600;">Wt: <span id="primary_stone_total_weight">0.000</span> cts</span>
                                    <span style="font-size: 0.85rem; color: #10b981; font-weight: 600;">Price: $<span id="primary_stone_total_price">0.00</span></span>
                                </div>
                            </div>
                        </div>

                        {{-- Side Stones Repeater --}}
                        <div>
                            @include('jewellery-stock.partials.side-stones-repeater')
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── ROW 3: Stock & Pricing (full width) ── --}}
            @include('jewellery-stock.partials.pricing-matrix')

            {{-- ── ROW 4: Certification & Media ── --}}
            <div class="form-section-card" style="margin-top:1.5rem;margin-bottom:1.5rem;">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i
                                class="bi bi-card-checklist"></i></div>
                        <div>
                            <h3 class="section-title">Certification &amp; Media</h3>
                            <p class="section-description">Certificate details, description and images</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;">
                        <div style="display:flex;flex-direction:column;gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Certificate Details</label>
                                <div style="display:flex;gap:.5rem;margin-bottom:.5rem;">
                                    <select name="certificate_type" class="form-control themed-select"
                                        style="width:110px;flex-shrink:0;">
                                        <option value="">Type</option>
                                        @foreach (['GIA', 'IGI', 'HRD', 'Self'] as $ct)
                                            <option value="{{ $ct }}"
                                                {{ old('certificate_type', $jewelleryStock->certificate_type) == $ct ? 'selected' : '' }}>
                                                {{ $ct }}</option>
                                        @endforeach
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
                                <textarea name="description" class="form-control" rows="5" placeholder="Enter detailed specifications...">{{ old('description', $jewelleryStock->description) }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Product Images</label>

                            {{-- Existing Images --}}
                            <div id="existing_images_container"
                                style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;margin-bottom:1rem;">
                                @if ($jewelleryStock->images && count($jewelleryStock->images) > 0)
                                    @foreach ($jewelleryStock->images as $image)
                                        <div class="existing-image-item"
                                            style="position:relative;aspect-ratio:1;border:2px solid var(--border);border-radius:8px;overflow:hidden;">
                                            <img src="{{ $image['url'] }}"
                                                style="width:100%;height:100%;object-fit:cover;">
                                            <button type="button" onclick="markForRemoval('{{ $image['url'] }}', this)"
                                                style="position:absolute;top:4px;right:4px;background:#ef4444;color:white;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @elseif($jewelleryStock->image_url)
                                    <div class="existing-image-item"
                                        style="position:relative;aspect-ratio:1;border:2px solid var(--border);border-radius:8px;overflow:hidden;">
                                        <img src="{{ $jewelleryStock->image_url }}"
                                            style="width:100%;height:100%;object-fit:cover;">
                                        <button type="button"
                                            onclick="markForRemoval('{{ $jewelleryStock->image_url }}', this)"
                                            style="position:absolute;top:4px;right:4px;background:#ef4444;color:white;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <div id="removal_input_container"></div>

                            <div id="image_upload_section"
                                style="border:2px dashed var(--border);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;"
                                onclick="document.getElementById('images').click()"
                                onmouseenter="this.style.borderColor='var(--primary)';this.style.background='rgba(99,102,241,.03)'"
                                onmouseleave="this.style.borderColor='var(--border)';this.style.background=''">
                                <input type="file" name="images[]" id="images" class="d-none"
                                    accept="image/*,video/*" multiple onchange="previewUploads(this)">
                                <i class="bi bi-cloud-arrow-up" style="font-size:2rem;color:var(--primary);"></i>
                                <p style="margin:.5rem 0 0;color:var(--gray);font-weight:500;">Click to upload images or
                                    videos</p>
                                <small style="color:var(--muted);">Max 20MB · JPEG, PNG, WEBP, AVIF, HEIC, MP4, MOV</small>
                            </div>
                            <div id="image_preview_container"
                                style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;margin-top:.75rem;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="action-footer">
                <a href="{{ route('jewellery-stock.index') }}" class="btn-secondary-custom">Cancel</a>
                <button type="submit" class="btn-primary-custom" style="padding-left:2rem;padding-right:2rem;">
                    <i class="bi bi-save"></i> Update Catalogue Item
                </button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const categorySelect = document.getElementById('categorySelect');
        const dimensionsCard = document.getElementById('dimensionsCard');
        const ringSizeSection = document.getElementById('ringSizeSection');
        const thicknessSection = document.getElementById('thicknessSection');
        const lengthSection = document.getElementById('lengthSection');
        const widthSection = document.getElementById('widthSection');
        const diameterSection = document.getElementById('diameterSection');
        const baleSection = document.getElementById('baleSection');
        const closureSection = document.getElementById('closureSection');

        function updateCategoryFields() {
            const category = categorySelect.value;
            [ringSizeSection, thicknessSection, lengthSection, widthSection, diameterSection, baleSection, closureSection].forEach(el => {
                if (el) el.style.display = 'none';
            });

            switch (category) {
                case 'ring':
                    if (ringSizeSection) ringSizeSection.style.display = 'block';
                    if (thicknessSection) thicknessSection.style.display = 'block';
                    if (widthSection) widthSection.style.display = 'block';
                    break;
                case 'earrings':
                    if (closureSection) closureSection.style.display = 'block';
                    if (widthSection) widthSection.style.display = 'block';
                    if (lengthSection) lengthSection.style.display = 'block';
                    break;
                case 'necklace':
                case 'bracelet':
                    if (lengthSection) lengthSection.style.display = 'block';
                    if (widthSection) widthSection.style.display = 'block';
                    if (closureSection) closureSection.style.display = 'block';
                    break;
                case 'pendant':
                    if (widthSection) widthSection.style.display = 'block';
                    if (lengthSection) lengthSection.style.display = 'block';
                    if (baleSection) baleSection.style.display = 'block';
                    break;
                case 'bangle':
                    if (diameterSection) diameterSection.style.display = 'block';
                    if (widthSection) widthSection.style.display = 'block';
                    break;
                default:
                    if (widthSection) widthSection.style.display = 'block';
                    if (lengthSection) lengthSection.style.display = 'block';
            }
        }

        function markForRemoval(url, btn) {
            const container = document.getElementById('removal_input_container');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'removed_images[]';
            input.value = url;
            container.appendChild(input);
            const item = btn.closest('.existing-image-item');
            item.style.opacity = '0.3';
            item.style.pointerEvents = 'none';
            btn.remove();
        }

        let selectedFiles = [];

        function previewUploads(input) {
            const container = document.getElementById('image_preview_container');
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    selectedFiles.push(file);
                });
            }
            renderPreviews();
            updateInputFiles();
        }

        function renderPreviews() {
            const container = document.getElementById('image_preview_container');
            container.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                // Create placeholder div first to preserve order
                const div = document.createElement('div');
                div.style.cssText = 'position:relative;aspect-ratio:1;';
                div.dataset.index = index;
                container.appendChild(div);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const targetDiv = container.querySelector(`[data-index="${index}"]`);
                    if (!targetDiv) return;

                    let mediaHtml = '';
                    if (file.type.startsWith('video/')) {
                        mediaHtml =
                            `<video src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;border:2px solid var(--border);"></video>`;
                    } else {
                        mediaHtml =
                            `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;border:2px solid var(--border);">`;
                    }

                    targetDiv.innerHTML = `
                    ${mediaHtml}
                    <button type="button" onclick="removeSelectedFile(${index})" 
                        style="position:absolute;top:4px;right:4px;background:#ef4444;color:white;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:10;box-shadow:0 2px 4px rgba(0,0,0,0.2);">
                        <i class="bi bi-x"></i>
                    </button>
                    <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.5);color:white;font-size:10px;padding:2px 4px;border-bottom-left-radius:8px;border-bottom-right-radius:8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${file.name}
                    </div>
                `;
                };
                reader.readAsDataURL(file);
            });
        }

        function removeSelectedFile(index) {
            selectedFiles.splice(index, 1);
            renderPreviews();
            updateInputFiles();
        }

        function updateInputFiles() {
            const input = document.getElementById('images');
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            input.files = dt.files;
        }

        document.getElementById('categorySelect')?.addEventListener('change', updateCategoryFields);
        document.addEventListener('DOMContentLoaded', () => {
            updateCategoryFields();
        });

        function calculateTotalCaratWeight() {
            let totalWeight = 0;
            let totalPrice = 0;

            // Primary stone weight & price
            const primaryWeightInput = document.querySelector('input[name="primary_stone_weight"]');
            const primaryPriceInput = document.querySelector('input[name="primary_stone_price"]');
            let pW = 0, pP = 0, pTotal = 0;
            
            if (primaryWeightInput && primaryWeightInput.value) {
                pW = parseFloat(primaryWeightInput.value) || 0;
            }
            if (primaryPriceInput && primaryPriceInput.value) {
                pP = parseFloat(primaryPriceInput.value) || 0;
            }
            
            if (pW > 0) {
                totalWeight += pW;
                pTotal = pW * pP;
                totalPrice += pTotal;
            }

            // Primary stone subtotal display
            const pWtEl = document.getElementById('primary_stone_total_weight');
            const pPrEl = document.getElementById('primary_stone_total_price');
            if (pWtEl) pWtEl.innerText = pW.toFixed(3);
            if (pPrEl) pPrEl.innerText = pTotal.toFixed(2);

            // Side stones
            let sideWeight = 0, sidePriceTotal = 0;
            const sideStoneWeights = document.querySelectorAll('input[name^="side_stones"][name$="[weight]"]');
            sideStoneWeights.forEach(input => {
                const name = input.name;
                const priceName = name.replace('[weight]', '[price]');
                const pInput = document.querySelector(`input[name="${priceName}"]`);
                
                let w = 0, p = 0, rowTotal = 0;
                if (input.value) w = parseFloat(input.value) || 0;
                if (pInput && pInput.value) p = parseFloat(pInput.value) || 0;
                
                if (w > 0) {
                    totalWeight += w;
                    sideWeight += w;
                    rowTotal = w * p;
                    sidePriceTotal += rowTotal;
                    totalPrice += rowTotal;
                }
            });

            // Side stones subtotal display
            const sWtEl = document.getElementById('side_stones_total_weight');
            const sPrEl = document.getElementById('side_stones_total_price');
            if (sWtEl) sWtEl.innerText = sideWeight.toFixed(3);
            if (sPrEl) sPrEl.innerText = sidePriceTotal.toFixed(2);

            // Grand total display
            const weightDisplay = document.getElementById('total_carat_weight_display');
            if (weightDisplay) {
                weightDisplay.innerText = totalWeight.toFixed(3) + ' cts';
            }
            const priceDisplay = document.getElementById('total_stone_price_display');
            if (priceDisplay) {
                priceDisplay.innerText = '$' + totalPrice.toFixed(2);
            }

            // Auto-fill stone_cost in ALL pricing matrix rows
            const stoneCostInputs = document.querySelectorAll('.pricing-row [data-field="stone_cost"]');
            stoneCostInputs.forEach(input => {
                input.value = totalPrice.toFixed(2);
            });

            // Recalculate pricing matrix
            if (typeof calculateJewelleryPricing === 'function') {
                calculateJewelleryPricing();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.body.addEventListener('input', (e) => {
                if (e.target.name === 'primary_stone_weight' ||
                    e.target.name === 'primary_stone_price' ||
                    (e.target.name && e.target.name.startsWith('side_stones') &&
                     (e.target.name.endsWith('[weight]') || e.target.name.endsWith('[price]')))) {
                    calculateTotalCaratWeight();
                }
            });
            document.body.addEventListener('click', (e) => {
                if (e.target.closest('button[onclick^="removeSideStoneRow"]')) {
                    setTimeout(calculateTotalCaratWeight, 50);
                }
            });
            calculateTotalCaratWeight();
        });
    </script>
@endpush
