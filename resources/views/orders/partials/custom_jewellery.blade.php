<!-- Order Details Section -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-file-text-fill"></i>
        </div>
        <div class="section-header-text">
            <h5 class="section-title">Order Details</h5>
            <p class="section-description">Enter customer and product information</p>
        </div>
    </div>
    <div class="section-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern" style="font-weight:600;">
                        <span class="label-icon" style="color:var(--primary);">
                            <i class="bi bi-person-lines-fill"></i>
                        </span>
                        <span class="label-text">Client Name</span>
                        <span class="required-badge">Required</span>
                    </label>
                    <input type="text" name="client_name" class="form-control-modern" required placeholder="Full name"
                        value="{{ old('client_name', $order->client_name ?? '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern" style="font-weight:600;">
                        <span class="label-icon">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <span class="label-text">Mail ID</span>
                        <span class="required-badge">Required</span>
                    </label>
                    <input type="email" name="client_email" class="form-control-modern" required
                        placeholder="client@example.com" value="{{ old('client_email', $order->client_email ?? '') }}">
                </div>
            </div>

            <div class="col-12">
                <div class="form-group-modern">
                    <label class="form-label-modern" style="font-weight:600;">
                        <span class="label-icon">
                            <i class="bi bi-geo-alt"></i>
                        </span>
                        <span class="label-text">Full Address</span>
                        <span class="required-badge">Required</span>
                    </label>
                    <textarea name="client_address" class="form-control-modern" rows="2" required
                        placeholder="Street, city, state, postal code">{{ old('client_address', $order->client_address ?? '') }}</textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern" style="font-weight:600;">
                        <span class="label-icon">
                            <i class="bi bi-phone"></i>
                        </span>
                        <span class="label-text">Mobile Number</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <input type="text" name="client_mobile" class="form-control-modern" placeholder="+91 98765 43210"
                        value="{{ old('client_mobile', $order->client_mobile ?? '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern" style="font-weight:600;">
                        <span class="label-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </span>
                        <span class="label-text">Tax ID</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <input type="text" name="client_tax_id" class="form-control-modern" placeholder="GST / Tax ID"
                        value="{{ old('client_tax_id', $order->client_tax_id ?? '') }}">
                </div>
            </div>
        </div>

        <div class="form-group-modern">
            <label class="form-label-modern">
                <span class="label-icon">
                    <i class="bi bi-gem"></i>
                </span>
                <span class="label-text">Jewellery Details</span>
                <span class="required-badge">Required</span>
            </label>
            <textarea name="jewellery_details" class="form-control-modern" rows="4"
                placeholder="Enter jewellery specifications, design details, and features..."
                required>{{ old('jewellery_details', $order->jewellery_details ?? '') }}</textarea>
            <div class="form-hint">
                <i class="bi bi-info-circle"></i>
                Include type, weight, dimensions, and design specifications
            </div>
        </div>

        <div class="form-group-modern">
            <label class="form-label-modern">
                <span class="label-icon">
                    <i class="bi bi-stars"></i>
                </span>
                <span class="label-text">Diamond Details</span>
                <span class="optional-badge">Optional</span>
            </label>
            <textarea name="diamond_details" class="form-control-modern" rows="3"
                placeholder="Enter diamond specifications (carat, cut, clarity, color)...">{{ old('diamond_details', $order->diamond_details ?? '') }}</textarea>
            <div class="form-hint">
                <i class="bi bi-info-circle"></i>
                Include carat weight, cut grade, clarity, and color specifications
            </div>
        </div>
        <div class="form-group-modern">
            <label class="form-label-modern">
                <span class="label-icon">
                    <i class="bi bi-tag"></i>
                </span>
                <span class="label-text">Diamond SKU</span>
                <span class="optional-badge">Optional</span>
            </label>
            <div class="sku-input-wrapper">
                <input type="text" name="diamond_sku" id="diamond_sku_input" class="form-control-modern"
                    placeholder="Enter diamond SKU (e.g., D-12345)"
                    value="{{ old('diamond_sku', $order->diamond_sku ?? '') }}" autocomplete="off">
                <span class="sku-validation-icon" id="sku_validation_icon"></span>
            </div>
            <div class="sku-validation-message" id="sku_validation_message"></div>
        </div>
    </div>
