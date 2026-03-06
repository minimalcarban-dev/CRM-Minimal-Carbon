@extends('layouts.admin')

@section('title', 'Package Handover System')

@section('content')
    <div class="packages-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Packages</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-box-seam-fill"></i>
                        Package Handover System
                    </h1>
                    <p class="page-subtitle">Manage package issuance and returns</p>
                </div>
                <div class="header-right">
                    @if (auth()->guard('admin')->check() &&
                            auth()->guard('admin')->user()->canAccessAny(['packages.create']))
                        <a href="{{ route('packages.create') }}" class="btn-primary-custom">
                            <i class="bi bi-plus-circle"></i>
                            <span>Issue New Package</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            {{-- Total Packages --}}
            <a href="{{ route('packages.index') }}"
                class="stat-card stat-card-primary {{ !request('status') ? 'active-filter' : '' }}">
                <div class="stat-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Packages</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-graph-up"></i> All records
                    </div>
                </div>
            </a>

            {{-- Issued --}}
            <a href="{{ route('packages.index', ['status' => 'Issued']) }}"
                class="stat-card stat-card-warning {{ request('status') == 'Issued' ? 'active-filter' : '' }}">
                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Issued (Pending)</div>
                    <div class="stat-value">{{ $stats['issued'] }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-clock"></i> Awaiting return
                    </div>
                </div>
            </a>

            {{-- Returned --}}
            <a href="{{ route('packages.index', ['status' => 'Returned']) }}"
                class="stat-card stat-card-success {{ request('status') == 'Returned' ? 'active-filter' : '' }}">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Returned</div>
                    <div class="stat-value">{{ $stats['returned'] }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-archive"></i> Completed
                    </div>
                </div>
            </a>

            {{-- Overdue --}}
            <a href="{{ route('packages.index', ['status' => 'Overdue']) }}"
                class="stat-card stat-card-danger {{ request('status') == 'Overdue' ? 'active-filter' : '' }}">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Overdue</div>
                    <div class="stat-value">{{ $stats['overdue'] }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-bell"></i> Action needed
                    </div>
                </div>
            </a>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('packages.index') }}" class="filter-form">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="search-input"
                        placeholder="Search by Slip ID, Name, or Mobile..." value="{{ request('search') }}">
                </div>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Issued" {{ request('status') == 'Issued' ? 'selected' : '' }}>Issued</option>
                    <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                    <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                </select>

                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i>
                    <span>Filter</span>
                </button>

                @if (request('search') || request('status'))
                    <a href="{{ route('packages.index') }}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Packages Table -->
        <div class="orders-table-card">
            <div class="table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th class="th-id">
                                <div class="th-content">
                                    <i class="bi bi-hash"></i>
                                    <span>Slip ID</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-person"></i>
                                    <span>Person Details</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-box-seam"></i>
                                    <span>Package Description</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>Dates</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-info-circle"></i>
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
                        @forelse($packages as $package)
                            <tr>
                                <td class="td-id" data-label="Slip ID">
                                    <a href="{{ route('packages.show', $package->id) }}" class="order-id-badge">
                                        {{ $package->slip_id }}
                                    </a>
                                </td>
                                <td data-label="Person Details">
                                    <div class="client-info">
                                        <div class="client-name fw-bold text-dark mb-1">{{ $package->person_name }}</div>
                                        <div class="client-meta text-muted small">
                                            <i class="bi bi-telephone me-1"></i> {{ $package->mobile_number }}
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Package Description">
                                    <div class="text-muted" style="max-width: 300px; white-space: pre-wrap;">
                                        {{ Str::limit($package->package_description, 50) }}</div>
                                </td>
                                <td data-label="Dates">
                                    <div class="d-flex flex-column gap-1">
                                        <div class="date-info">
                                            <small class="text-muted text-uppercase"
                                                style="font-size: 0.65rem; font-weight: 700;">Issued</small>
                                            <span class="d-block">{{ $package->issue_date->format('d M Y') }}</span>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($package->issue_time)->format('h:i A') }}</small>
                                        </div>
                                        <div class="date-info mt-2">
                                            <small class="text-muted text-uppercase"
                                                style="font-size: 0.65rem; font-weight: 700;">Return</small>
                                            <span
                                                class="d-block {{ $package->return_date < now() && $package->status == 'Issued' ? 'text-danger fw-bold' : '' }}">
                                                {{ $package->return_date->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    {!! $package->status_badge !!}
                                </td>
                                <td class="td-actions" data-label="Actions">
                                    <div class="action-buttons">
                                        <a href="{{ route('packages.show', $package->id) }}"
                                            class="action-btn action-btn-view" title="View Slip">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if (
                                            ($package->status == 'Issued' || $package->status == 'Overdue') &&
                                                auth()->guard('admin')->user() &&
                                                auth()->guard('admin')->user()->canAccessAny(['packages.return']))
                                            <form action="{{ route('packages.return', $package->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                <button type="submit" class="action-btn action-btn-edit text-success"
                                                    style="border-color: var(--success); color: var(--success);"
                                                    title="Mark Returned">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if (auth()->guard('admin')->user() &&
                                                auth()->guard('admin')->user()->canAccessAny(['packages.delete']))
                                            <form action="{{ route('packages.destroy', $package->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn action-btn-delete"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-icon"
                                            style="background: var(--light-gray); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; color: var(--gray);">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <h5 class="text-muted">No packages found</h5>
                                        <p class="text-muted small mb-3">Try adjusting your search or filters.</p>
                                        <a href="{{ route('packages.create') }}" class="btn-primary-custom">Issue New
                                            Package</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($packages->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            [data-theme="dark"] .packages-management-container {
                background: var(--bg-body, #0f172a);
            }

            [data-theme="dark"] .page-header,
            [data-theme="dark"] .stat-card,
            [data-theme="dark"] .filter-section,
            [data-theme="dark"] .orders-table-card,
            [data-theme="dark"] .card-footer {
                background: var(--bg-card, #1e293b) !important;
                border-color: rgba(148, 163, 184, 0.34) !important;
                box-shadow: 0 6px 18px rgba(2, 6, 23, 0.18);
            }

            [data-theme="dark"] .page-header {
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(139, 92, 246, 0.07));
            }

            [data-theme="dark"] .page-title,
            [data-theme="dark"] .stat-value,
            [data-theme="dark"] .client-name,
            [data-theme="dark"] .orders-table td {
                color: var(--text-primary, #f1f5f9) !important;
            }

            [data-theme="dark"] .page-subtitle,
            [data-theme="dark"] .breadcrumb-nav,
            [data-theme="dark"] .breadcrumb-link,
            [data-theme="dark"] .stat-label,
            [data-theme="dark"] .client-meta,
            [data-theme="dark"] .orders-table th,
            [data-theme="dark"] .text-muted {
                color: var(--text-secondary, #94a3b8) !important;
            }

            [data-theme="dark"] .search-input,
            [data-theme="dark"] .filter-select {
                background: rgba(15, 23, 42, 0.62);
                border-color: rgba(148, 163, 184, 0.32);
                color: var(--text-primary, #f1f5f9);
            }

            [data-theme="dark"] .search-input::placeholder {
                color: var(--text-secondary, #94a3b8);
            }

            [data-theme="dark"] .btn-filter {
                background: rgba(15, 23, 42, 0.7);
                border-color: rgba(148, 163, 184, 0.4);
                color: var(--text-primary, #f1f5f9);
            }

            [data-theme="dark"] .btn-reset,
            [data-theme="dark"] .btn-secondary-custom {
                background: rgba(255, 255, 255, 0.04);
                border-color: rgba(148, 163, 184, 0.35);
                color: var(--text-secondary, #94a3b8);
            }

            [data-theme="dark"] .orders-table th {
                background: rgba(15, 23, 42, 0.5);
                border-bottom-color: rgba(148, 163, 184, 0.34);
            }

            [data-theme="dark"] .orders-table td {
                border-bottom-color: rgba(148, 163, 184, 0.22);
            }

            [data-theme="dark"] .order-id-badge {
                background: rgba(255, 255, 255, 0.06);
                border-color: rgba(148, 163, 184, 0.3);
                color: var(--text-primary, #f1f5f9);
            }

            [data-theme="dark"] .action-btn {
                background: rgba(255, 255, 255, 0.03);
                border-color: rgba(148, 163, 184, 0.3);
                color: var(--text-secondary, #94a3b8);
            }

            [data-theme="dark"] .stat-card-primary .stat-icon {
                background: rgba(99, 102, 241, 0.22);
                border-color: rgba(129, 140, 248, 0.38);
                color: #a5b4fc;
            }

            [data-theme="dark"] .stat-card-warning .stat-icon {
                background: rgba(245, 158, 11, 0.22);
                border-color: rgba(251, 191, 36, 0.38);
                color: #fbbf24;
            }

            [data-theme="dark"] .stat-card-success .stat-icon {
                background: rgba(16, 185, 129, 0.22);
                border-color: rgba(52, 211, 153, 0.38);
                color: #34d399;
            }

            [data-theme="dark"] .stat-card-danger .stat-icon {
                background: rgba(239, 68, 68, 0.22);
                border-color: rgba(248, 113, 113, 0.38);
                color: #f87171;
            }

            /* Container & Header */
            .packages-management-container {
                padding: 2rem;
                max-width: 1600px;
                margin: 0 auto;
                min-height: 100vh;
                background: #f8fafc;
            }

            .page-header {
                background: white;
                padding: 2rem;
                border-radius: 16px;
                margin-bottom: 2rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
                font-size: 0.85rem;
                color: var(--gray);
                margin-bottom: 0.5rem;
            }

            .breadcrumb-link {
                color: var(--gray);
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 0.25rem;
                transition: color 0.2s;
            }

            .breadcrumb-link:hover {
                color: var(--primary);
            }

            .breadcrumb-current {
                color: var(--primary);
                font-weight: 600;
            }

            .page-title {
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .page-title i {
                color: var(--primary);
            }

            .page-subtitle {
                color: var(--gray);
                font-size: 0.95rem;
                margin: 0.25rem 0 0 0;
            }

            .btn-primary-custom {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                padding: 0.65rem 1.25rem;
                border-radius: 12px;
                border: none;
                font-weight: 600;
                font-size: 0.95rem;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
                text-decoration: none;
                transition: all 0.3s;
            }

            .btn-primary-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
                color: white;
            }

            /* Stats Grid */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 1.25rem;
                margin-bottom: 1.75rem;
            }

            .stat-card {
                background: white;
                border-radius: 15px;
                padding: 1.35rem 1.4rem;
                display: flex;
                gap: 1.25rem;
                text-decoration: none;
                border: 1.5px solid var(--border);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 4px 16px rgba(99, 102, 241, 0.05);
            }

            .stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
                border-color: currentColor;
            }

            .stat-card.active-filter {
                background: linear-gradient(to right, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.5));
                border-color: currentColor;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .stat-card.active-filter::after {
                content: '';
                position: absolute;
                inset: 0;
                border: 2px solid currentColor;
                border-radius: 14px;
                pointer-events: none;
            }

            .stat-card-primary {
                color: var(--primary);
            }

            .stat-card-success {
                color: var(--success);
            }

            .stat-card-warning {
                color: var(--warning);
            }

            .stat-card-danger {
                color: var(--danger);
            }

            .stat-icon {
                width: 56px;
                height: 56px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.75rem;
                flex-shrink: 0;
                background: rgba(99, 102, 241, 0.14);
                border: 1px solid rgba(99, 102, 241, 0.28);
                color: var(--primary);
            }

            .stat-card-primary .stat-icon {
                background: rgba(99, 102, 241, 0.14);
                border-color: rgba(99, 102, 241, 0.28);
                color: var(--primary);
            }

            .stat-card-warning .stat-icon {
                background: rgba(245, 158, 11, 0.14);
                border-color: rgba(245, 158, 11, 0.3);
                color: var(--warning);
            }

            .stat-card-success .stat-icon {
                background: rgba(16, 185, 129, 0.14);
                border-color: rgba(16, 185, 129, 0.3);
                color: var(--success);
            }

            .stat-card-danger .stat-icon {
                background: rgba(239, 68, 68, 0.14);
                border-color: rgba(239, 68, 68, 0.3);
                color: var(--danger);
            }

            .stat-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .stat-label {
                color: var(--gray);
                font-size: 0.875rem;
                font-weight: 600;
                margin-bottom: 0.25rem;
            }

            .stat-value {
                color: var(--dark);
                font-size: 1.5rem;
                font-weight: 700;
                line-height: 1.2;
            }

            .stat-trend {
                font-size: 0.75rem;
                margin-top: 0.25rem;
                display: flex;
                align-items: center;
                gap: 0.35rem;
                color: currentColor;
                opacity: 0.8;
                font-weight: 500;
            }

            /* Filter Section */
            .filter-section {
                background: white;
                border-radius: 20px;
                padding: 1rem;
                border: 1.5px solid var(--border);
                margin-bottom: 1.75rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 4px 16px rgba(99, 102, 241, 0.05);
            }

            .filter-form {
                display: flex;
                gap: 1rem;
                align-items: center;
                flex-wrap: wrap;
            }

            .search-box {
                position: relative;
                flex: 1;
                min-width: 250px;
            }

            .search-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--gray);
            }

            .search-input {
                width: 100%;
                padding: 0.65rem 1rem 0.65rem 2.5rem;
                border: 2px solid var(--border);
                border-radius: 10px;
                font-size: 0.95rem;
                outline: none;
                transition: border-color 0.2s;
            }

            .search-input:focus {
                border-color: var(--primary);
            }

            .filter-select {
                padding: 0.65rem 2.5rem 0.65rem 1rem;
                border: 2px solid var(--border);
                border-radius: 10px;
                font-size: 0.95rem;
                outline: none;
                cursor: pointer;
                background-color: white;
                min-width: 180px;
            }

            .filter-select:focus {
                border-color: var(--primary);
            }

            .btn-filter,
            .btn-reset {
                padding: 0.65rem 1.25rem;
                border-radius: 10px;
                font-weight: 600;
                font-size: 0.95rem;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                border: 2px solid;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
            }

            .btn-filter {
                background: var(--dark);
                color: white;
                border-color: var(--dark);
            }

            .btn-filter:hover {
                background: #334155;
            }

            .btn-reset {
                background: white;
                color: var(--gray);
                border-color: var(--border);
            }

            .btn-reset:hover {
                border-color: var(--danger);
                color: var(--danger);
            }

            /* Table */
            .orders-table-card {
                background: white;
                border-radius: 20px;
                border: 1.5px solid var(--border);
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 4px 16px rgba(99, 102, 241, 0.05);
            }

            .orders-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
            }

            .orders-table th {
                padding: 1rem;
                background: #f8fafc;
                border-bottom: 2px solid var(--border);
                text-align: left;
                font-weight: 600;
                color: var(--gray);
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .orders-table td {
                padding: 1rem;
                border-bottom: 1px solid var(--border);
                vertical-align: middle;
            }

            .th-content {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .order-id-badge {
                background: var(--light-gray);
                color: var(--dark);
                padding: 0.35rem 0.75rem;
                border-radius: 8px;
                font-weight: 700;
                font-size: 0.9rem;
                font-family: monospace;
                border: 1px solid var(--border);
                display: inline-block;
                text-decoration: none;
                transition: all 0.2s;
            }

            .order-id-badge:hover {
                border-color: var(--primary);
                color: var(--primary);
                background: rgba(99, 102, 241, 0.05);
            }

            .action-buttons {
                display: flex;
                gap: 0.5rem;
                justify-content: flex-end;
            }

            .action-btn {
                width: 36px;
                height: 36px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--border);
                background: white;
                color: var(--gray);
                transition: all 0.2s;
                cursor: pointer;
                text-decoration: none;
            }

            .action-btn:hover {
                border-color: var(--primary);
                color: var(--primary);
                background: rgba(99, 102, 241, 0.05);
            }

            .action-btn-delete:hover {
                border-color: var(--danger);
                color: var(--danger);
                background: rgba(239, 68, 68, 0.05);
            }

            /* ===== RESPONSIVE BREAKPOINTS ===== */

            /* Table horizontal scroll wrapper */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Tablet (≤992px) */
            @media (max-width: 992px) {
                .packages-management-container {
                    padding: 1.25rem;
                }

                .stats-grid {
                    grid-template-columns: repeat(2, 1fr);
                }

                .filter-form {
                    flex-wrap: wrap;
                }

                .search-box {
                    min-width: 0;
                    width: 100%;
                }

                .filter-select {
                    min-width: 0;
                    flex: 1;
                }
            }

            /* Mobile (≤640px) — card layout for table */
            @media (max-width: 640px) {
                .packages-management-container {
                    padding: 0.875rem;
                }

                .page-header {
                    padding: 1.25rem;
                }

                .header-content {
                    flex-direction: column;
                    gap: 1rem;
                    align-items: flex-start;
                }

                .header-right {
                    width: 100%;
                }

                .header-right .btn-primary-custom {
                    width: 100%;
                    justify-content: center;
                }

                .page-title {
                    font-size: clamp(1.1rem, 4vw, 1.5rem);
                }

                .stats-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 0.75rem;
                }

                .stat-card {
                    padding: 1rem;
                    gap: 0.75rem;
                }

                .stat-icon {
                    width: 44px;
                    height: 44px;
                    font-size: 1.35rem;
                    flex-shrink: 0;
                }

                .stat-value {
                    font-size: 1.25rem;
                }

                .filter-section {
                    padding: 0.85rem;
                }

                .filter-form {
                    flex-direction: column;
                    gap: 0.65rem;
                }

                .search-box,
                .filter-select,
                .btn-filter,
                .btn-reset {
                    width: 100%;
                }

                .filter-select {
                    min-width: 0;
                }

                .btn-filter,
                .btn-reset {
                    justify-content: center;
                }

                /* Mobile card table */
                .orders-table thead {
                    display: none;
                }

                .orders-table,
                .orders-table tbody,
                .orders-table tr,
                .orders-table td {
                    display: block;
                    width: 100%;
                }

                .orders-table tr {
                    border: 1.5px solid var(--border);
                    border-radius: 12px;
                    margin-bottom: 0.75rem;
                    padding: 0.25rem 0;
                    background: white;
                }

                .orders-table td {
                    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
                    padding: 0.6rem 1rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 0.5rem;
                    text-align: right;
                }

                .orders-table td:last-child {
                    border-bottom: none;
                }

                .orders-table td::before {
                    content: attr(data-label);
                    font-size: 0.75rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.4px;
                    color: var(--gray);
                    flex-shrink: 0;
                    text-align: left;
                }

                .orders-table td.td-id {
                    background: rgba(99, 102, 241, 0.04);
                    border-radius: 12px 12px 0 0;
                    font-weight: 700;
                }

                .orders-table td.td-actions {
                    border-radius: 0 0 12px 12px;
                }

                .action-buttons {
                    justify-content: flex-end;
                }

                .client-info {
                    text-align: right;
                }

                /* Dark mode card rows */
                [data-theme="dark"] .orders-table tr {
                    background: var(--bg-card, #1e293b);
                    border-color: rgba(148, 163, 184, 0.22);
                }

                [data-theme="dark"] .orders-table td {
                    border-bottom-color: rgba(148, 163, 184, 0.12);
                }

                [data-theme="dark"] .orders-table td.td-id {
                    background: rgba(99, 102, 241, 0.08);
                }
            }

            /* Extra small (≤380px) */
            @media (max-width: 380px) {
                .stats-grid {
                    grid-template-columns: 1fr 1fr;
                }

                .stat-label {
                    font-size: 0.75rem;
                }

                .stat-value {
                    font-size: 1.1rem;
                }

                .stat-trend {
                    display: none;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle delete confirmations with SweetAlert2
                document.querySelectorAll('.delete-form').forEach(form => {
                    form.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        // Check if it's a return action or delete action
                        const isReturn = this.action.includes('/return');
                        const title = isReturn ? 'Mark as Returned?' :
                            'Are you sure you want to delete this package?';
                        const text = isReturn ? 'This will update the package status.' :
                            'This action cannot be undone.';
                        const confirmBtnText = isReturn ? 'Yes, Return' : 'Yes, Delete';
                        const confirmBtnColor = isReturn ? '#10b981' : '#d33';

                        const confirmed = await showConfirm(
                            title,
                            text,
                            confirmBtnText,
                            'Cancel',
                            confirmBtnColor
                        );

                        if (confirmed) {
                            this.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
