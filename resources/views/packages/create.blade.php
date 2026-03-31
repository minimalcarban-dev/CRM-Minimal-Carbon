@extends('layouts.admin')

@section('title', 'Issue New Package')

@section('content')
    <div class="tracker-page">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('packages.index') }}" class="breadcrumb-link">Packages</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Issue New</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-plus-circle-fill" style="color: #6366f1;"></i>
                        Issue New Package
                    </h1>
                    <p class="page-subtitle">Create a new package handover record</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('packages.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('packages.store') }}" method="POST" enctype="multipart/form-data" id="packageForm">
            @csrf

            <div class="row g-4">
                <!-- Left Column: Primary Details -->
                <div class="col-lg-8">
                    <!-- Package Info Card -->
                    <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-box-seam" style="color: #6366f1;"></i> Package Information
                        </h3>
                        <p style="font-size: 0.85rem; color: #64748b; margin-top: -1rem; margin-bottom: 1.5rem;">Enter the details of the package being issued</p>
                        
                        <div class="mb-4">
                            <label for="package_description" class="form-label">Package Description <span class="text-danger">*</span></label>
                            <textarea name="package_description" id="package_description" rows="4" class="form-control"
                                placeholder="Describe the contents of the package..." required>{{ old('package_description') }}</textarea>
                            <div class="form-text text-muted" style="font-size: 0.8rem; margin-top: 0.25rem;"><i class="bi bi-info-circle me-1"></i> Provide a detailed description of items.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="party_type" class="form-label">Party Type <span class="text-danger">*</span></label>
                                <select name="party_type" id="party_type" class="form-control themed-select" required>
                                    <option value="">Select party type</option>
                                    <option value="Broker" {{ old('party_type') == 'Broker' ? 'selected' : '' }}>Broker</option>
                                    <option value="Retailer" {{ old('party_type') == 'Retailer' ? 'selected' : '' }}>Retailer</option>
                                    <option value="Manufacturer" {{ old('party_type') == 'Manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                    <option value="Internal Staff" {{ old('party_type') == 'Internal Staff' ? 'selected' : '' }}>Internal Staff</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Company name" value="{{ old('company_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="gst_number" class="form-label">GST Number</label>
                                <input type="text" name="gst_number" id="gst_number" class="form-control" placeholder="GST number" value="{{ old('gst_number') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="pan_number" class="form-label">PAN Number</label>
                                <input type="text" name="pan_number" id="pan_number" class="form-control" placeholder="PAN number" value="{{ old('pan_number') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="slip_id" class="form-label">Slip ID <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <i class="bi bi-hash position-absolute" style="left: 0.9rem; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                                    <input type="text" name="slip_id" id="slip_id" class="form-control" style="padding-left: 2.25rem;" placeholder="e.g. SLIP-2024-001" value="{{ old('slip_id') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" id="issue_date" class="form-control" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="issue_time" class="form-label">Issue Time <span class="text-danger">*</span></label>
                                <input type="time" name="issue_time" id="issue_time" class="form-control" value="{{ old('issue_time', date('H:i')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="return_date" class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" id="return_date" class="form-control" value="{{ old('return_date') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="handover_location" class="form-label">Handover Location <span class="text-danger">*</span></label>
                                <input type="text" name="handover_location" id="handover_location" class="form-control" placeholder="Office / Surat / Mumbai etc." value="{{ old('handover_location') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="handover_mode" class="form-label">Handover Mode <span class="text-danger">*</span></label>
                                <select name="handover_mode" id="handover_mode" class="form-control themed-select" required>
                                    <option value="">Select mode</option>
                                    <option value="Hand Delivery" {{ old('handover_mode') == 'Hand Delivery' ? 'selected' : '' }}>Hand Delivery</option>
                                    <option value="Courier" {{ old('handover_mode') == 'Courier' ? 'selected' : '' }}>Courier</option>
                                    <option value="Office Pickup" {{ old('handover_mode') == 'Office Pickup' ? 'selected' : '' }}>Office Pickup</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="purpose_of_handover" class="form-label">Purpose of Handover <span class="text-danger">*</span></label>
                                <textarea name="purpose_of_handover" id="purpose_of_handover" rows="2" class="form-control" placeholder="Approval / Memo / Repair / Recut etc." required>{{ old('purpose_of_handover') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="stock_id" class="form-label">Diamond Stock ID (SKU)</label>
                                <div class="d-flex gap-2">
                                    <input type="text" name="stock_id" id="stock_id" class="form-control" placeholder="Enter stock id" value="{{ old('stock_id') }}">
                                    <button type="button" class="btn btn-outline-secondary" id="btnLookupStock">Fetch</button>
                                </div>
                                <div id="stockLookupMessage" class="form-text text-muted" style="font-size: 0.8rem; margin-top: 0.25rem;"></div>
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-4">
                                <label class="form-label">Shape</label>
                                <input type="text" name="diamond_shape" id="diamond_shape" class="form-control" value="{{ old('diamond_shape') }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Size</label>
                                <input type="text" name="diamond_size" id="diamond_size" class="form-control" value="{{ old('diamond_size') }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" name="diamond_color" id="diamond_color" class="form-control" value="{{ old('diamond_color') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Clarity</label>
                                <input type="text" name="diamond_clarity" id="diamond_clarity" class="form-control" value="{{ old('diamond_clarity') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Carat</label>
                                <input type="text" name="diamond_carat" id="diamond_carat" class="form-control" value="{{ old('diamond_carat') }}" readonly>
                            </div>

                        </div>
                    </div>

                    <!-- Recipient Details Card -->
                    <div class="tracker-table-card" style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-person-badge" style="color: #10b981;"></i> Recipient Details
                        </h3>
                        <p style="font-size: 0.85rem; color: #64748b; margin-top: -1rem; margin-bottom: 1.5rem;">Who is receiving this package?</p>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="person_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="person_name" id="person_name" class="form-control" placeholder="John Doe" value="{{ old('person_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="mobile_number" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <input type="tel" name="mobile_number" id="mobile_number" class="form-control" placeholder="+1234567890" value="{{ old('mobile_number') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="package_image" class="form-label">Recipient Photo / ID Proof</label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="package_image" id="package_image" class="file-upload-input" accept="image/*" onchange="previewImage(this)">
                                <div class="file-upload-placeholder" id="uploadPlaceholder">
                                    <div class="upload-icon">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                    <p class="upload-text">Click or drag to upload image</p>
                                    <p class="upload-hint">Supports JPG, PNG, JPEG (Max 2MB)</p>
                                </div>
                                <div class="image-preview-container" id="imagePreviewContainer" style="display: none;">
                                    <img id="imagePreview" src="" alt="Preview">
                                    <button type="button" class="remove-image-btn" onclick="removeImage()">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Summary & Actions -->
                <div class="col-lg-4">
                    <div class="tracker-table-card sticky-top" style="padding: 1.5rem; top: 2rem; z-index: 1;">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-check2-circle" style="color: #f59e0b;"></i> Summary
                        </h3>
                        
                        <div class="alert alert-light border mb-4" style="background: #f8fafc;">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill text-primary mt-1"></i>
                                <small class="text-muted">Review all details before submitting. Once issued, a unique slip will be generated.</small>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary-custom w-100 justify-content-center py-2 mb-3">
                            <i class="bi bi-check-lg"></i>
                            <span>Issue Package</span>
                        </button>

                        <a href="{{ route('packages.index') }}" class="btn-secondary-custom w-100 justify-content-center text-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            /* File Upload (Create only) */
            .file-upload-wrapper {
                position: relative;
                width: 100%;
                min-height: 200px;
                border: 2px dashed var(--border);
                border-radius: 12px;
                background: #fafafa;
                transition: all 0.2s;
                cursor: pointer;
            }

            .file-upload-wrapper:hover {
                border-color: var(--primary);
                background: #f0fdfa;
            }

            .file-upload-input {
                position: absolute;
                width: 100%;
                height: 100%;
                opacity: 0;
                cursor: pointer;
                z-index: 2;
            }

            .file-upload-placeholder {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                width: 100%;
            }

            .upload-icon {
                font-size: 2.5rem;
                color: var(--gray);
                margin-bottom: 0.5rem;
            }

            .upload-text {
                font-weight: 600;
                color: var(--dark);
                margin-bottom: 0.25rem;
            }
            .upload-hint {
                color: #94a3b8;
                font-size: 0.85rem;
            }

            .image-preview-container {
                position: relative;
                width: 100%;
                height: 200px;
                border-radius: 10px;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #000;
            }

            #imagePreview {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }

            .remove-image-btn {
                position: absolute;
                top: 10px;
                right: 10px;
                background: rgba(239, 68, 68, 0.9);
                color: white;
                border: none;
                border-radius: 50%;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 3;
                transition: all 0.2s;
            }

            .remove-image-btn:hover {
                transform: scale(1.1);
            }

            [data-theme="dark"] .file-upload-wrapper {
                background: rgba(15, 23, 42, 0.45);
                border-color: rgba(148, 163, 184, 0.34);
            }

            [data-theme="dark"] .file-upload-wrapper:hover {
                background: rgba(99, 102, 241, 0.08);
                border-color: rgba(129, 140, 248, 0.55);
            }
            [data-theme="dark"] .upload-text {
                color: var(--text-primary, #f1f5f9);
            }
            [data-theme="dark"] .upload-hint {
                color: var(--text-secondary, #94a3b8);
            }
            [data-theme="dark"] .alert-light {
                background: var(--bg-card, #1e293b) !important;
                border-color: rgba(148, 163, 184, 0.34) !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function previewImage(input) {
                const previewContainer = document.getElementById('imagePreviewContainer');
                const placeholder = document.getElementById('uploadPlaceholder');
                const previewImage = document.getElementById('imagePreview');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewContainer.style.display = 'flex';
                        placeholder.style.display = 'none';
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function removeImage() {
                const input = document.getElementById('package_image');
                const previewContainer = document.getElementById('imagePreviewContainer');
                const placeholder = document.getElementById('uploadPlaceholder');
                const previewImage = document.getElementById('imagePreview');
                
                input.value = '';
                previewImage.src = '';
                previewContainer.style.display = 'none';
                placeholder.style.display = 'block';
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Stock Lookup Logic
                const btnLookup = document.getElementById('btnLookupStock');
                const stockInput = document.getElementById('stock_id');
                const messageEl = document.getElementById('stockLookupMessage');
                
                if(btnLookup) {
                    btnLookup.addEventListener('click', function() {
                        const stockId = stockInput.value.trim();
                        
                        if(!stockId) {
                            messageEl.innerHTML = '<span class="text-danger">Please enter a stock ID</span>';
                            return;
                        }
                        
                        messageEl.innerHTML = '<span class="text-primary"><i class="bi bi-hourglass-split"></i> Fetching details...</span>';
                        
                        // Need the correct endpoint URL here
                        fetch(`/admin/diamonds/lookup-by-sku/${encodeURIComponent(stockId)}`)
                            .then(response => {
                                if(!response.ok) {
                                    throw new Error('Stock not found');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if(data.success && data.diamond) {
                                    document.getElementById('diamond_shape').value = data.diamond.shape || '';
                                    document.getElementById('diamond_size').value = data.diamond.size || '';
                                    document.getElementById('diamond_color').value = data.diamond.color || '';
                                    document.getElementById('diamond_clarity').value = data.diamond.clarity || '';
                                    document.getElementById('diamond_carat').value = data.diamond.carat_weight || '';
                                    
                                    messageEl.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Details fetched successfully</span>';
                                } else {
                                    messageEl.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> Stock not found</span>';
                                    clearDiamondFields();
                                }
                            })
                            .catch(error => {
                                messageEl.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> ' + error.message + '</span>';
                                clearDiamondFields();
                            });
                    });
                }
                
                function clearDiamondFields() {
                    document.getElementById('diamond_shape').value = '';
                    document.getElementById('diamond_size').value = '';
                    document.getElementById('diamond_color').value = '';
                    document.getElementById('diamond_clarity').value = '';
                    document.getElementById('diamond_carat').value = '';
                }
                
                // Form submission loading state
                const form = document.getElementById('packageForm');
                if(form) {
                    form.addEventListener('submit', function() {
                        const btn = this.querySelector('button[type="submit"]');
                        if(btn) {
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
                            btn.disabled = true;
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
