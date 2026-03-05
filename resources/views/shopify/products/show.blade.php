@extends('layouts.admin')

@section('title', $product->title . ' — Shopify Product')

@section('content')
<div class="container-fluid py-4">

    {{-- Breadcrumb --}}
    <nav class="mb-3">
        <ol class="breadcrumb bg-transparent p-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('shopify.products') }}" class="text-decoration-none">Shopify Products</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($product->title, 40) }}</li>
        </ol>
    </nav>

    {{-- Product Header --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <div class="row g-4">
                {{-- Image --}}
                <div class="col-md-3 text-center">
                    @if($product->primary_image)
                        <img src="{{ $product->primary_image }}" alt="{{ $product->title }}"
                             style="max-width: 100%; max-height: 280px; border-radius: 14px; object-fit: cover; border: 2px solid var(--border);">
                    @else
                        <div style="height: 200px; border-radius: 14px; background: var(--light-gray); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="col-md-9">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="fw-bold mb-1" style="color: var(--dark);">{{ $product->title }}</h4>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                @php $statusColors = ['active' => 'success', 'draft' => 'warning', 'archived' => 'secondary']; @endphp
                                <span class="badge bg-{{ $statusColors[$product->status] ?? 'secondary' }} px-3 py-2">{{ ucfirst($product->status) }}</span>
                                @if($product->vendor)<span class="badge bg-light text-dark px-3 py-2">{{ $product->vendor }}</span>@endif
                                @if($product->product_type)<span class="badge bg-light text-dark px-3 py-2">{{ $product->product_type }}</span>@endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <form action="{{ route('shopify.products.sync', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-info btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Re-sync</button>
                            </form>
                            <form action="{{ route('shopify.products.export', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-cloud-upload me-1"></i>Export to Shopify</button>
                            </form>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Price</small>
                            <span class="fw-bold fs-5" style="color: var(--primary);">${{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Compare At</small>
                            <span class="fw-semibold">{{ $product->compare_at_price ? '$' . number_format($product->compare_at_price, 2) : '—' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">SKU</small>
                            <code>{{ $product->sku ?? '—' }}</code>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Stock</small>
                            <span class="fw-semibold">{{ $product->inventory_quantity }}</span>
                        </div>
                    </div>

                    @if($product->tags)
                    <div class="mt-3">
                        <small class="text-muted d-block mb-1">Tags</small>
                        @foreach(explode(',', $product->tags) as $tag)
                            <span class="badge bg-light text-dark me-1">{{ trim($tag) }}</span>
                        @endforeach
                    </div>
                    @endif

                    <div class="mt-3">
                        <small class="text-muted">
                            Shopify ID: <code>{{ $product->shopify_product_id }}</code>
                            @if($product->last_synced_at)
                                · Last synced: {{ $product->last_synced_at->diffForHumans() }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom Metafields --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color: var(--dark);">
                <i class="bi bi-gem me-2" style="color: var(--primary);"></i>Custom Metafields (Jewellery Details)
            </h5>
            <div class="row g-3">
                @php
                    $metaLabels = [
                        'metal_purity' => 'Metal Purity',
                        'metal' => 'Metal',
                        'resizable' => 'Resizable',
                        'comfort_fit' => 'Comfort Fit',
                        'ring_height_1' => 'Ring Height',
                        'ring_width_1' => 'Ring Width',
                        'product_video' => 'Product Video',
                        'stone_measurement' => 'Stone Measurement',
                        'stone_clarity' => 'Stone Clarity',
                        'stone_carat_weight' => 'Stone Carat Weight',
                        'stone_color' => 'Stone Color',
                        'stone_shape' => 'Stone Shape',
                        'stone_type' => 'Stone Type',
                        'side_stone_type' => 'Side Stone Type',
                        'side_shape' => 'Side Shape',
                        'side_color' => 'Side Color',
                        'side_carat_weight' => 'Side Carat Weight',
                        'side_measurement' => 'Side Measurement',
                        'side_clarity' => 'Side Clarity',
                        'melee_size' => 'Melee Size',
                    ];
                @endphp

                @foreach($metaLabels as $key => $label)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="p-3 rounded-3" style="background: var(--light-gray); min-height: 70px;">
                        <small class="text-muted d-block mb-1">{{ $label }}</small>
                        @if($key === 'product_video' && $product->{$key})
                            <a href="{{ $product->{$key} }}" target="_blank" class="text-primary text-decoration-none">
                                <i class="bi bi-play-circle me-1"></i>View Video
                            </a>
                        @else
                            <span class="fw-semibold" style="color: var(--dark);">{{ $product->{$key} ?? '—' }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Product Description --}}
    @if($product->description_html)
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color: var(--dark);">
                <i class="bi bi-file-text me-2" style="color: var(--primary);"></i>Description
            </h5>
            <div class="shopify-description" style="line-height: 1.7;">
                {!! $product->description_html !!}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
