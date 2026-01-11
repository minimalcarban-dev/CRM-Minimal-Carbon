@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
    <div class="order-details-wrapper">
        <!-- Header -->
        <div class="order-header no-print">
            <div class="header-left">
                <h1 class="order-title">Order #{{ $order->id }}</h1>
                <p class="order-date">Created: {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('orders.index') }}" class="btn-action btn-back">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <a href="{{ route('orders.edit', $order) }}" class="btn-action btn-edit">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <button onclick="window.print()" class="btn-action btn-print">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="status-cards">
            <div class="status-card">
                <span class="status-label">Order Type</span>
                <span class="status-badge status-{{ $order->order_type }}">
                    {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                </span>
            </div>

            @if($order->diamond_status)
                <div class="status-card">
                    <span class="status-label">Diamond Status</span>
                    <span class="status-badge status-{{ $order->diamond_status }}">
                        {{ ucfirst(str_replace('_', ' ', $order->diamond_status)) }}
                    </span>
                </div>
            @endif

            @if($order->note)
                <div class="status-card">
                    <span class="status-label">Priority</span>
                    <span class="status-badge status-{{ $order->note }}">
                        {{ ucfirst(str_replace('_', ' ', $order->note)) }}
                    </span>
                </div>
            @endif

            @if($order->company)
                <div class="status-card">
                    <span class="status-label">Company</span>
                    <span class="status-value">{{ $order->company->name }}</span>
                </div>
            @endif

            @if($order->creator)
                <div class="status-card">
                    <span class="status-label">Created By</span>
                    <span class="status-value">{{ $order->creator->name }}</span>
                </div>
            @endif

            @if($order->lastModifier)
                <div class="status-card">
                    <span class="status-label">Last Edited By</span>
                    <span class="status-value">{{ $order->lastModifier->name }}</span>
                </div>
            @endif
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Left Column -->
            <div class="content-column">

                <!-- Client Details -->
                <div class="info-section">
                    <h3 class="section-title">
                        <i class="bi bi-person-circle"></i> Client Details
                    </h3>
                    <div class="section-content">
                        <div class="client-details-grid">
                            <div class="detail-row"><strong>Name:</strong>
                                {{ $order->display_client_name ?? ($order->client_details ?? '') }}</div>
                            <div class="detail-row"><strong>Email:</strong> {{ $order->display_client_email ?? '' }}</div>
                            @if($order->display_client_mobile)
                                <div class="detail-row"><strong>Mobile:</strong> {{ $order->display_client_mobile }}</div>
                            @endif
                            <div class="detail-row"><strong>Address:</strong>
                                <p style="margin:0">{{ $order->display_client_address ?? '' }}</p>
                            </div>
                            @if($order->display_client_tax_id)
                                <div class="detail-row"><strong>Tax ID:</strong> {{ $order->display_client_tax_id }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                @if($order->jewellery_details || $order->diamond_details)
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="bi bi-gem"></i> Product Details
                        </h3>
                        <div class="section-content">
                            @if($order->jewellery_details)
                                <div class="detail-group">
                                    <strong>Jewellery:</strong>
                                    <p>{{ $order->jewellery_details }}</p>
                                </div>
                            @endif

                            @if($order->diamond_details)
                                <div class="detail-group">
                                    <strong>Diamond:</strong>
                                    <p>{{ $order->diamond_details }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Specifications -->
                @if($order->goldDetail || $order->ringSize || $order->settingType || $order->earringType)
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="bi bi-sliders"></i> Specifications
                        </h3>
                        <div class="section-content">
                            <div class="specs-grid">
                                @if($order->goldDetail)
                                    <div class="spec-item">
                                        <span class="spec-label">Metal Type</span>
                                        <span class="spec-value">{{ $order->goldDetail->name }}</span>
                                    </div>
                                @endif

                                @if($order->ringSize)
                                    <div class="spec-item">
                                        <span class="spec-label">Ring Size</span>
                                        <span class="spec-value">{{ $order->ringSize->name }}</span>
                                    </div>
                                @endif

                                @if($order->settingType)
                                    <div class="spec-item">
                                        <span class="spec-label">Setting Type</span>
                                        <span class="spec-value">{{ $order->settingType->name }}</span>
                                    </div>
                                @endif

                                @if($order->earringDetail)
                                    <div class="spec-item">
                                        <span class="spec-label">Earring Type</span>
                                        <span class="spec-value">{{ $order->earringDetail->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Financial Info -->
                @if($order->gross_sell)
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="bi bi-currency-dollar"></i> Pricing
                        </h3>
                        <div class="section-content">
                            <div class="price-display">
                                <span class="price-label">Gross Sell Amount</span>
                                <span class="price-value">$ {{ number_format($order->gross_sell, 2) }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Admin Notes -->
                @if($order->special_notes)
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="bi bi-journal-text"></i> Special Notes
                        </h3>
                        <div class="section-content">
                            <p style="white-space: pre-wrap;">{{ $order->special_notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Shipping Info -->
                @if($order->shipping_company_name || $order->tracking_number || $order->dispatch_date)
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="bi bi-truck"></i> Shipping Information
                        </h3>
                        <div class="section-content">
                            <div class="specs-grid">
                                @if($order->shipping_company_name)
                                    <div class="spec-item">
                                        <span class="spec-label">Company</span>
                                        <span class="spec-value">{{ $order->shipping_company_name }}</span>
                                    </div>
                                @endif

                                @if($order->tracking_number)
                                    <div class="spec-item">
                                        <span class="spec-label">Tracking #</span>
                                        <span class="spec-value">{{ $order->tracking_number }}</span>
                                    </div>
                                @endif

                                @if($order->dispatch_date)
                                    <div class="spec-item">
                                        <span class="spec-label">Dispatch Date</span>
                                        <span
                                            class="spec-value">{{ \Carbon\Carbon::parse($order->dispatch_date)->format('d M Y') }}</span>
                                    </div>
                                @endif
                            </div>

                            @if($order->tracking_url)
                                <a href="{{ $order->tracking_url }}" target="_blank" class="tracking-link no-print">
                                    <i class="bi bi-box-arrow-up-right"></i> Track Shipment
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

            </div>

            <!-- Right Column -->
            <div class="content-column">

                <!-- Product Images -->
                @php
                    $images = is_string($order->images) ? json_decode($order->images, true) : ($order->images ?? []);
                @endphp

                @if(!empty($images) && count($images) > 0)
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="bi bi-images"></i> Product Images ({{ count($images) }})
                        </h3>
                        <div class="section-content">
                            <div class="images-grid">
                                @foreach($images as $index => $image)
                                    <div class="image-item"
                                        onclick="viewImage('{{ $image['url'] }}', '{{ addslashes($image['name'] ?? 'Image') }}')">
                                        <img src="{{ $image['url'] }}" alt="{{ $image['name'] ?? 'Image' }}" loading="lazy">
                                        <div class="image-overlay">
                                            <i class="bi bi-eye"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- PDF Documents -->
                @php
                    $pdfs = is_string($order->order_pdfs) ? json_decode($order->order_pdfs, true) : ($order->order_pdfs ?? []);
                @endphp

                @if(!empty($pdfs) && count($pdfs) > 0)
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="bi bi-file-pdf"></i> Documents ({{ count($pdfs) }})
                        </h3>
                        <div class="section-content">
                            <div class="pdf-list">
                                @foreach($pdfs as $pdf)
                                    <div class="pdf-item">
                                        <div class="pdf-icon">
                                            <i class="bi bi-file-pdf-fill"></i>
                                        </div>
                                        <div class="pdf-info">
                                            <p class="pdf-name">{{ $pdf['name'] ?? 'Document.pdf' }}</p>
                                            <small class="pdf-size">
                                                {{ isset($pdf['size']) ? number_format($pdf['size'] / (1024 * 1024), 2) . ' MB' : '' }}
                                            </small>
                                        </div>
                                        <div class="pdf-actions no-print">
                                            <button type="button" class="pdf-btn"
                                                onclick="viewPDF('{{ $pdf['url'] }}', '{{ addslashes($pdf['name'] ?? 'Document.pdf') }}')"
                                                title="View PDF">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="pdf-btn"
                                                onclick="downloadPDF('{{ $pdf['url'] }}', '{{ addslashes($pdf['name'] ?? 'Document.pdf') }}')"
                                                title="Download PDF">
                                                <i class="bi bi-download"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox no-print" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <button class="lightbox-nav prev" onclick="event.stopPropagation(); navigateLightbox(-1)">
            <i class="bi bi-chevron-left"></i>
        </button>
        <img id="lightbox-img" class="lightbox-img" src="" alt="Full Image">
        <button class="lightbox-nav next" onclick="event.stopPropagation(); navigateLightbox(1)">
            <i class="bi bi-chevron-right"></i>
        </button>
        <div class="lightbox-counter" id="lightbox-counter"></div>
    </div>

    <!-- Image Viewer Modal -->
    <div id="imageModal" class="pdf-modal no-print" onclick="closeImageModal()">
        <div class="pdf-modal-content" onclick="event.stopPropagation()">
            <div class="pdf-modal-header">
                <h3 id="imageModalTitle">Image Viewer</h3>
                <button class="pdf-modal-close" onclick="closeImageModal()" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="pdf-modal-body" style="background: #000;">
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                    <img id="imageViewer" src="" style="max-width: 100%; max-height: 100%; object-fit: contain;"
                        alt="Image">
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Viewer Modal -->
    <div id="pdfModal" class="pdf-modal no-print" onclick="closePDFModal()">
        <div class="pdf-modal-content" onclick="event.stopPropagation()">
            <div class="pdf-modal-header">
                <h3 id="pdfModalTitle">Document Viewer</h3>
                <button class="pdf-modal-close" onclick="closePDFModal()" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="pdf-modal-body">
                <iframe id="pdfViewer" src="" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    <style>
        /* Root Variables */
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --gray: #64748b;
            --light: #f8fafc;
            --border: #e2e8f0;
            --white: #ffffff;
        }

        /* Main Wrapper */
        .order-details-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* Header */
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .order-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.25rem 0;
        }

        .order-date {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--white);
            color: var(--dark);
        }

        .btn-action:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-print {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .btn-print:hover {
            background: var(--primary-light);
            border-color: var(--primary-light);
            color: var(--white);
        }

        /* Status Cards */
        .status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .status-card {
            background: var(--white);
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .status-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
        }

        .status-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            text-align: center;
        }

        /* Status Badge Colors */
        .status-ready_to_ship {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-custom_diamond {
            background: #fef3c7;
            color: #92400e;
        }

        .status-custom_jewellery {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-processed {
            background: #ddd6fe;
            color: #5b21b6;
        }

        .status-completed,
        .status-diamond_completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-diamond_purchased {
            background: #fce7f3;
            color: #9f1239;
        }

        .status-factory_making {
            background: #fed7aa;
            color: #92400e;
        }

        .status-priority {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-non_priority {
            background: #f3f4f6;
            color: #374151;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .content-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Info Section */
        .info-section {
            background: var(--white);
            border: 2px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            background: var(--light);
            border-bottom: 2px solid var(--border);
        }

        .section-title i {
            color: var(--primary);
            font-size: 1.125rem;
        }

        .section-content {
            padding: 1.25rem;
        }

        .section-content p {
            margin: 0;
            line-height: 1.6;
            color: var(--dark);
        }

        .detail-group {
            margin-bottom: 1rem;
        }

        .detail-group:last-child {
            margin-bottom: 0;
        }

        .detail-group strong {
            display: block;
            margin-bottom: 0.25rem;
            color: var(--gray);
            font-size: 0.875rem;
        }

        .detail-group p {
            margin: 0;
        }

        /* Specs Grid */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .spec-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .spec-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .spec-value {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Price Display */
        .price-display {
            background: linear-gradient(135deg, var(--success), #059669);
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            color: var(--white);
        }

        .price-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .price-value {
            display: block;
            font-size: 2rem;
            font-weight: 700;
        }

        /* Tracking Link */
        .tracking-link {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .tracking-link:hover {
            background: var(--primary-light);
        }

        /* Images Grid */
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 0.75rem;
        }

        .image-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid var(--border);
            transition: all 0.3s;
        }

        .image-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            color: var(--white);
            font-size: 1.5rem;
        }

        .image-item:hover .image-overlay {
            opacity: 1;
        }

        /* PDF List */
        .pdf-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .pdf-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            transition: all 0.2s;
        }

        .pdf-item:hover {
            border-color: var(--primary);
        }

        .pdf-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: var(--white);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .pdf-info {
            flex: 1;
            min-width: 0;
        }

        .pdf-name {
            font-weight: 600;
            margin: 0 0 0.125rem 0;
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pdf-size {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .pdf-actions {
            display: flex;
            gap: 0.5rem;
        }

        .pdf-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border);
            border-radius: 6px;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.2s;
        }

        .pdf-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox-img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
        }

        .lightbox-close {
            position: absolute;
            top: 1.5rem;
            right: 2rem;
            font-size: 2.5rem;
            color: var(--white);
            cursor: pointer;
            font-weight: 300;
            line-height: 1;
            transition: all 0.2s;
        }

        .lightbox-close:hover {
            color: var(--gray);
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: var(--white);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .lightbox-nav:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .lightbox-nav.prev {
            left: 2rem;
        }

        .lightbox-nav.next {
            right: 2rem;
        }

        .lightbox-counter {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            color: var(--white);
            font-size: 0.875rem;
            background: rgba(0, 0, 0, 0.5);
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        /* PDF Modal */
        .pdf-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            animation: fadeIn 0.3s ease;
        }

        .pdf-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pdf-modal-content {
            background: var(--white);
            border-radius: 12px;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s ease;
        }

        .pdf-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--border);
            background: var(--light);
            border-radius: 12px 12px 0 0;
        }

        .pdf-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pdf-modal-close {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            cursor: pointer;
            padding: 0.5rem;
            line-height: 1;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .pdf-modal-close:hover {
            background: var(--danger);
            color: var(--white);
        }

        .pdf-modal-body {
            flex: 1;
            padding: 0;
            overflow: hidden;
            background: var(--light);
            border-radius: 0 0 12px 12px;
        }

        .pdf-modal-body iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: var(--white);
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }

            .order-details-wrapper {
                padding: 0;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .info-section {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .images-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            .status-cards {
                grid-template-columns: 1fr;
            }

            .specs-grid {
                grid-template-columns: 1fr;
            }

            .images-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }

            .lightbox-nav {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .lightbox-nav.prev {
                left: 1rem;
            }

            .lightbox-nav.next {
                right: 1rem;
            }
        }
    </style>

    <script>
        // Lightbox functionality
        let currentImageIndex = 0;
        const images = @json($images ?? []);

        function openLightbox(index) {
            currentImageIndex = index;
            showLightboxImage();
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = '';
        }

        function navigateLightbox(direction) {
            currentImageIndex += direction;
            if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1;
            } else if (currentImageIndex >= images.length) {
                currentImageIndex = 0;
            }
            showLightboxImage();
        }

        function showLightboxImage() {
            const img = document.getElementById('lightbox-img');
            const counter = document.getElementById('lightbox-counter');

            if (images[currentImageIndex]) {
                img.src = images[currentImageIndex].url;
                counter.textContent = `${currentImageIndex + 1} / ${images.length}`;
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', function (e) {
            const lightbox = document.getElementById('lightbox');
            if (lightbox.classList.contains('active')) {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') navigateLightbox(-1);
                if (e.key === 'ArrowRight') navigateLightbox(1);
            }

            const imageModal = document.getElementById('imageModal');
            if (imageModal.classList.contains('active')) {
                if (e.key === 'Escape') closeImageModal();
            }

            const pdfModal = document.getElementById('pdfModal');
            if (pdfModal.classList.contains('active')) {
                if (e.key === 'Escape') closePDFModal();
            }
        });

        // Image Viewer functionality
        function viewImage(url, name) {
            const modal = document.getElementById('imageModal');
            const viewer = document.getElementById('imageViewer');
            const title = document.getElementById('imageModalTitle');

            viewer.src = url;
            title.textContent = name || 'Image Viewer';

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            const viewer = document.getElementById('imageViewer');

            modal.classList.remove('active');
            viewer.src = '';
            document.body.style.overflow = '';
        }

        // PDF Viewer functionality
        function viewPDF(url, name) {
            const modal = document.getElementById('pdfModal');
            const viewer = document.getElementById('pdfViewer');
            const title = document.getElementById('pdfModalTitle');

            // Use Google Docs Viewer for better compatibility
            const viewerUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(url)}&embedded=true`;
            viewer.src = viewerUrl;
            title.textContent = name || 'Document Viewer';

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePDFModal() {
            const modal = document.getElementById('pdfModal');
            const viewer = document.getElementById('pdfViewer');

            modal.classList.remove('active');
            viewer.src = '';
            document.body.style.overflow = '';
        }

        // Download PDF with proper filename
        async function downloadPDF(url, filename) {
            try {
                // Show loading state
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                btn.disabled = true;

                // Fetch the PDF file
                const response = await fetch(url);
                const blob = await response.blob();

                // Create download link
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Clean up
                window.URL.revokeObjectURL(downloadUrl);

                // Restore button
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            } catch (error) {
                console.error('Download failed:', error);
                alert('Failed to download PDF. Please try again.');

                // Restore button on error
                const btn = event.target.closest('button');
                btn.innerHTML = '<i class="bi bi-download"></i>';
                btn.disabled = false;
            }
        }
    </script>
@endsection