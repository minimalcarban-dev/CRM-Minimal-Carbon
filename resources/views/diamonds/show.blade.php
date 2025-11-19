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
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --success: #10b981;
            --success-dark: #059669;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --purple: #8b5cf6;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
            --shadow-md: rgba(0, 0, 0, 0.1);
            --shadow-lg: rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
        }

        .diamond-details-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid var(--border);
            animation: slideDown 0.5s ease forwards;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .header-left {
            flex: 1;
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1.25rem;
        }

        .breadcrumb-link {
            color: var(--gray);
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
        }

        .breadcrumb-link:hover {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .breadcrumb-separator {
            font-size: 0.75rem;
            color: var(--border);
        }

        .breadcrumb-current {
            color: var(--dark);
            font-weight: 600;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0 0 1rem 0;
        }

        .title-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
        }

        .title-content {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .title-content>span:first-child {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
        }

        .title-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            border: 2px solid rgba(99, 102, 241, 0.2);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
            width: fit-content;
        }

        .page-subtitle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            font-size: 0.95rem;
            color: var(--gray);
            margin: 0;
        }

        .page-subtitle i {
            color: var(--primary);
        }

        .page-subtitle strong {
            color: var(--dark);
            font-weight: 600;
        }

        .subtitle-separator {
            color: var(--border);
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--gray);
            border-color: var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        /* Content Wrapper */
        .content-wrapper {
            animation: slideUp 0.5s ease forwards;
            animation-delay: 0.1s;
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            border: 2px solid var(--border);
            transition: all 0.3s;
            box-shadow: 0 2px 10px var(--shadow);
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px var(--shadow-md);
        }

        .metric-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px var(--shadow-md);
        }

        .price-card .metric-icon {
            background: linear-gradient(135deg, var(--success), var(--success-dark));
        }

        .listing-card .metric-icon {
            background: linear-gradient(135deg, var(--info), #2563eb);
        }

        .shape-card .metric-icon {
            background: linear-gradient(135deg, var(--purple), #7c3aed);
        }

        .type-card .metric-icon {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        .metric-content {
            flex: 1;
        }

        .metric-label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .metric-badge {
            font-size: 0.75rem;
            color: var(--gray);
            font-weight: 500;
        }

        /* Details Layout */
        .details-layout {
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 2rem;
            align-items: start;
        }

        .left-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .right-column {
            position: sticky;
            top: 2rem;
        }

        /* Detail Card */
        .detail-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .card-header {
            padding: 1.75rem;
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.25rem 0;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0;
        }

        .card-body {
            padding: 2rem;
        }

        /* Details Grid */
        .details-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            gap: 1rem;
        }

        .detail-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }

        .detail-label i {
            font-size: 1rem;
            color: var(--primary);
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            text-align: right;
        }

        .value-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .value-badge.primary {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
            border: 2px solid rgba(99, 102, 241, 0.2);
        }

        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--light-gray);
            border-radius: 10px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .user-avatar.secondary {
            background: linear-gradient(135deg, var(--info), #2563eb);
        }

        .date-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .date-badge.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 2px solid rgba(16, 185, 129, 0.2);
        }

        .text-muted {
            color: var(--gray);
            font-style: italic;
            font-weight: 500;
        }

        /* Description Section */
        .description-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--border);
        }

        .description-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .description-label i {
            color: var(--primary);
            font-size: 1rem;
        }

        .description-content {
            padding: 1.25rem;
            background: linear-gradient(135deg, var(--light-gray), white);
            border-left: 4px solid var(--primary);
            border-radius: 10px;
            line-height: 1.7;
            color: var(--dark);
            font-size: 0.95rem;
        }

        /* Barcode Section */
        .barcode-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .barcode-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .barcode-label i {
            color: var(--primary);
            font-size: 1rem;
        }

        .barcode-wrapper {
            padding: 1.5rem;
            background: var(--light-gray);
            border: 2px solid var(--border);
            border-radius: 12px;
            display: inline-block;
        }

        .barcode-image {
            max-width: 300px;
            height: auto;
            display: block;
        }

        /* Note Content */
        .note-content {
            position: relative;
            padding: 1.5rem 1.5rem 1.5rem 3.5rem;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            border-left: 4px solid var(--warning);
            border-radius: 12px;
            line-height: 1.7;
            color: var(--dark);
        }

        .note-content i {
            position: absolute;
            left: 1.5rem;
            top: 1.5rem;
            font-size: 1.5rem;
            color: var(--warning);
            opacity: 0.5;
        }

        .note-content p {
            margin: 0;
            font-size: 0.95rem;
        }

        /* Gallery Grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .gallery-item {
            cursor: pointer;
            transition: all 0.3s;
        }

        .gallery-image-wrapper {
            position: relative;
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid var(--border);
            background: var(--light-gray);
            transition: all 0.3s;
        }

        .gallery-item:hover .gallery-image-wrapper {
            border-color: var(--primary);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
            transform: translateY(-4px);
        }

        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s;
        }

        .gallery-item:hover .gallery-image {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.95), rgba(79, 70, 229, 0.95));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            opacity: 0;
            transition: opacity 0.3s;
            color: white;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-overlay i {
            font-size: 2rem;
        }

        .gallery-overlay span {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .gallery-label {
            margin-top: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            text-align: center;
        }

        /* Image Modal */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            animation: fadeIn 0.3s ease;
        }

        .modal-close {
            position: absolute;
            top: 2rem;
            right: 2rem;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10002;
            font-size: 1.25rem;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg) scale(1.1);
        }

        .modal-navigation {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            padding: 0 2rem;
            pointer-events: none;
            z-index: 10002;
        }

        .nav-button {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            pointer-events: auto;
            font-size: 1.5rem;
        }

        .nav-button:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .nav-button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .modal-image {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 85%;
            max-height: 85%;
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: zoomIn 0.3s ease;
        }

        .modal-counter {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .details-layout {
                grid-template-columns: 1fr;
            }

            .right-column {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .diamond-details-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .header-content {
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                flex-direction: column;
                align-items: flex-start;
            }

            .title-content>span:first-child {
                font-size: 1.5rem;
            }

            .card-header {
                padding: 1.25rem;
            }

            .card-body {
                padding: 1.25rem;
            }

            .detail-row {
                flex-direction: column;
                gap: 0.5rem;
            }

            .detail-value {
                text-align: left;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .modal-image {
                max-width: 95%;
                max-height: 80%;
            }

            .modal-navigation {
                padding: 0 1rem;
            }

            .nav-button {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .modal-close {
                width: 40px;
                height: 40px;
                top: 1rem;
                right: 1rem;
            }
        }

        @media (max-width: 480px) {
            .page-subtitle {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .metric-card {
                padding: 1.25rem;
            }

            .metric-icon {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }

            .metric-value {
                font-size: 1.25rem;
            }
        }
    </style>

    <script>
        // Image modal functionality
        let currentImageIndex = 0;
        let images = @json($diamond->multi_img_upload ?? []);

        function openImageModal(imageSrc, index) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const counter = document.getElementById('modalCounter');

            currentImageIndex = index;
            modal.style.display = 'block';
            modalImg.src = imageSrc;
            document.body.style.overflow = 'hidden';

            updateCounter();
            updateNavigationButtons();
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function navigateImage(direction) {
            currentImageIndex += direction;

            if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1;
            } else if (currentImageIndex >= images.length) {
                currentImageIndex = 0;
            }

            const modalImg = document.getElementById('modalImage');
            modalImg.src = images[currentImageIndex];

            updateCounter();
            updateNavigationButtons();
        }

        function updateCounter() {
            const counter = document.getElementById('modalCounter');
            if (images.length > 0) {
                counter.textContent = `${currentImageIndex + 1} / ${images.length}`;
            }
        }

        function updateNavigationButtons() {
            const prevBtn = document.querySelector('.nav-button.prev');
            const nextBtn = document.querySelector('.nav-button.next');

            if (images.length <= 1) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
            } else {
                if (prevBtn) prevBtn.style.display = 'flex';
                if (nextBtn) nextBtn.style.display = 'flex';
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', function (event) {
            const modal = document.getElementById('imageModal');
            if (modal.style.display === 'block') {
                if (event.key === 'Escape') {
                    closeImageModal();
                } else if (event.key === 'ArrowLeft') {
                    navigateImage(-1);
                } else if (event.key === 'ArrowRight') {
                    navigateImage(1);
                }
            }
        });

        // Smooth scroll animations
        document.addEventListener('DOMContentLoaded', function () {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 50);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.detail-card, .metric-card').forEach((element) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'all 0.5s ease';
                observer.observe(element);
            });
        });
    </script>
@endsection