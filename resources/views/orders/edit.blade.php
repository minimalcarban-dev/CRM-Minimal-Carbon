@extends('layouts.admin')

@section('title', 'Edit Order')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Edit Order #{{ $order->id }}</h1>
            <p class="page-subtitle">Update order details and related information</p>
        </div>
        <div class="header-right">
            <a href="{{ route('orders.index') }}" class="btn-primary-custom btn-sm">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Orders</span>
            </a>
        </div>
    </div>

    <!-- Currently Uploaded Files Section -->
    @if($order->images || $order->order_pdfs)
        <div class="current-files-section mb-4">
            <div class="card-custom p-4">
                <h5 class="section-title mb-3">
                    <i class="bi bi-folder2-open"></i> Currently Uploaded Files
                </h5>

                <div class="row">
                    <!-- Current Images -->
                    @if($order->images)
                        <div class="col-md-6 mb-3">
                            <div class="file-section">
                                <div class="file-section-header">
                                    <i class="bi bi-images"></i>
                                    <span>Product Images ({{ count(json_decode($order->images, true) ?? []) }})</span>
                                </div>
                                <div class="current-images-grid">
                                    @foreach(json_decode($order->images, true) ?? [] as $index => $image)
                                        <div class="current-image-item">
                                            <img src="{{ $image['url'] }}" alt="Image {{ $index + 1 }}"
                                                onclick="openImageModal(this.src)">
                                            <div class="image-overlay">
                                                <i class="bi bi-zoom-in"></i>
                                            </div>
                                            <div class="image-label">Image {{ $index + 1 }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Current PDFs -->
                    @if($order->order_pdfs)
                        <div class="col-md-6 mb-3">
                            <div class="file-section">
                                <div class="file-section-header">
                                    <i class="bi bi-file-pdf"></i>
                                    <span>PDF Documents ({{ count(json_decode($order->order_pdfs, true) ?? []) }})</span>
                                </div>
                                <div class="current-pdfs-list">
                                    @foreach(json_decode($order->order_pdfs, true) ?? [] as $index => $pdf)
                                        @php
                                            $path = is_array($pdf) ? $pdf['url'] : $pdf;
                                            $name = is_array($pdf) ? $pdf['name'] : basename($pdf);
                                        @endphp
                                        <a href="{{ asset($path) }}" target="_blank" class="current-pdf-item">
                                            <div class="pdf-icon">
                                                <i class="bi bi-file-pdf-fill"></i>
                                            </div>
                                            <div class="pdf-info">
                                                <div class="pdf-name">{{ $name }}</div>
                                                <div class="pdf-action">Click to view <i class="bi bi-box-arrow-up-right"></i></div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="card-custom p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Oops!</strong> Please correct the following errors:
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('orders.update', $order->id) }}" method="POST" enctype="multipart/form-data"
            id="editOrderForm">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-semibold">Order Type <span class="text-danger">*</span></label>
                <select name="order_type" id="order_type" class="form-select-custom" required>
                    <option value="ready_to_ship" {{ $order->order_type == 'ready_to_ship' ? 'selected' : '' }}>Ready to Ship
                    </option>
                    <option value="custom_diamond" {{ $order->order_type == 'custom_diamond' ? 'selected' : '' }}>Custom
                        Diamond</option>
                    <option value="custom_jewellery" {{ $order->order_type == 'custom_jewellery' ? 'selected' : '' }}>Custom
                        Jewellery</option>
                </select>
            </div>

            <div id="orderFormFields">
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-hourglass-split"></i> Loading form...
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn-primary-custom px-4">
                    <i class="bi bi-save me-1"></i> Update Order
                </button>
            </div>
        </form>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f8fafc;
            --border: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
        }

        /* Current Files Section */
        .current-files-section .card-custom {
            background: white;
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
            background: var(--light-gray);
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
            background: white;
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
            background: white;
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

        /* Image Modal */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.95);
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 85%;
            border-radius: 8px;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 48px;
            font-weight: 300;
            cursor: pointer;
            transition: 0.3s;
        }

        .modal-close:hover {
            color: #bbb;
        }

        /* File Upload Preview Styles - To be added to your form fields */
        .file-upload-preview {
            margin-top: 1rem;
            display: none;
        }

        .file-upload-preview.active {
            display: block;
        }

        .preview-header {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .preview-header i {
            color: var(--primary);
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
        }

        .preview-item {
            position: relative;
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 0.5rem;
            background: var(--light-gray);
            text-align: center;
        }

        .preview-item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }

        .preview-item .file-name {
            font-size: 0.75rem;
            color: var(--dark);
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
            background: white;
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const type = document.getElementById('order_type').value;
            loadForm(type);

            document.getElementById('order_type').addEventListener('change', function () {
                loadForm(this.value);
            });

            function loadForm(type) {
                const container = document.getElementById('orderFormFields');
                container.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-hourglass-split"></i> Loading...</div>';

                if (!type) return;

                fetch(`/admin/orders/form/${type}?edit=true&id={{ $order->id }}`)
                    .then(response => response.text())
                    .then(html => {
                        container.innerHTML = html;
                        initializeFilePreview();
                    })
                    .catch(() => {
                        container.innerHTML = `<div class="alert alert-danger">Error loading form. Please try again.</div>`;
                    });
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
                                        <div class="preview-header">
                                            <i class="bi bi-images"></i>
                                            <span>Selected Images (${files.length})</span>
                                        </div>
                                        <div class="preview-grid" id="imagePreviewGrid"></div>
                                    `;
                previewContainer.classList.add('active');

                const grid = previewContainer.querySelector('#imagePreviewGrid');

                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                                                <img src="${e.target.result}" alt="${file.name}">
                                                <div class="file-name">${file.name}</div>
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
                                        <div class="preview-header">
                                            <i class="bi bi-file-pdf"></i>
                                            <span>Selected PDFs (${files.length})</span>
                                        </div>
                                        <div id="pdfPreviewList"></div>
                                    `;
                previewContainer.classList.add('active');

                const list = previewContainer.querySelector('#pdfPreviewList');

                files.forEach((file, index) => {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'pdf-preview-item';
                    const fileSize = (file.size / 1024).toFixed(2);
                    previewItem.innerHTML = `
                                            <div class="pdf-preview-icon">
                                                <i class="bi bi-file-pdf-fill"></i>
                                            </div>
                                            <div class="pdf-preview-info">
                                                <div class="pdf-preview-name">${file.name}</div>
                                                <div class="pdf-preview-size">${fileSize} KB</div>
                                            </div>
                                        `;
                    list.appendChild(previewItem);
                });
            }
        });

        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImg.src = src;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
@endsection