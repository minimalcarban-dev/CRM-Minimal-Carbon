@extends('layouts.admin')

@section('title', 'Create Ring Size')

@section('content')
    <div class="attr-form-container">
        <div class="attr-page-header">
            <div class="attr-header-content">
                <div class="attr-header-left">
                    <div class="attr-breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="attr-breadcrumb-link"><i
                                class="bi bi-house-door"></i> Dashboard</a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <a href="{{ route('ring_sizes.index') }}" class="attr-breadcrumb-link">Ring Sizes</a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <span class="attr-breadcrumb-current">Create</span>
                    </div>
                    <h1 class="attr-page-title"><i class="bi bi-plus-circle"></i> Create Ring Size</h1>
                    <p class="attr-page-subtitle">Add a new ring size to your inventory</p>
                </div>
                <div class="attr-header-right">
                    <a href="{{ route('ring_sizes.index') }}" class="attr-btn-back"><i class="bi bi-arrow-left"></i> Back to
                        List</a>
                </div>
            </div>
        </div>

        <div class="attr-form-card">
            <div class="attr-form-card-header">
                <div class="attr-form-card-icon"><i class="bi bi-circle"></i></div>
                <div class="attr-form-card-title">
                    <h2>Ring Size Details</h2>
                    <p>Enter the information for the new ring size</p>
                </div>
            </div>
            <div class="attr-form-card-body">
                <form method="POST" action="{{ route('ring_sizes.store') }}">
                    @csrf
                    <div class="attr-form-group">
                        <label class="attr-form-label"><i class="bi bi-tag"></i> Name <span
                                class="attr-required">*</span></label>
                        <input type="text" name="name" class="attr-form-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="e.g., Size 5, Size 6, Size 7" required autofocus>
                        @error('name')<div class="attr-error-message"><i class="bi bi-exclamation-circle"></i>
                        {{ $message }}</div>@enderror
                    </div>
                    <div class="attr-form-group">
                        <label class="attr-form-label"><i class="bi bi-toggle-on"></i> Status</label>
                        <div class="attr-status-toggle-group">
                            <label class="attr-status-toggle"><input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <div class="attr-toggle-indicator active"><i class="bi bi-check-circle"></i> Active</div>
                            </label>
                            <label class="attr-status-toggle"><input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                <div class="attr-toggle-indicator inactive"><i class="bi bi-x-circle"></i> Inactive</div>
                            </label>
                        </div>
                    </div>
                    <div class="attr-form-actions">
                        <a href="{{ route('ring_sizes.index') }}" class="attr-btn-cancel"><i class="bi bi-x-circle"></i>
                            Cancel</a>
                        <button type="submit" class="attr-btn-submit"><i class="bi bi-check-circle"></i> Create Ring
                            Size</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('partials.attribute-styles')
@endsection