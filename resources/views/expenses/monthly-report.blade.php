@extends('layouts.admin')

@section('title', 'Monthly Report')

@section('content')
    <div class="diamond-management-container tracker-page expense-page">
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
                <div class="header-right tracker-report-header-right">
                    <form method="GET" action="{{ route('expenses.monthly-report') }}" class="tracker-report-filter-form"
                        style="flex-wrap:nowrap; align-items:center;">
                        <select name="month" class="tracker-filter-select" style="padding:0.5rem 0.75rem;">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="tracker-filter-select" style="padding:0.5rem 0.75rem;">
                            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endfor
                        </select>
                        <button type="submit" class="btn-primary-custom" style="padding:0.5rem 0.75rem; flex-shrink:0;"><i
                                class="bi bi-search"></i></button>
                        <a href="{{ route('expenses.export-monthly', ['year' => $year, 'month' => $month]) }}"
                            class="btn-secondary-custom" style="padding:0.5rem 1rem; flex-shrink:0;">
                            <i class="bi bi-download"></i> Excel
                        </a>
                        <a href="{{ route('expenses.index') }}" class="btn-secondary-custom"
                            style="padding:0.5rem 1rem; flex-shrink:0;">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid stats-grid-compact">
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Cash In</div>
                    <div class="stat-value" style="color:#10b981;">₹{{ number_format($totalIncome, 0) }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-danger">
                <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Cash Out</div>
                    <div class="stat-value" style="color:#ef4444;">₹{{ number_format($totalExpense, 0) }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-wallet"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Cash Balance</div>
                    <div class="stat-value" style="color:{{ $balance >= 0 ? '#10b981' : '#ef4444' }};">
                        ₹{{ number_format($balance, 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="tracker-charts-row tracker-pie-charts-row">
            <div class="tracker-chart-card">
                <h5 class="tracker-chart-title"><i class="bi bi-pie-chart"></i> Income by Category</h5>
                <div class="tracker-report-chart-layout">
                    <div class="tracker-report-chart-canvas"><canvas id="incomeChart"></canvas></div>
                    <div id="incomeLegend" class="tracker-report-legend"></div>
                </div>
            </div>
            <div class="tracker-chart-card">
                <h5 class="tracker-chart-title"><i class="bi bi-pie-chart"></i> Expense by Category</h5>
                <div class="tracker-report-chart-layout">
                    <div class="tracker-report-chart-canvas"><canvas id="expenseChart"></canvas></div>
                    <div id="expenseLegend" class="tracker-report-legend tracker-report-legend-scroll"></div>
                </div>
            </div>
        </div>

        <!-- Comparison Bar Chart + Breakdown Tables -->
        <div class="tracker-table-card tracker-report-comparison" style="padding: 1.25rem; margin-top: 1.5rem;">
            <h3 style="margin: 0 0 1.25rem; font-size: 1rem; color: #1e293b;">
                <i class="bi bi-bar-chart" style="color: #6366f1;"></i> Income vs Expense
            </h3>
            <div class="tracker-chart-container" style="padding: 0;">
                <canvas id="comparisonChart"></canvas>
            </div>

            <!-- Breakdown Tables -->
            <div class="tracker-tables-row" style="margin-top: 1.25rem;">
                <div class="tracker-table-card" style="padding: 1rem; box-shadow: none; border: 1px solid #f1f5f9;">
                    <h3
                        style="margin: 0 0 1rem; font-size: 0.95rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                        <div
                            style="background: linear-gradient(135deg, #10b981, #059669); width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink:0;">
                            <i class="bi bi-arrow-down"></i>
                        </div>
                        Cash In Breakdown
                    </h3>
                    <div class="table-responsive" style="padding: 0; margin-bottom: 0;">
                        <table class="tracker-table" style="margin-bottom: 0; min-width: 0;">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomeByCategory as $cat => $amount)
                                    <tr>
                                        <td>{{ \App\Models\Expense::INCOME_CATEGORIES[$cat] ?? (empty($cat) ? 'Uncategorized' : $cat) }}
                                        </td>
                                        <td style="color:#10b981; font-weight: 600;">₹{{ number_format($amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" style="text-align:center; padding:1.5rem; color:#64748b;">No
                                            income
                                            this month
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tracker-table-card" style="padding: 1rem; box-shadow: none; border: 1px solid #f1f5f9;">
                    <h3
                        style="margin: 0 0 1rem; font-size: 0.95rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                        <div
                            style="background: linear-gradient(135deg, #ef4444, #dc2626); width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink:0;">
                            <i class="bi bi-arrow-up"></i>
                        </div>
                        Cash Out Breakdown
                    </h3>
                    <div class="table-responsive" style="padding: 0;">
                        <table class="tracker-table" style="min-width: 0;">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenseByCategory as $cat => $amount)
                                    <tr>
                                        <td>{{ \App\Models\Expense::EXPENSE_CATEGORIES[$cat] ?? (empty($cat) ? 'Uncategorized' : $cat) }}
                                        </td>
                                        <td style="color:#ef4444; font-weight: 600;">₹{{ number_format($amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" style="text-align:center; padding:1.5rem; color:#64748b;">No
                                            expenses
                                            this month</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" integrity="sha384-..."
            crossorigin="anonymous"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const incomeData = @json($incomeByCategory);
                const expenseData = @json($expenseByCategory);
                const incomeLabels = @json(\App\Models\Expense::INCOME_CATEGORIES);
                const expenseLabels = @json(\App\Models\Expense::EXPENSE_CATEGORIES);

                const incomeColors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316',
                    '#6366f1'
                ];
                const expenseColors = ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e', '#14b8a6', '#0ea5e9',
                    '#8b5cf6', '#ec4899', '#f43f5e', '#64748b', '#6366f1', '#a855f7', '#d946ef', '#f472b6'
                ];

                // Generate custom HTML legend
                function generateLegend(data, labels, colors, containerId) {
                    const container = document.getElementById(containerId);
                    const keys = Object.keys(data);
                    const wrapper = document.createElement('div');
                    wrapper.style.cssText = 'display:flex; flex-direction:column; gap:0.5rem;';
                    keys.forEach((key, index) => {
                        const label = labels[key] || key;
                        const color = colors[index % colors.length];
                        const amount = new Intl.NumberFormat('en-IN').format(data[key]);
                        const row = document.createElement('div');
                        row.style.cssText = 'display:flex; align-items:center; gap:0.5rem;';
                        const colorBox = document.createElement('span');
                        colorBox.style.cssText =
                            `width:12px; height:12px; border-radius:3px; background:${color}; flex-shrink:0;`;
                        const labelSpan = document.createElement('span');
                        labelSpan.style.cssText = 'color:#1e293b; font-weight:500;';
                        labelSpan.textContent = label;
                        const amountSpan = document.createElement('span');
                        amountSpan.style.cssText = 'color:#64748b; margin-left:auto;';
                        amountSpan.textContent = `₹${amount}`;
                        row.append(colorBox, labelSpan, amountSpan);
                        wrapper.appendChild(row);
                    });
                    container.innerHTML = '';
                    container.appendChild(wrapper);
                }

                if (Object.keys(incomeData).length > 0) {
                    new Chart(document.getElementById('incomeChart'), {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(incomeData).map(k => incomeLabels[k] || k),
                            datasets: [{
                                data: Object.values(incomeData),
                                backgroundColor: incomeColors,
                                borderWidth: 3,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            cutout: '65%'
                        }
                    });
                    generateLegend(incomeData, incomeLabels, incomeColors, 'incomeLegend');
                } else {
                    document.getElementById('incomeLegend').innerHTML =
                        '<span style="color:#64748b;">No income this month</span>';
                }

                if (Object.keys(expenseData).length > 0) {
                    new Chart(document.getElementById('expenseChart'), {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(expenseData).map(k => expenseLabels[k] || k),
                            datasets: [{
                                data: Object.values(expenseData),
                                backgroundColor: expenseColors,
                                borderWidth: 3,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            cutout: '65%'
                        }
                    });
                    generateLegend(expenseData, expenseLabels, expenseColors, 'expenseLegend');
                } else {
                    document.getElementById('expenseLegend').innerHTML =
                        '<span style="color:#64748b;">No expenses this month</span>';
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
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endsection
