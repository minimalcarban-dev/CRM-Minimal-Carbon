@extends('layouts.admin')

@section('title', 'Create Metal Type')

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
                        <span class="breadcrumb-current">Create New</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-plus-circle"></i>
                        Create Metal Type
                    </h1>
                    <p class="page-subtitle">Add a new metal type to your inventory</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('metal_types.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('metal_types.store') }}" id="metalTypeForm">
            @csrf

            <!-- Metal Type Form Card -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-award"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Metal Type Information</h5>
                            <p class="section-description">Enter the metal type details below</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                Name <span class="required">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-tag input-icon"></i>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}"
                                       placeholder="e.g., Gold, Silver, Platinum"
                                       required>
                            </div>
                            @error('name')
                                <div class="error-message">
                                    <i class="bi bi-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Enter the name of the metal type
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <div class="toggle-switch-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <label for="is_active" class="toggle-label">
                                    <span class="status-text">Active</span>
                                    <span class="status-description">Enable this metal type for use</span>
                                </label>
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Toggle to activate or deactivate this metal type
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Footer -->
            <div class="action-footer">
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-circle"></i>
                    <span>Create Metal Type</span>
                </button>
                <a href="{{ route('metal_types.index') }}" class="btn-secondary-custom">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>

    @endsection
