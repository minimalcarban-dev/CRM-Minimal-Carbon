@extends('layouts.admin')
@section('title', 'View Order Details')
@section('content')
    <!-- Page Header -->
    <div class="page-header-modern mb-4">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <div>
                    <h1 class="header-title">Order #{{ $order->id }}</h1>
                    <p class="header-subtitle">Complete order information and details</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('orders.index') }}" class="btn-modern btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Orders</span>
                </a>
                <button class="btn-modern btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Order Type & Status Banner -->
    <div class="status-banner mb-4">
        <div class="status-item">
            <div class="status-label">Order Type</div>
            <div class="status-value">
                @if($order->order_type == 'ready_to_ship')
                    <span class="badge-modern badge-info">
                        <i class="bi bi-box-seam"></i> Ready to Ship
                    </span>
                @elseif($order->order_type == 'custom_diamond')
                    <span class="badge-modern badge-warning">
                        <i class="bi bi-gem"></i> Custom Diamond
                    </span>
                @else
                    <span class="badge-modern badge-primary">
                        <i class="bi bi-stars"></i> Custom Jewellery
                    </span>
                @endif
            </div>
        </div>
        <div class="status-item">
            <div class="status-label">Diamond Status</div>
            <div class="status-value">
                @if($order->diamond_status)
                    <span
                        class="badge-modern {{ in_array($order->diamond_status, ['completed', 'diamond_completed']) ? 'badge-success' : 'badge-secondary' }}">
                        <i class="bi bi-check-circle"></i> {{ ucfirst(str_replace('_', ' ', $order->diamond_status)) }}
                    </span>
                @else
                    <span class="badge-modern badge-secondary">
                        <i class="bi bi-dash-circle"></i> Not Set
                    </span>
                @endif
            </div>
        </div>
        @if($order->note)
            <div class="status-item">
                <div class="status-label">Priority</div>
                <div class="status-value">
                    <span class="badge-modern {{ $order->note == 'priority' ? 'badge-danger' : 'badge-secondary' }}">
                        <i class="bi bi-flag-fill"></i> {{ ucfirst(str_replace('_', ' ', $order->note)) }}
                    </span>
                </div>
            </div>
        @endif
    </div>

    <!-- Order Information Section -->
    <div class="detail-section-card mb-4">
        <div class="section-header">
            <div class="section-icon">
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div class="section-header-text">
                <h5 class="section-title">Order Information</h5>
                <p class="section-description">Client and product details</p>
            </div>
        </div>
        <div class="section-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="bi bi-person-lines-fill"></i>
                        Client Details
                    </div>
                    <div class="detail-value">{{ $order->client_details ?? '—' }}</div>
                </div>

                @if($order->jewellery_details)
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="bi bi-gem"></i>
                            Jewellery Details
                        </div>
                        <div class="detail-value">{{ $order->jewellery_details }}</div>
                    </div>
                @endif

                @if($order->diamond_details)
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="bi bi-stars"></i>
                            Diamond Details
                        </div>
                        <div class="detail-value">{{ $order->diamond_details }}</div>
                    </div>
                @endif

                @if($order->company)
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="bi bi-briefcase"></i>
                            Company
                        </div>
                        <div class="detail-value">{{ $order->company->name }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Product Specifications Section -->
    @if($order->gold_detail_id || $order->ring_size_id || $order->setting_type_id || $order->earring_type_id)
        <div class="detail-section-card mb-4">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-sliders"></i>
                </div>
                <div class="section-header-text">
                    <h5 class="section-title">Product Specifications</h5>
                    <p class="section-description">Metal type, sizes, and settings</p>
                </div>
            </div>
            <div class="section-body">
                <div class="detail-grid detail-grid-4">
                    @if($order->goldDetail)
                        <div class="detail-item-compact">
                            <div class="detail-label-compact">
                                <i class="bi bi-circle-fill" style="color: #FFD700;"></i>
                                Gold Type
                            </div>
                            <div class="detail-value-compact">{{ $order->goldDetail->name }}</div>
                        </div>
                    @endif

                    @if($order->ringSize)
                        <div class="detail-item-compact">
                            <div class="detail-label-compact">
                                <i class="bi bi-circle"></i>
                                Ring Size
                            </div>
                            <div class="detail-value-compact">{{ $order->ringSize->size }}</div>
                        </div>
                    @endif

                    @if($order->settingType)
                        <div class="detail-item-compact">
                            <div class="detail-label-compact">
                                <i class="bi bi-gear"></i>
                                Setting Type
                            </div>
                            <div class="detail-value-compact">{{ $order->settingType->name }}</div>
                        </div>
                    @endif

                    @if($order->earringType)
                        <div class="detail-item-compact">
                            <div class="detail-label-compact">
                                <i class="bi bi-flower1"></i>
                                Earring Type
                            </div>
                            <div class="detail-value-compact">{{ $order->earringType->name }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Financial Information Section -->
    @if($order->gross_sell)
        <div class="detail-section-card mb-4">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div class="section-header-text">
                    <h5 class="section-title">Financial Information</h5>
                    <p class="section-description">Pricing details</p>
                </div>
            </div>
            <div class="section-body">
                <div class="financial-display">
                    <div class="financial-label">Gross Sell Amount</div>
                    <div class="financial-amount">₹{{ number_format($order->gross_sell, 2) }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Shipping Information Section -->
    @if($order->shipping_company_name || $order->tracking_number || $order->dispatch_date || $order->tracking_url)
        <div class="detail-section-card mb-4">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="section-header-text">
                    <h5 class="section-title">Shipping Information</h5>
                    <p class="section-description">Delivery and tracking details</p>
                </div>
            </div>
            <div class="section-body">
                <div class="detail-grid detail-grid-4">
                    @if($order->shipping_company_name)
                        <div class="detail-item-compact">
                            <div class="detail-label-compact">
                                <i class="bi bi-building"></i>
                                Shipping Company
                            </div>
                            <div class="detail-value-compact">{{ $order->shipping_company_name }}</div>
                        </div>
                    @endif

                    @if($order->tracking_number)
                        <div class="detail-item-compact">
                            <div class="detail-label-compact">
                                <i class="bi bi-hash"></i>
                                Tracking Number
                            </div>
                            <div class="detail-value-compact">{{ $order->tracking_number }}</div>
                        </div>
                    @endif

                    @if($order->dispatch_date)
                        <div class="detail-item-compact">
                            <div class="detail-label-compact">
                                <i class="bi bi-calendar-event"></i>
                                Dispatch Date
                            </div>
                            <div class="detail-value-compact">{{ \Carbon\Carbon::parse($order->dispatch_date)->format('d M Y') }}
                            </div>
                        </div>
                    @endif

                    @if($order->tracking_url)
                        <div class="detail-item-compact">
                            <div class="detail-label-compact">
                                <i class="bi bi-link-45deg"></i>
                                Tracking URL
                            </div>
                            <div class="detail-value-compact">
                                <a href="{{ $order->tracking_url }}" target="_blank" class="link-modern">
                                    Track Shipment <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Images Section -->
    @if($order->images)
        <div class="detail-section-card mb-4">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-images"></i>
                </div>
                <div class="section-header-text">
                    <h5 class="section-title">Product Images</h5>
                    <p class="section-description">{{ count(json_decode($order->images, true) ?? []) }} image(s) uploaded</p>
                </div>
            </div>
            <div class="section-body">
                <div class="image-gallery">
                    @foreach(json_decode($order->images, true) ?? [] as $image)
                        <div class="gallery-item">
                            <img src="{{ asset($image) }}" alt="Order Image" onclick="openImageModal(this.src)">
                            <div class="gallery-overlay">
                                <i class="bi bi-zoom-in"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Documents Section -->
    @if($order->order_pdfs)
        <div class="detail-section-card mb-4">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-file-pdf"></i>
                </div>
                <div class="section-header-text">
                    <h5 class="section-title">PDF Documents</h5>
                    <p class="section-description">{{ count(json_decode($order->order_pdfs, true) ?? []) }} document(s) attached
                    </p>
                </div>
            </div>
            <div class="section-body">
                <div class="document-list">
                    @foreach(json_decode($order->order_pdfs, true) ?? [] as $pdf)
                        @php
                            $path = is_array($pdf) ? $pdf['path'] : $pdf;
                            $name = is_array($pdf) ? $pdf['name'] : basename($pdf);
                        @endphp
                        <a href="{{ asset($path) }}" target="_blank" class="document-item">
                            <div class="document-icon">
                                <i class="bi bi-file-pdf-fill"></i>
                            </div>
                            <div class="document-info">
                                <div class="document-name">{{ $name }}</div>
                                <div class="document-action">Click to view <i class="bi bi-box-arrow-up-right"></i></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f8fafc;
            --border: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
            --info: #3b82f6;
            --warning: #f59e0b;
        }

        /* Page Header */
        .page-header-modern {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            border: 2px solid var(--border);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .header-subtitle {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0.25rem 0 0 0;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        /* Modern Buttons */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-modern i {
            font-size: 1rem;
        }

        .btn-modern.btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-modern.btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-modern.btn-secondary {
            background: white;
            color: var(--dark);
            border-color: var(--border);
        }

        .btn-modern.btn-secondary:hover {
            background: var(--light-gray);
            border-color: var(--gray);
        }

        /* Status Banner */
        .status-banner {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .status-item {
            background: white;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            flex: 1;
            min-width: 200px;
        }

        .status-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .status-value {
            font-size: 1rem;
        }

        /* Badges */
        .badge-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge-modern.badge-info {
            background: linear-gradient(135deg, var(--info), #2563eb);
            color: white;
        }

        .badge-modern.badge-warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
            color: white;
        }

        .badge-modern.badge-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .badge-modern.badge-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
        }

        .badge-modern.badge-secondary {
            background: linear-gradient(135deg, var(--secondary), #475569);
            color: white;
        }

        .badge-modern.badge-danger {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
        }

        /* Detail Section Card */
        .detail-section-card {
            background: white;
            border-radius: 16px;
            border: 2px solid var(--border);
            overflow: hidden;
        }

        .section-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .section-header-text {
            flex: 1;
        }

        .section-title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.125rem 0;
        }

        .section-description {
            font-size: 0.8125rem;
            color: var(--gray);
            margin: 0;
        }

        .section-body {
            padding: 1.5rem;
        }

        /* Detail Grid */
        .detail-grid {
            display: grid;
            gap: 1.5rem;
        }

        .detail-grid-4 {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .detail-label i {
            color: var(--primary);
            font-size: 1rem;
        }

        .detail-value {
            font-size: 0.9375rem;
            color: var(--dark);
            line-height: 1.6;
            white-space: pre-wrap;
        }

        /* Compact Detail Items */
        .detail-item-compact {
            background: var(--light-gray);
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
        }

        .detail-label-compact {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0.5rem;
        }

        .detail-label-compact i {
            color: var(--primary);
        }

        .detail-value-compact {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Financial Display */
        .financial-display {
            background: linear-gradient(135deg, var(--success), #059669);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .financial-label {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .financial-amount {
            font-size: 2.5rem;
            font-weight: 700;
        }

        /* Image Gallery */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid var(--border);
            aspect-ratio: 1;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: white;
            font-size: 2rem;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        /* Document List */
        .document-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .document-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--light-gray);
            border: 2px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .document-item:hover {
            background: white;
            border-color: var(--primary);
            transform: translateX(4px);
        }

        .document-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .document-info {
            flex: 1;
            min-width: 0;
        }

        .document-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9375rem;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .document-action {
            font-size: 0.8125rem;
            color: var(--primary);
            font-weight: 500;
        }

        .link-modern {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .link-modern:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Image Modal */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.95);
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 85%;
            border-radius: 8px;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 48px;
            font-weight: 300;
            cursor: pointer;
            transition: 0.3s;
        }

        .modal-close:hover {
            color: #bbb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn-modern {
                width: 100%;
                justify-content: center;
            }

            .status-banner {
                flex-direction: column;
            }

            .status-item {
                min-width: 100%;
            }

            .detail-grid-4 {
                grid-template-columns: 1fr;
            }

            .image-gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .financial-amount {
                font-size: 2rem;
            }
        }

        /* Print Styles */
        @media print {

            .header-actions,
            .gallery-overlay {
                display: none !important;
            }

            .page-header-modern,
            .detail-section-card,
            .status-banner {
                border: 1px solid #ddd;
                break-inside: avoid;
            }
        }
    </style>

    <script>
        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImg.src = src;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
@endsection