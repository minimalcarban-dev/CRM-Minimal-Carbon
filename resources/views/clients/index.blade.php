@extends('layouts.admin')
@section('title', 'Clients')
@section('content')
    @php
        $currentSort = $sortColumn ?? 'orders_count';
        $currentDir = $sortDir ?? 'desc';
        
        function sortUrl($column, $currentSort, $currentDir) {
            $newDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $column, 'dir' => $newDir]);
        }
        
        function sortIcon($column, $currentSort, $currentDir) {
            if ($currentSort !== $column) return '<i class="bi bi-arrow-down-up text-muted opacity-50"></i>';
            return $currentDir === 'asc' 
                ? '<i class="bi bi-arrow-up"></i>' 
                : '<i class="bi bi-arrow-down"></i>';
        }
    @endphp

    <div class="clients-management-wrapper">
        <!-- Header Section -->
        <div class="clients-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-trail">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-item">
                            <i class="bi bi-house"></i>
                            Dashboard
                        </a>
                        <i class="bi bi-chevron-right"></i>
                        <span class="breadcrumb-active">Clients</span>
                    </div>
                    <h1 class="page-heading">
                        <i class="bi bi-people-fill"></i>
                        Client Dashboard
                    </h1>
                    <p class="page-description">View and manage all your clients from orders</p>
                </div>
                <div class="header-right">
                    @if(auth()->guard('admin')->user()->canAccessAny(['clients.export']))
                        <a href="{{ route('clients.export') }}" class="export-btn">
                            <i class="bi bi-download"></i>
                            <span>Export Excel</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-bar-card">
            <form method="GET" action="{{ route('clients.index') }}" class="search-form">
                <div class="search-input-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search clients by name, email, mobile..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="search-btn">Search</button>
                @if(request('search'))
                    <a href="{{ route('clients.index') }}" class="clear-btn">Clear</a>
                @endif
            </form>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="success-alert">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Clients Table Card -->
        <div class="table-card">
            <div class="table-wrapper">
                <table class="clients-table">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ sortUrl('name', $currentSort, $currentDir) }}" class="sort-link">
                                    <i class="bi bi-person"></i> Name {!! sortIcon('name', $currentSort, $currentDir) !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ sortUrl('email', $currentSort, $currentDir) }}" class="sort-link">
                                    <i class="bi bi-envelope"></i> Email {!! sortIcon('email', $currentSort, $currentDir) !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ sortUrl('mobile', $currentSort, $currentDir) }}" class="sort-link">
                                    <i class="bi bi-telephone"></i> Mobile {!! sortIcon('mobile', $currentSort, $currentDir) !!}
                                </a>
                            </th>
                            <th><i class="bi bi-geo-alt"></i> Address</th>
                            <th><i class="bi bi-receipt"></i> Tax ID</th>
                            <th>
                                <a href="{{ sortUrl('orders_count', $currentSort, $currentDir) }}" class="sort-link">
                                    <i class="bi bi-basket"></i> Orders {!! sortIcon('orders_count', $currentSort, $currentDir) !!}
                                </a>
                            </th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>
                                    <div class="client-name-cell">
                                        <div class="client-avatar">{{ strtoupper(substr($client->name ?? 'NA', 0, 2)) }}</div>
                                        <span>{{ $client->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>{{ $client->email ?? '-' }}</td>
                                <td>{{ $client->mobile ?? '-' }}</td>
                                <td>{{ $client->address ? Str::limit($client->address, 30) : '-' }}</td>
                                <td>{{ $client->tax_id ?? '-' }}</td>
                                <td><span class="orders-badge">{{ $client->orders_count }}</span></td>
                                <td>
                                    <a href="{{ route('clients.show', $client->id) }}" class="view-btn">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-content">
                                        <i class="bi bi-inbox"></i>
                                        <p>No clients found. Clients will appear here when orders are created.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($clients->hasPages())
                <div class="pagination-wrapper">
                    {{ $clients->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: rgba(99, 102, 241, 0.1);
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }

        .clients-management-wrapper {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--bg-light);
            min-height: 100vh;
        }

        .clients-header {
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
            transition: color 0.2s;
        }

        .breadcrumb-item:hover {
            color: var(--primary-color);
        }

        .breadcrumb-active {
            color: var(--text-dark);
            font-weight: 600;
        }

        .page-heading {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-heading i {
            color: var(--primary-color);
        }

        .page-description {
            color: var(--text-gray);
            margin: 0;
        }

        .export-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--success-color);
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .export-btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: var(--bg-white);
            border-radius: var(--radius);
            padding: 1.75rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-primary {
            border-color: rgba(99, 102, 241, 0.2);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .search-bar-card {
            background: var(--bg-white);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input-wrapper {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        .search-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .search-btn {
            padding: 0.875rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .search-btn:hover {
            background: var(--primary-dark);
        }

        .clear-btn {
            padding: 0.875rem 1.5rem;
            background: var(--bg-light);
            color: var(--text-gray);
            border: 2px solid var(--border-color);
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .clear-btn:hover {
            border-color: var(--danger-color);
            color: var(--danger-color);
        }

        .success-alert {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            border: 2px solid rgba(16, 185, 129, 0.3);
            color: #065f46;
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
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

        .clients-table {
            width: 100%;
            border-collapse: collapse;
        }

        .clients-table thead {
            background: linear-gradient(135deg, var(--bg-light), var(--bg-white));
            border-bottom: 2px solid var(--border-color);
        }

        .clients-table th {
            padding: 1.25rem 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .clients-table th i:first-child {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .sort-link {
            color: var(--text-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s;
        }

        .sort-link:hover {
            color: var(--primary-color);
        }

        .sort-link i.bi-arrow-up,
        .sort-link i.bi-arrow-down {
            color: var(--primary-color);
            font-size: 0.75rem;
        }

        .clients-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .clients-table tbody tr:hover {
            background: var(--bg-light);
        }

        .client-name-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .client-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .orders-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary-color);
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-white);
            color: var(--text-gray);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .view-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(99, 102, 241, 0.05);
        }

        .empty-state {
            text-align: center;
            padding: 3rem !important;
        }

        .empty-content {
            color: var(--text-gray);
        }

        .empty-content i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .pagination-wrapper {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .pagination-wrapper .pagination {
            margin: 0;
            justify-content: center;
            gap: 0.5rem;
        }

        .pagination-wrapper .page-item .page-link {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border-color);
            color: var(--text-gray);
            border-radius: 10px;
            padding: 0;
            font-weight: 600;
            font-size: 0.875rem;
            background: var(--bg-white);
            transition: all 0.2s;
        }

        .pagination-wrapper .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .pagination-wrapper .page-item .page-link:hover {
            background: var(--bg-light);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .pagination-wrapper .page-item.active .page-link:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: white;
        }

        .pagination-wrapper .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .clients-management-wrapper {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
            }

            .export-btn {
                width: 100%;
                justify-content: center;
            }

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input-wrapper {
                min-width: 100%;
            }
        }
    </style>
@endsection