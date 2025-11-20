@extends('layouts.admin')

@section('title', 'Create New Order')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="page-title mb-1">
                        <i class="bi bi-cart-plus me-2"></i>
                        Create New Order
                    </h2>
                    <p class="page-subtitle mb-0">Fill the details below to create a new order entry</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back again to Orders
                    </a>
                    <button type="submit" form="orderForm" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Create Order
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        @if ($errors->any())
            <div class="alert-card danger mb-4">
                <div class="alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="alert-content">
                    <h5 class="alert-title">Please Correct the Following Errors</h5>
                    <ul class="error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('orders.store') }}" method="POST" enctype="multipart/form-data" id="orderForm">
            @csrf

            <!-- Order Type Selection Card -->
            <div class="form-section-card mb-4">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-collection-fill"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Select Order Type</h5>
                            <p class="section-description">Choose the type of order you want to create</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="order-type-selector">
                        <div class="order-type-card" data-type="ready_to_ship">
                            <input type="radio" name="order_type" id="type_ready" value="ready_to_ship"
                                class="order-type-input" required>
                            <label for="type_ready" class="order-type-label">
                                <div class="order-type-icon">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="order-type-content">
                                    <h6 class="order-type-title">Ready to Ship</h6>
                                    <p class="order-type-desc">Pre-made products ready for delivery</p>
                                </div>
                                <div class="order-type-check">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            </label>
                        </div>

                        <div class="order-type-card" data-type="custom_diamond">
                            <input type="radio" name="order_type" id="type_diamond" value="custom_diamond"
                                class="order-type-input" required>
                            <label for="type_diamond" class="order-type-label">
                                <div class="order-type-icon">
                                    <i class="bi bi-gem"></i>
                                </div>
                                <div class="order-type-content">
                                    <h6 class="order-type-title">Custom Diamond</h6>
                                    <p class="order-type-desc">Custom diamond selection and settings</p>
                                </div>
                                <div class="order-type-check">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            </label>
                        </div>

                        <div class="order-type-card" data-type="custom_jewellery">
                            <input type="radio" name="order_type" id="type_jewellery" value="custom_jewellery"
                                class="order-type-input" required>
                            <label for="type_jewellery" class="order-type-label">
                                <div class="order-type-icon">
                                    <i class="bi bi-hammer"></i>
                                </div>
                                <div class="order-type-content">
                                    <h6 class="order-type-title">Custom Jewellery</h6>
                                    <p class="order-type-desc">Bespoke jewellery designs</p>
                                </div>
                                <div class="order-type-check">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Form Container -->
            <div id="orderFormFields">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-cursor"></i>
                    </div>
                    <h5 class="empty-state-title">Select an Order Type</h5>
                    <p class="empty-state-text">Choose an order type above to load the order details form</p>
                </div>
            </div>


        </form>
    </div>

    @push('styles')
        <style>
            :root {
                --primary: #6366f1;
                --primary-dark: #4f46e5;
                --dark: #1e293b;
                --gray: #64748b;
                --light-gray: #f8fafc;
                --border: #e2e8f0;
                --danger: #ef4444;
                --success: #10b981;
                --warning: #f59e0b;
            }

            /* Page Header */
            .page-header {
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
                padding: 2rem;
                border-radius: 16px;
                border: 2px solid rgba(99, 102, 241, 0.1);
            }

            .page-title {
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
            }

            .page-subtitle {
                color: var(--gray);
                font-size: 0.95rem;
            }

            /* Alert Card */
            .alert-card {
                background: white;
                border-radius: 16px;
                padding: 1.5rem;
                display: flex;
                gap: 1.25rem;
                border: 2px solid var(--border);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            }

            .alert-card.danger {
                background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05));
                border-color: rgba(239, 68, 68, 0.2);
            }

            .alert-card.danger .alert-icon {
                background: linear-gradient(135deg, var(--danger), #dc2626);
            }

            .alert-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                flex-shrink: 0;
            }

            .alert-content {
                flex: 1;
            }

            .alert-title {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0 0 0.75rem 0;
            }

            .error-list {
                margin: 0;
                padding-left: 1.25rem;
                color: var(--dark);
                font-size: 0.9rem;
                line-height: 1.8;
            }

            .error-list li {
                margin-bottom: 0.5rem;
            }

            /* Form Section Card */
            .form-section-card {
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .form-section-card:hover {
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            }

            .section-header {
                padding: 1.5rem;
                background: linear-gradient(135deg, var(--light-gray), white);
                border-bottom: 2px solid var(--border);
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .section-info {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .section-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                flex-shrink: 0;
            }

            .section-title {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
            }

            .section-description {
                font-size: 0.875rem;
                color: var(--gray);
                margin: 0.25rem 0 0;
            }

            .section-body {
                padding: 2rem;
            }

            /* Order Type Selector */
            .order-type-selector {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
            }

            .order-type-card {
                position: relative;
            }

            .order-type-input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            .order-type-label {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                padding: 1.5rem;
                border: 2px solid var(--border);
                border-radius: 16px;
                background: white;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                height: 100%;
            }

            .order-type-label:hover {
                border-color: var(--primary);
                background: rgba(99, 102, 241, 0.02);
                transform: translateY(-4px);
                box-shadow: 0 8px 24px rgba(99, 102, 241, 0.15);
            }

            .order-type-input:checked+.order-type-label {
                border-color: var(--primary);
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
                box-shadow: 0 8px 24px rgba(99, 102, 241, 0.2);
            }

            .order-type-icon {
                width: 64px;
                height: 64px;
                border-radius: 16px;
                background: linear-gradient(135deg, var(--light-gray), #e2e8f0);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                color: var(--gray);
                transition: all 0.3s ease;
            }

            .order-type-input:checked+.order-type-label .order-type-icon {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                transform: scale(1.05);
            }

            .order-type-content {
                flex: 1;
            }

            .order-type-title {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0 0 0.5rem 0;
            }

            .order-type-desc {
                font-size: 0.875rem;
                color: var(--gray);
                margin: 0;
                line-height: 1.5;
            }

            .order-type-check {
                position: absolute;
                top: 1rem;
                right: 1rem;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                background: var(--border);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1rem;
                transition: all 0.3s ease;
                opacity: 0;
            }

            .order-type-input:checked+.order-type-label .order-type-check {
                background: var(--success);
                opacity: 1;
                transform: scale(1.1);
            }

            /* Empty State */
            .empty-state {
                padding: 4rem 2rem;
                text-align: center;
                background: var(--light-gray);
                border-radius: 16px;
                border: 2px dashed var(--border);
            }

            .empty-state-icon {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                background: white;
                margin: 0 auto 1.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2.5rem;
                color: var(--gray);
                border: 2px solid var(--border);
            }

            .empty-state-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0 0 0.5rem 0;
            }

            .empty-state-text {
                font-size: 0.95rem;
                color: var(--gray);
                margin: 0;
            }

            /* Loading State */
            .loading-state {
                padding: 4rem 2rem;
                text-align: center;
            }

            .loading-spinner {
                width: 60px;
                height: 60px;
                border: 4px solid var(--border);
                border-top-color: var(--primary);
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 1.5rem;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            .loading-text {
                font-size: 1rem;
                color: var(--gray);
                font-weight: 600;
            }

            /* Action Footer */
            .action-footer {
                margin-top: 2rem;
                padding: 1.5rem 2rem;
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 1rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            }

            .action-footer .btn-lg {
                padding: 0.875rem 2rem;
                font-weight: 600;
                border-radius: 12px;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .page-header {
                    padding: 1.5rem;
                }

                .section-body {
                    padding: 1.5rem;
                }

                .order-type-selector {
                    grid-template-columns: 1fr;
                }

                .action-footer {
                    flex-direction: column;
                }

                .action-footer .btn-lg {
                    width: 100%;
                }

                .alert-card {
                    flex-direction: column;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                const orderTypeInputs = document.querySelectorAll('input[name="order_type"]');
                const formContainer = document.getElementById('orderFormFields');
                const submitButton = document.getElementById('submitButtonContainer');
                let currentType = '';

                orderTypeInputs.forEach(input => {
                    input.addEventListener('change', function () {
                        if (this.checked) {
                            currentType = this.value;
                            loadOrderForm(currentType);
                        }
                    });
                });

                function loadOrderForm(type) {
                    // Show loading state
                    formContainer.innerHTML = `
                                                        <div class="loading-state">
                                                            <div class="loading-spinner"></div>
                                                            <p class="loading-text">Loading order form...</p>
                                                        </div>
                                                    `;

                    // Fetch form
                    fetch(`/admin/orders/form/${type}`)
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.text();
                        })
                        .then(html => {
                            formContainer.innerHTML = html;

                            // Execute any scripts included in the returned partial so
                            // event listeners (file previews, drag/drop handlers) run.
                            // When inserting HTML via innerHTML, <script> tags are not
                            // executed by default, so we find them and append to body.
                            try {
                                const temp = document.createElement('div');
                                temp.innerHTML = html;
                                const scripts = temp.querySelectorAll('script');
                                scripts.forEach(s => {
                                    const newScript = document.createElement('script');
                                    if (s.src) {
                                        newScript.src = s.src;
                                        // preserve execution order
                                        newScript.async = false;
                                    } else {
                                        newScript.textContent = s.innerHTML;
                                    }
                                    document.body.appendChild(newScript);
                                });
                            } catch (e) {
                                // If script parsing fails, log for debugging but don't break UX
                                console.error('Error evaluating returned scripts:', e);
                            }
                            if (submitButton) submitButton.style.display = 'flex';

                            // Smooth scroll to form
                            setTimeout(() => {
                                formContainer.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }, 100);

                            showToastNotification('Order form loaded successfully', 'success');
                        })
                        .catch(error => {
                            console.error('Error loading form:', error);
                            formContainer.innerHTML = `
                                                                <div class="alert-card danger">
                                                                    <div class="alert-icon">
                                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                                    </div>
                                                                    <div class="alert-content">
                                                                        <h5 class="alert-title">Error Loading Form</h5>
                                                                        <p class="mb-0">Unable to load the order form. Please try again or contact support if the problem persists.</p>
                                                                    </div>
                                                                </div>
                                                            `;
                            if (submitButton) submitButton.style.display = 'none';
                            showToastNotification('Failed to load order form', 'error');
                        });
                }

                function showToastNotification(message, type) {
                    if (typeof showToast === 'function') {
                        showToast(message);
                        return;
                    }

                    const colors = {
                        success: '#10b981',
                        error: '#ef4444',
                        info: '#3b82f6'
                    };

                    const toast = document.createElement('div');
                    toast.style.cssText = 'position:fixed;top:20px;right:20px;background:' +
                        (colors[type] || colors.info) +
                        ';color:white;padding:1rem 1.5rem;border-radius:12px;' +
                        'box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;' +
                        'font-weight:600;animation:slideIn 0.3s ease;max-width:350px;';
                    toast.textContent = message;
                    document.body.appendChild(toast);

                    setTimeout(function () {
                        toast.style.animation = 'slideOut 0.3s ease';
                        setTimeout(function () {
                            if (toast.parentNode) document.body.removeChild(toast);
                        }, 300);
                    }, 3000);
                }
            })();
        </script>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }

                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        </style>
    @endpush
@endsection