@extends('layouts.admin')

@section('title', 'Create Setting Type')

@section('content')
<div class="setting-type-create-container">
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
                    <span class="breadcrumb-current">Create</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-plus-circle-fill"></i>
                    Create Setting Type
                </h1>
                <p class="page-subtitle">Add a new setting type to the system</p>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <div class="form-header-icon">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h2 class="form-title">Setting Type Information</h2>
                    <p class="form-subtitle">Enter the details for the new setting type</p>
                </div>
            </div>

            <form method="POST" action="{{ route('setting_types.store') }}" class="form-body">
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
                        placeholder="Enter setting type name"
                        autofocus>
                    @error('name')
                        <div class="error-message">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="field-hint">
                        <i class="bi bi-info-circle"></i>
                        Choose a clear and descriptive name for this setting type
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
                            <span class="toggle-description">Setting type will be available for use</span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle"></i>
                        <span>Create Setting Type</span>
                    </button>
                    <a href="{{ route('setting_types.index') }}" class="btn-cancel">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Help Card -->
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
