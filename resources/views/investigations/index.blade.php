@extends('layouts.admin')

@section('title', 'Shipment Investigations')

@section('content')
<div class="investigation-wrapper">
    <!-- Premium Header -->
    <div class="investigation-top-bar animate__animated animate__fadeInDown">
        <div class="row align-items-center w-100">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon-box">
                        <i class="bi bi-search"></i>
                    </div>
                    <div>
                        <h1 class="header-title">Investigations</h1>
                        <p class="header-subtitle">{{ $investigations->total() }} stalled shipments identified</p>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="stats-pills d-flex gap-4">
                    <div class="stat-pill">
                        <span class="stat-value text-warning">{{ $statusCounts['Pending'] }}</span>
                        <span class="stat-label">Pending</span>
                    </div>
                    <div class="stat-pill border-start ps-4">
                        <span class="stat-value text-primary">{{ $statusCounts['In Progress'] }}</span>
                        <span class="stat-label">Active</span>
                    </div>
                    <div class="stat-pill border-start ps-4">
                        <span class="stat-value text-success">{{ $statusCounts['Resolved'] }}</span>
                        <span class="stat-label">Resolved</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-end">
                <form action="{{ route('investigations.index') }}" method="GET" id="statusFilterForm">
                    <div class="filter-dropdown">
                        <select name="status" class="premium-select" onchange="this.form.submit()">
                            <option value="">All Investigations</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Carrier Contacted" {{ request('status') == 'Carrier Contacted' ? 'selected' : '' }}>Carrier Contacted</option>
                            <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                        <i class="bi bi-chevron-down filter-icon"></i>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="investigation-main-content">
        <!-- Sidebar List -->
        <div class="investigation-sidebar animate__animated animate__fadeInLeft">
            <div class="sidebar-scroll">
                @forelse($investigations as $investigation)
                    @php
                        $statusSlug = str_replace(' ', '-', strtolower($investigation->investigation_status));
                    @endphp
                    <div class="inv-card {{ $loop->first ? 'active' : '' }}" 
                         onclick="loadInvestigation({{ $investigation->id }}, this)"
                         id="item-{{ $investigation->id }}">
                        <div class="inv-card-header">
                            <span class="inv-id">#{{ $investigation->order->id }}</span>
                            <span class="inv-time">{{ $investigation->created_at->diffForHumans(null, true) }}</span>
                        </div>
                        <div class="inv-card-body">
                            <h6 class="inv-client">{{ Str::limit($investigation->order->client_name, 25) }}</h6>
                            <div class="inv-tracking">{{ $investigation->order->tracking_number }}</div>
                        </div>
                        <div class="inv-card-footer">
                            <span class="inv-status-tag tag-{{ $statusSlug }}">
                                <span class="tag-dot"></span> {{ $investigation->investigation_status }}
                            </span>
                            @if($investigation->investigation_notes && count($investigation->investigation_notes) > 0)
                                <div class="inv-has-notes" title="Has updates">
                                    <i class="bi bi-chat-text-fill"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-list-state">
                        <div class="empty-icon">
                            <i class="bi bi-inbox"></i>
                        </div>
                        <h5>Inbox Empty</h5>
                        <p>No investigations match your filters.</p>
                    </div>
                @endforelse
            </div>
            
            @if($investigations->hasPages())
                <div class="sidebar-pagination">
                    {{ $investigations->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
                </div>
            @endif
        </div>

        <!-- Detail Workspace -->
        <div class="investigation-workspace bg-white animate__animated animate__fadeIn" id="detail-panel">
            @if($investigations->count() > 0)
                @include('investigations.details', ['investigation' => $investigations->first()])
            @else
                <div class="workspace-placeholder">
                    <div class="placeholder-content">
                        <div class="pulse-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3>Select Investigation</h3>
                        <p>Choose an item from the sidebar to view detailed timeline and take action.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* Premium Root Variables - Synced with Carbon CRM */
    :root {
        --inv-sidebar-width: 360px;
        --inv-primary: #6366f1; /* Synced Indigo */
        --inv-primary-light: #e0e7ff;
        --inv-bg: #f8fafc;
        --inv-border: #e2e8f0;
        --inv-text-main: #1e293b;
        --inv-text-muted: #64748b;
        --inv-card-bg: #ffffff;
        --inv-active-bg: #f8fafc;
        --inv-tag-pending: #fffbeb;
        --inv-tag-pending-text: #b45309;
        --inv-tag-active: #eef2ff;
        --inv-tag-active-text: #4338ca;
        --inv-tag-resolved: #f0fdf4;
        --inv-tag-resolved-text: #15803d;
    }

    .investigation-wrapper {
        height: calc(100vh - 72px); /* Navbar is 72px */
        margin: -2rem; /* Negate #mainContent padding */
        display: flex;
        flex-direction: column;
        background: white;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        overflow: hidden;
    }

    @media (max-width: 992px) {
        .investigation-wrapper {
            height: calc(100vh - 72px);
            margin: -2rem;
        }
        .investigation-main-content {
            flex-direction: column;
        }
        .investigation-sidebar {
            width: 100%;
            height: 40%;
            border-right: none;
            border-bottom: 1px solid var(--inv-border);
        }
    }

    @media (max-width: 768px) {
        .investigation-wrapper {
            height: calc(100vh - 72px - 70px);
            margin: -2rem;
        }
        .investigation-top-bar {
            padding: 1rem;
        }
        .investigation-sidebar {
            height: 35%;
        }
    }

    /* Remove Scrollbars */
    .sidebar-scroll, .investigation-workspace, .scroll-y-custom, .timeline-container, .mini-history-list {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none;  /* IE and Edge */
    }
    .sidebar-scroll::-webkit-scrollbar, 
    .investigation-workspace::-webkit-scrollbar,
    .scroll-y-custom::-webkit-scrollbar,
    .timeline-container::-webkit-scrollbar,
    .mini-history-list::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    /* Top Bar Styling */
    .investigation-top-bar {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        padding: 1.25rem 2.5rem;
        border-bottom: 1px solid var(--inv-border);
        z-index: 100;
        flex-shrink: 0;
        position: sticky;
        top: 0;
    }

    .header-icon-box {
        width: 42px;
        height: 42px;
        background: var(--inv-primary-light);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--inv-primary);
    }

    .header-title {
        font-size: 1.15rem;
        font-weight: 800;
        margin: 0;
        color: var(--inv-text-main);
        letter-spacing: -0.5px;
    }

    .header-subtitle {
        font-size: 0.8rem;
        color: var(--inv-text-muted);
        margin: 0;
    }

    .stat-pill {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-value {
        font-size: 1rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 2px;
    }

    .stat-label {
        font-size: 0.7rem;
        color: var(--inv-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 600;
    }

    /* Filter Dropdown */
    .premium-select {
        width: 100%;
        padding: 0.5rem 1rem;
        padding-right: 2.5rem;
        border: 1px solid var(--inv-border);
        border-radius: 10px;
        background: #f8fafc;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--inv-text-main);
        appearance: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .premium-select:hover {
        background: #fff;
        border-color: var(--inv-primary);
    }

    /* Main Content Layout */
    .investigation-main-content {
        flex: 1;
        display: flex;
        overflow: hidden;
        background: #f8fafc;
    }

    .investigation-sidebar {
        width: var(--inv-sidebar-width);
        background: white;
        border-right: 1px solid var(--inv-border);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }

    .sidebar-scroll {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem;
    }

    /* Card Styling */
    .inv-card {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        background: white;
    }

    .inv-card:hover {
        background: var(--inv-bg);
        border-color: var(--inv-border);
    }

    .inv-card.active {
        background: white;
        border-color: var(--inv-primary);
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.1);
    }

    .inv-card.active::after {
        content: '';
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background: var(--inv-primary);
        border-radius: 50%;
    }

    .inv-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.4rem;
    }

    .inv-id {
        font-weight: 800;
        font-size: 0.9rem;
        color: var(--inv-text-main);
    }

    .inv-time {
        font-size: 0.7rem;
        font-weight: 500;
        color: var(--inv-text-muted);
    }

    .inv-client {
        font-size: 0.85rem;
        font-weight: 600;
        margin: 0 0 2px 0;
        color: var(--inv-text-main);
    }

    .inv-tracking {
        font-size: 0.75rem;
        color: var(--inv-primary);
        font-family: 'JetBrains Mono', monospace;
        opacity: 0.8;
    }

    .inv-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.75rem;
    }

    /* Status Tags */
    .inv-status-tag {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tag-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
    }

    .tag-pending { background: var(--inv-tag-pending); color: var(--inv-tag-pending-text); }
    .tag-pending .tag-dot { background: var(--inv-tag-pending-text); }

    .tag-in-progress, .tag-carrier-contacted { background: var(--inv-tag-active); color: var(--inv-tag-active-text); }
    .tag-in-progress .tag-dot, .tag-carrier-contacted .tag-dot { background: var(--inv-tag-active-text); }

    .tag-resolved, .tag-delivered { background: var(--inv-tag-resolved); color: var(--inv-tag-resolved-text); }
    .tag-resolved .tag-dot, .tag-delivered .tag-dot { background: var(--inv-tag-resolved-text); }

    /* Workspace */
    .investigation-workspace {
        flex: 1;
        overflow: hidden;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
    }

    .workspace-placeholder {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        background: white;
    }

    .pulse-icon {
        width: 64px;
        height: 64px;
        background: var(--inv-primary-light);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: var(--inv-primary);
        margin: 0 auto 1.25rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
        70% { transform: scale(1); box-shadow: 0 0 0 12px rgba(99, 102, 241, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
    }

    .placeholder-content h3 {
        font-weight: 800;
        color: var(--inv-text-main);
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
    }

    .placeholder-content p {
        color: var(--inv-text-muted);
        font-size: 0.9rem;
    }

    .sidebar-pagination {
        padding: 0.75rem;
        border-top: 1px solid var(--inv-border);
        background: white;
    }
</style>

<script>
    function loadInvestigation(id, element) {
        document.querySelectorAll('.inv-card').forEach(el => el.classList.remove('active'));
        element.classList.add('active');

        const workspace = document.getElementById('detail-panel');
        workspace.innerHTML = `
            <div class="h-100 d-flex align-items-center justify-content-center flex-column">
                <div class="spinner-grow text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <span class="text-muted fw-medium">Retrieving timeline...</span>
            </div>
        `;

        fetch(`/admin/investigations/${id}/fragment`)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.text();
            })
            .then(html => {
                workspace.innerHTML = html;
            })
            .catch(err => {
                workspace.innerHTML = `
                    <div class="h-100 d-flex align-items-center justify-content-center p-5">
                        <div class="alert alert-soft-danger text-center">
                            <i class="bi bi-exclamation-triangle h1 d-block"></i>
                            <h5>Failed to Load Details</h5>
                            <p class="mb-0">Please check your connection or try again.</p>
                        </div>
                    </div>
                `;
            });
    }
</script>
@endsection

