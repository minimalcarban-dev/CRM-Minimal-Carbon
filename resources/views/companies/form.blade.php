<div class="form-grid">
    <!-- SECTION 1: Company Identification -->
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-building"></i>
            <h3>Company Identification</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-building"></i>
                Company Name
                <span class="required">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', optional($company)->name ?? '') }}"
                class="form-input @error('name') error @enderror" placeholder="Enter company legal name" required>
            @error('name')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-image"></i>
                Company Logo
            </label>
            <div class="logo-upload-container">
                <input type="file" name="logo" accept="image/*" id="logoInput"
                    class="form-input @error('logo') error @enderror">

                <!-- Logo Preview -->
                <div id="logoPreview" class="logo-preview" style="display: none; margin-top: 15px;">
                    <div class="logo-preview-box">
                        <img id="logoPreviewImg" src="" alt="Logo Preview" class="logo-preview-img">
                    </div>
                    <small class="text-muted" style="display: block; margin-top: 8px;">New logo preview</small>
                </div>

                <!-- Current Logo -->
                @if(optional($company)->logo)
                    <div id="currentLogo" class="logo-preview" style="margin-top: 15px;">
                        <div class="logo-preview-box">
                            <img src="{{ str_starts_with($company->logo, 'http') ? $company->logo : asset($company->logo) }}"
                                alt="{{ $company->name }}" class="logo-preview-img">
                        </div>
                        <small class="text-muted" style="display: block; margin-top: 8px;">Current logo</small>
                    </div>
                @endif
            </div>
            @error('logo')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror

        </div>


    </div>

    <!-- SECTION 2: Tax & Regulatory Information -->
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-receipt"></i>
            <h3>Tax & Regulatory Information</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-receipt"></i>
                GST Number
            </label>
            <input type="text" name="gst_no" value="{{ old('gst_no', optional($company)->gst_no ?? '') }}"
                class="form-input @error('gst_no') error @enderror" placeholder="e.g., 27AABCT1234H1Z0">
            @error('gst_no')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-hash"></i>
                EIN/CIN Number
            </label>
            <input type="text" name="ein_cin_no" value="{{ old('ein_cin_no', optional($company)->ein_cin_no ?? '') }}"
                class="form-input @error('ein_cin_no') error @enderror"
                placeholder="Enter corporate identification number">
            @error('ein_cin_no')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-code-square"></i>
                State Code
            </label>
            <input type="text" name="state_code" value="{{ old('state_code', optional($company)->state_code ?? '') }}"
                class="form-input @error('state_code') error @enderror" placeholder="e.g., 27 for Maharashtra">
            @error('state_code')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <!-- SECTION 3: Contact Information -->
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-telephone"></i>
            <h3>Contact Information</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-envelope"></i>
                Email Address
            </label>
            <input type="email" name="email" value="{{ old('email', optional($company)->email ?? '') }}"
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
            <input type="text" name="phone" value="{{ old('phone', optional($company)->phone ?? '') }}"
                class="form-input @error('phone') error @enderror" placeholder="+1 (555) 000-0000">
            @error('phone')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <!-- SECTION 4: Location & Address -->
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-geo-alt-fill"></i>
            <h3>Location & Address</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-pin-map"></i>
                Address
            </label>
            <textarea name="address" class="form-textarea @error('address') error @enderror" rows="4"
                placeholder="Enter company's complete postal address">{{ old('address', optional($company)->address ?? '') }}</textarea>
            @error('address')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-globe"></i>
                Country
            </label>
            <input type="text" name="country" value="{{ old('country', optional($company)->country ?? '') }}"
                class="form-input @error('country') error @enderror" placeholder="e.g., India">
            @error('country')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-currency-exchange"></i>
                Currency
            </label>
            <select name="currency" id="currencySelect" class="form-input @error('currency') error @enderror">
                <option value="INR" {{ old('currency', optional($company)->currency ?? 'INR') == 'INR' ? 'selected' : '' }}>₹ INR (Indian Rupee)</option>
                <option value="USD" {{ old('currency', optional($company)->currency ?? '') == 'USD' ? 'selected' : '' }}>$
                    USD (US Dollar)</option>
                <option value="GBP" {{ old('currency', optional($company)->currency ?? '') == 'GBP' ? 'selected' : '' }}>£
                    GBP (British Pound)</option>
                <option value="EUR" {{ old('currency', optional($company)->currency ?? '') == 'EUR' ? 'selected' : '' }}>€
                    EUR (Euro)</option>
            </select>
            <small class="text-muted" style="display: block; margin-top: 5px;">
                <i class="bi bi-info-circle"></i> Currency auto-selects based on country
            </small>
            @error('currency')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <!-- SECTION 5: Bank Account Details (Primary) -->
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-bank2"></i>
            <h3>Bank Account Details</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-bank2"></i>
                Bank Name
            </label>
            <input type="text" name="bank_name" value="{{ old('bank_name', optional($company)->bank_name ?? '') }}"
                class="form-input @error('bank_name') error @enderror" placeholder="e.g., State Bank of India">
            @error('bank_name')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-person-fill"></i>
                Account Holder Name
            </label>
            <input type="text" name="account_holder_name"
                value="{{ old('account_holder_name', optional($company)->account_holder_name ?? '') }}"
                class="form-input @error('account_holder_name') error @enderror" placeholder="Name as per bank records">
            @error('account_holder_name')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-credit-card"></i>
                Account Number
            </label>
            <input type="text" name="account_no" value="{{ old('account_no', optional($company)->account_no ?? '') }}"
                class="form-input @error('account_no') error @enderror" placeholder="e.g., 0123456789012345">
            @error('account_no')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-code"></i>
                IFSC Code
            </label>
            <input type="text" name="ifsc_code" value="{{ old('ifsc_code', optional($company)->ifsc_code ?? '') }}"
                class="form-input @error('ifsc_code') error @enderror" placeholder="e.g., SBIN0001234">
            @error('ifsc_code')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <!-- SECTION 6: International Bank Details -->
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-lightning"></i>
            <h3>International Bank Details</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-hash"></i>
                IBAN (International Bank Account Number)
            </label>
            <input type="text" name="iban" value="{{ old('iban', optional($company)->iban ?? '') }}"
                class="form-input @error('iban') error @enderror" placeholder="e.g., DE89370400440532013000">
            @error('iban')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-lightning"></i>
                SWIFT Code
            </label>
            <input type="text" name="swift_code" value="{{ old('swift_code', optional($company)->swift_code ?? '') }}"
                class="form-input @error('swift_code') error @enderror" placeholder="e.g., DEUTDEDBBER">
            @error('swift_code')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-sort-down"></i>
                Sort Code (UK Banks)
            </label>
            <input type="text" name="sort_code" value="{{ old('sort_code', optional($company)->sort_code ?? '') }}"
                class="form-input @error('sort_code') error @enderror" placeholder="e.g., 20-50-80">
            @error('sort_code')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-code"></i>
                AD Code (RBI Codes)
            </label>
            <input type="text" name="ad_code" value="{{ old('ad_code', optional($company)->ad_code ?? '') }}"
                class="form-input @error('ad_code') error @enderror" placeholder="Authorised Dealer Code">
            @error('ad_code')
                <span class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <!-- SECTION 7: US Bank Details (Wire Transfer) -->
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-bank"></i>
            <h3>US Bank Details (Wire Transfer)</h3>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-person-fill"></i>
                    Beneficiary Name
                </label>
                <input type="text" name="beneficiary_name"
                    value="{{ old('beneficiary_name', optional($company)->beneficiary_name ?? '') }}"
                    class="form-input @error('beneficiary_name') error @enderror"
                    placeholder="Wire transfer beneficiary name">
                @error('beneficiary_name')
                    <span class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-hash"></i>
                    ABA Routing Number
                </label>
                <input type="text" name="aba_routing_number"
                    value="{{ old('aba_routing_number', optional($company)->aba_routing_number ?? '') }}"
                    class="form-input @error('aba_routing_number') error @enderror" placeholder="9-digit routing number"
                    maxlength="9">
                @error('aba_routing_number')
                    <span class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-credit-card"></i>
                    A/C Number (US)
                </label>
                <input type="text" name="us_account_no"
                    value="{{ old('us_account_no', optional($company)->us_account_no ?? '') }}"
                    class="form-input @error('us_account_no') error @enderror" placeholder="US bank account number">
                @error('us_account_no')
                    <span class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-list"></i>
                    A/C Type
                </label>
                <select name="account_type" class="form-input @error('account_type') error @enderror">
                    <option value="">Select Account Type</option>
                    <option value="checking" {{ old('account_type', optional($company)->account_type ?? '') == 'checking' ? 'selected' : '' }}>Checking</option>
                    <option value="savings" {{ old('account_type', optional($company)->account_type ?? '') == 'savings' ? 'selected' : '' }}>Savings</option>
                </select>
                @error('account_type')
                    <span class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-geo-alt"></i>
                    Beneficiary Address
                </label>
                <textarea name="beneficiary_address" class="form-textarea @error('beneficiary_address') error @enderror"
                    rows="3"
                    placeholder="Full beneficiary address for wire transfers">{{ old('beneficiary_address', optional($company)->beneficiary_address ?? '') }}</textarea>
                @error('beneficiary_address')
                    <span class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $message }}
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <!-- SECTION 8: Status -->
    <div class="form-section">
        <div class="section-header">
            <i class="bi bi-toggle-on"></i>
            <h3>Status</h3>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-toggle-on"></i>
                Status
            </label>
            <div class="status-toggle-group">
                <label
                    class="status-toggle {{ (old('status', optional($company)->status ?? 'active') == 'active') ? 'active' : '' }}">
                    <input type="radio" name="status" value="active" {{ (old('status', optional($company)->status ?? 'active') == 'active') ? 'checked' : '' }}>
                    <span class="toggle-indicator active">
                        <i class="bi bi-check-circle-fill"></i>
                        Active
                    </span>
                </label>
                <label
                    class="status-toggle {{ (old('status', optional($company)->status ?? '') == 'inactive') ? 'active' : '' }}">
                    <input type="radio" name="status" value="inactive" {{ (old('status', optional($company)->status ?? '') == 'inactive') ? 'checked' : '' }}>
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



    <style>
        .logo-upload-container {
            position: relative;
        }

        .logo-preview {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-preview-box {
            display: inline-block;
            width: 120px;
            height: 120px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoInput = document.getElementById('logoInput');
            const logoPreview = document.getElementById('logoPreview');
            const logoPreviewImg = document.getElementById('logoPreviewImg');

            if (logoInput) {
                logoInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];

                    if (file) {
                        // Check file type
                        if (!file.type.startsWith('image/')) {
                            alert('Please select a valid image file');
                            this.value = '';
                            logoPreview.style.display = 'none';
                            return;
                        }

                        // Check file size (max 2MB)
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File size should not exceed 2MB');
                            this.value = '';
                            logoPreview.style.display = 'none';
                            return;
                        }

                        // Create preview
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            logoPreviewImg.src = e.target.result;
                            logoPreview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        logoPreview.style.display = 'none';
                    }
                });
            }

            // Auto-select currency based on country
            const countryInput = document.querySelector('[name="country"]');
            const currencySelect = document.getElementById('currencySelect');

            if (countryInput && currencySelect) {
                countryInput.addEventListener('input', function (e) {
                    const country = e.target.value.toLowerCase();

                    if (country.includes('us') || country.includes('usa') || country.includes('united states') || country.includes('america')) {
                        currencySelect.value = 'USD';
                    } else if (country.includes('uk') || country.includes('united kingdom') || country.includes('britain') || country.includes('england')) {
                        currencySelect.value = 'GBP';
                    } else if (country.includes('india')) {
                        currencySelect.value = 'INR';
                    } else if (country.includes('euro') || country.includes('germany') || country.includes('france') || country.includes('italy') || country.includes('spain')) {
                        currencySelect.value = 'EUR';
                    }
                });
            }
        });
    </script>