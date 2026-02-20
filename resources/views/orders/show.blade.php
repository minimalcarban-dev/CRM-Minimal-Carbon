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
                <button onclick="window.history.back()" class="btn-action btn-back">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <a href="{{ route('orders.edit', $order) }}" class="btn-action btn-edit">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <!-- <button onclick="window.print()" class="btn-action btn-print">
                                                                                <i class="bi bi-printer"></i> Print
                                                                            </button> -->
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
                    <span class="status-date">{{ $order->updated_at->format('d M Y, h:i A') }}</span>
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
                        <div class="client-info-table">
                            <div class="info-row">
                                <span class="info-label">Name</span>
                                <span
                                    class="info-value">{{ $order->display_client_name ?? ($order->client_details ?? 'N/A') }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $order->display_client_email ?? 'N/A' }}</span>
                            </div>

                            @if($order->display_client_mobile)
                                <div class="info-row">
                                    <span class="info-label">Mobile</span>
                                    <span class="info-value">{{ $order->display_client_mobile }}</span>
                                </div>
                            @endif

                            <div class="info-row info-row-address">
                                <span class="info-label">Address</span>
                                <span class="info-value">{{ $order->display_client_address ?? 'N/A' }}</span>
                            </div>

                            @if($order->display_client_tax_id)
                                <div class="info-row">
                                    <span
                                        class="info-label">{{ \App\Models\Order::TAX_ID_TYPES[$order->client_tax_id_type] ?? 'Tax ID' }}</span>
                                    <span class="info-value">{{ $order->display_client_tax_id }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
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
                                <strong>Diamond Description:</strong>
                                <p>{{ $order->diamond_details }}</p>
                            </div>
                        @endif

                        @php
                            $skus = is_array($order->diamond_skus) ? $order->diamond_skus : (!empty($order->diamond_sku) ? [$order->diamond_sku] : []);
                            $prices = is_array($order->diamond_prices) ? $order->diamond_prices : [];
                        @endphp

                        @if(!empty($skus))
                            <div class="detail-group">
                                <strong>Diamond SKUs:</strong>
                                <div class="mt-2">
                                    @foreach($skus as $sku)
                                        <div
                                            class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded border">
                                            <span>
                                                <i class="bi bi-diamond text-primary"></i>
                                                <code>{{ $sku }}</code>
                                            </span>
                                            @if(isset($prices[$sku]))
                                                <span class="badge bg-success-subtle text-success">
                                                    $ {{ number_format($prices[$sku], 2) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($order->melee_diamond_id)
                            <div class="detail-group mt-4 p-3 border rounded bg-light">
                                <h6 class="mb-3 text-primary"><i class="bi bi-stars"></i> Melee Diamond Details</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Melee Item</small>
                                        <span>{{ $order->meleeDiamond->category->name ?? 'Melee' }} —
                                            {{ str_replace('-', ' ', $order->meleeDiamond->size_label ?? 'N/A') }}</span>
                                    </div>
                                    @if($order->melee_pieces)
                                        <div class="col-3">
                                            <small class="text-muted d-block">Pieces</small>
                                            <span>{{ $order->melee_pieces }} pcs</span>
                                        </div>
                                    @endif
                                    @if($order->melee_carat)
                                        <div class="col-3">
                                            <small class="text-muted d-block">Carat</small>
                                            <span>{{ number_format($order->melee_carat, 3) }} ct</span>
                                        </div>
                                    @endif
                                    @if($order->melee_price_per_ct)
                                        <div class="col-6 mt-2">
                                            <small class="text-muted d-block">Price per Ct</small>
                                            <span>$ {{ number_format($order->melee_price_per_ct, 2) }}</span>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <small class="text-muted d-block">Total Melee Value</small>
                                            <span class="fw-bold">$
                                                {{ number_format($order->melee_total_value ?? ($order->melee_carat * $order->melee_price_per_ct), 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

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
                                <span class="price-value">$ {{ number_format((float) $order->gross_sell, 2) }}</span>
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
                        <div class="section-title justify-content-between align-items-center no-print">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck"></i> Shipping Information
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($order->tracking_url)
                                    <a href="{{ $order->tracking_url }}" target="_blank" class="btn-action-sm">
                                        <i class="bi bi-box-arrow-up-right"></i> Official Page
                                    </a>
                                @endif
                                @if($order->tracking_number && ($order->shipping_company_name || $order->tracking_url))
                                    <form action="{{ route('orders.sync-tracking', $order) }}" method="POST" id="syncTrackingForm"
                                        class="m-0">
                                        @csrf
                                        <button type="submit" class="btn-action-sm header-action-btn"
                                            onclick="this.innerHTML='<i class=\'bi bi-arrow-repeat spin\'></i> Syncing...'">
                                            <i class="bi bi-arrow-repeat"></i> Sync Status
                                        </button>
                                    </form>

                                    <style>

                                    </style>
                                @endif
                            </div>
                        </div>
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

                                @if($order->tracking_status)
                                    <div class="spec-item col-12 mt-2">
                                        <span class="spec-label">Live Status</span>
                                        <span
                                            class="status-badge status-{{ strtolower(str_replace(' ', '_', $order->tracking_status)) }}">
                                            {{ $order->tracking_status }}
                                        </span>
                                    </div>
                                @endif
                            </div>


                            {{-- Tracking History Timeline --}}
                            @if(!empty($order->tracking_history) && is_array($order->tracking_history) && count($order->tracking_history) > 0)
                                <div class="tracking-timeline-container mt-4">
                                    <h5 class="timeline-title mb-3">Shipment Journey History</h5>
                                    <div class="tracking-history-timeline">
                                        @foreach($order->tracking_history as $history)
                                            <div class="tracking-item">
                                                <div class="tracking-point"></div>
                                                <div class="tracking-info">
                                                    <div class="tracking-header">
                                                        <span class="tracking-status">{{ $history['status'] ?? 'Update' }}</span>
                                                        <span class="tracking-date">{{ $history['date'] ?? '' }}</span>
                                                    </div>
                                                    @if(!empty($history['location']))
                                                        <div class="tracking-location">
                                                            <i class="bi bi-geo-alt"></i> {{ $history['location'] }}
                                                        </div>
                                                    @endif
                                                    @if(!empty($history['description']))
                                                        <div class="tracking-desc">{{ $history['description'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($order->last_tracker_sync)
                                        <div class="sync-info mt-3 text-muted">
                                            <small><i class="bi bi-clock"></i> Last updated:
                                                {{ $order->last_tracker_sync->diffForHumans() }}</small>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>

            <!-- Right Column -->
            <div class="content-column">

                <!-- Product Images -->
                @php
                    $images = $order->images;
                    if (is_string($images)) {
                        $images = json_decode($images, true);
                    }
                    $images = is_array($images) ? $images : [];
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
                    $pdfs = $order->order_pdfs;
                    if (is_string($pdfs)) {
                        $pdfs = json_decode($pdfs, true);
                    }
                    $pdfs = is_array($pdfs) ? $pdfs : [];
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

                {{-- Edit History Timeline (Superadmin Only) --}}
                @if(Auth::guard('admin')->user()?->is_super && $editHistory->count() > 0)
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="bi bi-clock-history"></i> Edit History ({{ $editHistory->count() }})
                        </h3>
                        <div class="section-content">
                            <div class="edit-timeline">
                                @foreach($editHistory as $index => $log)
                                    <div class="timeline-item">
                                        <div class="timeline-dot"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <span class="timeline-admin">
                                                    <i class="bi bi-person-fill"></i>
                                                    {{ $log->admin->name ?? 'Unknown Admin' }}
                                                </span>
                                                <span class="timeline-time">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ $log->created_at->format('d M Y, h:i A') }}
                                                </span>
                                                <span class="timeline-ago">{{ $log->created_at->diffForHumans() }}</span>
                                            </div>
                                            @if(!empty($log->old_values) || !empty($log->new_values))
                                                <div class="timeline-changes">
                                                    <button type="button" class="changes-toggle"
                                                        onclick="this.parentElement.classList.toggle('expanded')">
                                                        <i class="bi bi-list-check"></i>
                                                        {{ count($log->new_values ?? []) }} field(s) changed
                                                        <i class="bi bi-chevron-down toggle-icon"></i>
                                                    </button>
                                                    <div class="changes-detail">
                                                        <table class="changes-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Field</th>
                                                                    <th>Old Value</th>
                                                                    <th>New Value</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach(($log->new_values ?? []) as $field => $newVal)
                                                                    <tr>
                                                                        <td class="field-name">{{ $field }}</td>
                                                                        <td class="old-val">
                                                                            <span
                                                                                class="val-badge val-old">{{ Str::limit($log->old_values[$field] ?? '—', 80) }}</span>
                                                                        </td>
                                                                        <td class="new-val">
                                                                            <span
                                                                                class="val-badge val-new">{{ Str::limit($newVal ?? '—', 80) }}</span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
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

        .btn-action-sm {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--white);
            color: var(--secondary);
        }

        .btn-action-sm:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #eef2ff;
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

        /* Ready to Ship Diamond Statuses */
        .status-r_order_in_process {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-r_order_shipped {
            background: #d1fae5;
            color: #065f46;
        }

        /* Custom Diamond Statuses */
        .status-d_diamond_in_discuss {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-d_diamond_in_making {
            background: #fef3c7;
            color: #92400e;
        }

        .status-d_diamond_completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-d_diamond_in_certificate {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .status-d_order_shipped {
            background: #1e293b;
            color: #ffffff;
        }

        /* Custom Jewellery Diamond Statuses */
        .status-j_diamond_in_progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-j_diamond_completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-j_diamond_in_discuss {
            background: #cffafe;
            color: #0e7490;
        }

        .status-j_cad_in_progress {
            background: #fef3c7;
            color: #92400e;
        }

        .status-j_cad_done {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .status-j_order_completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-j_order_in_qc {
            background: #fef3c7;
            color: #92400e;
        }

        .status-j_qc_done {
            background: #d1fae5;
            color: #065f46;
        }

        .status-j_order_shipped {
            background: #1e293b;
            color: #ffffff;
        }

        .status-j_order_hold {
            background: #fee2e2;
            color: #991b1b;
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

        /* Client Info Table - Clean & Minimal */
        .client-info-table {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .info-row {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 1.5rem;
            padding: 0.875rem 0;
            border-bottom: 1px solid var(--border);
            align-items: start;
        }

        .info-row:first-child {
            padding-top: 0;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            display: flex;
            align-items: center;
        }

        .info-value {
            font-size: 0.9375rem;
            font-weight: 500;
            color: var(--dark);
            line-height: 1.6;
            word-break: break-word;
        }

        .info-row-address .info-value {
            white-space: pre-line;
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
            padding: 0.5rem 1.25rem;
            background: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
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
        }

        /* Tracking Timeline Styles */
        .section-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0.5rem 0 1rem 0;
            /* Added vertical padding */
            border-bottom: 2px solid var(--border);
            flex-wrap: wrap;
            /* Handle wrapping cases */
            gap: 1rem;
        }

        .section-header-flex .section-title {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .btn-sync-tracking {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
            white-space: nowrap;
        }

        .btn-sync-tracking:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3);
            filter: brightness(1.1);
        }

        .btn-sync-tracking i {
            font-size: 1rem;
            transition: transform 0.5s ease;
        }

        .btn-sync-tracking:active i {
            transform: rotate(180deg);
        }

        .tracking-history-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .tracking-history-timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border);
        }

        .tracking-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .tracking-item:last-child {
            padding-bottom: 0;
        }

        .tracking-point {
            position: absolute;
            left: -2rem;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            border: 3px solid var(--primary);
            z-index: 1;
        }

        .tracking-item:first-child .tracking-point {
            background: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
        }

        .tracking-info {
            background: var(--light);
            padding: 1rem;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .tracking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .tracking-status {
            font-weight: 700;
            color: var(--dark);
            font-size: 0.9375rem;
        }

        .tracking-date {
            font-size: 0.75rem;
            color: var(--gray);
            font-weight: 500;
        }

        /* Status Colors for Shipping Badge in Show Page */
        .status-badge.status-delivered {
            background: #dcfce7 !important;
            color: #166534 !important;
        }

        .status-badge.status-in_transit {
            background: #fef3c7 !important;
            color: #92400e !important;
        }

        .status-badge.status-picked_up {
            background: #dbeafe !important;
            color: #1e40af !important;
        }

        .status-badge.status-exception {
            background: #fee2e2 !important;
            color: #991b1b !important;
        }

        .status-badge.status-unknown {
            background: #f1f5f9 !important;
            color: #475569 !important;
        }

        .tracking-location {
            font-size: 0.8125rem;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .tracking-desc {
            font-size: 0.875rem;
            color: var(--secondary);
            line-height: 1.4;
        }

        /* Status Colors for Tracker */
        .status-badge.status-shipped {
            background: #e0e7ff;
            color: #4338ca;
        }

        .status-badge.status-delivered {
            background: #dcfce7;
            color: #15803d;
        }

        .status-badge.status-in_transit {
            background: #fef9c3;
            color: #854d0e;
        }

        .status-badge.status-pickup {
            background: #f3f4f6;
            color: #374151;
        }

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

        @media (max-width: 575px) {
            .order-details-wrapper {
                padding: 0;
                padding-top: 7px;
            }

            .order-title {
                font-size: 16px;
            }

            .order-date {
                font-size: 12px;
            }

            button.btn-action.btn-back,
            a.btn-action.btn-edit {
                padding: 5px 9px;
                font-size: 12px;
            }

            .order-header {
                margin-bottom: 15px;
                padding-bottom: 10px;
            }

            .status-card {
                padding: 7px;
                gap: 10px;
            }

            span.status-badge.status-r_order_in_process {
                background-color: #ddfedb;
            }

            .status-label {
                font-size: 11px;
            }

            span.status-badge,
            .status-value {
                font-size: 10px;
            }

            .section-title,
            .section-content {
                padding: 7px;
            }

            .detail-row {
                font-size: 13px;
            }
        }

        /* Status Date (for Last Edited By card) */
        .status-date {
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 2px;
        }

        /* ===== Edit History Timeline ===== */
        .edit-timeline {
            position: relative;
            padding-left: 24px;
        }

        .edit-timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 12px;
            bottom: 12px;
            width: 2px;
            background: var(--border);
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -20px;
            top: 6px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            border: 2px solid var(--white);
            box-shadow: 0 0 0 2px var(--primary-light);
            z-index: 1;
        }

        .timeline-item:first-child .timeline-dot {
            background: var(--success);
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
        }

        .timeline-content {
            background: var(--light);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.875rem 1rem;
        }

        .timeline-header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .timeline-admin {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--dark);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .timeline-admin i {
            color: var(--primary);
        }

        .timeline-time {
            font-size: 0.8rem;
            color: var(--gray);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .timeline-ago {
            font-size: 0.75rem;
            color: var(--secondary);
            background: var(--white);
            border: 1px solid var(--border);
            padding: 0.125rem 0.5rem;
            border-radius: 999px;
        }

        .timeline-changes {
            margin-top: 0.5rem;
        }

        .changes-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .changes-toggle:hover {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .toggle-icon {
            transition: transform 0.2s;
        }

        .timeline-changes.expanded .toggle-icon {
            transform: rotate(180deg);
        }

        .changes-detail {
            display: none;
            margin-top: 0.5rem;
        }

        .timeline-changes.expanded .changes-detail {
            display: block;
        }

        .changes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
            background: var(--white);
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .changes-table th {
            background: var(--light);
            padding: 0.5rem 0.75rem;
            text-align: left;
            font-weight: 600;
            color: var(--gray);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid var(--border);
        }

        .changes-table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .changes-table tr:last-child td {
            border-bottom: none;
        }

        .field-name {
            font-weight: 600;
            color: var(--dark);
            white-space: nowrap;
        }

        .val-badge {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            word-break: break-word;
            max-width: 250px;
        }

        .val-old {
            background: #fee2e2;
            color: #991b1b;
            text-decoration: line-through;
        }

        .val-new {
            background: #d1fae5;
            color: #065f46;
        }

        /* Responsive: timeline on mobile */
        @media (max-width: 575px) {
            .timeline-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .changes-table {
                font-size: 0.75rem;
            }

            .val-badge {
                max-width: 120px;
                font-size: 0.7rem;
            }
        }

        /* --- Tracking History Timeline (New) --- */
        .tracking-timeline-container {
            position: relative;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-top: 1.5rem;
        }

        .timeline-title {
            font-size: 1rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }

        .tracking-history-timeline {
            position: relative;
            padding-left: 2rem;
            border-left: 2px solid #cbd5e1;
            margin-left: 10px;
        }

        .tracking-item {
            position: relative;
            margin-bottom: 2rem;
        }

        .tracking-item:last-child {
            margin-bottom: 0;
        }

        .tracking-point {
            position: absolute;
            left: -2.6rem;
            top: 0.25rem;
            width: 16px;
            height: 16px;
            background: #fff;
            border: 3px solid #64748b;
            border-radius: 50%;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .tracking-item:first-child .tracking-point {
            border-color: #10b981;
            /* Green for latest */
            background: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
            transform: scale(1.1);
        }

        .tracking-info {
            background: #fff;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .tracking-item:hover .tracking-info {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .tracking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .tracking-status {
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e293b;
            text-transform: capitalize;
        }

        .tracking-date {
            font-size: 0.8rem;
            color: #64748b;
            font-family: monospace;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .tracking-location {
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .tracking-desc {
            font-size: 0.9rem;
            color: #4b5563;
            line-height: 1.5;
        }

        /* Tracking Status Badges Colors */
        .status-transit,
        .status-in_transit {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-delivered {
            background: #dcfce7;
            color: #15803d;
        }

        .status-pending {
            background: #f3f4f6;
            color: #374151;
        }

        .status-exception {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-not_found,
        .status-notfound {
            background: #f1f5f9;
            color: #64748b;
        }

        .status-info_received,
        .status-inforeceived {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-pickup {
            background: #ffedd5;
            color: #c2410c;
        }

        .status-out_for_delivery {
            background: #fae8ff;
            color: #86198f;
        }

        .status-failed_attempt {
            background: #ffe4e6;
            color: #be123c;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .bi-arrow-repeat.spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        /* Adjust button background for header context */
        .section-title .btn-action-sm {
            background: var(--white);
            border-color: var(--border);
        }

        .section-title .btn-action-sm:hover {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .btn-action-sm:hover i {
            color: var(--white);
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