@extends('layouts.admin')

@section('title', 'Return Gold')

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
                        <span class="breadcrumb-current">Return Gold</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-arrow-left-circle" style="color: #8b5cf6;"></i>
                        Return Gold from Factory
                    </h1>
                    <p class="page-subtitle">Record gold returned from a production factory back to owner stock</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        @if($factoriesWithStock->count() === 0)
            <div class="tracker-table-card" style="padding: 3rem; text-align: center;">
                <div style="font-size: 3rem; color: #64748b; margin-bottom: 1rem;"><i class="bi bi-inbox"></i></div>
                <h3 style="color: #1e293b;">No Gold in Factories</h3>
                <p style="color: #64748b;">There's no gold currently allocated to any factory.</p>
                <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i> Back to Gold Tracking
                </a>
            </div>
        @else
            <form action="{{ route('gold-tracking.return.store') }}" method="POST">
                @csrf

                <!-- Return Form -->
                <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-arrow-left-circle" style="color: #8b5cf6;"></i> Return Details
                    </h3>
                    <div class="form-grid"
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label">Return Date <span style="color: #ef4444;">*</span></label>
                            <input type="date" name="distribution_date"
                                class="form-control @error('distribution_date') is-invalid @enderror"
                                value="{{ old('distribution_date', date('Y-m-d')) }}" required>
                            @error('distribution_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Factory <span style="color: #ef4444;">*</span></label>
                            <select name="factory_id" id="factory_select"
                                class="form-control @error('factory_id') is-invalid @enderror" required
                                onchange="updateMaxWeight()">
                                <option value="">-- Select Factory --</option>
                                @foreach($factoriesWithStock as $factory)
                                    <option value="{{ $factory->id }}" data-stock="{{ $factory->gold_stock }}" {{ old('factory_id') == $factory->id ? 'selected' : '' }}>
                                        {{ $factory->name }} (Has: {{ number_format($factory->gold_stock, 3) }} gm)
                                    </option>
                                @endforeach
                            </select>
                            @error('factory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Weight to Return (grams) <span style="color: #ef4444;">*</span></label>
                            <input type="number" name="weight_grams" id="weight_grams"
                                class="form-control @error('weight_grams') is-invalid @enderror"
                                value="{{ old('weight_grams') }}" step="0.001" min="0.001" placeholder="e.g., 5.000" required
                                oninput="validateWeight()">
                            <small id="max_weight_hint" style="color: #8b5cf6;"><i class="bi bi-info-circle"></i> Select a
                                factory first</small>
                            <div id="weight_error"
                                style="display: none; color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">
                                <i class="bi bi-exclamation-circle"></i> <span id="weight_error_text"></span>
                            </div>
                            @error('weight_grams') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Purpose/Reason</label>
                            <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror"
                                value="{{ old('purpose') }}" placeholder="e.g., Unused gold, Job completed">
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
                    <button type="submit" class="btn-primary-custom" id="submitBtn"
                        style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <i class="bi bi-arrow-left-circle"></i> Return Gold
                    </button>
                </div>
            </form>
        @endif
    </div>

    @push('scripts')
        <script>
            let currentMaxStock = 0;

            function updateMaxWeight() {
                const select = document.getElementById('factory_select');
                const selectedOption = select.options[select.selectedIndex];
                const hint = document.getElementById('max_weight_hint');
                const input = document.getElementById('weight_grams');

                if (selectedOption.value) {
                    currentMaxStock = parseFloat(selectedOption.dataset.stock);
                    hint.innerHTML = '<i class="bi bi-exclamation-triangle" style="color: #f59e0b;"></i> Max: ' + currentMaxStock.toFixed(3) + ' gm available in this factory';
                    hint.style.color = '#f59e0b';
                    input.max = currentMaxStock;
                } else {
                    currentMaxStock = 0;
                    hint.innerHTML = '<i class="bi bi-info-circle"></i> Select a factory first';
                    hint.style.color = '#8b5cf6';
                    input.removeAttribute('max');
                }
                validateWeight();
            }

            function validateWeight() {
                const input = document.getElementById('weight_grams');
                const errorDiv = document.getElementById('weight_error');
                const errorText = document.getElementById('weight_error_text');
                const hint = document.getElementById('max_weight_hint');
                const submitBtn = document.getElementById('submitBtn');
                const value = parseFloat(input.value) || 0;

                if (currentMaxStock > 0 && value > currentMaxStock) {
                    // Show error state
                    input.style.borderColor = '#ef4444';
                    input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
                    input.style.background = 'rgba(239, 68, 68, 0.05)';
                    errorDiv.style.display = 'block';
                    errorText.textContent = 'Cannot return more than factory has (' + currentMaxStock.toFixed(3) + ' gm)';
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

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function () {
                updateMaxWeight();
            });
        </script>
    @endpush
@endsection