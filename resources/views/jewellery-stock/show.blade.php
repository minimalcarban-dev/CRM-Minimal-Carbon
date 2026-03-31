@extends('layouts.admin')
@section('title', 'Jewellery Stock Details')
@section('content')
    <div class="tracker-page">
        {{-- Page Header --}}
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
                    <div class="d-flex align-items-center gap-3" style="display: flex; align-items: center; gap: 1rem; margin-top: 5px;">
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
            
            <span class="tracker-badge" style="background: rgba(99, 102, 241, 0.1); color: #4338ca; padding: 0.5rem 1rem; font-size: 0.9rem; text-transform: capitalize;">
                <i class="bi bi-collection"></i> {{ str_replace('_', ' ', $jewelleryStock->type) }}
            </span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem;">
            <div>
                {{-- Basic Details Card --}}
                <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-info-circle" style="color: #6366f1;"></i> Basic Information
                    </h3>
                    <div class="detail-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">SKU</div>
                            <div class="detail-value" style="font-size: 1.1rem; color: #1e293b; font-weight: 600; font-family: monospace;">{{ $jewelleryStock->sku }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Name</div>
                            <div class="detail-value" style="font-size: 1.1rem; color: #1e293b; font-weight: 600;">{{ $jewelleryStock->name }}</div>
                        </div>
                    </div>
                </div>

                {{-- Specs Card --}}
                <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-sliders" style="color: #6366f1;"></i> Specifications
                    </h3>
                    <div class="detail-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Metal Type</div>
                            <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">{{ $jewelleryStock->metalType ? $jewelleryStock->metalType->name : 'N/A' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Ring Size</div>
                            <div class="detail-value" style="font-size: 1rem; color: #1e293b; font-weight: 500;">{{ $jewelleryStock->ringSize ? $jewelleryStock->ringSize->name : 'N/A' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Weight</div>
                            <div class="detail-value" style="font-size: 1.1rem; color: #f59e0b; font-weight: 700;">{{ $jewelleryStock->weight ? number_format($jewelleryStock->weight, 3) . ' g' : 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Pricing & Inventory --}}
                <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-currency-dollar" style="color: #6366f1;"></i> Pricing & Inventory
                    </h3>
                    <div class="detail-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0;">
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Purchase Price</div>
                            <div class="detail-value" style="font-size: 1.1rem; color: #1e293b; font-weight: 500;">${{ number_format($jewelleryStock->purchase_price, 2) }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Selling Price</div>
                            <div class="detail-value" style="font-size: 1.25rem; color: #10b981; font-weight: 700;">${{ number_format($jewelleryStock->selling_price, 2) }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Est. Margin</div>
                            <div class="detail-value" style="font-size: 1.1rem; font-weight: 600;">
                                @php
                                    $margin = $jewelleryStock->selling_price - $jewelleryStock->purchase_price;
                                    $marginPct = $jewelleryStock->purchase_price > 0 ? ($margin / $jewelleryStock->purchase_price) * 100 : 0;
                                @endphp
                                <span style="color: {{ $margin >= 0 ? '#10b981' : '#ef4444' }};">
                                    ${{ number_format($margin, 2) }} ({{ number_format($marginPct, 1) }}%)
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="detail-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Current Stock</div>
                            <div class="detail-value" style="font-size: 1.25rem; color: #1e293b; font-weight: 700;">{{ $jewelleryStock->quantity }} units</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label" style="font-size: 0.8rem; color: #64748b; font-weight: 500; text-transform: uppercase;">Low Stock Threshold</div>
                            <div class="detail-value" style="font-size: 1.1rem; color: #1e293b; font-weight: 500;">{{ $jewelleryStock->low_stock_threshold }} units</div>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                @if($jewelleryStock->description)
                <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0 0 1rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-text-paragraph" style="color: #6366f1;"></i> Description
                    </h3>
                    <p style="color: #475569; line-height: 1.6; margin: 0; white-space: pre-wrap;">{{ $jewelleryStock->description }}</p>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div>
                {{-- Image Card --}}
                <div class="tracker-table-card" style="padding: 1.5rem; margin-bottom: 1.5rem; text-align: center;">
                    <h3 style="margin: 0 0 1rem; font-size: 1.1rem; color: #1e293b; text-align: left;">
                        <i class="bi bi-image" style="color: #6366f1;"></i> Image
                    </h3>
                    @if ($jewelleryStock->image_url)
                        <img src="{{ $jewelleryStock->image_url }}" alt="{{ $jewelleryStock->name }}" style="max-width: 100%; height: auto; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1rem;">
                        <div style="font-size: 0.8rem; color: #64748b;">
                            <a href="{{ $jewelleryStock->image_url }}" target="_blank" style="color: #6366f1; text-decoration: none;">
                                <i class="bi bi-box-arrow-up-right"></i> Open full image
                            </a>
                        </div>
                    @else
                        <div style="padding: 3rem 1rem; background: #f8fafc; border-radius: 8px; color: #94a3b8; border: 1px dashed #cbd5e1;">
                            <i class="bi bi-image" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                            <p style="margin: 0;">No image available</p>
                        </div>
                    @endif
                </div>

                {{-- Record Info --}}
                <div class="tracker-table-card" style="padding: 1.5rem;">
                    <h3 style="margin: 0 0 1rem; font-size: 1.1rem; color: #1e293b;">
                        <i class="bi bi-clock-history" style="color: #6366f1;"></i> Record History
                    </h3>
                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.6;">
                        <div style="margin-bottom: 0.75rem;">
                            <strong>Created At:</strong><br>
                            {{ $jewelleryStock->created_at->format('d M, Y h:i A') }}
                        </div>
                        <div>
                            <strong>Last Updated:</strong><br>
                            {{ $jewelleryStock->updated_at->format('d M, Y h:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
