@extends('layouts.admin')

@section('title', 'Jewellery Stock Details')

@push('styles')
    <style>
        .jewellery-show {
            --js-card-radius: 16px;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .jewellery-show .jewellery-head {
            margin-bottom: 0;
        }

        .jewellery-show .header-content {
            align-items: center;
        }

        .jewellery-show .page-title {
            font-size: 1.75rem;
            line-height: 1.15;
            margin-bottom: 0.25rem;
        }

        .jewellery-show .product-board {
            display: grid;
            grid-template-columns: minmax(300px, 410px) minmax(360px, 1fr) minmax(280px, 340px);
            gap: 1.25rem;
            padding: 1.25rem;
            border: 1px solid rgba(226, 232, 240, 0.75);
        }

        .jewellery-show .gallery-panel,
        .jewellery-show .product-panel,
        .jewellery-show .commercial-panel,
        .jewellery-show .detail-card {
            min-width: 0;
        }

        .jewellery-show .gallery-panel {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .jewellery-show .hero-image {
            aspect-ratio: 1 / 1;
            width: 100%;
            overflow: hidden;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: linear-gradient(135deg, #f8fafc, #eef2ff);
            cursor: pointer;
        }

        .jewellery-show .hero-image img,
        .jewellery-show .thumb-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .jewellery-show .image-empty {
            height: 100%;
            min-height: 260px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            color: var(--gray);
            text-align: center;
        }

        .jewellery-show .image-empty i {
            color: var(--primary);
            font-size: 2.4rem;
        }

        .jewellery-show .thumb-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.55rem;
        }

        .jewellery-show .thumb-btn {
            aspect-ratio: 1;
            padding: 0;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            cursor: pointer;
            transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .jewellery-show .thumb-btn:hover {
            border-color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(99, 102, 241, 0.16);
        }

        .jewellery-show .product-panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 0.25rem 0.25rem 0.25rem 0;
        }

        .jewellery-show .identity-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .jewellery-show .item-name {
            margin: 0;
            color: var(--dark);
            font-size: 1.65rem;
            line-height: 1.18;
            font-weight: 850;
            overflow-wrap: anywhere;
        }

        .jewellery-show .sku-text {
            margin: 0.35rem 0 0;
            color: var(--gray);
            font-size: 0.92rem;
        }

        .jewellery-show .badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .jewellery-show .quick-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .jewellery-show .stat-tile,
        .jewellery-show .fact-tile,
        .jewellery-show .stone-box,
        .jewellery-show .cert-box,
        .jewellery-show .record-box {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #f8fafc;
            min-width: 0;
        }

        .jewellery-show .stat-tile {
            padding: 0.9rem;
        }

        .jewellery-show .tile-label,
        .jewellery-show .fact-label {
            margin: 0 0 0.28rem;
            color: var(--gray);
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .jewellery-show .tile-value,
        .jewellery-show .fact-value {
            margin: 0;
            color: var(--dark);
            font-size: 0.98rem;
            font-weight: 750;
            overflow-wrap: anywhere;
        }

        .jewellery-show .fact-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .jewellery-show .fact-tile {
            padding: 0.85rem;
        }

        .jewellery-show .commercial-panel {
            align-self: stretch;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1.15rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #f8fafc;
            min-width: 0;
        }

        .jewellery-show .commercial-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .jewellery-show .commercial-title {
            margin: 0;
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 850;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .jewellery-show .retail-label {
            margin: 0;
            color: var(--gray);
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .jewellery-show .retail-value {
            margin: 0.15rem 0 0;
            color: var(--dark);
            font-size: 2rem;
            line-height: 1.08;
            font-weight: 900;
        }

        .jewellery-show .money-lines {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .jewellery-show .money-line {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            font-size: 0.92rem;
        }

        .jewellery-show .money-line span:first-child {
            color: var(--gray);
        }

        .jewellery-show .money-line span:last-child {
            color: var(--dark);
            font-weight: 800;
        }

        .jewellery-show .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, 380px);
            gap: 1.25rem;
            align-items: start;
        }

        .jewellery-show .left-stack,
        .jewellery-show .right-stack {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            min-width: 0;
        }

        .jewellery-show .right-stack {
            position: sticky;
            top: 96px;
        }

        .jewellery-show .detail-card {
            overflow: hidden;
        }

        .jewellery-show .card-head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, #f8fafc, #fff);
        }

        .jewellery-show .card-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex: 0 0 auto;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .jewellery-show .card-title {
            margin: 0;
            color: var(--dark);
            font-size: 1rem;
            font-weight: 850;
        }

        .jewellery-show .card-body {
            padding: 1.15rem;
        }

        .jewellery-show .spec-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .jewellery-show .stone-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .jewellery-show .stone-box {
            padding: 1rem;
        }

        .jewellery-show .stone-box.primary {
            background: rgba(99, 102, 241, 0.055);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .jewellery-show .stone-title {
            margin: 0 0 0.9rem;
            color: var(--primary);
            font-size: 0.76rem;
            font-weight: 850;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .jewellery-show .stone-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .jewellery-show .description-copy {
            margin: 0;
            color: var(--gray);
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .jewellery-show .variant-list {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
        }

        .jewellery-show .variant-card {
            padding: 0.85rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
        }

        .jewellery-show .variant-card.is-default {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.22);
        }

        .jewellery-show .variant-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }

        .jewellery-show .variant-name {
            color: var(--dark);
            font-weight: 850;
        }

        .jewellery-show .variant-price {
            color: var(--primary);
            font-weight: 900;
            white-space: nowrap;
        }

        .jewellery-show .variant-meta {
            margin-top: 0.35rem;
            color: var(--gray);
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .jewellery-show .cert-box,
        .jewellery-show .record-box {
            padding: 1rem;
        }

        .jewellery-show .empty-state {
            padding: 1.25rem;
            text-align: center;
            color: var(--muted);
            border: 1px dashed var(--border);
            border-radius: 12px;
            background: #f8fafc;
        }

        .jewellery-show .mono {
            font-family: "JetBrains Mono", Consolas, monospace;
            font-size: 0.92rem;
        }

        .image-viewer-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: rgba(2, 6, 23, 0.86);
        }

        .image-viewer-modal.active {
            display: flex;
        }

        .image-viewer-card {
            width: min(1100px, 100%);
            max-height: 90vh;
            border-radius: 16px;
            overflow: hidden;
            background: var(--bg-card);
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);
        }

        .image-viewer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg-card);
        }

        .image-viewer-header h3 {
            margin: 0;
            color: var(--dark);
            font-size: 1rem;
            font-weight: 850;
        }

        .image-viewer-close {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 8px;
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .image-viewer-body {
            height: min(76vh, 760px);
            background: #020617;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-viewer-body img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        [data-theme="dark"] .jewellery-show .product-board,
        [data-theme="dark"] .jewellery-show .detail-card {
            border-color: var(--border);
        }

        [data-theme="dark"] .jewellery-show .hero-image,
        [data-theme="dark"] .jewellery-show .thumb-btn,
        [data-theme="dark"] .jewellery-show .stat-tile,
        [data-theme="dark"] .jewellery-show .fact-tile,
        [data-theme="dark"] .jewellery-show .commercial-panel,
        [data-theme="dark"] .jewellery-show .stone-box,
        [data-theme="dark"] .jewellery-show .cert-box,
        [data-theme="dark"] .jewellery-show .record-box,
        [data-theme="dark"] .jewellery-show .variant-card,
        [data-theme="dark"] .jewellery-show .empty-state {
            background: rgba(15, 23, 42, 0.42);
            border-color: var(--border);
        }

        [data-theme="dark"] .jewellery-show .card-head {
            background: rgba(255, 255, 255, 0.03);
        }

        [data-theme="dark"] .jewellery-show .item-name,
        [data-theme="dark"] .jewellery-show .tile-value,
        [data-theme="dark"] .jewellery-show .fact-value,
        [data-theme="dark"] .jewellery-show .card-title,
        [data-theme="dark"] .jewellery-show .variant-name,
        [data-theme="dark"] .jewellery-show .retail-value,
        [data-theme="dark"] .jewellery-show .money-line span:last-child {
            color: #f8fafc;
        }

        @media (max-width: 1380px) {
            .jewellery-show .product-board {
                grid-template-columns: minmax(280px, 380px) minmax(0, 1fr);
            }

            .jewellery-show .commercial-panel {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 1100px) {
            .jewellery-show .product-board,
            .jewellery-show .content-grid {
                grid-template-columns: 1fr;
            }

            .jewellery-show .right-stack {
                position: static;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .jewellery-show {
                gap: 1rem;
            }

            .jewellery-show .product-board,
            .jewellery-show .card-body {
                padding: 1rem;
            }

            .jewellery-show .product-panel {
                padding: 0;
            }

            .jewellery-show .identity-row {
                flex-direction: column;
            }

            .jewellery-show .item-name {
                font-size: 1.35rem;
            }

            .jewellery-show .quick-stats,
            .jewellery-show .fact-grid,
            .jewellery-show .spec-grid,
            .jewellery-show .stone-grid,
            .jewellery-show .stone-fields,
            .jewellery-show .right-stack {
                grid-template-columns: 1fr;
            }

            .jewellery-show .retail-value {
                font-size: 1.6rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $currentAdmin = auth()->guard('admin')->user();
        $canViewPricing =
            $currentAdmin && ($currentAdmin->is_super || $currentAdmin->hasPermission('jewellery_stock.view_pricing'));
        $canViewProfit =
            $currentAdmin && ($currentAdmin->is_super || $currentAdmin->hasPermission('jewellery_stock.view_profit'));
        $images = collect($jewelleryStock->images ?? [])
            ->filter(fn ($image) => is_array($image) && !empty($image['url']))
            ->values();

        if ($images->isEmpty() && $jewelleryStock->image_url) {
            $images = collect([['url' => $jewelleryStock->image_url]]);
        }

        $primaryImage = $images->first()['url'] ?? null;
        $statusClass = match ($jewelleryStock->status) {
            'in_stock' => 'tracker-badge-success',
            'low_stock' => 'tracker-badge-warning',
            default => 'tracker-badge-danger',
        };
        $statusIcon = match ($jewelleryStock->status) {
            'in_stock' => 'bi-check-circle',
            'low_stock' => 'bi-exclamation-triangle',
            default => 'bi-x-circle',
        };
        $statusLabel = match ($jewelleryStock->status) {
            'in_stock' => 'In Stock',
            'low_stock' => 'Low Stock',
            default => 'Out of Stock',
        };
        $margin = (float) $jewelleryStock->selling_price - (float) $jewelleryStock->purchase_price;
        $marginPct = (float) $jewelleryStock->selling_price > 0
            ? ($margin / (float) $jewelleryStock->selling_price) * 100
            : 0;
        $metalLabel = $jewelleryStock->metalType->name ?? '';
        $discountPercent = (float) $jewelleryStock->discount_percent;
    @endphp

    <div class="tracker-page jewellery-show">
        <div class="page-header jewellery-head">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('jewellery-stock.index') }}" class="breadcrumb-link">Jewellery Stock</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">{{ $jewelleryStock->sku }}</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-gem" style="color: #8b5cf6;"></i>
                        {{ $jewelleryStock->name }}
                    </h1>
                    <p class="page-subtitle">SKU: {{ $jewelleryStock->sku }}</p>
                </div>
                <div class="header-right">
                    <div class="tracker-actions-row">
                        <a href="{{ route('jewellery-stock.edit', $jewelleryStock) }}" class="btn-primary-custom">
                            <i class="bi bi-pencil"></i>
                            <span>Edit Item</span>
                        </a>
                        <a href="{{ route('jewellery-stock.index') }}" class="btn-secondary-custom">
                            <i class="bi bi-arrow-left"></i>
                            <span>Back to List</span>
                        </a>
                    </div>
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

        <section class="tracker-table-card product-board">
            <div class="gallery-panel">
                <div class="hero-image" @if ($primaryImage) onclick='openImageViewer(@json($primaryImage), @json($jewelleryStock->name))' @endif>
                    @if ($primaryImage)
                        <img src="{{ $primaryImage }}" alt="{{ $jewelleryStock->name }}">
                    @else
                        <div class="image-empty">
                            <i class="bi bi-image"></i>
                            <span>No product image uploaded</span>
                        </div>
                    @endif
                </div>

                @if ($images->count() > 1)
                    <div class="thumb-strip">
                        @foreach ($images->take(8) as $index => $image)
                            <button type="button" class="thumb-btn"
                                onclick='openImageViewer(@json($image['url']), @json($jewelleryStock->name . ' image ' . ($index + 1)))'>
                                <img src="{{ $image['url'] }}" alt="{{ $jewelleryStock->name }} thumbnail {{ $index + 1 }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="product-panel">
                <div class="identity-row">
                    <div>
                        <h2 class="item-name">{{ $jewelleryStock->name }}</h2>
                        <p class="sku-text">SKU: <strong>{{ $jewelleryStock->sku }}</strong></p>
                    </div>
                    <span class="tracker-badge {{ $statusClass }}">
                        <i class="bi {{ $statusIcon }}"></i>
                        {{ $statusLabel }} ({{ $jewelleryStock->quantity }})
                    </span>
                </div>

                <div class="badge-row">
                    <span class="tracker-badge tracker-badge-primary">
                        <i class="bi bi-collection"></i>
                        {{ ucwords(str_replace('_', ' ', $jewelleryStock->type)) }}
                    </span>
                    @if($jewelleryStock->metal_type_id)
                        <span class="tracker-badge tracker-badge-secondary">
                            <i class="bi bi-palette"></i>
                            {{ $jewelleryStock->metalType->name }}
                        </span>
                    @endif
                    @if ($jewelleryStock->certificate_type)
                        <span class="tracker-badge tracker-badge-success">
                            <i class="bi bi-patch-check"></i>
                            {{ $jewelleryStock->certificate_type }} Certified
                        </span>
                    @endif
                </div>

                <div class="quick-stats">
                    <div class="stat-tile">
                        <p class="tile-label">Retail Price</p>
                        @if ($discountPercent > 0)
                            <p class="tile-value" style="font-size: 0.8rem; text-decoration: line-through; color: var(--gray); margin-bottom: 0;">
                                ${{ number_format((float) $jewelleryStock->selling_price, 2) }}
                            </p>
                            <p class="tile-value" style="color: #10b981;">
                                ${{ number_format((float) $jewelleryStock->discounted_price, 2) }}
                            </p>
                        @else
                            <p class="tile-value">${{ number_format((float) $jewelleryStock->selling_price, 2) }}</p>
                        @endif
                    </div>
                    @if($jewelleryStock->weight)
                        <div class="stat-tile">
                            <p class="tile-label">Gross Weight</p>
                            <p class="tile-value">{{ number_format((float) $jewelleryStock->weight, 3) }} g</p>
                        </div>
                    @endif
                    <div class="stat-tile">
                        <p class="tile-label">Stock Alert</p>
                        <p class="tile-value">{{ $jewelleryStock->low_stock_threshold }} pcs</p>
                    </div>
                </div>

                <div class="fact-grid">
                    @if($jewelleryStock->metal_type_id)
                        <div class="fact-tile">
                            <p class="fact-label">Metal Composition</p>
                            <p class="fact-value">{{ $metalLabel }}</p>
                        </div>
                    @endif
                    @if($jewelleryStock->ring_size_id)
                        <div class="fact-tile">
                            <p class="fact-label">Ring Size</p>
                            <p class="fact-value">{{ $jewelleryStock->ringSize->name }}</p>
                        </div>
                    @endif
                    @if($jewelleryStock->width)
                        <div class="fact-tile">
                            <p class="fact-label">Width</p>
                            <p class="fact-value">{{ $jewelleryStock->width }} mm</p>
                        </div>
                    @endif
                    @if($jewelleryStock->closure_type_id)
                        <div class="fact-tile">
                            <p class="fact-label">Closure / Backing</p>
                            <p class="fact-value">{{ $jewelleryStock->closureType->name }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <aside class="commercial-panel">
                <div>
                    <div class="commercial-head">
                        <h3 class="commercial-title">Commercial Data</h3>
                        <i class="bi bi-tag-fill" style="color: #10b981;"></i>
                    </div>
                    <div style="margin-top: 1rem;">
                        <p class="retail-label">Retail Price</p>
                        @if ($discountPercent > 0)
                            <p class="retail-value" style="font-size: 1.2rem; text-decoration: line-through; color: var(--gray); margin-bottom: 0.2rem;">
                                ${{ number_format((float) $jewelleryStock->selling_price, 2) }}
                            </p>
                            <p class="retail-value" style="color: #10b981;">
                                ${{ number_format((float) $jewelleryStock->discounted_price, 2) }}
                                <span style="font-size: 0.85rem; font-weight: 700; padding: 0.2rem 0.5rem; background: #10b981; color: #fff; border-radius: 6px; margin-left: 0.5rem; vertical-align: middle;">
                                    -{{ number_format($discountPercent, 0) }}%
                                </span>
                            </p>
                        @else
                            <p class="retail-value">${{ number_format((float) $jewelleryStock->selling_price, 2) }}</p>
                        @endif
                    </div>
                </div>

                <div class="money-lines">
                    <div class="money-line">
                        <span>Cost</span>
                        <span>
                            @if ($canViewPricing)
                                ${{ number_format((float) $jewelleryStock->purchase_price, 2) }}
                            @else
                                Restricted
                            @endif
                        </span>
                    </div>
                    @if ($canViewProfit)
                        <div class="money-line">
                            <span>Profit</span>
                            <span style="color: {{ $margin >= 0 ? '#10b981' : '#ef4444' }};">
                                {{ $margin >= 0 ? '+' : '-' }}${{ number_format(abs($margin), 2) }}
                                ({{ number_format($marginPct, 1) }}%)
                            </span>
                        </div>
                    @endif
                </div>
            </aside>
        </section>

        <div class="content-grid">
            <main class="left-stack">
                <section class="tracker-table-card detail-card">
                    <div class="card-head">
                        <span class="card-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                            <i class="bi bi-rulers"></i>
                        </span>
                        <h3 class="card-title">Physical Dimensions</h3>
                    </div>
                    <div class="card-body">
                        <div class="spec-grid">
                            @if($jewelleryStock->ring_size_id)
                                <div class="fact-tile">
                                    <p class="fact-label">Ring Size</p>
                                    <p class="fact-value">{{ $jewelleryStock->ringSize->name }}</p>
                                </div>
                            @endif
                            @if($jewelleryStock->length)
                                <div class="fact-tile">
                                    <p class="fact-label">Length</p>
                                    <p class="fact-value">{{ $jewelleryStock->length }} in/cm</p>
                                </div>
                            @endif
                            @if($jewelleryStock->width)
                                <div class="fact-tile">
                                    <p class="fact-label">Width</p>
                                    <p class="fact-value">{{ $jewelleryStock->width }} mm</p>
                                </div>
                            @endif
                            @if($jewelleryStock->diameter)
                                <div class="fact-tile">
                                    <p class="fact-label">Diameter</p>
                                    <p class="fact-value">{{ $jewelleryStock->diameter }} mm</p>
                                </div>
                            @endif
                            @if($jewelleryStock->bale_size)
                                <div class="fact-tile">
                                    <p class="fact-label">Bale Size</p>
                                    <p class="fact-value">{{ $jewelleryStock->bale_size }} mm</p>
                                </div>
                            @endif
                            <div class="fact-tile">
                                <p class="fact-label">Quantity</p>
                                <p class="fact-value">{{ $jewelleryStock->quantity }} pcs</p>
                            </div>
                        </div>
                    </div>
                </section>

                @if ($jewelleryStock->primary_stone_type_id || $jewelleryStock->sideStones->isNotEmpty())
                    <section class="tracker-table-card detail-card">
                        <div class="card-head">
                            <span class="card-icon" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                                <i class="bi bi-diamond-half"></i>
                            </span>
                            <h3 class="card-title">Component Gemstones</h3>
                        </div>
                        <div class="card-body">
                            <div class="stone-grid">
                                @if ($jewelleryStock->primary_stone_type_id)
                                    <div class="stone-box primary">
                                        <h4 class="stone-title">Primary Stone</h4>
                                        <div class="stone-fields">
                                            <div>
                                                <p class="fact-label">Type</p>
                                                <p class="fact-value">{{ $jewelleryStock->primaryStoneType->name }}</p>
                                            </div>
                                            @if($jewelleryStock->primary_stone_weight)
                                                <div>
                                                    <p class="fact-label">Weight</p>
                                                    <p class="fact-value">{{ $jewelleryStock->primary_stone_weight }} cts</p>
                                                </div>
                                            @endif
                                            @if($jewelleryStock->primary_stone_shape_id)
                                                <div>
                                                    <p class="fact-label">Shape</p>
                                                    <p class="fact-value">{{ $jewelleryStock->primaryStoneShape->name }}</p>
                                                </div>
                                            @endif
                                            @if($jewelleryStock->primary_stone_cut_id)
                                                <div>
                                                    <p class="fact-label">Cut Grade</p>
                                                    <p class="fact-value">{{ $jewelleryStock->primaryStoneCut->name }}</p>
                                                </div>
                                            @endif
                                            @if($jewelleryStock->primary_stone_color_id)
                                                <div>
                                                    <p class="fact-label">Color</p>
                                                    <p class="fact-value">{{ $jewelleryStock->primaryStoneColor->name }}</p>
                                                </div>
                                            @endif
                                            @if($jewelleryStock->primary_stone_clarity_id)
                                                <div>
                                                    <p class="fact-label">Clarity</p>
                                                    <p class="fact-value">{{ $jewelleryStock->primaryStoneClarity->name }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @foreach($jewelleryStock->sideStones as $stone)
                                    <div class="stone-box">
                                        <h4 class="stone-title">Secondary / Side Stone</h4>
                                        <div class="stone-fields">
                                            <div>
                                                <p class="fact-label">Type</p>
                                                <p class="fact-value">{{ $stone->type->name }}</p>
                                            </div>
                                            @if($stone->weight)
                                                <div>
                                                    <p class="fact-label">Weight</p>
                                                    <p class="fact-value">{{ $stone->weight }} cts</p>
                                                </div>
                                            @endif
                                            @if($stone->count)
                                                <div>
                                                    <p class="fact-label">Count</p>
                                                    <p class="fact-value">{{ $stone->count }} stones</p>
                                                </div>
                                            @endif
                                            @if($stone->stone_shape_id)
                                                <div>
                                                    <p class="fact-label">Shape</p>
                                                    <p class="fact-value">{{ $stone->shape->name }}</p>
                                                </div>
                                            @endif
                                            @if($stone->stone_cut_id)
                                                <div>
                                                    <p class="fact-label">Cut</p>
                                                    <p class="fact-value">{{ $stone->cut->name }}</p>
                                                </div>
                                            @endif
                                            @if($stone->stone_color_id || $stone->stone_clarity_id)
                                                <div>
                                                    <p class="fact-label">Color / Clarity</p>
                                                    <p class="fact-value">
                                                        @if($stone->stone_color_id && $stone->stone_clarity_id)
                                                            {{ $stone->color->name }} / {{ $stone->clarity->name }}
                                                        @elseif($stone->stone_color_id)
                                                            {{ $stone->color->name }}
                                                        @else
                                                            {{ $stone->clarity->name }}
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                @if ($jewelleryStock->description)
                    <section class="tracker-table-card detail-card">
                        <div class="card-head">
                            <span class="card-icon" style="background: linear-gradient(135deg, #64748b, #475569);">
                                <i class="bi bi-card-text"></i>
                            </span>
                            <h3 class="card-title">Detailed Product Description</h3>
                        </div>
                        <div class="card-body">
                            <p class="description-copy">{!! nl2br(e($jewelleryStock->description)) !!}</p>
                        </div>
                    </section>
                @endif
            </main>

            <aside class="right-stack">
                @if ($jewelleryStock->pricingVariants->isNotEmpty())
                    <section class="tracker-table-card detail-card">
                        <div class="card-head">
                            <span class="card-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="bi bi-calculator"></i>
                            </span>
                            <h3 class="card-title">Pricing Variants</h3>
                        </div>
                        <div class="card-body">
                            <div class="variant-list">
                                @foreach ($jewelleryStock->pricingVariants as $variant)
                                    <div class="variant-card {{ $variant->is_default_listing ? 'is-default' : '' }}">
                                        <div class="variant-row">
                                            <span class="variant-name">{{ $variant->variant_label }}</span>
                                            <div style="text-align: right;">
                                                @if($discountPercent > 0)
                                                    <div style="font-size: 0.75rem; text-decoration: line-through; color: var(--gray);">${{ number_format((float) $variant->listing_price, 2) }}</div>
                                                    <span class="variant-price" style="color: #10b981;">${{ number_format((float) $variant->discounted_price, 2) }}</span>
                                                @else
                                                    <span class="variant-price">${{ number_format((float) $variant->listing_price, 2) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="variant-meta">
                                            Wt {{ number_format((float) $variant->net_weight_grams, 3) }}g
                                            @if ($canViewPricing)
                                                | Cost ${{ number_format((float) $variant->subtotal_cost, 2) }}
                                            @endif
                                            @if ($canViewProfit)
                                                | Profit ${{ number_format((float) $variant->profit_amount, 2) }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                @if ($jewelleryStock->certificate_type)
                    <section class="tracker-table-card detail-card">
                        <div class="card-head">
                            <span class="card-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                                <i class="bi bi-patch-check"></i>
                            </span>
                            <h3 class="card-title">Certification</h3>
                        </div>
                        <div class="card-body">
                            <div class="cert-box">
                                <p class="fact-label">Certificate</p>
                                <p class="fact-value">{{ $jewelleryStock->certificate_type }}</p>
                                @if($jewelleryStock->certificate_number)
                                    <p class="fact-value mono" style="margin-top: 0.35rem;">#{{ $jewelleryStock->certificate_number }}</p>
                                @endif
                                @if ($jewelleryStock->certificate_url)
                                    <a href="{{ $jewelleryStock->certificate_url }}" target="_blank"
                                        class="btn-secondary-custom" style="width: 100%; justify-content: center; margin-top: 0.85rem;">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        <span>View Certificate</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif

                <section class="tracker-table-card detail-card">
                    <div class="card-head">
                        <span class="card-icon" style="background: linear-gradient(135deg, #94a3b8, #64748b);">
                            <i class="bi bi-clock-history"></i>
                        </span>
                        <h3 class="card-title">Record Tracking</h3>
                    </div>
                    <div class="card-body">
                        <div class="record-box">
                            <p class="fact-label">Registered</p>
                            <p class="fact-value">{{ $jewelleryStock->created_at->format('d/m/Y H:i') }}</p>
                            <p class="fact-label" style="margin-top: 1rem;">Modified</p>
                            <p class="fact-value">{{ $jewelleryStock->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <div id="imageViewerModal" class="image-viewer-modal" onclick="closeImageViewer()">
        <div class="image-viewer-card" onclick="event.stopPropagation()">
            <div class="image-viewer-header">
                <h3 id="modalImageTitle">Image Preview</h3>
                <button type="button" class="image-viewer-close" onclick="closeImageViewer()" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="image-viewer-body">
                <img id="modalImageViewer" src="" alt="Image preview">
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openImageViewer(url, title) {
            const modal = document.getElementById('imageViewerModal');
            const viewer = document.getElementById('modalImageViewer');
            const heading = document.getElementById('modalImageTitle');

            if (!modal || !viewer || !heading || !url) return;

            viewer.src = url;
            heading.innerText = title || 'Image Preview';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeImageViewer() {
            const modal = document.getElementById('imageViewerModal');
            const viewer = document.getElementById('modalImageViewer');

            if (!modal || !viewer) return;

            modal.classList.remove('active');
            viewer.src = '';
            document.body.style.overflow = '';
        }

        function openImageModal(url, sku) {
            openImageViewer(url, 'SKU: ' + sku);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageViewer();
            }
        });
    </script>
@endpush
