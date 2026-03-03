@extends('layouts.admin')

@section('title', $company->name . ' - Sales Dashboard')

@section('content')
    <div class="sales-dashboard-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('companies.index') }}" class="breadcrumb-link">Companies</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">{{ $company->name }}</span>
                    </div>
                    <h1 class="page-title">
                        @if($company->logo)
                            <img src="{{ str_starts_with($company->logo, 'http') ? $company->logo : asset($company->logo) }}"
                                alt="{{ $company->name }}" class="company-title-logo">
                        @else
                            <i class="bi bi-building"></i>
                        @endif
                        {{ $company->name }} - Sales Report
                    </h1>
                    <p class="page-subtitle">Track sales performance, targets, and daily history</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('orders.index') }}" class="btn-export btn-back">
                        <i class="bi bi-arrow-left"></i> Back to Orders
                    </a>
                    <a href="{{ route('companies.export-pdf', $company->id) }}?year={{ $year }}" class="btn-export btn-pdf">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                    <a href="{{ route('companies.export-csv', $company->id) }}?year={{ $year }}" class="btn-export btn-csv">
                        <i class="bi bi-filetype-csv"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>


        <!-- Target Progress Section -->
        <div class="target-progress-section">
            @php
                $ringTotal   = $filteredTotal ?? ($monthToDate['total_revenue'] ?? 0);
                $progress    = $currentTarget > 0 ? min(100, round(($ringTotal / $currentTarget) * 100, 1)) : 0;
                $dayPct      = round((now()->day / now()->daysInMonth) * 100);
                $gap         = $targetGap ?? ($currentTarget ? $currentTarget - $ringTotal : 0);
                $dailyAvg    = now()->day > 0 ? round($ringTotal / now()->day, 0) : 0;
                $ordersCount = $filteredOrderCount ?? ($monthToDate['order_count'] ?? 0);
            @endphp

            <div class="target-header">
                <h2>
                    @if($isEntireYearFilter ?? false)
                        {{ $year }} Target Progress
                    @elseif(!($isCurrentMonthFilter ?? true))
                        {{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} -
                        {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }} Progress
                    @else
                        {{ now()->format('F Y') }} Target Progress
                    @endif
                </h2>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <span class="header-date-chip">
                        <i class="bi bi-clock"></i> {{ now()->format('d M Y, h:i A') }}
                    </span>
                    <button type="button" class="btn-set-target" onclick="openTargetModal()">
                        <i class="bi bi-bullseye"></i> Set Target
                    </button>
                </div>
            </div>

            <div class="tp-layout">

                {{-- LEFT: Mini stat cards --}}
                <div class="tp-left">
                    <div class="tp-mini-card">
                        <div class="tp-mini-icon" style="background:rgba(99,102,241,0.1);color:#6366f1;">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="tp-mini-body">
                            <span class="tp-mini-label">Total Orders</span>
                            <span class="tp-mini-value">{{ $ordersCount }}</span>
                        </div>
                    </div>
                    <div class="tp-mini-card">
                        <div class="tp-mini-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6;">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="tp-mini-body">
                            <span class="tp-mini-label">Daily Avg Revenue</span>
                            <span class="tp-mini-value">{{ $company->currency_symbol }}{{ number_format($dailyAvg, 0) }}</span>
                        </div>
                    </div>
                    <div class="tp-mini-card">
                        <div class="tp-mini-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <div class="tp-mini-body">
                            <span class="tp-mini-label">Target Gap</span>
                            <span class="tp-mini-value {{ $gap <= 0 ? 'text-success' : '' }}">
                                {{ $gap <= 0 ? 'Met!' : $company->currency_symbol.number_format($gap, 0) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- CENTER: Ring --}}
                <div class="tp-center">
                    <div class="large-progress-ring">
                        <div class="ring-glow-bg"></div>
                        <svg viewBox="0 0 100 100" class="circular-chart-large">
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%"   style="stop-color:#818cf8;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#4f46e5;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <circle class="circle-bg-large" cx="50" cy="50" r="40" />
                            <circle class="circle-large" cx="50" cy="50" r="40"
                                stroke-dasharray="{{ $progress * 2.51327 }}, 251.327" stroke-dashoffset="0" />
                        </svg>
                        <div class="ring-content">
                            <span class="ring-label">TOTAL</span>
                            <span class="ring-value">{{ $company->currency_symbol }}{{ number_format($ringTotal, 0) }}</span>
                            <span class="ring-divider">of</span>
                            <span class="ring-target">
                                @if($currentTarget)
                                    {{ $company->currency_symbol }}{{ number_format($currentTarget, 0) }}
                                @else
                                    <em class="ring-no-target">No Target</em>
                                @endif
                            </span>
                        </div>
                        <div class="ring-pct-badge">{{ round($progress) }}%</div>
                    </div>
                </div>

                {{-- RIGHT: Stats --}}
                <div class="tp-right">
                    <div class="progress-percentage">
                        <span class="pct-number">{{ round($progress) }}%</span>
                        <span class="pct-label">of target achieved</span>
                    </div>

                    @if($isCurrentMonthFilter ?? true)
                        <div class="progress-days-block">
                            <div class="days-row">
                                <span><i class="bi bi-calendar3"></i> Month progress</span>
                                <span class="days-count">{{ now()->day }} / {{ now()->daysInMonth }} days</span>
                            </div>
                            <div class="days-track">
                                <div class="days-fill" style="width:{{ $dayPct }}%"></div>
                            </div>
                            <div class="days-pct">{{ $dayPct }}% of month elapsed</div>
                        </div>
                    @elseif($isEntireYearFilter ?? false)
                        <div class="progress-days-block">
                            <div class="days-row">
                                <span><i class="bi bi-calendar3"></i> Year progress</span>
                                @php 
                                    $daysInYear = Carbon\Carbon::create($year)->daysInYear;
                                    $dayOfYear = $year == now()->year ? now()->dayOfYear : $daysInYear;
                                    $yearPct = round(($dayOfYear / $daysInYear) * 100);
                                @endphp
                                <span class="days-count">{{ $dayOfYear }} / {{ $daysInYear }} days</span>
                            </div>
                            <div class="days-track">
                                <div class="days-fill" style="width:{{ $yearPct }}%"></div>
                            </div>
                            <div class="days-pct">{{ $yearPct }}% of year elapsed</div>
                        </div>
                    @else
                        <div class="progress-days-block">
                            <div class="days-row">
                                <span><i class="bi bi-calendar-range"></i> Selected period</span>
                                <span class="days-count">
                                    {{ \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1 }} days
                                </span>
                            </div>
                        </div>
                    @endif

                    @if(($isCurrentMonthFilter ?? true) && $projectedTotal > 0)
                        <div class="progress-projection {{ $projectedTotal >= ($currentTarget ?? 0) ? 'on-track' : 'behind' }}">
                            <div class="proj-icon">
                                <i class="bi {{ $projectedTotal >= ($currentTarget ?? 0) ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                            </div>
                            <div class="proj-text">
                                <span class="proj-label">Month-end projection</span>
                                <span class="proj-amount">{{ $company->currency_symbol }}{{ number_format($projectedTotal, 0) }}</span>
                            </div>
                            <span class="proj-status-chip {{ $projectedTotal >= ($currentTarget ?? 0) ? 'on-track-chip' : 'behind-chip' }}">
                                {{ $projectedTotal >= ($currentTarget ?? 0) ? 'On Track' : 'Behind' }}
                            </span>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">

            {{-- Card 1: Today's Sales --}}
            <div class="stat-card stat-card-sales">
                <div class="stat-card-top">
                    <div class="stat-icon-wrap green">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <span class="live-badge"><i class="bi bi-circle-fill"></i> Live</span>
                </div>
                <div class="stat-label">Today's Sales</div>
                <div class="stat-value">{{ $company->currency_symbol }}{{ number_format($todaysSales, 0) }}</div>
                <div class="stat-footer">
                    <span class="stat-footer-chip green-chip">
                        <i class="bi bi-bag-check"></i> {{ $todaysOrderCount }} orders today
                    </span>
                </div>
            </div>

            {{-- Card 2: This Month / Selected Period --}}
            <div class="stat-card stat-card-info">
                <div class="stat-card-top">
                    <div class="stat-icon-wrap blue">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                </div>
                <div class="stat-label">
                    @if($isEntireYearFilter ?? false)
                        This Year
                    @elseif(!($isCurrentMonthFilter ?? true)) 
                        Selected Period 
                    @else 
                        This Month 
                    @endif
                </div>
                @php $periodTotal = $filteredTotal ?? ($monthToDate['total_revenue'] ?? 0); @endphp
                <div class="stat-value">{{ $company->currency_symbol }}{{ number_format($periodTotal, 0) }}</div>
                <div class="stat-footer">
                    <span class="stat-footer-chip blue-chip">
                        <i class="bi bi-cart-check"></i> {{ $filteredOrderCount ?? ($monthToDate['order_count'] ?? 0) }} orders
                    </span>
                </div>
            </div>

            {{-- Card 3: Avg Order Value --}}
            <div class="stat-card stat-card-primary">
                <div class="stat-card-top">
                    <div class="stat-icon-wrap purple">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div class="stat-label">Avg Order Value</div>
                <div class="stat-value">{{ $company->currency_symbol }}{{ number_format($avgOrderValue ?? 0, 0) }}</div>
                <div class="stat-footer">
                    <span class="stat-footer-chip purple-chip">
                        <i class="bi bi-calculator"></i> Per order
                    </span>
                </div>
            </div>

            {{-- Card 4: Target Gap --}}
            <div class="stat-card stat-card-warning">
                <div class="stat-card-top">
                    <div class="stat-icon-wrap amber">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    @if($gap <= 0)
                        <span class="achieved-badge"><i class="bi bi-check-circle-fill"></i> Met</span>
                    @endif
                </div>
                <div class="stat-label">Target Gap</div>
                <div class="stat-value {{ $gap <= 0 ? 'text-success' : '' }}">
                    {{ $gap <= 0 ? 'Achieved!' : $company->currency_symbol . number_format($gap, 0) }}
                </div>
                <div class="stat-footer">
                    <span class="stat-footer-chip {{ $gap <= 0 ? 'green-chip' : 'amber-chip' }}">
                        @if($gap > 0)
                            <i class="bi bi-arrow-up"></i> {{ $company->currency_symbol }}{{ number_format($gap, 0) }} still needed
                        @else
                            <i class="bi bi-trophy"></i> Target met!
                        @endif
                    </span>
                </div>
            </div>

        </div>

        <!-- Monthly Chart -->
        <div class="chart-section">
            <div class="chart-header">
                <h3><i class="bi bi-bar-chart-fill"></i> Monthly Revenue {{ $year }}</h3>
                <div class="year-selector">
                    <a href="{{ route('companies.sales-dashboard', [$company->id, 'year' => $year - 1]) }}"
                        class="year-nav">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <span class="current-year">{{ $year }}</span>
                    <a href="{{ route('companies.sales-dashboard', [$company->id, 'year' => $year + 1]) }}"
                        class="year-nav {{ $year >= now()->year ? 'disabled' : '' }}">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Sales History -->
        <div class="history-section">
            <div class="history-header">
                <h3><i class="bi bi-table"></i> Sales History</h3>
                <form method="GET" class="date-filter-form" id="salesHistoryFilterForm">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="date-range-wrapper">
                        <input type="text" id="salesDateRange" class="date-range-input" placeholder="Select Date Range"
                            readonly>
                        <input type="hidden" name="date_from" id="salesDateFrom" value="{{ $dateFrom }}">
                        <input type="hidden" name="date_to" id="salesDateTo" value="{{ $dateTo }}">
                    </div>
                    <button type="submit" class="btn-filter">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </form>
            </div>
            <div class="table-container">
                @if($dailyHistoryWithTotals->count() > 0)
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                                <th>Order Types</th>
                                <th>Running Total</th>
                                <th>% of Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyHistoryWithTotals as $day)
                                <tr>
                                    <td>
                                        <span class="date-main">{{ $day->sales_date->format('M d, Y') }}</span>
                                        <span class="date-day">{{ $day->sales_date->format('l') }}</span>
                                    </td>
                                    <td>{{ $day->order_count }}</td>
                                    <td class="revenue-cell">
                                        {{ $company->currency_symbol }}{{ number_format($day->total_revenue, 0) }}
                                    </td>
                                    <td>
                                        @if($day->order_type_breakdown)
                                            @foreach($day->order_type_breakdown as $type => $count)
                                                <span class="type-badge type-{{ $type }}">{{ $count }}
                                                    {{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="running-total">
                                        {{ $company->currency_symbol }}{{ number_format($day->running_total, 0) }}
                                    </td>
                                    <td>                                        @if($day->target_percent !== null)
                                            <span class="target-badge {{ $day->target_percent >= 100 ? 'achieved' : '' }}">
                                                {{ $day->target_percent }}%
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <p>No sales data found for the selected date range.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Set Target Modal -->
    <div class="modal-overlay" id="targetModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i class="bi bi-bullseye"></i> Set Monthly Target</h4>
                <button type="button" class="modal-close" onclick="closeTargetModal()">&times;</button>
            </div>
            <form action="{{ route('companies.set-target', $company->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Year</label>
                        <select name="year" class="form-input">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Month</label>
                        <select name="month" class="form-input">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Target Amount ({{ $company->currency_symbol }})</label>
                        <input type="number" name="target_amount" class="form-input" step="0.01" min="0"
                            value="{{ $currentTarget ?? '' }}" placeholder="Enter target amount">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeTargetModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Target</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        [data-theme="dark"] .sales-dashboard-container {
            background: var(--bg-body, #0f172a);
        }

        [data-theme="dark"] .page-header,
        [data-theme="dark"] .target-progress-section,
        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .chart-section,
        [data-theme="dark"] .history-section,
        [data-theme="dark"] .modal-content {
            background: var(--bg-card, #1e293b);
            border: 1px solid rgba(148, 163, 184, 0.38);
            box-shadow: 0 6px 18px rgba(2, 6, 23, 0.18);
        }

        [data-theme="dark"] .target-progress-section::after {
            background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 72%);
        }

        [data-theme="dark"] .tp-mini-card,
        [data-theme="dark"] .year-selector {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="dark"] .page-title,
        [data-theme="dark"] .target-header h2,
        [data-theme="dark"] .tp-mini-value,
        [data-theme="dark"] .pct-number,
        [data-theme="dark"] .days-count,
        [data-theme="dark"] .stat-value,
        [data-theme="dark"] .chart-header h3,
        [data-theme="dark"] .history-header h3,
        [data-theme="dark"] .current-year,
        [data-theme="dark"] .date-main,
        [data-theme="dark"] .modal-header h4,
        [data-theme="dark"] .form-group label {
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] .page-subtitle,
        [data-theme="dark"] .breadcrumb-nav,
        [data-theme="dark"] .breadcrumb-link,
        [data-theme="dark"] .tp-mini-label,
        [data-theme="dark"] .pct-label,
        [data-theme="dark"] .days-row,
        [data-theme="dark"] .days-pct,
        [data-theme="dark"] .ring-label,
        [data-theme="dark"] .ring-divider,
        [data-theme="dark"] .ring-target,
        [data-theme="dark"] .stat-label,
        [data-theme="dark"] .date-day {
            color: var(--text-secondary, #94a3b8);
        }

        [data-theme="dark"] .tp-mini-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(148, 163, 184, 0.42);
            box-shadow: none;
        }

        [data-theme="dark"] .circle-bg-large,
        [data-theme="dark"] .days-track {
            stroke: #334155;
            background: #334155;
        }

        [data-theme="dark"] .btn-export,
        [data-theme="dark"] .btn-filter,
        [data-theme="dark"] .btn-cancel {
            background: rgba(15, 23, 42, 0.45);
            border-color: rgba(148, 163, 184, 0.4);
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] .btn-export:hover,
        [data-theme="dark"] .btn-filter:hover,
        [data-theme="dark"] .btn-cancel:hover,
        [data-theme="dark"] .year-nav:hover:not(.disabled) {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(148, 163, 184, 0.5);
            box-shadow: none;
        }

        [data-theme="dark"] .btn-back {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff !important;
        }

        [data-theme="dark"] .history-table th {
            background: rgba(15, 23, 42, 0.5);
            border-bottom-color: rgba(148, 163, 184, 0.36);
        }

        [data-theme="dark"] .history-table td {
            border-bottom-color: rgba(148, 163, 184, 0.26);
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] .history-table tr:hover td {
            background: rgba(255, 255, 255, 0.025);
        }

        [data-theme="dark"] .type-badge,
        [data-theme="dark"] .target-badge,
        [data-theme="dark"] .header-date-chip {
            background: rgba(255, 255, 255, 0.055);
            color: var(--text-secondary, #94a3b8);
            border-color: rgba(148, 163, 184, 0.28);
        }

        [data-theme="dark"] .modal-header,
        [data-theme="dark"] .modal-footer {
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="dark"] .modal-footer {
            background: rgba(15, 23, 42, 0.42);
        }

        [data-theme="dark"] .form-input {
            background: rgba(15, 23, 42, 0.62);
            color: var(--text-primary, #f1f5f9);
            border-color: rgba(148, 163, 184, 0.32);
        }

        [data-theme="dark"] .form-input::placeholder {
            color: var(--text-secondary, #94a3b8);
        }

        .sales-dashboard-container {
            padding: 2rem;
            max-width: 1600px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

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

        .company-title-logo {
            width: 350px;
            height: 200px;
            border-radius: 10px;
            object-fit: cover;
        }

        .page-subtitle {
            color: var(--gray);
            margin: 0;
        }

        .header-right {
            display: flex;
            gap: 0.75rem;
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: 2px solid var(--border);
            background: white;
            color: var(--dark);
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-pdf:hover {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-csv:hover {
            border-color: #10b981;
            color: #10b981;
        }

        .btn-back {
            background: var(--primary);
            color: white !important;
            border-color: var(--primary);
        }

        .btn-back:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* ── TARGET PROGRESS SECTION ── */
        .target-progress-section {
            background: white;
            border-radius: 20px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 4px 16px rgba(99,102,241,0.06);
            position: relative;
            overflow: hidden;
        }

        .target-progress-section::after {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(99,102,241,0.06) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .target-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .target-header h2 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .header-date-chip {
            font-size: 0.78rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.35rem;
            background: var(--light-gray);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
        }

        .btn-set-target {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-set-target:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* 3-column grid */
        .tp-layout {
            display: grid;
            grid-template-columns: 190px 220px 1fr;
            gap: 2rem;
            align-items: center;
        }

        @media (max-width: 900px) {
            .tp-layout { grid-template-columns: 1fr; }
        }

        .tp-left {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .tp-mini-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 0.9rem;
            background: var(--light-gray);
            border-radius: 12px;
            border: 1.5px solid transparent;
            transition: all 0.2s;
        }

        .tp-mini-card:hover {
            border-color: rgba(99,102,241,0.2);
            background: white;
            box-shadow: 0 2px 8px rgba(99,102,241,0.08);
        }

        .tp-mini-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .tp-mini-body { display: flex; flex-direction: column; gap: 0.05rem; }

        .tp-mini-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--gray);
        }

        .tp-mini-value {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.02em;
        }

        .tp-center {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .large-progress-ring {
            position: relative;
            width: 200px;
            height: 200px;
            flex-shrink: 0;
        }

        .ring-glow-bg {
            position: absolute;
            inset: 20px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 65%);
            animation: ringGlowAnim 3s ease-in-out infinite;
        }

        @keyframes ringGlowAnim {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50%       { opacity: 1;   transform: scale(1.1); }
        }

        .circular-chart-large {
            display: block;
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .circle-bg-large { fill: none; stroke: #e8eaf6; stroke-width: 7; }

        .circle-large {
            fill: none;
            stroke: url(#gradient);
            stroke-width: 7;
            stroke-linecap: round;
            transition: stroke-dasharray 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ring-content {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.1rem;
            width: 100%;
        }

        .ring-label {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: var(--gray);
            text-transform: uppercase;
        }

        .ring-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .ring-divider { font-size: 0.7rem; color: var(--gray); line-height: 1; }
        .ring-target  { font-size: 0.82rem; color: var(--gray); }
        .ring-no-target { font-style: italic; font-size: 0.8rem; }

        .ring-pct-badge {
            position: absolute;
            bottom: 8px; right: 4px;
            background: var(--primary);
            color: white;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(99,102,241,0.4);
        }

        .tp-right {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .progress-percentage {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .pct-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.05em;
            line-height: 1;
        }

        .pct-label {
            font-size: 0.82rem;
            color: var(--gray);
            font-weight: 500;
        }

        .progress-days-block { display: flex; flex-direction: column; gap: 0.4rem; }

        .days-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .days-row i { margin-right: 0.3rem; }
        .days-count { font-weight: 700; color: var(--dark); font-size: 0.8rem; }

        .days-track {
            width: 100%; height: 6px;
            background: #e8eaf6;
            border-radius: 10px;
            overflow: hidden;
        }

        .days-fill {
            height: 100%;
            background: linear-gradient(90deg, #a5b4fc, var(--primary));
            border-radius: 10px;
            animation: fillGrow 1s ease forwards;
        }

        @keyframes fillGrow { from { width: 0 !important; } }

        .days-pct { font-size: 0.75rem; color: var(--gray); text-align: right; }
        .progress-days { font-size: 0.85rem; color: var(--gray); }

        .progress-projection {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            border-radius: 14px;
            font-weight: 600;
            border: 1.5px solid transparent;
        }

        .progress-projection.on-track { background: rgba(16,185,129,0.07); border-color: rgba(16,185,129,0.2); }
        .progress-projection.behind   { background: rgba(239,68,68,0.07);   border-color: rgba(239,68,68,0.2); }

        .proj-icon {
            width: 34px; height: 34px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }

        .on-track .proj-icon { background: rgba(16,185,129,0.15); color: var(--success); }
        .behind   .proj-icon { background: rgba(239,68,68,0.15);   color: var(--danger); }
        .proj-text { display: flex; flex-direction: column; gap: 0.05rem; flex: 1; }

        .proj-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--gray);
            font-weight: 600;
        }

        .proj-amount { font-size: 1rem; font-weight: 800; letter-spacing: -0.02em; }
        .on-track .proj-amount { color: var(--success); }
        .behind   .proj-amount { color: var(--danger); }

        .proj-status-chip {
            font-size: 0.68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.06em;
            padding: 0.25rem 0.6rem; border-radius: 20px; white-space: nowrap;
        }

        .on-track-chip { background: rgba(16,185,129,0.15); color: var(--success); }
        .behind-chip   { background: rgba(239,68,68,0.15);  color: var(--danger); }

        /* ── STATS GRID ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.4rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 4px 16px rgba(99,102,241,0.05);
            border: 1.5px solid transparent;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            border-radius: 20px 20px 0 0;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .stat-card-sales::before  { background: var(--success); }
        .stat-card-info::before   { background: var(--info); }
        .stat-card-primary::before{ background: var(--primary); }
        .stat-card-warning::before{ background: var(--warning); }

        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px rgba(99,102,241,0.12), 0 2px 8px rgba(0,0,0,0.06); }
        .stat-card:hover::before { opacity: 1; }
        .stat-card-sales:hover   { border-color: rgba(16,185,129,0.2); }
        .stat-card-info:hover    { border-color: rgba(59,130,246,0.2); }
        .stat-card-primary:hover { border-color: rgba(99,102,241,0.2); }
        .stat-card-warning:hover { border-color: rgba(245,158,11,0.2); }

        .stat-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .stat-icon-wrap {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }

        .stat-icon-wrap.green  { background: rgba(16,185,129,0.1);  color: var(--success); }
        .stat-icon-wrap.blue   { background: rgba(59,130,246,0.1);   color: var(--info); }
        .stat-icon-wrap.purple { background: rgba(99,102,241,0.1);   color: var(--primary); }
        .stat-icon-wrap.amber  { background: rgba(245,158,11,0.1);   color: var(--warning); }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.04em;
            line-height: 1.1;
        }

        .text-success { color: var(--success) !important; }

        .stat-footer { margin-top: 0.25rem; }

        .stat-footer-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
        }

        .green-chip  { background: rgba(16,185,129,0.1);  color: var(--success); }
        .blue-chip   { background: rgba(59,130,246,0.1);   color: var(--info); }
        .purple-chip { background: rgba(99,102,241,0.1);   color: var(--primary); }
        .amber-chip  { background: rgba(245,158,11,0.1);   color: var(--warning); }

        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--success);
            font-size: 0.72rem;
            font-weight: 700;
            background: rgba(16,185,129,0.1);
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
        }

        .live-badge i { font-size: 0.4rem; animation: livePulse 1.5s ease-in-out infinite; }

        @keyframes livePulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .achieved-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--success);
            background: rgba(16,185,129,0.1);
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
        }

        /* ── CHART SECTION ── */
        .chart-section,
        .history-section {
            background: white;
            border-radius: 20px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 4px 16px rgba(99,102,241,0.05);
        }

        .chart-header,
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .chart-header h3,
        .history-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-header h3 i,
        .history-header h3 i { color: var(--primary); }

        .year-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--light-gray);
            border-radius: 12px;
            padding: 0.3rem 0.5rem;
        }

        .year-nav {
            width: 30px; height: 30px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            border: none;
            background: transparent;
            color: var(--gray);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .year-nav:hover:not(.disabled) {
            background: white;
            color: var(--primary);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .year-nav.disabled { opacity: 0.35; pointer-events: none; }

        .current-year {
            font-weight: 800;
            font-size: 1rem;
            color: var(--dark);
            min-width: 40px;
            text-align: center;
        }

        .chart-container { position: relative; height: 360px; }

        /* Date Filter Form */
        .date-filter-form {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .date-input {
            padding: 0.5rem 0.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .date-separator { color: var(--gray); }

        .btn-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: white;
            color: var(--gray);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-filter:hover { border-color: var(--primary); color: var(--primary); }

        /* ── HISTORY TABLE ── */
        .table-container { overflow-x: auto; }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            background: #f8fafc;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            border-bottom: 2px solid var(--border);
        }

        .history-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.875rem;
            color: var(--dark);
            vertical-align: middle;
            transition: background 0.15s;
        }

        .history-table tr:hover td { background: #f8fafc; }
        .history-table tr:last-child td { border-bottom: none; }

        .date-main { display: block; font-weight: 600; }
        .date-day  { display: block; font-size: 0.78rem; color: var(--gray); }

        .revenue-cell { font-weight: 700; color: var(--success); }
        .running-total { font-weight: 700; color: var(--primary); }

        .type-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            margin-right: 0.25rem;
            background: var(--light-gray);
            color: var(--gray);
        }

        .target-badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            background: var(--light-gray);
            color: var(--gray);
        }

        .target-badge.achieved {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .empty-state { text-align: center; padding: 3rem; color: var(--gray); }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            cursor: pointer;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .btn-cancel {
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            background: white;
            color: var(--gray);
            font-weight: 600;
            cursor: pointer;
        }

        .btn-save {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-save:hover {
            background: var(--primary-dark);
        }

        /* Date Range Wrapper for filter form */
        .date-range-wrapper {
            position: relative;
        }

        .date-range-wrapper .date-range-input {
            min-width: 250px;
        }
    </style>

    {{-- Include daterangepicker styles --}}
    @include('partials.daterangepicker-styles')

    {{-- DateRangePicker CDN --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function openTargetModal() {
            document.getElementById('targetModal').style.display = 'flex';
        }

        function closeTargetModal() {
            document.getElementById('targetModal').style.display = 'none';
        }

        // Monthly Chart
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyData = @json(array_values($monthlySummary));

            const barGradient = ctx.createLinearGradient(0, 0, 0, 400);
            barGradient.addColorStop(0,   'rgba(99, 102, 241, 0.90)');
            barGradient.addColorStop(0.6, 'rgba(99, 102, 241, 0.55)');
            barGradient.addColorStop(1,   'rgba(99, 102, 241, 0.10)');

            const targetGradient = ctx.createLinearGradient(0, 0, 0, 400);
            targetGradient.addColorStop(0, 'rgba(239, 68, 68, 0.12)');
            targetGradient.addColorStop(1, 'rgba(239, 68, 68, 0.00)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthlyData.map(d => d.month_name),
                    datasets: [{
                        label: 'Revenue',
                        data: monthlyData.map(d => d.revenue),
                        backgroundColor: barGradient,
                        borderColor: '#6366f1',
                        borderWidth: 0,
                        borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                        borderSkipped: false,
                        hoverBackgroundColor: 'rgba(99, 102, 241, 1)',
                    }, {
                        label: 'Target',
                        data: monthlyData.map(d => d.target),
                        type: 'line',
                        borderColor: '#f43f5e',
                        borderWidth: 2,
                        borderDash: [5, 4],
                        backgroundColor: targetGradient,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#f43f5e',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#f43f5e',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'center',
                            labels: {
                                padding: 24,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 12, weight: '600' },
                                color: '#475569',
                                boxWidth: 8, boxHeight: 8,
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.92)',
                            titleColor: '#f1f5f9',
                            bodyColor: '#cbd5e1',
                            borderColor: 'rgba(99,102,241,0.3)',
                            borderWidth: 1,
                            padding: { top: 10, bottom: 10, left: 14, right: 14 },
                            cornerRadius: 12,
                            titleFont: { size: 13, weight: '700' },
                            bodyFont: { size: 12 },
                            displayColors: true,
                            boxWidth: 8, boxHeight: 8,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed.y;
                                    return '  ' + context.dataset.label + ': {{ $company->currency_symbol }}' + val.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: '#94a3b8', font: { size: 12, weight: '600' }, padding: 8 }
                        },
                        y: {
                            beginAtZero: true,
                            border: { display: false, dash: [4, 4] },
                            grid: { color: 'rgba(226, 232, 240, 0.8)' },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 },
                                padding: 10,
                                maxTicksLimit: 6,
                                callback: function(value) {
                                    if (value >= 1000) return '{{ $company->currency_symbol }}' + (value / 1000).toFixed(0) + 'k';
                                    return '{{ $company->currency_symbol }}' + value;
                                }
                            }
                        }
                    },
                    animation: { duration: 800, easing: 'easeOutQuart' }
                }
            });
        });

        // Initialize Date Range Picker for Sales History filter
        $(document).ready(function () {
            var startDate = moment('{{ $dateFrom }}');
            var endDate = moment('{{ $dateTo }}');

            $('#salesDateRange').daterangepicker({
                startDate: startDate,
                endDate: endDate,
                opens: 'left',
                drops: 'up',
                autoApply: false,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 3 Months': [moment().subtract(3, 'months').startOf('month'), moment().subtract(1, 'day')],
                    'This Year': [moment().startOf('year'), moment()]
                },
                locale: {
                    format: 'MMM D, YYYY',
                    separator: ' - ',
                    applyLabel: 'Apply',
                    cancelLabel: 'Cancel',
                    customRangeLabel: 'Custom Range'
                }
            }, function (start, end, label) {
                // Update hidden fields
                $('#salesDateFrom').val(start.format('YYYY-MM-DD'));
                $('#salesDateTo').val(end.format('YYYY-MM-DD'));
                // Auto-submit the form
                $('#salesHistoryFilterForm').submit();
            });

            // Update display text
            $('#salesDateRange').val(startDate.format('MMM D, YYYY') + ' - ' + endDate.format('MMM D, YYYY'));
        });
    </script>
@endsection
