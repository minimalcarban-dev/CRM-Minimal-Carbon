@extends('layouts.admin')

@section('title', 'Admin Details')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="page-title mb-1">
                        <i class="bi bi-person-badge me-2"></i>
                        Admin Details
                    </h2>
                    <p class="page-subtitle mb-0">View complete administrator profile and information</p>
                </div>
                <div class="header-actions">
                    @if (isset($currentAdmin) && ($currentAdmin->is_super || $currentAdmin->hasPermission('admins.edit')))
                        <a href="{{ route('admins.edit', $admin) }}" class="btn btn-primary">
                            <i class="bi bi-pencil-square me-2"></i>Edit Profile
                        </a>
                    @endif
                    @if (isset($currentAdmin) && ($currentAdmin->is_super || $currentAdmin->hasPermission('admins.delete')))
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="bi bi-trash me-2"></i>Delete
                        </button>
                    @endif
                    <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Admin Profile Card -->
        <div class="profile-overview-card mb-4">
            <div class="profile-header">
                <div class="profile-avatar-large">
                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                </div>
                <div class="profile-info">
                    <h3 class="profile-name">{{ $admin->name }}</h3>
                    <p class="profile-email">
                        <i class="bi bi-envelope me-2"></i>{{ $admin->email }}
                    </p>
                    <div class="profile-meta">
                        <span class="meta-badge">
                            <i class="bi bi-hash"></i>
                            ID: {{ $admin->id }}
                        </span>
                        @if ($admin->is_super)
                            <span class="meta-badge super-admin">
                                <i class="bi bi-star-fill"></i>
                                Super Admin
                            </span>
                        @endif
                        <span class="meta-badge">
                            <i class="bi bi-calendar-check"></i>
                            Joined {{ $admin->created_at ? $admin->created_at->format('M d, Y') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Contact Information -->
            <div class="col-lg-6">
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <h5 class="info-title">Contact Information</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-phone me-2"></i>Phone Number
                            </div>
                            <div class="info-value">
                                {{ $admin->country_code }} {{ $admin->phone_number }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-person-lines-fill me-2"></i>Family Member Phone
                            </div>
                            <div class="info-value">
                                {{ $admin->family_member_phone ?: 'Not provided' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-envelope me-2"></i>Email Address
                            </div>
                            <div class="info-value">
                                {{ $admin->email }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="col-lg-6">
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h5 class="info-title">Location Details</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-building me-2"></i>City
                            </div>
                            <div class="info-value">
                                {{ $admin->city ?: 'Not provided' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-map me-2"></i>State & Country
                            </div>
                            <div class="info-value">
                                {{ $admin->state ?: 'N/A' }}, {{ $admin->country ?: 'N/A' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-mailbox me-2"></i>Pincode
                            </div>
                            <div class="info-value">
                                {{ $admin->pincode ?: 'Not provided' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full Address -->
            <div class="col-12">
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="bi bi-house-fill"></i>
                        </div>
                        <h5 class="info-title">Complete Address</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="address-box">
                            <i class="bi bi-geo-alt address-icon"></i>
                            <p class="address-text">
                                {{ $admin->address ?: 'No address provided' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Verification -->
            <div class="col-12">
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <h5 class="info-title">Verification Documents</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="row g-4">
                            <!-- Aadhar Front -->
                            <div class="col-md-4">
                                <div class="document-card">
                                    <div class="document-header">
                                        <i class="bi bi-card-image"></i>
                                        <span>Aadhar Front</span>
                                    </div>
                                    <div class="document-preview">
                                        @if ($admin->aadhar_front_image)
                                            <img src="{{ Storage::url($admin->aadhar_front_image) }}" alt="Aadhar Front"
                                                class="document-image" onclick="openImageModal(this.src, 'Aadhar Front')" />
                                            <div class="document-overlay">
                                                <i class="bi bi-eye"></i>
                                                <span>Click to view</span>
                                            </div>
                                        @else
                                            <div class="document-empty">
                                                <i class="bi bi-file-x"></i>
                                                <span>Not uploaded</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Aadhar Back -->
                            <div class="col-md-4">
                                <div class="document-card">
                                    <div class="document-header">
                                        <i class="bi bi-card-image"></i>
                                        <span>Aadhar Back</span>
                                    </div>
                                    <div class="document-preview">
                                        @if ($admin->aadhar_back_image)
                                            <img src="{{ Storage::url($admin->aadhar_back_image) }}" alt="Aadhar Back"
                                                class="document-image" onclick="openImageModal(this.src, 'Aadhar Back')" />
                                            <div class="document-overlay">
                                                <i class="bi bi-eye"></i>
                                                <span>Click to view</span>
                                            </div>
                                        @else
                                            <div class="document-empty">
                                                <i class="bi bi-file-x"></i>
                                                <span>Not uploaded</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Passbook -->
                            <div class="col-md-4">
                                <div class="document-card">
                                    <div class="document-header">
                                        <i class="bi bi-bank"></i>
                                        <span>Bank Passbook</span>
                                    </div>
                                    <div class="document-preview">
                                        @if ($admin->bank_passbook_image)
                                            <img src="{{ Storage::url($admin->bank_passbook_image) }}" alt="Bank Passbook"
                                                class="document-image" onclick="openImageModal(this.src, 'Bank Passbook')" />
                                            <div class="document-overlay">
                                                <i class="bi bi-eye"></i>
                                                <span>Click to view</span>
                                            </div>
                                        @else
                                            <div class="document-empty">
                                                <i class="bi bi-file-x"></i>
                                                <span>Not uploaded</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <div class="image-modal" id="imageModal" onclick="closeImageModal()">
            <div class="image-modal-content">
                <button class="image-modal-close" onclick="closeImageModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
                <h4 class="image-modal-title" id="modalTitle"></h4>
                <img src="" alt="" id="modalImage" />
            </div>
        </div>

        <!-- Delete Confirmation Form (Hidden) -->
        @if (isset($currentAdmin) && ($currentAdmin->is_super || $currentAdmin->hasPermission('admins.delete')))
            <form method="POST" action="{{ route('admins.destroy', $admin) }}" id="deleteForm" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endif
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
                --success: #10b981;
                --warning: #f59e0b;
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

            .header-actions {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            /* Profile Overview Card */
            .profile-overview-card {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                border-radius: 16px;
                padding: 2.5rem;
                color: white;
                box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
                position: relative;
                overflow: hidden;
            }

            .profile-overview-card::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -20%;
                width: 400px;
                height: 400px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            }

            .profile-header {
                display: flex;
                align-items: center;
                gap: 2rem;
                position: relative;
                z-index: 1;
            }

            .profile-avatar-large {
                width: 120px;
                height: 120px;
                border-radius: 20px;
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(10px);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2.5rem;
                font-weight: 700;
                flex-shrink: 0;
                border: 3px solid rgba(255, 255, 255, 0.3);
            }

            .profile-info {
                flex: 1;
            }

            .profile-name {
                font-size: 2rem;
                font-weight: 700;
                margin: 0 0 0.5rem 0;
                color: white;
            }

            .profile-email {
                font-size: 1.1rem;
                margin: 0 0 1rem 0;
                opacity: 0.95;
                display: flex;
                align-items: center;
            }

            .profile-meta {
                display: flex;
                align-items: center;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .meta-badge {
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(10px);
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-size: 0.875rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                border: 1px solid rgba(255, 255, 255, 0.3);
            }

            .meta-badge.super-admin {
                background: linear-gradient(135deg, #fbbf24, #f59e0b);
                border-color: rgba(251, 191, 36, 0.95);
                color: #1f2937;
                font-weight: 800;
                box-shadow: 0 0 0 1px rgba(146, 64, 14, 0.3), 0 8px 20px rgba(245, 158, 11, 0.45);
                position: relative;
                overflow: hidden;
            }

            .meta-badge.super-admin::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(120deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0));
                pointer-events: none;
            }

            .meta-badge.super-admin i {
                color: #92400e;
                filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.4));
            }

            /* Info Card */
            .info-card {
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                overflow: hidden;
                transition: all 0.3s ease;
                height: 100%;
            }

            .info-card:hover {
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
                transform: translateY(-2px);
            }

            .info-card-header {
                padding: 1.5rem;
                background: linear-gradient(135deg, var(--light-gray), white);
                border-bottom: 2px solid var(--border);
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .info-icon {
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

            .info-title {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
            }

            .info-card-body {
                padding: 1.5rem;
            }

            .info-item {
                display: flex;
                justify-content: space-between;
                align-items: start;
                padding: 1rem;
                border-radius: 12px;
                background: var(--light-gray);
                margin-bottom: 1rem;
                border: 2px solid var(--border);
                transition: all 0.2s;
            }

            .info-item:last-child {
                margin-bottom: 0;
            }

            .info-item:hover {
                background: white;
                border-color: var(--primary);
            }

            .info-label {
                font-size: 0.875rem;
                color: var(--gray);
                font-weight: 600;
                display: flex;
                align-items: center;
            }

            .info-value {
                font-size: 0.95rem;
                color: var(--dark);
                font-weight: 600;
                text-align: right;
            }

            /* Address Box */
            .address-box {
                display: flex;
                gap: 1rem;
                padding: 1.5rem;
                background: var(--light-gray);
                border-radius: 12px;
                border: 2px solid var(--border);
            }

            .address-icon {
                font-size: 1.5rem;
                color: var(--primary);
                flex-shrink: 0;
            }

            .address-text {
                margin: 0;
                color: var(--dark);
                font-size: 0.95rem;
                line-height: 1.6;
            }

            /* Document Card */
            .document-card {
                background: white;
                border-radius: 12px;
                border: 2px solid var(--border);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .document-card:hover {
                border-color: var(--primary);
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
            }

            .document-header {
                padding: 1rem;
                background: var(--light-gray);
                border-bottom: 2px solid var(--border);
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-weight: 600;
                color: var(--dark);
                font-size: 0.95rem;
            }

            .document-preview {
                position: relative;
                aspect-ratio: 16/10;
                overflow: hidden;
                cursor: pointer;
            }

            .document-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }

            .document-preview:hover .document-image {
                transform: scale(1.05);
            }

            .document-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                opacity: 0;
                transition: opacity 0.3s ease;
                color: white;
            }

            .document-preview:hover .document-overlay {
                opacity: 1;
            }

            .document-overlay i {
                font-size: 2rem;
            }

            .document-overlay span {
                font-size: 0.875rem;
                font-weight: 600;
            }

            .document-empty {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100%;
                background: var(--light-gray);
                color: var(--gray);
                gap: 0.5rem;
            }

            .document-empty i {
                font-size: 2rem;
                opacity: 0.5;
            }

            .document-empty span {
                font-size: 0.875rem;
                font-weight: 500;
            }

            /* Image Modal */
            .image-modal {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.95);
                animation: fadeIn 0.3s ease;
            }

            .image-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .image-modal-content {
                position: relative;
                max-width: 90%;
                max-height: 90%;
                padding: 2rem;
            }

            .image-modal-close {
                position: absolute;
                top: 1rem;
                right: 1rem;
                background: white;
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 1.25rem;
                color: var(--dark);
                transition: all 0.2s;
                z-index: 10;
            }

            .image-modal-close:hover {
                background: var(--danger);
                color: white;
                transform: rotate(90deg);
            }

            .image-modal-title {
                color: white;
                text-align: center;
                margin-bottom: 1rem;
                font-size: 1.5rem;
                font-weight: 700;
            }

            #modalImage {
                max-width: 100%;
                max-height: 70vh;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            /* Responsive */
            @media (max-width: 1200px) {
                .profile-overview-card {
                    padding: 2rem;
                }

                .profile-name {
                    font-size: 1.8rem;
                }

                .profile-avatar-large {
                    width: 100px;
                    height: 100px;
                    font-size: 2.1rem;
                }
            }

            @media (max-width: 992px) {
                .header-actions {
                    width: 100%;
                    gap: 0.55rem;
                }

                .header-actions .btn,
                .header-actions button {
                    flex: 1 1 calc(50% - 0.55rem);
                    min-height: 42px;
                    justify-content: center;
                }

                .profile-overview-card {
                    padding: 1.6rem;
                }

                .profile-header {
                    gap: 1.2rem;
                }

                .profile-name {
                    font-size: 1.55rem;
                }

                .profile-email {
                    font-size: 1rem;
                    margin-bottom: 0.75rem;
                }

                .meta-badge {
                    font-size: 0.8rem;
                    padding: 0.45rem 0.8rem;
                }
            }

            @media (max-width: 768px) {
                .container-fluid {
                    padding-left: 0.25rem !important;
                    padding-right: 0.25rem !important;
                }

                .page-header,
                .profile-overview-card,
                .info-card {
                    border-radius: 12px;
                }

                .page-header {
                    padding: 1rem;
                }

                .profile-overview-card {
                    padding: 1rem;
                }

                .profile-header {
                    flex-direction: column;
                    align-items: flex-start;
                    text-align: left;
                    gap: 0.9rem;
                }

                .profile-name {
                    font-size: 1.25rem;
                    margin-bottom: 0.35rem;
                }

                .profile-email {
                    justify-content: flex-start;
                    font-size: 0.9rem;
                    margin-bottom: 0.6rem;
                    word-break: break-word;
                }

                .profile-meta {
                    gap: 0.45rem;
                }

                .meta-badge {
                    width: 100%;
                    justify-content: flex-start;
                }

                .info-card-header,
                .info-card-body {
                    padding: 0.9rem;
                }

                .info-item {
                    padding: 0.8rem;
                    margin-bottom: 0.65rem;
                    gap: 0.45rem;
                    flex-direction: column;
                }

                .info-value {
                    text-align: left;
                }

                .address-box {
                    padding: 0.9rem;
                    gap: 0.55rem;
                }

                .document-header {
                    padding: 0.8rem;
                    font-size: 0.86rem;
                }

                .image-modal-content {
                    padding: 0.9rem;
                }

                #modalImage {
                    max-height: 58vh;
                }
            }
            @media (max-width: 575px) {
                #mainContent {
                    margin-top: 84px;
                }
                .header-actions {
                    flex-direction: column;
                }
                .header-actions a, .header-actions button {
                    width: 100%;
                }
                .profile-overview-card {
                    padding: 10px;
                }
                #mainContent .profile-header {
                    gap: 10px;
                    padding: 0;
                    padding-bottom: 7px;
                }
                .profile-avatar-large{
                    width: 40px;
                    height: 40px;
                    font-size: 18px;
                }
                .profile-name {
                    font-size: 12px;
                }
                p.profile-email {
                    font-size: 12px;
                    margin-bottom: 12px;
                }
                .meta-badge {
                    padding: 5px;
                    font-size: 12px;
                    gap: 4px;
                    width: 100%;
                }
                .profile-meta {
                    gap: 5px;
                }
                .info-card-header, .info-card-body, .info-item {
                    padding: 10px;
                    gap: 5px;
                }
                .address-box {
                    padding: 10px;
                    align-items: center;
                    gap: 5px;
                }
                .info-label, .info-value {
                    font-size: 12px;
                }
                .info-icon {
                    width: 30px;
                    height: 30px;
                    font-size: 13px;
                }
                h5.info-title {
                    font-size: 13px;
                }
                #mainContent .container-fluid {
                    padding-left: 0;
                    padding-right: 0;
                }
            }

            /* Admin Module Dark Theme */
            [data-theme="dark"] .page-header,
            [data-theme="dark"] .info-card {
                background: var(--bg-card, #1e293b) !important;
                border-color: rgba(148, 163, 184, 0.3) !important;
                box-shadow: none !important;
            }

            [data-theme="dark"] .info-card-header {
                background: rgba(15, 23, 42, 0.5) !important;
                border-bottom-color: rgba(148, 163, 184, 0.25) !important;
            }

            [data-theme="dark"] .info-card-body {
                background: rgba(30, 41, 59, 0.35) !important;
            }

            [data-theme="dark"] .info-item {
                background: rgba(15, 23, 42, 0.62) !important;
                border-color: rgba(148, 163, 184, 0.28) !important;
            }

            [data-theme="dark"] .info-item:hover {
                background: rgba(30, 41, 59, 0.75) !important;
                border-color: rgba(99, 102, 241, 0.5) !important;
            }

            [data-theme="dark"] .page-title,
            [data-theme="dark"] .info-title,
            [data-theme="dark"] .info-label {
                color: var(--text-primary, #f1f5f9) !important;
            }

            [data-theme="dark"] .page-subtitle,
            [data-theme="dark"] .info-value,
            [data-theme="dark"] .address-text,
            [data-theme="dark"] .text-muted {
                color: var(--text-secondary, #94a3b8) !important;
            }

            [data-theme="dark"] .meta-badge,
            [data-theme="dark"] .address-box,
            [data-theme="dark"] .document-empty {
                background: rgba(15, 23, 42, 0.62) !important;
                border-color: rgba(148, 163, 184, 0.3) !important;
                color: var(--text-secondary, #cbd5e1) !important;
            }

            [data-theme="dark"] .meta-badge.super-admin {
                background: linear-gradient(135deg, #f59e0b, #d97706) !important;
                border-color: rgba(251, 191, 36, 0.9) !important;
                color: #fff7ed !important;
                box-shadow: 0 0 0 1px rgba(251, 191, 36, 0.28), 0 10px 24px rgba(245, 158, 11, 0.4) !important;
            }

            [data-theme="dark"] .meta-badge.super-admin i {
                color: #fde68a !important;
            }

            [data-theme="dark"] .document-card {
                background: rgba(30, 41, 59, 0.55) !important;
                border-color: rgba(148, 163, 184, 0.26) !important;
            }

            [data-theme="dark"] .document-header {
                background: rgba(15, 23, 42, 0.48) !important;
                border-bottom-color: rgba(148, 163, 184, 0.25) !important;
                color: var(--text-primary, #e2e8f0) !important;
            }

            [data-theme="dark"] .header-actions .btn {
                border-radius: 10px;
                border-width: 1px;
                min-height: 40px;
                box-shadow: none !important;
            }

            [data-theme="dark"] .header-actions .btn-primary {
                background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
                border-color: rgba(129, 140, 248, 0.55) !important;
                color: #eef2ff !important;
            }

            [data-theme="dark"] .header-actions .btn-primary:hover {
                background: linear-gradient(135deg, #4338ca, #3730a3) !important;
            }

            [data-theme="dark"] .header-actions .btn-danger {
                background: rgba(239, 68, 68, 0.16) !important;
                border-color: rgba(248, 113, 113, 0.45) !important;
                color: #fecaca !important;
            }

            [data-theme="dark"] .header-actions .btn-danger:hover {
                background: rgba(239, 68, 68, 0.24) !important;
                color: #fff1f2 !important;
            }

            [data-theme="dark"] .header-actions .btn-outline-secondary {
                background: rgba(15, 23, 42, 0.5) !important;
                border-color: rgba(148, 163, 184, 0.4) !important;
                color: #cbd5e1 !important;
            }

            [data-theme="dark"] .header-actions .btn-outline-secondary:hover {
                background: rgba(51, 65, 85, 0.5) !important;
                color: #f1f5f9 !important;
            }

            [data-theme="dark"] .image-modal-close {
                background: rgba(30, 41, 59, 0.95) !important;
                color: #e2e8f0 !important;
            }

            /* Admin Module Mobile Compact */
            @media (max-width: 768px) {
                .container-fluid {
                    padding-left: 0.25rem !important;
                    padding-right: 0.25rem !important;
                }

                .page-header,
                .info-card,
                .profile-overview-card {
                    border-radius: 12px !important;
                }

                .header-actions .btn,
                .header-actions button {
                    min-height: 40px;
                    justify-content: center;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function openImageModal(src, title) {
                const modal = document.getElementById('imageModal');
                const modalImage = document.getElementById('modalImage');
                const modalTitle = document.getElementById('modalTitle');

                modalImage.src = src;
                modalTitle.textContent = title;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeImageModal() {
                const modal = document.getElementById('imageModal');
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }

            function confirmDelete() {
                if (confirm('Are you sure you want to delete this admin? This action cannot be undone.')) {
                    document.getElementById('deleteForm').submit();
                }
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeImageModal();
                }
            });
        </script>
    @endpush
@endsection
