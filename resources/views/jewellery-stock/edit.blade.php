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

        <form action="{{ route('jewellery-stock.update', $jewelleryStock) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Basic Information --}}
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-gem" style="color: #8b5cf6;"></i> Basic Information
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">SKU <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                            placeholder="e.g. JW-RING-001" value="{{ old('sku', $jewelleryStock->sku) }}" required>
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small style="color: #64748b; margin-top: 0.25rem; display: block;">Unique stock-keeping unit identifier</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type <span style="color: #ef4444;">*</span></label>
                        <select name="type" class="form-control themed-select @error('type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="ring" {{ old('type', $jewelleryStock->type) == 'ring' ? 'selected' : '' }}>Ring</option>
                            <option value="earrings" {{ old('type', $jewelleryStock->type) == 'earrings' ? 'selected' : '' }}>Earrings</option>
                            <option value="tennis_bracelet" {{ old('type', $jewelleryStock->type) == 'tennis_bracelet' ? 'selected' : '' }}>Tennis Bracelet</option>
                            <option value="other" {{ old('type', $jewelleryStock->type) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. 18K Gold Solitaire Ring" value="{{ old('name', $jewelleryStock->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-palette" style="color: #6366f1;"></i> Details
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Metal Type <span style="color: #ef4444;">*</span></label>
                        <select name="metal_type_id" class="form-control themed-select @error('metal_type_id') is-invalid @enderror" required>
                            <option value="">Select Metal Type</option>
                            @foreach ($metalTypes as $metal)
                                <option value="{{ $metal->id }}" {{ old('metal_type_id', $jewelleryStock->metal_type_id) == $metal->id ? 'selected' : '' }}>
                                    {{ $metal->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('metal_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ring Size</label>
                        <select name="ring_size_id" class="form-control themed-select @error('ring_size_id') is-invalid @enderror">
                            <option value="">Select Ring Size (optional)</option>
                            @foreach ($ringSizes as $size)
                                <option value="{{ $size->id }}" {{ old('ring_size_id', $jewelleryStock->ring_size_id) == $size->id ? 'selected' : '' }}>
                                    {{ $size->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('ring_size_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small style="color: #64748b; margin-top: 0.25rem; display: block;">Only applicable for rings</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weight (grams)</label>
                        <input type="number" name="weight" class="form-control @error('weight') is-invalid @enderror"
                            placeholder="0.000" step="0.001" min="0" value="{{ old('weight', $jewelleryStock->weight) }}">
                        @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Stock Information --}}
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-box-seam" style="color: #6366f1;"></i> Stock Information
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Quantity <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                            placeholder="0" min="0" value="{{ old('quantity', $jewelleryStock->quantity) }}" required>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror"
                            placeholder="5" min="0" value="{{ old('low_stock_threshold', $jewelleryStock->low_stock_threshold) }}">
                        @error('low_stock_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small style="color: #64748b; margin-top: 0.25rem; display: block;">Alert when stock falls below this number</small>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-currency-dollar" style="color: #6366f1;"></i> Pricing
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Purchase Price ($)</label>
                        <input type="number" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
                            placeholder="0.00" step="0.01" min="0" value="{{ old('purchase_price', $jewelleryStock->purchase_price) }}">
                        @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Selling Price ($)</label>
                        <input type="number" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror"
                            placeholder="0.00" step="0.01" min="0" value="{{ old('selling_price', $jewelleryStock->selling_price) }}">
                        @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Profit Margin (Auto-calculated)</label>
                        <div id="margin_display"
                            style="padding: 0.75rem; background: rgba(16, 185, 129, 0.1); border-radius: 8px; font-weight: 700; font-size: 1.1rem; color: #10b981;">
                            $0.00 (0%)
                        </div>
                    </div>
                </div>
            </div>

            {{-- Additional Information --}}
            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-card-text" style="color: #6366f1;"></i> Additional Information
                    <small style="font-weight: 400; color: #64748b;">(Optional)</small>
                </h3>
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                        placeholder="Enter a detailed description...">{{ old('description', $jewelleryStock->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Image Source</label>
                    <div style="display: flex; gap: 1.5rem; margin-bottom: 1rem;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            <input type="radio" name="image_source_type" id="image_source_upload" value="upload"
                                {{ !old('image_url', $jewelleryStock->image_url) ? 'checked' : '' }} onchange="toggleImageSource('upload')">
                            Upload Image File
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            <input type="radio" name="image_source_type" id="image_source_url" value="url"
                                {{ old('image_url', $jewelleryStock->image_url) ? 'checked' : '' }} onchange="toggleImageSource('url')">
                            Provide Image URL
                        </label>
                    </div>

                    <div id="image_upload_section" style="display: {{ !old('image_url', $jewelleryStock->image_url) ? 'block' : 'none' }};">
                        <input type="file" name="image_upload" id="image_upload"
                            class="form-control @error('image_upload') is-invalid @enderror"
                            accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewUpload(this)">
                        @error('image_upload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small style="color: #64748b; margin-top: 0.25rem; display: block;">Max size: 5MB. Formats: JPEG, PNG, JPG, WEBP.</small>
                    </div>

                    <div id="image_url_section" style="display: {{ old('image_url', $jewelleryStock->image_url) ? 'block' : 'none' }};">
                        <input type="url" name="image_url" id="image_url"
                            class="form-control @error('image_url') is-invalid @enderror"
                            placeholder="https://example.com/image.jpg" value="{{ old('image_url', $jewelleryStock->image_url) }}"
                            oninput="previewUrl(this.value)">
                        @error('image_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small style="color: #64748b; margin-top: 0.25rem; display: block;">Direct link to the item image</small>
                    </div>

                    <div class="mt-3 image-preview-container" style="display: {{ old('image_url', $jewelleryStock->image_url) ? 'block' : 'none' }};">
                        <small style="color: #64748b;">Image Preview</small>
                        <br>
                        <img id="image_preview" src="{{ old('image_url', $jewelleryStock->image_url) }}" alt="Preview"
                            style="max-height: 200px; max-width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; margin-top: 0.5rem;">
                        <div id="preview_error" style="display: none; color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">
                            <i class="bi bi-exclamation-triangle"></i> Preview not available
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="tracker-form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('jewellery-stock.index') }}" class="btn-secondary-custom">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-save"></i> Update Jewellery Item
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Margin Calculator
        function calculateMargin() {
            const purchase = parseFloat(document.querySelector('[name="purchase_price"]').value) || 0;
            const selling = parseFloat(document.querySelector('[name="selling_price"]').value) || 0;
            const margin = selling - purchase;
            const pct = purchase > 0 ? (margin / purchase * 100) : 0;
            const display = document.getElementById('margin_display');
            const isPositive = margin >= 0;
            display.style.background = isPositive ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
            display.style.color = isPositive ? '#10b981' : '#ef4444';
            display.textContent = '$' + margin.toFixed(2) + ' (' + pct.toFixed(1) + '%)';
        }
        document.querySelector('[name="purchase_price"]')?.addEventListener('input', calculateMargin);
        document.querySelector('[name="selling_price"]')?.addEventListener('input', calculateMargin);

        function toggleImageSource(type) {
            if (type === 'upload') {
                document.getElementById('image_upload_section').style.display = 'block';
                document.getElementById('image_url_section').style.display = 'none';
                document.getElementById('preview_error').style.display = 'none';
                const fileInput = document.getElementById('image_upload');
                if (fileInput.files && fileInput.files[0]) {
                    previewUpload(fileInput);
                } else {
                    document.querySelector('.image-preview-container').style.display = 'none';
                    document.getElementById('image_preview').src = '';
                }
            } else {
                document.getElementById('image_upload_section').style.display = 'none';
                document.getElementById('image_url_section').style.display = 'block';
                document.getElementById('image_upload').value = '';
                const urlInput = document.getElementById('image_url');
                if (urlInput.value) previewUrl(urlInput.value);
                else {
                    document.querySelector('.image-preview-container').style.display = 'none';
                    document.getElementById('image_preview').src = '';
                }
            }
        }

        function previewUrl(url) {
            const container = document.querySelector('.image-preview-container');
            const img = document.getElementById('image_preview');
            const error = document.getElementById('preview_error');
            if (url && url.trim() !== '') {
                container.style.display = 'block';
                error.style.display = 'none';
                img.style.display = 'block';
                img.onerror = function () { img.style.display = 'none'; error.style.display = 'block'; };
                img.onload = function () { img.style.display = 'block'; error.style.display = 'none'; };
                img.src = url;
            } else {
                container.style.display = 'none';
                img.src = '';
            }
        }

        function previewUpload(input) {
            const container = document.querySelector('.image-preview-container');
            const img = document.getElementById('image_preview');
            const error = document.getElementById('preview_error');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    container.style.display = 'block';
                    error.style.display = 'none';
                    img.style.display = 'block';
                    img.src = e.target.result;
                };
                reader.onerror = function () { img.style.display = 'none'; error.style.display = 'block'; };
                reader.readAsDataURL(input.files[0]);
            } else {
                container.style.display = 'none';
                img.src = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('image_source_url').checked) {
                toggleImageSource('url');
                const initialUrl = document.getElementById('image_url').value;
                if (initialUrl) previewUrl(initialUrl);
            } else {
                toggleImageSource('upload');
            }
            // Run margin calculation on load
            calculateMargin();
        });
    </script>
@endpush
