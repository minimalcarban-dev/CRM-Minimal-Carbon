@extends('layouts.admin')

@section('title', 'Create Ring Size')

@section('content')
<div class="ring-size-create-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="breadcrumb-nav">
                    <a href="{{ url('/') }}" class="breadcrumb-link">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <a href="{{ route('ring_sizes.index') }}" class="breadcrumb-link">Ring Sizes</a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Create</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-plus-circle-fill"></i>
                    Create Ring Size
                </h1>
                <p class="page-subtitle">Add a new ring size to the system</p>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <div class="form-header-icon">
                    <i class="bi bi-circle"></i>
                </div>
                <div>
                    <h2 class="form-title">Ring Size Information</h2>
                    <p class="form-subtitle">Enter the details for the new ring size</p>
                </div>
            </div>

            <form method="POST" action="{{ route('ring_sizes.store') }}" class="form-body">
                @csrf

                <!-- Name Field -->
                <div class="form-group">
                    <label class="form-label" for="name">
                        <i class="bi bi-tag"></i>
                        <span>Name</span>
                        <span class="required-indicator">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name"
                        name="name" 
                        class="form-control-custom @error('name') is-invalid @enderror" 
                        value="{{ old('name') }}"
                        placeholder="Enter ring size name (e.g., Size 7, Medium, etc.)"
                        autofocus>
                    @error('name')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="field-hint">
                        <i class="bi bi-info-circle"></i>
                        Use a clear identifier for the ring size (e.g., numerical size or descriptive name)
                    </div>
                </div>

                <!-- Active Status Toggle -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-toggle2-on"></i>
                        <span>Status</span>
                    </label>
                    <div class="toggle-container">
                        <label class="toggle-switch">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                id="is_active" 
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <div class="toggle-label-container">
                            <span class="toggle-label">Active</span>
                            <span class="toggle-description">Ring size will be available for selection</span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle"></i>
                        <span>Create Ring Size</span>
                    </button>
                    <a href="{{ route('ring_sizes.index') }}" class="btn-cancel">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Help Card -->
        <div class="help-card">
            <div class="help-header">
                <i class="bi bi-lightbulb-fill"></i>
                <h3>Quick Guide</h3>
            </div>
            <div class="help-content">
                <div class="help-item">
                    <div class="help-icon">
                        <i class="bi bi-1-circle-fill"></i>
                    </div>
                    <div class="help-text">
                        <strong>Naming Convention</strong>
                        <p>Use standard ring size formats like "Size 6", "Size 7.5", or descriptive names like "Small", "Medium", "Large"</p>
                    </div>
                </div>

                <div class="help-item">
                    <div class="help-icon">
                        <i class="bi bi-2-circle-fill"></i>
                    </div>
                    <div class="help-text">
                        <strong>Availability</strong>
                        <p>Active ring sizes appear in product listings and checkout. Inactive sizes are hidden from customers</p>
                    </div>
                </div>

                <div class="help-item">
                    <div class="help-icon">
                        <i class="bi bi-3-circle-fill"></i>
                    </div>
                    <div class="help-text">
                        <strong>Organization</strong>
                        <p>Keep ring sizes organized and consistent for easier inventory management</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-focus on first input
        const firstInput = document.querySelector('.form-control-custom');
        if (firstInput) {
            firstInput.focus();
        }

        // Toggle animation
        const toggleInput = document.getElementById('is_active');
        const toggleContainer = toggleInput?.closest('.toggle-container');
        
        if (toggleInput && toggleContainer) {
            toggleInput.addEventListener('change', function() {
                if (this.checked) {
                    toggleContainer.style.borderColor = 'var(--success)';
                    setTimeout(() => {
                        toggleContainer.style.borderColor = '';
                    }, 300);
                }
            });
        }
    });
</script>

@endsection

