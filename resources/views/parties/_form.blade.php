@csrf
<div class="party-form-wrapper">
    <!-- Form Header -->
    <div class="form-header">
        <div class="header-info">
            <h2 class="form-title">
                <i class="bi bi-person-circle"></i>
                Party Information
            </h2>
            <p class="form-description">Fill in the details to {{ isset($party) ? 'update' : 'create' }} a party</p>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="main-form-card">
        <!-- Personal Details -->
        <div class="field-group">
            <div class="group-header">
                <i class="bi bi-person-badge"></i>
                <span>Personal Details</span>
            </div>
            <div class="fields-row">
                <div class="field-col">
                    <label class="field-label">
                        Party Name <span class="req">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-person"></i>
                        <input 
                            type="text" 
                            name="name" 
                            class="field-input" 
                            value="{{ old('name', $party->name ?? '') }}" 
                            placeholder="Enter party name"
                            required
                        >
                    </div>
                </div>

                <div class="field-col">
                    <label class="field-label">Phone Number</label>
                    <div class="input-wrapper">
                        <i class="bi bi-telephone"></i>
                        <input 
                            type="text" 
                            name="phone" 
                            class="field-input" 
                            value="{{ old('phone', $party->phone ?? '') }}"
                            placeholder="+91 XXXXX XXXXX"
                        >
                    </div>
                </div>

                <div class="field-col">
                    <label class="field-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="bi bi-envelope"></i>
                        <input 
                            type="email" 
                            name="email" 
                            class="field-input" 
                            value="{{ old('email', $party->email ?? '') }}"
                            placeholder="email@example.com"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Details -->
        <div class="field-group">
            <div class="group-header">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Tax & Identification</span>
            </div>
            <div class="fields-row">
                <div class="field-col">
                    <label class="field-label">GST Number (India)</label>
                    <div class="input-wrapper">
                        <i class="bi bi-file-earmark-text"></i>
                        <input 
                            type="text" 
                            name="gst_no" 
                            class="field-input" 
                            value="{{ old('gst_no', $party->gst_no ?? '') }}"
                            placeholder="22AAAAA0000A1Z5"
                        >
                    </div>
                    <span class="field-note">For Indian registered businesses</span>
                </div>

                <div class="field-col">
                    <label class="field-label">Tax ID / VAT</label>
                    <div class="input-wrapper">
                        <i class="bi bi-globe"></i>
                        <input 
                            type="text" 
                            name="tax_id" 
                            class="field-input" 
                            value="{{ old('tax_id', $party->tax_id ?? '') }}"
                            placeholder="VAT or Tax ID"
                        >
                    </div>
                    <span class="field-note">For foreign parties</span>
                </div>

                <div class="field-col">
                    <label class="field-label">PAN Number</label>
                    <div class="input-wrapper">
                        <i class="bi bi-credit-card-2-front"></i>
                        <input 
                            type="text" 
                            name="pan_no" 
                            class="field-input" 
                            value="{{ old('pan_no', $party->pan_no ?? '') }}"
                            placeholder="ABCDE1234F"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Details -->
        <div class="field-group">
            <div class="group-header">
                <i class="bi bi-geo-alt"></i>
                <span>Location & Address</span>
            </div>
            <div class="fields-row">
                <div class="field-col">
                    <label class="field-label">State</label>
                    <div class="input-wrapper">
                        <i class="bi bi-map"></i>
                        <input 
                            type="text" 
                            name="state" 
                            class="field-input" 
                            value="{{ old('state', $party->state ?? '') }}"
                            placeholder="Maharashtra, Gujarat"
                        >
                    </div>
                </div>

                <div class="field-col field-col-sm">
                    <label class="field-label">State Code</label>
                    <div class="input-wrapper">
                        <i class="bi bi-hash"></i>
                        <input 
                            type="text" 
                            name="state_code" 
                            class="field-input" 
                            value="{{ old('state_code', $party->state_code ?? '') }}"
                            placeholder="27"
                        >
                    </div>
                </div>

                <div class="field-col">
                    <label class="field-label">Country</label>
                    <div class="select-wrapper">
                        <i class="bi bi-flag"></i>
                        <select name="country" class="field-select">
                            <option value="">India</option>
                            <option value="United Kingdom" {{ (old('country',$party->country ?? '')=='United Kingdom')?'selected':'' }}>United Kingdom</option>
                            <option value="United States" {{ (old('country',$party->country ?? '')=='United States')?'selected':'' }}>United States</option>
                            <option value="Other" {{ (old('country',$party->country ?? '')=='Other')?'selected':'' }}>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="fields-row">
                <div class="field-col field-col-full">
                    <label class="field-label">Full Address</label>
                    <textarea 
                        name="address" 
                        class="field-textarea" 
                        rows="3"
                        placeholder="Enter complete address with street, city, state, and postal code"
                    >{{ old('address', $party->address ?? '') }}</textarea>
                </div>
            </div>

            <div class="fields-row">
                <div class="field-col">
                    <div class="toggle-field">
                        <input 
                            class="toggle-checkbox" 
                            type="checkbox" 
                            name="is_foreign" 
                            value="1" 
                            id="is_foreign" 
                            {{ old('is_foreign', $party->is_foreign ?? false) ? 'checked' : '' }}
                        >
                        <label class="toggle-label" for="is_foreign">
                            <span class="toggle-switch"></span>
                            <span class="toggle-text">
                                <strong>Foreign Party</strong>
                                <small>Enable if this is an international party</small>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Buttons -->
        <div class="form-footer">
            <a href="{{ route('parties.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i>
                Save Party
            </button>
        </div>
    </div>
