@extends('layouts.admin')

@section('title', 'Diamonds')

@section('content')
    <div class="diamond-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Diamonds</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-gem"></i>
                        Diamond Management
                    </h1>
                    <p class="page-subtitle">Manage your diamond inventory and listings</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('diamond.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Diamond</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards (visible to Super Admin only) -->
        @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->is_super)

         <div class="stats-grid">
             <div class="stat-card stat-card-primary">
                 <div class="stat-icon">
                     <i class="bi bi-gem"></i>
                 </div>
                 <div class="stat-content">
                     <div class="stat-label">Total Diamonds</div>
                     <div class="stat-value">{{ $diamonds->count() }}</div>
                     <div class="stat-trend">
                         <i class="bi bi-arrow-up"></i> In Stock
                     </div>
                 </div>
             </div>
 
             <div class="stat-card stat-card-success">
                 <div class="stat-icon">
                     <i class="bi bi-currency-dollar"></i>
                 </div>
                 <div class="stat-content">
                     <div class="stat-label">Total Value</div>
                     <div class="stat-value">${{ number_format($diamonds->sum('price'), 2) }}</div>
                     <div class="stat-trend">
                         <i class="bi bi-graph-up"></i> Inventory
                     </div>
                 </div>
             </div>
 
             <div class="stat-card stat-card-info">
                 <div class="stat-icon">
                     <i class="bi bi-tag"></i>
                 </div>
                 <div class="stat-content">
                     <div class="stat-label">Avg. Price</div>
                     <div class="stat-value">${{ $diamonds->count() > 0 ? number_format($diamonds->avg('price'), 2) : '0.00' }}</div>
                     <div class="stat-trend">
                         <i class="bi bi-calculator"></i> Per Item
                     </div>
                 </div>
             </div>
         </div>

        @endif

        <!-- Success Alert -->
        @if(session('success'))
            <div class="alert-card success">
                <div class="alert-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="alert-content">
                    <h5 class="alert-title">Success!</h5>
                    <p class="alert-message">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-controls">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input id="diamond-search" type="text" class="search-input"
                        placeholder="Search by Stock ID, SKU, or barcode...">
                </div>

                <button id="diamond-reset" class="btn-reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </button>
            </div>

            <div class="filter-info">
                <span id="diamond-count" class="result-count"></span>
            </div>
        </div>

        <!-- Diamonds Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-hash"></i>
                                    <span>Stock ID</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-upc"></i>
                                    <span>SKU</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-currency-dollar"></i>
                                    <span>Price</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-tag"></i>
                                    <span>Listing Price</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-diamond"></i>
                                    <span>Shape</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-upc-scan"></i>
                                    <span>Barcode</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-person-badge"></i>
                                    <span>Assigned By</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-person-check"></i>
                                    <span>Assigned To</span>
                                </div>
                            </th>
                            <th class="text-end">
                                <div class="th-content">
                                    <i class="bi bi-gear"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diamonds as $d)
                            <tr class="diamond-row" data-search="{{ strtolower($d->stockid . ' ' . $d->sku . ' ' . $d->barcode_number) }}">
                                <td>
                                    <div class="cell-content">
                                        <span class="badge-custom badge-primary">{{ $d->stockid }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="text-semibold">{{ $d->sku }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="price-value">${{ number_format($d->price, 2) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="price-value listing">${{ number_format($d->listing_price, 2) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="text-muted">{{ $d->shape ?: '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        @if($d->barcode_image_url)
                                            <img src="{{ $d->barcode_image_url }}" alt="barcode" class="barcode-image">
                                        @else
                                            <span class="badge-custom badge-secondary">{{ $d->barcode_number }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        @if($d->assignedByAdmin)
                                            <span class="badge-custom badge-info">{{ $d->assignedByAdmin->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <div class="admin-assignment">
                                            @if($d->assignedAdmin)
                                                <span class="admin-name badge-custom badge-success">{{ $d->assignedAdmin->name }}</span>
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                            <button type="button" class="btn-reassign" data-diamond-id="{{ $d->id }}" title="Reassign to another admin">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content justify-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('diamond.edit', $d) }}" class="action-btn action-btn-edit" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('diamond.destroy', $d) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this diamond?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn action-btn-delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">
                                    <div class="empty-state-inline">
                                        <div class="empty-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h3 class="empty-title">No diamonds found</h3>
                                        <p class="empty-description">Start by adding your first diamond to the inventory</p>
                                        <a href="{{ route('diamond.create') }}" class="btn-primary-custom">
                                            <i class="bi bi-plus-circle"></i>
                                            <span>Add First Diamond</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="empty-state d-none">
            <div class="empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <h3 class="empty-title">No diamonds found</h3>
            <p class="empty-description">Try adjusting your search criteria</p>
            <button id="empty-reset" class="btn-primary-custom">
                <i class="bi bi-arrow-counterclockwise"></i>
                Reset Search
            </button>
        </div>

        <div class="mt-4">
            {{ $diamonds->appends(request()->query())->links() }}
        </div>
    </div>

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
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

        .diamond-management-container {
            max-width: 1600px;
            margin: 0 auto;
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

        /* Alert Card */
        .alert-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            gap: 1.25rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .alert-card.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.05));
            border: 2px solid rgba(16, 185, 129, 0.2);
        }

        .alert-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .alert-card.success .alert-icon {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.25rem 0;
        }

        .alert-message {
            color: var(--gray);
            margin: 0;
            font-size: 0.95rem;
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

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px var(--shadow);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
        }

        .data-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .th-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .th-content i {
            color: var(--primary);
            font-size: 1rem;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: all 0.2s;
        }

        .data-table tbody tr:hover {
            background: var(--light-gray);
        }

        .data-table td {
            padding: 1.25rem 1.5rem;
        }

        .cell-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cell-content.justify-end {
            justify-content: flex-end;
        }

        .text-semibold {
            font-weight: 600;
            color: var(--dark);
        }

        .text-muted {
            color: var(--gray);
        }

        .price-value {
            font-weight: 600;
            color: var(--success);
            font-family: 'Courier New', monospace;
        }

        .price-value.listing {
            color: var(--info);
        }

        .badge-custom {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .badge-secondary {
            background: var(--light-gray);
            color: var(--gray);
        }

        .barcode-image {
            height: 40px;
            max-width: 120px;
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .action-buttons {
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

        /* Empty States */
        .empty-state-inline {
            text-align: center;
            padding: 4rem 2rem;
        }

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

        .text-end {
            text-align: right;
        }

        .d-inline {
            display: inline;
        }

        .d-none {
            display: none;
        }

        /* Admin Assignment Styles */
        .admin-assignment {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-name {
            white-space: nowrap;
        }

        .btn-reassign {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-reassign:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        /* Reassign Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.d-none {
            display: none;
        }

        .reassign-modal {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .modal-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-select:hover {
            border-color: var(--primary);
        }

        .modal-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-modal {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-modal-primary {
            background: var(--primary);
            color: white;
        }

        .btn-modal-primary:hover {
            background: #5558e3;
        }

        .btn-modal-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .btn-modal-cancel {
            background: var(--border-color);
            color: var(--text-secondary);
        }

        .btn-modal-cancel:hover {
            background: #ddd;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .data-table {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .diamond-management-container {
                padding: 0;
            }

            .page-header {
                border-radius: 12px;
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

            .table-card {
                border-radius: 12px;
            }

            .data-table th,
            .data-table td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }

            .th-content i {
                display: none;
            }
        }
    </style>

    <!-- Reassign Modal -->
    <div id="reassignModal" class="modal-overlay d-none">
        <div class="reassign-modal">
            <div class="modal-header">
                <i class="bi bi-arrow-repeat"></i>
                <span>Reassign Diamond</span>
            </div>
            <div class="modal-body">
                <div>
                    <label class="modal-label">SKU: <span id="modalDiamondSku" class="text-semibold">—</span></label>
                </div>
                <div style="margin-top: 12px;">
                    <label class="modal-label" for="adminSelect">Select Admin</label>
                    <select id="adminSelect" name="admin_id" class="modal-select">
                        <option value="">-- Choose Admin --</option>
                        @foreach($admins ?? [] as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-cancel" id="cancelReassign">Cancel</button>
                <button type="button" class="btn-modal btn-modal-primary" id="confirmReassign">Reassign</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('diamond-search');
            const resetBtn = document.getElementById('diamond-reset');
            const emptyResetBtn = document.getElementById('empty-reset');
            const rows = Array.from(document.querySelectorAll('.diamond-row'));
            const countEl = document.getElementById('diamond-count');
            const emptyState = document.getElementById('empty-state');
            const tableCard = document.querySelector('.table-card');

            const applyFilters = () => {
                const term = search.value.trim().toLowerCase();

                let visibleCount = 0;
                rows.forEach(el => {
                    const searchData = el.dataset.search || '';
                    const isVisible = !term || searchData.includes(term);
                    el.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });

                // Update count
                if (countEl) {
                    countEl.textContent = `Showing ${visibleCount} of ${rows.length} diamonds`;
                }

                // Toggle empty state
                if (emptyState && tableCard) {
                    if (visibleCount === 0 && rows.length > 0) {
                        emptyState.classList.remove('d-none');
                        tableCard.style.display = 'none';
                    } else {
                        emptyState.classList.add('d-none');
                        tableCard.style.display = '';
                    }
                }
            };

            search?.addEventListener('input', applyFilters);

            resetBtn?.addEventListener('click', () => {
                search.value = '';
                applyFilters();
            });

            emptyResetBtn?.addEventListener('click', () => resetBtn?.click());

            // Initial filter application
            applyFilters();

            // Admin Reassignment Logic
            const modal = document.getElementById('reassignModal');
            const adminSelect = document.getElementById('adminSelect');
            const cancelBtn = document.getElementById('cancelReassign');
            const confirmBtn = document.getElementById('confirmReassign');
            const modalDiamondSku = document.getElementById('modalDiamondSku');
            let currentDiamondId = null;

            // Open modal when reassign button clicked
            document.querySelectorAll('.btn-reassign').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentDiamondId = this.dataset.diamondId;
                    const row = this.closest('tr');
                    const sku = row.querySelector('td:nth-child(2) .text-semibold').textContent;
                    
                    modalDiamondSku.textContent = sku;
                    adminSelect.value = '';
                    modal.classList.remove('d-none');
                });
            });

            // Close modal
            cancelBtn?.addEventListener('click', () => {
                modal.classList.add('d-none');
                currentDiamondId = null;
            });

            // Close modal on overlay click
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('d-none');
                    currentDiamondId = null;
                }
            });

            // Confirm reassignment
            confirmBtn?.addEventListener('click', async function() {
                const adminId = adminSelect.value;
                
                if (!adminId) {
                    showAlert('Please select an admin', 'warning', 'Select Admin');
                    return;
                }

                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Reassigning...';

                try {
                    const response = await fetch(`/admin/diamonds/${currentDiamondId}/assign`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({
                            admin_id: adminId
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Show success message
                        showAlert(data.message, 'success', 'Success');
                        
                        // Close modal
                        modal.classList.add('d-none');
                        
                        // Reload the page to see updated assignments
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message || 'Failed to reassign diamond', 'error', 'Error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('An error occurred while reassigning', 'error', 'Error');
                } finally {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Reassign';
                }
            });
        });
    </script>

@endsection