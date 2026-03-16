@extends('layouts.admin')
@section('title', 'Jewellery Stock')

@section('content')
    <style>
        /* ── Status Pills ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.35rem 0.875rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            border: 2px solid transparent;
            white-space: nowrap;
        }

        .status-instock {
            background: rgba(16, 185, 129, 0.12);
            color: #065f46;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .status-low {
            background: rgba(245, 158, 11, 0.12);
            color: #92400e;
            border-color: rgba(245, 158, 11, 0.3);
        }

        .status-sold {
            background: rgba(239, 68, 68, 0.12);
            color: #991b1b;
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* ── Filter Toggle ── */
        .filter-body {
            display: none;
            padding: 1.75rem;
        }

        .filter-body.show {
            display: block;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
            font-size: 0.875rem;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        /* ── Filter Section Groups ── */
        .filter-section-group {
            margin-bottom: 1.5rem;
        }

        .filter-section-group:last-child {
            margin-bottom: 0;
        }

        .filter-section-title {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--dark, #1e293b);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-left: 1rem;
            position: relative;
        }

        .filter-section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 16px;
            background: linear-gradient(135deg, var(--primary, #6366f1), var(--primary-dark, #4f46e5));
            border-radius: 2px;
        }

        .filter-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        /* ── Filter Fields & Inputs ── */
        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-field-range {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .filter-label i {
            color: var(--primary);
            font-size: 0.875rem;
        }

        .filter-label span {
            color: var(--dark);
        }

        .filter-input,
        .filter-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.875rem;
            background: var(--bg-body, #fff);
            transition: all 0.2s ease;
            color: var(--dark);
            font-weight: 500;
        }

        .filter-input::placeholder {
            color: #94a3b8;
        }

        .filter-input:hover,
        .filter-select:hover {
            border-color: #cbd5e1;
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        /* ── Filter Range Inputs ── */
        .filter-range-inputs {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .filter-range-inputs .filter-input {
            flex: 1;
        }

        .range-separator {
            color: var(--gray, #64748b);
            font-weight: 600;
        }

        /* ── Filter Actions ── */
        .filter-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.75rem;
            border-top: 2px solid var(--border, #e2e8f0);
            background: rgba(248, 250, 252, 0.5);
        }

        .filter-actions-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .filter-actions-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .per-page-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-left: 1.5rem;
            border-left: 2px solid var(--border, #e2e8f0);
            font-size: 0.875rem;
            color: var(--gray, #64748b);
        }

        .per-page-select {
            padding: 0.375rem 0.75rem;
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 8px;
            font-size: 0.875rem;
            color: var(--dark, #1e293b);
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .per-page-select:focus {
            border-color: var(--primary, #6366f1);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .btn-filter-apply {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, var(--primary, #6366f1), var(--primary-dark, #4f46e5));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .btn-filter-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-filter-clear {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: white;
            color: var(--gray, #64748b);
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-filter-clear:hover {
            background: var(--light-gray, #f1f5f9);
            color: var(--dark, #1e293b);
            border-color: var(--gray, #64748b);
        }

        /* ── Price Value ── */
        .price-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #059669;
            font-size: 0.9375rem;
        }

        .price-value.listing {
            color: var(--primary, #6366f1);
        }

        /* ── Table Header Content ── */
        .th-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .th-content-center {
            justify-content: center;
        }

        /* ── Pagination Container ── */
        .pagination-container {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
        }

        /* ── Responsive ── */
        @media (max-width: 1200px) {
            .filter-row-3 {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .filter-row-3 {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .filter-actions-left {
                justify-content: space-between;
            }

            .filter-actions-right {
                justify-content: flex-end;
            }

            .per-page-filter {
                border-left: none;
                padding-left: 0;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575px) {
            .diamond-management-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.25rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .filter-actions-left,
            .filter-actions-right {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-filter-apply,
            .btn-filter-clear {
                width: 100%;
                justify-content: center;
            }
        }

        /* ── Dark Mode ── */
        [data-theme="dark"] .status-instock {
            background: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.4);
        }

        [data-theme="dark"] .status-low {
            background: rgba(245, 158, 11, 0.2);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, 0.4);
        }

        [data-theme="dark"] .status-sold {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.4);
        }

        [data-theme="dark"] .filter-actions {
            background: rgba(30, 41, 59, 0.5);
            border-top-color: rgba(51, 65, 85, 0.5);
        }

        [data-theme="dark"] .filter-section-title {
            color: #e2e8f0;
        }

        [data-theme="dark"] .per-page-filter {
            border-left-color: rgba(51, 65, 85, 0.5);
            color: #94a3b8;
        }

        [data-theme="dark"] .per-page-select {
            background: var(--bg-card, #1e293b);
            color: #e2e8f0;
            border-color: rgba(51, 65, 85, 0.5);
        }

        [data-theme="dark"] .btn-filter-clear {
            background: var(--bg-card, #1e293b);
            color: #94a3b8;
            border-color: rgba(51, 65, 85, 0.5);
        }

        [data-theme="dark"] .btn-filter-clear:hover {
            background: rgba(51, 65, 85, 0.5);
            color: #e2e8f0;
        }

        [data-theme="dark"] .price-value {
            color: #34d399;
        }

        [data-theme="dark"] .price-value.listing {
            color: #a5b4fc;
        }

        [data-theme="dark"] .data-table thead {
            background: rgba(51, 65, 85, 0.4);
        }

        [data-theme="dark"] .data-table thead th {
            color: #94a3b8;
            border-bottom-color: rgba(51, 65, 85, 0.5);
        }

        [data-theme="dark"] .data-table tbody tr:hover td {
            background: rgba(99, 102, 241, 0.05);
        }

        /* ── Fixed Stat Cards CSS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px var(--shadow, rgba(0, 0, 0, 0.1));
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            opacity: 0.06;
            transform: translate(20px, -20px);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-body {
            flex: 1;
            min-width: 0;
        }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray, #64748b);
            margin-bottom: 0.2rem;
        }

        .stat-val {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--dark, #1e293b);
            line-height: 1.1;
        }

        /* Primary / Indigo */
        .stat-card-primary {
            border-color: rgba(99, 102, 241, 0.15);
            border-left: 4px solid #6366f1;
        }

        .stat-card-primary::before,
        .stat-card-primary .stat-icon-wrap {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .stat-card-primary:hover {
            border-color: #6366f1;
        }

        /* Success / Green */
        .stat-card-success {
            border-color: rgba(16, 185, 129, 0.15);
            border-left: 4px solid #10b981;
        }

        .stat-card-success::before,
        .stat-card-success .stat-icon-wrap {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .stat-card-success:hover {
            border-color: #10b981;
        }

        /* Warning / Amber */
        .stat-card-warning {
            border-color: rgba(245, 158, 11, 0.15);
            border-left: 4px solid #f59e0b;
        }

        .stat-card-warning::before,
        .stat-card-warning .stat-icon-wrap {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .stat-card-warning:hover {
            border-color: #f59e0b;
        }

        /* Danger / Red */
        .stat-card-danger {
            border-color: rgba(239, 68, 68, 0.15);
            border-left: 4px solid #ef4444;
        }

        .stat-card-danger::before,
        .stat-card-danger .stat-icon-wrap {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .stat-card-danger:hover {
            border-color: #ef4444;
        }

        /* Info / Blue */
        .stat-card-info {
            border-color: rgba(59, 130, 246, 0.15);
            border-left: 4px solid #3b82f6;
        }

        .stat-card-info::before,
        .stat-card-info .stat-icon-wrap {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .stat-card-info:hover {
            border-color: #3b82f6;
        }

        [data-theme="dark"] .stat-card {
            background: #1e293b;
        }

        [data-theme="dark"] .stat-val {
            color: #f1f5f9;
        }
    </style>
 
    <div class="diamond-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <nav class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i>
                            <span>Dashboard</span>
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Jewellery Stock</span>
                    </nav>
                    <h1 class="page-title">
                        <i class="bi bi-gem"></i>
                        Jewellery Stock Management
                    </h1>
                    <p class="page-subtitle">Manage rings, earrings, tennis bracelets and other jewellery inventory</p>
                </div>
                <div class="header-right">
                    @if (auth()->guard('admin')->user() &&
                            auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.create']))
                        <a href="{{ route('jewellery-stock.create') }}" class="btn-primary-custom">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add Jewellery</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Grid (Super Admin Only) -->
        @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->is_super)
            <div class="stats-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon-wrap">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Total Items</div>
                        <div class="stat-val">{{ number_format($totalItems ?? 0) }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon-wrap">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">In Stock</div>
                        <div class="stat-val">{{ number_format($inStockCount ?? 0) }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon-wrap">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Low Stock</div>
                        <div class="stat-val">{{ number_format($lowStockCount ?? 0) }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon-wrap">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Out of Stock</div>
                        <div class="stat-val">{{ number_format($outOfStockCount ?? 0) }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-info">
                    <div class="stat-icon-wrap">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-label">Total Value</div>
                        <div class="stat-val">${{ number_format($totalValue ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-header">
                <button type="button" class="btn-toggle-filters" id="toggleFilters">
                    <i class="bi bi-funnel"></i>
                    <span>Filters</span>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
            </div>

            <form method="GET" action="{{ route('jewellery-stock.index') }}" id="filterForm">
                <div class="filter-body" id="filterBody">
                    <!-- Search Filters -->
                    <div class="filter-section-group">
                        <h6 class="filter-section-title">
                            <i class="bi bi-search"></i> Search
                        </h6>
                        <div class="filter-row-3">
                            <div class="filter-field">
                                <label class="filter-label">
                                    <i class="bi bi-upc-scan"></i>
                                    <span>SKU</span>
                                </label>
                                <input type="text" name="sku" class="filter-input" placeholder="Search by SKU..."
                                    value="{{ request('sku') }}">
                            </div>
                            <div class="filter-field">
                                <label class="filter-label">
                                    <i class="bi bi-tag"></i>
                                    <span>Name</span>
                                </label>
                                <input type="text" name="name" class="filter-input" placeholder="Search by name..."
                                    value="{{ request('name') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Properties Filters -->
                    <div class="filter-section-group">
                        <h6 class="filter-section-title">
                            <i class="bi bi-sliders"></i> Properties
                        </h6>
                        <div class="filter-row-3">
                            <div class="filter-field">
                                <label class="filter-label">
                                    <i class="bi bi-collection"></i>
                                    <span>Type</span>
                                </label>
                                <select name="type" class="filter-select">
                                    <option value="">All Types</option>
                                    <option value="ring" {{ request('type') == 'ring' ? 'selected' : '' }}>Ring</option>
                                    <option value="earrings" {{ request('type') == 'earrings' ? 'selected' : '' }}>
                                        Earrings</option>
                                    <option value="tennis_bracelet"
                                        {{ request('type') == 'tennis_bracelet' ? 'selected' : '' }}>Tennis Bracelet
                                    </option>
                                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                            </div>
                            <div class="filter-field">
                                <label class="filter-label">
                                    <i class="bi bi-palette"></i>
                                    <span>Metal Type</span>
                                </label>
                                <select name="metal_type_id" class="filter-select">
                                    <option value="">All Metals</option>
                                    @foreach ($metalTypes as $metal)
                                        <option value="{{ $metal->id }}"
                                            {{ request('metal_type_id') == $metal->id ? 'selected' : '' }}>
                                            {{ $metal->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-field">
                                <label class="filter-label">
                                    <i class="bi bi-circle"></i>
                                    <span>Status</span>
                                </label>
                                <select name="status" class="filter-select">
                                    <option value="">All Statuses</option>
                                    <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In
                                        Stock</option>
                                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low
                                        Stock</option>
                                    <option value="out_of_stock"
                                        {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-section-group">
                        <h6 class="filter-section-title">
                            <i class="bi bi-currency-dollar"></i> Price Range
                        </h6>
                        <div class="filter-row-3">
                            <div class="filter-field filter-field-range">
                                <label class="filter-label">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>Selling Price</span>
                                </label>
                                <div class="filter-range-inputs">
                                    <input type="number" name="min_price" class="filter-input" placeholder="Min"
                                        step="0.01" min="0" value="{{ request('min_price') }}">
                                    <span class="range-separator">—</span>
                                    <input type="number" name="max_price" class="filter-input" placeholder="Max"
                                        step="0.01" min="0" value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="filter-actions">
                    <div class="filter-actions-left">
                        <span class="result-count">
                            <i class="bi bi-list-ul"></i>
                            {{ $items->total() }} result{{ $items->total() !== 1 ? 's' : '' }}
                        </span>
                        <div class="per-page-filter">
                            <label>Show</label>
                            <select name="per_page" class="per-page-select"
                                onchange="document.getElementById('filterForm').submit()">
                                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions-right">
                        <a href="{{ route('jewellery-stock.index') }}" class="btn-filter-clear">
                            <i class="bi bi-x-circle"></i>
                            <span>Clear</span>
                        </a>
                        <button type="submit" class="btn-filter-apply">
                            <i class="bi bi-funnel-fill"></i>
                            <span>Apply Filters</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-upc-scan"></i>
                                    <span>SKU</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-tag"></i>
                                    <span>Name</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-collection"></i>
                                    <span>Type</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-palette"></i>
                                    <span>Metal</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-vinyl"></i>
                                    <span>Ring Size</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-speedometer"></i>
                                    <span>Weight</span>
                                </div>
                            </th>
                            <th class="text-center">
                                <div class="th-content th-content-center">
                                    <i class="bi bi-box-seam"></i>
                                    <span>Qty</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-cash"></i>
                                    <span>Purchase</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>Selling</span>
                                </div>
                            </th>
                            <th class="text-center">
                                <div class="th-content th-content-center">
                                    <i class="bi bi-circle"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="text-center">
                                <div class="th-content th-content-center">
                                    <i class="bi bi-gear"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <div class="cell-content">
                                        <span class="badge-custom badge-primary">{{ $item->sku }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="text-semibold">{{ $item->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span
                                            class="badge-custom badge-secondary">{{ ucwords(str_replace('_', ' ', $item->type)) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="text-muted">{{ $item->metalType->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="text-muted">{{ $item->ringSize->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="text-muted">{{ number_format($item->weight, 3) }}g</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="cell-content cell-content-center">
                                        <span class="text-semibold">{{ $item->quantity }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="price-value">${{ number_format($item->purchase_price, 2) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span
                                            class="price-value listing">${{ number_format($item->selling_price, 2) }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="cell-content cell-content-center">
                                        @if ($item->status === 'in_stock')
                                            <span class="status-pill status-instock">In Stock</span>
                                        @elseif($item->status === 'low_stock')
                                            <span class="status-pill status-low">Low Stock</span>
                                        @else
                                            <span class="status-pill status-sold">Out of Stock</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content cell-content-center">
                                        <div class="action-buttons">
                                            @if (auth()->guard('admin')->user() &&
                                                    auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.view']))
                                                <a href="{{ route('jewellery-stock.show', $item) }}"
                                                    class="action-btn action-btn-view" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif

                                            @if (auth()->guard('admin')->user() &&
                                                    auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.edit']))
                                                <a href="{{ route('jewellery-stock.edit', $item) }}"
                                                    class="action-btn action-btn-edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif

                                            @if (auth()->guard('admin')->user() &&
                                                    auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.delete']))
                                                <form action="{{ route('jewellery-stock.destroy', $item) }}"
                                                    method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="action-btn action-btn-delete delete-btn"
                                                        title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11">
                                    <div class="empty-state-inline">
                                        <div class="empty-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h3 class="empty-title">No jewellery items found</h3>
                                        <p class="empty-description">Start by adding your first jewellery item to the
                                            inventory</p>
                                        @if (auth()->guard('admin')->user() &&
                                                auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.create']))
                                            <a href="{{ route('jewellery-stock.create') }}" class="btn-primary-custom">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add First Item</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if ($items->hasPages())
            <div class="pagination-container">
                {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle filters
                const toggleBtn = document.getElementById('toggleFilters');
                const filterBody = document.getElementById('filterBody');
                if (toggleBtn && filterBody) {
                    toggleBtn.addEventListener('click', function() {
                        filterBody.classList.toggle('show');
                        this.querySelector('.toggle-icon').classList.toggle('rotated');
                    });

                    // Auto-show filters if any filter is active
                    const urlParams = new URLSearchParams(window.location.search);
                    const filterKeys = ['sku', 'name', 'type', 'metal_type_id', 'status', 'min_price', 'max_price'];
                    const hasFilters = filterKeys.some(key => urlParams.get(key));
                    if (hasFilters) {
                        filterBody.classList.add('show');
                        toggleBtn.querySelector('.toggle-icon').classList.add('rotated');
                    }
                }

                // Delete confirmation
                document.querySelectorAll('.delete-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        if (confirm('Are you sure you want to delete this jewellery item?')) {
                            this.closest('.delete-form').submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
