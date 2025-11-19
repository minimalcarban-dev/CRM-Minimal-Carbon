@extends('layouts.admin')

@section('title', 'Closure Types Management')

@php
    // Calculate stats
    $totalItems = $items->total();
    $activeCount = 0;
    $inactiveCount = 0;
    
    foreach ($items as $item) {
        if ($item->is_active) {
            $activeCount++;
        } else {
            $inactiveCount++;
        }
    }
@endphp

@section('content')
<div class="closure-types-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="breadcrumb-nav">
                    <a href="{{ url('/') }}" class="breadcrumb-link">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Closure Types</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-layers-fill"></i>
                    Closure Types
                </h1>
                <p class="page-subtitle">Manage closure type configurations and settings</p>
            </div>
            <div class="header-right">
                @if ($currentAdmin && $currentAdmin->hasPermission('closure_types.create'))
                    <a href="{{ route('closure_types.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Closure Type</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-layers"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Types</div>
                <div class="stat-value">{{ $totalItems }}</div>
                <div class="stat-trend">
                    <i class="bi bi-collection"></i> All items
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
                    <i class="bi bi-toggle-on"></i> Enabled
                </div>
            </div>
        </div>

        <div class="stat-card stat-card-secondary">
            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Inactive</div>
                <div class="stat-value">{{ $inactiveCount }}</div>
                <div class="stat-trend">
                    <i class="bi bi-toggle-off"></i> Disabled
                </div>
            </div>
        </div>

        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-percent"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Rate</div>
                <div class="stat-value">{{ $totalItems > 0 ? round(($activeCount / $totalItems) * 100) : 0 }}%</div>
                <div class="stat-trend">
                    <i class="bi bi-graph-up"></i> Usage
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('closure_types.index') }}" class="filter-form">
            <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" name="search" class="search-input" 
                       placeholder="Search by name..." 
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
            <a href="{{ route('closure_types.index') }}" class="btn-reset">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Reset</span>
            </a>
            @endif
        </form>

        <div class="filter-info">
            <span class="result-count">Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} closure types</span>
        </div>
    </div>

    <!-- Closure Types Table -->
    <div class="closure-types-table-card">
        <div class="table-container">
            @if($items->count() > 0)
            <table class="closure-types-table">
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
                                <i class="bi bi-tag"></i>
                                <span>Name</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-toggle2-on"></i>
                                <span>Status</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-calendar-plus"></i>
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
                    @foreach ($items as $item)
                    <tr class="table-row">
                        <td class="td-id">
                            <span class="item-id-badge">#{{ $item->id }}</span>
                        </td>
                        <td>
                            <div class="item-name-container">
                                <div class="item-icon">
                                    <i class="bi bi-layers"></i>
                                </div>
                                <span class="item-name">{{ $item->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if($item->is_active)
                                <span class="status-badge status-active">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Active
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    <i class="bi bi-x-circle-fill"></i>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($item->created_at)
                                <div class="date-info">
                                    <span class="date-main">{{ $item->created_at->format('M d, Y') }}</span>
                                    <span class="date-time">{{ $item->created_at->format('h:i A') }}</span>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="td-actions">
                            <div class="action-buttons">
                                @if ($currentAdmin && $currentAdmin->hasPermission('closure_types.view'))
                                    <a href="{{ route('closure_types.show', $item->id) }}" 
                                       class="action-btn action-btn-view" 
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                                @if ($currentAdmin && $currentAdmin->hasPermission('closure_types.edit'))
                                    <a href="{{ route('closure_types.edit', $item->id) }}" 
                                       class="action-btn action-btn-edit" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if ($currentAdmin && $currentAdmin->hasPermission('closure_types.delete'))
                                    <form action="{{ route('closure_types.destroy', $item->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this closure type? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="action-btn action-btn-delete" 
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 class="empty-title">No closure types found</h3>
                <p class="empty-description">
                    @if(request('search') || request('status'))
                        No closure types match your search criteria. Try adjusting your filters.
                    @else
                        Get started by creating your first closure type.
                    @endif
                </p>
                @if(request('search') || request('status'))
                    <a href="{{ route('closure_types.index') }}" class="btn-primary-custom">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset Filters
                    </a>
                @elseif($currentAdmin && $currentAdmin->hasPermission('closure_types.create'))
                    <a href="{{ route('closure_types.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        Create First Closure Type
                    </a>
                @endif
            </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
        <div class="pagination-container">
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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