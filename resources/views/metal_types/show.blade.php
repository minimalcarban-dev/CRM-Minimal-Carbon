@extends('layouts.admin')

@section('title', 'Metal Type Details')

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
                        <a href="{{ route('metal_types.index') }}" class="breadcrumb-link">
                            Metal Types
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">{{ $item->name }}</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-eye"></i>
                        Metal Type Details
                    </h1>
                    <p class="page-subtitle">View complete information about this metal type</p>
                </div>
                <div class="header-right">
                    @if ($currentAdmin && $currentAdmin->hasPermission('metal_types.edit'))
                        <a href="{{ route('metal_types.edit', $item->id) }}" class="btn-primary-custom">
                            <i class="bi bi-pencil"></i>
                            <span>Edit</span>
                        </a>
                    @endif
                    <a href="{{ route('metal_types.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Metal Type Details Card -->
        <div class="details-card">
            <div class="details-header">
                <div class="metal-avatar-large">
                    {{ strtoupper(substr($item->name, 0, 2)) }}
                </div>
                <div class="metal-title-section">
                    <h2 class="metal-name">{{ $item->name }}</h2>
                    <span class="badge-status-large {{ $item->is_active ? 'active' : 'inactive' }}">
                        <i class="bi bi-{{ $item->is_active ? 'check-circle-fill' : 'pause-circle-fill' }}"></i>
                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <div class="details-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-hash"></i>
                        </div>
                        <div class="info-content">
                            <label class="info-label">Metal Type ID</label>
                            <div class="info-value">{{ $item->id }}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-tag"></i>
                        </div>
                        <div class="info-content">
                            <label class="info-label">Name</label>
                            <div class="info-value">{{ $item->name }}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-toggle-on"></i>
                        </div>
                        <div class="info-content">
                            <label class="info-label">Status</label>
                            <div class="info-value">
                                <span class="badge-status {{ $item->is_active ? 'active' : 'inactive' }}">
                                    <i class="bi bi-{{ $item->is_active ? 'check-circle-fill' : 'pause-circle-fill' }}"></i>
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                        <div class="info-content">
                            <label class="info-label">Created At</label>
                            <div class="info-value">
                                <div class="date-display">
                                    <span class="date-main">{{ $item->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                                    @if($item->created_at)
                                        <span class="date-relative">{{ $item->created_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($item->updated_at && $item->updated_at != $item->created_at)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">Last Updated</label>
                                <div class="info-value">
                                    <div class="date-display">
                                        <span class="date-main">{{ $item->updated_at->format('d M Y, h:i A') }}</span>
                                        <span class="date-relative">{{ $item->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="action-footer">
            <a href="{{ route('metal_types.index') }}" class="btn-secondary-custom">
                <i class="bi bi-arrow-left"></i>
                <span>Back to List</span>
            </a>
            @if ($currentAdmin && $currentAdmin->hasPermission('metal_types.edit'))
                <a href="{{ route('metal_types.edit', $item->id) }}" class="btn-primary-custom">
                    <i class="bi bi-pencil"></i>
                    <span>Edit Metal Type</span>
                </a>
            @endif
        </div>
    </div>

    @endsection
