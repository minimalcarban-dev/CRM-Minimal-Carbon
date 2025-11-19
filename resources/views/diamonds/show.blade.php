@extends('layouts.admin')

@section('title', 'Diamond Details')

@section('content')
    <div class="diamond-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('diamond.index') }}" class="breadcrumb-link">
                            Diamonds
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Diamond #{{ $diamond->stockid }}</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-gem"></i>
                        Diamond Details
                    </h1>
                    <p class="page-subtitle">View the diamond information (SKU: {{ $diamond->sku }})</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('diamond.edit', $diamond) }}" class="btn-primary-custom">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit</span>
                    </a>
                    <a href="{{ route('diamond.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Diamond Details Card -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div>
                        <h5 class="section-title">Basic Information</h5>
                        <p class="section-description">Core diamond specifications</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Stock ID</label>
                        <p class="form-value">{{ $diamond->stockid }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <p class="form-value">{{ $diamond->sku }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <p class="form-value">Rs. {{ number_format($diamond->price, 2) }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Listing Price</label>
                        <p class="form-value">Rs. {{ number_format($diamond->listing_price, 2) }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cut</label>
                        <p class="form-value">{{ $diamond->cut ?? 'N/A' }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Shape</label>
                        <p class="form-value">{{ $diamond->shape ?? 'N/A' }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Measurement</label>
                        <p class="form-value">{{ $diamond->measurement ?? 'N/A' }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Number of Pictures</label>
                        <p class="form-value">{{ $diamond->number_of_pics ?? '0' }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Diamond Type</label>
                        <p class="form-value">{{ $diamond->diamond_type ?? 'N/A' }}</p>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Description</label>
                        <p class="form-value">{{ $diamond->description ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Details Card -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <h5 class="section-title">Assignment Details</h5>
                        <p class="section-description">Admin assignment and tracking information</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Assigned Admin</label>
                        <p class="form-value">
                            @if($diamond->assignedAdmin)
                                {{ $diamond->assignedAdmin->name }}
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assigned By</label>
                        <p class="form-value">
                            @if($diamond->assignedByAdmin)
                                {{ $diamond->assignedByAdmin->name }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assigned At</label>
                        <p class="form-value">
                            @if($diamond->assigned_at)
                                {{ $diamond->assigned_at->format('M d, Y h:i A') }}
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Barcode Number</label>
                        <p class="form-value">{{ $diamond->barcode_number ?? 'N/A' }}</p>
                    </div>

                    @if($diamond->barcode_image_url)
                        <div class="form-group full-width">
                            <label class="form-label">Barcode</label>
                            <div style="padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
                                {!! $diamond->barcode_image_url ? '<img src="' . $diamond->barcode_image_url . '" style="max-width: 200px; height: auto;" alt="Barcode">' : 'No barcode' !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes Card -->
        @if($diamond->note)
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Notes</h5>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <p class="form-value">{{ $diamond->note }}</p>
                </div>
            </div>
        @endif

        <!-- Images Card -->
        @if($diamond->multi_img_upload)
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-images"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Images</h5>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="image-gallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                        @foreach($diamond->multi_img_upload as $image)
                            <div style="border-radius: 4px; overflow: hidden; background-color: #f8f9fa;">
                                <img src="{{ $image }}" style="width: 100%; height: 150px; object-fit: cover;" alt="Diamond Image">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
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
