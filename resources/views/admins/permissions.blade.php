@extends('layouts.admin')

@section('title', 'Manage Permissions')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="page-title mb-1">
                        <i class="bi bi-shield-lock me-2"></i>
                        Manage Permissions
                    </h2>
                    <p class="page-subtitle mb-0">Configure access rights for {{ $admin->name }}</p>
                </div>
                <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Admins
                </a>
            </div>
        </div>

        <!-- Admin Info Card -->
        <div class="admin-info-card mb-4">
            <div class="admin-avatar">
                {{ strtoupper(substr($admin->name, 0, 2)) }}
            </div>
            <div class="admin-details">
                <h5 class="mb-1">{{ $admin->name }}</h5>
                <p class="text-muted mb-0">{{ $admin->email }}</p>
            </div>
            <div class="admin-stats">
                <div class="stat-item">
                    <div class="stat-value" id="selectedCount">{{ count($assignedPermissions) }}</div>
                    <div class="stat-label">Assigned</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-value" id="totalCount">{{ $permissionsByCategory->flatten()->count() }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
        </div>

        <!-- Control Bar -->
        <div class="control-bar mb-4">
            <div class="control-group">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="form-control" id="permissionSearch" 
                           placeholder="Search permissions...">
                    <button class="clear-search" id="clearSearch" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="control-group">
                <button class="btn btn-outline-primary" id="globalSelectAll">
                    <i class="bi bi-check-square me-2"></i>
                    <span>Select All</span>
                </button>
                <button class="btn btn-outline-secondary" id="clearAllBtn">
                    <i class="bi bi-x-square me-2"></i>
                    <span>Clear All</span>
                </button>
            </div>
        </div>

        <!-- Permissions Form -->
        <form method="POST" action="{{ route('admins.permissions.update', $admin) }}" id="permissionsForm">
            @csrf
            @method('PUT')

            <div class="permissions-grid">
                @foreach ($permissionsByCategory as $category => $permissions)
                    <div class="permission-category-card" data-category="{{ $category }}">
                        <div class="category-header">
                            <div class="category-info">
                                <div class="category-icon">
                                    <i class="bi bi-{{ $loop->iteration % 2 == 0 ? 'star' : 'lightning' }}-fill"></i>
                                </div>
                                <div>
                                    <h5 class="category-title">
                                        {{ ucwords(str_replace('_', ' ', $category ?: 'General')) }}
                                    </h5>
                                    <p class="category-count">
                                        <span class="selected-count">0</span> / {{ count($permissions) }} selected
                                    </p>
                                </div>
                            </div>
                            <div class="category-actions">
                                <div class="form-check form-switch">
                                    <input class="form-check-input category-select-all" 
                                           type="checkbox" 
                                           id="select-all-{{ $category }}"
                                           data-category="{{ $category }}">
                                    <label class="form-check-label" for="select-all-{{ $category }}">
                                        Select All
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="category-body">
                            <div class="permissions-list">
                                @foreach ($permissions as $permission)
                                    <div class="permission-item" data-permission-id="{{ $permission->id }}">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}"
                                                   id="permission-{{ $permission->id }}"
                                                   data-category="{{ $category }}"
                                                   @checked(in_array($permission->id, $assignedPermissions))>
                                            <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                <span class="permission-name">
                                                    {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                </span>
                                                <span class="permission-slug">{{ $permission->slug }}</span>
                                                @if($permission->description)
                                                    <span class="permission-description">{{ $permission->description }}</span>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Action Buttons -->
            <div class="action-footer">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle me-2"></i>
                    Save Permissions
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

            /* Admin Info Card */
            .admin-info-card {
                background: white;
                border-radius: 16px;
                padding: 1.5rem 2rem;
                display: flex;
                align-items: center;
                gap: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                border: 2px solid var(--border);
            }

            .admin-avatar {
                width: 64px;
                height: 64px;
                border-radius: 16px;
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                font-weight: 700;
                flex-shrink: 0;
            }

            .admin-details {
                flex: 1;
            }

            .admin-details h5 {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--dark);
            }

            .admin-stats {
                display: flex;
                align-items: center;
                gap: 1.5rem;
                padding: 0.75rem 1.5rem;
                background: var(--light-gray);
                border-radius: 12px;
            }

            .stat-item {
                text-align: center;
            }

            .stat-value {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--primary);
                line-height: 1;
            }

            .stat-label {
                font-size: 0.75rem;
                color: var(--gray);
                margin-top: 0.25rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .stat-divider {
                width: 2px;
                height: 40px;
                background: var(--border);
            }

            /* Control Bar */
            .control-bar {
                background: white;
                border-radius: 16px;
                padding: 1.25rem 1.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                border: 2px solid var(--border);
                flex-wrap: wrap;
            }

            .control-group {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .search-box {
                position: relative;
                min-width: 320px;
            }

            .search-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--gray);
                font-size: 1rem;
                pointer-events: none;
            }

            .search-box .form-control {
                padding-left: 2.75rem;
                padding-right: 2.75rem;
                height: 46px;
                border-radius: 12px;
                border: 2px solid var(--border);
                font-size: 0.95rem;
                transition: all 0.3s ease;
            }

            .search-box .form-control:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }

            .clear-search {
                position: absolute;
                right: 0.75rem;
                top: 50%;
                transform: translateY(-50%);
                background: var(--light-gray);
                border: none;
                width: 28px;
                height: 28px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
                color: var(--gray);
            }

            .clear-search:hover {
                background: var(--danger);
                color: white;
            }

            /* Permissions Grid */
            .permissions-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(480px, 1fr));
                gap: 1.5rem;
            }

            .permission-category-card {
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .permission-category-card:hover {
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
                transform: translateY(-2px);
            }

            .category-header {
                padding: 1.5rem;
                background: linear-gradient(135deg, var(--light-gray), white);
                border-bottom: 2px solid var(--border);
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
            }

            .category-info {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .category-icon {
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

            .category-title {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
            }

            .category-count {
                font-size: 0.875rem;
                color: var(--gray);
                margin: 0.25rem 0 0;
            }

            .selected-count {
                color: var(--primary);
                font-weight: 600;
            }

            .category-actions .form-switch {
                padding-left: 3rem;
            }

            .category-actions .form-switch .form-check-input {
                width: 48px;
                height: 24px;
                cursor: pointer;
                border: 2px solid var(--border);
                background-color: var(--light-gray);
            }

            .category-actions .form-switch .form-check-input:checked {
                background-color: var(--primary);
                border-color: var(--primary);
            }

            .category-actions .form-check-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--gray);
                cursor: pointer;
            }

            /* Category Body */
            .category-body {
                padding: 1.5rem;
            }

            .permissions-list {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .permission-item {
                padding: 1rem;
                border-radius: 12px;
                border: 2px solid var(--border);
                background: white;
                transition: all 0.2s ease;
            }

            .permission-item:hover {
                background: var(--light-gray);
                border-color: var(--primary);
            }

            .permission-item .form-check {
                display: flex;
                align-items: start;
                gap: 0.75rem;
            }

            .permission-item .form-check-input {
                width: 20px;
                height: 20px;
                margin-top: 0.125rem;
                cursor: pointer;
                border: 2px solid var(--border);
                flex-shrink: 0;
            }

            .permission-item .form-check-input:checked {
                background-color: var(--primary);
                border-color: var(--primary);
            }

            .permission-item .form-check-label {
                cursor: pointer;
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
                flex: 1;
            }

            .permission-name {
                font-weight: 600;
                color: var(--dark);
                font-size: 0.95rem;
            }

            .permission-slug {
                font-size: 0.8rem;
                color: var(--gray);
                font-family: 'Courier New', monospace;
                background: var(--light-gray);
                padding: 0.125rem 0.5rem;
                border-radius: 4px;
                display: inline-block;
            }

            .permission-description {
                font-size: 0.85rem;
                color: var(--gray);
                line-height: 1.4;
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

            /* Hidden State */
            .permission-category-card[style*="display: none"],
            .permission-item[style*="display: none"] {
                display: none !important;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .permissions-grid {
                    grid-template-columns: 1fr;
                }

                .control-bar {
                    flex-direction: column;
                    align-items: stretch;
                }

                .control-group {
                    flex-wrap: wrap;
                }

                .search-box {
                    min-width: 100%;
                }

                .admin-info-card {
                    flex-direction: column;
                    text-align: center;
                }

                .admin-stats {
                    width: 100%;
                    justify-content: center;
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
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    // Elements
                    const globalSelectAll = document.getElementById('globalSelectAll');
                    const clearAllBtn = document.getElementById('clearAllBtn');
                    const searchInput = document.getElementById('permissionSearch');
                    const clearSearch = document.getElementById('clearSearch');
                    const selectedCountEl = document.getElementById('selectedCount');
                    const form = document.getElementById('permissionsForm');

                    // Update counts
                    function updateCounts() {
                        const allCheckboxes = document.querySelectorAll('.permission-checkbox:not([disabled])');
                        const allChecked = document.querySelectorAll('.permission-checkbox:not([disabled]):checked');
                        
                        selectedCountEl.textContent = allChecked.length;

                        // Update category counts
                        document.querySelectorAll('.permission-category-card').forEach(card => {
                            const category = card.dataset.category;
                            const categoryCheckboxes = card.querySelectorAll('.permission-checkbox:not([disabled])');
                            const categoryChecked = card.querySelectorAll('.permission-checkbox:not([disabled]):checked');
                            const countEl = card.querySelector('.selected-count');
                            if (countEl) {
                                countEl.textContent = categoryChecked.length;
                            }
                        });

                        // Update global select all
                        if (globalSelectAll) {
                            const visibleCheckboxes = document.querySelectorAll('.permission-item:not([style*="display: none"]) .permission-checkbox:not([disabled])');
                            const visibleChecked = document.querySelectorAll('.permission-item:not([style*="display: none"]) .permission-checkbox:not([disabled]):checked');
                            
                            globalSelectAll.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.length === visibleChecked.length;
                            globalSelectAll.indeterminate = visibleChecked.length > 0 && visibleChecked.length < visibleCheckboxes.length;
                        }
                    }

                    // Global select all
                    if (globalSelectAll) {
                        globalSelectAll.addEventListener('change', function() {
                            const visibleCheckboxes = document.querySelectorAll('.permission-item:not([style*="display: none"]) .permission-checkbox:not([disabled])');
                            visibleCheckboxes.forEach(cb => cb.checked = this.checked);
                            document.querySelectorAll('.category-select-all').forEach(updateCategorySelectAll);
                            updateCounts();
                        });
                    }

                    // Clear all
                    if (clearAllBtn) {
                        clearAllBtn.addEventListener('change', function() {
                            document.querySelectorAll('.permission-checkbox:not([disabled])').forEach(cb => cb.checked = false);
                            document.querySelectorAll('.category-select-all').forEach(cb => cb.checked = false);
                            updateCounts();
                        });
                    }

                    // Category select all
                    document.querySelectorAll('.category-select-all').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const category = this.dataset.category;
                            const categoryCheckboxes = document.querySelectorAll(
                                `.permission-checkbox[data-category="${category}"]:not([disabled])`
                            );
                            categoryCheckboxes.forEach(cb => cb.checked = this.checked);
                            updateCounts();
                        });

                        updateCategorySelectAll(checkbox);
                    });

                    // Individual checkboxes
                    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const category = this.dataset.category;
                            const selectAllCheckbox = document.querySelector(
                                `.category-select-all[data-category="${category}"]`
                            );
                            if (selectAllCheckbox) {
                                updateCategorySelectAll(selectAllCheckbox);
                            }
                            updateCounts();
                        });
                    });

                    // Search functionality
                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            const searchTerm = this.value.toLowerCase().trim();
                            clearSearch.style.display = searchTerm ? 'flex' : 'none';

                            if (searchTerm === '') {
                                document.querySelectorAll('.permission-item').forEach(item => {
                                    item.style.display = '';
                                });
                                document.querySelectorAll('.permission-category-card').forEach(card => {
                                    card.style.display = '';
                                });
                            } else {
                                document.querySelectorAll('.permission-item').forEach(item => {
                                    const text = item.textContent.toLowerCase();
                                    item.style.display = text.includes(searchTerm) ? '' : 'none';
                                });

                                document.querySelectorAll('.permission-category-card').forEach(card => {
                                    const visibleItems = card.querySelectorAll('.permission-item:not([style*="display: none"])');
                                    card.style.display = visibleItems.length > 0 ? '' : 'none';
                                });
                            }

                            updateCounts();
                        });
                    }

                    // Clear search
                    if (clearSearch) {
                        clearSearch.addEventListener('click', function() {
                            searchInput.value = '';
                            searchInput.dispatchEvent(new Event('input'));
                            searchInput.focus();
                        });
                    }

                    // Helper: Update category select all state
                    function updateCategorySelectAll(selectAllCheckbox) {
                        const category = selectAllCheckbox.dataset.category;
                        const categoryCheckboxes = document.querySelectorAll(
                            `.permission-checkbox[data-category="${category}"]:not([disabled])`
                        );
                        const checkedBoxes = document.querySelectorAll(
                            `.permission-checkbox[data-category="${category}"]:not([disabled]):checked`
                        );

                        selectAllCheckbox.checked = categoryCheckboxes.length > 0 && 
                            categoryCheckboxes.length === checkedBoxes.length;
                        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && 
                            checkedBoxes.length < categoryCheckboxes.length;
                    }

                    // Initialize
                    updateCounts();

                } catch (error) {
                    console.error('Error initializing permissions page:', error);
                }
            });
        </script>
    @endpush
@endsection