

<?php $__env->startSection('title', 'Annual Report'); ?>

<?php $__env->startSection('content'); ?>
    <div class="diamond-management-container tracker-page">
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="breadcrumb-link"><i class="bi bi-house-door"></i>
                            Dashboard</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="<?php echo e(route('expenses.index')); ?>" class="breadcrumb-link">Expenses</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Annual Report</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-calendar3"></i>
                        Annual Report - <?php echo e($year); ?>

                    </h1>
                </div>
                <div class="header-right" style="display:flex; align-items:center; gap:0.5rem; flex-wrap:nowrap;">
                    <form method="GET" action="<?php echo e(route('expenses.annual-report')); ?>"
                        style="display:flex; gap:0.5rem; align-items:center;">
                        <select name="year" class="tracker-filter-select" style="width:auto; padding:0.5rem 0.75rem;">
                            <?php for($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="btn-primary-custom" style="padding:0.5rem 0.75rem;"><i
                                class="bi bi-search"></i></button>
                    </form>
                    <a href="<?php echo e(route('expenses.export-annual', ['year' => $year])); ?>" class="btn-secondary-custom"
                        style="padding:0.5rem 1rem;">
                        <i class="bi bi-download"></i> Excel
                    </a>
                    <a href="<?php echo e(route('expenses.index')); ?>" class="btn-secondary-custom" style="padding:0.5rem 1rem;">
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
                    <div class="stat-value" style="color:#10b981;">₹<?php echo e(number_format($totals['income'], 0)); ?></div>
                </div>
            </div>
            <div class="stat-card stat-card-danger">
                <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Expense</div>
                    <div class="stat-value" style="color:#ef4444;">₹<?php echo e(number_format($totals['expense'], 0)); ?></div>
                </div>
            </div>
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-wallet"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Net Cash Flow</div>
                    <div class="stat-value" style="color:<?php echo e($totals['cashflow'] >= 0 ? '#10b981' : '#ef4444'); ?>;">
                        ₹<?php echo e(number_format($totals['cashflow'], 0)); ?></div>
                </div>
            </div>
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="bi bi-calculator"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Monthly Average</div>
                    <div class="stat-value">₹<?php echo e(number_format($averages['cashflow'], 0)); ?></div>
                </div>
            </div>
        </div>

        <!-- Monthly Charts -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-bar-chart-line"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Monthly Overview</h5>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <canvas id="monthlyChart" height="80"></canvas>
            </div>
        </div>

        <!-- Cash Flow Chart -->
        <div class="form-section-card" style="margin-top: 1.5rem;">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-graph-up"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Cash Flow Trend</h5>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <canvas id="cashflowChart" height="60"></canvas>
            </div>
        </div>

        <!-- Monthly Data Table -->
        <div class="form-section-card" style="margin-top: 1.5rem;">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-table"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Monthly Breakdown</h5>
                    </div>
                </div>
            </div>
            <div class="section-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="tracker-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <?php $__currentLoopData = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th style="text-align: center;"><?php echo e($m); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <th style="text-align: right;">Total</th>
                                <th style="text-align: right;">Avg</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong style="color:#10b981;">Income</strong></td>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <td style="text-align: center;">₹<?php echo e(number_format($monthlyData[$m]['income'] / 1000, 0)); ?>k
                                    </td>
                                <?php endfor; ?>
                                <td style="text-align: right;"><strong
                                        style="color:#10b981;">₹<?php echo e(number_format($totals['income'], 0)); ?></strong></td>
                                <td style="text-align: right;">₹<?php echo e(number_format($averages['income'], 0)); ?></td>
                            </tr>
                            <tr>
                                <td><strong style="color:#ef4444;">Expense</strong></td>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <td style="text-align: center;">₹<?php echo e(number_format($monthlyData[$m]['expense'] / 1000, 0)); ?>k
                                    </td>
                                <?php endfor; ?>
                                <td style="text-align: right;"><strong
                                        style="color:#ef4444;">₹<?php echo e(number_format($totals['expense'], 0)); ?></strong></td>
                                <td style="text-align: right;">₹<?php echo e(number_format($averages['expense'], 0)); ?></td>
                            </tr>
                            <tr class="tracker-cashflow-row">
                                <td><strong>Cash Flow</strong></td>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <?php $cf = $monthlyData[$m]['cashflow']; ?>
                                    <td style="text-align: center; color:<?php echo e($cf >= 0 ? '#10b981' : '#ef4444'); ?>;">
                                        ₹<?php echo e(number_format($cf / 1000, 0)); ?>k</td>
                                <?php endfor; ?>
                                <td
                                    style="text-align: right; color:<?php echo e($totals['cashflow'] >= 0 ? '#10b981' : '#ef4444'); ?>;">
                                    <strong>₹<?php echo e(number_format($totals['cashflow'], 0)); ?></strong>
                                </td>
                                <td style="text-align: right;">₹<?php echo e(number_format($averages['cashflow'], 0)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const monthlyData = <?php echo json_encode($monthlyData, 15, 512) ?>;
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            const incomeData = [];
            const expenseData = [];
            const cashflowData = [];

            for (let m = 1; m <= 12; m++) {
                incomeData.push(monthlyData[m].income);
                expenseData.push(monthlyData[m].expense);
                cashflowData.push(monthlyData[m].cashflow);
            }

            // Monthly Overview - Slim bars with line overlay
            new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Income',
                            data: incomeData,
                            backgroundColor: 'rgba(99, 102, 241, 0.7)',
                            borderRadius: 4,
                            barThickness: 12,
                            order: 2
                        },
                        {
                            type: 'bar',
                            label: 'Expense',
                            data: expenseData,
                            backgroundColor: 'rgba(236, 72, 153, 0.5)',
                            borderRadius: 4,
                            barThickness: 12,
                            order: 3
                        },
                        {
                            type: 'line',
                            label: 'Income Trend',
                            data: incomeData,
                            borderColor: '#10b981',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#10b981',
                            pointBorderWidth: 2,
                            order: 1
                        },
                        {
                            type: 'line',
                            label: 'Expense Trend',
                            data: expenseData,
                            borderColor: '#ec4899',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#ec4899',
                            pointBorderWidth: 2,
                            order: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(236, 72, 153, 0.08)', drawBorder: false },
                            ticks: { callback: value => '₹' + (value / 1000) + 'k' }
                        },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { usePointStyle: true, boxWidth: 8, padding: 20 }
                        }
                    }
                }
            });

            // Cash Flow Trend - Smooth curved line with gradient
            const ctx = document.getElementById('cashflowChart').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
            gradient.addColorStop(1, 'rgba(99, 102, 241, 0.02)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Cash Flow',
                        data: cashflowData,
                        borderColor: '#6366f1',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#6366f1',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                            ticks: { callback: value => '₹' + (value / 1000) + 'k' }
                        },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { usePointStyle: true, boxWidth: 8, padding: 20 }
                        }
                    }
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/expenses/annual-report.blade.php ENDPATH**/ ?>