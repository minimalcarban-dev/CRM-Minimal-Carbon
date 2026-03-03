@extends('layouts.admin')

@section('title', 'Lead Analytics')

@push('styles')
    <style>
        .analytics-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .kpi-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .kpi-icon.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .kpi-icon.success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .kpi-icon.warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        .kpi-icon.info {
            background: linear-gradient(135deg, var(--info), #2563eb);
        }

        .kpi-icon.danger {
            background: linear-gradient(135deg, var(--danger), #dc2626);
        }

        .kpi-content {
            flex: 1;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .kpi-label {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }

        .kpi-trend {
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .kpi-trend.up {
            color: var(--success);
        }

        .kpi-trend.down {
            color: var(--danger);
        }

        /* Charts Row */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-title i {
            color: var(--primary);
        }

        /* Status Bars */
        .status-bars {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .status-bar-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .status-bar-label {
            width: 100px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--dark);
        }

        .status-bar-track {
            flex: 1;
            height: 10px;
            background: var(--light-gray);
            border-radius: 5px;
            overflow: hidden;
        }

        .status-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s;
        }

        .status-bar-fill.primary {
            background: var(--primary);
        }

        .status-bar-fill.info {
            background: var(--info);
        }

        .status-bar-fill.success {
            background: var(--success);
        }

        .status-bar-fill.secondary {
            background: var(--gray);
        }

        .status-bar-value {
            width: 50px;
            text-align: right;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--dark);
        }

        /* Platform Pie */
        .platform-pie {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
        }

        .pie-chart {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            position: relative;
        }

        .pie-legend {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .pie-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .pie-legend-color {
            width: 12px;
            height: 12px;
            border-radius: 4px;
        }

        .pie-legend-color.facebook {
            background: #1877F2;
        }

        .pie-legend-color.instagram {
            background: #E4405F;
        }

        /* Top Performers Table */
        .performers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .performers-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray);
            border-bottom: 2px solid var(--border);
            text-transform: uppercase;
        }

        .performers-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
        }

        .performers-table tr:last-child td {
            border-bottom: none;
        }

        .rank-badge {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .rank-badge.gold {
            background: linear-gradient(135deg, #FFD700, #FFA500);
        }

        .rank-badge.silver {
            background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
        }

        .rank-badge.bronze {
            background: linear-gradient(135deg, #CD7F32, #B87333);
        }

        @media (max-width: 768px) {
            .analytics-container {
                padding: 0 0.15rem;
            }

            .page-header {
                padding: 0.85rem !important;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .header-left {
                min-width: 0;
            }

            .breadcrumb-nav {
                flex-wrap: wrap;
                row-gap: 0.35rem;
            }

            .page-title {
                font-size: 1.25rem;
                line-height: 1.2;
            }

            .page-subtitle {
                font-size: 0.82rem;
            }

            .header-right {
                width: 100%;
            }

            .filter-select {
                width: 100%;
                min-height: 44px;
            }

            .kpi-grid {
                grid-template-columns: 1fr;
                gap: 0.6rem;
                margin-bottom: 0.8rem;
            }

            .kpi-card {
                padding: 0.7rem 0.8rem;
                gap: 0.65rem;
                border-radius: 10px;
                min-height: 70px;
            }

            .kpi-icon {
                width: 34px;
                height: 34px;
                border-radius: 9px;
                font-size: 0.95rem;
            }

            .kpi-value {
                font-size: 1.35rem;
                line-height: 1;
            }

            .kpi-label {
                font-size: 0.74rem;
                margin-bottom: 0;
                line-height: 1.2;
            }

            .charts-row {
                grid-template-columns: 1fr;
                gap: 0.7rem;
                margin-bottom: 0.7rem;
            }

            .chart-card {
                padding: 0.85rem;
                border-radius: 10px;
            }

            .chart-header {
                margin-bottom: 0.8rem;
            }

            .chart-title {
                font-size: 0.88rem;
            }

            .status-bar-item {
                gap: 0.45rem;
            }

            .status-bar-label {
                width: 60px;
                font-size: 0.75rem;
            }

            .status-bar-value {
                width: 28px;
                font-size: 0.76rem;
            }

            .platform-pie {
                gap: 0.9rem;
                flex-direction: column;
                align-items: flex-start;
            }

            .pie-chart {
                width: 88px;
                height: 88px;
                align-self: center;
            }

            .pie-legend {
                width: 100%;
                gap: 0.45rem;
            }

            .pie-legend-item {
                font-size: 0.74rem;
            }

            .performers-scroll {
                overflow-x: hidden;
                -webkit-overflow-scrolling: touch;
            }

            .performers-table {
                min-width: 100%;
                table-layout: fixed;
            }

            .performers-table th,
            .performers-table td {
                padding: 0.55rem 0.4rem;
                font-size: 0.7rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .performers-table th:nth-child(4),
            .performers-table td:nth-child(4) {
                display: none;
            }

            .performers-table th:nth-child(2),
            .performers-table td:nth-child(2) {
                width: 34%;
            }

            .kpi-icon.primary {
                background: rgba(99, 102, 241, 0.18);
                color: #818cf8;
            }

            .kpi-icon.success {
                background: rgba(16, 185, 129, 0.18);
                color: #34d399;
            }

            .kpi-icon.info {
                background: rgba(59, 130, 246, 0.18);
                color: #60a5fa;
            }

            .kpi-icon.warning {
                background: rgba(245, 158, 11, 0.18);
                color: #fbbf24;
            }

            .kpi-icon.danger {
                background: rgba(239, 68, 68, 0.18);
                color: #f87171;
            }
        }

        @media (min-width: 430px) and (max-width: 768px) {
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        [data-theme="dark"] .analytics-container {
            --dark: #e2e8f0;
            --gray: #94a3b8;
            --border: rgba(148, 163, 184, 0.28);
            --light-gray: #0f172a;
            --shadow: rgba(2, 6, 23, 0.45);
        }

        [data-theme="dark"] .kpi-card,
        [data-theme="dark"] .chart-card {
            background: #1b263b;
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: none;
        }

        [data-theme="dark"] .status-bar-track {
            background: #0f172a;
        }

        [data-theme="dark"] .performers-table th,
        [data-theme="dark"] .performers-table td {
            border-color: rgba(148, 163, 184, 0.28);
        }

        [data-theme="dark"] .filter-select {
            background: #0f172a;
            border: 2px solid rgba(148, 163, 184, 0.28);
            color: #e2e8f0;
            border-radius: 10px;
            padding: 0.6rem 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="analytics-container">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('leads.index') }}" class="breadcrumb-link">Leads Inbox</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Analytics</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-graph-up"></i>
                        Lead Analytics
                    </h1>
                    <p class="page-subtitle">Performance metrics and insights</p>
                </div>
                <div class="header-right">
                    <select class="filter-select" id="dateRange" onchange="window.location.href='?range='+this.value">
                        <option value="7" {{ $dateRange == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $dateRange == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $dateRange == 90 ? 'selected' : '' }}>Last 90 days</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon primary">
                    <i class="bi bi-people"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ number_format($kpis['total_leads']) }}</div>
                    <div class="kpi-label">Total Leads</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon success">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ $kpis['new_today'] }}</div>
                    <div class="kpi-label">New Today</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon info">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ $kpis['avg_response_time'] }}</div>
                    <div class="kpi-label">Avg Response Time</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon warning">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ $kpis['conversion_rate'] }}%</div>
                    <div class="kpi-label">Conversion Rate</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon danger">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ $kpis['sla_compliance'] }}%</div>
                    <div class="kpi-label">SLA Compliance</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <!-- Leads by Status -->
            <div class="chart-card">
                <div class="chart-header">
                    <span class="chart-title">
                        <i class="bi bi-bar-chart"></i> Leads by Status
                    </span>
                </div>
                <div class="status-bars">
                    @php
                        $total = array_sum($byStatus) ?: 1;
                        $statusColors = ['new' => 'primary', 'in_process' => 'info', 'completed' => 'success', 'lost' => 'secondary'];
                    @endphp
                    @foreach(['new', 'in_process', 'completed', 'lost'] as $status)
                        <div class="status-bar-item">
                            <span class="status-bar-label">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            <div class="status-bar-track">
                                <div class="status-bar-fill {{ $statusColors[$status] }}"
                                    style="width: {{ ($byStatus[$status] ?? 0) / $total * 100 }}%"></div>
                            </div>
                            <span class="status-bar-value">{{ $byStatus[$status] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Platform Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <span class="chart-title">
                        <i class="bi bi-pie-chart"></i> Platform Distribution
                    </span>
                </div>
                <div class="platform-pie">
                    @php
                        $platformTotal = array_sum($byPlatform) ?: 1;
                        $fbPercent = round(($byPlatform['facebook'] ?? 0) / $platformTotal * 100);
                        $igPercent = 100 - $fbPercent;
                    @endphp
                    <div class="pie-chart"
                        style="background: conic-gradient(#1877F2 0% {{ $fbPercent }}%, #E4405F {{ $fbPercent }}% 100%);">
                    </div>
                    <div class="pie-legend">
                        <div class="pie-legend-item">
                            <span class="pie-legend-color facebook"></span>
                            <span>Facebook ({{ $byPlatform['facebook'] ?? 0 }})</span>
                        </div>
                        <div class="pie-legend-item">
                            <span class="pie-legend-color instagram"></span>
                            <span>Instagram ({{ $byPlatform['instagram'] ?? 0 }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="chart-card">
            <div class="chart-header">
                <span class="chart-title">
                    <i class="bi bi-trophy"></i> Top Performers
                </span>
            </div>
            <div class="performers-scroll">
                <table class="performers-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Agent</th>
                            <th>Leads</th>
                            <th>Completed</th>
                            <th>Conversion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topAgents as $index => $agent)
                            <tr>
                                <td>
                                    @if($index === 0)
                                        <span class="rank-badge gold">🥇</span>
                                    @elseif($index === 1)
                                        <span class="rank-badge silver">🥈</span>
                                    @elseif($index === 2)
                                        <span class="rank-badge bronze">🥉</span>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td><strong>{{ $agent->name }}</strong></td>
                                <td>{{ $agent->lead_count }}</td>
                                <td>{{ $agent->completed_count }}</td>
                                <td>
                                    {{ $agent->lead_count > 0 ? round($agent->completed_count / $agent->lead_count * 100) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--gray);">
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
