@extends('layouts.admin')
@section('title', 'Add New Jewellery Stock')

@section('content')

    <div class="diamond-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <nav class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i>
                            <span>Dashboard</span>
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('jewellery-stock.index') }}" class="breadcrumb-link">
                            <span>Jewellery Stock</span>
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Add New</span>
                    </nav>
                    <h1 class="page-title">
                        <i class="bi bi-plus-circle"></i>
                        Add Jewellery Item
                    </h1>
                    <p class="page-subtitle">Enter the details for the new jewellery stock item</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('jewellery-stock.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
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

        <form action="{{ route('jewellery-stock.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Basic Information -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Basic Information</h5>
                            <p class="section-description">Core details of the jewellery item</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">SKU <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-upc-scan input-icon"></i>
                                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                    placeholder="e.g. JW-RING-001" value="{{ old('sku') }}" required>
                            </div>
                            @error('sku')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i> Unique stock-keeping unit identifier
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Type <span class="required">*</span></label>
                            <select name="type" class="form-control themed-select @error('type') is-invalid @enderror"
                                required>
                                <option value="">Select Type</option>
                                <option value="ring" {{ old('type') == 'ring' ? 'selected' : '' }}>Ring</option>
                                <option value="earrings" {{ old('type') == 'earrings' ? 'selected' : '' }}>Earrings</option>
                                <option value="tennis_bracelet" {{ old('type') == 'tennis_bracelet' ? 'selected' : '' }}>
                                    Tennis Bracelet</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i> Category of the jewellery item
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. 18K Gold Solitaire Ring" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i> Descriptive name for the jewellery item
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-palette"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Details</h5>
                            <p class="section-description">Metal type, ring size and weight</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Metal Type <span class="required">*</span></label>
                            <select name="metal_type_id"
                                class="form-control themed-select @error('metal_type_id') is-invalid @enderror" required>
                                <option value="">Select Metal Type</option>
                                @foreach ($metalTypes as $metal)
                                    <option value="{{ $metal->id }}"
                                        {{ old('metal_type_id') == $metal->id ? 'selected' : '' }}>
                                        {{ $metal->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('metal_type_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ring Size</label>
                            <select name="ring_size_id"
                                class="form-control themed-select @error('ring_size_id') is-invalid @enderror">
                                <option value="">Select Ring Size (optional)</option>
                                @foreach ($ringSizes as $size)
                                    <option value="{{ $size->id }}"
                                        {{ old('ring_size_id') == $size->id ? 'selected' : '' }}>
                                        {{ $size->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ring_size_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i> Only applicable for rings
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Weight (grams)</label>
                            <div class="input-with-icon">
                                <i class="bi bi-speedometer input-icon"></i>
                                <input type="number" name="weight"
                                    class="form-control @error('weight') is-invalid @enderror" placeholder="0.000"
                                    step="0.001" min="0" value="{{ old('weight') }}">
                            </div>
                            @error('weight')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Information -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Stock Information</h5>
                            <p class="section-description">Set initial quantity and low stock alert threshold</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Quantity <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="bi bi-hash input-icon"></i>
                                <input type="number" name="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror" placeholder="0"
                                    min="0" value="{{ old('quantity', 1) }}" required>
                            </div>
                            @error('quantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Low Stock Threshold</label>
                            <div class="input-with-icon">
                                <i class="bi bi-exclamation-triangle input-icon"></i>
                                <input type="number" name="low_stock_threshold"
                                    class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                    placeholder="5" min="0" value="{{ old('low_stock_threshold', 5) }}">
                            </div>
                            @error('low_stock_threshold')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i> Alert when stock falls below this number
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Pricing</h5>
                            <p class="section-description">Set purchase and selling prices</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Purchase Price ($)</label>
                            <div class="input-with-icon">
                                <i class="bi bi-cash input-icon"></i>
                                <input type="number" name="purchase_price"
                                    class="form-control @error('purchase_price') is-invalid @enderror" placeholder="0.00"
                                    step="0.01" min="0" value="{{ old('purchase_price') }}">
                            </div>
                            @error('purchase_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Selling Price ($)</label>
                            <div class="input-with-icon">
                                <i class="bi bi-cash-stack input-icon"></i>
                                <input type="number" name="selling_price"
                                    class="form-control @error('selling_price') is-invalid @enderror" placeholder="0.00"
                                    step="0.01" min="0" value="{{ old('selling_price') }}">
                            </div>
                            @error('selling_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-card-text"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Additional Information</h5>
                            <p class="section-description">Optional description and image for the item</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                                placeholder="Enter a detailed description...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Image Source</label>

                            <div class="d-flex gap-4 mb-3">
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="image_source_type"
                                        id="image_source_upload" value="upload" {{ !old('image_url') ? 'checked' : '' }}
                                        onchange="toggleImageSource('upload')">
                                    <label class="form-check-label" for="image_source_upload">
                                        Upload Image File
                                    </label>
                                </div>
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="image_source_type"
                                        id="image_source_url" value="url" {{ old('image_url') ? 'checked' : '' }}
                                        onchange="toggleImageSource('url')">
                                    <label class="form-check-label" for="image_source_url">
                                        Provide Image URL
                                    </label>
                                </div>
                            </div>

                            <div id="image_upload_section" style="display: {{ !old('image_url') ? 'block' : 'none' }};">
                                <div class="input-with-icon">
                                    <i class="bi bi-upload input-icon"></i>
                                    <input type="file" name="image_upload" id="image_upload"
                                        class="form-control @error('image_upload') is-invalid @enderror"
                                        accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewUpload(this)">
                                </div>
                                @error('image_upload')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-hint mt-1">
                                    <i class="bi bi-info-circle"></i> Max size: 5MB. Formats: JPEG, PNG, JPG, WEBP.
                                </div>
                            </div>

                            <div id="image_url_section" style="display: {{ old('image_url') ? 'block' : 'none' }};">
                                <div class="input-with-icon">
                                    <i class="bi bi-link-45deg input-icon"></i>
                                    <input type="url" name="image_url" id="image_url"
                                        class="form-control @error('image_url') is-invalid @enderror"
                                        placeholder="https://example.com/image.jpg" value="{{ old('image_url') }}"
                                        oninput="previewUrl(this.value)">
                                </div>
                                @error('image_url')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-hint mt-1">
                                    <i class="bi bi-info-circle"></i> Direct link to the item image
                                </div>
                            </div>

                            <!-- Image Preview Area -->
                            <div class="mt-3 image-preview-container"
                                style="display: {{ old('image_url') ? 'block' : 'none' }};">
                                <label class="form-label d-block text-muted small">Image Preview</label>
                                <img id="image_preview" src="{{ old('image_url') }}" alt="Preview"
                                    class="img-thumbnail"
                                    style="max-height: 200px; max-width: 100%; border: 1px solid var(--border, #e2e8f0); border-radius: 8px;">
                                <div id="preview_error" class="text-danger small mt-1" style="display: none;">
                                    <i class="bi bi-exclamation-triangle"></i> Preview not available
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Actions -->
            <div class="action-footer">
                <div class="action-footer-left"></div>
                <div class="action-footer-right">
                    <a href="{{ route('jewellery-stock.index') }}" class="btn-cancel">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-save"></i> Save Jewellery Item
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleImageSource(type) {
            if (type === 'upload') {
                document.getElementById('image_upload_section').style.display = 'block';
                document.getElementById('image_url_section').style.display = 'none';
                // Clear URL and error when switching to upload
                document.getElementById('image_url').value = '';
                document.getElementById('preview_error').style.display = 'none';

                // Re-trigger preview for upload if a file is already selected
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
                // Clear file upload when switching to URL
                document.getElementById('image_upload').value = '';

                // Re-trigger preview for URL
                const urlInput = document.getElementById('image_url');
                if (urlInput.value) {
                    previewUrl(urlInput.value);
                } else {
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

                // Set error handler just for this load attempt
                img.onerror = function() {
                    img.style.display = 'none';
                    error.style.display = 'block';
                };
                img.onload = function() {
                    img.style.display = 'block';
                    error.style.display = 'none';
                };

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

                reader.onload = function(e) {
                    container.style.display = 'block';
                    error.style.display = 'none';
                    img.style.display = 'block';
                    img.src = e.target.result;
                }

                reader.onerror = function() {
                    img.style.display = 'none';
                    error.style.display = 'block';
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                container.style.display = 'none';
                img.src = '';
            }
        }

        // Initial check for radio buttons on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('image_source_url').checked) {
                toggleImageSource('url');
            } else {
                toggleImageSource('upload');
            }
        });
    </script>
@endpush