</div>

<!-- Product Specifications Section -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-sliders"></i>
        </div>
        <div class="section-header-text">
            <h5 class="section-title">Product Specifications</h5>
            <p class="section-description">Select metal type, sizes, and settings</p>
        </div>
    </div>
    <div class="section-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon" style="color: #FFD700;">
                            <i class="bi bi-circle-fill"></i>
                        </span>
                        <span class="label-text">Metal Type</span>
                        <span class="required-badge">Required</span>
                    </label>
                    <select name="gold_detail_id" class="form-control-modern" required>
                        <option value="">-- Select Metal Type --</option>
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
                        <span class="label-icon">
                            <i class="bi bi-circle"></i>
                        </span>
                        <span class="label-text">Ring Size</span>
                        <span class="optional-badge">Optional</span>
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
                        <span class="label-icon">
                            <i class="bi bi-gear"></i>
                        </span>
                        <span class="label-text">Ring Setting Type</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <select name="setting_type_id" class="form-control-modern">
                        <option value="">-- Select Setting Type --</option>
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
                        <span class="label-icon">
                            <i class="bi bi-flower1"></i>
                        </span>
                        <span class="label-text">Earring Type</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <select name="earring_type_id" class="form-control-modern">
                        <option value="">-- Select Earring Type --</option>
                        @foreach($closureTypes as $earring)
                            <option value="{{ $earring->id }}" {{ old('earring_type_id', $order->earring_type_id ?? '') == $earring->id ? 'selected' : '' }}>
                                {{ $earring->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-record-circle"></i>
                        </span>
                        <span class="label-text">Other (specify)</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <input type="text" name="product_other" class="form-control-modern"
                        placeholder="If Other, specify (e.g., Bracelet)"
                        value="{{ old('product_other', $order->product_other ?? '') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Business Details Section -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-building"></i>
        </div>
        <div class="section-header-text">
            <h5 class="section-title">Business Details</h5>
            <p class="section-description">Company, pricing, and status information</p>
        </div>
    </div>
    <div class="section-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-briefcase"></i>
                        </span>
                        <span class="label-text">Company</span>
                        <span class="required-badge">Required</span>
                    </label>
                    <select name="company_id" class="form-control-modern" required>
                        <option value="">-- Select Company --</option>
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
                        <span class="label-icon">
                            <i class="bi bi-check-circle"></i>
                        </span>
                        <span class="label-text">Diamond Status</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <select name="diamond_status" class="form-control-modern">
                        <option value="">-- Select Status --</option>
                        <option value="j_diamond_in_progress" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_diamond_in_progress' ? 'selected' : '' }}>
                            J - Diamond In Progress
                        </option>
                        <option value="j_diamond_completed" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_diamond_completed' ? 'selected' : '' }}>
                            J - Diamond Completed
                        </option>
                        <option value="j_diamond_in_discuss" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_diamond_in_discuss' ? 'selected' : '' }}>
                            J - Diamond In Discuss
                        </option>
                        <option value="j_cad_in_progress" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_cad_in_progress' ? 'selected' : '' }}>
                            J - CAD In Progress
                        </option>
                        <option value="j_cad_done" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_cad_done' ? 'selected' : '' }}>
                            J - CAD Done
                        </option>
                        <option value="j_order_completed" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_order_completed' ? 'selected' : '' }}>
                            J - Order Completed
                        </option>
                        <option value="j_order_in_qc" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_order_in_qc' ? 'selected' : '' }}>
                            J - Order IN QC
                        </option>
                        <option value="j_qc_done" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_qc_done' ? 'selected' : '' }}>
                            J - QC Done
                        </option>
                        <option value="j_order_shipped" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_order_shipped' ? 'selected' : '' }}>
                            J - Order Shipped
                        </option>
                        <option value="j_order_hold" {{ old('diamond_status', $order->diamond_status ?? '') == 'j_order_hold' ? 'selected' : '' }}>
                            J - Order Hold
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </span>
                        <span class="label-text">Gross Sell ($)</span>
                        <span class="required-badge">Required</span>
                    </label>
                    <input type="number" step="0.01" name="gross_sell" class="form-control-modern" required
                        placeholder="0.00" value="{{ old('gross_sell', $order->gross_sell ?? '') }}">
                    <div class="form-hint">
                        <i class="bi bi-info-circle"></i>
                        <span>Enter the total selling price</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-chat-left-text"></i>
                        </span>
                        <span class="label-text">Priority Note</span>
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

            <div class="col-12">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-journal-text"></i>
                        </span>
                        <span class="label-text">Special Notes</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <textarea name="special_notes" class="form-control-modern" rows="3"
                        placeholder="Enter any special requirements, changes, or notes for this order...">{{ old('special_notes', $order->special_notes ?? '') }}</textarea>
                    <div class="form-hint">
                        <i class="bi bi-info-circle"></i>
                        Notes for internal use - special instructions, changes, or requirements
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Upload Section -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-images"></i>
        </div>
        <div class="section-header-text">
            <h5 class="section-title">Media & Documents</h5>
            <p class="section-description">Upload design images and PDF documents</p>
        </div>
    </div>
    <div class="section-body">
        <div class="file-upload-wrapper">
            <label class="form-label-modern">
                <span class="label-icon">
                    <i class="bi bi-card-image"></i>
                </span>
                <span class="label-text">Product Images</span>
                <span class="required-badge">Required</span>
                <span class="badge-info">Max 10 images</span>
            </label>
            <input type="file" name="images[]" id="product_images" class="file-input-hidden" accept="image/*" {{ !isset($order) || !$order ? 'required' : '' }} multiple>
            <label for="product_images" class="file-upload-area diamond">
                <div class="file-upload-content">
                    <div class="file-upload-icon">
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
                <span class="label-icon">
                    <i class="bi bi-file-pdf"></i>
                </span>
                <span class="label-text">PDF Documents</span>
                <span class="optional-badge">Optional</span>
                <span class="badge-info">Max 5 PDFs</span>
            </label>
            <input type="file" name="order_pdfs[]" id="order_pdfs" class="file-input-hidden" accept="application/pdf"
                multiple>
            <label for="order_pdfs" class="file-upload-area pdf">
                <div class="file-upload-content">
                    <div class="file-upload-icon pdf">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                    </div>
                    <div class="file-upload-text">
                        <span class="upload-title">Click to upload PDFs</span>
                        <span class="upload-subtitle">or drag and drop</span>
                    </div>
                    <div class="upload-formats">PDF files up to 10MB each (compress if larger)</div>
                </div>
            </label>
            <div class="file-preview-list" id="preview_order_pdfs"></div>
        </div>
    </div>
</div>

<!-- Shipping Details Section -->
<div class="form-section-card mb-4">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-truck"></i>
        </div>
        <div class="section-header-text">
            <h5 class="section-title">Shipping Information</h5>
            <p class="section-description">Delivery and tracking details</p>
        </div>
    </div>
    <div class="section-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-building"></i>
                        </span>
                        <span class="label-text">Shipping Company</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <input type="text" name="shipping_company_name" class="form-control-modern"
                        placeholder="e.g., FedEx, DHL, Blue Dart"
                        value="{{ old('shipping_company_name', $order->shipping_company_name ?? '') }}">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-hash"></i>
                        </span>
                        <span class="label-text">Tracking Number</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <input type="text" name="tracking_number" class="form-control-modern"
                        placeholder="Enter tracking number"
                        value="{{ old('tracking_number', $order->tracking_number ?? '') }}">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-calendar-event"></i>
                        </span>
                        <span class="label-text">Dispatch Date</span>
                        <span class="required-badge">Required</span>
                    </label>
                    <input type="date" name="dispatch_date" class="form-control-modern"
                        value="{{ old('dispatch_date', $order && $order->dispatch_date ? $order->dispatch_date->format('Y-m-d') : '') }}">
                </div>
            </div>

            <div class="col-12">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <span class="label-icon">
                            <i class="bi bi-link-45deg"></i>
                        </span>
                        <span class="label-text">Tracking URL</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <input type="url" name="tracking_url" class="form-control-modern"
                        placeholder="https://tracking.example.com/track?id=..."
                        value="{{ old('tracking_url', $order->tracking_url ?? '') }}">
                    <div class="form-hint">
                        <i class="bi bi-info-circle"></i>
                        Full URL for tracking shipment online
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
        --danger: #ef4444;
        --success: #10b981;
        --info: #3b82f6;
    }

    /* Section Card */
    .form-section-card {
        background: white;
        border-radius: 16px;
        border: 2px solid var(--border);
        overflow: hidden;
    }

    .section-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, var(--light-gray), white);
        border-bottom: 2px solid var(--border);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        flex-shrink: 0;
    }

    .section-header-text {
        flex: 1;
    }

    .section-title {
        font-size: 1.0625rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.125rem 0;
    }

    .section-description {
        font-size: 0.8125rem;
        color: var(--gray);
        margin: 0;
    }

    .section-body {
        padding: 1.5rem;
    }

    /* Form Group */
    .form-group-modern {
        margin-bottom: 1.25rem;
    }

    .form-group-modern:last-child {
        margin-bottom: 0;
    }

    /* Form Label */
    .form-label-modern {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.625rem;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.875rem;
    }

    .label-icon {
        display: flex;
        align-items: center;
        color: var(--primary);
        font-size: 1rem;
    }

    .label-text {
        flex: 1;
    }

    /* Badges */
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
        background: linear-gradient(135deg, var(--info), #2563eb);
        color: white;
        font-size: 0.625rem;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    /* Form Control */
    .form-control-modern {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
        color: var(--dark);
        font-family: inherit;
    }

    .form-control-modern:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-control-modern::placeholder {
        color: #94a3b8;
    }

    /* Form Hint */
    .form-hint {
        margin-top: 0.5rem;
        font-size: 0.75rem;
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .form-hint i {
        font-size: 0.875rem;
    }

    /* File Upload */
    .file-upload-wrapper {
        margin-bottom: 1.5rem;
    }

    .file-upload-wrapper:last-child {
        margin-bottom: 0;
    }

    .file-input-hidden {
        display: none;
    }

    .file-upload-area {
        display: block;
        padding: 2.5rem 2rem;
        border: 2px dashed var(--border);
        border-radius: 12px;
        background: var(--light-gray);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-area:hover {
        border-color: var(--primary);
        background: rgba(255, 255, 255, 0.03);
    }

    .file-upload-area.pdf:hover {
        border-color: #EF4444;
    }

    .file-upload-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        text-align: center;
    }

    .file-upload-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: white;
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--primary);
    }

    .file-upload-icon.pdf {
        color: var(--danger);
    }

    .file-upload-text {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .upload-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark);
    }

    .upload-subtitle {
        font-size: 0.8125rem;
        color: var(--gray);
    }

    .upload-formats {
        font-size: 0.75rem;
        color: var(--gray);
    }

    /* File Preview Grid */
    .file-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.875rem;
        margin-top: 1rem;
    }

    .file-preview-item {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid var(--border);
        background: white;
        aspect-ratio: 1;
    }

    .file-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .file-preview-remove {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: var(--danger);
        color: white;
        border: none;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        opacity: 0;
        font-size: 0.875rem;
    }

    .file-preview-item:hover .file-preview-remove {
        opacity: 1;
    }

    .file-preview-remove:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    /* File Preview List */
    .file-preview-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .file-preview-list-item {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 0.875rem 1rem;
        background: white;
        border: 2px solid var(--border);
        border-radius: 10px;
    }

    .file-preview-icon-pdf {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--danger), #dc2626);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .file-preview-info {
        flex: 1;
        min-width: 0;
    }

    .file-preview-name {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.125rem;
        font-size: 0.875rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-preview-size {
        font-size: 0.75rem;
        color: var(--gray);
    }

    .file-preview-list-remove {
        background: var(--danger);
        color: white;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .file-preview-list-remove:hover {
        background: #dc2626;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .section-body {
            padding: 1.25rem;
        }

        .file-upload-area {
            padding: 2rem 1.5rem;
        }

        .file-preview-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        }
    }

    /* SKU Real-time Validation Styles */
    .sku-input-wrapper {
        position: relative;
    }

    .sku-input-wrapper .form-control-modern {
        padding-right: 2.75rem;
    }

    .sku-validation-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.25rem;
        line-height: 1;
    }

    .sku-validation-message {
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        min-height: 1.25rem;
    }

    .form-control-modern.sku-valid {
        border-color: var(--success) !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .form-control-modern.sku-invalid {
        border-color: var(--danger) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .text-success {
        color: var(--success) !important;
    }

    .text-danger {
        color: var(--danger) !important;
    }

    .sku-spin {
        animation: skuSpin 1s linear infinite;
        color: var(--primary);
    }

    @keyframes skuSpin {
        100% {
            transform: translateY(-50%) rotate(360deg);
        }
    }
</style>

<!-- JavaScript -->
<script>
    (function () {
        // Image upload preview
        const imageInput = document.getElementById('product_images');
        const imagePreview = document.getElementById('preview_product_images');

        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function (e) {
                const files = Array.from(e.target.files);
                imagePreview.innerHTML = '';

                files.slice(0, 10).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const div = document.createElement('div');
                        div.className = 'file-preview-item';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${index + 1}">
                            <button type="button" class="file-preview-remove" onclick="removeImagePreview(this, ${index})">
                                <i class="bi bi-x"></i>
                            </button>
                        `;
                        imagePreview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        // PDF upload preview
        const pdfInput = document.getElementById('order_pdfs');
        const pdfPreview = document.getElementById('preview_order_pdfs');

        if (pdfInput && pdfPreview) {
            pdfInput.addEventListener('change', function (e) {
                const files = Array.from(e.target.files);
                pdfPreview.innerHTML = '';

                files.slice(0, 5).forEach((file, index) => {
                    const size = (file.size / (1024 * 1024)).toFixed(2);
                    const div = document.createElement('div');
                    div.className = 'file-preview-list-item';
                    div.innerHTML = `
                        <div class="file-preview-icon-pdf">
                            <i class="bi bi-file-pdf"></i>
                        </div>
                        <div class="file-preview-info">
                            <div class="file-preview-name">${file.name}</div>
                            <div class="file-preview-size">${size} MB</div>
                        </div>
                        <button type="button" class="file-preview-list-remove" onclick="removePdfPreview(this, ${index})">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
                    pdfPreview.appendChild(div);
                });
            });
        }
    })();

    function removeImagePreview(btn, index) {
        const imageInput = document.getElementById('product_images');
        const dt = new DataTransfer();
        const files = Array.from(imageInput.files);

        files.forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });

        imageInput.files = dt.files;
        btn.closest('.file-preview-item').remove();
    }

    function removePdfPreview(btn, index) {
        const pdfInput = document.getElementById('order_pdfs');
        const dt = new DataTransfer();
        const files = Array.from(pdfInput.files);

        files.forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });

        pdfInput.files = dt.files;
        btn.closest('.file-preview-list-item').remove();
    }
</script>