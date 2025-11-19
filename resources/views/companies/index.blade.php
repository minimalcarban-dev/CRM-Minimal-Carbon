@extends('layouts.admin')

@section('title', 'Companies')

@section('content')
    <div class="companies-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Companies</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-buildings"></i>
                        Company Management
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
                    <div class="stat-value">{{ $companies->total() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-graph-up"></i> All Time
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Active</div>
                    <div class="stat-value">{{ $companies->where('status', 'active')->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-arrow-up"></i> Operating
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-pause-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Inactive</div>
                    <div class="stat-value">{{ $companies->where('status', 'inactive')->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-dash-circle"></i> Suspended
                    </div>
                </div>
            </div>
        </div>

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
            <form method="GET" action="{{ route('companies.index') }}" id="filterForm">
                <div class="filter-controls">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="search" class="search-input" 
                               placeholder="Search company name or email..."
                               value="{{ request('search') }}">
                    </div>

                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-funnel"></i>
                        Filter
                    </button>

                    @if(request()->has('search') || request()->has('status'))
                        <a href="{{ route('companies.index') }}" class="btn-reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Reset
                        </a>
                    @endif
                </div>

                <div class="filter-info">
                    <span class="result-count">
                        Showing {{ $companies->count() }} of {{ $companies->total() }} companies
                    </span>
                </div>
            </form>
        </div>

        <!-- Companies Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-hash"></i>
                                    <span>#</span>
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
                                    <i class="bi bi-calendar"></i>
                                    <span>Created At</span>
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
                        @forelse ($companies as $company)
                            <tr class="company-row">
                                <td>
                                    <div class="cell-content">
                                        <span class="row-number">{{ $loop->iteration + ($companies->currentPage() - 1) * $companies->perPage() }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <div class="company-info">
                                            <div class="company-avatar">
                                                {{ strtoupper(substr($company->name, 0, 2)) }}
                                            </div>
                                            <span class="text-semibold">{{ $company->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        @if($company->email)
                                            <a href="mailto:{{ $company->email }}" class="email-link">
                                                {{ $company->email }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        @if($company->phone)
                                            <a href="tel:{{ $company->phone }}" class="phone-link">
                                                {{ $company->phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="badge-status {{ $company->status }}">
                                            <i class="bi bi-{{ $company->status == 'active' ? 'check-circle-fill' : 'pause-circle-fill' }}"></i>
                                            {{ ucfirst($company->status) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <div class="date-info">
                                            <span class="date-main">{{ $company->created_at ? $company->created_at->format('d M Y') : '—' }}</span>
                                            @if($company->created_at)
                                                <span class="date-relative">{{ $company->created_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content justify-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('companies.edit', $company->id) }}" 
                                               class="action-btn action-btn-edit" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('companies.destroy', $company->id) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this company?');">
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
                                <td colspan="7">
                                    <div class="empty-state-inline">
                                        <div class="empty-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h3 class="empty-title">No companies found</h3>
                                        <p class="empty-description">
                                            @if(request()->has('search') || request()->has('status'))
                                                Try adjusting your filters or search criteria
                                            @else
                                                Start by adding your first company
                                            @endif
                                        </p>
                                        @if(!request()->has('search') && !request()->has('status'))
                                            <a href="{{ route('companies.create') }}" class="btn-primary-custom">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Add First Company</span>
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
        @if($companies->hasPages())
            <div class="pagination-container">
                {{ $companies->links() }}
            </div>
        @endif
    </div>

    @endsection
