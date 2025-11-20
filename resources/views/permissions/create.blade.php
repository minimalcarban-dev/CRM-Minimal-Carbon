@extends('layouts.admin')

@section('title', 'Create Permission')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="page-title mb-1">
                        <i class="bi bi-shield-plus me-2"></i>
                        Create New Permission
                    </h2>
                    <p class="page-subtitle mb-0">Define a new access permission for the system</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Permissions
                    </a>
                    <!-- Top primary action submits the form below -->
                    <button type="submit" form="createPermissionForm" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Create Permission
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert-card info mb-4">
            <div class="alert-icon">
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div class="alert-content">
                <h5 class="alert-title">Permission Guidelines</h5>
                <ul class="alert-list">
                    <li>Use clear, descriptive names indicating the permission's function</li>
                    <li>Apply dot notation for slugs: <code>resource.action</code></li>
                    <li>Maintain lowercase slugs with underscores for multi-word resources</li>
                    <li>Provide concise descriptions for administrative clarity</li>
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('permissions.store') }}" id="createPermissionForm">
            @csrf

            <!-- Permission Details Card -->
            <div class="form-section-card mb-4">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Permission Details</h5>
                            <p class="section-description">Basic information about this permission</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row g-4">
                        <!-- Permission Name -->
                        <div class="col-12">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-tag me-2"></i>Permission Name
                                    <span class="required-badge">Required</span>
                                </label>
                                <input type="text" name="name" id="permission_name" class="form-control-modern"
                                    value="{{ old('name') }}" placeholder="e.g., Create User, View Reports, Edit Settings"
                                    required>
                                <small class="form-hint">
                                    <i class="bi bi-lightbulb me-1"></i>
                                    Use a clear, action-oriented name that describes what this permission allows
                                </small>
                            </div>
                        </div>

                        <!-- Permission Slug -->
                        <div class="col-12">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-code-slash me-2"></i>Permission Slug
                                    <span class="required-badge">Required</span>
                                    <span class="badge-unique">Unique</span>
                                </label>
                                <div class="input-with-suggestion">
                                    <input type="text" name="slug" id="permission_slug" class="form-control-modern"
                                        value="{{ old('slug') }}"
                                        placeholder="e.g., users.create, reports.view, settings.edit" required>
                                    <button type="button" id="generateSlug" class="suggestion-btn"
                                        title="Auto-generate from name">
                                        <i class="bi bi-magic"></i>
                                    </button>
                                </div>
                                <div class="slug-examples">
                                    <span class="examples-label">Examples:</span>
                                    <button type="button" class="example-chip" data-slug="users.view">users.view</button>
                                    <button type="button" class="example-chip"
                                        data-slug="users.create">users.create</button>
                                    <button type="button" class="example-chip" data-slug="users.edit">users.edit</button>
                                    <button type="button" class="example-chip"
                                        data-slug="users.delete">users.delete</button>
                                    <button type="button" class="example-chip"
                                        data-slug="reports.export">reports.export</button>
                                </div>
                                <small class="form-hint">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Use dot notation: <code>resource.action</code> format (lowercase only)
                                </small>
                            </div>
                        </div>

                        <!-- Permission Category -->
                        <div class="col-12">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-list-task me-2"></i>Permission Category
                                    <span class="required-badge">Required</span>
                                </label>
                                <input type="text" name="category" id="permission_category" class="form-control-modern"
                                    list="permission_categories" value="{{ old('category') }}"
                                    placeholder="Type or select an existing category" required>

                                <datalist id="permission_categories">
                                    @if(isset($categories) && $categories->count())
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    @endif
                                </datalist>

                                <small class="form-hint">
                                    <i class="bi bi-lightbulb me-1"></i>
                                    Start typing to select an existing category or enter a new one
                                </small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="bi bi-text-paragraph me-2"></i>Description
                                    <span class="optional-badge">Optional</span>
                                </label>
                                <textarea name="description" id="permission_description" class="form-control-modern"
                                    rows="4"
                                    placeholder="Describe when and why this permission should be granted...">{{ old('description') }}</textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="form-hint">
                                        <i class="bi bi-lightbulb me-1"></i>
                                        Help admins understand the purpose and implications of this permission
                                    </small>
                                    <small class="char-counter text-muted">
                                        <span id="charCount">0</span> characters
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="preview-card mb-4">
                <div class="preview-header">
                    <i class="bi bi-eye me-2"></i>
                    <h5>Preview</h5>
                </div>
                <div class="preview-body">
                    <div class="permission-preview-item">
                        <div class="preview-content">
                            <div class="preview-name" id="preview_name">Permission Name</div>
                            <div class="preview-slug" id="preview_slug">permission.slug</div>
                            <div class="preview-description" id="preview_description">Description will appear here...
                            </div>
                        </div>
                        <div class="preview-badge">
                            <i class="bi bi-shield-check"></i>
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
                --dark: #1e293b;
                --gray: #64748b;
                --light-gray: #f8fafc;
                --border: #e2e8f0;
                --danger: #ef4444;
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

            /* Alert Card */
            .alert-card {
                background: white;
                border-radius: 16px;
                padding: 1.5rem;
                display: flex;
                gap: 1.25rem;
                border: 2px solid var(--border);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            }

            .alert-card.info {
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(99, 102, 241, 0.05));
                border-color: rgba(59, 130, 246, 0.2);
            }

            .alert-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--info), #2563eb);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                flex-shrink: 0;
            }

            .alert-content {
                flex: 1;
            }

            .alert-title {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0 0 0.75rem 0;
            }

            .alert-list {
                margin: 0;
                padding-left: 1.25rem;
                color: var(--dark);
                font-size: 0.9rem;
                line-height: 1.8;
            }

            .alert-list li {
                margin-bottom: 0.5rem;
            }

            .alert-list code {
                background: rgba(99, 102, 241, 0.1);
                padding: 0.125rem 0.5rem;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                color: var(--primary);
                font-size: 0.875rem;
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

            /* Form Controls */
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
                gap: 0.5rem;
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

            .optional-badge {
                background: linear-gradient(135deg, var(--gray), #475569);
                color: white;
                font-size: 0.7rem;
                padding: 0.125rem 0.5rem;
                border-radius: 4px;
                margin-left: auto;
                font-weight: 600;
                letter-spacing: 0.3px;
            }

            .badge-unique {
                background: linear-gradient(135deg, var(--warning), #ea580c);
                color: white;
                font-size: 0.7rem;
                padding: 0.125rem 0.5rem;
                border-radius: 4px;
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

            /* Input with Suggestion */
            .input-with-suggestion {
                position: relative;
                display: flex;
                gap: 0.5rem;
            }

            .input-with-suggestion .form-control-modern {
                flex: 1;
            }

            .suggestion-btn {
                width: 48px;
                height: 48px;
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                border: none;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 1.125rem;
                flex-shrink: 0;
            }

            .suggestion-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            }

            /* Slug Examples */
            .slug-examples {
                margin-top: 0.75rem;
                padding: 1rem;
                background: var(--light-gray);
                border-radius: 12px;
                border: 2px solid var(--border);
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                align-items: center;
            }

            .examples-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--gray);
                margin-right: 0.5rem;
            }

            .example-chip {
                background: white;
                border: 2px solid var(--border);
                padding: 0.375rem 0.875rem;
                border-radius: 8px;
                font-size: 0.875rem;
                font-weight: 500;
                color: var(--dark);
                cursor: pointer;
                transition: all 0.2s;
                font-family: 'Courier New', monospace;
            }

            .example-chip:hover {
                border-color: var(--primary);
                background: rgba(99, 102, 241, 0.1);
                color: var(--primary);
                transform: translateY(-1px);
            }

            /* Form Hint */
            .form-hint {
                display: block;
                margin-top: 0.5rem;
                color: var(--gray);
                font-size: 0.875rem;
            }

            .form-hint code {
                background: var(--light-gray);
                padding: 0.125rem 0.5rem;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                color: var(--primary);
                font-size: 0.85rem;
            }

            /* Character Counter */
            .char-counter {
                font-size: 0.875rem;
            }

            /* Preview Card */
            .preview-card {
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                overflow: hidden;
            }

            .preview-header {
                padding: 1.25rem 1.5rem;
                background: linear-gradient(135deg, var(--light-gray), white);
                border-bottom: 2px solid var(--border);
                display: flex;
                align-items: center;
                font-weight: 700;
                color: var(--dark);
            }

            .preview-header h5 {
                margin: 0;
                font-size: 1rem;
            }

            .preview-body {
                padding: 1.5rem;
            }

            .permission-preview-item {
                display: flex;
                align-items: start;
                gap: 1rem;
                padding: 1.25rem;
                background: var(--light-gray);
                border-radius: 12px;
                border: 2px solid var(--border);
            }

            .preview-content {
                flex: 1;
            }

            .preview-name {
                font-size: 1rem;
                font-weight: 600;
                color: var(--dark);
                margin-bottom: 0.5rem;
            }

            .preview-name:empty::before {
                content: 'Permission Name';
                color: #cbd5e1;
            }

            .preview-slug {
                font-size: 0.85rem;
                color: var(--gray);
                font-family: 'Courier New', monospace;
                background: white;
                padding: 0.25rem 0.75rem;
                border-radius: 6px;
                display: inline-block;
                margin-bottom: 0.75rem;
                border: 1px solid var(--border);
            }

            .preview-slug:empty::before {
                content: 'permission.slug';
                color: #cbd5e1;
            }

            .preview-description {
                font-size: 0.9rem;
                color: var(--dark);
                line-height: 1.6;
            }

            .preview-description:empty::before {
                content: 'Description will appear here...';
                color: #cbd5e1;
                font-style: italic;
            }

            .preview-badge {
                width: 48px;
                height: 48px;
                background: linear-gradient(135deg, var(--success), #059669);
                color: white;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                flex-shrink: 0;
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
                .page-header {
                    padding: 1.5rem;
                }

                .section-body {
                    padding: 1.5rem;
                }

                .alert-card {
                    flex-direction: column;
                }

                .slug-examples {
                    flex-direction: column;
                    align-items: stretch;
                }

                .example-chip {
                    width: 100%;
                    text-align: center;
                }

                .action-footer {
                    flex-direction: column;
                }

                .action-footer .btn-lg {
                    width: 100%;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                function initCreatePermissionForm() {
                    const nameInput = document.getElementById('permission_name');
                    const slugInput = document.getElementById('permission_slug');
                    const descInput = document.getElementById('permission_description');
                    const generateBtn = document.getElementById('generateSlug');
                    const charCount = document.getElementById('charCount');

                    const previewName = document.getElementById('preview_name');
                    const previewSlug = document.getElementById('preview_slug');
                    const previewDesc = document.getElementById('preview_description');

                    // Generate slug from name
                    function generateSlug(text) {
                        return text
                            .toLowerCase()
                            .replace(/[^a-z0-9\s.-]/g, '')
                            .replace(/\s+/g, '.')
                            .replace(/\.+/g, '.')
                            .replace(/^\.+|\.+$/g, '');
                    }

                    // Auto-generate slug button
                    if (generateBtn && nameInput && slugInput) {
                        generateBtn.addEventListener('click', function () {
                            const name = nameInput.value.trim();
                            if (name) {
                                const slug = generateSlug(name);
                                slugInput.value = slug;
                                slugInput.dispatchEvent(new Event('input'));
                                showToastNotification('Slug generated from name', 'success');
                            } else {
                                showToastNotification('Please enter a permission name first', 'warning');
                            }
                        });
                    }

                    // Example chips
                    document.querySelectorAll('.example-chip').forEach(chip => {
                        chip.addEventListener('click', function () {
                            const slug = this.dataset.slug;
                            if (slugInput) {
                                slugInput.value = slug;
                                slugInput.dispatchEvent(new Event('input'));
                                showToastNotification('Example slug applied', 'success');
                            }
                        });
                    });

                    // Character counter
                    if (descInput && charCount) {
                        descInput.addEventListener('input', function () {
                            charCount.textContent = this.value.length;
                        });
                        // Initialize
                        charCount.textContent = descInput.value.length;
                    }

                    // Live preview updates
                    if (nameInput && previewName) {
                        nameInput.addEventListener('input', function () {
                            previewName.textContent = this.value || '';
                        });
                    }

                    if (slugInput && previewSlug) {
                        slugInput.addEventListener('input', function () {
                            previewSlug.textContent = this.value || '';
                        });
                    }

                    if (descInput && previewDesc) {
                        descInput.addEventListener('input', function () {
                            previewDesc.textContent = this.value || '';
                        });
                    }

                    // Initialize preview with existing values
                    if (nameInput && previewName) previewName.textContent = nameInput.value;
                    if (slugInput && previewSlug) previewSlug.textContent = slugInput.value;
                    if (descInput && previewDesc) previewDesc.textContent = descInput.value;
                }

                // Toast notification helper
                function showToastNotification(message, type) {
                    if (typeof showToast === 'function') {
                        showToast(message);
                    } else {
                        const colors = {
                            success: '#10b981',
                            warning: '#f59e0b',
                            error: '#ef4444',
                            info: '#3b82f6'
                        };

                        const toast = document.createElement('div');
                        toast.style.cssText = 'position:fixed;top:20px;right:20px;background:' +
                            (colors[type] || colors.info) +
                            ';color:white;padding:1rem 1.5rem;border-radius:12px;' +
                            'box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;' +
                            'font-weight:600;animation:slideIn 0.3s ease;';
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

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initCreatePermissionForm);
                } else {
                    initCreatePermissionForm();
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