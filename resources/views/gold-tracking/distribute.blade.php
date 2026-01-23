@extends('layouts.admin')

@section('title', 'Distribute Gold')

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
                        <a href="{{ route('gold-tracking.index') }}" class="breadcrumb-link">Gold Tracking</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Distribute Gold</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-buildings" style="color: #6366f1;"></i>
                        Distribute Gold to Factory
                    </h1>
                    <p class="page-subtitle">Send gold from owner stock to a production factory</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Available Stock Alert -->
        <div
            style="background: rgba(59, 130, 246, 0.1); border: 2px solid #3b82f6; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="bi bi-box-seam" style="font-size: 1.5rem; color: #3b82f6;"></i>
            <div>
                <strong style="color: #1e40af;">Available Owner Stock: {{ number_format($availableStock, 3) }} gm</strong>
                <span style="color: #3b82f6;">(You can distribute up to this amount)</span>
            </div>
        </div>

        @if($availableStock <= 0)
            <div class="tracker-table-card" style="padding: 3rem; text-align: center;">
                <div style="font-size: 3rem; color: #64748b; margin-bottom: 1rem;"><i class="bi bi-exclamation-triangle"></i>
                </div>
                <h3 style="color: #1e293b;">No Gold Available</h3>
                <p style="color: #64748b;">You don't have any gold in stock to distribute. Add a purchase first.</p>
                <a href="{{ route('gold-tracking.purchases.create') }}" class="btn-primary-custom">
                    <i class="bi bi-plus-circle"></i> Add Gold Purchase
                </a>
            </div>
        @else
            <form action="{{ route('gold-tracking.distribute.store') }}" method="POST">
                @csrf

                <!-- Distribution Form -->
                <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-arrow-right-circle" style="color: #6366f1;"></i> Distribution Details
                    </h3>
                    <div class="form-grid"
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Distribution Date <span style="color: #ef4444;">*</span></label>
                            <input type="date" name="distribution_date"
                                class="form-control @error('distribution_date') is-invalid @enderror"
                                value="{{ old('distribution_date', date('Y-m-d')) }}" required>
                            @error('distribution_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Factory <span style="color: #ef4444;">*</span></label>
                            <select name="factory_id" class="form-control @error('factory_id') is-invalid @enderror" required>
                                <option value="">-- Select Factory --</option>
                                @foreach($factories as $factory)
                                    <option value="{{ $factory->id }}" {{ old('factory_id') == $factory->id ? 'selected' : '' }}>
                                        {{ $factory->name }} (Current: {{ number_format($factory->gold_stock, 1) }} gm)
                                    </option>
                                @endforeach
                            </select>
                            @error('factory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Weight to Distribute (grams) <span
                                    style="color: #ef4444;">*</span></label>
                            <input type="number" name="weight_grams" id="weight_grams"
                                class="form-control @error('weight_grams') is-invalid @enderror"
                                value="{{ old('weight_grams') }}" step="0.001" min="0.001" max="{{ $availableStock }}"
                                placeholder="e.g., 10.000" required oninput="validateWeight()">
                            <small id="weight_hint" style="color: #f59e0b;"><i class="bi bi-exclamation-triangle"></i> Max:
                                {{ number_format($availableStock, 3) }} gm available</small>
                            <div id="weight_error"
                                style="display: none; color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">
                                <i class="bi bi-exclamation-circle"></i> <span id="weight_error_text"></span>
                            </div>
                            @error('weight_grams') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Purpose</label>
                            <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror"
                                value="{{ old('purpose') }}" placeholder="e.g., Ring Production, Jewellery Making">
                            @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Additional notes...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tracker-form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">Cancel</a>
                    <button type="submit" class="btn-primary-custom" id="submitBtn">
                        <i class="bi bi-truck"></i> Distribute Gold
                    </button>
                </div>
            </form>
        @endif
    </div>

    @push('scripts')
        <script>
            const maxStock = {{ $availableStock }};

            function validateWeight() {
                const input = document.getElementById('weight_grams');
                const errorDiv = document.getElementById('weight_error');
                const errorText = document.getElementById('weight_error_text');
                const hint = document.getElementById('weight_hint');
                const submitBtn = document.getElementById('submitBtn');
                const value = parseFloat(input.value) || 0;

                if (value > maxStock) {
                    // Show error state
                    input.style.borderColor = '#ef4444';
                    input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
                    input.style.background = 'rgba(239, 68, 68, 0.05)';
                    errorDiv.style.display = 'block';
                    errorText.textContent = 'Cannot distribute more than available stock (' + maxStock.toFixed(3) + ' gm)';
                    hint.style.display = 'none';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                } else if (value <= 0 && input.value !== '') {
                    // Show error for zero or negative
                    input.style.borderColor = '#ef4444';
                    input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
                    input.style.background = 'rgba(239, 68, 68, 0.05)';
                    errorDiv.style.display = 'block';
                    errorText.textContent = 'Weight must be greater than 0';
                    hint.style.display = 'none';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                } else {
                    // Clear error state
                    input.style.borderColor = '';
                    input.style.boxShadow = '';
                    input.style.background = '';
                    errorDiv.style.display = 'none';
                    hint.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                }
            }

            // Validate on page load if there's a value
            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('weight_grams').value) {
                    validateWeight();
                }
            });
        </script>
    @endpush
@endsection