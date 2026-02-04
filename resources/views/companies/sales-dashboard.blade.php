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
            <div class="target-header">
                <h2>
                    @if(!($isCurrentMonthFilter ?? true))
                        {{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} -
                        {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }} Progress
                    @else
                        {{ now()->format('F Y') }} Target Progress
                    @endif
                </h2>
                <button type="button" class="btn-set-target" onclick="openTargetModal()">
                    <i class="bi bi-bullseye"></i> Set Target
                </button>
            </div>
            <div class="target-content">
                <div class="target-ring-wrapper">
                    @php
                        // Use filtered total for ring chart
                        $ringTotal = $filteredTotal ?? ($monthToDate['total_revenue'] ?? 0);
                        $progress = $currentTarget > 0 ? min(100, round(($ringTotal / $currentTarget) * 100, 1)) : 0;
                    @endphp
                    <div class="large-progress-ring">
                        <svg viewBox="0 0 100 100" class="circular-chart-large">
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#4f46e5;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <circle class="circle-bg-large" cx="50" cy="50" r="40" />
                            <circle class="circle-large" cx="50" cy="50" r="40"
                                stroke-dasharray="{{ $progress * 2.51327 }}, 251.327" stroke-dashoffset="0" />
                        </svg>
                        <div class="ring-content">
                            <span
                                class="ring-value">{{ $company->currency_symbol }}{{ number_format($ringTotal, 0) }}</span>
                            <span class="ring-divider">/</span>
                            <span
                                class="ring-target">{{ $currentTarget ? $company->currency_symbol . number_format($currentTarget, 0) : 'No Target' }}</span>
                        </div>
                    </div>
                    <div class="progress-info">
                        <div class="progress-percentage">{{ round($progress) }}% of target achieved</div>
                        @if($isCurrentMonthFilter ?? true)
                            <div class="progress-days">{{ now()->day }} of {{ now()->daysInMonth }} days
                                ({{ round((now()->day / now()->daysInMonth) * 100) }}% of month)</div>
                        @else
                            <div class="progress-days">
                                {{ \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1 }} days
                                selected
                            </div>
                        @endif
                        @if(($isCurrentMonthFilter ?? true) && $projectedTotal > 0)
                            <div
                                class="progress-projection {{ $projectedTotal >= ($currentTarget ?? 0) ? 'on-track' : 'behind' }}">
                                <i
                                    class="bi {{ $projectedTotal >= ($currentTarget ?? 0) ? 'bi-arrow-up-circle' : 'bi-arrow-down-circle' }}"></i>
                                Projected: {{ $company->currency_symbol }}{{ number_format($projectedTotal, 0) }} by month end
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-sales">
                <div class="stat-icon"
                    style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05)); color: #10b981;">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Today's Sales</div>
                    <div class="stat-value">{{ $company->currency_symbol }}{{ number_format($todaysSales, 0) }}</div>
                    <div class="stat-trend">
                        <span class="live-badge"><i class="bi bi-circle-fill"></i> Live</span>
                        {{ $todaysOrderCount }} orders
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-calendar-week"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">
                        @if(!($isCurrentMonthFilter ?? true))
                            Selected Period
                        @else
                            This Month
                        @endif
                    </div>
                    @php $periodTotal = $filteredTotal ?? ($monthToDate['total_revenue'] ?? 0); @endphp
                    <div class="stat-value">{{ $company->currency_symbol }}{{ number_format($periodTotal, 0) }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-cart-check"></i> {{ $filteredOrderCount ?? ($monthToDate['order_count'] ?? 0) }}
                        orders
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Avg Order Value</div>
                    <div class="stat-value">{{ $company->currency_symbol }}{{ number_format($avgOrderValue ?? 0, 0) }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-calculator"></i> Per order
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Target Gap</div>
                    @php
                        $gap = $targetGap ?? ($currentTarget ? $currentTarget - ($filteredTotal ?? 0) : 0);
                    @endphp
                    <div class="stat-value {{ $gap <= 0 ? 'text-success' : '' }}">
                        {{ $gap <= 0 ? 'Achieved!' : $company->currency_symbol . number_format($gap, 0) }}
                    </div>
                    <div class="stat-trend">
                        @if($gap > 0)
                            <i class="bi bi-arrow-up"></i> Still needed
                        @else
                            <i class="bi bi-check-circle"></i> Target met!
                        @endif
                    </div>
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
                <canvas id="monthlyChart" height="300"></canvas>
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
                                    </td>
                                    <td>
                                        @if($day->target_percent !== null)
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

        /* Target Progress Section */
        .target-progress-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .target-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .target-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .btn-set-target {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
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

        .target-content {
            display: flex;
            justify-content: center;
        }

        .target-ring-wrapper {
            display: flex;
            align-items: center;
            gap: 3rem;
        }

        .large-progress-ring {
            position: relative;
            width: 200px;
            height: 200px;
        }

        .circular-chart-large {
            display: block;
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .circle-bg-large {
            fill: none;
            stroke: #eee;
            stroke-width: 8;
        }

        .circle-large {
            fill: none;
            stroke: url(#gradient);
            stroke-width: 8;
            stroke-linecap: round;
            transition: stroke-dasharray 0.5s ease;
        }

        .ring-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .ring-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .ring-divider {
            font-size: 1rem;
            color: var(--gray);
        }

        .ring-target {
            font-size: 1rem;
            color: var(--gray);
        }

        .progress-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .progress-percentage {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
        }

        .progress-days {
            font-size: 0.95rem;
            color: var(--gray);
        }

        .progress-projection {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-weight: 600;
        }

        .progress-projection.on-track {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .progress-projection.behind {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card-sales .stat-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
            color: #10b981;
        }

        .stat-card-info .stat-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
            color: #3b82f6;
        }

        .stat-card-primary .stat-icon {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
            color: #6366f1;
        }

        .stat-card-warning .stat-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            color: #f59e0b;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .stat-trend {
            font-size: 0.875rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .text-success {
            color: #10b981 !important;
        }

        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #10b981;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .live-badge i {
            font-size: 0.5rem;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        /* Chart Section */
        .chart-section,
        .history-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-header h3 i,
        .history-header h3 i {
            color: var(--primary);
        }

        .year-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .year-nav {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border);
            color: var(--gray);
            text-decoration: none;
            transition: all 0.2s;
        }

        .year-nav:hover:not(.disabled) {
            border-color: var(--primary);
            color: var(--primary);
        }

        .year-nav.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .current-year {
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--dark);
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

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

        .date-separator {
            color: var(--gray);
        }

        .btn-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            background: white;
            color: var(--gray);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-filter:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* History Table */
        .table-container {
            overflow-x: auto;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            background: var(--light-gray);
            padding: 1rem;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .history-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
            color: var(--dark);
        }

        .date-main {
            display: block;
            font-weight: 600;
        }

        .date-day {
            display: block;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .revenue-cell {
            font-weight: 600;
            color: #10b981;
        }

        .running-total {
            font-weight: 600;
            color: var(--primary);
        }

        .type-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.25rem;
            background: var(--light-gray);
            color: var(--gray);
        }

        .target-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            background: var(--light-gray);
            color: var(--gray);
        }

        .target-badge.achieved {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

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
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthlyData.map(d => d.month_name),
                    datasets: [{
                        label: 'Revenue',
                        data: monthlyData.map(d => d.revenue),
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderColor: '#6366f1',
                        borderWidth: 1,
                        borderRadius: 6,
                    }, {
                        label: 'Target',
                        data: monthlyData.map(d => d.target),
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: '#ef4444',
                        borderWidth: 2,
                        type: 'line',
                        fill: false,
                        tension: 0.1,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '{{ $company->currency_symbol }}' + value.toLocaleString();
                                }
                            }
                        }
                    }
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