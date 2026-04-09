<section class="attribute-workspace-card">
    <div class="attribute-workspace-header">
        <div>
            <p class="attribute-eyebrow">{{ $mode === 'edit' ? 'Edit Record' : 'Create Record' }}</p>
            <h2 class="attribute-workspace-title">{{ $headingLabel }}</h2>
            <p class="attribute-workspace-subtitle">{{ $descriptionLabel }}</p>
        </div>

        <div class="attribute-workspace-header-actions">
            <button type="button" class="attribute-btn attribute-btn-secondary"
                data-hub-action="list" data-hub-module="{{ $module['key'] }}">
                <i class="bi bi-arrow-left"></i>
                Back to List
            </button>
        </div>
    </div>

    <div class="attribute-form-card">
        <form action="{{ $formAction }}" method="POST" data-attribute-form>
            @csrf
            @if ($formMethod === 'PUT')
                @method('PUT')
            @endif

            <input type="hidden" name="module" value="{{ $module['key'] }}">

            <div class="attribute-form-group">
                <label class="attribute-form-label" for="attribute-name">
                    <i class="bi bi-tag"></i>
                    Name <span class="attribute-required">*</span>
                </label>
                <input type="text" name="name" id="attribute-name"
                    class="attribute-form-input @error('name') is-invalid @enderror"
                    value="{{ old('name', $item->name ?? '') }}" placeholder="Enter {{ strtolower($module['singular_label']) }} name"
                    required autofocus>
                @error('name')
                    <div class="attribute-error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="attribute-form-group">
                <label class="attribute-form-label">
                    <i class="bi bi-toggle-on"></i>
                    Status
                </label>
                <div class="attribute-status-toggle-group">
                    <label class="attribute-status-toggle">
                        <input type="radio" name="is_active" value="1"
                            {{ old('is_active', $item->is_active ?? 1) == '1' ? 'checked' : '' }}>
                        <span class="attribute-toggle-indicator active">
                            <i class="bi bi-check-circle"></i>
                            Active
                        </span>
                    </label>

                    <label class="attribute-status-toggle">
                        <input type="radio" name="is_active" value="0"
                            {{ old('is_active', $item->is_active ?? 1) == '0' ? 'checked' : '' }}>
                        <span class="attribute-toggle-indicator inactive">
                            <i class="bi bi-x-circle"></i>
                            Inactive
                        </span>
                    </label>
                </div>
            </div>

            <div class="attribute-form-actions">
                <button type="button" class="attribute-btn attribute-btn-secondary"
                    data-hub-action="list" data-hub-module="{{ $module['key'] }}">
                    Cancel
                </button>
                <button type="submit" class="attribute-btn attribute-btn-primary">
                    <i class="bi bi-check-circle"></i>
                    {{ $submitLabel }}
                </button>
            </div>
        </form>
    </div>
</section>
