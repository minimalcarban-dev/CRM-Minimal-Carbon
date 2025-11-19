@extends('layouts.admin')

@section('title', 'Diamond Details')

@section('content')
    <div class="diamond-details-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('diamond.index') }}" class="breadcrumb-link">Diamonds</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">#{{ $diamond->stockid }}</span>
                    </div>
                    <h1 class="page-title">
                        <div class="title-icon">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div class="title-content">
                            <span>Diamond Details</span>
                            <span class="title-badge">#{{ $diamond->stockid }}</span>
                        </div>
                    </h1>
                    <p class="page-subtitle">
                        <i class="bi bi-upc-scan"></i>
                        <strong>SKU:</strong> {{ $diamond->sku }}
                        <span class="subtitle-separator">•</span>
                        <i class="bi bi-calendar-check"></i>
                        {{ $diamond->created_at ? $diamond->created_at->format('M d, Y') : 'N/A' }}
                    </p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('diamond.edit', $diamond) }}" class="btn-action btn-primary">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit Diamond</span>
                    </a>
                    <a href="{{ route('diamond.index') }}" class="btn-action btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Key Metrics Cards -->
            <div class="metrics-grid">
                <div class="metric-card price-card">
                    <div class="metric-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Base Price</div>
                        <div class="metric-value">${{ number_format($diamond->price, 2) }}</div>
                        <div class="metric-badge">Current Value</div>
                    </div>
                </div>

                <div class="metric-card listing-card">
                    <div class="metric-icon">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Listing Price</div>
                        <div class="metric-value">${{ number_format($diamond->listing_price, 2) }}</div>
                        <div class="metric-badge">Public Price</div>
                    </div>
                </div>

                <div class="metric-card shape-card">
                    <div class="metric-icon">
                        <i class="bi bi-pentagon-fill"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Diamond Shape</div>
                        <div class="metric-value">{{ $diamond->shape ?? 'N/A' }}</div>
                        <div class="metric-badge">Cut Style</div>
                    </div>
                </div>

                <div class="metric-card type-card">
                    <div class="metric-icon">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Diamond Type</div>
                        <div class="metric-value">{{ $diamond->diamond_type ?? 'N/A' }}</div>
                        <div class="metric-badge">Classification</div>
                    </div>
                </div>
            </div>

            <!-- Main Details Grid -->
            <div class="details-layout">
                <!-- Left Column -->
                <div class="left-column">
                    <!-- Basic Information -->
                    <div class="detail-card">
                        <div class="card-header">
                            <div class="header-icon">
                                <i class="bi bi-info-circle-fill"></i>
                            </div>
                            <div class="header-text">
                                <h2 class="card-title">Basic Information</h2>
                                <p class="card-subtitle">Core diamond specifications</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="details-grid">
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="bi bi-hash"></i>
                                        <span>Stock ID</span>
                                    </div>
                                    <div class="detail-value">
                                        <span class="value-badge primary">{{ $diamond->stockid }}</span>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="bi bi-upc"></i>
                                        <span>SKU</span>
                                    </div>
                                    <div class="detail-value">{{ $diamond->sku }}</div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="bi bi-scissors"></i>
                                        <span>Cut Quality</span>
                                    </div>
                                    <div class="detail-value">{{ $diamond->cut ?? 'N/A' }}</div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="bi bi-rulers"></i>
                                        <span>Measurement</span>
                                    </div>
                                    <div class="detail-value">{{ $diamond->measurement ?? 'N/A' }}</div>
                                </div>
                            </div>

                            @if($diamond->description)
                                <div class="description-section">
                                    <div class="description-label">
                                        <i class="bi bi-file-text"></i>
                                        <span>Description</span>
                                    </div>
                                    <div class="description-content">
                                        {{ $diamond->description }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Assignment Details -->
                    <div class="detail-card">
                        <div class="card-header">
                            <div class="header-icon">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div class="header-text">
                                <h2 class="card-title">Assignment Details</h2>
                                <p class="card-subtitle">Admin tracking information</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="details-grid">
                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="bi bi-person-badge"></i>
                                        <span>Assigned Admin</span>
                                    </div>
                                    <div class="detail-value">
                                        @if($diamond->assignedAdmin)
                                            <div class="user-badge">
                                                <div class="user-avatar">
                                                    {{ substr($diamond->assignedAdmin->name, 0, 1) }}
                                                </div>
                                                <span>{{ $diamond->assignedAdmin->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="bi bi-person-plus"></i>
                                        <span>Assigned By</span>
                                    </div>
                                    <div class="detail-value">
                                        @if($diamond->assignedByAdmin)
                                            <div class="user-badge">
                                                <div class="user-avatar secondary">
                                                    {{ substr($diamond->assignedByAdmin->name, 0, 1) }}
                                                </div>
                                                <span>{{ $diamond->assignedByAdmin->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="bi bi-clock-history"></i>
                                        <span>Assignment Date</span>
                                    </div>
                                    <div class="detail-value">
                                        @if($diamond->assigned_at)
                                            <div class="date-badge success">
                                                <i class="bi bi-calendar-check-fill"></i>
                                                <span>{{ $diamond->assigned_at->format('M d, Y h:i A') }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-label">
                                        <i class="bi bi-upc-scan"></i>
                                        <span>Barcode Number</span>
                                    </div>
                                    <div class="detail-value">{{ $diamond->barcode_number ?? 'N/A' }}</div>
                                </div>
                            </div>

                            @if($diamond->barcode_image_url)
                                <div class="barcode-section">
                                    <div class="barcode-label">
                                        <i class="bi bi-qr-code"></i>
                                        <span>Barcode Image</span>
                                    </div>
                                    <div class="barcode-wrapper">
                                        <img src="{{ $diamond->barcode_image_url }}" alt="Barcode" class="barcode-image">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($diamond->note)
                        <div class="detail-card">
                            <div class="card-header">
                                <div class="header-icon">
                                    <i class="bi bi-sticky-fill"></i>
                                </div>
                                <div class="header-text">
                                    <h2 class="card-title">Notes</h2>
                                    <p class="card-subtitle">Additional information</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="note-content">
                                    <i class="bi bi-quote"></i>
                                    <p>{{ $diamond->note }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column - Images -->
                @if($diamond->multi_img_upload && count($diamond->multi_img_upload) > 0)
                    <div class="right-column">
                        <div class="detail-card images-card">
                            <div class="card-header">
                                <div class="header-icon">
                                    <i class="bi bi-images"></i>
                                </div>
                                <div class="header-text">
                                    <h2 class="card-title">Diamond Gallery</h2>
                                    <p class="card-subtitle">{{ count($diamond->multi_img_upload) }} images available</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="gallery-grid">
                                    @foreach($diamond->multi_img_upload as $index => $image)
                                        <div class="gallery-item" onclick="openImageModal('{{ $image }}', {{ $index }})">
                                            <div class="gallery-image-wrapper">
                                                <img src="{{ $image }}" alt="Diamond {{ $index + 1 }}" class="gallery-image">
                                                <div class="gallery-overlay">
                                                    <i class="bi bi-zoom-in"></i>
                                                    <span>View</span>
                                                </div>
                                            </div>
                                            <div class="gallery-label">Image {{ $index + 1 }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Image Modal -->
        <div id="imageModal" class="image-modal" onclick="closeImageModal()">
            <button class="modal-close" onclick="closeImageModal(); event.stopPropagation();">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="modal-navigation">
                <button class="nav-button prev" onclick="navigateImage(-1); event.stopPropagation();">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="nav-button next" onclick="navigateImage(1); event.stopPropagation();">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <img class="modal-image" id="modalImage" onclick="event.stopPropagation();">
            <div class="modal-counter" id="modalCounter"></div>
        </div>
    </div>

    <style>
        .form-value {
            margin: 0;
            font-size: 1rem;
            color: #333;
            padding: 8px 0;
        }

        .text-muted {
            color: #6c757d;
            font-style: italic;
        }
    </style>
@endsection