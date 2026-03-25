@extends('layouts.admin')

@section('title', 'Edit Order')

@section('content')
    <div
        class="page-header d-block d-sm-flex d-lg-flex d-md-flex d-xl-flex justify-content-between align-items-center mb-lg-4 mb-xl-4 mb-md-4 mb-sm-4 mb-2">
        <div class="mb-lg-0 mb-xl-0 mb-md-0 mb-2">
            <h1 class="page-title">Edit Order #{{ $order->id }}</h1>
            <p class="page-subtitle">Update order details and related information</p>
        </div>
        <div class="header-right d-flex align-items-center gap-2">
            <a href="{{ route('orders.index') }}" class="btn-primary-custom btn-sm">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Orders</span>
            </a>
            <button type="submit" form="editOrderForm" class="btn-primary-custom px-4">
                <i class="bi bi-save me-1"></i> Update Order
            </button>
        </div>
    </div>


    <div class="card-custom p-0 p-lg-4 p-xl-4 p-md-4 p-sm-4">
        @if(in_array($order->diamond_status, ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled']))
            <div class="alert alert-danger d-flex align-items-center m-4 mb-4"
                style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c;">
                <i class="bi bi-slash-circle me-3" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>This order is cancelled.</strong>
                    @if(auth()->guard('admin')->user()->is_super)
                        <div style="font-size: 0.9rem;">However, as a <span style="font-weight: 600;">Super Admin</span>, you retain
                            full editing privileges to bypass the lock and modify this order.</div>
                    @else
                        <div style="font-size: 0.9rem;">Form is read-only. You may only update the Special Notes at the bottom of
                            the page.</div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Errors are displayed via the unified flash partial in layout -->

        <form action="{{ route('orders.update', $order->id) }}" method="POST" enctype="multipart/form-data"
            id="editOrderForm">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-semibold">Order Type <span class="text-danger">*</span></label>
                {{-- Disabled in edit mode - order type cannot be changed after creation --}}
                <select name="order_type_display" id="order_type" class="form-select-custom" disabled>
                    <option value="ready_to_ship" {{ $order->order_type == 'ready_to_ship' ? 'selected' : '' }}>Ready to Ship
                    </option>
                    <option value="custom_diamond" {{ $order->order_type == 'custom_diamond' ? 'selected' : '' }}>Custom
                        Diamond</option>
                    <option value="custom_jewellery" {{ $order->order_type == 'custom_jewellery' ? 'selected' : '' }}>Custom
                        Jewellery</option>
                </select>
                {{-- Hidden input to submit the actual value since disabled fields don't submit --}}
                <input type="hidden" name="order_type" value="{{ $order->order_type }}">
                <small class="text-muted d-block mt-1">
                    <i class="bi bi-lock-fill me-1"></i> Order type cannot be changed after creation
                </small>
            </div>

            <div id="orderFormFields">
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-hourglass-split"></i> Loading form...
                </div>
            </div>


        </form>
    </div>

    {{-- ─── IMAGE MODAL (exact match with show.blade.php) ── --}}
    <div id="imageModal" class="od-modal no-print" onclick="closeImageModal()">
        <div class="od-modal-box" style="max-width:1000px; height:auto; max-height:92vh;" onclick="event.stopPropagation()">
            <div class="od-modal-head">
                <h3 id="imageModalTitle">Image Viewer</h3>
                <button class="od-modal-close" onclick="closeImageModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="od-modal-body" style="background:#000; display:flex; align-items:center; justify-content:center;">
                <img id="imageViewer" src="" alt="Image" style="max-width:100%; max-height:80vh; object-fit:contain;">
            </div>
        </div>
    </div>

    {{-- ─── PDF MODAL (exact match with show.blade.php) ── --}}
    <div id="pdfModal" class="od-modal no-print" onclick="closePDFModal()">
        <div class="od-modal-box" onclick="event.stopPropagation()">
            <div class="od-modal-head">
                <h3 id="pdfModalTitle">Document Viewer</h3>
                <button class="od-modal-close" onclick="closePDFModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="od-modal-body">
                <iframe id="pdfViewer" src="" frameborder="0"
                    style="width:100%; height:100%; min-height:0; flex:1;"></iframe>
            </div>
        </div>
    </div>

    <style>
        /* edit.blade.php — uses global theme variables from layouts.admin */

        /* Custom Button Styles */
        .header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 0.65rem 1.5rem;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, var(--primary-dark), #4338ca);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.45);
            color: white;
        }

        .btn-secondary-custom {
            background: var(--bg-card);
            color: var(--gray);
            border: 2px solid var(--border);
            padding: 0.6rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-secondary-custom:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        /* Current Files Section */
        .current-files-section .card-custom {
            background: var(--bg-card);
            border-radius: 12px;
            border: 2px solid var(--border);
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary);
            font-size: 1.25rem;
        }

        .file-section {
            background: var(--bg-body);
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            height: 100%;
        }

        .file-section-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border);
        }

        .file-section-header i {
            color: var(--primary);
            font-size: 1.125rem;
        }

        /* Current Images Grid */
        .current-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 0.75rem;
        }

        .current-image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--border);
            aspect-ratio: 1;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--bg-card);
        }

        .current-image-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.2);
            border-color: var(--primary);
        }

        .current-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-overlay {
            position: absolute;
            inset: 0;
            background: rgba(59, 130, 246, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: white;
            font-size: 1.5rem;
        }

        .current-image-item:hover .image-overlay {
            opacity: 1;
        }

        .image-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(59, 130, 246, 0.95);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            padding: 0.375rem;
        }

        /* Current PDFs List */
        .current-pdfs-list {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .current-pdf-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem;
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .current-pdf-item:hover {
            border-color: var(--primary);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .pdf-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            border-radius: 8px;
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
            color: var(--dark);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pdf-action {
            font-size: 0.75rem;
            color: var(--primary);
            font-weight: 500;
        }

        /* ── Preview Modals (copied from show.blade.php) ── */
        .od-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .75);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            backdrop-filter: blur(3px);
        }

        .od-modal.active {
            display: flex;
        }

        .od-modal-box {
            background: var(--surface);
            border-radius: var(--radius-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 95vh;
            width: 95vw;
            max-width: 1300px;
            box-shadow: var(--shadow-md);
        }

        .od-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1.5px solid var(--border);
            background: var(--bg);
            gap: .75rem;
        }

        .od-modal-head h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .od-modal-close {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .875rem;
            transition: var(--transition);
        }

        .od-modal-close:hover {
            background: var(--danger-soft);
            border-color: var(--danger);
            color: var(--danger);
        }

        .od-modal-body {
            flex: 1;
            overflow: hidden;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .od-modal-body iframe,
        .od-modal-body img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border: none;
            display: block;
        }

        /* ── Images Grid (copied from show.blade.php) ── */
        .od-img-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: .625rem;
         padding-bottom: 10px;
        }

        .od-img-item {
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            cursor: pointer;
            border: 1.5px solid var(--border);
            position: relative;
            transition: var(--transition);
         border-radius: 12px;
        }

        .od-img-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            border-color: var(--primary);
        }

        .od-img-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
            border-radius: 12px;
        }

        .od-img-item:hover img {
            transform: scale(1.08);
        }

        .od-img-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
            color: #fff;
            font-size: 1.25rem;
        }

        .od-img-item:hover .od-img-overlay {
            opacity: 1;
        }

        .od-pdf-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: .75rem;
            margin-bottom: 0.75rem;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .od-pdf-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .od-pdf-icon {
            width: 42px;
            height: 42px;
            background: #fdf2f2;
            border-radius: .625rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            font-size: 1.25rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .od-pdf-info {
            flex: 1;
            min-width: 0;
        }

        .od-pdf-name {
            font-size: .875rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0 0 .125rem 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .od-pdf-size {
            font-size: .75rem;
            color: var(--muted);
            display: block;
        }

        .od-pdf-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-left: 1rem;
        }

        .od-pdf-btn {
            width: 34px;
            height: 34px;
            border-radius: .5rem;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .875rem;
            transition: var(--transition);
        }

        .od-pdf-btn:hover {
        .file-upload-preview {
            margin-top: 1rem;
            display: none;
            padding: 1.25rem;
            background: rgba(var(--primary-rgb), 0.02);
            border: 1.5px dashed var(--border);
            border-radius: var(--radius);
        }

        .file-upload-preview.active {
            display: block;
        }

        .preview-header {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            word-break: break-all;
            line-height: 1.3;
        }

        .preview-item .remove-file {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--danger);
            color: white;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .preview-item .remove-file:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .pdf-preview-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .pdf-preview-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .pdf-preview-info {
            flex: 1;
            min-width: 0;
        }

        .pdf-preview-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pdf-preview-size {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .current-images-grid {
                grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            }

            .row>div {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 575px) {
            #mainContent {
                margin-top: 83px;
            }

            .btn-primary-custom {
                padding: 5px 7px;
                border-radius: 7px;
                font-size: 11px;
            }

            .file-section {
                padding: 7px;
            }

            .file-section-header {
                font-size: 14px;
                gap: 5px;
                margin-bottom: 7px;
                padding-bottom: 6px;
            }

            .section-header {
                padding: 7px;
            }

            .section-icon {
                width: 34px;
                height: 34px;
                font-size: 15px;
            }

            p.section-description {
                font-size: 12px;
            }

            .form-group-modern .form-label-modern {
                font-size: 12px;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 7px;
            }

            .section-body,
            .order-type-label,
            #orderFormFields .section-body {
                padding: 10px;
            }

            .form-control-modern {
                padding: 3px 5px;
                border-radius: 7px;
                font-size: 11px;
            }

            #orderFormFields .row>div {
                margin-bottom: 0;
            }

            #orderFormFields .required-badge,
            #orderFormFields .form-group-modern .optional-badge {
                font-size: 8px;
                padding: 1px 2px;
            }

            #orderFormFields .badge-info,
            #orderFormFields .optional-badge {
                padding: 1px 4px;
                border-radius: 2px;
                font-size: 10px;
            }

            .section-content .price-display {
                padding: 0.5rem;
                border-radius: 5px;
            }

            .section-content .price-value {
                font-size: 15px;
            }
        }

        /* Existing File Removal Buttons */
        .remove-existing-file {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: 1px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s;
            padding: 0;
            line-height: 1;
        }

        .remove-existing-file:hover {
            background: #ef4444;
            transform: scale(1.1);
        }

        .current-pdf-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .current-pdf-item {
            flex: 1;
        }

        .remove-pdf-btn {
            background: rgba(239,68,68,0.08);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.2);
            width: 36px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .remove-pdf-btn:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }
        /* ── Dark Mode Overrides ── */
        [data-theme="dark"] .card-custom {
            background: var(--bg-card) !important;
            border-color: var(--border) !important;
            color: var(--dark);
        }

        [data-theme="dark"] .page-header {
            background: var(--bg-card) !important;
            border-color: var(--border) !important;
        }

        [data-theme="dark"] .page-title,
        [data-theme="dark"] .page-subtitle {
            color: var(--dark) !important;
        }

        [data-theme="dark"] .form-select-custom,
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background: var(--bg-card) !important;
            border-color: var(--border) !important;
            color: var(--dark) !important;
        }

        [data-theme="dark"] .alert-danger {
            background: rgba(239,68,68,0.1) !important;
            border-color: rgba(239,68,68,0.3) !important;
            color: #fca5a5 !important;
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const type = document.getElementById('order_type').value;
            loadForm(type);
            setupAjaxEditSubmission();

            document.getElementById('order_type').addEventListener('change', function () {
                loadForm(this.value);
            });

            function loadForm(type) {
                const container = document.getElementById('orderFormFields');
                container.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-hourglass-split"></i> Loading...</div>';

                if (!type) return;

                fetch(`/admin/orders/form/${type}?edit=true&id={{ $order->id }}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Extract scripts before setting innerHTML
                        const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
                        let match;
                        const scripts = [];
                        let htmlWithoutScripts = html;

                        while ((match = scriptRegex.exec(html)) !== null) {
                            scripts.push(match[1]);
                            htmlWithoutScripts = htmlWithoutScripts.replace(match[0], '');
                        }

                        container.innerHTML = htmlWithoutScripts;

                        // Wait a brief moment to ensure DOM represents the new elements
                        setTimeout(() => {
                            // Execute extracted scripts
                            scripts.forEach(scriptContent => {
                                if (scriptContent.trim()) {
                                    try {
                                        new Function(scriptContent)();
                                    } catch (e) {
                                        console.error('Error executing dynamically loaded script:', e);
                                    }
                                }
                            });

                            initializeFilePreview();
                            applyReadOnlyIfCancelled();
                        }, 50);
                    })
                    .catch(() => {
                        container.innerHTML = `<div class="alert alert-danger">Error loading form. Please try again.</div>`;
                    });
            }

            function applyReadOnlyIfCancelled() {
                @if(in_array($order->diamond_status, ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled']) && !auth()->guard('admin')->user()->is_super)
                    const form = document.getElementById('editOrderForm');
                    if (form) {
                        const elements = form.querySelectorAll('input, select, textarea');
                        elements.forEach(el => {
                            if (el.name !== 'special_notes' && el.type !== 'hidden') {
                                el.disabled = true;
                                el.style.backgroundColor = '#f3f4f6';
                                el.style.cursor = 'not-allowed';
                            }
                        });

                        // Remove file remove buttons
                        const removeBtns = document.querySelectorAll('.remove-existing-file, .remove-pdf-btn');
                        removeBtns.forEach(btn => btn.style.display = 'none');

                        // Disable file inputs explicitly just in case
                        const fileInputs = form.querySelectorAll('input[type="file"]');
                        fileInputs.forEach(el => el.disabled = true);
                    }
                @endif
                            }

            function setupAjaxEditSubmission() {
                const form = document.getElementById('editOrderForm');
                if (!form || form.dataset.ajaxBound === '1') {
                    return;
                }

                form.dataset.ajaxBound = '1';

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    if (form.dataset.submitting === '1') {
                        return;
                    }

                    form.dataset.submitting = '1';
                    clearFormErrors(form);

                    const externalBtns = document.querySelectorAll(`button[type="submit"][form="${form.id}"]`);
                    const internalBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                    const allBtns = Array.from(externalBtns).concat(Array.from(internalBtns));
                    allBtns.forEach(btn => btn.disabled = true);

                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                        .then(async response => {
                            const data = await response.json();

                            if (response.ok && data.success) {
                                broadcastMeleeStockRefresh(form, data, formData);

                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: data.message,
                                        timer: 1800,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href = data.redirect || '/admin/orders';
                                    });
                                } else {
                                    window.location.href = data.redirect || '/admin/orders';
                                }
                                return;
                            }

                            form.dataset.submitting = '0';
                            allBtns.forEach(btn => btn.disabled = false);

                            if (data.errors) {
                                displayValidationErrors(form, data.errors);
                            } else {
                                showErrorBanner(form, data.message || 'Unable to update order right now.');
                            }
                        })
                        .catch(error => {
                            console.error('Edit form submission error:', error);
                            form.dataset.submitting = '0';
                            allBtns.forEach(btn => btn.disabled = false);
                            showErrorBanner(form, 'Network error. Please try again.');
                        });
                });
            }

            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                form.querySelectorAll('.error-banner').forEach(el => el.remove());
            }

            function displayValidationErrors(form, errors) {
                Object.entries(errors).forEach(([field, messages]) => {
                    const input = findFieldInput(form, field);
                    if (!input) {
                        return;
                    }

                    input.classList.add('is-invalid');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.style.display = 'block';
                    errorDiv.textContent = Array.isArray(messages) ? messages[0] : messages;
                    (input.parentNode || form).appendChild(errorDiv);
                });

                const firstError = form.querySelector('.is-invalid, .error-banner');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            function findFieldInput(form, field) {
                const bracketField = convertFieldToBracketNotation(field);
                const selectors = [
                    `[name="${field}"]`,
                    `[name="${field}[]"]`,
                    `[name="${bracketField}"]`,
                    `[name="${bracketField}[]"]`
                ];

                for (const selector of selectors) {
                    const input = form.querySelector(selector);
                    if (input) {
                        return input;
                    }
                }

                if (field === 'melee_entries_json') {
                    return document.getElementById('melee_search_select') || form.querySelector('[name="melee_entries_json"]');
                }

                return null;
            }

            function convertFieldToBracketNotation(field) {
                const parts = String(field).split('.');
                return parts.reduce((name, part, index) => {
                    return index === 0 ? part : `${name}[${part}]`;
                }, '');
            }

            function showErrorBanner(form, message) {
                const banner = document.createElement('div');
                banner.className = 'error-banner alert alert-danger';
                banner.textContent = message;
                form.prepend(banner);
            }

            function extractMeleeDiamondIds(formData, responseData = null) {
                const ids = new Set();

                if (responseData && responseData.melee_stock_summary) {
                    Object.keys(responseData.melee_stock_summary).forEach(id => ids.add(parseInt(id, 10)));
                }

                const jsonPayloads = [
                    formData.get('melee_entries_json'),
                    formData.get('melee_entries')
                ].filter(Boolean);

                jsonPayloads.forEach(rawEntries => {
                    try {
                        const entries = JSON.parse(rawEntries);
                        if (Array.isArray(entries)) {
                            entries.forEach(entry => {
                                if (entry.melee_diamond_id) {
                                    ids.add(parseInt(entry.melee_diamond_id, 10));
                                }
                            });
                        }
                    } catch (e) {
                        console.warn('Failed to parse melee entries payload:', e);
                    }
                });

                for (const [key, value] of formData.entries()) {
                    if (/^melee_entries\[\d+\]\[melee_diamond_id\]$/.test(key) && value) {
                        ids.add(parseInt(value, 10));
                    }
                }

                const singleId = formData.get('melee_diamond_id');
                if (singleId) {
                    ids.add(parseInt(singleId, 10));
                }

                return Array.from(ids).filter(Number.isFinite);
            }

            function broadcastMeleeStockRefresh(form, responseData, formData) {
                const ids = extractMeleeDiamondIds(formData, responseData);
                const stockSummary = responseData && typeof responseData.melee_stock_summary === 'object'
                    ? responseData.melee_stock_summary
                    : {};

                if (!ids.length && !Object.keys(stockSummary).length) {
                    return;
                }

                const payload = {
                    timestamp: Date.now(),
                    ids,
                    stock: stockSummary
                };

                try {
                    localStorage.setItem('melee_stock_refresh', JSON.stringify(payload));
                } catch (e) {
                    console.warn('Unable to persist melee stock refresh payload:', e);
                }

                if (window.applyMeleeStockSummary && Object.keys(stockSummary).length) {
                    window.applyMeleeStockSummary(stockSummary);
                } else if (window.refreshMeleeStock && ids.length) {
                    window.refreshMeleeStock(ids);
                }

                window.dispatchEvent(new CustomEvent('melee:stock-refresh', {
                    detail: payload
                }));
            }

            // Initialize file preview for dynamically loaded forms
            function initializeFilePreview() {
                // Handle image uploads
                const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
                imageInputs.forEach(input => {
                    if (!input.dataset.previewInitialized) {
                        input.dataset.previewInitialized = 'true';
                        input.addEventListener('change', function (e) {
                            handleImagePreview(e.target);
                        });
                    }
                });

                // Handle PDF uploads
                const pdfInputs = document.querySelectorAll('input[type="file"][accept*="pdf"]');
                pdfInputs.forEach(input => {
                    if (!input.dataset.previewInitialized) {
                        input.dataset.previewInitialized = 'true';
                        input.addEventListener('change', function (e) {
                            handlePDFPreview(e.target);
                        });
                    }
                });
            }

            function handleImagePreview(input) {
                const files = Array.from(input.files);
                if (files.length === 0) return;

                let previewContainer = input.parentElement.querySelector('.file-upload-preview');
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.className = 'file-upload-preview';
                    input.parentElement.appendChild(previewContainer);
                }

                previewContainer.innerHTML = `
                    <div class="preview-header mb-3 d-flex align-items-center">
                        <i class="bi bi-images text-primary me-2"></i>
                        <span class="fw-bold">Selected Images</span>
                        <span class="od-badge od-badge-images">${files.length}</span>
                    </div>
                    <div class="od-img-grid" id="imagePreviewGrid"></div>
                `;
                previewContainer.classList.add('active');

                const grid = previewContainer.querySelector('#imagePreviewGrid');

                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'od-img-item';
                        previewItem.onclick = () => viewImage(e.target.result, file.name);
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="${file.name}">
                            <div class="od-img-overlay">
                                <i class="bi bi-zoom-in"></i>
                            </div>
                        `;
                        grid.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                });
            }

            function handlePDFPreview(input) {
                const files = Array.from(input.files);
                if (files.length === 0) return;

                let previewContainer = input.parentElement.querySelector('.file-upload-preview');
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.className = 'file-upload-preview';
                    input.parentElement.appendChild(previewContainer);
                }

                previewContainer.innerHTML = `
                    <div class="preview-header mb-3 d-flex align-items-center">
                        <i class="bi bi-file-pdf text-danger me-2"></i>
                        <span class="fw-bold">Selected Documents</span>
                        <span class="od-badge od-badge-pdfs">${files.length}</span>
                    </div>
                    <div id="pdfPreviewList" class="current-pdfs-list"></div>
                `;
                previewContainer.classList.add('active');

                const list = previewContainer.querySelector('#pdfPreviewList');

                files.forEach((file, index) => {
                    const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
                    const fileUrl = URL.createObjectURL(file);
                    
                    const previewItem = document.createElement('div');
                    previewItem.className = 'od-pdf-item';
                    previewItem.innerHTML = `
                        <div class="od-pdf-icon">
                            <i class="bi bi-file-pdf"></i>
                        </div>
                        <div class="od-pdf-info" style="cursor: pointer;" onclick="viewPDF('${fileUrl}', '${file.name.replace(/'/g, "\\'")}')">
                            <p class="od-pdf-name">${file.name}</p>
                            <span class="od-pdf-size">${fileSize} MB</span>
                        </div>
                        <div class="od-pdf-actions">
                            <button type="button" class="od-pdf-btn" title="View" onclick="viewPDF('${fileUrl}', '${file.name.replace(/'/g, "\\'")}')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    `;
                    list.appendChild(previewItem);
                });
            }
        });

        function removeExistingFile(fileUrl, type, elementId, event) {
            if (!confirm('Are you sure you want to delete this ' + type + '? This action cannot be undone.')) {
                return;
            }

            const btn = event?.currentTarget || document.querySelector(`#${elementId} button`);
            if (!btn) return;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;

            fetch('{{ route('orders.remove-file', $order->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    file_url: fileUrl,
                    type: type
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const el = document.getElementById(elementId);
                        el.style.transition = 'all 0.4s ease';
                        el.style.opacity = '0';
                        el.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            el.remove();
                            // Update counts if needed or just let it be
                        }, 400);
                    } else {
                        alert('Error: ' + data.message);
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the file.');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
        }

        /* ── Image viewer (exact match with show.blade.php) ── */
        function viewImage(url, name) {
            document.getElementById('imageViewer').src = url;
            document.getElementById('imageModalTitle').textContent = name || 'Image Viewer';
            document.getElementById('imageModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeImageModal() {
            document.getElementById('imageModal').classList.remove('active');
            document.getElementById('imageViewer').src = '';
            document.body.style.overflow = '';
        }

        /* ── PDF viewer (exact match with show.blade.php + blob support) ── */
        function viewPDF(url, name) {
            let viewerUrl = url;
            // Use Google Docs Viewer for remote URLs, but direct for local blobs
            if (!url.startsWith('blob:')) {
                viewerUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(url)}&embedded=true`;
            }
            document.getElementById('pdfViewer').src = viewerUrl;
            document.getElementById('pdfModalTitle').textContent = name || 'Document Viewer';
            document.getElementById('pdfModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closePDFModal() {
            document.getElementById('pdfModal').classList.remove('active');
            document.getElementById('pdfViewer').src = '';
            document.body.style.overflow = '';
        }

        /* ── PDF download (exact match with show.blade.php) ── */
        async function downloadPDF(url, filename) {
            const btn = event.target.closest('button');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            btn.disabled = true;
            try {
                const res = await fetch(url);
                const blob = await res.blob();
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(a.href);
            } catch (e) {
                alert('Download failed. Please try again.');
            } finally {
                btn.innerHTML = orig;
                btn.disabled = false;
            }
        }

        /* ── Legend Compatibility Aliases ── */
        function openImageModal(src) { viewImage(src, 'Image Viewer'); }

        /* ── Keyboard shortcuts ── */
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { 
                closeImageModal(); 
                closePDFModal(); 
            }
        });
    </script>
@endsection
