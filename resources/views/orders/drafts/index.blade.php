@extends('layouts.admin')
@section('title', 'Order Drafts')
@section('content')

    <!-- Main Content Area -->
    <div class="drafts-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('orders.index') }}" class="breadcrumb-link">Orders</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Drafts</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-file-earmark-text"></i>
                        Order Drafts
                    </h1>
                    <p class="page-subtitle">Incomplete orders that need your attention</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('orders.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>New Order</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-file-earmark"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Drafts</div>
                    <div class="stat-value">{{ $totalDrafts }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-danger">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Error Drafts</div>
                    <div class="stat-value">{{ $errorDrafts }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Auto-Saved</div>
                    <div class="stat-value">{{ $autoSaveDrafts }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-warning">
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Expiring Soon</div>
                    <div class="stat-value">{{ $expiringSoon }}</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" action="{{ route('orders.drafts.index') }}" class="filter-form">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search by client name..."
                        value="{{ request('search') }}">
                </div>

                <select name="source" class="filter-select">
                    <option value="">All Sources</option>
                    <option value="auto_save" {{ request('source') == 'auto_save' ? 'selected' : '' }}>Auto-Saved</option>
                    <option value="error" {{ request('source') == 'error' ? 'selected' : '' }}>Error</option>
                    <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual</option>
                </select>

                <select name="order_type" class="filter-select">
                    <option value="">All Types</option>
                    <option value="ready_to_ship" {{ request('order_type') == 'ready_to_ship' ? 'selected' : '' }}>Ready to
                        Ship</option>
                    <option value="custom_diamond" {{ request('order_type') == 'custom_diamond' ? 'selected' : '' }}>Custom
                        Diamond</option>
                    <option value="custom_jewellery" {{ request('order_type') == 'custom_jewellery' ? 'selected' : '' }}>
                        Custom Jewellery</option>
                </select>

                <select name="days" class="filter-select">
                    <option value="">All Time</option>
                    <option value="7" {{ request('days') == '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ request('days') == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ request('days') == '90' ? 'selected' : '' }}>Last 90 Days</option>
                </select>

                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i>
                    <span>Filter</span>
                </button>

                @if(request()->hasAny(['search', 'source', 'order_type', 'days']))
                    <a href="{{ route('orders.drafts.index') }}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Drafts List -->
        <div class="drafts-list">
            @forelse($drafts as $draft)
                <div
                    class="draft-card {{ $draft->source === 'error' ? 'draft-error' : '' }} {{ $draft->isExpiringSoon() ? 'draft-expiring' : '' }}">
                    <div class="draft-header">
                        <div class="draft-id">
                            <span class="draft-badge {{ $draft->source }}">
                                @if($draft->source === 'error')
                                    <i class="bi bi-exclamation-triangle"></i> Error
                                @elseif($draft->source === 'auto_save')
                                    <i class="bi bi-cloud-arrow-up"></i> Auto-saved
                                @else
                                    <i class="bi bi-save"></i> Manual
                                @endif
                            </span>
                            <span class="draft-number">#D-{{ $draft->id }}</span>
                        </div>
                        <div class="draft-time">
                            <i class="bi bi-clock"></i>
                            {{ $draft->updated_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="draft-body">
                        <div class="draft-info">
                            <div class="draft-row">
                                <span class="draft-label">
                                    <i class="bi bi-tag"></i> Type:
                                </span>
                                <span class="order-type-badge badge-{{ $draft->order_type }}">
                                    {{ $draft->order_type_label }}
                                </span>
                            </div>
                            <div class="draft-row">
                                <span class="draft-label">
                                    <i class="bi bi-person"></i> Client:
                                </span>
                                <span>{{ $draft->client_name ?? 'Not set' }}</span>
                            </div>
                            <div class="draft-row">
                                <span class="draft-label">
                                    <i class="bi bi-person-badge"></i> Created by:
                                </span>
                                <span>{{ $draft->admin->name ?? 'Unknown' }}</span>
                            </div>
                        </div>

                        @if($draft->error_message)
                            <div class="draft-error-message">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ Str::limit($draft->error_message, 150) }}
                            </div>
                        @endif

                        <div class="draft-progress">
                            <div class="progress-label">
                                <span>Progress</span>
                                <span>{{ $draft->completion_percentage }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $draft->completion_percentage }}%"></div>
                            </div>
                        </div>

                        @if($draft->isExpiringSoon())
                            <div class="expiry-warning">
                                <i class="bi bi-hourglass-split"></i>
                                Expires {{ $draft->expires_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>

                    <div class="draft-actions">
                        <a href="{{ route('orders.drafts.resume', $draft->id) }}" class="btn-action btn-resume">
                            <i class="bi bi-play-circle"></i>
                            Resume
                        </a>
                        <a href="{{ route('orders.drafts.show', $draft->id) }}" class="btn-action btn-preview">
                            <i class="bi bi-eye"></i>
                            Preview
                        </a>
                        <form id="delete-draft-{{ $draft->id }}" action="{{ route('orders.drafts.destroy', $draft->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn-action btn-discard" onclick="confirmDeleteDraft({{ $draft->id }}, '{{ $draft->client_name ?? 'Unnamed Draft' }}')">
                                <i class="bi bi-trash"></i>
                                Discard
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <h3 class="empty-title">No Drafts Found</h3>
                    <p class="empty-description">
                        @if(request()->hasAny(['search', 'source', 'order_type', 'days']))
                            No drafts match your filters. Try adjusting your search.
                        @else
                            Great! You have no pending order drafts.
                        @endif
                    </p>
                    <a href="{{ route('orders.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        Create New Order
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($drafts->hasPages())
            <div class="pagination-container">
                {{ $drafts->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <!-- CSS -->
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

        .drafts-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .breadcrumb-link {
            color: var(--gray);
            text-decoration: none;
        }

        .breadcrumb-link:hover {
            color: var(--primary);
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
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            color: white;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card-primary .stat-icon {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .stat-card-danger .stat-icon {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .stat-card-info .stat-icon {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .stat-card-warning .stat-icon {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }

        /* Filters */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.875rem;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.875rem;
            min-width: 150px;
        }

        .btn-filter,
        .btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-filter {
            background: var(--primary);
            color: white;
            border: none;
        }

        .btn-reset {
            background: var(--light-gray);
            color: var(--gray);
            border: 2px solid var(--border);
        }

        /* Draft Cards */
        .drafts-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .draft-card {
            background: white;
            border-radius: 16px;
            border: 2px solid var(--border);
            overflow: hidden;
            transition: all 0.3s;
        }

        .draft-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .draft-card.draft-error {
            border-color: rgba(239, 68, 68, 0.3);
        }

        .draft-card.draft-expiring {
            border-color: rgba(245, 158, 11, 0.3);
        }

        .draft-header {
            padding: 1rem 1.5rem;
            background: var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border);
        }

        .draft-id {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .draft-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .draft-badge.error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .draft-badge.auto_save {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .draft-badge.manual {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .draft-number {
            font-weight: 700;
            color: var(--dark);
        }

        .draft-time {
            color: var(--gray);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .draft-body {
            padding: 1.5rem;
        }

        .draft-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .draft-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .draft-label {
            color: var(--gray);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .order-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-ready_to_ship {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .badge-custom_diamond {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge-custom_jewellery {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .draft-error-message {
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            color: var(--danger);
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .draft-progress {
            margin-bottom: 1rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }

        .progress-bar {
            height: 6px;
            background: var(--light-gray);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--info));
            border-radius: 3px;
        }

        .expiry-warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: var(--warning);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .draft-actions {
            padding: 1rem 1.5rem;
            background: var(--light-gray);
            display: flex;
            gap: 0.75rem;
            border-top: 2px solid var(--border);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-resume {
            background: var(--primary);
            color: white;
        }

        .btn-preview {
            background: white;
            color: var(--gray);
            border: 2px solid var(--border);
        }

        .btn-discard {
            background: white;
            color: var(--danger);
            border: 2px solid rgba(239, 68, 68, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--light-gray);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: var(--success);
        }

        .empty-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin: 0 0 0.5rem;
        }

        .empty-description {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        /* Pagination */
        .pagination-container {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }
    </style>

    <!-- JavaScript -->
    <script>
        async function confirmDeleteDraft(draftId, clientName) {
            const confirmed = await showConfirm(
                `Draft: ${clientName}`,
                'Discard this draft?',
                'Yes, Discard',
                'Cancel'
            );
            
            if (confirmed) {
                document.getElementById('delete-draft-' + draftId).submit();
            }
        }
    </script>
@endsection