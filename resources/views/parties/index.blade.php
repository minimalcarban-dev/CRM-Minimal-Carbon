@extends('layouts.admin')
@section('title', 'Parties')
@section('content')
    <div class="parties-management-wrapper">
        <!-- Header Section -->
        <div class="parties-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-trail">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-item">
                            <i class="bi bi-house"></i>
                            Dashboard
                        </a>
                        <i class="bi bi-chevron-right"></i>
                        <span class="breadcrumb-active">Parties</span>
                    </div>
                    <h1 class="page-heading">
                        <i class="bi bi-people-fill"></i>
                        Parties Management
                    </h1>
                    <p class="page-description">Manage your business parties, customers, and suppliers</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('parties.create') }}" class="create-btn">
                        <i class="bi bi-plus-lg"></i>
                        <span>Add New Party</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-overview">
            <div class="stat-box stat-primary">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Total Parties</div>
                    <div class="stat-value">{{ $parties->total() }}</div>
                </div>
            </div>
            <div class="stat-box stat-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Active Today</div>
                    <div class="stat-value">{{ $parties->count() }}</div>
                </div>
            </div>
            <div class="stat-box stat-info">
                <div class="stat-icon">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Locations</div>
                    <div class="stat-value">{{ $parties->pluck('state')->unique()->count() }}</div>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="success-alert">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <form method="GET" action="{{ route('parties.index') }}" class="search-form">
                <div class="search-group">
                    <i class="bi bi-search search-icon"></i>
                    <input 
                        type="text" 
                        name="search" 
                        class="search-field" 
                        placeholder="Search by name, GST, phone, email..." 
                        value="{{ request('search') }}"
                    >
                </div>
                <button type="submit" class="search-btn">
                    <i class="bi bi-filter"></i>
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('parties.index') }}" class="reset-btn">
                        <i class="bi bi-x-circle"></i>
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Parties Table Card -->
        <div class="table-card">
            @if($parties->count())
                <div class="table-wrapper">
                    <table class="parties-table">
                        <thead>
                            <tr>
                                <th class="th-id">
                                    <i class="bi bi-hash"></i>
                                    ID
                                </th>
                                <th class="th-name">
                                    <i class="bi bi-person"></i>
                                    Party Name
                                </th>
                                <th>
                                    <i class="bi bi-receipt"></i>
                                    GST Number
                                </th>
                                <th>
                                    <i class="bi bi-telephone"></i>
                                    Phone
                                </th>
                                <th>
                                    <i class="bi bi-envelope"></i>
                                    Email
                                </th>
                                <th>
                                    <i class="bi bi-geo"></i>
                                    State
                                </th>
                                <th class="th-actions">
                                    <i class="bi bi-gear"></i>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parties as $p)
                                <tr class="table-row">
                                    <td class="td-id">
                                        <span class="id-badge">#{{ $p->id }}</span>
                                    </td>
                                    <td class="td-name">
                                        <a href="{{ route('parties.show', $p->id) }}" class="party-link">
                                            <div class="party-avatar">
                                                {{ strtoupper(substr($p->name, 0, 2)) }}
                                            </div>
                                            <span class="party-name">{{ $p->name }}</span>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="gst-number">{{ $p->gst_no ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="phone-number">{{ $p->phone ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="email-text">{{ $p->email ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="state-badge">{{ $p->state ?: '—' }}</span>
                                    </td>
                                    <td class="td-actions">
                                        <div class="action-group">
                                            <a href="{{ route('parties.show', $p->id) }}" class="action-btn view-btn" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('parties.edit', $p->id) }}" class="action-btn edit-btn" title="Edit Party">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('parties.destroy', $p->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this party?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn delete-btn" title="Delete Party">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Showing {{ $parties->firstItem() }} to {{ $parties->lastItem() }} of {{ $parties->total() }} parties
                    </div>
                    <div class="pagination-links">
                        {{ $parties->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h3 class="empty-title">No Parties Found</h3>
                    <p class="empty-text">
                        @if(request('search'))
                            No parties match your search criteria. Try a different search term.
                        @else
                            You haven't added any parties yet. Get started by creating your first party.
                        @endif
                    </p>
                    @if(request('search'))
                        <a href="{{ route('parties.index') }}" class="empty-action">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Clear Search
                        </a>
                    @else
                        <a href="{{ route('parties.create') }}" class="empty-action">
                            <i class="bi bi-plus-circle"></i>
                            Create First Party
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --text-dark: #0f172a;
            --text-gray: #64748b;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }

        .parties-management-wrapper {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--bg-light);
            min-height: 100vh;
        }

        /* Header */
        .parties-header {
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

        .create-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary-color);
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .create-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
            color: white;
        }

        /* Stats Overview */
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
            border-color: rgba(59, 130, 246, 0.2);
        }

        .stat-success {
            border-color: rgba(16, 185, 129, 0.2);
        }

        .stat-info {
            border-color: rgba(6, 182, 212, 0.2);
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
        }

        .stat-primary .stat-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
            color: var(--primary-color);
        }

        .stat-success .stat-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
            color: var(--success-color);
        }

        .stat-info .stat-icon {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(6, 182, 212, 0.05));
            color: var(--info-color);
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

        /* Success Alert */
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

        .success-alert i {
            font-size: 1.25rem;
        }

        /* Search Filter */
        .search-filter-section {
            background: var(--bg-white);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-group {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            pointer-events: none;
        }

        .search-field {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.95rem;
            transition: all 0.2s;
            background: var(--bg-light);
        }

        .search-field:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--bg-white);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .search-btn, .reset-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--bg-white);
            color: var(--text-dark);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .search-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .reset-btn:hover {
            border-color: var(--danger-color);
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.05);
        }

        /* Table Card */
        .table-card {
            background: var(--bg-white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .parties-table {
            width: 100%;
            border-collapse: collapse;
        }

        .parties-table thead {
            background: linear-gradient(135deg, var(--bg-light), var(--bg-white));
            border-bottom: 2px solid var(--border-color);
        }

        .parties-table th {
            padding: 1.25rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .parties-table th i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .th-id {
            width: 80px;
        }

        .th-actions {
            width: 200px;
            text-align: center;
        }

        .table-row {
            border-bottom: 1px solid var(--border-color);
            transition: all 0.2s;
        }

        .table-row:hover {
            background: var(--bg-light);
        }

        .parties-table td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
        }

        .id-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            color: var(--primary-color);
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .party-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-dark);
        }

        .party-avatar {
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

        .party-name {
            font-weight: 600;
            transition: color 0.2s;
        }

        .party-link:hover .party-name {
            color: var(--primary-color);
        }

        .gst-number, .phone-number, .email-text {
            color: var(--text-gray);
        }

        .state-badge {
            display: inline-block;
            padding: 0.375rem 0.875rem;
            background: var(--bg-light);
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .td-actions {
            text-align: center;
        }

        .action-group {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border-color);
            background: var(--bg-white);
            color: var(--text-gray);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .view-btn:hover {
            border-color: var(--info-color);
            color: var(--info-color);
            background: rgba(6, 182, 212, 0.05);
        }

        .edit-btn:hover {
            border-color: var(--warning-color);
            color: var(--warning-color);
            background: rgba(245, 158, 11, 0.05);
        }

        .delete-btn:hover {
            border-color: var(--danger-color);
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.05);
        }

        .delete-form {
            display: inline;
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination-info {
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 0.5rem 0;
        }

        .empty-text {
            color: var(--text-gray);
            margin: 0 0 2rem 0;
            line-height: 1.6;
        }

        .empty-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary-color);
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .empty-action:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .parties-management-wrapper {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
            }

            .create-btn {
                width: 100%;
                justify-content: center;
            }

            .stats-overview {
                grid-template-columns: 1fr;
            }

            .search-group {
                min-width: 100%;
            }

            .search-form {
                flex-direction: column;
            }

            .parties-table {
                min-width: 900px;
            }

            .pagination-wrapper {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
@endsection