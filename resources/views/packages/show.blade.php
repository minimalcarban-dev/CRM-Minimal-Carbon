@extends('layouts.admin')

@section('title', 'Package Details')

@section('content')
    <div class="package-details-wrapper">
        <!-- Header -->
        <div class="page-header no-print">
            <div class="header-left">
                <div class="breadcrumb-nav">
                    <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <a href="{{ route('packages.index') }}" class="breadcrumb-link">Packages</a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">{{ $package->slip_id }}</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-box-seam"></i>
                    Package #{{ $package->slip_id }}
                </h1>
                <p class="page-subtitle">Created on {{ $package->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('packages.index') }}" class="btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <button onclick="window.print()" class="btn-primary-custom">
                    <i class="bi bi-printer"></i> Print Slip
                </button>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="status-cards no-print">
            <div class="status-card">
                <span class="status-label">Current Status</span>
                <span class="status-badge status-{{ strtolower($package->status) }}">
                    {{ $package->status }}
                </span>
            </div>

            <div class="status-card">
                <span class="status-label">Issued Date</span>
                <div class="status-value">
                    {{ $package->issue_date->format('d M Y') }}
                </div>
                <small class="text-muted">{{ \Carbon\Carbon::parse($package->issue_time)->format('h:i A') }}</small>
            </div>

            <div class="status-card">
                <span class="status-label">Return Date</span>
                <div class="status-value">
                    {{ $package->return_date->format('d M Y') }}
                </div>
                @if($package->status === 'Issued' && $package->return_date->isPast())
                    <span class="text-danger small fw-bold"><i class="bi bi-exclamation-circle"></i> Overdue</span>
                @else
                    <span class="text-muted small">{{ $package->return_date->diffForHumans() }}</span>
                @endif
            </div>

            <div class="status-card">
                <span class="status-label">Issued By</span>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <div class="user-avatar-sm">
                        {{ strtoupper(substr($package->creator->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="status-value">{{ $package->creator->name ?? 'Admin' }}</span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Left Column -->
            <div class="content-column">

                <!-- Package Details -->
                <div class="info-section">
                    <div class="section-header-simple">
                        <h3 class="section-title">
                            <i class="bi bi-box-seam"></i> Package Details
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="detail-group">
                            <label class="detail-label">Description</label>
                            <div class="detail-value text-pre-wrap">{{ $package->package_description }}</div>
                        </div>
                    </div>
                </div>

                <!-- Recipient Info -->
                <div class="info-section">
                    <div class="section-header-simple">
                        <h3 class="section-title">
                            <i class="bi bi-person-badge"></i> Recipient Information
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="client-info-table">
                            <div class="info-row">
                                <span class="info-label">Full Name</span>
                                <span class="info-value fw-bold">{{ $package->person_name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Mobile Number</span>
                                <span class="info-value">{{ $package->mobile_number }}</span>
                            </div>
                        </div>

                        @if($package->package_image)
                            <div class="mt-4">
                                <label class="detail-label mb-2">ID Proof / Photo</label>
                                <div class="id-proof-wrapper">
                                    <img src="{{ $package->package_image }}" alt="Recipient ID" class="id-proof-img"
                                        onclick="viewImage(this.src)">
                                    <div class="zoom-hint"><i class="bi bi-zoom-in"></i> Click to enlarge</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="content-column no-print">
                <!-- Actions -->
                @if($package->status === 'Issued' || $package->status === 'Overdue')
                    <div class="info-section bg-light-primary border-primary-light">
                        <div class="section-content text-center p-4">
                            <h4 class="text-primary fw-bold mb-3">Actions</h4>
                            @if(auth()->guard('admin')->user()->can('packages.return'))
                                <form action="{{ route('packages.return', $package->id) }}" method="POST"
                                    onsubmit="return confirm('Mark this package as returned?');">
                                    @csrf
                                    <button type="submit" class="btn-success-custom w-100 mb-3">
                                        <i class="bi bi-check-circle-fill"></i> Mark as Returned
                                    </button>
                                </form>
                            @endif

                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle"></i> This actions will update the inventory status.
                            </p>
                        </div>
                    </div>
                @endif

                @if($package->status === 'Returned')
                    <div class="info-section bg-light-success border-success-light">
                        <div class="section-content text-center p-4">
                            <div class="success-icon mb-3">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h4 class="text-success fw-bold mb-2">Package Returned</h4>
                            <p class="text-muted mb-0">This transaction is complete.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Printable Slip Section (Hidden on Screen) -->
        <div class="printable-slip">
            <div class="slip-header">
                <div class="slip-logo">
                    <!-- Replace with your actual logo if available, or just text -->
                    <h2>CRM Minimal</h2>
                </div>
                <div class="slip-title">
                    <h1>PACKAGE HANDOVER SLIP</h1>
                    <p>Receipt #: {{ $package->slip_id }}</p>
                </div>
            </div>

            <div class="slip-grid">
                <div class="slip-box">
                    <h3>Issued From:</h3>
                    <p><strong>CRM Minimal Admin</strong></p>
                    <p>123 Diamond Street, NY</p>
                    <p>Authorized By: {{ $package->creator->name ?? 'Admin' }}</p>
                    <p>Date: {{ $package->issue_date->format('d M Y') }}</p>
                </div>
                <div class="slip-box">
                    <h3>Issued To:</h3>
                    <p><strong>{{ $package->person_name }}</strong></p>
                    <p>Mobile: {{ $package->mobile_number }}</p>
                    <p>Return Due: <strong>{{ $package->return_date->format('d M Y') }}</strong></p>
                </div>
            </div>

            <div class="slip-body">
                <h3>Package Details</h3>
                <div class="slip-description">
                    {{ $package->package_description }}
                </div>
            </div>

            <div class="slip-footer">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p>Issuer Signature</p>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p>Receiver Signature</p>
                </div>
            </div>

            <div class="slip-legal">
                <p>By signing above, the receiver acknowledges receipt of the package in good condition and agrees to return
                    it by the specified due date.</p>
                <p>Printed on: {{ now()->format('d M Y, h:i A') }}</p>
            </div>
        </div>

    </div>

    <!-- Image Viewer Modal (Same as Orders) -->
    <div id="imageModal" class="pdf-modal no-print" onclick="closeImageModal()">
        <div class="pdf-modal-content" onclick="event.stopPropagation()">
            <div class="pdf-modal-header">
                <h3 id="imageModalTitle">Image Viewer</h3>
                <button class="pdf-modal-close" onclick="closeImageModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="pdf-modal-body" style="background: #000; display:flex; justify-content:center; align-items:center;">
                <img id="imageViewer" src="" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            :root {
                --primary: #6366f1;
                --primary-dark: #4f46e5;
                --success: #10b981;
                --danger: #ef4444;
                --dark: #1e293b;
                --gray: #64748b;
                --light-gray: #f8fafc;
                --border: #e2e8f0;
            }

            /* Screen Styles */
            .package-details-wrapper {
                max-width: 1200px;
                margin: 0 auto;
                padding: 1.5rem;
            }

            .page-header {
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
                padding: 2rem;
                border-radius: 16px;
                border: 2px solid rgba(99, 102, 241, 0.1);
                margin-bottom: 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .header-content {
                flex: 1;
            }

            .breadcrumb-nav {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.85rem;
                color: var(--gray);
                margin-bottom: 0.5rem;
            }

            .breadcrumb-link {
                color: var(--gray);
                text-decoration: none;
                transition: color 0.2s;
            }

            .breadcrumb-link:hover {
                color: var(--primary);
            }

            .breadcrumb-separator {
                font-size: 0.7rem;
                color: #cbd5e1;
            }

            .breadcrumb-current {
                color: var(--primary);
                font-weight: 600;
            }

            .page-title {
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .page-subtitle {
                color: var(--gray);
                margin-top: 0.25rem;
                font-size: 0.95rem;
            }

            /* Buttons */
            .btn-primary-custom,
            .btn-secondary-custom,
            .btn-success-custom {
                padding: 0.65rem 1.25rem;
                border-radius: 12px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                text-decoration: none;
                transition: all 0.2s;
                border: none;
                cursor: pointer;
                font-size: 0.95rem;
            }

            .btn-primary-custom {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
            }

            .btn-primary-custom:hover {
                transform: translateY(-2px);
                color: white;
            }

            .btn-secondary-custom {
                background: white;
                color: var(--gray);
                border: 2px solid var(--border);
            }

            .btn-secondary-custom:hover {
                background: var(--light-gray);
                color: var(--dark);
            }

            .btn-success-custom {
                background: linear-gradient(135deg, var(--success), #059669);
                color: white;
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            }

            .btn-success-custom:hover {
                transform: translateY(-2px);
                color: white;
            }

            /* Status Cards */
            .status-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .status-card {
                background: white;
                border-radius: 16px;
                padding: 1.5rem;
                border: 2px solid var(--border);
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .status-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: var(--gray);
                font-weight: 600;
            }

            .status-value {
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--dark);
            }

            .status-badge {
                display: inline-block;
                padding: 0.35rem 0.75rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                text-transform: uppercase;
            }

            .status-issued {
                background: #dbeafe;
                color: #1e40af;
            }

            .status-returned {
                background: #d1fae5;
                color: #065f46;
            }

            .status-overdue {
                background: #fee2e2;
                color: #991b1b;
            }

            .user-avatar-sm {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                background: var(--primary);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.7rem;
                font-weight: bold;
            }

            /* Content Grid */
            .content-grid {
                display: grid;
                grid-template-columns: 1fr 350px;
                gap: 2rem;
            }

            @media (max-width: 992px) {
                .content-grid {
                    grid-template-columns: 1fr;
                }
            }

            /* Info Sections */
            .info-section {
                background: white;
                border-radius: 16px;
                border: 2px solid var(--border);
                overflow: hidden;
                margin-bottom: 2rem;
            }

            .section-header-simple {
                padding: 1.25rem 1.5rem;
                border-bottom: 2px solid var(--border);
                background: #f8fafc;
            }

            .section-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--dark);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .section-title i {
                color: var(--primary);
            }

            .section-content {
                padding: 1.5rem;
            }

            .detail-group {
                margin-bottom: 1.5rem;
            }

            .detail-label {
                font-size: 0.85rem;
                color: var(--gray);
                font-weight: 600;
                margin-bottom: 0.5rem;
                display: block;
            }

            .detail-value {
                font-size: 1rem;
                color: var(--dark);
                line-height: 1.6;
            }

            .text-pre-wrap {
                white-space: pre-wrap;
            }

            .client-info-table .info-row {
                display: flex;
                justify-content: space-between;
                padding: 0.75rem 0;
                border-bottom: 1px solid var(--border);
            }

            .client-info-table .info-row:last-child {
                border-bottom: none;
            }

            .info-row .info-label {
                color: var(--gray);
                font-size: 0.95rem;
            }

            .info-row .info-value {
                color: var(--dark);
                font-size: 0.95rem;
                text-align: right;
            }

            .id-proof-wrapper {
                position: relative;
                border-radius: 12px;
                overflow: hidden;
                border: 2px solid var(--border);
                cursor: pointer;
                transition: all 0.2s;
                max-width: 200px;
            }

            .id-proof-wrapper:hover {
                border-color: var(--primary);
            }

            .id-proof-img {
                width: 100%;
                display: block;
            }

            .zoom-hint {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.6);
                color: white;
                font-size: 0.75rem;
                padding: 0.25rem;
                text-align: center;
                opacity: 0;
                transition: opacity 0.2s;
            }

            .id-proof-wrapper:hover .zoom-hint {
                opacity: 1;
            }

            /* Action Box Colors */
            .bg-light-primary {
                background: rgba(99, 102, 241, 0.05) !important;
            }

            .border-primary-light {
                border-color: rgba(99, 102, 241, 0.2) !important;
            }

            .bg-light-success {
                background: rgba(16, 185, 129, 0.05) !important;
            }

            .border-success-light {
                border-color: rgba(16, 185, 129, 0.2) !important;
            }

            .success-icon {
                font-size: 3rem;
                color: var(--success);
            }

            /* Printable Slip (Hidden by Default) */
            .printable-slip {
                display: none;
            }

            /* Modal Styles */
            .pdf-modal {
                display: none;
                position: fixed;
                z-index: 1050;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                background-color: rgba(0, 0, 0, 0.8);
                backdrop-filter: blur(5px);
            }

            .pdf-modal-content {
                position: relative;
                background-color: #fefefe;
                margin: 2% auto;
                padding: 0;
                border: 1px solid #888;
                width: 90%;
                height: 90%;
                border-radius: 12px;
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
                display: flex;
                flex-direction: column;
            }

            .pdf-modal-header {
                padding: 1rem 1.5rem;
                border-bottom: 1px solid var(--border);
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #fff;
                border-radius: 12px 12px 0 0;
            }

            .pdf-modal-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                color: var(--gray);
                cursor: pointer;
                transition: color 0.2s;
            }

            .pdf-modal-close:hover {
                color: var(--danger);
            }

            .pdf-modal-body {
                flex: 1;
                overflow: hidden;
                position: relative;
            }


            @media print {
                body {
                    background: white;
                    -webkit-print-color-adjust: exact;
                }

                .no-print,
                .sidebar,
                .main-header,
                footer {
                    display: none !important;
                }

                .content-wrapper {
                    margin: 0;
                    padding: 0;
                    background: white;
                }

                .package-details-wrapper {
                    padding: 0;
                    margin: 0;
                    max-width: 100%;
                }

                .printable-slip {
                    display: block;
                    padding: 2rem;
                    border: 2px solid #000;
                }

                .slip-header {
                    display: flex;
                    justify-content: space-between;
                    border-bottom: 2px solid #000;
                    padding-bottom: 1rem;
                    margin-bottom: 2rem;
                }

                .slip-title h1 {
                    font-size: 24px;
                    font-weight: bold;
                    margin: 0;
                    text-transform: uppercase;
                }

                .slip-title p {
                    margin: 5px 0 0;
                }

                .slip-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 2rem;
                    margin-bottom: 2rem;
                }

                .slip-box h3 {
                    font-size: 16px;
                    border-bottom: 1px solid #ccc;
                    padding-bottom: 5px;
                    margin-bottom: 10px;
                }

                .slip-box p {
                    margin: 5px 0;
                    font-size: 14px;
                }

                .slip-body {
                    margin-bottom: 4rem;
                }

                .slip-body h3 {
                    font-size: 16px;
                    background: #eee;
                    padding: 5px 10px;
                    margin-bottom: 1rem;
                }

                .slip-description {
                    padding: 10px;
                    border: 1px solid #ccc;
                    min-height: 150px;
                    white-space: pre-wrap;
                }

                .slip-footer {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 2rem;
                }

                .signature-box {
                    width: 40%;
                    text-align: center;
                }

                .signature-line {
                    border-top: 1px solid #000;
                    margin-bottom: 5px;
                    height: 50px;
                    margin-top: 50px;
                }

                .slip-legal {
                    font-size: 10px;
                    color: #666;
                    text-align: center;
                    border-top: 1px solid #eee;
                    padding-top: 10px;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function viewImage(src) {
                document.getElementById('imageViewer').src = src;
                document.getElementById('imageModal').style.display = 'block';
            }

            function closeImageModal() {
                document.getElementById('imageModal').style.display = 'none';
            }

            // Close modal when clicking outside
            window.onclick = function (event) {
                var modal = document.getElementById('imageModal');
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        </script>
    @endpush
@endsection