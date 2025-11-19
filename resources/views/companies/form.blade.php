<div class="form-grid">
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-info-circle-fill"></i>
            <h3>Basic Information</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-building"></i>
                Company Name
                <span class="required">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $company->name ?? '') }}"
                class="form-input @error('name') error @enderror" placeholder="Enter company name" required>
            @error('name')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-envelope"></i>
                Email Address
            </label>
            <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}"
                class="form-input @error('email') error @enderror" placeholder="company@example.com">
            @error('email')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-telephone"></i>
                Phone Number
            </label>
            <input type="text" name="phone" value="{{ old('phone', $company->phone ?? '') }}"
                class="form-input @error('phone') error @enderror" placeholder="+1 (555) 000-0000">
            @error('phone')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-geo-alt-fill"></i>
            <h3>Location & Status</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-pin-map"></i>
                Address
            </label>
            <textarea name="address" class="form-textarea @error('address') error @enderror" rows="4"
                placeholder="Enter company address">{{ old('address', $company->address ?? '') }}</textarea>
            @error('address')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-toggle-on"></i>
                Status
            </label>
            <div class="status-toggle-group">
                <label
                    class="status-toggle {{ (old('status', $company->status ?? 'active') == 'active') ? 'active' : '' }}">
                    <input type="radio" name="status" value="active" {{ (old('status', $company->status ?? 'active') == 'active') ? 'checked' : '' }}>
                    <span class="toggle-indicator active">
                        <i class="bi bi-check-circle-fill"></i>
                        Active
                    </span>
                </label>
                <label
                    class="status-toggle {{ (old('status', $company->status ?? '') == 'inactive') ? 'active' : '' }}">
                    <input type="radio" name="status" value="inactive" {{ (old('status', $company->status ?? '') == 'inactive') ? 'checked' : '' }}>
                    <span class="toggle-indicator inactive">
                        <i class="bi bi-pause-circle-fill"></i>
                        Inactive
                    </span>
                </label>
            </div>
            @error('status')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>