@extends('layouts.admin')

@section('title', 'Permission Details')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="page-title mb-1">
                        <i class="bi bi-shield-fill-check me-2"></i>
                        Permission Details
                    </h2>
                    <p class="page-subtitle mb-0">View permission information and configuration</p>
                </div>
                <div class="header-actions">
                    @if(isset($currentAdmin) && ($currentAdmin->is_super || $currentAdmin->hasPermission('permissions.edit')))
                        <a href="{{ route('permissions.edit', $permission) }}" class="btn-primary-custom">
                            <i class="bi bi-pencil-square me-2"></i>Edit Permission
                        </a>
                    @endif
                    <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Permission Overview Card -->
        <div class="permission-overview-card mb-4">
            <div class="permission-header">
                <div class="permission-icon-large">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="permission-info">
                    <h3 class="permission-name">{{ $permission->name }}</h3>
                    <div class="permission-slug-display">
                        <code class="slug-code">{{ $permission->slug }}</code>
                        <button type="button" class="copy-btn" onclick="copyToClipboard('{{ $permission->slug }}')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <div class="permission-meta-badges">
                        <span class="meta-badge">
                            <i class="bi bi-hash"></i>
                            ID: {{ $permission->id }}
                        </span>
                        @if($permission->created_at)
                            <span class="meta-badge">
                                <i class="bi bi-calendar-check"></i>
                                Created {{ $permission->created_at->format('M d, Y') }}
                            </span>
                        @endif
                        @if($permission->updated_at && $permission->updated_at != $permission->created_at)
                            <span class="meta-badge">
                                <i class="bi bi-clock-history"></i>
                                Updated {{ $permission->updated_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Permission Information Card -->
            <div class="col-lg-8">
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <h5 class="info-title">Permission Information</h5>
                    </div>
                    <div class="info-card-body">
                        <!-- Name -->
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-tag-fill me-2"></i>
                                Permission Name
                            </div>
                            <div class="detail-value">
                                {{ $permission->name }}
                            </div>
                        </div>

                        <!-- Slug -->
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-code-slash me-2"></i>
                                Permission Slug
                            </div>
                            <div class="detail-value">
                                <code class="detail-code">{{ $permission->slug }}</code>
                                <button type="button" class="copy-btn-small"
                                    onclick="copyToClipboard('{{ $permission->slug }}')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="detail-item full-width">
                            <div class="detail-label">
                                <i class="bi bi-text-paragraph me-2"></i>
                                Description
                            </div>
                            <div class="detail-value-text">
                                @if($permission->description)
                                    {{ $permission->description }}
                                @else
                                    <span class="text-muted-custom">
                                        <i class="bi bi-info-circle me-1"></i>
                                        No description provided for this permission
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Guidelines Card -->
                <div class="info-card mt-4">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="bi bi-lightbulb-fill"></i>
                        </div>
                        <h5 class="info-title">Usage Guidelines</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="guidelines-content">
                            <div class="guideline-item">
                                <div class="guideline-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="guideline-text">
                                    <strong>Assigning Permissions:</strong> This permission can be assigned to
                                    administrators through the admin management interface
                                </div>
                            </div>
                            <div class="guideline-item">
                                <div class="guideline-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="guideline-text">
                                    <strong>Best Practice:</strong> Only assign this permission to users who need specific
                                    access to the associated functionality
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Cards -->
            <div class="col-lg-4">
                <!-- Quick Actions Card -->
                <div class="info-card mb-4">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <h5 class="info-title">Quick Actions</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="action-buttons">
                            @if(isset($currentAdmin) && ($currentAdmin->is_super || $currentAdmin->hasPermission('permissions.edit')))
                                <a href="{{ route('permissions.edit', $permission) }}" class="action-btn primary">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Edit Permission</span>
                                </a>
                            @endif
                            <button type="button" class="action-btn secondary"
                                onclick="copyToClipboard('{{ $permission->slug }}')">
                                <i class="bi bi-clipboard"></i>
                                <span>Copy Slug</span>
                            </button>
                            <a href="{{ route('permissions.index') }}" class="action-btn secondary">
                                <i class="bi bi-list-ul"></i>
                                <span>View All Permissions</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Metadata Card -->
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <h5 class="info-title">Metadata</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="metadata-list">
                            <div class="metadata-item">
                                <span class="metadata-label">Created</span>
                                <span class="metadata-value">
                                    @if($permission->created_at)
                                        {{ $permission->created_at->format('M d, Y') }}
                                        <small
                                            class="text-muted-custom d-block">{{ $permission->created_at->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted-custom">Unknown</span>
                                    @endif
                                </span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Last Updated</span>
                                <span class="metadata-value">
                                    @if($permission->updated_at)
                                        {{ $permission->updated_at->format('M d, Y') }}
                                        <small
                                            class="text-muted-custom d-block">{{ $permission->updated_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted-custom">Never</span>
                                    @endif
                                </span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Permission ID</span>
                                <span class="metadata-value">
                                    <code class="id-badge">{{ $permission->id }}</code>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                --success: #10b981;
                --warning: #f59e0b;
                --info: #3b82f6;
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

            /* Permission Overview Card */
            .permission-overview-card {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                border-radius: 16px;
                padding: 2.5rem;
                color: white;
                box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
                position: relative;
                overflow: hidden;
            }

            .permission-overview-card::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -20%;
                width: 400px;
                height: 400px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            }

            .permission-header {
                display: flex;
                align-items: center;
                gap: 2rem;
                position: relative;
                z-index: 1;
            }

            .permission-icon-large {
                width: 100px;
                height: 100px;
                border-radius: 20px;
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(10px);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 3rem;
                flex-shrink: 0;
                border: 3px solid rgba(255, 255, 255, 0.3);
            }

            .permission-info {
                flex: 1;
            }

            .permission-name {
                font-size: 2rem;
                font-weight: 700;
                margin: 0 0 1rem 0;
                color: white;
            }

            .permission-slug-display {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin-bottom: 1rem;
            }

            .slug-code {
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(10px);
                padding: 0.625rem 1.25rem;
                border-radius: 8px;
                font-family: 'Courier New', monospace;
                color: white;
                font-size: 1.125rem;
                border: 2px solid rgba(255, 255, 255, 0.3);
                font-weight: 600;
            }

            .copy-btn {
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(10px);
                border: 2px solid rgba(255, 255, 255, 0.3);
                color: white;
                width: 42px;
                height: 42px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
                font-size: 1.125rem;
            }

            .copy-btn:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: translateY(-2px);
            }

            .permission-meta-badges {
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

            /* Info Card */
            .info-card {
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .info-card:hover {
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
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

            /* Detail Items */
            .detail-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem;
                border-radius: 12px;
                background: var(--light-gray);
                margin-bottom: 1rem;
                border: 2px solid var(--border);
            }

            .detail-item.full-width {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .detail-item:last-child {
                margin-bottom: 0;
            }

            .detail-label {
                font-size: 0.875rem;
                color: var(--gray);
                font-weight: 600;
                display: flex;
                align-items: center;
            }

            .detail-value {
                font-size: 0.95rem;
                color: var(--dark);
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .detail-value-text {
                font-size: 0.95rem;
                color: var(--dark);
                line-height: 1.7;
                width: 100%;
            }

            .detail-code {
                background: white;
                padding: 0.375rem 0.75rem;
                border-radius: 6px;
                font-family: 'Courier New', monospace;
                color: var(--primary);
                font-size: 0.9rem;
                border: 1px solid var(--border);
            }

            .copy-btn-small {
                background: var(--light-gray);
                border: 2px solid var(--border);
                color: var(--gray);
                width: 32px;
                height: 32px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
            }

            .copy-btn-small:hover {
                background: var(--primary);
                color: white;
                border-color: var(--primary);
            }

            .text-muted-custom {
                color: var(--gray);
                font-style: italic;
            }

            /* Guidelines */
            .guidelines-content {
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }

            .guideline-item {
                display: flex;
                gap: 1rem;
                padding: 1rem;
                background: var(--light-gray);
                border-radius: 12px;
                border: 2px solid var(--border);
            }

            .guideline-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                background: linear-gradient(135deg, var(--success), #059669);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.125rem;
                flex-shrink: 0;
            }

            .guideline-text {
                flex: 1;
                font-size: 0.9rem;
                line-height: 1.6;
                color: var(--dark);
            }

            .guideline-text strong {
                display: block;
                margin-bottom: 0.25rem;
                color: var(--dark);
            }

            .guideline-text code {
                background: white;
                padding: 0.125rem 0.5rem;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                color: var(--primary);
                font-size: 0.85rem;
                border: 1px solid var(--border);
            }

            /* Action Buttons */
            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .action-btn {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.875rem 1.25rem;
                border-radius: 12px;
                border: 2px solid var(--border);
                background: white;
                color: var(--dark);
                font-weight: 600;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.2s;
                text-decoration: none;
            }

            .action-btn i {
                font-size: 1.125rem;
            }

            .action-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .action-btn.primary {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                border-color: var(--primary);
            }

            .action-btn.primary:hover {
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            }

            .action-btn.secondary:hover {
                border-color: var(--primary);
                color: var(--primary);
            }

            /* Metadata List */
            .metadata-list {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .metadata-item {
                display: flex;
                justify-content: space-between;
                align-items: start;
                padding: 1rem;
                background: var(--light-gray);
                border-radius: 12px;
                border: 2px solid var(--border);
            }

            .metadata-label {
                font-size: 0.875rem;
                color: var(--gray);
                font-weight: 600;
            }

            .metadata-value {
                font-size: 0.875rem;
                color: var(--dark);
                font-weight: 600;
                text-align: right;
            }

            .id-badge {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                padding: 0.25rem 0.75rem;
                border-radius: 6px;
                font-family: 'Courier New', monospace;
                font-size: 0.875rem;
            }

            .btn-primary-custom {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: var(--primary);
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.2s;
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            }

            .btn-primary-custom:hover {
                background: var(--primary-dark);
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
                color: white;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .page-header {
                    padding: 1.5rem;
                }

                .header-actions {
                    width: 100%;
                }

                .header-actions .btn {
                    flex: 1;
                }

                .btn-primary-custom {
                    width: 100%;
                    justify-content: center;
                }

                .permission-overview-card {
                    padding: 1.5rem;
                }

                .permission-header {
                    flex-direction: column;
                    text-align: center;
                }

                .permission-name {
                    font-size: 1.5rem;
                }

                .permission-slug-display {
                    justify-content: center;
                    flex-wrap: wrap;
                }

                .permission-meta-badges {
                    justify-content: center;
                }

                .detail-item {
                    flex-direction: column;
                    gap: 0.5rem;
                    align-items: flex-start;
                }

                .detail-value {
                    width: 100%;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function copyToClipboard(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function () {
                        showToastNotification('Copied to clipboard: ' + text, 'success');
                    }).catch(function (err) {
                        console.error('Failed to copy:', err);
                        showToastNotification('Failed to copy to clipboard', 'error');
                    });
                } else {
                    // Fallback for older browsers
                    var textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    try {
                        document.execCommand('copy');
                        showToastNotification('Copied to clipboard: ' + text, 'success');
                    } catch (err) {
                        showToastNotification('Failed to copy to clipboard', 'error');
                    }
                    document.body.removeChild(textarea);
                }
            }

            function copyPermissionCode() {
                var code = "$admin->hasPermission('{{ $permission->slug }}')";
                copyToClipboard(code);
            }

            function showToastNotification(message, type) {
                if (typeof showToast === 'function') {
                    showToast(message);
                } else {
                    const colors = {
                        success: '#10b981',
                        error: '#ef4444',
                        info: '#3b82f6'
                    };

                    const toast = document.createElement('div');
                    toast.style.cssText = 'position:fixed;top:20px;right:20px;background:' +
                        (colors[type] || colors.info) +
                        ';color:white;padding:1rem 1.5rem;border-radius:12px;' +
                        'box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;' +
                        'font-weight:600;animation:slideIn 0.3s ease;max-width:400px;';
                    toast.textContent = message;
                    document.body.appendChild(toast);

                    setTimeout(function () {
                        toast.style.animation = 'slideOut 0.3s ease';
                        setTimeout(function () {
                            if (toast.parentNode) {
                                document.body.removeChild(toast);
                            }
                        }, 300);
                    }, 3000);
                }
            }
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