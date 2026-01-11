@extends('layouts.admin')

@section('title', 'Create Stone Shape')

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
                        <a href="{{ route('stone_shapes.index') }}" class="attr-breadcrumb-link">Stone Shapes</a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <span class="attr-breadcrumb-current">Create</span>
                    </div>
                    <h1 class="attr-page-title">
                        <i class="bi bi-plus-circle"></i>
                        Create Stone Shape
                    </h1>
                    <p class="attr-page-subtitle">Add a new stone shape to your inventory</p>
                </div>
                <div class="attr-header-right">
                    <a href="{{ route('stone_shapes.index') }}" class="attr-btn-back">
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
                    <i class="bi bi-pentagon"></i>
                </div>
                <div class="attr-form-card-title">
                    <h2>Stone Shape Details</h2>
                    <p>Enter the information for the new stone shape</p>
                </div>
            </div>

            <div class="attr-form-card-body">
                <form method="POST" action="{{ route('stone_shapes.store') }}">
                    @csrf

                    <!-- Name Field -->
                    <div class="attr-form-group">
                        <label class="attr-form-label">
                            <i class="bi bi-tag"></i>
                            <span>Name</span>
                            <span class="attr-required">*</span>
                        </label>
                        <input type="text" name="name" class="attr-form-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="e.g., Round, Oval, Princess, Cushion" required autofocus>
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
                                <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <div class="attr-toggle-indicator active">
                                    <i class="bi bi-check-circle"></i>
                                    <span>Active</span>
                                </div>
                            </label>
                            <label class="attr-status-toggle">
                                <input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                <div class="attr-toggle-indicator inactive">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Inactive</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="attr-form-actions">
                        <a href="{{ route('stone_shapes.index') }}" class="attr-btn-cancel">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancel</span>
                        </a>
                        <button type="submit" class="attr-btn-submit">
                            <i class="bi bi-check-circle"></i>
                            <span>Create Stone Shape</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.attribute-styles')
@endsection