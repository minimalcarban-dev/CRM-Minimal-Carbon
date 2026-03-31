@extends('layouts.admin')

@section('title', 'Package Details')

@section('content')
    <div class="tracker-page">
        <!-- Header -->
        <div class="page-header no-print" style="margin-bottom: 2rem;">
            <div class="header-content d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="header-left">
                    <div class="breadcrumb-nav mb-2">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link text-decoration-none text-muted">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right text-muted mx-2" style="font-size: 0.75rem;"></i>
                        <a href="{{ route('packages.index') }}" class="breadcrumb-link text-decoration-none text-muted">Packages</a>
                        <i class="bi bi-chevron-right text-muted mx-2" style="font-size: 0.75rem;"></i>
                        <span class="breadcrumb-current text-primary fw-semibold">{{ $package->slip_id }}</span>
                    </div>
                    <h1 class="page-title m-0 d-flex align-items-center gap-2" style="font-size: 1.75rem; font-weight: 700; color: #1e293b;">
                        <i class="bi bi-box-seam" style="color: #6366f1;"></i>
                        Package #{{ $package->slip_id }}
                    </h1>
                    <p class="page-subtitle text-muted mt-1 mb-0" style="font-size: 0.95rem;">Created on {{ $package->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="header-actions d-flex gap-2">
                    <a href="{{ route('packages.index') }}" class="btn btn-light border fw-semibold d-inline-flex align-items-center gap-2" style="padding: 0.65rem 1.25rem; border-radius: 12px;">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button onclick="window.print()" class="btn btn-primary fw-semibold d-inline-flex align-items-center gap-2" style="padding: 0.65rem 1.25rem; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #4f46e5); border: none;">
                        <i class="bi bi-printer"></i> Print Slip
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row no-print">
            <!-- Sidebar -->
            <div class="col-lg-4 mb-4">
                <div class="d-flex flex-column gap-4" style="position: sticky; top: 1.5rem;">
                    
                    <!-- Status Cards in a Column -->
                    <div class="tracker-table-card" style="padding: 1.5rem;">
                        <h4 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                            <i class="bi bi-info-circle text-primary me-2"></i>Status Summary
                        </h4>
                        
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <span class="d-block text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Current Status</span>
                                <div>{!! str_replace('badge', 'tracker-badge', $package->status_badge) !!}</div>
                            </div>

                            <div>
                                <span class="d-block text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Issued Date</span>
                                <div style="font-size: 1.1rem; font-weight: 600; color: var(--dark);">{{ $package->issue_date->format('d M Y') }}</div>
                                <div class="text-muted small mt-1">{{ \Carbon\Carbon::parse($package->issue_time)->format('h:i A') }}</div>
                            </div>

                            <div>
                                <span class="d-block text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Return Due</span>
                                <div style="font-size: 1.1rem; font-weight: 600; color: var(--dark);">{{ $package->return_date->format('d M Y') }}</div>
                                @if ($package->status === 'Issued' && $package->return_date->isPast())
                                    <div class="text-danger small fw-bold mt-1"><i class="bi bi-exclamation-circle"></i> Overdue</div>
                                @else
                                    <div class="text-muted small mt-1">{{ $package->return_date->diffForHumans() }}</div>
                                @endif
                            </div>

                            <div>
                                <span class="d-block text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Issued By</span>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold;">
                                        {{ strtoupper(substr($package->creator->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span style="font-weight: 600; color: var(--dark);">{{ $package->creator->name ?? 'Admin' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if ($package->status === 'Issued' || $package->status === 'Overdue')
                        <div class="tracker-table-card border-primary" style="padding: 1.5rem; background: rgba(99, 102, 241, 0.03);">
                            <div class="text-center">
                                <h4 class="text-primary fw-bold mb-3">Actions</h4>
                                @if (auth()->guard('admin')->user()->can('packages.return'))
                                    <form action="{{ route('packages.return', $package->id) }}" method="POST"
                                        onsubmit="return confirm('Mark this package as returned?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100 fw-bold mb-2 py-2" style="border-radius: 10px;">
                                            <i class="bi bi-check-circle-fill me-1"></i> Mark as Returned
                                        </button>
                                    </form>
                                @endif
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="bi bi-info-circle"></i> This action will update the inventory status.
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($package->status === 'Returned')
                        <div class="tracker-table-card border-success" style="padding: 1.5rem; background: rgba(16, 185, 129, 0.03);">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="bi bi-check-lg text-success" style="font-size: 2.5rem;"></i>
                                </div>
                                <h4 class="text-success fw-bold mb-2">Package Returned</h4>
                                <p class="text-muted mb-0 small">This transaction is complete.</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <!-- Main Details -->
            <div class="col-lg-8">
                <!-- Package Details -->
                <div class="tracker-table-card mb-4" style="padding: 0; overflow: hidden;">
                    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); background: #f8fafc;">
                        <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="bi bi-box-seam text-primary"></i> Package Details
                        </h3>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div class="mb-4">
                            <label class="d-block text-muted fw-semibold mb-2" style="font-size: 0.85rem;">Description</label>
                            <div style="font-size: 1rem; color: var(--dark); line-height: 1.6; white-space: pre-wrap; background: #f8fafc; padding: 1rem; border-radius: 10px; border: 1px solid var(--border);">{{ $package->package_description }}</div>
                        </div>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                <span class="text-muted">Party Type</span>
                                <span style="color: var(--dark); font-weight: 500;">{{ $package->party_type ?: '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                <span class="text-muted">Company</span>
                                <span style="color: var(--dark); font-weight: 500;">{{ $package->company_name ?: '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                <span class="text-muted">GST / PAN</span>
                                <span style="color: var(--dark); font-weight: 500;">
                                    {{ $package->gst_number ?: '-' }} / {{ $package->pan_number ?: '-' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                <span class="text-muted">Purpose</span>
                                <span style="color: var(--dark); font-weight: 500;">{{ $package->purpose_of_handover ?: '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                <span class="text-muted">Handover Location</span>
                                <span style="color: var(--dark); font-weight: 500;">{{ $package->handover_location ?: '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Handover Mode</span>
                                <span style="color: var(--dark); font-weight: 500;">{{ $package->handover_mode ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($package->stock_id || $package->diamond_shape || $package->diamond_size || $package->diamond_color || $package->diamond_clarity || $package->diamond_carat)
                    <div class="tracker-table-card mb-4" style="padding: 0; overflow: hidden;">
                        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); background: #f8fafc;">
                            <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-gem text-info"></i> Diamond Snapshot
                            </h3>
                        </div>
                        <div style="padding: 1.5rem;">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                    <span class="text-muted">Stock ID</span>
                                    <span style="color: var(--dark); font-weight: 700;">{{ $package->stock_id ?: '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                    <span class="text-muted">Shape</span>
                                    <span style="color: var(--dark); font-weight: 500;">{{ $package->diamond_shape ?: '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                    <span class="text-muted">Size</span>
                                    <span style="color: var(--dark); font-weight: 500;">{{ $package->diamond_size ?: '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                    <span class="text-muted">Color</span>
                                    <span style="color: var(--dark); font-weight: 500;">{{ $package->diamond_color ?: '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                    <span class="text-muted">Clarity</span>
                                    <span style="color: var(--dark); font-weight: 500;">{{ $package->diamond_clarity ?: '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Carat</span>
                                    <span style="color: var(--dark); font-weight: 500;">{{ $package->diamond_carat ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Recipient Info -->
                <div class="tracker-table-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); background: #f8fafc;">
                        <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="bi bi-person-badge text-success"></i> Recipient Information
                        </h3>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div class="row">
                            <div class="col-md-{{ $package->package_image_url ? '7' : '12' }}">
                                <div class="d-flex flex-column gap-3 h-100 justify-content-center">
                                    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                        <span class="text-muted">Full Name</span>
                                        <span style="color: var(--dark); font-weight: 700;">{{ $package->person_name }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pb-3">
                                        <span class="text-muted">Mobile Number</span>
                                        <span style="color: var(--dark); font-weight: 500;">{{ $package->mobile_number }}</span>
                                    </div>
                                </div>
                            </div>

                            @if ($package->package_image_url)
                                <div class="col-md-5 d-flex flex-column align-items-center justify-content-center mt-4 mt-md-0 border-start ps-md-4">
                                    <label class="d-block text-muted fw-semibold mb-2" style="font-size: 0.85rem;">ID Proof / Photo</label>
                                    <div style="position: relative; border-radius: 12px; overflow: hidden; border: 2px solid var(--border); cursor: pointer; max-width: 200px; transition: border-color 0.2s;" class="image-hover-wrapper">
                                        <img src="{{ $package->package_image_url }}" onerror="this.style.display='none';"
                                            alt="Recipient ID" style="width: 100%; display: block;" onclick="viewImage(this.src)">
                                        <div class="zoom-hint d-flex align-items-center justify-content-center w-100 p-2" style="position: absolute; bottom: 0; background: rgba(0,0,0,0.6); color: white; font-size: 0.75rem; opacity: 0; transition: opacity 0.2s; pointer-events: none;">
                                            <i class="bi bi-zoom-in me-1"></i> Click to enlarge
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
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
                <p>By signing above, the receiver acknowledges receipt of the package in good condition and agrees to return it by the specified due date.</p>
                <p>Printed on: {{ now()->format('d M Y, h:i A') }}</p>
            </div>
        </div>

    </div>

    <!-- Image Viewer Modal (Same as Orders) -->
    <div id="imageModal" class="pdf-modal no-print" onclick="closeImageModal()">
        <div class="pdf-modal-content" onclick="event.stopPropagation()">
            <div class="pdf-modal-header">
                <h3 id="imageModalTitle" class="m-0 fs-5">Image Viewer</h3>
                <button class="pdf-modal-close border-0 bg-transparent fs-4" style="color: #64748b;" onclick="closeImageModal()">
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
            /* Hover logic for image */
            .image-hover-wrapper:hover {
                border-color: var(--primary) !important;
            }
            .image-hover-wrapper:hover .zoom-hint {
                opacity: 1 !important;
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
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #fff;
                border-bottom: 1px solid var(--border);
                border-radius: 12px 12px 0 0;
            }

            .pdf-modal-body {
                flex: 1;
                overflow: hidden;
                position: relative;
                border-radius: 0 0 12px 12px;
            }

            .pdf-modal-close:hover {
                color: #ef4444 !important;
            }

            @media screen and (max-width: 640px) {
                .pdf-modal-content {
                    width: 95%;
                    height: 92%;
                    margin: 4% auto;
                }
            }

            /* Printable Slip (Hidden by Default) */
            .printable-slip {
                display: none;
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
                    margin: 0 !important;
                    padding: 0 !important;
                    background: white;
                }

                .tracker-page {
                    padding: 0 !important;
                    margin: 0 !important;
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

            /* Dark mode specific for standard card backgrounds if needed */
            [data-theme="dark"] .tracker-table-card {
                background: var(--bg-card, #1e293b);
                border-color: rgba(148, 163, 184, 0.34);
            }
            [data-theme="dark"] .tracker-table-card > div.bg-f8fafc, [data-theme="dark"] .tracker-table-card > div[style*="#f8fafc"] {
                background: rgba(15, 23, 42, 0.4) !important;
                border-color: rgba(148, 163, 184, 0.22) !important;
            }
            [data-theme="dark"] .pdf-modal-content {
                background: var(--bg-card, #1e293b);
                border-color: rgba(148, 163, 184, 0.34);
            }
            [data-theme="dark"] .pdf-modal-header {
                background: var(--bg-card, #1e293b);
                border-color: rgba(148, 163, 184, 0.34);
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
            window.onclick = function(event) {
                var modal = document.getElementById('imageModal');
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        </script>
    @endpush
@endsection
