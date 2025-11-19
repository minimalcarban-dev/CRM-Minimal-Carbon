@extends('layouts.admin')

@section('title', 'Edit Setting Type')

@section('content')
<div class="setting-type-edit-container">
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
                    <span class="breadcrumb-current">Edit</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-pencil-square"></i>
                    Edit Setting Type
                </h1>
                <p class="page-subtitle">Update the details for "{{ $item->name }}"</p>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="content-grid">
        <div class="form-card">
            <div class="form-header">
                <div class="form-header-icon">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h2 class="form-title">Setting Type Information</h2>
                    <p class="form-subtitle">Modify the details for this setting type</p>
                </div>
            </div>

            <form method="POST" action="{{ route('setting_types.update', $item->id) }}" class="form-body">
                @csrf
                @method('PUT')

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
                        value="{{ old('name', $item->name) }}"
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
                                {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
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
                        <span>Update Setting Type</span>
                    </button>
                    <a href="{{ route('setting_types.index') }}" class="btn-cancel">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Card -->
        <div class="info-card">
            <div class="info-header">
                <div class="info-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="info-title">Edit Information</h3>
            </div>

            <div class="info-content">
                <div class="info-item">
                    <div class="info-item-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="info-item-text">
                        <strong>Created</strong>
                        <p>{{ $item->created_at?->format('M d, Y') ?? 'Unknown' }}</p>
                        @if($item->created_at)
                            <span class="text-muted small">{{ $item->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>

                @if($item->updated_at && $item->updated_at != $item->created_at)
                    <div class="info-item">
                        <div class="info-item-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="info-item-text">
                            <strong>Last Modified</strong>
                            <p>{{ $item->updated_at->format('M d, Y') }}</p>
                            <span class="text-muted small">{{ $item->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endif

                <div class="info-divider"></div>

                <div class="info-item">
                    <div class="info-item-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div class="info-item-text">
                        <strong>Save Changes</strong>
                        <p>Click "Update" to save your modifications to this setting type</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-item-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="info-item-text">
                        <strong>Be Careful</strong>
                        <p>Changes will affect all systems using this setting type</p>
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
            // Select text for easy editing
            firstInput.select();
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
                } else {
                    toggleContainer.style.borderColor = 'var(--gray)';
                    setTimeout(() => {
                        toggleContainer.style.borderColor = '';
                    }, 300);
                }
            });
        }

        // Unsaved changes warning
        let formChanged = false;
        const form = document.querySelector('.form-body');
        const inputs = form?.querySelectorAll('input');

        inputs?.forEach(input => {
            input.addEventListener('change', () => {
                formChanged = true;
            });
        });

        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        form?.addEventListener('submit', () => {
            formChanged = false;
        });
    });
</script>

@endsection
