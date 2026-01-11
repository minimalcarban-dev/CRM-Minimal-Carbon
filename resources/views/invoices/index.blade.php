@extends('layouts.admin')

@section('title', 'Invoices Management')

@section('content')
    <div class="orders-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Invoices</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-receipt"></i>
                        Invoices Management
                    </h1>
                    <p class="page-subtitle">Manage and track all invoices and billing information</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('invoices.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Invoice</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Invoices</div>
                    <div class="stat-value">{{ $invoices->total() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-graph-up"></i> All invoices
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('invoices.index') }}" class="filter-form">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search by invoice no or company..."
                        value="{{ request('search') }}">
                </div>

                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                    <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i>
                    <span>Filter</span>
                </button>

                @if(request('search') || request('status'))
                    <a href="{{ route('invoices.index') }}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            <div class="filter-info">
                <span class="result-count">Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of
                    {{ $invoices->total() }} invoices</span>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="orders-table-card">
            <div class="table-container">
                @if($invoices->count() > 0)
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th class="th-id">
                                    <div class="th-content">
                                        <i class="bi bi-hash"></i>
                                        <span>ID</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-file-text"></i>
                                        <span>Invoice No</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-building"></i>
                                        <span>Company</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>Date</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-currency-dollar"></i>
                                        <span>Total</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-flag"></i>
                                        <span>Status</span>
                                    </div>
                                </th>
                                <th class="th-actions">
                                    <div class="th-content">
                                        <i class="bi bi-gear"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                                <tr class="table-row">
                                    <td class="td-id">
                                        <span class="order-id-badge">#{{ $inv->id }}</span>
                                    </td>
                                    <td>
                                        <span class="invoice-no">{{ $inv->invoice_no }}</span>
                                    </td>
                                    <td>
                                        <span class="company-name">{{ $inv->company->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if($inv->invoice_date)
                                            <div class="date-info">
                                                <span
                                                    class="date-main">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('M d, Y') }}</span>
                                                <span
                                                    class="date-time">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('l') }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="amount-value">
                                            $ {{ number_format($inv->total_invoice_value, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($inv->status == 'draft')
                                            <span class="status-badge status-warning">
                                                <i class="bi bi-pencil-square"></i>
                                                Draft
                                            </span>
                                        @elseif($inv->status == 'done')
                                            <span class="status-badge status-success">
                                                <i class="bi bi-check-circle"></i>
                                                Done
                                            </span>
                                        @elseif($inv->status == 'final')
                                            <span class="status-badge status-success">
                                                <i class="bi bi-check-circle"></i>
                                                Final
                                            </span>
                                        @elseif($inv->status == 'cancelled')
                                            <span class="status-badge status-danger">
                                                <i class="bi bi-x-circle"></i>
                                                Cancelled
                                            </span>
                                        @else
                                            <span class="status-badge status-secondary">
                                                <i class="bi bi-circle"></i>
                                                {{ ucfirst($inv->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="td-actions">
                                        <div class="action-buttons">
                                            <a href="{{ route('invoices.show', $inv->id) }}" class="action-btn action-btn-view"
                                                title="View Invoice">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('invoices.edit', $inv->id) }}" class="action-btn action-btn-edit"
                                                title="Edit Invoice">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('invoices.pdf', $inv->id) }}" class="action-btn action-btn-download"
                                                title="Download PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h3 class="empty-title">No invoices found</h3>
                        <p class="empty-description">
                            @if(request('search') || request('status'))
                                No invoices match your search criteria. Try adjusting your filters.
                            @else
                                Get started by creating your first invoice.
                            @endif
                        </p>
                        @if(request('search') || request('status'))
                            <a href="{{ route('invoices.index') }}" class="btn-primary-custom">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset Filters
                            </a>
                        @else
                            <a href="{{ route('invoices.create') }}" class="btn-primary-custom">
                                <i class="bi bi-plus-circle"></i>
                                Create First Invoice
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="pagination-container">
                    {{ $invoices->links('pagination::bootstrap-5') }}
                </div>
            @endif
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

        .orders-management-container {
            padding: 2rem;
            max-width: 1800px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

        /* Page Header */
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .filter-form {
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

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 180px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-filter,
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
            text-decoration: none;
        }

        .btn-filter:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .btn-reset:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
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

        /* Table */
        .orders-table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px var(--shadow);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead {
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
        }

        .orders-table th {
            padding: 1.25rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
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

        .th-id {
            width: 80px;
        }

        .th-actions {
            width: 180px;
            text-align: center;
        }

        .th-actions .th-content {
            justify-content: center;
        }

        .table-row {
            border-bottom: 1px solid var(--border);
            transition: all 0.2s;
        }

        .table-row:hover {
            background: var(--light-gray);
        }

        .orders-table td {
            padding: 1.25rem 1.5rem;
            color: var(--dark);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .td-id {
            font-weight: 600;
        }

        .order-id-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .invoice-no {
            font-weight: 600;
            color: var(--dark);
        }

        .company-name {
            font-weight: 500;
            color: var(--dark);
        }

        .date-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .date-main {
            font-weight: 500;
            color: var(--dark);
        }

        .date-time {
            font-size: 0.875rem;
            color: var(--gray);
        }

        .amount-value {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--success);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .status-secondary {
            background: rgba(100, 116, 139, 0.1);
            color: var(--gray);
        }

        .td-actions {
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
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

        .action-btn-download:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
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

        /* Pagination */
        .pagination-container {
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
        }

        .pagination-container .pagination {
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .orders-management-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
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

            .filter-form {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            .filter-select {
                width: 100%;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .orders-table {
                min-width: 1000px;
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

        .stat-card {
            animation: fadeIn 0.4s ease forwards;
            opacity: 0;
        }

        .table-row {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Text Utilities */
        .text-muted {
            color: var(--gray) !important;
        }

        /* Bootstrap Pagination Override */
        .pagination-container .pagination .page-link {
            color: var(--primary);
            border: 2px solid var(--border);
            border-radius: 8px;
            margin: 0 0.25rem;
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .pagination-container .pagination .page-link:hover {
            background: rgba(99, 102, 241, 0.05);
            border-color: var(--primary);
            color: var(--primary);
        }

        .pagination-container .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .pagination-container .pagination .page-item.disabled .page-link {
            color: var(--gray);
            border-color: var(--border);
            opacity: 0.5;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add stagger animation to table rows
            const rows = document.querySelectorAll('.table-row');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${(index % 10) * 0.05}s`;
            });

            // Initialize stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.opacity = '1';
                }, 100 * (index + 1));
            });
        });
    </script>

@endsection