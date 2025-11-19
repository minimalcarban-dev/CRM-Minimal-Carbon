<!-- Order Details Section -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-info">
            <div class="section-icon">
                <i class="bi bi-file-text-fill"></i>
            </div>
            <div>
                <h5 class="section-title">Order Details</h5>
                <p class="section-description">Enter client and product information</p>
            </div>
        </div>
    </div>
    <div class="section-body">
        <div class="form-group-modern mb-4">
            <label class="form-label-modern">
                <span class="label-content">
                    <span class="label-icon"><i class="bi bi-person-lines-fill"></i></span>
                    <span class="label-text">Client Details</span>
                </span>
                <span class="required-badge">Required</span>
            </label>
            <textarea name="client_details" class="form-control-modern" rows="4"
                placeholder="Enter client name, contact details, and requirements..."
                required>{{ old('client_details', $order->client_details ?? '') }}</textarea>
            <div class="form-hint">
                <i class="bi bi-info-circle"></i>
                <span>Include client name, phone, email, and any special requests</span>
            </div>
        </div>

        <div class="form-group-modern mb-4">
            <label class="form-label-modern">
                <span class="label-content">
                    <span class="label-icon"><i class="bi bi-gem"></i></span>
                    <span class="label-text">Jewellery Details</span>
                </span>
                <span class="optional-badge">Optional</span>
            </label>
            <textarea name="jewellery_details" class="form-control-modern" rows="3"
                placeholder="Enter jewellery specifications or details...">{{ old('jewellery_details', $order->jewellery_details ?? '') }}</textarea>
        </div>

        <div class="form-group-modern">
            <label class="form-label-modern">
                <span class="label-content">
                    <span class="label-icon"><i class="bi bi-stars"></i></span>
                    <span class="label-text">Diamond Details</span>
                </span>
                <span class="optional-badge">Optional</span>
            </label>
            <textarea name="diamond_details" class="form-control-modern" rows="3"
                placeholder="Enter diamond specifications, carat, clarity, cut...">{{ old('diamond_details', $order->diamond_details ?? '') }}</textarea>
        </div>
    </div>
</div>

