@extends('layouts.admin')

@section('title', 'Companies Management')

@php
    // Calculate stats
    $totalCompanies = $companies->total();
    $activeCount = 0;
    $inactiveCount = 0;

    foreach ($companies as $company) {
        if ($company->status === 'active') {
            $activeCount++;
        } else {
            $inactiveCount++;
        }
    }
@endphp

@section('content')
    <div class="companies-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Companies</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-buildings-fill"></i>
                        Companies Management
                    </h1>
                    <p class="page-subtitle">Manage all registered companies in the system</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('companies.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Company</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-buildings"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Companies</div>
                    <div class="stat-value">{{ $totalCompanies }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-graph-up"></i> All companies
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Active</div>
                    <div class="stat-value">{{ $activeCount }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-arrow-up"></i> Currently active
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-pause-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Inactive</div>
                    <div class="stat-value">{{ $inactiveCount }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-dash-circle"></i> Not active
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-calendar-plus"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">
                        {{ $companies->filter(function ($c) {
        return $c->created_at->isCurrentMonth(); })->count() }}
                    </div>
                    <div class="stat-trend">
                        <i class="bi bi-calendar-check"></i> New additions
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('companies.index') }}" class="filter-form">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search company name or email..."
                        value="{{ request('search') }}">
                </div>

                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i>
                    <span>Filter</span>
                </button>

                @if(request('search') || request('status'))
                    <a href="{{ route('companies.index') }}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            <div class="filter-info">
                <span class="result-count">Showing {{ $companies->firstItem() ?? 0 }} to {{ $companies->lastItem() ?? 0 }}
                    of
                    {{ $companies->total() }} companies</span>
            </div>
        </div>

        <!-- Companies Table -->
        <div class="companies-table-card">
            <div class="table-container">
                @if($companies->count() > 0)
                    <table class="companies-table">
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
                                        <i class="bi bi-building"></i>
                                        <span>Company Name</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-envelope"></i>
                                        <span>Email</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-telephone"></i>
                                        <span>Phone</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-toggle-on"></i>
                                        <span>Status</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>Created At</span>
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
                            @foreach ($companies as $company)
                                <tr class="table-row">
                                    <td class="td-id">
                                        <span class="company-id-badge">#{{ $company->id }}</span>
                                    </td>
                                    <td>
                                        <div class="company-info">
                                            <div class="company-avatar" onclick="showCompanyModal({{ $company->id }})" style="cursor: pointer;">
                                                @if($company->logo)
                                                    <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}">
                                                @else
                                                    {{ strtoupper(substr($company->name, 0, 2)) }}
                                                @endif
                                            </div>
                                            <span class="company-name" onclick="showCompanyModal({{ $company->id }})" style="cursor: pointer;">{{ $company->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($company->email)
                                            <div class="contact-info">
                                                <i class="bi bi-envelope-fill contact-icon"></i>
                                                <span>{{ $company->email }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($company->phone)
                                            <div class="contact-info">
                                                <i class="bi bi-telephone-fill contact-icon"></i>
                                                <span>{{ $company->phone }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($company->status == 'active')
                                            <span class="status-badge status-success">
                                                <i class="bi bi-check-circle"></i>
                                                Active
                                            </span>
                                        @else
                                            <span class="status-badge status-secondary">
                                                <i class="bi bi-pause-circle"></i>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <span class="date-main">{{ $company->created_at->format('M d, Y') }}</span>
                                            <span class="date-time">{{ $company->created_at->format('l') }}</span>
                                        </div>
                                    </td>
                                    <td class="td-actions">
                                        <div class="action-buttons">
                                            <a href="{{ route('companies.edit', $company->id) }}" class="action-btn action-btn-edit"
                                                title="Edit Company">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('companies.destroy', $company->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn action-btn-delete" title="Delete Company">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-building-x"></i>
                        </div>
                        <h3 class="empty-title">No companies found</h3>
                        <p class="empty-description">
                            @if(request('search') || request('status'))
                                No companies match your search criteria. Try adjusting your filters.
                            @else
                                Get started by adding your first company.
                            @endif
                        </p>
                        @if(request('search') || request('status'))
                            <a href="{{ route('companies.index') }}" class="btn-primary-custom">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset Filters
                            </a>
                        @else
                            <a href="{{ route('companies.create') }}" class="btn-primary-custom">
                                <i class="bi bi-plus-circle"></i>
                                Add First Company
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($companies->hasPages())
                <div class="pagination-container">
                    {{ $companies->links('pagination::bootstrap-5') }}
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

        .companies-management-container {
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

        .stat-card-success {
            border-color: rgba(16, 185, 129, 0.1);
        }

        .stat-card-success:hover {
            border-color: var(--success);
        }

        .stat-card-warning {
            border-color: rgba(245, 158, 11, 0.1);
        }

        .stat-card-warning:hover {
            border-color: var(--warning);
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

        .stat-card-warning .stat-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            color: var(--warning);
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

        /* Companies Table */
        .companies-table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px var(--shadow);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
        }

        .companies-table {
            width: 100%;
            border-collapse: collapse;
        }

        .companies-table thead {
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
        }

        .companies-table th {
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
            width: 140px;
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

        .companies-table td {
            padding: 1.25rem 1.5rem;
            color: var(--dark);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .td-id {
            font-weight: 600;
        }

        .company-id-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .company-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .company-avatar {
            width: 250px;
            height: auto;
            border-radius: 10px;
            /* background: linear-gradient(135deg, var(--primary), var(--primary-dark)); */
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
            overflow: hidden;
            position: relative;
        }

        .company-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .company-name {
            font-weight: 600;
            color: var(--dark);
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
        }

        .contact-icon {
            color: var(--primary);
            font-size: 0.875rem;
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

        .status-secondary {
            background: rgba(100, 116, 139, 0.1);
            color: var(--gray);
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

        /* Text Utilities */
        .text-muted {
            color: var(--gray) !important;
        }

        .d-inline {
            display: inline !important;
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

        /* Responsive */
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .companies-management-container {
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

            .companies-table {
                min-width: 1000px;
            }

            .action-buttons {
                flex-direction: row;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
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

        .stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stat-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .table-row {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Print Styles */
        @media print {

            .page-header,
            .filter-section,
            .action-buttons,
            .pagination-container {
                display: none;
            }

            .companies-table-card {
                box-shadow: none;
                border: 1px solid var(--border);
            }

            .table-row:hover {
                background: white;
            }

            .companies-table {
                font-size: 0.85rem;
            }
        }

        /* Custom Scrollbar */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray);
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

            // Handle delete confirmations
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const confirmed = await showConfirm('Delete this company?', 'This action cannot be undone', 'Yes, Delete', 'Cancel');
                    if (confirmed) this.submit();
                });
            });

            // Add success message handling
            @if(session('success'))
                if (typeof showAlert !== 'undefined') {
                    showAlert('{{ session('success') }}', 'success', 'Success');
                }
            @endif
            });
    </script>

    <!-- Include Company Details Modal -->
    @include('companies.show-modal')

@endsection