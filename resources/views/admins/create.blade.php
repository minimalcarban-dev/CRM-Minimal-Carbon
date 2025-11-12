@extends('layouts.admin')

@section('title', 'Create Admin')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="page-title mb-1">
                        <i class="bi bi-person-plus-fill me-2"></i>
                        Create New Admin
                    </h2>
                    <p class="page-subtitle mb-0">Add a new administrator to the system</p>
                </div>
                <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Admins
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('admins.store') }}" enctype="multipart/form-data" id="createAdminForm">
            @csrf

            <!-- Basic Information Card -->
            <div class="form-section-card mb-4">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Basic Information</h5>
                            <p class="section-description">Personal details and contact information</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-person me-2"></i>Full Name
                                    <span class="required-badge">Required</span>
                                </label>
                                <input type="text" name="name" class="form-control-modern" value="{{ old('name') }}"
                                    placeholder="Enter full name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-envelope me-2"></i>Email Address
                                    <span class="required-badge">Required</span>
                                </label>
                                <input type="email" name="email" class="form-control-modern" value="{{ old('email') }}"
                                    placeholder="admin@example.com" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="form-section-card mb-4">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Security Credentials</h5>
                            <p class="section-description">Set password and authentication details</p>
                        </div>
                    </div>
                    <button type="button" id="suggestPw" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-stars me-2"></i>Generate Strong Password
                    </button>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-key me-2"></i>Password
                                    <span class="required-badge">Required</span>
                                </label>
                                <div class="password-input-wrapper">
                                    <input id="password" type="password" name="password" class="form-control-modern"
                                        placeholder="Enter secure password" required>
                                    <button type="button" id="togglePassword" class="password-toggle-btn"
                                        title="Show/Hide password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength-wrapper mt-3">
                                    <div class="strength-bar-container">
                                        <div id="pw-strength-bar" class="strength-bar"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small id="pw-strength-text" class="strength-text"></small>
                                        <small class="text-muted">Min. 8 characters</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-check-circle me-2"></i>Confirm Password
                                    <span class="required-badge">Required</span>
                                </label>
                                <input id="confirm_password" type="password" name="confirm_password"
                                    class="form-control-modern" placeholder="Re-enter password" required>
                                <div id="pw-match" class="password-match-indicator mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="form-section-card mb-4">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Contact Information</h5>
                            <p class="section-description">Phone number and contact details</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-flag me-2"></i>Country Code
                                </label>
                                <input type="text" name="country_code" class="form-control-modern"
                                    value="{{ old('country_code') }}" placeholder="+91">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-phone me-2"></i>Phone Number
                                    <span class="required-badge">Required</span>
                                </label>
                                <input type="text" name="phone_number" maxlength="10" class="form-control-modern"
                                    value="{{ old('phone_number') }}" placeholder="1234567890" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-mailbox me-2"></i>Pincode
                                </label>
                                <input type="text" name="pincode" maxlength="6" class="form-control-modern"
                                    value="{{ old('pincode') }}" placeholder="380001">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information Card -->
            <div class="form-section-card mb-4">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Address Details</h5>
                            <p class="section-description">Location and address information</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-globe me-2"></i>Country
                                </label>
                                <input type="text" name="country" class="form-control-modern"
                                    value="{{ old('country') }}" placeholder="India">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-map me-2"></i>State
                                </label>
                                <input type="text" name="state" class="form-control-modern"
                                    value="{{ old('state') }}" placeholder="Gujarat">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-building me-2"></i>City
                                </label>
                                <input type="text" name="city" class="form-control-modern"
                                    value="{{ old('city') }}" placeholder="Ahmedabad">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-house me-2"></i>Full Address
                                </label>
                                <textarea name="address" class="form-control-modern" rows="3" placeholder="Enter complete address">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Upload Card -->
            <div class="form-section-card mb-4">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Document Verification</h5>
                            <p class="section-description">Upload identity and bank documents (JPG/PNG only)</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="file-upload-modern">
                                <input type="file" name="aadhar_front_image" id="aadhar_front"
                                    class="file-input-modern" accept="image/jpeg,image/png">
                                <label for="aadhar_front" class="file-upload-label">
                                    <div class="file-upload-icon">
                                        <i class="bi bi-card-image"></i>
                                    </div>
                                    <div class="file-upload-text">
                                        <span class="file-upload-title">Aadhar Front</span>
                                        <span class="file-upload-subtitle">Click to upload</span>
                                    </div>
                                </label>
                                <div class="file-preview" id="preview_aadhar_front"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="file-upload-modern">
                                <input type="file" name="aadhar_back_image" id="aadhar_back"
                                    class="file-input-modern" accept="image/jpeg,image/png">
                                <label for="aadhar_back" class="file-upload-label">
                                    <div class="file-upload-icon">
                                        <i class="bi bi-card-image"></i>
                                    </div>
                                    <div class="file-upload-text">
                                        <span class="file-upload-title">Aadhar Back</span>
                                        <span class="file-upload-subtitle">Click to upload</span>
                                    </div>
                                </label>
                                <div class="file-preview" id="preview_aadhar_back"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="file-upload-modern">
                                <input type="file" name="bank_passbook_image" id="bank_passbook"
                                    class="file-input-modern" accept="image/jpeg,image/png">
                                <label for="bank_passbook" class="file-upload-label">
                                    <div class="file-upload-icon">
                                        <i class="bi bi-bank"></i>
                                    </div>
                                    <div class="file-upload-text">
                                        <span class="file-upload-title">Bank Passbook</span>
                                        <span class="file-upload-subtitle">Click to upload</span>
                                    </div>
                                </label>
                                <div class="file-preview" id="preview_bank_passbook"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Footer -->
            <div class="action-footer">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle me-2"></i>
                    Create Admin
                </button>
                <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle me-2"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            :root {
                --primary: #6366f1;
                --primary-dark: #4f46e5;
                --dark: #1e293b;
                --gray: #64748b;
                --light-gray: #f8fafc;
                --border: #e2e8f0;
                --danger: #ef4444;
                --warning: #f59e0b;
                --success: #10b981;
            }

            /* Page Header */
            .page-header {
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
                padding: 2rem;
                border-radius: 16px;
                border: 2px solid rgba(99, 102, 241, 0.1);
            }

            .page-title {
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
            }

            .page-subtitle {
                color: var(--gray);
                font-size: 0.95rem;
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
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                flex-wrap: wrap;
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

            /* Modern Form Controls */
            .form-group-modern {
                position: relative;
            }

            .form-label-modern {
                display: flex;
                align-items: center;
                font-weight: 600;
                color: var(--dark);
                margin-bottom: 0.75rem;
                font-size: 0.95rem;
            }

            .required-badge {
                background: linear-gradient(135deg, var(--danger), #dc2626);
                color: white;
                font-size: 0.7rem;
                padding: 0.125rem 0.5rem;
                border-radius: 4px;
                margin-left: auto;
                font-weight: 600;
                letter-spacing: 0.3px;
            }

            .form-control-modern {
                width: 100%;
                padding: 0.875rem 1rem;
                border: 2px solid var(--border);
                border-radius: 12px;
                font-size: 0.95rem;
                transition: all 0.3s ease;
                background: white;
            }

            .form-control-modern:focus {
                outline: none;
                border-color: var(--primary);
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }

            .form-control-modern::placeholder {
                color: #cbd5e1;
            }

            /* Password Input */
            .password-input-wrapper {
                position: relative;
            }

            .password-toggle-btn {
                position: absolute;
                right: 0.75rem;
                top: 50%;
                transform: translateY(-50%);
                background: var(--light-gray);
                border: none;
                width: 36px;
                height: 36px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
                color: var(--gray);
                font-size: 1.1rem;
            }

            .password-toggle-btn:hover {
                background: var(--primary);
                color: white;
            }

            /* Password Strength */
            .password-strength-wrapper {
                padding: 1rem;
                background: var(--light-gray);
                border-radius: 12px;
                border: 2px solid var(--border);
            }

            .strength-bar-container {
                height: 8px;
                background: #e2e8f0;
                border-radius: 4px;
                overflow: hidden;
            }

            .strength-bar {
                height: 100%;
                width: 0;
                transition: all 0.3s ease;
                border-radius: 4px;
            }

            .strength-bar.weak {
                width: 40%;
                background: linear-gradient(90deg, var(--danger), #dc2626);
            }

            .strength-bar.medium {
                width: 70%;
                background: linear-gradient(90deg, var(--warning), #f59e0b);
            }

            .strength-bar.strong {
                width: 100%;
                background: linear-gradient(90deg, var(--success), #059669);
            }

            .strength-text {
                font-weight: 600;
                font-size: 0.875rem;
            }

            .strength-text.weak {
                color: var(--danger);
            }

            .strength-text.medium {
                color: var(--warning);
            }

            .strength-text.strong {
                color: var(--success);
            }

            /* Password Match Indicator */
            .password-match-indicator {
                padding: 0.75rem;
                border-radius: 8px;
                font-size: 0.875rem;
                font-weight: 600;
                display: none;
            }

            .password-match-indicator.match {
                display: block;
                background: rgba(16, 185, 129, 0.1);
                color: var(--success);
                border: 2px solid rgba(16, 185, 129, 0.2);
            }

            .password-match-indicator.no-match {
                display: block;
                background: rgba(239, 68, 68, 0.1);
                color: var(--danger);
                border: 2px solid rgba(239, 68, 68, 0.2);
            }

            /* File Upload Modern */
            .file-upload-modern {
                position: relative;
            }

            .file-input-modern {
                display: none;
            }

            .file-upload-label {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem;
                border: 2px dashed var(--border);
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                background: var(--light-gray);
                min-height: 160px;
            }

            .file-upload-label:hover {
                border-color: var(--primary);
                background: rgba(99, 102, 241, 0.05);
            }

            .file-upload-icon {
                width: 56px;
                height: 56px;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }

            .file-upload-text {
                text-align: center;
            }

            .file-upload-title {
                display: block;
                font-weight: 600;
                color: var(--dark);
                font-size: 0.95rem;
                margin-bottom: 0.25rem;
            }

            .file-upload-subtitle {
                display: block;
                font-size: 0.8rem;
                color: var(--gray);
            }

            .file-preview {
                margin-top: 1rem;
                display: none;
            }

            .file-preview.active {
                display: block;
            }

            .file-preview img {
                width: 100%;
                height: 120px;
                object-fit: cover;
                border-radius: 8px;
                border: 2px solid var(--border);
            }

            .file-preview-info {
                margin-top: 0.5rem;
                padding: 0.75rem;
                background: var(--light-gray);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
            }

            .file-preview-name {
                font-size: 0.85rem;
                color: var(--dark);
                font-weight: 500;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                flex: 1;
            }

            .file-remove-btn {
                background: var(--danger);
                color: white;
                border: none;
                width: 28px;
                height: 28px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
                flex-shrink: 0;
            }

            .file-remove-btn:hover {
                background: #dc2626;
            }

            /* Action Footer */
            .action-footer {
                margin-top: 2rem;
                padding: 1.5rem 2rem;
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 1rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            }

            .action-footer .btn-lg {
                padding: 0.875rem 2rem;
                font-weight: 600;
                border-radius: 12px;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .section-header {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .section-body {
                    padding: 1.5rem;
                }

                .action-footer {
                    flex-direction: column;
                }

                .action-footer .btn-lg {
                    width: 100%;
                }

                .page-header {
                    padding: 1.5rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                function scorePassword(pass) {
                    var score = 0;
                    if (!pass) return 0;
                    if (pass.length >= 8) score += 1;
                    if (/[a-z]/.test(pass)) score += 1;
                    if (/[A-Z]/.test(pass)) score += 1;
                    if (/[0-9]/.test(pass)) score += 1;
                    if (/[^a-zA-Z0-9]/.test(pass)) score += 1;
                    return score;
                }

                function initCreateAdminForm() {
                    var pw = document.getElementById('password');
                    var cpw = document.getElementById('confirm_password');
                    var bar = document.getElementById('pw-strength-bar');
                    var txt = document.getElementById('pw-strength-text');
                    var matchEl = document.getElementById('pw-match');
                    var suggestBtn = document.getElementById('suggestPw');
                    var toggleBtn = document.getElementById('togglePassword');

                    // Password strength
                    if (pw && bar && txt) {
                        const updateStrength = function() {
                            var s = scorePassword(pw.value);
                            bar.className = 'strength-bar';
                            txt.className = 'strength-text';

                            if (s <= 2) {
                                bar.classList.add('weak');
                                txt.classList.add('weak');
                                txt.textContent = 'Weak password';
                            } else if (s <= 4) {
                                bar.classList.add('medium');
                                txt.classList.add('medium');
                                txt.textContent = 'Medium strength';
                            } else {
                                bar.classList.add('strong');
                                txt.classList.add('strong');
                                txt.textContent = 'Strong password';
                            }
                        };
                        pw.addEventListener('input', updateStrength);
                        updateStrength();
                    }

                    // Password match
                    if (cpw && matchEl && pw) {
                        const checkMatch = function() {
                            var p = pw.value;
                            var c = cpw.value;
                            matchEl.className = 'password-match-indicator';

                            if (!c) {
                                matchEl.style.display = 'none';
                                return;
                            }

                            if (p === c) {
                                matchEl.classList.add('match');
                                matchEl.innerHTML = '<i class="bi bi-check-circle me-2"></i>Passwords match';
                            } else {
                                matchEl.classList.add('no-match');
                                matchEl.innerHTML = '<i class="bi bi-x-circle me-2"></i>Passwords do not match';
                            }
                        };
                        cpw.addEventListener('input', checkMatch);
                        pw.addEventListener('input', checkMatch);
                    }

                    // Suggest password
                    if (suggestBtn && pw && cpw) {
                        suggestBtn.addEventListener('click', function() {
                            var chars =
                                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,./<>?';
                            var out = '';
                            for (var i = 0; i < 12; i++) out += chars.charAt(Math.floor(Math.random() * chars
                                .length));
                            pw.value = out;
                            cpw.value = out;
                            pw.dispatchEvent(new Event('input'));
                            cpw.dispatchEvent(new Event('input'));

                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText(out).then(function() {
                                    showToastNotification('Password copied to clipboard', 'success');
                                }).catch(function() {
                                    showToastNotification('Password generated', 'success');
                                });
                            } else {
                                showToastNotification('Password generated', 'success');
                            }
                        });
                    }

                    // Toggle password visibility
                    if (toggleBtn && pw) {
                        toggleBtn.addEventListener('click', function() {
                            var icon = toggleBtn.querySelector('i');
                            if (pw.type === 'password') {
                                pw.type = 'text';
                                icon.className = 'bi bi-eye-slash';
                            } else {
                                pw.type = 'password';
                                icon.className = 'bi bi-eye';
                            }

                            if (pw.value && navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText(pw.value).then(function() {
                                    showToastNotification('Password copied to clipboard', 'success');
                                }).catch(function() {});
                            }
                        });
                    }

                    // File upload previews
                    ['aadhar_front', 'aadhar_back', 'bank_passbook'].forEach(function(id) {
                        var input = document.getElementById(id);
                        var preview = document.getElementById('preview_' + id);

                        if (input && preview) {
                            input.addEventListener('change', function(e) {
                                var file = e.target.files[0];
                                if (file) {
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        preview.className = 'file-preview active';
                                        preview.innerHTML =
                                            '<img src="' + e.target.result + '" alt="Preview">' +
                                            '<div class="file-preview-info">' +
                                            '<span class="file-preview-name"><i class="bi bi-file-image me-2"></i>' +
                                            file.name + '</span>' +
                                            '<button type="button" class="file-remove-btn" data-target="' +
                                            id + '">' +
                                            '<i class="bi bi-x"></i>' +
                                            '</button>' +
                                            '</div>';

                                        // Add remove handler
                                        var removeBtn = preview.querySelector('.file-remove-btn');
                                        if (removeBtn) {
                                            removeBtn.addEventListener('click', function() {
                                                input.value = '';
                                                preview.className = 'file-preview';
                                                preview.innerHTML = '';
                                            });
                                        }
                                    };
                                    reader.readAsDataURL(file);
                                }
                            });
                        }
                    });
                }

                // Toast notification helper
                function showToastNotification(message, type) {
                    if (typeof showToast === 'function') {
                        showToast(message);
                    } else {
                        // Fallback toast
                        var toast = document.createElement('div');
                        toast.style.cssText = 'position:fixed;top:20px;right:20px;background:' +
                            (type === 'success' ? '#10b981' : '#6366f1') +
                            ';color:white;padding:1rem 1.5rem;border-radius:12px;' +
                            'box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;' +
                            'font-weight:600;animation:slideIn 0.3s ease;';
                        toast.textContent = message;
                        document.body.appendChild(toast);

                        setTimeout(function() {
                            toast.style.animation = 'slideOut 0.3s ease';
                            setTimeout(function() {
                                document.body.removeChild(toast);
                            }, 300);
                        }, 3000);
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initCreateAdminForm);
                } else {
                    initCreateAdminForm();
                }
            })();
        </script>
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
    @endpush
@endsection