</div>

<style>
    :root {
        --clr-primary: #2563eb;
        --clr-primary-hover: #1d4ed8;
        --clr-success: #10b981;
        --clr-danger: #ef4444;
        --clr-text: #0f172a;
        --clr-text-muted: #64748b;
        --clr-text-light: #94a3b8;
        --clr-bg: #f8fafc;
        --clr-card: #ffffff;
        --clr-border: #e2e8f0;
        --clr-border-focus: #3b82f6;
        --clr-input-bg: #f8fafc;
        --shadow: 0 1px 3px rgba(0,0,0,0.05);
        --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
        --radius: 10px;
    }

    * {
        box-sizing: border-box;
    }

    .party-form-wrapper {
        max-width: 1100px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    /* Form Header */
    .form-header {
        background: var(--clr-card);
        padding: 1.75rem 2rem;
        border-radius: var(--radius);
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--clr-border);
    }

    .form-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--clr-text);
        margin: 0 0 0.5rem 0;
    }

    .form-title i {
        color: var(--clr-primary);
    }

    .form-description {
        color: var(--clr-text-muted);
        margin: 0;
        font-size: 0.95rem;
    }

    /* Main Card */
    .main-form-card {
        background: var(--clr-card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--clr-border);
        overflow: hidden;
    }

    /* Field Group */
    .field-group {
        padding: 2rem;
        border-bottom: 1px solid var(--clr-border);
    }

    .field-group:last-of-type {
        border-bottom: none;
    }

    .group-header {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--clr-text);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--clr-border);
    }

    .group-header i {
        color: var(--clr-primary);
        font-size: 1.25rem;
    }

    /* Fields Row */
    .fields-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .fields-row:last-child {
        margin-bottom: 0;
    }

    .field-col {
        display: flex;
        flex-direction: column;
    }

    .field-col-sm {
        grid-column: span 1;
        min-width: 150px;
    }

    .field-col-full {
        grid-column: 1 / -1;
    }

    .field-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--clr-text);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .req {
        color: var(--clr-danger);
        margin-left: 0.25rem;
    }

    /* Input Wrapper */
    .input-wrapper,
    .select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-wrapper i,
    .select-wrapper i {
        position: absolute;
        left: 0.875rem;
        color: var(--clr-text-muted);
        font-size: 0.95rem;
        pointer-events: none;
    }

    .field-input,
    .field-select,
    .field-textarea {
        width: 100%;
        padding: 0.75rem 0.875rem 0.75rem 2.5rem;
        border: 2px solid var(--clr-border);
        border-radius: var(--radius);
        font-size: 0.95rem;
        color: var(--clr-text);
        background: var(--clr-input-bg);
        transition: all 0.2s;
        font-family: inherit;
    }

    .field-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.875rem center;
        padding-right: 2.5rem;
    }

    .field-input:focus,
    .field-select:focus,
    .field-textarea:focus {
        outline: none;
        border-color: var(--clr-border-focus);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background: var(--clr-card);
    }

    .field-textarea {
        resize: vertical;
        min-height: 80px;
        line-height: 1.5;
    }

    .field-note {
        display: block;
        margin-top: 0.375rem;
        font-size: 0.75rem;
        color: var(--clr-text-light);
        font-style: italic;
    }

    /* Toggle Field */
    .toggle-field {
        display: flex;
        align-items: flex-start;
        padding: 1rem;
        background: var(--clr-bg);
        border: 2px solid var(--clr-border);
        border-radius: var(--radius);
        transition: all 0.2s;
    }

    .toggle-field:hover {
        border-color: var(--clr-primary);
    }

    .toggle-checkbox {
        display: none;
    }

    .toggle-label {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        cursor: pointer;
        width: 100%;
    }

    .toggle-switch {
        position: relative;
        width: 48px;
        height: 26px;
        background: var(--clr-border);
        border-radius: 13px;
        transition: all 0.3s;
        flex-shrink: 0;
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        background: var(--clr-card);
        border-radius: 50%;
        top: 3px;
        left: 3px;
        transition: all 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .toggle-checkbox:checked + .toggle-label .toggle-switch {
        background: var(--clr-primary);
    }

    .toggle-checkbox:checked + .toggle-label .toggle-switch::after {
        transform: translateX(22px);
    }

    .toggle-text {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .toggle-text strong {
        color: var(--clr-text);
        font-size: 0.95rem;
    }

    .toggle-text small {
        color: var(--clr-text-muted);
        font-size: 0.8rem;
    }

    /* Form Footer */
    .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.875rem;
        padding: 1.75rem 2rem;
        background: var(--clr-bg);
        border-top: 1px solid var(--clr-border);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.75rem;
        border-radius: var(--radius);
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: none;
    }

    .btn-primary {
        background: var(--clr-primary);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .btn-primary:hover {
        background: var(--clr-primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
    }

    .btn-secondary {
        background: var(--clr-card);
        color: var(--clr-text-muted);
        border: 2px solid var(--clr-border);
    }

    .btn-secondary:hover {
        background: var(--clr-bg);
        color: var(--clr-text);
        border-color: var(--clr-text-muted);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .party-form-wrapper {
            padding: 1rem;
        }

        .form-header {
            padding: 1.25rem 1.5rem;
        }

        .field-group {
            padding: 1.5rem;
        }

        .fields-row {
            grid-template-columns: 1fr;
        }

        .field-col-sm {
            min-width: 100%;
        }

        .form-footer {
            flex-direction: column-reverse;
            padding: 1.25rem 1.5rem;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .form-title {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .group-header {
            font-size: 1rem;
        }
    }
</style>