<!-- Metal, Company & Status -->
<div class="form-section-card mb-4">
    <div class="section-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="section-info" style="display:flex; align-items:center;">
            <div class="section-icon" style="margin-right:10px;">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div>
                <h5 class="section-title">Product Specifications</h5>
                <p class="section-description">Select metal type, sizes, and settings</p>
            </div>
        </div>
        <div class="optional-badge" style="font-weight:600; color:#fffff;">
            Optional
        </div>
    </div>
    <div class="section-body">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon" style="color: #FFD700;">
                                <i class="bi bi-circle-fill"></i>
                            </span>
                            <span class="label-text">Gold Detail</span>
                        </span>
                    </label>
                    <select name="gold_detail_id" class="form-control-modern">
                        <option value="">Select Metal Type</option>
                        @foreach($metalTypes as $metal)
                            <option value="{{ $metal->id }}" {{ old('gold_detail_id', $order->gold_detail_id ?? '') == $metal->id ? 'selected' : '' }}>
                                {{ $metal->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-circle"></i></span>
                            <span class="label-text">Ring Size</span>
                        </span>
                    </label>
                    <select name="ring_size_id" class="form-control-modern">
                        <option value="">Select Ring Size</option>
                        @foreach($ringSizes as $size)
                            <option value="{{ $size->id }}" {{ old('ring_size_id', $order->ring_size_id ?? '') == $size->id ? 'selected' : '' }}>
                                {{ $size->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-gear"></i></span>
                            <span class="label-text">Setting Type</span>
                        </span>
                    </label>
                    <select name="setting_type_id" class="form-control-modern">
                        <option value="">Select Setting Type</option>
                        @foreach($settingTypes as $setting)
                            <option value="{{ $setting->id }}" {{ old('setting_type_id', $order->setting_type_id ?? '') == $setting->id ? 'selected' : '' }}>
                                {{ $setting->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-flower1"></i></span>
                            <span class="label-text">Earring Type</span>
                        </span>
                    </label>
                    <select name="earring_type_id" class="form-control-modern">
                        <option value="">Select Earring Type</option>
                        @foreach($closureTypes as $ear)
                            <option value="{{ $ear->id }}" {{ old('earring_type_id', $order->earring_type_id ?? '') == $ear->id ? 'selected' : '' }}>
                                {{ $ear->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Upload Section -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-info">
            <div class="section-icon">
                <i class="bi bi-images"></i>
            </div>
            <div>
                <h5 class="section-title">Media & Documents</h5>
                <p class="section-description">Upload product images and PDF files</p>
            </div>
        </div>
    </div>
    <div class="section-body">
        <div class="file-upload-wrapper mb-4">
            <label class="form-label-modern">
                <span class="label-content">
                    <span class="label-icon"><i class="bi bi-card-image"></i></span>
                    <span class="label-text">Product Images</span>
                </span>
                <span style="display:flex; gap:0.5rem; align-items:center;">
                    <span class="required-badge">Required</span>
                    <span class="badge-info">Max 10 Images</span>
                </span>
            </label>
            <input type="file" name="images[]" id="product_images" class="file-input-hidden" accept="image/*" multiple>
            <label for="product_images" class="file-upload-area" id="imageUploadArea">
                <div class="file-upload-content">
                    <div class="file-upload-icon diamond">
                        <i class="bi bi-cloud-upload"></i>
                    </div>
                    <div class="file-upload-text">
                        <span class="upload-title">Click to upload images</span>
                        <span class="upload-subtitle">or drag and drop</span>
                    </div>
                    <div class="upload-formats">JPG, PNG, GIF up to 10MB each</div>
                </div>
            </label>
            <div class="file-preview-grid" id="preview_product_images"></div>
        </div>

        <div class="file-upload-wrapper">
            <label class="form-label-modern">
                <span class="label-content">
                    <span class="label-icon"><i class="bi bi-file-pdf"></i></span>
                    <span class="label-text">Order PDFs</span>
                </span>
                <span style="display:flex; gap:0.5rem; align-items:center;">
                    <span class="required-badge">Required</span>
                    <span class="badge-info">Max 5 PDFs</span>
                </span>
            </label>
            <input type="file" name="order_pdfs[]" id="order_pdfs" class="file-input-hidden" accept="application/pdf"
                multiple>
            <label for="order_pdfs" class="file-upload-area pdf" id="pdfUploadArea">
                <div class="file-upload-content">
                    <div class="file-upload-icon pdf">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                    </div>
                    <div class="file-upload-text">
                        <span class="upload-title">Click to upload PDFs</span>
                        <span class="upload-subtitle">or drag and drop</span>
                    </div>
                    <div class="upload-formats">PDF up to 10MB each (compress if larger)</div>
                </div>
            </label>
            <div class="file-preview-list" id="preview_order_pdfs"></div>
        </div>
    </div>
</div>

<!-- Order Management -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-info">
            <div class="section-icon">
                <i class="bi bi-sliders"></i>
            </div>
            <div>
                <h5 class="section-title">Order Management</h5>
                <p class="section-description">Company, priority, and pricing details</p>
            </div>
        </div>
    </div>
    <div class="section-body">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-building"></i></span>
                            <span class="label-text">Company</span>
                        </span>
                        <span class="required-badge">Required</span>
                    </label>
                    <select name="company_id" class="form-control-modern" required>
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $order->company_id ?? '') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-chat-left-text"></i></span>
                            <span class="label-text">Priority Note</span>
                        </span>
                        <span class="required-badge">Required</span>
                    </label>
                    <select name="note" class="form-control-modern" required>
                        <option value="">Select Priority</option>
                        <option value="priority" {{ old('note', $order->note ?? '') == 'priority' ? 'selected' : '' }}>
                            Priority
                        </option>
                        <option value="non_priority" {{ old('note', $order->note ?? '') == 'non_priority' ? 'selected' : '' }}>
                            Non Priority
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-check-circle"></i></span>
                            <span class="label-text">Diamond Status</span>
                        </span>
                    </label>
                    <select name="diamond_status" class="form-control-modern">
                        <option value="">Select Status</option>
                        <option value="processed" {{ old('diamond_status', $order->diamond_status ?? '') == 'processed' ? 'selected' : '' }}>
                            Processed
                        </option>
                        <option value="completed" {{ old('diamond_status', $order->diamond_status ?? '') == 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-currency-dollar"></i></span>
                            <span class="label-text">Gross Sell ($)</span>
                        </span>
                        <span class="required-badge">Required</span>
                    </label>
                    <input type="number" step="0.01" name="gross_sell" class="form-control-modern" required
                        placeholder="0.00" value="{{ old('gross_sell', $order->gross_sell ?? '0.00') }}">
                    <div class="form-hint">
                        <i class="bi bi-info-circle"></i>
                        <span>Enter the total selling price</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Shipping Details -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-info">
            <div class="section-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div>
                <h5 class="section-title">Shipping Details</h5>
                <p class="section-description">Add courier and tracking information</p>
            </div>
        </div>
    </div>
    <div class="section-body">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-building"></i></span>
                            <span class="label-text">Shipping Company</span>
                        </span>
                    </label>
                    <input type="text" name="shipping_company_name" class="form-control-modern"
                        placeholder="e.g., FedEx, DHL, Blue Dart"
                        value="{{ old('shipping_company_name', $order->shipping_company_name ?? '') }}">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-hash"></i></span>
                            <span class="label-text">Tracking Number</span>
                        </span>
                    </label>
                    <input type="text" name="tracking_number" class="form-control-modern"
                        placeholder="Enter tracking number"
                        value="{{ old('tracking_number', $order->tracking_number ?? '') }}">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-calendar-event"></i></span>
                            <span class="label-text">Dispatch Date</span>
                        </span>
                        <span class="required-badge">Required</span>
                    </label>
                    <input type="date" name="dispatch_date" class="form-control-modern" required
                        value="{{ old('dispatch_date', $order->dispatch_date ? \Carbon\Carbon::parse($order->dispatch_date)->format('Y-m-d') : '') }}">
                </div>
            </div>

            <div class="col-12">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-content">
                            <span class="label-icon"><i class="bi bi-link-45deg"></i></span>
                            <span class="label-text">Tracking URL</span>
                        </span>
                    </label>
                    <input type="url" name="tracking_url" class="form-control-modern"
                        placeholder="https://tracking.example.com/..."
                        value="{{ old('tracking_url', $order->tracking_url ?? '') }}">
                    <div class="form-hint">
                        <i class="bi bi-info-circle"></i>
                        <span>Full URL for tracking the shipment</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --dark: #1e293b;
        --gray: #64748b;
        --light-gray: #f8fafc;
        --border: #e2e8f0;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
    }

    /* Form Section Card */
    .form-section-card {
        background: white;
        border-radius: 16px;
        border: 2px solid var(--border);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .form-section-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .section-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, var(--light-gray), white);
        border-bottom: 2px solid var(--border);
    }

    .section-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .section-description {
        font-size: 0.875rem;
        color: var(--gray);
        margin: 0.25rem 0 0;
    }

    .section-body {
        padding: 2rem;
    }

    /* Form Groups */
    .form-group-modern {
        margin-bottom: 0;
    }

    .form-label-modern {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .label-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .label-icon {
        display: flex;
        align-items: center;
        color: var(--primary);
        font-size: 1rem;
    }

    .label-text {
        font-weight: 600;
        color: var(--dark);
    }

    .required-badge {
        background: linear-gradient(135deg, var(--danger), #dc2626);
        color: white;
        font-size: 0.625rem;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .optional-badge {
        background: linear-gradient(135deg, var(--gray), #475569);
        color: white;
        font-size: 0.625rem;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .badge-info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .form-control-modern {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 0.95rem;
        color: var(--dark);
        background: white;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        outline: none;
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.02);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .form-control-modern::placeholder {
        color: #94a3b8;
    }

    .form-hint {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.85rem;
        color: var(--gray);
    }

    .form-hint i {
        color: var(--primary);
        font-size: 1rem;
    }

    /* File Upload */
    .file-upload-wrapper {
        margin-bottom: 0;
    }

    .file-input-hidden {
        display: none;
    }

    .file-upload-area {
        display: block;
        padding: 2rem;
        border: 2px dashed var(--border);
        border-radius: 12px;
        background: var(--light-gray);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-area:hover {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.02);
    }

    .file-upload-area.dragover {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
        transform: scale(1.01);
    }

    .file-upload-content {
        text-align: center;
    }

    .file-upload-icon {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        background: white;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1rem;
        border: 2px solid var(--border);
    }

    .file-upload-icon.diamond {
        color: var(--primary);
    }

    .file-upload-icon.pdf {
        color: var(--danger);
        border-color: #EF4444;
    }

    .file-upload-area.pdf:hover {
        border-color: #EF4444;
    }

    .file-upload-text {
        margin-bottom: 0.75rem;
    }

    .upload-title {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .upload-subtitle {
        display: block;
        font-size: 0.875rem;
        color: var(--gray);
    }

    .upload-formats {
        font-size: 0.85rem;
        color: var(--gray);
        background: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        display: inline-block;
    }

    /* File Preview Grid (Images) */
    .file-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .preview-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid var(--border);
        background: white;
        transition: all 0.3s ease;
    }

    .preview-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .preview-image {
        width: 100%;
        height: 120px;
        object-fit: cover;
        display: block;
    }

    .preview-remove {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--danger);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        transition: all 0.3s ease;
        opacity: 0;
    }

    .preview-item:hover .preview-remove {
        opacity: 1;
    }

    .preview-remove:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .preview-name {
        padding: 0.5rem;
        font-size: 0.75rem;
        color: var(--dark);
        background: var(--light-gray);
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* File Preview List (PDFs) */
    .file-preview-list {
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .pdf-preview-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        background: white;
        transition: all 0.3s ease;
    }

    .pdf-preview-item:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .pdf-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .pdf-info {
        flex: 1;
        min-width: 0;
    }

    .pdf-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pdf-size {
        font-size: 0.85rem;
        color: var(--gray);
    }

    .pdf-remove {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .pdf-remove:hover {
        background: var(--danger);
        color: white;
        transform: scale(1.05);
    }

    /* Empty State for Previews */
    .preview-empty {
        text-align: center;
        padding: 2rem;
        color: var(--gray);
        font-size: 0.9rem;
        display: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .section-body {
            padding: 1.5rem;
        }

        .form-label-modern {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .label-content {
            width: 100%;
        }

        .file-preview-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        }

        .preview-image {
            height: 100px;
        }
    }
</style>

<!-- JavaScript -->
<script>
    (function () {
        'use strict';

        // Image Upload Handler
        const imageInput = document.getElementById('product_images');
        const imagePreview = document.getElementById('preview_product_images');
        const imageUploadArea = document.getElementById('imageUploadArea');
        let selectedImages = [];

        // PDF Upload Handler
        const pdfInput = document.getElementById('order_pdfs');
        const pdfPreview = document.getElementById('preview_order_pdfs');
        const pdfUploadArea = document.getElementById('pdfUploadArea');
        let selectedPDFs = [];

        // Image Input Change Handler
        if (imageInput) {
            imageInput.addEventListener('change', function (e) {
                handleImageFiles(e.target.files);
            });
        }

        // PDF Input Change Handler
        if (pdfInput) {
            pdfInput.addEventListener('change', function (e) {
                handlePDFFiles(e.target.files);
            });
        }

        // Drag and Drop for Images
        if (imageUploadArea) {
            imageUploadArea.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('dragover');
            });

            imageUploadArea.addEventListener('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
            });

            imageUploadArea.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');

                const files = Array.from(e.dataTransfer.files).filter(file =>
                    file.type.startsWith('image/')
                );

                if (files.length > 0) {
                    handleImageFiles(files);
                }
            });
        }

        // Drag and Drop for PDFs
        if (pdfUploadArea) {
            pdfUploadArea.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('dragover');
            });

            pdfUploadArea.addEventListener('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
            });

            pdfUploadArea.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');

                const files = Array.from(e.dataTransfer.files).filter(file =>
                    file.type === 'application/pdf'
                );

                if (files.length > 0) {
                    handlePDFFiles(files);
                }
            });
        }

        // Handle Image Files
        function handleImageFiles(files) {
            const fileArray = Array.from(files);

            // Limit to 10 images
            if (selectedImages.length + fileArray.length > 10) {
                showNotification('Maximum 10 images allowed', 'warning');
                return;
            }

            fileArray.forEach(file => {
                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    showNotification(`${file.name} is too large. Max 10MB`, 'error');
                    return;
                }

                // Validate file type
                if (!file.type.startsWith('image/')) {
                    showNotification(`${file.name} is not a valid image`, 'error');
                    return;
                }

                selectedImages.push(file);
                displayImagePreview(file, selectedImages.length - 1);
            });

            updateImageInput();
            showNotification(`${fileArray.length} image(s) added`, 'success');
        }

        // Display Image Preview
        function displayImagePreview(file, index) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.dataset.index = index;

                previewItem.innerHTML = `
                <img src="${e.target.result}" alt="${file.name}" class="preview-image">
                <button type="button" class="preview-remove" onclick="removeImage(${index})">
                    <i class="bi bi-x"></i>
                </button>
            `;

                imagePreview.appendChild(previewItem);
            };

            reader.readAsDataURL(file);
        }

        // Remove Image
        window.removeImage = function (index) {
            selectedImages.splice(index, 1);
            updateImageInput();
            renderImagePreviews();
            showNotification('Image removed', 'success');
        };

        // Render Image Previews
        function renderImagePreviews() {
            imagePreview.innerHTML = '';
            selectedImages.forEach((file, index) => {
                displayImagePreview(file, index);
            });
        }

        // Update Image Input
        function updateImageInput() {
            const dataTransfer = new DataTransfer();
            selectedImages.forEach(file => {
                dataTransfer.items.add(file);
            });
            imageInput.files = dataTransfer.files;
        }

        // Handle PDF Files
        function handlePDFFiles(files) {
            const fileArray = Array.from(files);

            // Limit to 5 PDFs
            if (selectedPDFs.length + fileArray.length > 5) {
                showNotification('Maximum 5 PDFs allowed', 'warning');
                return;
            }

            fileArray.forEach(file => {
                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    showNotification(`${file.name} is too large. Max 10MB`, 'error');
                    return;
                }

                // Validate file type
                if (file.type !== 'application/pdf') {
                    showNotification(`${file.name} is not a valid PDF`, 'error');
                    return;
                }

                selectedPDFs.push(file);
                displayPDFPreview(file, selectedPDFs.length - 1);
            });

            updatePDFInput();
            showNotification(`${fileArray.length} PDF(s) added`, 'success');
        }

        // Display PDF Preview
        function displayPDFPreview(file, index) {
            const previewItem = document.createElement('div');
            previewItem.className = 'pdf-preview-item';
            previewItem.dataset.index = index;

            const fileSize = formatFileSize(file.size);

            previewItem.innerHTML = `
            <div class="pdf-icon">
                <i class="bi bi-file-pdf-fill"></i>
            </div>
            <div class="pdf-info">
                <div class="pdf-name" title="${file.name}">${file.name}</div>
                <div class="pdf-size">${fileSize}</div>
            </div>
            <button type="button" class="pdf-remove" onclick="removePDF(${index})">
                <i class="bi bi-trash"></i>
            </button>
        `;

            pdfPreview.appendChild(previewItem);
        }

        // Remove PDF
        window.removePDF = function (index) {
            selectedPDFs.splice(index, 1);
            updatePDFInput();
            renderPDFPreviews();
            showNotification('PDF removed', 'success');
        };

        // Render PDF Previews
        function renderPDFPreviews() {
            pdfPreview.innerHTML = '';
            selectedPDFs.forEach((file, index) => {
                displayPDFPreview(file, index);
            });
        }

        // Update PDF Input
        function updatePDFInput() {
            const dataTransfer = new DataTransfer();
            selectedPDFs.forEach(file => {
                dataTransfer.items.add(file);
            });
            pdfInput.files = dataTransfer.files;
        }

        // Format File Size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Show Notification
        function showNotification(message, type) {
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-x-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill'
            };

            const notification = document.createElement('div');
            notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${colors[type] || colors.info};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
            max-width: 350px;
        `;

            notification.innerHTML = `
            <i class="bi ${icons[type] || icons.info}" style="font-size: 1.25rem;"></i>
            <span>${message}</span>
        `;

            document.body.appendChild(notification);

            setTimeout(function () {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(function () {
                    if (notification.parentNode) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

    })();
</script>

<!-- Mobile CSS -->
<style>
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>