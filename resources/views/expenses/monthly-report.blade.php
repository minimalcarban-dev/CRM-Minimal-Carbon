@extends('layouts.admin')

@section('title', 'Monthly Report')

@section('content')
    <div class="diamond-management-container tracker-page">
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link"><i class="bi bi-house-door"></i>
                            Dashboard</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('expenses.index') }}" class="breadcrumb-link">Expenses</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Monthly Report</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-calendar-month"></i>
                        Monthly Report - {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
                    </h1>
                </div>
                <div class="header-right" style="display:flex; align-items:center; gap:0.5rem; flex-wrap:nowrap;">
                    <form method="GET" action="{{ route('expenses.monthly-report') }}"
                        style="display:flex; gap:0.5rem; align-items:center;">
                        <select name="month" class="tracker-filter-select" style="width:auto; padding:0.5rem 0.75rem;">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="tracker-filter-select" style="width:auto; padding:0.5rem 0.75rem;">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="btn-primary-custom" style="padding:0.5rem 0.75rem;"><i
                                class="bi bi-search"></i></button>
                    </form>
                    <a href="{{ route('expenses.export-monthly', ['year' => $year, 'month' => $month]) }}"
                        class="btn-secondary-custom" style="padding:0.5rem 1rem;">
                        <i class="bi bi-download"></i> Excel
                    </a>
                    <a href="{{ route('expenses.index') }}" class="btn-secondary-custom" style="padding:0.5rem 1rem;">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid stats-grid-compact">
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Income</div>
                    <div class="stat-value" style="color:#10b981;">₹{{ number_format($totalIncome, 0) }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-danger">
                <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Expense</div>
                    <div class="stat-value" style="color:#ef4444;">₹{{ number_format($totalExpense, 0) }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-wallet"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Balance</div>
                    <div class="stat-value" style="color:{{ $balance >= 0 ? '#10b981' : '#ef4444' }};">
                        ₹{{ number_format($balance, 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="tracker-charts-row">
            <div class="tracker-chart-card">
                <h5 class="tracker-chart-title"><i class="bi bi-pie-chart"></i> Income by Category</h5>
                <div style="display:flex; align-items:center; gap:1.5rem;">
                    <div style="flex:0 0 180px;"><canvas id="incomeChart"></canvas></div>
                    <div id="incomeLegend" style="flex:1; font-size:0.85rem;"></div>
                </div>
            </div>
            <div class="tracker-chart-card">
                <h5 class="tracker-chart-title"><i class="bi bi-pie-chart"></i> Expense by Category</h5>
                <div style="display:flex; align-items:center; gap:1.5rem;">
                    <div style="flex:0 0 180px;"><canvas id="expenseChart"></canvas></div>
                    <div id="expenseLegend" style="flex:1; font-size:0.85rem; max-height:200px; overflow-y:auto;"></div>
                </div>
            </div>
        </div>

        <!-- Comparison Bar Chart -->
        <div class="form-section-card" style="margin-top: 1.5rem;">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-bar-chart"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Income vs Expense</h5>
                    </div>
                </div>
            </div>
            <div class="section-body" style="padding: 1rem;">
                <canvas id="comparisonChart" height="50"></canvas>
            </div>
        </div>

        <!-- Breakdown Tables -->
        <div class="tracker-tables-row" style="margin-top: 1.5rem;">
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i
                                class="bi bi-arrow-down"></i></div>
                        <div class="section-text">
                            <h5 class="section-title">Income Breakdown</h5>
                        </div>
                    </div>
                </div>
                <div class="section-body" style="padding: 0;">
                    <table class="tracker-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomeByCategory as $cat => $amount)
                                <tr>
                                    <td>{{ \App\Models\Expense::INCOME_CATEGORIES[$cat] ?? $cat }}</td>
                                    <td style="color:#10b981; font-weight: 600;">₹{{ number_format($amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" style="text-align:center; padding:2rem; color:#64748b;">No income this month
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);"><i
                                class="bi bi-arrow-up"></i></div>
                        <div class="section-text">
                            <h5 class="section-title">Expense Breakdown</h5>
                        </div>
                    </div>
                </div>
                <div class="section-body" style="padding: 0;">
                    <table class="tracker-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenseByCategory as $cat => $amount)
                                <tr>
                                    <td>{{ \App\Models\Expense::EXPENSE_CATEGORIES[$cat] ?? $cat }}</td>
                                    <td style="color:#ef4444; font-weight: 600;">₹{{ number_format($amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" style="text-align:center; padding:2rem; color:#64748b;">No expenses this
                                        month</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const incomeData = @json($incomeByCategory);
            const expenseData = @json($expenseByCategory);
            const incomeLabels = @json(\App\Models\Expense::INCOME_CATEGORIES);
            const expenseLabels = @json(\App\Models\Expense::EXPENSE_CATEGORIES);

            const incomeColors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1'];
            const expenseColors = ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e', '#14b8a6', '#0ea5e9', '#8b5cf6', '#ec4899', '#f43f5e', '#64748b', '#6366f1', '#a855f7', '#d946ef', '#f472b6'];

            // Generate custom HTML legend
            function generateLegend(data, labels, colors, containerId) {
                const container = document.getElementById(containerId);
                const keys = Object.keys(data);
                let html = '<div style="display:flex; flex-direction:column; gap:0.5rem;">';
                keys.forEach((key, index) => {
                    const label = labels[key] || key;
                    const color = colors[index % colors.length];
                    const amount = new Intl.NumberFormat('en-IN').format(data[key]);
                    html += `<div style="display:flex; align-items:center; gap:0.5rem;">
                            <span style="width:12px; height:12px; border-radius:3px; background:${color}; flex-shrink:0;"></span>
                            <span style="color:#1e293b; font-weight:500;">${label}</span>
                            <span style="color:#64748b; margin-left:auto;">₹${amount}</span>
                        </div>`;
                });
                html += '</div>';
                container.innerHTML = html;
            }

            if (Object.keys(incomeData).length > 0) {
                new Chart(document.getElementById('incomeChart'), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(incomeData).map(k => incomeLabels[k] || k),
                        datasets: [{ data: Object.values(incomeData), backgroundColor: incomeColors, borderWidth: 3, borderColor: '#fff' }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        cutout: '65%'
                    }
                });
                generateLegend(incomeData, incomeLabels, incomeColors, 'incomeLegend');
            } else {
                document.getElementById('incomeLegend').innerHTML = '<span style="color:#64748b;">No income this month</span>';
            }

            if (Object.keys(expenseData).length > 0) {
                new Chart(document.getElementById('expenseChart'), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(expenseData).map(k => expenseLabels[k] || k),
                        datasets: [{ data: Object.values(expenseData), backgroundColor: expenseColors, borderWidth: 3, borderColor: '#fff' }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        cutout: '65%'
                    }
                });
                generateLegend(expenseData, expenseLabels, expenseColors, 'expenseLegend');
            } else {
                document.getElementById('expenseLegend').innerHTML = '<span style="color:#64748b;">No expenses this month</span>';
            }

            new Chart(document.getElementById('comparisonChart'), {
                type: 'bar',
                data: {
                    labels: ['Income', 'Expense'],
                    datasets: [{
                        label: 'Amount',
                        data: [{{ $totalIncome }}, {{ $totalExpense }}],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } }
                }
            });
        });
    </script>
@endsection