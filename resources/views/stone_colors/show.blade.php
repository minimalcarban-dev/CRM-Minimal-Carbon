@extends('layouts.admin')

@section('title', 'View Stone Color')

@section('content')
    <div class="attr-form-container">
        <div class="attr-page-header">
            <div class="attr-header-content">
                <div class="attr-header-left">
                    <div class="attr-breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="attr-breadcrumb-link"><i
                                class="bi bi-house-door"></i> Dashboard</a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <a href="{{ route('stone_colors.index') }}" class="attr-breadcrumb-link">Stone Colors</a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <span class="attr-breadcrumb-current">{{ $item->name }}</span>
                    </div>
                    <h1 class="attr-page-title"><i class="bi bi-eye"></i> Stone Color Details</h1>
                    <p class="attr-page-subtitle">View stone color information</p>
                </div>
                <div class="attr-header-right">
                    <a href="{{ route('stone_colors.index') }}" class="attr-btn-back"><i class="bi bi-arrow-left"></i> Back
                        to List</a>
                </div>
            </div>
        </div>

        <div class="attr-detail-card">
            <div class="attr-detail-header">
                <div class="attr-detail-info">
                    <div class="attr-detail-icon"><i class="bi bi-palette"></i></div>
                    <div class="attr-detail-title">
                        <h2>{{ $item->name }}</h2>
                        <p>Stone Color Information</p>
                    </div>
                </div>
                <div class="attr-status-badge {{ $item->is_active ? 'active' : 'inactive' }}">
                    <i class="bi bi-{{ $item->is_active ? 'check-circle' : 'x-circle' }}"></i>
                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                </div>
            </div>
            <div class="attr-detail-body">
                <div class="attr-detail-row">
                    <div class="attr-detail-label"><i class="bi bi-hash"></i> ID</div>
                    <div class="attr-detail-value">{{ $item->id }}</div>
                </div>
                <div class="attr-detail-row">
                    <div class="attr-detail-label"><i class="bi bi-tag"></i> Name</div>
                    <div class="attr-detail-value">{{ $item->name }}</div>
                </div>
                <div class="attr-detail-row">
                    <div class="attr-detail-label"><i class="bi bi-calendar"></i> Created</div>
                    <div class="attr-detail-value">{{ $item->created_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                </div>
                <div class="attr-detail-row">
                    <div class="attr-detail-label"><i class="bi bi-clock"></i> Updated</div>
                    <div class="attr-detail-value">{{ $item->updated_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                </div>
                <div class="attr-detail-actions">
                    <a href="{{ route('stone_colors.edit', $item->id) }}" class="attr-btn-edit"><i class="bi bi-pencil"></i>
                        Edit</a>
                    <form action="{{ route('stone_colors.destroy', $item->id) }}" method="POST" style="display:inline;"
                        onsubmit="return confirm('Are you sure?')">@csrf @method('DELETE')<button type="submit"
                            class="attr-btn-delete"><i class="bi bi-trash"></i> Delete</button></form>
                </div>
            </div>
        </div>
    </div>
    @include('partials.attribute-styles')
@endsection