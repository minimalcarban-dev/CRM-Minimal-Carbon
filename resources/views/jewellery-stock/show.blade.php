@extends('layouts.admin')
@section('title', 'Jewellery Stock Details')
@push('scripts')
    <script>
        function openImageViewer(url, title) {
            document.getElementById('modalImageViewer').src = url;
            document.getElementById('modalImageTitle').innerText = title;
            document.getElementById('imageViewerModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeImageViewer() {
            document.getElementById('imageViewerModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // For compatibility
        function openImageModal(url, sku) {
            openImageViewer(url, 'SKU: ' + sku);
        }
    </script>
@endpush
@section('content')
    @php
        $currentAdmin = auth()->guard('admin')->user();
        $canViewPricing =
            $currentAdmin && ($currentAdmin->is_super || $currentAdmin->hasPermission('jewellery_stock.view_pricing'));
        $canViewProfit =
            $currentAdmin && ($currentAdmin->is_super || $currentAdmin->hasPermission('jewellery_stock.view_profit'));
    @endphp

    <div class="tracker-page">
        {{-- Page Header --}}
        <div class="page-header">
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
                    <div class="d-flex align-items-center gap-3"
                        style="display: flex; align-items: center; gap: 1rem; margin-top: 5px;">
                        <h1 class="page-title" style="margin: 0;">
                            <i class="bi bi-gem" style="color: #8b5cf6;"></i>
                            {{ $jewelleryStock->name }}
                        </h1>
                    </div>
                    <p class="page-subtitle">SKU: {{ $jewelleryStock->sku }}</p>
                </div>
                <div class="header-right">
                    <div class="tracker-actions-row">
                        <a href="{{ route('jewellery-stock.edit', $jewelleryStock) }}" class="btn-primary-custom">
                            <i class="bi bi-pencil"></i> Edit Item
                        </a>
                        <a href="{{ route('jewellery-stock.index') }}" class="btn-secondary-custom">
                            <i class="bi bi-arrow-left"></i> Back to List
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

        {{-- Status Badges --}}
        <div style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem;">
            @if ($jewelleryStock->quantity > 0 && $jewelleryStock->quantity > $jewelleryStock->low_stock_threshold)
                <span class="tracker-badge"
                    style="background: rgba(16, 185, 129, 0.1); color: #065f46; padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="bi bi-check-circle"></i> In Stock ({{ $jewelleryStock->quantity }})
                </span>
            @elseif ($jewelleryStock->quantity > 0 && $jewelleryStock->quantity <= $jewelleryStock->low_stock_threshold)
                <span class="tracker-badge"
                    style="background: rgba(245, 158, 11, 0.1); color: #b45309; padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="bi bi-exclamation-triangle"></i> Low Stock ({{ $jewelleryStock->quantity }})
                </span>
            @else
                <span class="tracker-badge"
                    style="background: rgba(239, 68, 68, 0.1); color: #991b1b; padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="bi bi-x-circle"></i> Out of Stock
                </span>
            @endif

            <span class="tracker-badge"
                style="background: rgba(99, 102, 241, 0.1); color: #4338ca; padding: 0.5rem 1rem; font-size: 0.9rem; text-transform: capitalize;">
                <i class="bi bi-collection"></i> {{ str_replace('_', ' ', $jewelleryStock->type) }}
            </span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 1.5rem; align-items: start;">
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                {{-- Specifications & Metal --}}
                <div class="tracker-table-card" style="padding: 1.5rem;">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-info-circle" style="color: #6366f1;"></i> Core Identification & Metal
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                        <div class="detail-item">
                            <div class="detail-label"
                                style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">SKU
                            </div>
                            <div class="detail-value"
                                style="font-size: 1.1rem; color: #1e293b; font-weight: 700; font-family: 'JetBrains Mono', monospace;">
                                {{ $jewelleryStock->sku }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"
                                style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">
                                Metal Composition</div>
                            <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                                {{ $jewelleryStock->metal_purity ?? '' }}
                                {{ $jewelleryStock->metalType ? $jewelleryStock->metalType->name : 'N/A' }}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"
                                style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">
                                Gross Weight</div>
                            <div class="detail-value" style="font-size: 1rem; color: #f59e0b; font-weight: 700;">
                                {{ $jewelleryStock->weight ? number_format($jewelleryStock->weight, 3) . ' g' : 'N/A' }}
                            </div>
                        </div>
                        @if ($jewelleryStock->closureType)
                            <div class="detail-item">
                                <div class="detail-label"
                                    style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">
                                    Closure Type</div>
                                <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">
                                    {{ $jewelleryStock->closureType->name }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Physical Dimensions --}}
                <div class="tracker-table-card" style="padding: 1.5rem;">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-rulers" style="color: #6366f1;"></i> Physical Dimensions
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">
                        @if ($jewelleryStock->ringSize)
                            <div class="detail-item">
                                <div class="detail-label"
                                    style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">
                                    Ring Size</div>
                                <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 600;">
                                    {{ $jewelleryStock->ringSize->name }}</div>
                            </div>
                        @endif
                        @if ($jewelleryStock->length)
                            <div class="detail-item">
                                <div class="detail-label"
                                    style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">
                                    Length</div>
                                <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 600;">
                                    {{ $jewelleryStock->length }} in/cm</div>
                            </div>
                        @endif
                        @if ($jewelleryStock->width)
                            <div class="detail-item">
                                <div class="detail-label"
                                    style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">
                                    Width</div>
                                <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 600;">
                                    {{ $jewelleryStock->width }} mm</div>
                            </div>
                        @endif
                        @if ($jewelleryStock->diameter)
                            <div class="detail-item">
                                <div class="detail-label"
                                    style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">
                                    Diameter</div>
                                <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 600;">
                                    {{ $jewelleryStock->diameter }} mm</div>
                            </div>
                        @endif
                        @if ($jewelleryStock->bale_size)
                            <div class="detail-item">
                                <div class="detail-label"
                                    style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">
                                    Bale Size</div>
                                <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 600;">
                                    {{ $jewelleryStock->bale_size }} mm</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Stone Details --}}
                @if ($jewelleryStock->primary_stone_type_id || $jewelleryStock->side_stone_type_id)
                    <div class="tracker-table-card" style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-diamond-half" style="color: #6366f1;"></i> Component Gemstones
                        </h3>

                        @if ($jewelleryStock->primary_stone_type_id)
                            <div
                                style="background: rgba(99, 102, 241, 0.05); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; border: 1px solid rgba(99, 102, 241, 0.1);">
                                <h4
                                    style="font-size: 0.85rem; color: #4338ca; margin: 0 0 1rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                                    Primary Stone</h4>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                    <div class="detail-item">
                                        <div class="detail-label" style="font-size: 0.7rem; color: #64748b;">Type</div>
                                        <div class="detail-value" style="font-weight: 600;">
                                            {{ $jewelleryStock->primaryStoneType->name }}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label" style="font-size: 0.7rem; color: #64748b;">Weight</div>
                                        <div class="detail-value" style="font-weight: 600;">
                                            {{ $jewelleryStock->primary_stone_weight }} cts</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label" style="font-size: 0.7rem; color: #64748b;">Shape</div>
                                        <div class="detail-value" style="font-weight: 600;">
                                            {{ $jewelleryStock->primaryStoneShape ? $jewelleryStock->primaryStoneShape->name : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label" style="font-size: 0.7rem; color: #64748b;">Color /
                                            Clarity</div>
                                        <div class="detail-value" style="font-weight: 600;">
                                            {{ $jewelleryStock->primaryStoneColor ? $jewelleryStock->primaryStoneColor->name : '-' }}
                                            /
                                            {{ $jewelleryStock->primaryStoneClarity ? $jewelleryStock->primaryStoneClarity->name : '-' }}
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label" style="font-size: 0.7rem; color: #64748b;">Cut Grade
                                        </div>
                                        <div class="detail-value" style="font-weight: 600;">
                                            {{ $jewelleryStock->primaryStoneCut ? $jewelleryStock->primaryStoneCut->name : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($jewelleryStock->side_stone_type_id)
                            <div
                                style="background: #f8fafc; border-radius: 12px; padding: 1.25rem; border: 1px solid #e2e8f0;">
                                <h4
                                    style="font-size: 0.85rem; color: #475569; margin: 0 0 1rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                                    Secondary / Side Stones</h4>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                    <div class="detail-item">
                                        <div class="detail-label" style="font-size: 0.7rem; color: #64748b;">Type</div>
                                        <div class="detail-value" style="font-weight: 600;">
                                            {{ $jewelleryStock->sideStoneType->name }}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label" style="font-size: 0.7rem; color: #64748b;">Total Weight
                                        </div>
                                        <div class="detail-value" style="font-weight: 600;">
                                            {{ $jewelleryStock->side_stone_weight }} cts</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label" style="font-size: 0.7rem; color: #64748b;">Count</div>
                                        <div class="detail-value" style="font-weight: 600;">
                                            {{ $jewelleryStock->side_stone_count }} stones</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                {{-- Image Viewer Modal --}}
                <div id="imageViewerModal"
                    style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; padding: 2rem;"
                    onclick="closeImageViewer()">
                    <button
                        style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: white; font-size: 2rem; cursor: pointer;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <div style="background: white; border-radius: 12px; overflow: hidden; max-width: 900px; width: 100%; animation: zoomIn 0.3s ease-out;"
                        onclick="event.stopPropagation()">
                        <div
                            style="padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                            <h4 id="modalImageTitle" style="margin: 0; font-weight: 700; color: #1e293b;">Image Preview
                            </h4>
                        </div>
                        <div
                            style="background: #000; display: flex; align-items: center; justify-content: center; max-height: 80vh;">
                            <img id="modalImageViewer" src=""
                                style="max-width: 100%; max-height: 80vh; object-fit: contain;">
                        </div>
                    </div>
                </div>

                <style>
                    @keyframes zoomIn {
                        from {
                            opacity: 0;
                            transform: scale(0.9);
                        }

                        to {
                            opacity: 1;
                            transform: scale(1);
                        }
                    }

                    .gallery-item:hover {
                        transform: scale(1.05);
                        border-color: #6366f1 !important;
                        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
                    }
                </style>

                {{-- Pricing Summary --}}
                <div class="tracker-table-card" style="padding: 1.5rem; background: #1e293b; color: white;">
                    <h3
                        style="margin: 0 0 1.5rem; font-size: 1rem; color: rgba(255,255,255,0.7); display: flex; align-items: center; justify-content: space-between;">
                        <span>Commercial Data</span>
                        <i class="bi bi-tag-fill" style="color: #10b981;"></i>
                    </h3>
                    <div style="margin-bottom: 1.25rem;">
                        <div
                            style="font-size: 0.8rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.05em;">
                            Retail Price</div>
                        <div style="font-size: 2rem; font-weight: 800; color: #10b981;">
                            ${{ number_format($jewelleryStock->selling_price, 2) }}</div>
                    </div>

                    @if ($canViewPricing)
                        <div style="padding-top: 1.25rem; border-top: 1px solid rgba(255,255,255,0.1);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="color: rgba(255,255,255,0.5);">Cost:</span>
                                <span
                                    style="font-weight: 600;">${{ number_format($jewelleryStock->purchase_price, 2) }}</span>
                            </div>
                            @php
                                $margin = $jewelleryStock->selling_price - $jewelleryStock->purchase_price;
                                $marginPct =
                                    $jewelleryStock->selling_price > 0
                                        ? ($margin / $jewelleryStock->selling_price) * 100
                                        : 0;
                            @endphp
                            @if ($canViewProfit)
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: rgba(255,255,255,0.5);">Profit:</span>
                                    <span style="font-weight: 600; color: {{ $margin >= 0 ? '#10b981' : '#ef4444' }};">
                                        +${{ number_format($margin, 2) }} ({{ number_format($marginPct, 1) }}%)
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($jewelleryStock->pricingVariants->isNotEmpty())
                    <div class="tracker-table-card" style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 1rem; font-size: 1.1rem; color: #1e293b;">
                            <i class="bi bi-calculator" style="color: #10b981;"></i> Pricing Variants
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            @foreach ($jewelleryStock->pricingVariants as $variant)
                                <div
                                    style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem; background: {{ $variant->is_default_listing ? 'rgba(16,185,129,0.08)' : '#fff' }};">
                                    <div style="display: flex; justify-content: space-between; gap: 1rem;">
                                        <strong style="color: #1e293b;">{{ $variant->variant_label }}</strong>
                                        <strong
                                            style="color: #6366f1;">${{ number_format((float) $variant->listing_price, 2) }}</strong>
                                    </div>
                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.35rem;">
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
                @endif

                {{-- Media Section --}}
                <div class="tracker-table-card" style="padding: 1rem;">
                    <h3 style="margin: 0 0 1rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-images" style="color: #6366f1;"></i> Media Gallery
                    </h3>

                    <div id="image_gallery" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                        @if ($jewelleryStock->images && count($jewelleryStock->images) > 0)
                            @foreach ($jewelleryStock->images as $image)
                                <div class="gallery-item"
                                    style="aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; cursor: pointer; transition: transform 0.2s;"
                                    onclick="openImageViewer('{{ $image['url'] }}', '{{ $jewelleryStock->name }}')">
                                    <img src="{{ $image['url'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            @endforeach
                        @elseif($jewelleryStock->image_url)
                            <div class="gallery-item"
                                style="aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; cursor: pointer; transition: transform 0.2s;"
                                onclick="openImageViewer('{{ $jewelleryStock->image_url }}', '{{ $jewelleryStock->name }}')">
                                <img src="{{ $jewelleryStock->image_url }}"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        @else
                            <div
                                style="grid-column: 1 / -1; padding: 2rem; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 8px;">
                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                                <p style="margin-top: 5px; font-size: 0.9rem;">No images available</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Certification --}}
                <div class="tracker-table-card" style="padding: 1.5rem;">
                    <h3 style="margin: 0 0 1rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-patch-check" style="color: #6366f1;"></i> Certification
                    </h3>
                    @if ($jewelleryStock->certificate_type)
                        <div
                            style="background: rgba(16, 185, 129, 0.05); border-radius: 8px; padding: 1rem; border: 1px solid rgba(16, 185, 129, 0.1);">
                            <div style="font-weight: 700; color: #065f46; font-size: 1.1rem;">
                                {{ $jewelleryStock->certificate_type }}</div>
                            <div style="font-size: 0.85rem; color: #065f46; font-family: monospace;">
                                #{{ $jewelleryStock->certificate_number }}</div>
                            @if ($jewelleryStock->certificate_url)
                                <a href="{{ $jewelleryStock->certificate_url }}" target="_blank"
                                    class="btn btn-sm btn-outline-success mt-2 w-100">
                                    <i class="bi bi-file-earmark-pdf"></i> View Certificate
                                </a>
                            @endif
                        </div>
                    @else
                        <p style="color: #94a3b8; font-size: 0.85rem; margin: 0; text-align: center; padding: 1rem;">No
                            digital certificate linked</p>
                    @endif
                </div>


                {{-- Record Tracking --}}
                <div class="tracker-table-card" style="padding: 1.25rem;">
                    <div style="font-size: 0.75rem; color: #94a3b8; line-height: 1.8;">
                        <div><i class="bi bi-calendar-plus me-1"></i> Registered:
                            {{ $jewelleryStock->created_at->format('d/m/Y H:i') }}</div>
                        <div><i class="bi bi-clock-history me-1"></i> Modified:
                            {{ $jewelleryStock->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expanded Description Row --}}
        @if ($jewelleryStock->description)
            <div class="tracker-table-card" style="padding: 1.5rem; margin-top: 1.5rem;">
                <h3 style="margin: 0 0 1.25rem; font-size: 1.1rem; color: #1e293b;">
                    <i class="bi bi-card-text" style="color: #6366f1;"></i> Detailed Product Description
                </h3>
                <div style="color: #475569; line-height: 1.7; font-size: 1rem; max-width: 800px;">
                    {!! nl2br(e($jewelleryStock->description)) !!}
                </div>
            </div>
        @endif
    </div>
@endsection
