{{-- Multi-SKU Selector Component --}}
{{-- This component allows selecting multiple diamond/jewellery SKUs with real-time validation --}}
{{-- Each SKU is displayed as a removable pill with item details --}}

<div class="form-group-modern">
    <label class="form-label-modern">
        <span class="label-content">
            <span class="label-icon"><i class="bi bi-tag"></i></span>
            <span class="label-text">Diamond / Jewellery SKU</span>
        </span>
        <span class="optional-badge">Optional</span>
    </label>

    {{-- Hidden inputs for form submission --}}
    @php
        // Get SKUs from new array field, or fallback to legacy single SKU field
        $existingSkus = $order->diamond_skus ?? [];
        if (empty($existingSkus) && !empty($order->diamond_sku)) {
            $existingSkus = [$order->diamond_sku];
        }
    @endphp
    <input type="hidden" name="diamond_skus_json" id="diamond_skus_json"
        value="{{ old('diamond_skus_json', json_encode($existingSkus)) }}">

    {{-- SKU Pills Container - hidden by default, shown when pills added --}}
    <div class="sku-pills-container" id="sku_pills_container"></div>

    {{-- SKU Input with Validation --}}
    <div class="sku-input-wrapper">
        <input type="text" id="diamond_sku_input" class="form-control-modern"
            placeholder="Enter diamond or jewellery SKU (e.g., D-12345, J-1001)" autocomplete="off">
        <span class="sku-validation-icon" id="sku_validation_icon"></span>
    </div>
    <div class="sku-validation-message" id="sku_validation_message"></div>
</div>

