@extends('layouts.admin')

@section('title', 'Edit Factory')

@section('content')
    <div class="diamond-management-container tracker-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('factories.index') }}" class="breadcrumb-link">Factories</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Edit {{ $factory->name }}</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-buildings"></i>
                        Edit Factory
                    </h1>
                    <p class="page-subtitle">Update factory details</p>
                </div>
            </div>
        </div>

        <form action="{{ route('factories.update', $factory) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-building" style="color: #6366f1;"></i> Factory Details
                    <span class="tracker-badge tracker-badge-primary"
                        style="margin-left: 0.5rem;">{{ $factory->code }}</span>
                </h3>
                <div class="form-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Factory Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $factory->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person"
                            class="form-control @error('contact_person') is-invalid @enderror"
                            value="{{ old('contact_person', $factory->contact_person) }}">
                        @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="contact_phone"
                            class="form-control @error('contact_phone') is-invalid @enderror"
                            value="{{ old('contact_phone', $factory->contact_phone) }}">
                        @error('contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                            value="{{ old('location', $factory->location) }}">
                        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.25rem;">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $factory->notes) }}</textarea>
                </div>

                <div class="form-group" style="margin-top: 1.25rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $factory->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                        <span>Factory is Active</span>
                    </label>
                </div>

                @if($factory->current_stock > 0)
                    <div
                        style="margin-top: 1.5rem; padding: 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 8px; border: 1px solid #f59e0b;">
                        <i class="bi bi-info-circle" style="color: #f59e0b;"></i>
                        <strong style="color: #b45309;">Current Gold Stock:</strong>
                        {{ number_format($factory->current_stock, 3) }} gm
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="tracker-form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('factories.index') }}" class="btn-secondary-custom">Cancel</a>
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-lg"></i> Update Factory
                </button>
            </div>
        </form>
    </div>
@endsection