@extends('layouts.admin')

@section('title', 'Factory Management')

@section('content')
    <div class="diamond-management-container tracker-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Factory Management</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-buildings"></i>
                        Factory Management
                    </h1>
                    <p class="page-subtitle">Manage your production factories</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('factories.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Factory</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-buildings"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Factories</div>
                    <div class="stat-value">{{ $totalFactories }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Active Factories</div>
                    <div class="stat-value">{{ $activeFactories }}</div>
                </div>
            </div>
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Gold in Factories</div>
                    <div class="stat-value" style="color: #f59e0b;">{{ number_format($totalInFactories, 3) }} gm</div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="tracker-table-card">
            <div class="table-responsive">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-code"></i> Code</th>
                            <th><i class="bi bi-building"></i> Name</th>
                            <th><i class="bi bi-person"></i> Contact Person</th>
                            <th><i class="bi bi-telephone"></i> Phone</th>
                            <th><i class="bi bi-box-seam"></i> Gold Stock</th>
                            <th><i class="bi bi-toggle-on"></i> Status</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factories as $factory)
                            <tr>
                                <td><span class="tracker-badge tracker-badge-primary">{{ $factory->code }}</span></td>
                                <td><strong>{{ $factory->name }}</strong></td>
                                <td>{{ $factory->contact_person ?? '—' }}</td>
                                <td>{{ $factory->contact_phone ?? '—' }}</td>
                                <td>
                                    <span
                                        style="font-weight: 600; color: {{ $factory->current_stock > 0 ? '#f59e0b' : '#94a3b8' }};">
                                        {{ number_format($factory->current_stock, 3) }} gm
                                    </span>
                                </td>
                                <td>
                                    @if($factory->is_active)
                                        <span class="tracker-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="tracker-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                            <i class="bi bi-x-circle"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tracker-actions">
                                        <a href="{{ route('factories.edit', $factory) }}"
                                            class="tracker-action-btn tracker-action-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($factory->current_stock <= 0)
                                            <form action="{{ route('factories.destroy', $factory) }}" method="POST"
                                                style="display:inline" onsubmit="return confirm('Delete this factory?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="tracker-action-btn tracker-action-delete"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="tracker-action-btn"
                                                style="background: #f1f5f9; color: #94a3b8; cursor: not-allowed;"
                                                title="Cannot delete: has gold allocated">
                                                <i class="bi bi-trash"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="tracker-empty">
                                        <div class="tracker-empty-icon"><i class="bi bi-inbox"></i></div>
                                        <h3 class="tracker-empty-title">No factories found</h3>
                                        <p class="tracker-empty-desc">Start by adding your first factory</p>
                                        <a href="{{ route('factories.create') }}" class="btn-primary-custom">
                                            <i class="bi bi-plus-circle"></i> Add Factory
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($factories->hasPages())
            <div class="pagination-container">
                {{ $factories->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection