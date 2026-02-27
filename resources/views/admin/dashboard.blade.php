@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

    @php
        // Safe defaults — used when route still points to Closure (no controller yet)
        $alerts = $alerts ?? [];
        $hasRange = $hasRange ?? false;
        $dateFrom = $dateFrom ?? null;
        $dateTo = $dateTo ?? null;
        $todayOrderCount = $todayOrderCount ?? 0;
        $todayRevenue = $todayRevenue ?? 0;
        $monthRevenue = $monthRevenue ?? 0;
        $diamondsInStock = $diamondsInStock ?? 0;
        $diamondsSoldThisMonth = $diamondsSoldThisMonth ?? 0;
        $activeOrders = $activeOrders ?? 0;
        $overdueOrders = $overdueOrders ?? 0;
        $myDraftCount = $myDraftCount ?? 0;
        $leadStats = $leadStats ?? ['newLeads' => 0, 'slaBreached' => 0];
        $overduePackages = $overduePackages ?? 0;
        $invoiceStats = $invoiceStats ?? null;
        $totalClients = $totalClients ?? 0;
        $recentActivity = $recentActivity ?? collect();
    @endphp
    <div class="dash-wrap">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
        <div class="dash-header">
            <div>
                <h1 class="dash-title"><i class="bi bi-speedometer2"></i> Dashboard</h1>
                <p class="dash-sub">Welcome back, {{ auth()->guard('admin')->user()->name ?? 'Admin' }}! Here's what's
                    happening today.</p>
            </div>
            <div class="dash-header-meta">

                {{-- Date range filter form --}}
                <form id="dashDateForm" method="GET" action="{{ route('admin.dashboard') }}" class="dash-date-form">
                    <input type="hidden" id="dashDateFrom" name="date_from" value="{{ $dateFrom ?? '' }}">
                    <div id="dashDateRange" class="dash-date-picker {{ $hasRange ? 'active' : '' }}" tabindex="0"
                        role="button" aria-haspopup="dialog" aria-label="Select date range">
                        <i class="bi bi-calendar-range"></i>
                        <span id="dashDateLabel">
                            @if($hasRange)
                                {{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }} —
                                {{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }}
                            @else
                                Filter by date range
                            @endif
                        </span>
                        <i class="bi bi-chevron-down" style="margin-left:auto;font-size:0.75rem;opacity:0.6"></i>
                    </div> <i class="bi bi-chevron-down" style="margin-left:auto;font-size:0.75rem;opacity:0.6"></i>
            </div>

            @if($hasRange)
                <a href="{{ route('admin.dashboard') }}" class="dash-clear-filter" title="Clear filter">
                    <i class="bi bi-x-circle-fill"></i> Clear
                </a>
            @endif
            </form>
        </div>
    </div>

    {{-- ── ALERT BANNERS ─────────────────────────────────────────── --}}
    @if(count($alerts) > 0)
        <div class="alert-stack">
            @foreach($alerts as $alert)
                <div class="alert-banner alert-{{ $alert['type'] }}">
                    <i class="bi {{ $alert['icon'] }} alert-icon"></i>
                    <span class="alert-msg">{{ $alert['message'] }}</span>
                    <a href="{{ $alert['link'] }}" class="alert-cta">{{ $alert['label'] }} <i class="bi bi-arrow-right"></i></a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── STAT CARDS ────────────────────────────────────────────── --}}
    <div class="stats-grid">

        {{-- Today's Orders --}}
        @if(auth()->guard('admin')->user()->canAccessAny(['orders.view']))
            <a href="{{ route('orders.index') }}" class="stat-card stat-indigo">
                <div class="stat-icon-wrap">
                    <i class="bi bi-basket"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-label">{{ $hasRange ? 'Orders in Range' : "Today's Orders" }}</div>
                    <div class="stat-val">{{ number_format($todayOrderCount) }}</div>
                    @if($overdueOrders > 0)
                        <div class="stat-badge badge-danger">{{ $overdueOrders }} overdue</div>
                    @else
                        <div class="stat-badge badge-ok">All on track</div>
                    @endif
                </div>
                <i class="bi bi-arrow-right stat-arrow"></i>
            </a>
        @endif

        {{-- Today's Revenue --}}
        @if(auth()->guard('admin')->user()->canAccessAny(['sales.view_all']))
            <div class="stat-card stat-green">
                <div class="stat-icon-wrap">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-label">{{ $hasRange ? 'Revenue in Range' : "Today's Revenue" }}</div>
                    <div class="stat-val">{{ $todayRevenue > 0 ? '$' . number_format($todayRevenue, 0) : '—' }}</div>
                    @if(!$hasRange)
                        <div class="stat-badge badge-muted">Month: ${{ number_format($monthRevenue, 0) }}</div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Diamonds in Stock --}}
        @if(auth()->guard('admin')->user()->canAccessAny(['diamonds.view']))
            <a href="{{ route('diamond.index') }}" class="stat-card stat-purple">
                <div class="stat-icon-wrap">
                    <i class="bi bi-gem"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Diamonds In Stock</div>
                    <div class="stat-val">{{ number_format($diamondsInStock) }}</div>
                    <div class="stat-badge badge-muted">{{ $diamondsSoldThisMonth }} sold
                        {{ $hasRange ? 'in range' : 'this month' }}
                    </div>
                </div>
                <i class="bi bi-arrow-right stat-arrow"></i>
            </a>
        @endif

        {{-- Active Leads / New Leads --}}
        @if(auth()->guard('admin')->user()->canAccessAny(['leads.view']))
            <a href="{{ route('leads.index') }}" class="stat-card stat-amber">
                <div class="stat-icon-wrap">
                    <i class="bi bi-inbox-fill"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-label">New Leads</div>
                    <div class="stat-val">{{ number_format($leadStats['newLeads']) }}</div>
                    @if($leadStats['slaBreached'] > 0)
                        <div class="stat-badge badge-danger">{{ $leadStats['slaBreached'] }} SLA breached</div>
                    @else
                        <div class="stat-badge badge-ok">SLAs met</div>
                    @endif
                </div>
                <i class="bi bi-arrow-right stat-arrow"></i>
            </a>
        @endif

        {{-- Invoices This Month --}}
        @if(auth()->guard('admin')->user()->canAccessAny(['invoices.view']))
            <a href="{{ route('invoices.index') }}" class="stat-card stat-blue">
                <div class="stat-icon-wrap">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-label">{{ $hasRange ? 'Invoices in Range' : 'Invoices This Month' }}</div>
                    <div class="stat-val">{{ number_format($invoiceStats?->count ?? 0) }}</div>
                    <div class="stat-badge badge-muted">Total: ${{ number_format($invoiceStats?->total ?? 0, 0) }}</div>
                </div>
                <i class="bi bi-arrow-right stat-arrow"></i>
            </a>
        @endif

        {{-- Total Clients --}}
        @if(auth()->guard('admin')->user()->canAccessAny(['clients.view']))
            <a href="{{ route('clients.index') }}" class="stat-card stat-teal">
                <div class="stat-icon-wrap">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Total Clients</div>
                    <div class="stat-val">{{ number_format($totalClients) }}</div>
                    <div class="stat-badge badge-muted">Shoppers database</div>
                </div>
                <i class="bi bi-arrow-right stat-arrow"></i>
            </a>
        @endif

    </div>{{-- /stats-grid --}}

    {{-- ── MAIN CONTENT ROW ──────────────────────────────────────── --}}
    <div class="dash-main-row">

        {{-- LEFT: Welcome card + Quick Access --}}
        <div class="dash-left">

            {{-- Welcome Card --}}
            <div class="welcome-card">
                <div class="welcome-top">
                    <div class="welcome-icon-wrap"><i class="bi bi-house-fill"></i></div>
                    <div>
                        <div class="welcome-title">Welcome to {{ config('app.name') }}</div>
                        <div class="welcome-sub">You can only access the modules assigned to you.</div>
                    </div>
                </div>

                @if(session('error'))
                    <div class="welcome-error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><strong>Access Denied:</strong> {{ session('error') }}</span>
                    </div>
                @endif

                <div class="welcome-actions">
                    @if(auth()->guard('admin')->user()->canAccessAny(['admins.view', 'admins.create']))
                        <a href="{{ route('admins.index') }}" class="welcome-btn welcome-btn-solid">
                            <i class="bi bi-people"></i> Manage Admins
                        </a>
                    @endif
                    @if(auth()->guard('admin')->user()->canAccessAny(['permissions.view', 'permissions.create']))
                        <a href="{{ route('permissions.index') }}" class="welcome-btn welcome-btn-outline">
                            <i class="bi bi-shield-lock"></i> View Permissions
                        </a>
                    @endif
                    @if(auth()->guard('admin')->user()->canAccessAny(['sales.view_all']))
                        <a href="{{ route('companies.all-sales-dashboard') }}" class="welcome-btn welcome-btn-outline">
                            <i class="bi bi-graph-up-arrow"></i> Sales Dashboard
                        </a>
                    @endif
                </div>

                {{-- My Drafts Banner --}}
                @if($myDraftCount > 0)
                    <a href="{{ route('orders.drafts.index') }}" class="draft-banner">
                        <div class="draft-left">
                            <i class="bi bi-file-earmark-text-fill"></i>
                            <div>
                                <div class="draft-title">{{ $myDraftCount }} Draft Order{{ $myDraftCount > 1 ? 's' : '' }}
                                    Saved</div>
                                <div class="draft-sub">Resume where you left off</div>
                            </div>
                        </div>
                        <span class="draft-cta">Resume <i class="bi bi-arrow-right"></i></span>
                    </a>
                @endif
            </div>

            {{-- Quick Access --}}
            <div class="section-head">
                <h3 class="section-title"><i class="bi bi-lightning-fill"></i> Quick Access</h3>
                <p class="section-sub">Frequently used actions</p>
            </div>

            <div class="quick-grid">

                @if(auth()->guard('admin')->user()->canAccessAny(['admins.create']))
                    <a href="{{ route('admins.create') }}" class="quick-card">
                        <div class="quick-icon qi-primary"><i class="bi bi-person-plus"></i></div>
                        <div class="quick-text">
                            <div class="quick-title">Add New Admin</div>
                            <div class="quick-desc">Create administrator account</div>
                        </div>
                        <i class="bi bi-arrow-right quick-arrow"></i>
                    </a>
                @endif

                @if(auth()->guard('admin')->user()->canAccessAny(['permissions.create']))
                    <a href="{{ route('permissions.create') }}" class="quick-card">
                        <div class="quick-icon qi-success"><i class="bi bi-shield-plus"></i></div>
                        <div class="quick-text">
                            <div class="quick-title">Create Permission</div>
                            <div class="quick-desc">Define system permissions</div>
                        </div>
                        <i class="bi bi-arrow-right quick-arrow"></i>
                    </a>
                @endif

                @if(auth()->guard('admin')->user()->canAccessAny(['orders.create']))
                    <a href="{{ route('orders.create') }}" class="quick-card">
                        <div class="quick-icon qi-warning"><i class="bi bi-basket"></i></div>
                        <div class="quick-text">
                            <div class="quick-title">New Order</div>
                            <div class="quick-desc">Create a new order</div>
                        </div>
                        <i class="bi bi-arrow-right quick-arrow"></i>
                    </a>
                @endif

                @if(auth()->guard('admin')->user()->canAccessAny(['diamonds.create']))
                    <a href="{{ route('diamond.create') }}" class="quick-card">
                        <div class="quick-icon qi-purple"><i class="bi bi-gem"></i></div>
                        <div class="quick-text">
                            <div class="quick-title">Add Diamond</div>
                            <div class="quick-desc">Add to stock list</div>
                        </div>
                        <i class="bi bi-arrow-right quick-arrow"></i>
                    </a>
                @endif

                @if(auth()->guard('admin')->user()->canAccessAny(['invoices.create']))
                    <a href="{{ route('invoices.create') }}" class="quick-card">
                        <div class="quick-icon qi-blue"><i class="bi bi-receipt"></i></div>
                        <div class="quick-text">
                            <div class="quick-title">Create Invoice</div>
                            <div class="quick-desc">Generate invoice</div>
                        </div>
                        <i class="bi bi-arrow-right quick-arrow"></i>
                    </a>
                @endif

                @if(auth()->guard('admin')->user()->canAccessAny(['chat.access']))
                    <a href="{{ route('chat.index') }}" class="quick-card">
                        <div class="quick-icon qi-info"><i class="bi bi-chat-dots"></i></div>
                        <div class="quick-text">
                            <div class="quick-title">Team Chat</div>
                            <div class="quick-desc">Messages &amp; channels</div>
                        </div>
                        <i class="bi bi-arrow-right quick-arrow"></i>
                    </a>
                @endif

            </div>{{-- /quick-grid --}}
        </div>{{-- /dash-left --}}

        {{-- RIGHT: Recent Activity --}}
        <div class="dash-right">

            {{-- Overdue Summary mini-cards --}}
            @if($overdueOrders > 0 || $overduePackages > 0 || $leadStats['slaBreached'] > 0)
                <div class="overdue-panel">
                    <div class="overdue-title"><i class="bi bi-exclamation-circle-fill"></i> Needs Attention</div>

                    @if($overdueOrders > 0)
                        <a href="{{ route('orders.index', ['overdue' => 1]) }}" class="overdue-item">
                            <i class="bi bi-basket-fill" style="color:#ef4444"></i>
                            <span>{{ $overdueOrders }} overdue order{{ $overdueOrders > 1 ? 's' : '' }}</span>
                            <i class="bi bi-arrow-right ms-auto"></i>
                        </a>
                    @endif

                    @if($overduePackages > 0)
                        <a href="{{ route('packages.index') }}" class="overdue-item">
                            <i class="bi bi-box-seam-fill" style="color:#f59e0b"></i>
                            <span>{{ $overduePackages }} overdue package{{ $overduePackages > 1 ? 's' : '' }}</span>
                            <i class="bi bi-arrow-right ms-auto"></i>
                        </a>
                    @endif

                    @if($leadStats['slaBreached'] > 0)
                        <a href="{{ route('leads.index') }}" class="overdue-item">
                            <i class="bi bi-clock-fill" style="color:#f59e0b"></i>
                            <span>{{ $leadStats['slaBreached'] }} SLA
                                breach{{ $leadStats['slaBreached'] > 1 ? 'es' : '' }}</span>
                            <i class="bi bi-arrow-right ms-auto"></i>
                        </a>
                    @endif
                </div>
            @endif

            <div class="activity-card">
                <div class="activity-head">
                    <div>
                        <div class="activity-title"><i class="bi bi-activity"></i> Recent Activity</div>
                        <div class="activity-sub">Your latest notifications</div>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="activity-view-all">View all</a>
                </div>

                @if($recentActivity->isEmpty())
                    <div class="activity-empty">
                        <i class="bi bi-bell-slash"></i>
                        <p>No recent activity</p>
                    </div>
                @else
                    <div class="activity-list">
                        @foreach($recentActivity as $item)
                            @php
                                $colorMap = [
                                    'red' => ['bg' => 'rgba(239,68,68,0.1)', 'color' => '#ef4444'],
                                    'amber' => ['bg' => 'rgba(245,158,11,0.1)', 'color' => '#f59e0b'],
                                    'blue' => ['bg' => 'rgba(59,130,246,0.1)', 'color' => '#3b82f6'],
                                    'green' => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#10b981'],
                                    'purple' => ['bg' => 'rgba(139,92,246,0.1)', 'color' => '#8b5cf6'],
                                    'gray' => ['bg' => 'rgba(100,116,139,0.1)', 'color' => '#64748b'],
                                ];
                                $c = $colorMap[$item->color] ?? $colorMap['gray'];
                            @endphp
                            <div class="activity-item {{ $item->read ? '' : 'activity-unread' }}">
                                <div class="activity-icon-wrap" style="background:{{ $c['bg'] }};color:{{ $c['color'] }}">
                                    <i class="bi {{ $item->icon }}"></i>
                                </div>
                                <div class="activity-body">
                                    <div class="activity-item-title">{{ Str::limit($item->title, 50) }}</div>
                                    @if($item->message)
                                        <div class="activity-item-msg">{{ Str::limit($item->message, 80) }}</div>
                                    @endif
                                </div>
                                <div class="activity-meta">
                                    <span class="activity-time">{{ $item->time }}</span>
                                    @if(!$item->read)
                                        <span class="activity-dot"></span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>{{-- /dash-right --}}
    </div>{{-- /dash-main-row --}}

    </div>{{-- /dash-wrap --}}

    <style>
        /* ═══════════════════════════════════════════
                       DASHBOARD — v3.0
                       Full dark mode support via [data-theme="dark"]
                    ═══════════════════════════════════════════ */
        .dash-wrap {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .dash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .dash-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--dark, #1e293b);
            margin: 0 0 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .dash-title i {
            color: var(--primary, #6366f1);
        }

        .dash-sub {
            color: var(--gray, #64748b);
            margin: 0;
            font-size: 0.95rem;
        }

        .dash-date-chip {
            background: var(--light-gray, #f1f5f9);
            border: 1.5px solid var(--border, #e2e8f0);
            color: var(--gray, #64748b);
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        [data-theme="dark"] .dash-title {
            color: #f1f5f9;
        }

        [data-theme="dark"] .dash-date-chip {
            background: rgba(255, 255, 255, 0.06);
            border-color: #334155;
            color: #94a3b8;
        }

        /* ── Alert Banners ── */
        .alert-stack {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
        }

        .alert-banner {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            flex-wrap: wrap;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.08);
            border: 1.5px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.08);
            border: 1.5px solid rgba(245, 158, 11, 0.3);
            color: #d97706;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.08);
            border: 1.5px solid rgba(59, 130, 246, 0.3);
            color: #2563eb;
        }

        .alert-icon {
            font-size: 1rem;
            flex-shrink: 0;
        }

        .alert-msg {
            flex: 1;
        }

        .alert-cta {
            font-weight: 700;
            font-size: 0.78rem;
            white-space: nowrap;
            text-decoration: none;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.18);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.15s;
            color: inherit;
            flex-shrink: 0;
        }

        .alert-cta:hover {
            background: rgba(255, 255, 255, 0.28);
            color: inherit;
            text-decoration: none;
        }

        [data-theme="dark"] .alert-danger {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.4);
            color: #f87171;
        }

        [data-theme="dark"] .alert-warning {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.4);
            color: #fbbf24;
        }

        [data-theme="dark"] .alert-info {
            background: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.4);
            color: #60a5fa;
        }

        /* ── Stats Grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            opacity: 0.06;
            transform: translate(20px, -20px);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: inherit;
        }

        /* Color variants */
        .stat-indigo {
            border-color: rgba(99, 102, 241, 0.15);
        }

        .stat-indigo::before,
        .stat-indigo .stat-icon-wrap {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .stat-indigo:hover {
            border-color: #6366f1;
        }

        .stat-green {
            border-color: rgba(16, 185, 129, 0.15);
        }

        .stat-green::before,
        .stat-green .stat-icon-wrap {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .stat-green:hover {
            border-color: #10b981;
        }

        .stat-purple {
            border-color: rgba(139, 92, 246, 0.15);
        }

        .stat-purple::before,
        .stat-purple .stat-icon-wrap {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .stat-purple:hover {
            border-color: #8b5cf6;
        }

        .stat-amber {
            border-color: rgba(245, 158, 11, 0.15);
        }

        .stat-amber::before,
        .stat-amber .stat-icon-wrap {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .stat-amber:hover {
            border-color: #f59e0b;
        }

        .stat-blue {
            border-color: rgba(59, 130, 246, 0.15);
        }

        .stat-blue::before,
        .stat-blue .stat-icon-wrap {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .stat-blue:hover {
            border-color: #3b82f6;
        }

        .stat-teal {
            border-color: rgba(20, 184, 166, 0.15);
        }

        .stat-teal::before,
        .stat-teal .stat-icon-wrap {
            background: rgba(20, 184, 166, 0.1);
            color: #14b8a6;
        }

        .stat-teal:hover {
            border-color: #14b8a6;
        }

        .stat-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-body {
            flex: 1;
            min-width: 0;
        }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray, #64748b);
            margin-bottom: 0.2rem;
        }

        .stat-val {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--dark, #1e293b);
            line-height: 1.1;
            margin-bottom: 0.35rem;
        }

        .stat-badge {
            display: inline-flex;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .badge-ok {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .badge-muted {
            background: var(--light-gray, #f1f5f9);
            color: var(--gray, #64748b);
        }

        .stat-arrow {
            color: var(--gray, #64748b);
            font-size: 1rem;
            transition: transform 0.2s;
        }

        .stat-card:hover .stat-arrow {
            transform: translateX(3px);
        }

        [data-theme="dark"] .stat-card {
            background: #1e293b;
        }

        [data-theme="dark"] .stat-val {
            color: #f1f5f9;
        }

        [data-theme="dark"] .badge-muted {
            background: rgba(255, 255, 255, 0.06);
            color: #94a3b8;
        }

        /* ── Main Row Layout ── */
        .dash-main-row {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1.5rem;
            align-items: start;
        }

        .dash-left,
        .dash-right {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* ── Welcome Card ── */
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 1.75rem;
            color: white;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        }

        .welcome-top {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .welcome-icon-wrap {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .welcome-title {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 0.35rem;
        }

        .welcome-sub {
            opacity: 0.85;
            font-size: 0.9rem;
            margin: 0;
            line-height: 1.5;
        }

        .welcome-error {
            background: rgba(255, 255, 255, 0.15);
            border-left: 3px solid rgba(255, 255, 255, 0.6);
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.875rem;
        }

        .welcome-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .welcome-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .welcome-btn:last-child {
            margin-bottom: 0;
        }

        .welcome-btn-solid {
            background: white;
            color: #6366f1;
        }

        .welcome-btn-solid:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 255, 255, 0.3);
            color: #6366f1;
        }

        .welcome-btn-outline {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
        }

        .welcome-btn-outline:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        /* Draft Banner */
        .draft-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.15);
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 0.85rem 1rem;
            text-decoration: none;
            color: white;
            transition: background 0.2s;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .draft-banner:hover {
            background: rgba(255, 255, 255, 0.22);
            color: white;
        }

        .draft-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .draft-left i {
            font-size: 1.4rem;
        }

        .draft-title {
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.1rem;
        }

        .draft-sub {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .draft-cta {
            font-weight: 700;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
        }

        /* ── Section Heading ── */
        .section-head {
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--dark, #1e293b);
            margin: 0 0 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary, #6366f1);
            font-size: 1rem;
        }

        .section-sub {
            color: var(--gray, #64748b);
            margin: 0;
            font-size: 0.85rem;
        }

        [data-theme="dark"] .section-title {
            color: #f1f5f9;
        }

        /* ── Quick Access Grid ── */
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.9rem;
        }

        .quick-card {
            background: white;
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 14px;
            padding: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.25s;
        }

        .quick-card:hover {
            border-color: var(--primary, #6366f1);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: inherit;
        }

        .quick-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .qi-primary {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .qi-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .qi-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .qi-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .qi-blue {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .qi-info {
            background: rgba(20, 184, 166, 0.1);
            color: #14b8a6;
        }

        .quick-text {
            flex: 1;
            min-width: 0;
        }

        .quick-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--dark, #1e293b);
            margin: 0 0 0.15rem;
        }

        .quick-desc {
            font-size: 0.75rem;
            color: var(--gray, #64748b);
            margin: 0;
        }

        .quick-arrow {
            color: var(--gray, #64748b);
            font-size: 0.95rem;
            transition: transform 0.2s;
        }

        .quick-card:hover .quick-arrow {
            transform: translateX(4px);
            color: var(--primary, #6366f1);
        }

        [data-theme="dark"] .quick-card {
            background: #1e293b;
            border-color: #334155;
        }

        [data-theme="dark"] .quick-title {
            color: #f1f5f9;
        }

        /* ── Activity Card ── */
        .activity-card {
            background: white;
            border-radius: 20px;
            border: 1.5px solid var(--border, #e2e8f0);
            overflow: hidden;
        }

        .activity-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid var(--border, #e2e8f0);
        }

        .activity-title {
            font-weight: 800;
            font-size: 1rem;
            color: var(--dark, #1e293b);
            margin: 0 0 0.15rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .activity-title i {
            color: var(--primary, #6366f1);
        }

        .activity-sub {
            font-size: 0.75rem;
            color: var(--gray, #64748b);
            margin: 0;
        }

        .activity-view-all {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary, #6366f1);
            text-decoration: none;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.08);
            white-space: nowrap;
        }

        .activity-view-all:hover {
            background: rgba(99, 102, 241, 0.15);
        }

        .activity-empty {
            padding: 2.5rem;
            text-align: center;
            color: var(--gray, #64748b);
            font-size: 0.875rem;
        }

        .activity-empty i {
            font-size: 2rem;
            display: block;
            margin-bottom: 0.5rem;
            opacity: 0.4;
        }

        .activity-list {
            padding: 0.25rem 0;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 0.85rem 1.25rem;
            transition: background 0.15s;
            position: relative;
        }

        .activity-item:hover {
            background: rgba(99, 102, 241, 0.04);
        }

        .activity-unread {
            background: rgba(99, 102, 241, 0.03);
        }

        .activity-icon-wrap {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .activity-body {
            flex: 1;
            min-width: 0;
        }

        .activity-item-title {
            font-weight: 600;
            font-size: 0.83rem;
            color: var(--dark, #1e293b);
            margin: 0 0 0.15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-item-msg {
            font-size: 0.75rem;
            color: var(--gray, #64748b);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.3rem;
            flex-shrink: 0;
        }

        .activity-time {
            font-size: 0.7rem;
            color: var(--gray, #64748b);
            white-space: nowrap;
        }

        .activity-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--primary, #6366f1);
        }

        [data-theme="dark"] .activity-card {
            background: #1e293b;
            border-color: #334155;
        }

        [data-theme="dark"] .activity-head {
            border-color: #334155;
        }

        [data-theme="dark"] .activity-title {
            color: #f1f5f9;
        }

        [data-theme="dark"] .activity-item-title {
            color: #f1f5f9;
        }

        /* ── Overdue Panel ── */
        .overdue-panel {
            background: white;
            border-radius: 16px;
            border: 1.5px solid rgba(239, 68, 68, 0.2);
            overflow: hidden;
        }

        .overdue-title {
            padding: 1rem 1.25rem 0.75rem;
            font-weight: 800;
            font-size: 0.875rem;
            color: #dc2626;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid rgba(239, 68, 68, 0.1);
        }

        .overdue-item {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.75rem 1.25rem;
            text-decoration: none;
            color: var(--dark, #1e293b);
            font-size: 0.875rem;
            font-weight: 500;
            border-bottom: 1px solid var(--border, #e2e8f0);
            transition: background 0.15s;
        }

        .overdue-item:last-child {
            border-bottom: none;
        }

        .overdue-item:hover {
            background: rgba(239, 68, 68, 0.04);
            color: inherit;
        }

        .overdue-item i {
            font-size: 1rem;
            flex-shrink: 0;
        }

        [data-theme="dark"] .overdue-panel {
            background: #1e293b;
            border-color: rgba(239, 68, 68, 0.3);
        }

        [data-theme="dark"] .overdue-item {
            color: #f1f5f9;
            border-color: #334155;
        }

        /* ════════════════════════════════
                       RESPONSIVE — MOBILE FIRST
                    ════════════════════════════════ */
        @media (max-width: 1280px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1024px) {
            .dash-main-row {
                grid-template-columns: 1fr;
            }

            .dash-right {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .dash-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .dash-date-chip {
                align-self: flex-start;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .stat-val {
                font-size: 1.4rem;
            }

            .stat-icon-wrap {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
                border-radius: 10px;
            }

            .dash-right {
                grid-template-columns: 1fr;
            }

            .quick-grid {
                grid-template-columns: 1fr;
            }

            .welcome-top {
                flex-direction: column;
                gap: 0.75rem;
            }

            .welcome-icon-wrap {
                width: 48px;
                height: 48px;
                font-size: 1.4rem;
            }

            .activity-item {
                gap: 0.65rem;
            }

            .alert-banner {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 0.6rem;
            }

            .stat-card {
                padding: 0.9rem;
                gap: 0.6rem;
            }

            .stat-label {
                font-size: 0.65rem;
            }

            .stat-val {
                font-size: 1.25rem;
            }

            .stat-badge {
                font-size: 0.6rem;
            }
        }

        /* ── Date Range Picker button ── */
        .dash-date-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dash-date-picker {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.45rem 0.9rem;
            background: var(--bg-card, #fff);
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--gray, #64748b);
            cursor: pointer;
            transition: all 0.2s;
            min-width: 200px;
            user-select: none;
        }

        .dash-date-picker:hover,
        .dash-date-picker.active {
            border-color: var(--primary, #6366f1);
            color: var(--primary, #6366f1);
            background: rgba(99, 102, 241, 0.05);
        }

        .dash-date-picker i:first-child {
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .dash-clear-filter {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.08);
            border: 1.5px solid rgba(239, 68, 68, 0.3);
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .dash-clear-filter:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        [data-theme="dark"] .dash-date-picker {
            background: rgba(255, 255, 255, 0.05);
            border-color: #334155;
        }

        [data-theme="dark"] .dash-date-picker.active {
            background: rgba(99, 102, 241, 0.12);
        }
    </style>

    @include('partials.daterangepicker-styles')

    @push('scripts')
        <script>
            $(document).ready(function () {
                var initFrom = $('#dashDateFrom').val();
                var initTo = $('#dashDateTo').val();
                var startDate = initFrom ? moment(initFrom) : null;
                var endDate = initTo ? moment(initTo) : null;

                $('#dashDateRange').daterangepicker({
                    autoUpdateInput: false,
                    opens: 'left',
                    showDropdowns: true,
                    linkedCalendars: false,
                    startDate: startDate || moment(),
                    endDate: endDate || moment(),
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                        'This Year': [moment().startOf('year'), moment()],
                    },
                    locale: {
                        cancelLabel: 'Clear',
                        applyLabel: 'Apply',
                        format: 'MMM D, YYYY'
                    }
                }, function (start, end) {
                    // Update label + hidden inputs on pick
                    $('#dashDateLabel').text(start.format('MMM D, YYYY') + ' \u2014 ' + end.format('MMM D, YYYY'));
                    $('#dashDateFrom').val(start.format('YYYY-MM-DD'));
                    $('#dashDateTo').val(end.format('YYYY-MM-DD'));
                    $('#dashDateRange').addClass('active');
                });

                // Apply -> submit form
                $('#dashDateRange').on('apply.daterangepicker', function () {
                    if ($('#dashDateFrom').val() && $('#dashDateTo').val()) {
                        $('#dashDateForm').submit();
                    }
                });

                // Cancel -> clear and reload without params
                $('#dashDateRange').on('cancel.daterangepicker', function () {
                    window.location.href = '{{ route("admin.dashboard") }}';
                });
            });
        </script>
    @endpush

@endsection