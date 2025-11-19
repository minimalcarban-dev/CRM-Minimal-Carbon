@extends('layouts.admin')

@section('title', 'Metal Types')

@section('content')
    <div class="metal-types-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Metal Types</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-award"></i>
                        Metal Types Management
                    </h1>
                    <p class="page-subtitle">Manage all metal types in your inventory</p>
                </div>
                <div class="header-right">
                    @if ($currentAdmin && $currentAdmin->hasPermission('metal_types.create'))
                        <a href="{{ route('metal_types.create') }}" class="btn-primary-custom">
                            <i class="bi bi-plus-circle"></i>
                            <span>Create Metal Type</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-award"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Metal Types</div>
                    <div class="stat-value">{{ $items->total() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-collection"></i> All Types
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Active</div>
                    <div class="stat-value">{{ $items->where('is_active', true)->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-arrow-up"></i> Available
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-pause-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Inactive</div>
                    <div class="stat-value">{{ $items->where('is_active', false)->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-dash-circle"></i> Disabled
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('metal_types.index') }}" id="filterForm">
                <div class="filter-controls">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="search" class="search-input" 
                               placeholder="Search by name..."
                               value="{{ request('search') }}">
                    </div>

                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-funnel"></i>
                        Search
                    </button>

                    @if(request()->has('search'))
                        <a href="{{ route('metal_types.index') }}" class="btn-reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Reset
                        </a>
                    @endif
                </div>

                <div class="filter-info">
                    <span class="result-count">
                        Showing {{ $items->count() }} of {{ $items->total() }} metal types
                    </span>
                </div>
            </form>
        </div>

        <!-- Metal Types Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>
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
                                    <i class="bi bi-toggle-on"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-calendar"></i>
                                    <span>Created</span>
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
                        @forelse ($items as $item)
                            <tr class="metal-type-row">
                                <td>
                                    <div class="cell-content">
                                        <span class="badge-custom badge-secondary">{{ $item->id }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <div class="metal-info">
                                            <div class="metal-avatar">
                                                {{ strtoupper(substr($item->name, 0, 2)) }}
                                            </div>
                                            <span class="text-semibold">{{ $item->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="badge-status {{ $item->is_active ? 'active' : 'inactive' }}">
                                            <i class="bi bi-{{ $item->is_active ? 'check-circle-fill' : 'pause-circle-fill' }}"></i>
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <div class="date-info">
                                            <span class="date-main">{{ $item->created_at?->format('d M Y') ?? '—' }}</span>
                                            @if($item->created_at)
                                                <span class="date-relative">{{ $item->created_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content justify-end">
                                        <div class="action-buttons">
                                            @if ($currentAdmin && $currentAdmin->hasPermission('metal_types.view'))
                                                <a href="{{ route('metal_types.show', $item->id) }}" 
                                                   class="action-btn action-btn-view" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif
                                            @if ($currentAdmin && $currentAdmin->hasPermission('metal_types.edit'))
                                                <a href="{{ route('metal_types.edit', $item->id) }}" 
                                                   class="action-btn action-btn-edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                            @if ($currentAdmin && $currentAdmin->hasPermission('metal_types.delete'))
                                                <form action="{{ route('metal_types.destroy', $item->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this metal type?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn action-btn-delete" title="Delete">
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
                                <td colspan="5">
                                    <div class="empty-state-inline">
                                        <div class="empty-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h3 class="empty-title">No metal types found</h3>
                                        <p class="empty-description">
                                            @if(request()->has('search'))
                                                Try adjusting your search criteria
                                            @else
                                                Start by creating your first metal type
                                            @endif
                                        </p>
                                        @if(!request()->has('search') && $currentAdmin && $currentAdmin->hasPermission('metal_types.create'))
                                            <a href="{{ route('metal_types.create') }}" class="btn-primary-custom">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Create First Metal Type</span>
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
        @if($items->hasPages())
            <div class="pagination-container">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    @endsection