<style>
    /* Form label styling - ensures badge alignment to right */
    .form-group-modern .form-label-modern {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--dark, #1e293b);
        font-size: 0.95rem;
    }

    .form-group-modern .label-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group-modern .label-icon {
        display: flex;
        align-items: center;
        color: var(--primary, #6366f1);
        font-size: 1rem;
    }

    .form-group-modern .label-text {
        font-weight: 600;
    }

    .form-group-modern .optional-badge {
        background: linear-gradient(135deg, #64748b, #475569);
        color: white;
        font-size: 0.625rem;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        margin-left: auto;
    }

    /* Multi-SKU Pills Container */
    .sku-pills-container {
        display: none;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .sku-pills-container.has-pills {
        display: flex;
    }

    /* Professional Compact SKU Pill */
    .sku-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 50px;
        font-size: 0.8125rem;
        transition: all 0.25s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .sku-pill:hover {
        border-color: #6366f1;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.12);
        transform: translateY(-1px);
    }

    /* Loading State - Sleek animated pill */
    .sku-pill.loading {
        background: linear-gradient(90deg, #e2e8f0 0%, #f1f5f9 50%, #e2e8f0 100%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite linear;
        border-color: #cbd5e1;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .sku-pill.error {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-color: #fca5a5;
    }

    /* SKU Badge */
    .sku-pill-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sku-pill-sku {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 5px 10px;
        color: #5d5ced;
        background: #f8f9ff;
        border: 1px solid #e0e7ff;
        border-radius: 24px;
        /* Rounded pill shape like SKU */
        font-size: 0.8125rem;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        flex-wrap: nowrap;
        min-width: 0;
        font-weight: 700;
    }

    .sku-pill.loading .sku-pill-sku {
        color: #eef2ff;
        background: rgba(100, 116, 139, 0.1);
    }

    .sku-pill-details {
        font-size: 0.7rem;
        color: #eef2ff;
        font-weight: 500;
        white-space: nowrap;
    }

    .sku-pill.loading .sku-pill-details {
        color: #94a3b8;
    }

    /* Spinner for loading state */
    .sku-pill.loading .sku-pill-details::before {
        content: '';
        display: inline-block;
        width: 10px;
        height: 10px;
        border: 2px solid #cbd5e1;
        border-top-color: #6366f1;
        border-radius: 50%;
        margin-right: 6px;
        animation: spin 0.8s linear infinite;
        vertical-align: middle;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Price Input - Compact inline style */
    .diamond-price-wrapper {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.15rem 0.35rem;
        transition: all 0.2s ease;
    }

    .diamond-price-wrapper:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }

    .price-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
    }

    .diamond-price-input {
        width: 60px;
        padding: 0.2rem 0.25rem;
        border: none;
        background: transparent;
        font-size: 0.8rem;
        font-weight: 600;
        color: #1f2937;
        text-align: right;
    }

    .diamond-price-input:focus {
        outline: none;
    }

    .diamond-price-input::placeholder {
        color: #9ca3af;
        font-weight: 400;
    }

    /* Remove button - Minimal style */
    .sku-pill-remove {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: transparent;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        padding: 0;
        margin-left: 0.25rem;
    }

    .sku-pill-remove:hover {
        background: #fee2e2;
        color: #ef4444;
        transform: scale(1.1);
    }

    @keyframes pillFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-4px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Validation styles (reuse existing) */
    .sku-input-wrapper {
        position: relative;
    }

    .sku-input-wrapper .form-control-modern {
        padding-right: 2.75rem;
    }

    .sku-validation-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.25rem;
        line-height: 1;
    }

    .sku-validation-message {
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        min-height: 1.25rem;
    }

    .form-control-modern.sku-valid {
        border-color: var(--success, #10b981) !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .form-control-modern.sku-invalid {
        border-color: var(--danger, #ef4444) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .sku-spin {
        animation: skuSpin 1s linear infinite;
        color: var(--primary, #6366f1);
    }

    @keyframes skuSpin {
        100% {
            transform: translateY(-50%) rotate(360deg);
        }
    }
</style>

<script>
    (function () {
        'use strict';

        const stockSkuCheckUrl = "{{ route('orders.check-stock-sku') }}";

        // Multi-SKU Manager
        const MultiSkuManager = {
            skus: [], // Array of {sku, stockItem, itemType} objects
            container: null,
            input: null,
            validationIcon: null,
            validationMessage: null,
            hiddenInput: null,
            debounceTimer: null,

            init: function () {
                this.container = document.getElementById('sku_pills_container');
                this.input = document.getElementById('diamond_sku_input');
                this.validationIcon = document.getElementById('sku_validation_icon');
                this.validationMessage = document.getElementById('sku_validation_message');
                this.hiddenInput = document.getElementById('diamond_skus_json');

                if (!this.container || !this.input) return;

                // Load existing SKUs from hidden input (for edit mode)
                this.loadExistingSkus();

                // Bind events
                this.input.addEventListener('keydown', this.handleKeydown.bind(this));
                this.input.addEventListener('input', this.handleInput.bind(this));
            },

            loadExistingSkus: function () {
                if (!this.hiddenInput || !this.hiddenInput.value) return;

                try {
                    const existingSkus = JSON.parse(this.hiddenInput.value);
                    if (Array.isArray(existingSkus)) {
                        existingSkus.forEach(sku => {
                            if (sku && typeof sku === 'string') {
                                // Validate each existing SKU
                                this.validateAndAddSku(sku, true);
                            }
                        });
                    }
                } catch (e) {
                    console.error('Error loading existing SKUs:', e);
                }
            },

            handleKeydown: function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const sku = this.input.value.trim().toUpperCase();
                    if (sku) {
                        this.validateAndAddSku(sku, false);
                    }
                }
            },

            handleInput: function () {
                const sku = this.input.value.trim();

                // Clear previous validation
                clearTimeout(this.debounceTimer);
                this.validationIcon.innerHTML = '';
                this.validationMessage.innerHTML = '';
                this.input.classList.remove('sku-valid', 'sku-invalid');

                if (!sku) return;

                // Show loading spinner
                this.validationIcon.innerHTML = '<i class="bi bi-arrow-repeat sku-spin"></i>';

                // Debounce: wait 500ms after user stops typing
                this.debounceTimer = setTimeout(() => {
                    this.checkSkuAvailability(sku);
                }, 500);
            },

            checkSkuAvailability: function (sku) {
                fetch(`${stockSkuCheckUrl}?sku=${encodeURIComponent(sku)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.available) {
                            this.input.classList.add('sku-valid');
                            this.validationIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                            const item = data.item || data.diamond || {};
                            const details = item.display_details ? ` - ${item.display_details}` : '';
                            const kind = data.type ? ` (${data.type})` : '';
                            this.validationMessage.innerHTML = `<span class="text-success">✓ ${data.message}${kind}${details} - Press Enter to add</span>`;
                        } else {
                            this.input.classList.add('sku-invalid');
                            this.validationIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                            this.validationMessage.innerHTML = `<span class="text-danger">✗ ${data.message}</span>`;
                        }
                    })
                    .catch(err => {
                        console.error('SKU validation error:', err);
                        this.validationIcon.innerHTML = '';
                        this.validationMessage.innerHTML = '<span class="text-danger">✗ Error checking SKU</span>';
                    });
            },

            validateAndAddSku: function (sku, isInitialLoad) {
                // Check for duplicates
                if (this.skus.some(s => s.sku === sku)) {
                    if (!isInitialLoad) {
                        this.showNotification('This SKU is already added', 'warning');
                    }
                    return;
                }

                // Add placeholder pill with loading state
                const tempId = 'temp_' + Date.now();
                if (!isInitialLoad) {
                    this.addPill({ sku: sku, loading: true, tempId: tempId });
                }

                // Validate SKU via API
                fetch(`${stockSkuCheckUrl}?sku=${encodeURIComponent(sku)}`)
                    .then(res => res.json())
                    .then(data => {
                        // Remove temp pill
                        this.removePillByTempId(tempId);

                        if (data.available) {
                            const item = data.item || data.diamond || {};
                            this.skus.push({ sku: sku, stockItem: item, itemType: data.type || null });
                            this.addPill({ sku: sku, stockItem: item, itemType: data.type || null });
                            this.updateHiddenInput();

                            if (!isInitialLoad) {
                                this.input.value = '';
                                this.clearValidation();
                                this.showNotification(`Added ${sku}${data.type ? ' (' + data.type + ')' : ''}`, 'success');
                            }
                        } else {
                            if (!isInitialLoad) {
                                this.showNotification(data.message || 'SKU not available', 'error');
                            }
                        }
                    })
                    .catch(err => {
                        this.removePillByTempId(tempId);
                        console.error('Error validating SKU:', err);
                        if (!isInitialLoad) {
                            this.showNotification('Error validating SKU', 'error');
                        }
                    });
            },

            addPill: function (data) {
                const pill = document.createElement('div');
                pill.className = 'sku-pill' + (data.loading ? ' loading' : '');
                pill.dataset.sku = data.sku;
                if (data.tempId) pill.dataset.tempId = data.tempId;

                let detailsHtml = '';
                let priceInputHtml = '';

                if (data.stockItem) {
                    const item = data.stockItem;
                    const details = item.display_details || `${item.carat || '?'}ct ${item.shape || ''}`;
                    detailsHtml = `<span class="sku-pill-details">${details}</span>`;
                    // Add price input with wrapper for better styling
                    priceInputHtml = `
                        <div class="diamond-price-wrapper">
                            <span class="price-label">$</span>
                            <input type="number" step="0.01" min="0" 
                                   class="diamond-price-input" 
                                   name="diamond_prices[${data.sku}]"
                                   data-sku="${data.sku}"
                                   placeholder="0.00"
                                   value="${data.price || ''}"
                                   title="Sold price ($)">
                        </div>
                    `;
                } else if (data.loading) {
                    detailsHtml = '<span class="sku-pill-details">Verifying...</span>';
                }

                pill.innerHTML = `
                <div class="sku-pill-content">
                    <span class="sku-pill-sku">${data.sku}</span>
                    ${detailsHtml}
                </div>
                ${priceInputHtml}
                <button type="button" class="sku-pill-remove" title="Remove">
                    <i class="bi bi-x"></i>
                </button>
            `;

                // Bind remove handler
                pill.querySelector('.sku-pill-remove').addEventListener('click', () => {
                    this.removeSku(data.sku);
                });

                this.container.appendChild(pill);
                this.updateContainerVisibility();
            },

            updateContainerVisibility: function () {
                // Show container only when it has pills
                if (this.container.children.length > 0) {
                    this.container.classList.add('has-pills');
                } else {
                    this.container.classList.remove('has-pills');
                }
            },

            removePillByTempId: function (tempId) {
                const pill = this.container.querySelector(`[data-temp-id="${tempId}"]`);
                if (pill) pill.remove();
            },

            removeSku: function (sku) {
                this.skus = this.skus.filter(s => s.sku !== sku);
                const pill = this.container.querySelector(`[data-sku="${sku}"]`);
                if (pill) {
                    pill.style.animation = 'pillFadeIn 0.2s ease reverse';
                    setTimeout(() => {
                        pill.remove();
                        this.updateContainerVisibility();
                    }, 200);
                }
                this.updateHiddenInput();
                this.showNotification(`Removed ${sku}`, 'info');
            },

            updateHiddenInput: function () {
                const skuArray = this.skus.map(s => s.sku);
                this.hiddenInput.value = JSON.stringify(skuArray);

                // Also create individual hidden inputs for form submission compatibility
                const form = this.hiddenInput.closest('form');
                if (!form) return;

                // Remove existing diamond_skus[] inputs
                form.querySelectorAll('input[name="diamond_skus[]"]').forEach(el => el.remove());

                // Add new ones
                skuArray.forEach(sku => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'diamond_skus[]';
                    input.value = sku;
                    form.appendChild(input);
                });
            },

            clearValidation: function () {
                this.validationIcon.innerHTML = '';
                this.validationMessage.innerHTML = '';
                this.input.classList.remove('sku-valid', 'sku-invalid');
            },

            showNotification: function (message, type) {
                const colors = {
                    success: '#10b981',
                    error: '#ef4444',
                    warning: '#f59e0b',
                    info: '#3b82f6'
                };

                const notification = document.createElement('div');
                notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${colors[type] || colors.info};
                color: white;
                padding: 0.75rem 1.25rem;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                font-weight: 600;
                font-size: 0.875rem;
                animation: slideIn 0.3s ease;
            `;
                notification.textContent = message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }, 2000);
            }
        };

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => MultiSkuManager.init());
        } else {
            MultiSkuManager.init();
        }

        // Expose for external initialization (when form is loaded via AJAX)
        window.initMultiSkuManager = function () {
            MultiSkuManager.skus = [];
            MultiSkuManager.init();
        };
    })();
</script>