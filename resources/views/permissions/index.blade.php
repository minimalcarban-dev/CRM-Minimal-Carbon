@extends('layouts.admin')
@section('title', 'Permission Management')

@php
    use Illuminate\Support\Str;

    // Group permissions by module name prefix (before first dot)
    $grouped = $permissions
        ->groupBy(function ($permission) {
            $parts = explode('.', $permission->slug);
            return $parts[0] ?? 'Other';
        })
        ->sortKeys();

    // Define known actions and badge colors
    $actionOrder = ['view', 'create', 'edit', 'delete', 'assign_permissions', 'access'];
    $badgeMap = [
        'view' => 'info',
        'create' => 'success',
        'edit' => 'warning',
        'delete' => 'danger',
        'assign_permissions' => 'purple',
        'access' => 'primary',
    ];

    $iconMap = [
        'view' => 'bi-eye',
        'create' => 'bi-plus-circle',
        'edit' => 'bi-pencil-square',
        'delete' => 'bi-trash',
        'assign_permissions' => 'bi-shield-check',
        'access' => 'bi-door-open',
    ];

    // Calculate high-level metrics
    $totalPermissions = $permissions->count();
    $totalModules = $grouped->keys()->count();
    $allActionsPresent = $permissions->map(fn($p) => explode('.', $p->slug)[1] ?? 'other')->unique()->values();

    // Format module names nicely
    function humanize_module($name)
    {
        return Str::title(str_replace(['_', '-'], ' ', $name));
    }
@endphp

