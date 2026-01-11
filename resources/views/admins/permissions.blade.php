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
                <div class="header-right">
                    <button form="permissionsForm" type="submit" class="btn-primary-custom me-2">
                        <i class="bi bi-check-circle me-2"></i>
                        Save Permissions
                    </button>
                    <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Admins
                    </a>
                </div>
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

        <!-- Super Admin Toggle Card -->
        <div class="super-admin-card mb-4">
            <div class="super-admin-icon">
                <i class="bi bi-shield-shaded"></i>
            </div>
            <div class="super-admin-info">
                <h5 class="super-admin-title">
                    <i class="bi bi-stars me-2"></i>Super Admin Access
                </h5>
                <p class="super-admin-desc">
                    Enable this to grant full access to all features. Super Admins can view and manage everything without any permission restrictions.
                </p>
            </div>
            <div class="super-admin-toggle">
                <div class="form-check form-switch">
                    <input class="form-check-input super-admin-switch" 
                            type="checkbox" 
                            id="isSuperAdmin"
                            name="is_super"
                            value="1"
                            form="permissionsForm"
                            {{ $admin->is_super ? 'checked' : '' }}>
                    <label class="form-check-label" for="isSuperAdmin">
                        {{ $admin->is_super ? 'Active' : 'Inactive' }}
                    </label>
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
                <button class="btn-primary-custom me-2" id="globalSelectAll">
                    <i class="bi bi-check-circle me-2"></i>
                    <span>Select All</span>
                </button>
            </div>
        </div>

        <!-- Permissions Form -->
        <form method="POST" action="{{ route('admins.permissions.update', $admin) }}" id="permissionsForm">
            @csrf
            @method('PUT')
            
            <!-- Hidden input for Super Admin status (synced via JS from the toggle above) -->
            <input type="hidden" name="is_super" id="isSuperHidden" value="{{ $admin->is_super ? '1' : '0' }}">

            <div class="permissions-grid">
                @foreach ($permissionsByCategory as $category => $permissions)
                    <div class="permission-category-card" data-category="{{ $category }}">
                        <div class="category-header" onclick="toggleCategory(this)">
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
                                <div class="form-check form-switch" onclick="event.stopPropagation()">
                                    <input class="form-check-input category-select-all" 
                                            type="checkbox" 
                                            id="select-all-{{ $category }}"
                                            data-category="{{ $category }}">
                                    <label class="form-check-label" for="select-all-{{ $category }}">
                                        Select All
                                    </label>
                                </div>
                                <!-- <div class="collapse-toggle">
                                    <i class="bi bi-chevron-down"></i>
                                </div> -->
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

            <!-- Action Buttons moved to header for quick access -->
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

            /* Super Admin Card */
            .super-admin-card {
                background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fbbf24 100%);
                border-radius: 16px;
                padding: 1.5rem 2rem;
                display: flex;
                align-items: center;
                gap: 1.5rem;
                box-shadow: 0 4px 20px rgba(251, 191, 36, 0.3);
                border: 2px solid #f59e0b;
                position: relative;
                overflow: hidden;
            }

            .super-admin-card::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -50%;
                width: 100%;
                height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
                pointer-events: none;
            }

            .super-admin-icon {
                width: 64px;
                height: 64px;
                border-radius: 16px;
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.75rem;
                flex-shrink: 0;
                box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4);
            }

            .super-admin-info {
                flex: 1;
            }

            .super-admin-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #92400e;
                margin: 0 0 0.25rem 0;
                display: flex;
                align-items: center;
            }

            .super-admin-title i {
                color: #d97706;
            }

            .super-admin-desc {
                font-size: 0.875rem;
                color: #a16207;
                margin: 0;
                line-height: 1.5;
            }

            .super-admin-toggle {
                display: flex;
                align-items: center;
            }

            .super-admin-toggle .form-check.form-switch {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin: 0;
                padding: 0;
            }

            .super-admin-toggle .super-admin-switch {
                width: 60px;
                height: 32px;
                cursor: pointer;
                border: 2px solid #d97706;
                background-color: #fef3c7;
                background-image: none;
            }

            .super-admin-toggle .super-admin-switch:checked {
                background-color: #16a34a;
                border-color: #15803d;
            }

            .super-admin-toggle .form-check-label {
                font-size: 0.9rem;
                font-weight: 700;
                color: #92400e;
                cursor: pointer;
                min-width: 60px;
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

            /* Make the first control-group (search area) flexible so the search input
               can expand to fill available space and look like the provided design */
            .control-bar .control-group:first-child {
                flex: 1 1 auto;
                min-width: 0;
            }

            .search-box {
                position: relative;
                min-width: 0;
                width: 100%;
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
                width: 100%;
                padding-left: 3rem;
                padding-right: 3rem;
                height: 52px;
                border-radius: 14px;
                border: 1px solid rgba(226, 232, 240, 0.9);
                background: var(--light-gray);
                font-size: 0.95rem;
                transition: all 0.18s ease;
                box-shadow: none;
            }

            .search-box .form-control:focus {
                outline: none;
                border-color: var(--primary);
                background: white;
                box-shadow: 0 6px 20px rgba(99,102,241,0.06);
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
                align-items: start;
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
                padding-left: 0;
            }

            /* Align the category toggle vertically and style to match theme */
            .category-actions {
                display: flex;
                align-items: center;
            }

            .category-actions .form-check.form-switch {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin: 0;
            }

            .category-actions .form-check.form-switch .form-check-input {
                width: 48px;
                height: 24px;
                cursor: pointer;
                border: 2px solid var(--border);
                background-color: var(--light-gray);
            }

            .category-actions .form-check.form-switch .form-check-input:checked {
                background-color: var(--primary);
                border-color: var(--primary);
            }

            .category-actions .form-check-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--gray);
                cursor: pointer;
            }

            /* Collapse Toggle Icon */
            .collapse-toggle {
                width: 36px;
                height: 36px;
                border-radius: 8px;
                background: var(--light-gray);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-left: 1rem;
                transition: all 0.3s ease;
            }

            .collapse-toggle i {
                font-size: 1.25rem;
                color: var(--gray);
                transition: transform 0.3s ease;
            }

            .permission-category-card.expanded .collapse-toggle i {
                transform: rotate(180deg);
            }

            .permission-category-card.expanded .collapse-toggle {
                background: var(--primary);
            }

            .permission-category-card.expanded .collapse-toggle i {
                color: white;
            }

            /* Category Header clickable */
            .category-header {
                cursor: pointer;
            }

            .category-header:hover .collapse-toggle {
                background: var(--primary);
            }

            .category-header:hover .collapse-toggle i {
                color: white;
            }

            /* Category Body - collapsed by default */
            .category-body {
                padding: 1.5rem;
                max-height: 0;
                overflow: hidden;
                padding: 0 1.5rem;
                transition: all 0.3s ease;
            }

            .permission-category-card.expanded .category-body {
                max-height: 2000px;
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
                display: none; /* moved to header */
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            }

            /* Header action buttons (Save / Cancel) */
            .header-right {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .btn-primary-custom {
                background: var(--primary);
                color: white;
                padding: 0.6rem 1.25rem;
                border-radius: 10px;
                border: none;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                box-shadow: 0 6px 16px rgba(99, 102, 241, 0.2);
                cursor: pointer;
            }

            .btn-primary-custom:hover {
                background: var(--primary-dark);
            }

            .header-right .btn.btn-outline-secondary {
                background: white;
                color: var(--gray);
                border: 2px solid var(--border);
                padding: 0.55rem 1rem;
                border-radius: 10px;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .header-right .btn.btn-outline-secondary:hover {
                border-color: var(--primary);
                color: var(--primary);
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
            // Toggle category card expand/collapse
            function toggleCategory(headerElement) {
                const card = headerElement.closest('.permission-category-card');
                if (card) {
                    card.classList.toggle('expanded');
                }
            }

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

                    // Global select all (button) - toggles visible checkboxes
                    if (globalSelectAll) {
                        globalSelectAll.addEventListener('click', function(e) {
                            e.preventDefault();
                            const visibleCheckboxes = document.querySelectorAll('.permission-item:not([style*="display: none"]) .permission-checkbox:not([disabled])');
                            const anyUnchecked = Array.from(visibleCheckboxes).some(cb => !cb.checked);
                            visibleCheckboxes.forEach(cb => cb.checked = anyUnchecked);
                            // Update category select all states based on visible items
                            document.querySelectorAll('.category-select-all').forEach(cb => updateCategorySelectAll(cb));
                            updateCounts();
                        });
                    }

                    // Clear all button - clears every checkbox
                    if (clearAllBtn) {
                        clearAllBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            document.querySelectorAll('.permission-checkbox:not([disabled])').forEach(cb => cb.checked = false);
                            document.querySelectorAll('.category-select-all').forEach(cb => { cb.checked = false; cb.indeterminate = false; });
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
                            `.permission-item:not([style*="display: none"]) .permission-checkbox[data-category="${category}"]:not([disabled])`
                        );
                        const checkedBoxes = document.querySelectorAll(
                            `.permission-item:not([style*="display: none"]) .permission-checkbox[data-category="${category}"]:not([disabled]):checked`
                        );

                        selectAllCheckbox.checked = categoryCheckboxes.length > 0 && 
                            categoryCheckboxes.length === checkedBoxes.length;
                        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && 
                            checkedBoxes.length < categoryCheckboxes.length;
                    }

                    // Super Admin toggle - sync with hidden input inside form
                    const superAdminSwitch = document.getElementById('isSuperAdmin');
                    const superAdminHidden = document.getElementById('isSuperHidden');
                    if (superAdminSwitch && superAdminHidden) {
                        superAdminSwitch.addEventListener('change', function() {
                            // Update hidden input value
                            superAdminHidden.value = this.checked ? '1' : '0';
                            
                            // Update label text
                            const label = this.nextElementSibling;
                            if (label) {
                                label.textContent = this.checked ? 'Active' : 'Inactive';
                            }
                        });
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