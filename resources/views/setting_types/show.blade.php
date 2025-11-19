@extends('layouts.admin')

@section('title', 'Setting Type Details')

@section('content')
<div class="setting-type-show-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="breadcrumb-nav">
                    <a href="{{ url('/') }}" class="breadcrumb-link">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <a href="{{ route('setting_types.index') }}" class="breadcrumb-link">Setting Types</a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Details</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-eye-fill"></i>
                    Setting Type Details
                </h1>
                <p class="page-subtitle">View detailed information about this setting type</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-grid">
        <!-- Details Card -->
        <div class="details-card">
            <div class="card-header">
                <div class="header-icon">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div>
                    <h2 class="card-title">Basic Information</h2>
                    <p class="card-subtitle">Core details of the setting type</p>
                </div>
            </div>

            <div class="card-body">
                <!-- ID Field -->
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="bi bi-hash"></i>
                        <span>ID</span>
                    </div>
                    <div class="detail-value">
                        <span class="id-badge">#{{ $item->id }}</span>
                    </div>
                </div>

                <div class="detail-divider"></div>

                <!-- Name Field -->
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="bi bi-tag"></i>
                        <span>Name</span>
                    </div>
                    <div class="detail-value">
                        <span class="name-text">{{ $item->name }}</span>
                    </div>
                </div>

                <div class="detail-divider"></div>

                <!-- Status Field -->
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="bi bi-toggle2-on"></i>
                        <span>Status</span>
                    </div>
                    <div class="detail-value">
                        @if($item->is_active)
                            <span class="status-badge status-active">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Active</span>
                            </span>
                        @else
                            <span class="status-badge status-inactive">
                                <i class="bi bi-x-circle-fill"></i>
                                <span>Inactive</span>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="detail-divider"></div>

                <!-- Created Date Field -->
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="bi bi-calendar3"></i>
                        <span>Created Date</span>
                    </div>
                    <div class="detail-value">
                        <div class="date-display">
                            <i class="bi bi-calendar-check"></i>
                            <span>{{ $item->created_at?->format('M d, Y') ?? '—' }}</span>
                        </div>
                        @if($item->created_at)
                            <div class="date-relative">
                                {{ $item->created_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($item->updated_at && $item->updated_at != $item->created_at)
                    <div class="detail-divider"></div>

                    <!-- Updated Date Field -->
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="bi bi-clock-history"></i>
                            <span>Last Updated</span>
                        </div>
                        <div class="detail-value">
                            <div class="date-display">
                                <i class="bi bi-calendar-check"></i>
                                <span>{{ $item->updated_at->format('M d, Y') }}</span>
                            </div>
                            <div class="date-relative">
                                {{ $item->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Card -->
        <div class="info-card">
            <div class="info-header">
                <div class="info-icon">
                    <i class="bi bi-lightbulb"></i>
                </div>
                <h3 class="info-title">Quick Info</h3>
            </div>

            <div class="info-content">
                <div class="info-item">
                    <div class="info-item-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="info-item-text">
                        <strong>Current Status</strong>
                        <p>This setting type is currently <strong>{{ $item->is_active ? 'active' : 'inactive' }}</strong></p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-item-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div class="info-item-text">
                        <strong>Need to Update?</strong>
                        <p>You can edit this setting type to change its details or status</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-item-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="info-item-text">
                        <strong>Data Protection</strong>
                        <p>All changes are logged for security and audit purposes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-footer">
        <a href="{{ route('setting_types.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
            <span>Back to List</span>
        </a>
        @if ($currentAdmin && $currentAdmin->hasPermission('setting_types.edit'))
            <a href="{{ route('setting_types.edit', $item->id) }}" class="btn-edit">
                <i class="bi bi-pencil"></i>
                <span>Edit Setting Type</span>
            </a>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth scroll effect
        const detailRows = document.querySelectorAll('.detail-row');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateX(0)';
                    }, index * 100);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px'
        });

        detailRows.forEach((row) => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-20px)';
            row.style.transition = 'all 0.4s ease';
            observer.observe(row);
        });
    });
</script>

@endsection
