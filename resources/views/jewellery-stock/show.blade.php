@extends('layouts.admin')
@section('title', 'Jewellery Stock Details')

@section('content')

    <div class="diamond-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <nav class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i>
                            <span>Dashboard</span>
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('jewellery-stock.index') }}" class="breadcrumb-link">
                            <span>Jewellery Stock</span>
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">{{ $jewelleryStock->sku }}</span>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <h1 class="page-title mb-0">
                            <i class="bi bi-gem"></i>
                            {{ $jewelleryStock->name }}
                        </h1>
                        <span class="status-pill {{ $jewelleryStock->quantity > 0 ? 'status-active' : 'status-inactive' }}">
                            <i class="bi bi-{{ $jewelleryStock->quantity > 0 ? 'check-circle' : 'x-circle' }}"></i>
                            {{ $jewelleryStock->quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    </div>
                </div>
                <div class="header-right">
                    <a href="{{ route('jewellery-stock.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                    <a href="{{ route('jewellery-stock.edit', $jewelleryStock) }}" class="btn-primary-custom">
                        <i class="bi bi-pencil"></i>
                        <span>Edit Item</span>
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($jewelleryStock->quantity <= $jewelleryStock->low_stock_threshold)
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2 fs-4 text-warning"></i>
                <div>
                    <strong>Low Stock Alert:</strong> This item is running low on stock. Current quantity:
                    {{ $jewelleryStock->quantity }}
                </div>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <!-- Left Column: Image -->
            <div class="col-12 col-lg-4">
                <div class="form-section-card h-100">
                    <div class="section-header">
                        <div class="section-info">
                            <div class="section-icon">
                                <i class="bi bi-image"></i>
                            </div>
                            <div>
                                <h5 class="section-title">Item Image</h5>
                            </div>
                        </div>
                    </div>
                    <div class="section-body text-center p-4">
                        @if ($jewelleryStock->image_url)
                            <img src="{{ $jewelleryStock->image_url }}" alt="{{ $jewelleryStock->name }}"
                                class="img-fluid rounded shadow-sm" style="max-height: 300px; object-fit: contain;">
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-center text-muted"
                                style="height: 250px; background: var(--bg-body, #f8fafc); border-radius: 0.5rem;">
                                <i class="bi bi-image" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p class="mb-0">No image available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="col-12 col-lg-8">
                <!-- Basic Details -->
                <div class="form-section-card mb-4">
                    <div class="section-header">
                        <div class="section-info">
                            <div class="section-icon">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <div>
                                <h5 class="section-title">Basic Information</h5>
                            </div>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <div class="form-label text-uppercase text-muted small fw-bold">SKU</div>
                                <div class="form-value font-monospace fw-medium">{{ $jewelleryStock->sku }}</div>
                            </div>
                            <div class="form-group">
                                <div class="form-label text-uppercase text-muted small fw-bold">Type</div>
                                <div class="form-value text-capitalize">{{ str_replace('_', ' ', $jewelleryStock->type) }}
                                </div>
                            </div>
                            <div class="form-group full-width">
                                <div class="form-label text-uppercase text-muted small fw-bold">Description</div>
                                <div class="form-value">{{ $jewelleryStock->description ?: 'No description provided.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specifications -->
                <div class="form-section-card mb-4">
                    <div class="section-header">
                        <div class="section-info">
                            <div class="section-icon">
                                <i class="bi bi-sliders"></i>
                            </div>
                            <div>
                                <h5 class="section-title">Specifications</h5>
                            </div>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <div class="form-label text-uppercase text-muted small fw-bold">Metal Type</div>
                                <div class="form-value">
                                    {{ $jewelleryStock->metalType ? $jewelleryStock->metalType->name : 'N/A' }}</div>
                            </div>
                            <div class="form-group">
                                <div class="form-label text-uppercase text-muted small fw-bold">Ring Size</div>
                                <div class="form-value">
                                    {{ $jewelleryStock->ringSize ? $jewelleryStock->ringSize->name : 'N/A' }}</div>
                            </div>
                            <div class="form-group">
                                <div class="form-label text-uppercase text-muted small fw-bold">Weight</div>
                                <div class="form-value">
                                    {{ $jewelleryStock->weight ? number_format($jewelleryStock->weight, 3) . ' g' : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory & Pricing -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-section-card h-100">
                            <div class="section-header">
                                <div class="section-info">
                                    <div class="section-icon">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div>
                                        <h5 class="section-title">Inventory</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="section-body">
                                <div class="form-grid" style="grid-template-columns: 1fr;">
                                    <div class="form-group d-flex justify-content-between align-items-center p-3 rounded"
                                        style="background: var(--bg-body, #f8fafc);">
                                        <div class="form-label mb-0 text-uppercase text-muted small fw-bold">Current Stock
                                        </div>
                                        <div
                                            class="form-value fw-bold fs-5 {{ $jewelleryStock->quantity <= $jewelleryStock->low_stock_threshold ? 'text-danger' : 'text-success' }}">
                                            {{ $jewelleryStock->quantity }} units
                                        </div>
                                    </div>
                                    <div class="form-group d-flex justify-content-between align-items-center p-3 rounded"
                                        style="background: var(--bg-body, #f8fafc);">
                                        <div class="form-label mb-0 text-uppercase text-muted small fw-bold">Low Stock
                                            Threshold</div>
                                        <div class="form-value">{{ $jewelleryStock->low_stock_threshold }} units</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-section-card h-100">
                            <div class="section-header">
                                <div class="section-info">
                                    <div class="section-icon">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <div>
                                        <h5 class="section-title">Pricing</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="section-body">
                                <div class="form-grid" style="grid-template-columns: 1fr;">
                                    <div class="form-group d-flex justify-content-between align-items-center p-3 rounded"
                                        style="background: var(--bg-body, #f8fafc);">
                                        <div class="form-label mb-0 text-uppercase text-muted small fw-bold">Purchase Price
                                        </div>
                                        <div class="form-value fw-semibold">
                                            ${{ number_format($jewelleryStock->purchase_price, 2) }}</div>
                                    </div>
                                    <div class="form-group d-flex justify-content-between align-items-center p-3 rounded"
                                        style="background: var(--bg-body, #f8fafc);">
                                        <div class="form-label mb-0 text-uppercase text-muted small fw-bold">Selling Price
                                        </div>
                                        <div class="form-value fw-bold text-primary fs-5">
                                            ${{ number_format($jewelleryStock->selling_price, 2) }}</div>
                                    </div>
                                    @if ($jewelleryStock->purchase_price > 0)
                                        @php
                                            $margin = $jewelleryStock->selling_price - $jewelleryStock->purchase_price;
                                            $marginPct =
                                                $jewelleryStock->purchase_price > 0
                                                    ? ($margin / $jewelleryStock->purchase_price) * 100
                                                    : 0;
                                        @endphp
                                        <div class="form-group d-flex justify-content-between align-items-center p-3 rounded mt-2"
                                            style="background: var(--bg-body, #f8fafc);">
                                            <div class="form-label mb-0 text-uppercase text-muted small fw-bold">Margin
                                            </div>
                                            <div class="form-value">
                                                <span class="badge {{ $margin >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                    ${{ number_format($margin, 2) }} ({{ number_format($marginPct, 1) }}%)
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Record Information -->
        <div class="form-section-card mb-4">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h5 class="section-title">Record Information</h5>
                        <p class="section-description">Audit trail</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <div class="form-label text-uppercase text-muted small fw-bold"><i
                                class="bi bi-calendar-plus me-1"></i> Created At</div>
                        <div class="form-value">{{ $jewelleryStock->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="form-group">
                        <div class="form-label text-uppercase text-muted small fw-bold"><i
                                class="bi bi-calendar-check me-1"></i> Last Updated</div>
                        <div class="form-value">{{ $jewelleryStock->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <style>
        /* Fix pill colors strictly according to diamond theme */
        [data-theme="dark"] .status-active {
            background: rgba(16, 185, 129, 0.15) !important;
            color: #34d399 !important;
            border-color: rgba(52, 211, 153, 0.3) !important;
        }

        [data-theme="dark"] .status-inactive {
            background: rgba(239, 68, 68, 0.15) !important;
            color: #f87171 !important;
            border-color: rgba(248, 113, 113, 0.3) !important;
        }
    </style>
@endsection
