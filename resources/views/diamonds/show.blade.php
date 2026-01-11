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
                        <span class="breadcrumb-current">Diamond #{{ $diamond->lot_no }}</span>
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

        <!-- Basic Identification -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                    <div>
                        <h5 class="section-title">Basic Identification</h5>
                        <p class="section-description">Essential identifiers and diamond type</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Lot No</label>
                        <p class="form-value">{{ $diamond->lot_no }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <p class="form-value">{{ $diamond->sku }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Diamond Type</label>
                        <p class="form-value">{{ $diamond->diamond_type ?? 'N/A' }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Barcode Number</label>
                        <p class="form-value">{{ $diamond->barcode_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diamond Specifications -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div>
                        <h5 class="section-title">Diamond Specifications</h5>
                        <p class="section-description">The 4 C's and physical characteristics</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Shape</label>
                        <p class="form-value">{{ $diamond->shape ?? 'N/A' }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cut</label>
                        <p class="form-value">{{ $diamond->cut ?? 'N/A' }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Measurement</label>
                        <p class="form-value">{{ $diamond->measurement ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing & Weight -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <h5 class="section-title">Pricing & Weight</h5>
                        <p class="section-description">Weight, per carat pricing, and cost information</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    @if(Auth::guard('admin')->user()?->hasPermission('diamonds.view_pricing'))
                        <div class="form-group">
                            <label class="form-label">Price Per Ct</label>
                            <p class="form-value">${{ number_format($diamond->per_ct ?? 0, 2) }}</p>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">Weight</label>
                        <p class="form-value">{{ $diamond->weight ?? 0 }} carats</p>
                    </div>

                    @if(Auth::guard('admin')->user()?->hasPermission('diamonds.view_pricing'))
                        <div class="form-group">
                            <label class="form-label">Purchase Price</label>
                            <p class="form-value">${{ number_format($diamond->purchase_price ?? 0, 2) }}</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Margin</label>
                            <p class="form-value">{{ $diamond->margin ?? 0 }}%</p>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">Listing Price</label>
                        <p class="form-value">${{ number_format($diamond->listing_price ?? 0, 2) }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Shipping Price</label>
                        <p class="form-value">${{ number_format($diamond->shipping_price ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lifecycle & Status -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <h5 class="section-title">Lifecycle & Status</h5>
                        <p class="section-description">Track dates, status, and financial performance</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Purchase Date</label>
                        <p class="form-value">
                            {{ $diamond->purchase_date ? \Carbon\Carbon::parse($diamond->purchase_date)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sold Out Date</label>
                        <p class="form-value">
                            {{ $diamond->sold_out_date ? \Carbon\Carbon::parse($diamond->sold_out_date)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <p class="form-value">
                            @if($diamond->sold_out_date)
                                <span class="status-pill status-sold">Sold Out</span>
                            @else
                                <span class="status-pill status-instock">In Stock</span>
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Duration Days</label>
                        <p class="form-value">{{ $diamond->duration_days ?? 0 }} days</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Duration Price</label>
                        <p class="form-value">${{ number_format($diamond->duration_price ?? 0, 2) }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sold Out Price</label>
                        <p class="form-value">${{ number_format($diamond->sold_out_price ?? 0, 2) }}</p>
                    </div>

                    @if(Auth::guard('admin')->user()?->hasPermission('diamonds.view_pricing'))
                        <div class="form-group">
                            <label class="form-label">Profit</label>
                            <p class="form-value">${{ number_format($diamond->profit ?? 0, 2) }}</p>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">Sold Out Month</label>
                        <p class="form-value">{{ $diamond->sold_out_month ?? 'N/A' }}</p>
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
                        <label class="form-label">Last Edited By</label>
                        <p class="form-value">
                            @if($diamond->lastModifier)
                                {{ $diamond->lastModifier->name }}
                                <small class="text-muted">({{ $diamond->updated_at->format('M d, Y h:i A') }})</small>
                            @else
                                <span class="text-muted">Not edited</span>
                            @endif
                        </p>
                    </div>

                    @if($diamond->barcode_image_url)
                        <div class="form-group full-width">
                            <label class="form-label">Barcode</label>
                            <div style="padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
                                <img src="{{ $diamond->barcode_image_url }}" style="max-width: 200px; height: auto;"
                                    alt="Barcode">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Description & Notes -->
        @if($diamond->description || $diamond->note)
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Description & Notes</h5>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        @if($diamond->description)
                            <div class="form-group full-width">
                                <label class="form-label">Description</label>
                                <p class="form-value">{{ $diamond->description }}</p>
                            </div>
                        @endif

                        @if($diamond->note)
                            <div class="form-group full-width">
                                <label class="form-label">Note</label>
                                <p class="form-value">{{ $diamond->note }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Images Card -->
        @if($diamond->multi_img_upload && is_array($diamond->multi_img_upload) && count($diamond->multi_img_upload) > 0)
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
                    <div class="image-gallery"
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                        @foreach($diamond->multi_img_upload as $index => $image)
                            <div onclick="viewImage('{{ $image }}', 'Diamond Image {{ $index + 1 }}')"
                                style="border-radius: 8px; overflow: hidden; background-color: #f8f9fa; cursor: pointer; position: relative; transition: all 0.3s;">
                                <img src="{{ $image }}" style="width: 100%; height: 150px; object-fit: cover;"
                                    alt="Diamond Image {{ $index + 1 }}">
                                <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;"
                                    onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                                    <i class="bi bi-eye" style="color: white; font-size: 2rem;"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Image Viewer Modal -->
    <div id="imageModal" class="image-modal no-print" onclick="closeImageModal()">
        <div class="image-modal-content" onclick="event.stopPropagation()">
            <div class="image-modal-header">
                <h3 id="imageModalTitle">Image Viewer</h3>
                <button class="image-modal-close" onclick="closeImageModal()" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="image-modal-body">
                <div
                    style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #000;">
                    <img id="imageViewer" src="" style="max-width: 100%; max-height: 100%; object-fit: contain;"
                        alt="Image">
                </div>
            </div>
        </div>
    </div>

    <style>
        .form-value {
            margin: 0;
            font-size: 1rem;
            color: #333;
            padding: 8px 0;
            font-weight: 500;
        }

        .text-muted {
            color: #6c757d;
            font-style: italic;
        }

        /* Status Pills */
        .status-pill {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            border: 1px solid transparent;
        }

        .status-instock {
            background: #eafaf1;
            color: #0f5132;
            border-color: #cbe7d6;
        }

        .status-sold {
            background: #fdeaea;
            color: #842029;
            border-color: #f6cccc;
        }

        /* Image Modal */
        .image-modal {
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

        .image-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-modal-content {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s ease;
        }

        .image-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }

        .image-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .image-modal-close {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            line-height: 1;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .image-modal-close:hover {
            background: #ef4444;
            color: #fff;
        }

        .image-modal-body {
            flex: 1;
            padding: 0;
            overflow: hidden;
            background: #000;
            border-radius: 0 0 12px 12px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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
    </style>

    <script>
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

        // Keyboard navigation
        document.addEventListener('keydown', function (e) {
            const imageModal = document.getElementById('imageModal');
            if (imageModal && imageModal.classList.contains('active')) {
                if (e.key === 'Escape') closeImageModal();
            }
        });
    </script>
@endsection