@extends('layouts.admin')
@section('title', 'Party Details')
@section('content')
    <div class="party-details-wrapper">
        <!-- Header Bar -->
        <div class="details-header">
            <div class="header-left">
                <div class="breadcrumb-path">
                    <a href="{{ url('/admin/dashboard') }}" class="path-link">
                        <i class="bi bi-house"></i>
                        Dashboard
                    </a>
                    <i class="bi bi-chevron-right"></i>
                    <a href="{{ route('parties.index') }}" class="path-link">Parties</a>
                    <i class="bi bi-chevron-right"></i>
                    <span class="path-current">{{ $party->name }}</span>
                </div>
                <h1 class="page-title">
                    <div class="party-avatar-large">
                        {{ strtoupper(substr($party->name, 0, 2)) }}
                    </div>
                    <div class="title-info">
                        <span class="title-text">{{ $party->name }}</span>
                        @if($party->is_foreign)
                            <span class="foreign-badge">
                                <i class="bi bi-globe"></i>
                                Foreign Party
                            </span>
                        @endif
                    </div>
                </h1>
            </div>
            <div class="header-actions">
                <a href="{{ route('parties.edit', $party->id) }}" class="action-btn edit-action">
                    <i class="bi bi-pencil-square"></i></a>
                <a href="{{ route('parties.index') }}" class="action-btn back-action">
                    <i class="bi bi-arrow-left"></i></a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-icon stat-icon-primary">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Invoices</div>
                    <div class="stat-value">{{ $invoicesCount ?? 0 }}</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon stat-icon-success">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Location</div>
                    <div class="stat-value">{{ $party->state ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon stat-icon-info">
                    <i class="bi bi-flag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Country</div>
                    <div class="stat-value">{{ $party->country ?? 'India' }}</div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Left Column - Contact & Tax Info -->
            <div class="content-column">
                <!-- Contact Information Card -->
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <h3 class="card-title">Contact Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-telephone"></i>
                                Phone Number
                            </div>
                            <div class="info-value">{{ $party->phone ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-envelope"></i>
                                Email Address
                            </div>
                            <div class="info-value">{{ $party->email ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-house"></i>
                                Address
                            </div>
                            <div class="info-value address-text">{!! nl2br(e($party->address ?: '—')) !!}</div>
                        </div>
                    </div>
                </div>

                <!-- Tax Information Card -->
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon tax-icon">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                        <h3 class="card-title">Tax Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-file-earmark-text"></i>
                                GST Number
                            </div>
                            <div class="info-value">{{ $party->gst_no ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-globe"></i>
                                Tax ID / VAT
                            </div>
                            <div class="info-value">{{ $party->tax_id ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-credit-card"></i>
                                PAN Number
                            </div>
                            <div class="info-value">{{ $party->pan_no ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Location Card -->
                <div class="info-card">
                    <div class="card-header">
                        <div class="card-icon location-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h3 class="card-title">Location Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-map"></i>
                                State
                            </div>
                            <div class="info-value">{{ $party->state ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-hash"></i>
                                State Code
                            </div>
                            <div class="info-value">{{ $party->state_code ?? '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-flag"></i>
                                Country
                            </div>
                            <div class="info-value">{{ $party->country ?? 'India' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Invoices -->
            <div class="content-column">
                <div class="invoices-card">
                    <div class="card-header">
                        <div class="card-icon invoice-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h3 class="card-title">Recent Invoices</h3>
                    </div>
                    <div class="card-body">
                        @if(isset($recentInvoices) && $recentInvoices->count())
                            <div class="invoices-list">
                                @foreach($recentInvoices as $inv)
                                    <div class="invoice-item">
                                        <div class="invoice-info">
                                            <div class="invoice-number">{{ $inv->invoice_no }}</div>
                                            <div class="invoice-meta">
                                                <span class="invoice-company">
                                                    <i class="bi bi-building"></i>
                                                    {{ $inv->company->name ?? '—' }}
                                                </span>
                                                <span class="invoice-date">
                                                    <i class="bi bi-calendar"></i>
                                                    {{ \Carbon\Carbon::parse($inv->invoice_date)->format('M d, Y') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="invoice-details">
                                            <div class="invoice-amount">
                                                ₹ {{ number_format($inv->total_invoice_value, 2) }}
                                            </div>
                                            <a href="{{ route('invoices.show', $inv->id) }}" class="invoice-view-btn">
                                                <i class="bi bi-eye"></i>
                                                View
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-invoices">
                                <div class="empty-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <p class="empty-text">No invoices found for this party yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --text-dark: #0f172a;
            --text-gray: #64748b;
            --text-light: #94a3b8;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --radius: 12px;
        }

        .party-details-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--bg-light);
            min-height: 100vh;
        }

        /* Header */
        .details-header {
            background: var(--bg-white);
            border-radius: var(--radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .breadcrumb-path {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .path-link {
            color: var(--text-gray);
            text-decoration: none;
            transition: color 0.2s;
        }

        .path-link:hover {
            color: var(--primary);
        }

        .path-current {
            color: var(--text-dark);
            font-weight: 600;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin: 0;
        }

        .party-avatar-large {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .title-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .title-text {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .foreign-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
            color: var(--primary);
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            width: fit-content;
        }

        .header-actions {
            display: flex;
            gap: 0.875rem;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: 2px solid var(--border);
        }

        .edit-action {
            background: var(--warning);
            color: white;
            border-color: var(--warning);
        }

        .edit-action:hover {
            background: #d97706;
            border-color: #d97706;
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .back-action {
            background: var(--bg-white);
            color: var(--text-gray);
        }

        .back-action:hover {
            background: var(--bg-light);
            color: var(--text-dark);
        }

        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            background: var(--bg-white);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: var(--shadow-sm);
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .stat-item:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .stat-icon-primary {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
            color: var(--primary);
        }

        .stat-icon-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .stat-icon-info {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(6, 182, 212, 0.05));
            color: var(--info);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 0.375rem;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        /* Info Card */
        .info-card,
        .invoices-card {
            background: var(--bg-white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 1.5rem 1.75rem;
            background: linear-gradient(135deg, var(--bg-light), var(--bg-white));
            border-bottom: 2px solid var(--border);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .tax-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .location-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            color: var(--warning);
        }

        .invoice-icon {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(6, 182, 212, 0.05));
            color: var(--info);
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .card-body {
            padding: 1.75rem;
        }

        /* Info Row */
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .info-label i {
            color: var(--primary);
        }

        .info-value {
            font-weight: 600;
            color: var(--text-dark);
            text-align: right;
        }

        .address-text {
            max-width: 60%;
            line-height: 1.6;
        }

        /* Invoices List */
        .invoices-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .invoice-item {
            padding: 1.25rem;
            background: var(--bg-light);
            border-radius: 10px;
            border: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }

        .invoice-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .invoice-number {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .invoice-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--text-gray);
        }

        .invoice-meta i {
            margin-right: 0.25rem;
        }

        .invoice-details {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.75rem;
        }

        .invoice-amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--success);
        }

        .invoice-view-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .invoice-view-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            color: white;
        }

        /* Empty State */
        .empty-invoices {
            text-align: center;
            padding: 3rem 2rem;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
        }

        .empty-text {
            color: var(--text-gray);
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .party-details-wrapper {
                padding: 1rem;
            }

            .details-header {
                padding: 1.5rem;
            }

            .page-title {
                flex-direction: column;
                align-items: flex-start;
            }

            .title-text {
                font-size: 1.5rem;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }

            .stats-bar {
                grid-template-columns: 1fr;
            }

            .info-row {
                flex-direction: column;
                gap: 0.5rem;
            }

            .info-value,
            .address-text {
                text-align: left;
                max-width: 100%;
            }

            .invoice-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .invoice-details {
                width: 100%;
                align-items: flex-start;
            }
        }
    </style>
@endsection