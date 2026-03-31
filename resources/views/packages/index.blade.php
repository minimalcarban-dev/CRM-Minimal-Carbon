@extends('layouts.admin')

@section('title', 'Package Handover System')

@section('content')
    <div class="tracker-page">
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
                        <i class="bi bi-box-seam-fill" style="color: #6366f1;"></i>
                        Package Handover System
                    </h1>
                    <p class="page-subtitle">Manage package issuance and returns</p>
                </div>
                <div class="header-right">
                    @if (auth()->guard('admin')->check() && auth()->guard('admin')->user()->canAccessAny(['packages.create']))
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
            <a href="{{ route('packages.index') }}" class="stat-card" style="{{ !request('status') ? 'border-color: #6366f1; background: rgba(99, 102, 241, 0.05);' : 'border-left-color: #6366f1;' }} text-decoration: none;">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Packages</div>
                    <div class="stat-value" style="color: #6366f1;">{{ $stats['total'] }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-graph-up"></i> All records
                    </div>
                </div>
            </a>

            {{-- Issued --}}
            <a href="{{ route('packages.index', ['status' => 'Issued']) }}" class="stat-card stat-card-warning" style="{{ request('status') == 'Issued' ? 'border-color: #f59e0b; background: rgba(245, 158, 11, 0.05);' : '' }} text-decoration: none;">
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
            <a href="{{ route('packages.index', ['status' => 'Returned']) }}" class="stat-card stat-card-success" style="{{ request('status') == 'Returned' ? 'border-color: #10b981; background: rgba(16, 185, 129, 0.05);' : '' }} text-decoration: none;">
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
            <a href="{{ route('packages.index', ['status' => 'Overdue']) }}" class="stat-card stat-card-danger" style="{{ request('status') == 'Overdue' ? 'border-color: #ef4444; background: rgba(239, 68, 68, 0.05);' : '' }} text-decoration: none;">
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
        <div class="tracker-filter">
            <form method="GET" action="{{ route('packages.index') }}" class="tracker-filter-form" id="packagesFilterForm">
                <div class="tracker-filter-field" style="flex: 2;">
                    <label class="tracker-filter-label"><i class="bi bi-search"></i> Search</label>
                    <input type="text" name="search" class="tracker-filter-input" placeholder="Search by Slip ID, Name, or Mobile..." value="{{ request('search') }}">
                </div>

                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-circle"></i> Status</label>
                    <select name="status" class="tracker-filter-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Issued" {{ request('status') == 'Issued' ? 'selected' : '' }}>Issued</option>
                        <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                        <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>

                <div class="tracker-filter-actions" style="margin-top: auto; border-top: none; padding-top: 0;">
                    @if (request('search') || request('status'))
                        <a href="{{ route('packages.index') }}" class="btn-tracker-reset">
                            <i class="bi bi-arrow-counterclockwise"></i> Clear
                        </a>
                    @endif
                    <button type="submit" class="btn-tracker-apply">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Packages Table -->
        <div class="tracker-table-card">
            <div class="table-responsive">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-hash"></i> Slip ID</th>
                            <th><i class="bi bi-person"></i> Person Details</th>
                            <th><i class="bi bi-box-seam"></i> Package Description</th>
                            <th><i class="bi bi-calendar-event"></i> Dates</th>
                            <th><i class="bi bi-info-circle"></i> Status</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr>
                                <td>
                                    <a href="{{ route('packages.show', $package->id) }}" class="tracker-badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; text-decoration: none;">
                                        {{ $package->slip_id }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--dark, #1e293b); margin-bottom: 0.25rem;">{{ $package->person_name }}</div>
                                    <div style="font-size: 0.85rem; color: #64748b;">
                                        <i class="bi bi-telephone"></i> {{ $package->mobile_number }}
                                    </div>
                                </td>
                                <td>
                                    <div style="color: #475569; max-width: 300px; white-space: pre-wrap; font-size: 0.9rem;">{{ Str::limit($package->package_description, 50) }}</div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                                        <div>
                                            <span style="color: #64748b; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Issued</span><br>
                                            <span style="color: #1e293b; font-weight: 500;">{{ $package->issue_date->format('d M Y') }}</span>
                                            <span style="color: #64748b; margin-left: 0.25rem;">{{ \Carbon\Carbon::parse($package->issue_time)->format('h:i A') }}</span>
                                        </div>
                                        <div>
                                            <span style="color: #64748b; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">Return</span><br>
                                            <span style="{{ $package->return_date < now() && $package->status == 'Issued' ? 'color: #ef4444; font-weight: 600;' : 'color: #1e293b; font-weight: 500;' }}">
                                                {{ $package->return_date->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {!! str_replace('badge', 'tracker-badge', $package->status_badge) !!}
                                </td>
                                <td>
                                    <div class="tracker-actions">
                                        <a href="{{ route('packages.show', $package->id) }}" class="tracker-action-btn tracker-action-view" title="View Slip">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if (($package->status == 'Issued' || $package->status == 'Overdue') && auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['packages.return']))
                                            <form action="{{ route('packages.return', $package->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Mark this package as returned?');">
                                                @csrf
                                                <button type="submit" class="tracker-action-btn tracker-action-edit" style="color: #10b981; border-color: rgba(16, 185, 129, 0.3);" title="Mark Returned">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['packages.delete']))
                                            <form action="{{ route('packages.destroy', $package->id) }}" method="POST" style="display:inline" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="tracker-action-btn tracker-action-delete delete-btn" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="tracker-empty">
                                        <div class="tracker-empty-icon"><i class="bi bi-box-seam"></i></div>
                                        <h3 class="tracker-empty-title">No packages found</h3>
                                        <p class="tracker-empty-desc">Try adjusting your search or filters.</p>
                                        <a href="{{ route('packages.create') }}" class="btn-primary-custom">
                                            <i class="bi bi-plus-circle"></i> Issue New Package
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($packages->hasPages())
                <div class="pagination-container" style="padding: 1.5rem; border-top: 1px solid var(--border);">
                    {{ $packages->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Delete confirmation
                document.querySelectorAll('.delete-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (confirm('Are you sure you want to delete this package record?')) {
                            this.closest('.delete-form').submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
