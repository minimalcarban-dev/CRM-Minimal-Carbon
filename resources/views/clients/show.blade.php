@extends('layouts.admin')
@section('title', 'Client Details')
@section('content')
    <div class="client-detail-wrapper">
        <!-- Header -->
        <div class="detail-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-trail">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-item">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right"></i>
                        <a href="{{ route('clients.index') }}" class="breadcrumb-item">Clients</a>
                        <i class="bi bi-chevron-right"></i>
                        <span class="breadcrumb-active">{{ $client->name ?? 'Client' }}</span>
                    </div>
                    <h1 class="page-heading">
                        <i class="bi bi-person-fill"></i>
                        {{ $client->name ?? 'Unnamed Client' }}
                    </h1>
                </div>
                <div class="header-right">
                    <a href="{{ route('clients.index') }}" class="back-btn">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to Clients</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Client Info Card -->
        <div class="info-cards-grid">
            <div class="info-card">
                <div class="info-icon"><i class="bi bi-envelope"></i></div>
                <div class="info-content">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $client->email ?? '-' }}</div>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="bi bi-telephone"></i></div>
                <div class="info-content">
                    <div class="info-label">Mobile</div>
                    <div class="info-value">{{ $client->mobile ?? '-' }}</div>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="bi bi-receipt"></i></div>
                <div class="info-content">
                    <div class="info-label">Tax ID / VAT ID</div>
                    <div class="info-value">{{ $client->tax_id ?? '-' }}</div>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="bi bi-basket"></i></div>
                <div class="info-content">
                    <div class="info-label">Total Orders</div>
                    <div class="info-value">{{ $client->orders_count ?? 0 }}</div>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="bi bi-currency-dollar"></i></div>
                <div class="info-content">
                    <div class="info-label">Total Spend</div>
                    <div class="info-value">${{ number_format($client->total_spend ?? 0, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Address Card -->
        @if ($client->address)
            <div class="address-card">
                <div class="card-header">
                    <i class="bi bi-geo-alt"></i>
                    <span>Address</span>
                </div>
                <div class="card-body">
                    {{ $client->address }}
                </div>
            </div>
        @endif

        <!-- Order History -->
        <div class="orders-section">
            <div class="section-header">
                <h2><i class="bi bi-clock-history"></i> Order History</h2>
            </div>
            <div class="table-card">
                @if ($orders->count())
                    <div class="table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Type</th>
                                    <th>Company</th>
                                    <th>Gross Sell</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td><span class="id-badge">#{{ $order->id }}</span></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</td>
                                        <td>{{ $order->company->name ?? '-' }}</td>
                                        <td>${{ number_format($order->gross_sell ?? 0, 2) }}</td>
                                        <td>
                                            <span class="status-badge">{{ $order->diamond_status ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('orders.show', $order->id) }}" class="view-btn">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-wrapper">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No orders found for this client.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --success-color: #10b981;
            --text-dark: #0f172a;
            --text-gray: #64748b;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --radius: 12px;
        }

        .client-detail-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--bg-light);
            min-height: 100vh;
        }

        .detail-header {
            background: var(--bg-white);
            border-radius: var(--radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .breadcrumb-trail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 1rem;
        }

        .breadcrumb-item {
            color: var(--text-gray);
            text-decoration: none;
        }

        .breadcrumb-item:hover {
            color: var(--primary-color);
        }

        .breadcrumb-active {
            color: var(--text-dark);
            font-weight: 600;
        }

        .page-heading {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-heading i {
            color: var(--primary-color);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--bg-white);
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .back-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .info-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--bg-white);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .info-content {
            flex: 1;
            min-width: 0;
        }

        .info-label {
            font-size: 0.8rem;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .address-card {
            background: var(--bg-white);
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .card-header {
            padding: 1rem 1.5rem;
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-header i {
            color: var(--primary-color);
        }

        .card-body {
            padding: 1.5rem;
            color: var(--text-gray);
            line-height: 1.6;
        }

        .orders-section {
            margin-bottom: 2rem;
        }

        .section-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-header h2 i {
            color: var(--primary-color);
        }

        .table-card {
            background: var(--bg-white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.8rem;
            text-transform: uppercase;
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
        }

        .orders-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .orders-table tbody tr:hover {
            background: var(--bg-light);
        }

        .id-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            color: var(--primary-color);
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--bg-light);
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.4rem 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-white);
            color: var(--text-gray);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .view-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .pagination-wrapper {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .empty-state {
            padding: 3rem;
            text-align: center;
            color: var(--text-gray);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        @media (max-width: 768px) {
            .client-detail-wrapper {
                padding: 1rem;
            }

            .detail-header {
                padding: 1.25rem;
                flex-direction: column;
                gap: 1.25rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1.25rem;
                align-items: stretch;
            }

            .page-heading {
                font-size: 1.5rem;
                word-break: break-word;
            }

            .header-right,
            .back-btn {
                width: 100%;
                justify-content: center;
            }

            .back-btn {
                min-height: 48px;
            }

            .info-cards-grid {
                grid-template-columns: 1fr;
            }

            .card-header {
                padding: 1.25rem;
            }

            .card-body {
                padding: 1.25rem;
            }

            /* Order Table to Mobile Card View */
            .table-wrapper {
                background: transparent;
                border: none;
                overflow-x: visible;
            }

            .table-card {
                background: transparent;
                box-shadow: none;
            }

            .orders-table {
                display: block;
            }

            .orders-table thead {
                display: none !important;
            }

            .orders-table tbody,
            .orders-table tr,
            .orders-table td {
                display: block !important;
            }

            .orders-table tbody tr {
                background: var(--bg-white);
                border: 1px solid var(--border-color);
                border-radius: var(--radius);
                box-shadow: var(--shadow-sm);
                padding: 1.25rem;
                margin-bottom: 1rem;
                display: grid !important;
                grid-template-columns: 1fr auto;
                grid-template-rows: auto auto auto auto auto;
                gap: 0.5rem;
            }

            .orders-table td {
                padding: 0 !important;
                border: none !important;
                display: flex;
                align-items: center;
                background: var(--bg-light);
                padding: 0.5rem 0.75rem !important;
                border-radius: 6px;
                font-size: 0.85rem;
            }

            /* 1) Order ID */
            .orders-table td:nth-child(1) {
                grid-column: 1;
                grid-row: 1;
                background: transparent !important;
                padding: 0 !important;
                font-size: 1.1rem;
            }

            /* 2) Actions */
            .orders-table td:nth-child(7) {
                grid-column: 2;
                grid-row: 1;
                background: transparent !important;
                padding: 0 !important;
                justify-self: end;
            }

            /* 3) Status Badge */
            .orders-table td:nth-child(5) {
                grid-column: 1 / span 2;
                grid-row: 2;
                background: transparent !important;
                padding: 0 !important;
                margin-bottom: 0.5rem;
            }

            .orders-table td:nth-child(2)::before {
                content: "Type: ";
                color: var(--text-gray);
                font-weight: 500;
                margin-right: 0.5rem;
            }

            .orders-table td:nth-child(3)::before {
                content: "Company: ";
                color: var(--text-gray);
                font-weight: 500;
                margin-right: 0.5rem;
            }

            .orders-table td:nth-child(4)::before {
                content: "Gross Sell: ";
                color: var(--text-gray);
                font-weight: 500;
                margin-right: 0.5rem;
            }

            .orders-table td:nth-child(6)::before {
                content: "Date: ";
                color: var(--text-gray);
                font-weight: 500;
                margin-right: 0.5rem;
            }

            .view-btn {
                min-height: 44px;
                min-width: 44px;
                justify-content: center;
            }
        }

        --bg-white: #1e293b;
        --border-color: rgba(148, 163, 184, 0.28);
        --shadow-sm: 0 1px 3px rgba(2, 6, 23, 0.45);
        }

        [data-theme="dark"] .card-header,
        [data-theme="dark"] .orders-table th {
            background: #162033;
        }

        [data-theme="dark"] .orders-table tbody tr:hover {
            background: #162033;
        }

        [data-theme="dark"] .back-btn,
        [data-theme="dark"] .view-btn {
            background: #0f172a;
            color: #cbd5e1;
            border-color: rgba(148, 163, 184, 0.28);
        }

        [data-theme="dark"] .back-btn:hover,
        [data-theme="dark"] .view-btn:hover {
            background: #111b2d;
            color: #e2e8f0;
        }
    </style>
@endsection
