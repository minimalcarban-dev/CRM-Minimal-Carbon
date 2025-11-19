@extends('layouts.admin')

@section('title', 'Edit Order')

@section('content')
    <!-- Page Header -->
    <div class="page-header-wrapper">
        <div class="page-header-content">
            <div class="header-left">
                <div class="header-title-section">
                    <h1 class="page-main-title">Edit Order #{{ $order->id }}</h1>
                    <div class="order-type-badge {{ $order->order_type }}">
                        @if($order->order_type == 'ready_to_ship')
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                            </svg>
                            Ready to Ship
                        @elseif($order->order_type == 'custom_diamond')
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                            Custom Diamond
                        @else
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="6" />
                                <path d="M12 14v8m-4-4h8" />
                            </svg>
                            Custom Jewellery
                        @endif
                    </div>
                </div>
            </div>
            <div class="header-right">
                <a href="{{ route('orders.index') }}" class="btn-back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    <span>Back to Orders</span>
                </a>
                <button type="button" onclick="document.getElementById('editOrderForm').submit()" class="btn-update-order">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    <span>Update Order</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Currently Uploaded Files Section -->
        @if($order->images || $order->order_pdfs)
            <div class="section-card files-section">
                <div class="section-header">
                    <div class="section-header-left">
                        <div class="section-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="section-title">Currently Uploaded Files</h2>
                            <p class="section-description">Files attached to this order</p>
                        </div>
                    </div>
                </div>

                <div class="section-body">
                    <div class="files-grid">
                        <!-- Current Images -->
                        @if($order->images)
                            <div class="file-group">
                                <div class="file-group-header">
                                    <div class="file-type-icon images">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <polyline points="21 15 16 10 5 21" />
                                        </svg>
                                    </div>
                                    <div class="file-group-info">
                                        <span class="file-group-title">Product Images</span>
                                        <span class="file-count">{{ count(json_decode($order->images, true) ?? []) }} files</span>
                                    </div>
                                </div>
                                <div class="images-grid">
                                    @foreach(json_decode($order->images, true) ?? [] as $index => $image)
                                        <div class="image-item" onclick="openImageModal('{{ $image['url'] }}')">
                                            <div class="image-wrapper">
                                                <img src="{{ $image['url'] }}" alt="Image {{ $index + 1 }}">
                                                <div class="image-hover-overlay">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2">
                                                        <circle cx="11" cy="11" r="8" />
                                                        <path d="M21 21l-4.35-4.35" />
                                                        <line x1="11" y1="8" x2="11" y2="14" />
                                                        <line x1="8" y1="11" x2="14" y2="11" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="image-label">Image {{ $index + 1 }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Current PDFs -->
                        @if($order->order_pdfs)
                            <div class="file-group">
                                <div class="file-group-header">
                                    <div class="file-type-icon pdfs">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                            <line x1="16" y1="13" x2="8" y2="13" />
                                            <line x1="16" y1="17" x2="8" y2="17" />
                                            <polyline points="10 9 9 9 8 9" />
                                        </svg>
                                    </div>
                                    <div class="file-group-info">
                                        <span class="file-group-title">PDF Documents</span>
                                        <span class="file-count">{{ count(json_decode($order->order_pdfs, true) ?? []) }}
                                            files</span>
                                    </div>
                                </div>
                                <div class="pdfs-list">
                                    @foreach(json_decode($order->order_pdfs, true) ?? [] as $index => $pdf)
                                        @php
                                            $path = is_array($pdf) ? $pdf['url'] : $pdf;
                                            $name = is_array($pdf) ? $pdf['name'] : basename($pdf);
                                        @endphp
                                        <a href="{{ asset($path) }}" target="_blank" class="pdf-item">
                                            <div class="pdf-item-icon">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                                    <polyline points="14 2 14 8 20 8" fill="none" stroke="white" stroke-width="2" />
                                                </svg>
                                            </div>
                                            <div class="pdf-item-info">
                                                <div class="pdf-name">{{ $name }}</div>
                                                <div class="pdf-action">
                                                    <span>Click to open</span>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                                                        <polyline points="15 3 21 3 21 9" />
                                                        <line x1="10" y1="14" x2="21" y2="3" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Order Form Section -->
        <div class="form-section">
            @if ($errors->any())
                <div class="alert-error">
                    <div class="alert-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">Please correct the following errors:</div>
                        <ul class="alert-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('orders.update', $order->id) }}" method="POST" enctype="multipart/form-data"
                id="editOrderForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="order_type" value="{{ $order->order_type }}">

                <div id="orderFormFields">
                    <div class="loading-container">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Loading order details...</p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <button class="modal-close-btn" onclick="closeImageModal()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
        <img class="modal-image-content" id="modalImage">
    </div>

    <style>
        :root {
            --color-primary: #4F46E5;
            --color-primary-hover: #4338CA;
            --color-primary-light: #EEF2FF;
            --color-success: #10B981;
            --color-success-light: #D1FAE5;
            --color-danger: #EF4444;
            --color-danger-light: #FEE2E2;
            --color-warning: #F59E0B;
            --color-warning-light: #FEF3C7;
            --color-info: #3B82F6;
            --color-info-light: #DBEAFE;

            --color-text-primary: #111827;
            --color-text-secondary: #6B7280;
            --color-text-tertiary: #9CA3AF;

            --color-bg-primary: #FFFFFF;
            --color-bg-secondary: #F9FAFB;
            --color-bg-tertiary: #F3F4F6;

            --color-border: #E5E7EB;
            --color-border-light: #F3F4F6;

            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);

            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--color-bg-secondary);
            color: var(--color-text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* Page Header */
        .page-header-wrapper {
            /* max-width: 87.5rem; */
            margin: 0 auto;
            padding: 1.5rem;
        }

        .page-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
            min-width: 0;
        }

        .btn-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-back-link:hover {
            background: var(--color-bg-tertiary);
            color: var(--color-text-primary);
            border-color: var(--color-text-tertiary);
        }

        .btn-back-link svg {
            flex-shrink: 0;
        }

        .header-title-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            min-width: 0;
        }

        .page-main-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-text-primary);
            margin: 0;
            line-height: 1.2;
        }

        .order-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .order-type-badge.ready_to_ship {
            background: var(--color-success-light);
            color: var(--color-success);
        }

        .order-type-badge.custom_diamond {
            background: var(--color-info-light);
            color: var(--color-info);
        }

        .order-type-badge.custom_jewellery {
            background: var(--color-warning-light);
            color: var(--color-warning);
        }

        .order-type-badge svg {
            flex-shrink: 0;
        }

        .btn-update-order {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: var(--color-primary);
            border: none;
            border-radius: var(--radius-md);
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-update-order:hover {
            background: var(--color-primary-hover);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .btn-update-order svg {
            flex-shrink: 0;
        }

        /* Content Wrapper */
        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Section Card */
        .section-card {
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--color-border-light);
        }

        .section-header-left {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background: var(--color-primary-light);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-primary);
            flex-shrink: 0;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-text-primary);
            margin: 0 0 0.125rem 0;
            line-height: 1.2;
        }

        .section-description {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
            margin: 0;
            line-height: 1.4;
        }

        .section-body {
            padding: 1.5rem;
        }

        /* Files Grid */
        .files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .file-group {
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-light);
            border-radius: var(--radius-md);
            padding: 1rem;
        }

        .file-group-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.875rem;
            border-bottom: 1px solid var(--color-border);
        }

        .file-type-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .file-type-icon.images {
            background: linear-gradient(135deg, #A78BFA 0%, #8B5CF6 100%);
            color: white;
        }

        .file-type-icon.pdfs {
            background: linear-gradient(135deg, #F87171 0%, #EF4444 100%);
            color: white;
        }

        .file-group-info {
            flex: 1;
        }

        .file-group-title {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-text-primary);
            line-height: 1.2;
            margin-bottom: 0.125rem;
        }

        .file-count {
            display: block;
            font-size: 0.75rem;
            color: var(--color-text-tertiary);
            font-weight: 500;
        }

        /* Images Grid */
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
        }

        .image-item {
            cursor: pointer;
        }

        .image-wrapper {
            position: relative;
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--color-border);
            background: white;
            transition: all 0.2s ease;
        }

        .image-item:hover .image-wrapper {
            border-color: var(--color-primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-hover-overlay {
            position: absolute;
            inset: 0;
            background: rgba(79, 70, 229, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            color: white;
        }

        .image-item:hover .image-hover-overlay {
            opacity: 1;
        }

        .image-label {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--color-text-secondary);
            text-align: center;
        }

        /* PDFs List */
        .pdfs-list {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .pdf-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem;
            background: white;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pdf-item:hover {
            border-color: var(--color-danger);
            box-shadow: var(--shadow-sm);
            transform: translateX(4px);
        }

        .pdf-item-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #F87171 0%, #EF4444 100%);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .pdf-item-info {
            flex: 1;
            min-width: 0;
        }

        .pdf-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pdf-action {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            color: var(--color-danger);
            font-weight: 500;
        }

        /* Loading */
        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--color-border);
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            margin-top: 1rem;
            font-size: 0.875rem;
            color: var(--color-text-secondary);
            font-weight: 500;
        }

        /* Alert */
        .alert-error {
            display: flex;
            gap: 0.875rem;
            padding: 1rem 1.25rem;
            background: var(--color-danger-light);
            border: 1px solid var(--color-danger);
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
        }

        .alert-icon {
            color: var(--color-danger);
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #991B1B;
            margin-bottom: 0.5rem;
        }

        .alert-list {
            margin: 0;
            padding-left: 1.25rem;
            font-size: 0.8125rem;
            color: #991B1B;
        }

        .alert-list li {
            margin-bottom: 0.25rem;
        }

        .alert-list li:last-child {
            margin-bottom: 0;
        }

        /* Image Modal */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(8px);
            padding: 3rem;
        }

        .modal-close-btn {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 10000;
        }

        .modal-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .modal-image-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .files-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-header-wrapper {
                margin: -1rem -1rem 1rem -1rem;
                padding: 1rem;
            }

            .page-header-content {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }

            .header-left {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .header-title-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .btn-back-link {
                align-self: flex-start;
            }

            .btn-update-order {
                width: 100%;
                justify-content: center;
            }

            .page-main-title {
                font-size: 1.25rem;
            }

            .section-body {
                padding: 1rem;
            }

            .images-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 0.5rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const orderType = '{{ $order->order_type }}';
            loadForm(orderType);

            function loadForm(type) {
                const container = document.getElementById('orderFormFields');
                container.innerHTML = `
                                <div class="loading-container">
                                    <div class="loading-spinner"></div>
                                    <p class="loading-text">Loading order details...</p>
                                </div>
                            `;

                if (!type) return;

                fetch(`/admin/orders/form/${type}?edit=true&id={{ $order->id }}`)
                    .then(response => response.text())
                    .then(html => {
                        container.innerHTML = html;
                        initializeFilePreview();
                    })
                    .catch(() => {
                        container.innerHTML = `
                                        <div class="alert-error">
                                            <div class="alert-icon">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                                </svg>
                                            </div>
                                            <div class="alert-content">
                                                <div class="alert-title">Error loading form</div>
                                                <p style="margin: 0.5rem 0 0 0; font-size: 0.8125rem; color: #991B1B;">Please try again or refresh the page.</p>
                                            </div>
                                        </div>
                                    `;
                    });
            }

            // Initialize file preview for dynamically loaded forms
            function initializeFilePreview() {
                const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
                imageInputs.forEach(input => {
                    if (!input.dataset.previewInitialized) {
                        input.dataset.previewInitialized = 'true';
                        input.addEventListener('change', function (e) {
                            handleImagePreview(e.target);
                        });
                    }
                });

                const pdfInputs = document.querySelectorAll('input[type="file"][accept*="pdf"]');
                pdfInputs.forEach(input => {
                    if (!input.dataset.previewInitialized) {
                        input.dataset.previewInitialized = 'true';
                        input.addEventListener('change', function (e) {
                            handlePDFPreview(e.target);
                        });
                    }
                });
            }

            function handleImagePreview(input) {
                const files = Array.from(input.files);
                if (files.length === 0) return;

                let previewContainer = input.parentElement.querySelector('.file-upload-preview');
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.className = 'file-upload-preview';
                    input.parentElement.appendChild(previewContainer);
                }

                previewContainer.innerHTML = `
                                <div class="preview-header">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                    <span>Selected Images (${files.length})</span>
                                </div>
                                <div class="preview-grid" id="imagePreviewGrid"></div>
                            `;
                previewContainer.classList.add('active');

                const grid = previewContainer.querySelector('#imagePreviewGrid');

                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                                        <img src="${e.target.result}" alt="${file.name}">
                                        <div class="file-name">${file.name}</div>
                                    `;
                        grid.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                });
            }

            function handlePDFPreview(input) {
                const files = Array.from(input.files);
                if (files.length === 0) return;

                let previewContainer = input.parentElement.querySelector('.file-upload-preview');
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.className = 'file-upload-preview';
                    input.parentElement.appendChild(previewContainer);
                }

                previewContainer.innerHTML = `
                                <div class="preview-header">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    <span>Selected PDFs (${files.length})</span>
                                </div>
                                <div id="pdfPreviewList"></div>
                            `;
                previewContainer.classList.add('active');

                const list = previewContainer.querySelector('#pdfPreviewList');

                files.forEach((file, index) => {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'pdf-preview-item';
                    const fileSize = (file.size / 1024).toFixed(2);
                    previewItem.innerHTML = `
                                    <div class="pdf-preview-icon">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                            <polyline points="14 2 14 8 20 8" fill="none" stroke="white" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    <div class="pdf-preview-info">
                                        <div class="pdf-preview-name">${file.name}</div>
                                        <div class="pdf-preview-size">${fileSize} KB</div>
                                    </div>
                                `;
                    list.appendChild(previewItem);
                });
            }
        });

        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImg.src = src;
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });

        // Prevent form submission on Enter key in input fields
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
                const form = document.getElementById('editOrderForm');
                if (form && event.target.form === form) {
                    event.preventDefault();
                }
            }
        });
    </script>

    <style>
        /* File Upload Preview Styles */
        .file-upload-preview {
            margin-top: 1rem;
            display: none;
            padding: 1rem;
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-light);
            border-radius: var(--radius-md);
        }

        .file-upload-preview.active {
            display: block;
        }

        .preview-header {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--color-border);
        }

        .preview-header svg {
            color: var(--color-primary);
            flex-shrink: 0;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
        }

        .preview-item {
            position: relative;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            padding: 0.5rem;
            background: var(--color-bg-primary);
            text-align: center;
            transition: all 0.2s ease;
        }

        .preview-item:hover {
            border-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .preview-item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            margin-bottom: 0.5rem;
        }

        .preview-item .file-name {
            font-size: 0.75rem;
            color: var(--color-text-secondary);
            font-weight: 500;
            word-break: break-all;
            line-height: 1.3;
        }

        .pdf-preview-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }

        .pdf-preview-item:hover {
            border-color: var(--color-danger);
            transform: translateX(4px);
        }

        .pdf-preview-item:last-child {
            margin-bottom: 0;
        }

        .pdf-preview-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #F87171 0%, #EF4444 100%);
            color: white;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pdf-preview-info {
            flex: 1;
            min-width: 0;
        }

        .pdf-preview-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--color-text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.125rem;
        }

        .pdf-preview-size {
            font-size: 0.75rem;
            color: var(--color-text-tertiary);
            font-weight: 500;
        }
    </style>
@endsection