@extends('layouts.admin')

@section('title', 'Issue New Package')

@section('content')
    <div class="packages-management-container">
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
                        <i class="bi bi-plus-circle-fill"></i>
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
                    <div class="form-section-card mb-4">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="section-title-wrapper">
                                <h5 class="section-title">Package Information</h5>
                                <p class="section-description">Enter the details of the package being issued</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="mb-4">
                                <label for="package_description" class="form-label-custom">Package Description <span
                                        class="text-danger">*</span></label>
                                <textarea name="package_description" id="package_description" rows="4"
                                    class="form-control form-control-custom"
                                    placeholder="Describe the contents of the package..."
                                    required>{{ old('package_description') }}</textarea>
                                <div class="form-text text-muted"><i class="bi bi-info-circle me-1"></i> Provide a detailed
                                    description of items.</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="slip_id" class="form-label-custom">Slip ID <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-hash"></i></span>
                                        <input type="text" name="slip_id" id="slip_id"
                                            class="form-control form-control-custom border-start-0 ps-0"
                                            placeholder="e.g. SLIP-2024-001" value="{{ old('slip_id') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="issue_date" class="form-label-custom">Issue Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="issue_date" id="issue_date"
                                        class="form-control form-control-custom"
                                        value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="issue_time" class="form-label-custom">Issue Time <span
                                            class="text-danger">*</span></label>
                                    <input type="time" name="issue_time" id="issue_time"
                                        class="form-control form-control-custom"
                                        value="{{ old('issue_time', date('H:i')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="return_date" class="form-label-custom">Expected Return Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="return_date" id="return_date"
                                        class="form-control form-control-custom" value="{{ old('return_date') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Details Card -->
                    <div class="form-section-card">
                        <div class="section-header">
                            <div class="section-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="section-title-wrapper">
                                <h5 class="section-title">Recipient Details</h5>
                                <p class="section-description">Who is receiving this package?</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="person_name" class="form-label-custom">Full Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="person_name" id="person_name"
                                        class="form-control form-control-custom" placeholder="John Doe"
                                        value="{{ old('person_name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="mobile_number" class="form-label-custom">Mobile Number <span
                                            class="text-danger">*</span></label>
                                    <input type="tel" name="mobile_number" id="mobile_number"
                                        class="form-control form-control-custom" placeholder="+1234567890"
                                        value="{{ old('mobile_number') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="package_image" class="form-label-custom">Recipient Photo / ID Proof</label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="package_image" id="package_image" class="file-upload-input"
                                        accept="image/*" onchange="previewImage(this)">
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
                </div>

                <!-- Right Column: Summary & Actions -->
                <div class="col-lg-4">
                    <div class="form-section-card sticky-top" style="top: 2rem; z-index: 1;">
                        <div class="section-header">
                            <div class="section-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <div class="section-title-wrapper">
                                <h5 class="section-title">Summary</h5>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="alert alert-light border mb-4">
                                <div class="d-flex gap-2">
                                    <i class="bi bi-info-circle-fill text-primary mt-1"></i>
                                    <small class="text-muted">Review all details before submitting. Once issued, a unique
                                        slip will be generated.</small>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary-custom w-100 justify-content-center py-3 mb-3">
                                <i class="bi bi-check-lg"></i>
                                <span>Issue Package</span>
                            </button>

                            <a href="{{ route('packages.index') }}"
                                class="btn-secondary-custom w-100 justify-content-center text-center">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            :root {
                --primary: #6366f1;
                --primary-dark: #4f46e5;
                --secondary: #64748b;
                --success: #10b981;
                --warning: #f59e0b;
                --danger: #ef4444;
                --dark: #1e293b;
                --gray: #64748b;
                --light-gray: #f8fafc;
                --border: #e2e8f0;
            }

            .packages-management-container {
                padding: 1.5rem;
                max-width: 1200px;
                margin: 0 auto;
            }

            /* Page Header */
            .page-header {
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
                padding: 2rem;
                border-radius: 16px;
                border: 2px solid rgba(99, 102, 241, 0.1);
            }

            .header-content {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .breadcrumb-nav {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.85rem;
                color: var(--gray);
                margin-bottom: 0.5rem;
            }

            .breadcrumb-link {
                color: var(--gray);
                text-decoration: none;
                transition: color 0.2s;
            }

            .breadcrumb-link:hover {
                color: var(--primary);
            }

            .breadcrumb-separator {
                font-size: 0.7rem;
                color: #cbd5e1;
            }

            .breadcrumb-current {
                color: var(--primary);
                font-weight: 600;
            }

            .page-title {
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .page-title i {
                color: var(--primary);
            }

            .page-subtitle {
                color: var(--gray);
                font-size: 0.95rem;
                margin: 0.25rem 0 0 0;
            }

            /* Form Cards */
            .form-section-card {
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                overflow: hidden;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                height: 100%;
            }

            .section-header {
                padding: 1.25rem 1.5rem;
                background: #f8fafc;
                border-bottom: 2px solid var(--border);
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
                font-size: 1.5rem;
                flex-shrink: 0;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .section-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
            }

            .section-description {
                font-size: 0.85rem;
                color: var(--gray);
                margin: 0;
            }

            .section-body {
                padding: 1.5rem;
            }

            /* Form Controls */
            .form-label-custom {
                font-weight: 600;
                color: var(--dark);
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
                display: block;
            }

            .form-control-custom {
                padding: 0.75rem 1rem;
                border-radius: 10px;
                border: 2px solid var(--border);
                font-size: 0.95rem;
                transition: all 0.2s;
            }

            .form-control-custom:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }

            .input-group-text {
                border: 2px solid var(--border);
                border-radius: 10px;
            }

            /* File Upload */
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

            /* Buttons */
            .btn-primary-custom,
            .btn-secondary-custom {
                padding: 0.65rem 1.25rem;
                border-radius: 12px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                text-decoration: none;
                transition: all 0.2s;
                border: none;
                cursor: pointer;
            }

            .btn-primary-custom {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
            }

            .btn-primary-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
                color: white;
            }

            .btn-secondary-custom {
                background: white;
                color: var(--gray);
                border: 2px solid var(--border);
            }

            .btn-secondary-custom:hover {
                border-color: var(--dark);
                color: var(--dark);
                background: var(--light-gray);
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

                    reader.onload = function (e) {
                        previewImage.src = e.target.result;
                        placeholder.style.display = 'none';
                        previewContainer.style.display = 'flex';
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            function removeImage() {
                const input = document.getElementById('package_image');
                input.value = '';

                document.getElementById('imagePreviewContainer').style.display = 'none';
                document.getElementById('uploadPlaceholder').style.display = 'block';
            }
        </script>
    @endpush
@endsection