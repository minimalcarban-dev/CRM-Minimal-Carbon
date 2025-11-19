@extends('layouts.admin')

@section('title', 'Ring Sizes')

@section('content')
    <div class="page-container">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-circle"></i>
                    Ring Sizes
                </h1>
                <p class="page-subtitle">Manage all ring size options available in your store</p>
            </div>
            <div class="header-actions">
                @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.create'))
                    <a href="{{ route('ring_sizes.create') }}" class="btn-primary-action">
                        <i class="bi bi-plus-lg"></i>
                        Create New Size
                    </a>
                @endif
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-card">
            <form method="GET" class="search-form">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="search-input" 
                           placeholder="Search by name..." 
                           value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn-search">
                    <i class="bi bi-search"></i>
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('ring_sizes.index') }}" class="btn-clear">
                        <i class="bi bi-x-lg"></i>
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="th-id">#</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="table-row">
                                <td class="td-id">
                                    <span class="id-badge">{{ $item->id }}</span>
                                </td>
                                <td>
                                    <div class="item-name">{{ $item->name }}</div>
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="status-badge status-active">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Active
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="bi bi-x-circle-fill"></i>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="date-text">
                                        <i class="bi bi-calendar3"></i>
                                        {{ $item->created_at?->format('M d, Y') ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.view'))
                                            <a href="{{ route('ring_sizes.show', $item->id) }}" 
                                               class="btn-action btn-action-info" 
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                        @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.edit'))
                                            <a href="{{ route('ring_sizes.edit', $item->id) }}" 
                                               class="btn-action btn-action-primary" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.delete'))
                                            <form action="{{ route('ring_sizes.destroy', $item->id) }}" 
                                                  method="POST" 
                                                  style="display:inline-block;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this ring size?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn-action btn-action-danger" 
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <div class="empty-icon">
                                        <i class="bi bi-inbox"></i>
                                    </div>
                                    <h4>No Ring Sizes Found</h4>
                                    <p>
                                        @if(request('search'))
                                            No results found for "{{ request('search') }}"
                                        @else
                                            Get started by creating your first ring size
                                        @endif
                                    </p>
                                    @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.create') && !request('search'))
                                        <a href="{{ route('ring_sizes.create') }}" class="btn-primary-action mt-3">
                                            <i class="bi bi-plus-lg"></i>
                                            Create Ring Size
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($items->hasPages())
                <div class="pagination-wrapper">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
        }

        .page-container {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary);
        }

        .page-subtitle {
            color: var(--gray);
            margin: 0;
            font-size: 1rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .btn-primary-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary-action:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
            color: white;
        }

        /* Search Card */
        .search-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 2px solid var(--border);
        }

        .search-form {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input-wrapper {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-search {
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-search:hover {
            background: var(--primary-dark);
        }

        .btn-clear {
            padding: 0.75rem 1.5rem;
            background: var(--light-gray);
            color: var(--gray);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-clear:hover {
            background: var(--border);
            color: var(--dark);
        }

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 2px solid var(--border);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }

        .modern-table thead {
            background: var(--light-gray);
        }

        .modern-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
        }

        .th-id {
            width: 80px;
        }

        .th-actions {
            width: 180px;
            text-align: center;
        }

        .modern-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }

        .modern-table tbody tr:hover {
            background: var(--light-gray);
        }

        .modern-table td {
            padding: 1.25rem 1.5rem;
            color: var(--dark);
        }

        .td-id {
            font-weight: 600;
        }

        .id-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .item-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-inactive {
            background: rgba(100, 116, 139, 0.1);
            color: var(--gray);
        }

        .date-text {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
            font-size: 0.875rem;
        }

        .date-text i {
            font-size: 1rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 2px solid var(--border);
            background: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-action-info {
            color: var(--info);
        }

        .btn-action-info:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--info);
            color: var(--info);
        }

        .btn-action-primary {
            color: var(--primary);
        }

        .btn-action-primary:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-action-danger {
            color: var(--danger);
        }

        .btn-action-danger:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: var(--danger);
            color: var(--danger);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--gray);
            opacity: 0.5;
            margin-bottom: 1rem;
        }

        .empty-state h4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray);
            margin-bottom: 0;
        }

        /* Pagination */
        .pagination-wrapper {
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
            }

            .search-form {
                flex-direction: column;
            }

            .search-input-wrapper {
                width: 100%;
            }

            .btn-search,
            .btn-clear {
                width: 100%;
                justify-content: center;
            }

            .modern-table {
                font-size: 0.875rem;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.75rem 1rem;
            }

            .action-buttons {
                flex-wrap: wrap;
            }
        }
    </style>
@endsection