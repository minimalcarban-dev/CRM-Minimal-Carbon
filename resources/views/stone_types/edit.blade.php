@extends('layouts.admin')

@section('title', 'Edit Stone Type')

@section('content')
    <div class="attr-form-container">
        <!-- Page Header -->
        <div class="attr-page-header">
            <div class="attr-header-content">
                <div class="attr-header-left">
                    <div class="attr-breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="attr-breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <a href="{{ route('stone_types.index') }}" class="attr-breadcrumb-link">Stone Types</a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <span class="attr-breadcrumb-current">Edit</span>
                    </div>
                    <h1 class="attr-page-title">
                        <i class="bi bi-pencil-square"></i>
                        Edit Stone Type
                    </h1>
                    <p class="attr-page-subtitle">Update stone type information</p>
                </div>
                <div class="attr-header-right">
                    <a href="{{ route('stone_types.index') }}" class="attr-btn-back">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="attr-form-card">
            <div class="attr-form-card-header">
                <div class="attr-form-card-icon">
                    <i class="bi bi-gem"></i>
                </div>
                <div class="attr-form-card-title">
                    <h2>Stone Type Details</h2>
                    <p>Update the stone type information</p>
                </div>
            </div>

            <div class="attr-form-card-body">
                <form method="POST" action="{{ route('stone_types.update', $item->id) }}">
                    @csrf
                    @method('PUT')

                    <!-- Name Field -->
                    <div class="attr-form-group">
                        <label class="attr-form-label">
                            <i class="bi bi-tag"></i>
                            <span>Name</span>
                            <span class="attr-required">*</span>
                        </label>
                        <input type="text" name="name" class="attr-form-input @error('name') is-invalid @enderror"
                            value="{{ old('name', $item->name) }}" placeholder="e.g., Ruby, Sapphire, Emerald" required>
                        @error('name')
                            <div class="attr-error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Status Toggle -->
                    <div class="attr-form-group">
                        <label class="attr-form-label">
                            <i class="bi bi-toggle-on"></i>
                            <span>Status</span>
                        </label>
                        <div class="attr-status-toggle-group">
                            <label class="attr-status-toggle">
                                <input type="radio" name="is_active" value="1" {{ old('is_active', $item->is_active) == '1' ? 'checked' : '' }}>
                                <div class="attr-toggle-indicator active">
                                    <i class="bi bi-check-circle"></i>
                                    <span>Active</span>
                                </div>
                            </label>
                            <label class="attr-status-toggle">
                                <input type="radio" name="is_active" value="0" {{ old('is_active', $item->is_active) == '0' ? 'checked' : '' }}>
                                <div class="attr-toggle-indicator inactive">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Inactive</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="attr-form-actions">
                        <a href="{{ route('stone_types.index') }}" class="attr-btn-cancel">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancel</span>
                        </a>
                        <button type="submit" class="attr-btn-submit">
                            <i class="bi bi-check-circle"></i>
                            <span>Update Stone Type</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.attribute-styles')
@endsection