@section('content')
    <div class="permission-management-container">
        <!-- Header Section -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Permissions</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-shield-lock-fill"></i>
                        Permission Management
                    </h1>
                    <p class="page-subtitle">Manage and organize system permissions by modules and actions</p>
                </div>
                <div class="header-right">
                    @if (isset($currentAdmin) && ($currentAdmin->is_super || $currentAdmin->hasPermission('permissions.create')))
                        <a href="{{ route('permissions.create') }}" class="btn-primary-custom">
                            <i class="bi bi-plus-circle"></i>
                            <span>Create Permission</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Permissions</div>
                    <div class="stat-value">{{ $totalPermissions }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-arrow-up"></i> Active
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Modules</div>
                    <div class="stat-value">{{ $totalModules }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-diagram-3"></i> Categories
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-lightning"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Action Types</div>
                    <div class="stat-value">{{ $allActionsPresent->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-check-circle"></i> Available
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="filter-section">
            <div class="filter-controls">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input id="perm-search" type="text" class="search-input"
                        placeholder="Search permissions, slugs, or descriptions...">
                </div>

                <div class="filter-group">
                    <select id="perm-module" class="filter-select">
                        <option value="">All Modules</option>
                        @foreach ($grouped->keys() as $module)
                            <option value="{{ $module }}">{{ humanize_module($module) }}</option>
                        @endforeach
                    </select>

                    <select id="perm-action" class="filter-select">
                        <option value="">All Actions</option>
                        @foreach ($actionOrder as $action)
                            <option value="{{ $action }}">{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                        @endforeach
                    </select>

                    <button id="perm-reset" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </button>
                </div>
            </div>

            <div class="filter-info">
                <span id="perm-count" class="result-count"></span>
            </div>
        </div>

        <!-- Permission Modules -->
        <div class="modules-container">
            @foreach ($grouped as $module => $permissionsGroup)
                @php
                    $moduleId = str_replace(['.', ' ', '/'], '-', $module);
                    $moduleLabel = humanize_module($module);
                    $presentActions = collect($permissionsGroup)
                        ->map(fn($p) => explode('.', $p->slug)[1] ?? 'other')
                        ->unique()
                        ->values()
                        ->all();
                @endphp

                <div class="module-card permission-section" data-module="{{ $module }}">
                    <div class="module-header" onclick="toggleModule('{{ $moduleId }}')">
                        <div class="module-info">
                            <h3 class="module-title">
                                <i class="bi bi-folder2-open"></i>
                                {{ $moduleLabel }}
                            </h3>
                            <span class="module-count">{{ $permissionsGroup->count() }} permissions</span>
                        </div>

                        <div class="module-actions">
                            <div class="action-badges">
                                @foreach ($presentActions as $act)
                                    <span class="action-badge action-{{ $act }}">
                                        <i class="bi {{ $iconMap[$act] ?? 'bi-circle-fill' }}"></i>
                                        {{ ucfirst(str_replace('_', ' ', $act)) }}
                                    </span>
                                @endforeach
                            </div>
                            <i class="bi bi-chevron-down collapse-icon" id="icon-{{ $moduleId }}"></i>
                        </div>
                    </div>

                    <div id="module-{{ $moduleId }}" class="module-body" style="display: block;">
                        <div class="permissions-grid">
                            @foreach ($permissionsGroup->sortBy('slug') as $perm)
                                @php
                                    $parts = explode('.', $perm->slug);
                                    $action = $parts[1] ?? 'other';
                                    $badge = $badgeMap[$action] ?? 'secondary';
                                @endphp

                                <div class="permission-card perm-item" data-module="{{ $module }}" data-action="{{ $action }}">
                                    <div class="permission-header">
                                        <div class="permission-badge badge-{{ $action }}">
                                            <i class="bi {{ $iconMap[$action] ?? 'bi-circle-fill' }}"></i>
                                        </div>
                                        <div class="permission-actions">
                                            @if ($currentAdmin->is_super || $currentAdmin->hasPermission('permissions.view'))
                                                <a href="{{ route('permissions.show', $perm) }}" class="action-btn action-btn-view"
                                                    title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif
                                            @if ($currentAdmin->is_super || $currentAdmin->hasPermission('permissions.edit'))
                                                <a href="{{ route('permissions.edit', $perm) }}" class="action-btn action-btn-edit"
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                            @if ($currentAdmin->is_super || $currentAdmin->hasPermission('permissions.delete'))
                                                <form action="{{ route('permissions.delete', $perm) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this permission?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="action-btn action-btn-delete" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="permission-content">
                                        <h4 class="permission-name">{{ $perm->name }}</h4>
                                        @if (!empty($perm->description))
                                            <p class="permission-description">{{ $perm->description }}</p>
                                        @endif
                                        <div class="permission-meta">
                                            <code class="permission-slug" onclick="copySlug('{{ $perm->slug }}', this)">
                                                            <i class="bi bi-code-slash"></i>
                                                            {{ $perm->slug }}
                                                        </code>
                                            <span class="permission-type">{{ ucfirst(str_replace('_', ' ', $action)) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="empty-state d-none">
            <div class="empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <h3 class="empty-title">No permissions found</h3>
            <p class="empty-description">Try adjusting your search criteria or filters</p>
            <button id="empty-reset" class="btn-primary-custom">
                <i class="bi bi-arrow-counterclockwise"></i>
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-notification" id="toast-copy">
        <i class="bi bi-check-circle-fill"></i>
        <span>Slug copied to clipboard!</span>
    </div>

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --purple: #a855f7;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
            --shadow-md: rgba(0, 0, 0, 0.1);
            --shadow-lg: rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
        }

        .permission-management-container {
            padding: 2rem;
            max-width: 1600px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

        /* Header */
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1rem;
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
            font-size: 0.75rem;
        }

        .breadcrumb-current {
            color: var(--dark);
            font-weight: 500;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary);
        }

        .page-subtitle {
            color: var(--gray);
            margin: 0;
            font-size: 1rem;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px var(--shadow-md);
        }

        .stat-card-primary {
            border-color: rgba(99, 102, 241, 0.1);
        }

        .stat-card-primary:hover {
            border-color: var(--primary);
        }

        .stat-card-success {
            border-color: rgba(16, 185, 129, 0.1);
        }

        .stat-card-success:hover {
            border-color: var(--success);
        }

        .stat-card-info {
            border-color: rgba(59, 130, 246, 0.1);
        }

        .stat-card-info:hover {
            border-color: var(--info);
        }

        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .stat-card-primary .stat-icon {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
        }

        .stat-card-success .stat-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .stat-card-info .stat-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            color: var(--info);
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-trend {
            font-size: 0.875rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: var(--light-gray);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .filter-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 180px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-reset {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            background: white;
            color: var(--gray);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-reset:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .filter-info {
            display: flex;
            justify-content: flex-end;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border);
        }

        .result-count {
            color: var(--gray);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Module Cards */
        .modules-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .module-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px var(--shadow);
            overflow: hidden;
            transition: all 0.3s;
        }

        .module-card:hover {
            box-shadow: 0 4px 12px var(--shadow-md);
        }

        .module-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .module-header:hover {
            background: linear-gradient(135deg, #e0e7ff, white);
        }

        .module-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .module-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .module-title i {
            color: var(--primary);
        }

        .module-count {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .module-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .action-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-view {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .action-create {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .action-edit {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .action-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .action-assign_permissions {
            background: rgba(168, 85, 247, 0.1);
            color: var(--purple);
        }

        .action-access {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .collapse-icon {
            font-size: 1.25rem;
            color: var(--gray);
            transition: transform 0.3s ease;
        }

        .module-body {
            padding: 1.5rem;
            overflow: visible;
            transition: all 0.3s ease;
            max-height: none;
        }

        .module-body.collapsed {
            max-height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            overflow: hidden !important;
        }

        /* Permissions Grid */
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
        }

        .permission-card {
            background: white;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .permission-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gray);
            transition: all 0.3s;
        }

        .permission-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px var(--shadow-md);
        }

        .permission-card:hover::before {
            background: var(--primary);
        }

        .permission-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .permission-badge {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .badge-view {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .badge-create {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge-edit {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .badge-assign_permissions {
            background: rgba(168, 85, 247, 0.1);
            color: var(--purple);
        }

        .badge-access {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .badge-other {
            background: var(--light-gray);
            color: var(--gray);
        }

        .permission-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border);
            background: white;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-md);
        }

        .action-btn-view:hover {
            border-color: var(--info);
            color: var(--info);
            background: rgba(59, 130, 246, 0.05);
        }

        .action-btn-edit:hover {
            border-color: var(--warning);
            color: var(--warning);
            background: rgba(245, 158, 11, 0.05);
        }

        .action-btn-delete:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        .permission-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .permission-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
        }

        .permission-description {
            font-size: 0.9rem;
            color: var(--gray);
            margin: 0 0 1rem 0;
            line-height: 1.5;
            flex: 1;
        }

        .permission-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            margin-top: auto;
        }

        .permission-slug {
            background: var(--light-gray);
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            color: var(--primary);
            font-family: 'Courier New', monospace;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid transparent;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .permission-slug:hover {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
        }

        .permission-type {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary);
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
        }

        .empty-description {
            color: var(--gray);
            margin: 0 0 2rem 0;
        }

        /* Toast */
        .toast-notification {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
            transform: translateY(150%);
            transition: transform 0.3s ease;
            z-index: 9999;
        }

        .toast-notification.show {
            transform: translateY(0);
        }

        .toast-notification i {
            font-size: 1.25rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .permissions-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .permission-management-container {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
            }

            .header-right {
                width: 100%;
            }

            .btn-primary-custom {
                width: 100%;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-controls {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            .filter-group {
                flex-direction: column;
                width: 100%;
            }

            .filter-select {
                width: 100%;
            }

            .module-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .module-actions {
                width: 100%;
                justify-content: space-between;
            }

            .permissions-grid {
                grid-template-columns: 1fr;
            }

            .toast-notification {
                left: 1rem;
                right: 1rem;
                bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .action-badges {
                display: none;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .permission-card {
            animation: fadeIn 0.3s ease forwards;
            opacity: 0;
        }

        /* Print Styles */
        @media print {

            .page-header,
            .filter-section,
            .action-btn,
            .toast-notification {
                display: none;
            }

            .module-card {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid var(--border);
            }
        }
    </style>

    <script>
        function copySlug(text, element) {
            navigator.clipboard.writeText(text).then(() => {
                const toast = document.getElementById('toast-copy');
                toast.classList.add('show');

                // Add visual feedback to clicked element
                const originalBg = element.style.background;
                const originalColor = element.style.color;
                const originalBorder = element.style.borderColor;

                element.style.background = 'var(--primary)';
                element.style.color = 'white';
                element.style.borderColor = 'var(--primary)';

                setTimeout(() => {
                    toast.classList.remove('show');
                    element.style.background = originalBg;
                    element.style.color = originalColor;
                    element.style.borderColor = originalBorder;
                }, 2500);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }

        function toggleModule(moduleId) {
            const moduleBody = document.getElementById('module-' + moduleId);
            const icon = document.getElementById('icon-' + moduleId);
            const openKey = 'perm_open_sections';

            if (moduleBody && icon) {
                const isCollapsed = moduleBody.classList.contains('collapsed');

                if (isCollapsed) {
                    // Expand
                    moduleBody.classList.remove('collapsed');
                    icon.style.transform = 'rotate(180deg)';

                    // Save state
                    try {
                        const stored = JSON.parse(localStorage.getItem(openKey) || '[]');
                        const next = new Set(stored);
                        next.add(moduleId);
                        localStorage.setItem(openKey, JSON.stringify(Array.from(next)));
                    } catch (_) { }
                } else {
                    // Collapse
                    moduleBody.classList.add('collapsed');
                    icon.style.transform = 'rotate(0deg)';

                    // Save state
                    try {
                        const stored = JSON.parse(localStorage.getItem(openKey) || '[]');
                        const next = new Set(stored);
                        next.delete(moduleId);
                        localStorage.setItem(openKey, JSON.stringify(Array.from(next)));
                    } catch (_) { }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('perm-search');
            const moduleFilter = document.getElementById('perm-module');
            const actionFilter = document.getElementById('perm-action');
            const resetBtn = document.getElementById('perm-reset');
            const emptyResetBtn = document.getElementById('empty-reset');
            const rows = Array.from(document.querySelectorAll('.perm-item'));
            const sections = Array.from(document.querySelectorAll('.permission-section'));
            const countEl = document.getElementById('perm-count');
            const emptyState = document.getElementById('empty-state');

            const matches = (el, term) => el.textContent.toLowerCase().includes(term.toLowerCase());

            const applyFilters = () => {
                const term = search.value.trim();
                const module = moduleFilter.value;
                const action = actionFilter.value;

                let visibleCount = 0;
                rows.forEach(el => {
                    const okSearch = !term || matches(el, term);
                    const okModule = !module || el.dataset.module === module;
                    const okAction = !action || el.dataset.action === action;
                    const isVisible = okSearch && okModule && okAction;
                    el.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });

                // Update section visibility
                sections.forEach(section => {
                    const anyVisible = Array.from(section.querySelectorAll('.perm-item')).some(r => r
                        .style.display !== 'none');
                    section.style.display = anyVisible ? '' : 'none';
                });

                // Update count and empty state
                if (countEl) {
                    countEl.textContent = `Showing ${visibleCount} of ${rows.length} permissions`;
                }
                if (emptyState) {
                    if (visibleCount === 0) {
                        emptyState.classList.remove('d-none');
                    } else {
                        emptyState.classList.add('d-none');
                    }
                }
            };

            search?.addEventListener('input', applyFilters);
            moduleFilter?.addEventListener('change', applyFilters);
            actionFilter?.addEventListener('change', applyFilters);

            resetBtn?.addEventListener('click', () => {
                search.value = '';
                moduleFilter.value = '';
                actionFilter.value = '';
                applyFilters();
            });

            emptyResetBtn?.addEventListener('click', () => resetBtn?.click());

            // Initialize all modules with proper state
            const openKey = 'perm_open_sections';
            let savedState = [];
            try {
                savedState = JSON.parse(localStorage.getItem(openKey) || '[]');
            } catch (_) { }

            document.querySelectorAll('.module-body').forEach(moduleBody => {
                const moduleId = moduleBody.id.replace('module-', '');
                const icon = document.getElementById('icon-' + moduleId);

                // Check if module should be open or closed
                const shouldBeOpen = savedState.includes(moduleId);

                if (!shouldBeOpen) {
                    // Close it if not in saved open state
                    moduleBody.classList.add('collapsed');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                } else {
                    // Keep it open
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            });

            // Initial filter application
            applyFilters();

            // Add stagger animation to permission cards
            const cards = document.querySelectorAll('.permission-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${(index % 6) * 0.05}s`;
            });
        });
    </script>
@endsection