

<?php $__env->startSection('title', 'Office Expenses'); ?>

<?php $__env->startSection('content'); ?>
    <div class="diamond-management-container tracker-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Office Expenses</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-wallet2"></i>
                        Office Expense Manager
                    </h1>
                    <p class="page-subtitle">Track income and expenses</p>
                </div>
                <div class="header-right">
                    <div class="tracker-actions-stack">
                        <a href="<?php echo e(route('expenses.create')); ?>" class="btn-primary-custom">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add Transaction</span>
                        </a>
                        <div class="tracker-actions-row">
                            <a href="<?php echo e(route('expenses.monthly-report')); ?>" class="btn-secondary-custom">
                                <i class="bi bi-calendar-month"></i> Monthly
                            </a>
                            <a href="<?php echo e(route('expenses.annual-report')); ?>" class="btn-secondary-custom">
                                <i class="bi bi-calendar3"></i> Annual
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">This Month In</div>
                    <div class="stat-value" style="color: #10b981;">₹<?php echo e(number_format($monthlyIncome, 0)); ?></div>
                </div>
            </div>
            <div class="stat-card stat-card-danger">
                <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-label">This Month Out</div>
                    <div class="stat-value" style="color: #ef4444;">₹<?php echo e(number_format($monthlyExpense, 0)); ?></div>
                </div>
            </div>
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-wallet"></i></div>
                <div class="stat-content">
                    <div class="stat-label">This Month Balance</div>
                    <div class="stat-value" style="color: <?php echo e($monthlyBalance >= 0 ? '#10b981' : '#ef4444'); ?>;">
                        ₹<?php echo e(number_format($monthlyBalance, 0)); ?>

                    </div>
                </div>
            </div>
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="bi bi-bank"></i></div>
                <div class="stat-content">
                    <div class="stat-label">All Time Balance</div>
                    <div class="stat-value" style="color: <?php echo e($totalBalance >= 0 ? '#10b981' : '#ef4444'); ?>;">
                        ₹<?php echo e(number_format($totalBalance, 0)); ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="tracker-filter">
            <form method="GET" action="<?php echo e(route('expenses.index')); ?>" class="tracker-filter-form">
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-calendar"></i> From</label>
                    <input type="date" name="from_date" class="tracker-filter-input" value="<?php echo e(request('from_date')); ?>">
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-calendar"></i> To</label>
                    <input type="date" name="to_date" class="tracker-filter-input" value="<?php echo e(request('to_date')); ?>">
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-arrow-down-up"></i> Type</label>
                    <select name="transaction_type" class="tracker-filter-select">
                        <option value="">All</option>
                        <option value="in" <?php echo e(request('transaction_type') == 'in' ? 'selected' : ''); ?>>Money In</option>
                        <option value="out" <?php echo e(request('transaction_type') == 'out' ? 'selected' : ''); ?>>Money Out</option>
                    </select>
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-search"></i> Search</label>
                    <input type="text" name="search" class="tracker-filter-input" placeholder="Search title..."
                        value="<?php echo e(request('search')); ?>">
                </div>
                <div class="tracker-filter-actions">
                    <span class="tracker-result-count">
                        <i class="bi bi-info-circle"></i>
                        <strong><?php echo e($expenses->count()); ?></strong> items
                    </span>
                    <a href="<?php echo e(route('expenses.index')); ?>" class="btn-tracker-reset">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </a>
                    <button type="submit" class="btn-tracker-apply">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="tracker-table-card">
            <div class="table-responsive">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-calendar"></i> Date</th>
                            <th><i class="bi bi-card-text"></i> Title</th>
                            <th><i class="bi bi-arrow-down"></i> In</th>
                            <th><i class="bi bi-arrow-up"></i> Out</th>
                            <th><i class="bi bi-tag"></i> Category</th>
                            <th><i class="bi bi-credit-card"></i> Payment</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($expense->transaction_type == 'in' ? 'tracker-income-row' : 'tracker-expense-row'); ?>">
                                <td><?php echo e($expense->date->format('d-M-Y')); ?></td>
                                <td>
                                    <strong><?php echo e($expense->title); ?></strong>
                                    <?php if($expense->paid_to_received_from): ?>
                                        <br><small style="color: #64748b;"><?php echo e($expense->paid_to_received_from); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($expense->transaction_type == 'in'): ?>
                                        <span class="tracker-amount-in">₹<?php echo e(number_format($expense->amount, 0)); ?></span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($expense->transaction_type == 'out'): ?>
                                        <span class="tracker-amount-out">₹<?php echo e(number_format($expense->amount, 0)); ?></span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><span class="tracker-badge tracker-badge-secondary"><?php echo e($expense->category_name); ?></span>
                                </td>
                                <td><span
                                        class="tracker-badge tracker-badge-info"><?php echo e(\App\Models\Expense::PAYMENT_METHODS[$expense->payment_method] ?? $expense->payment_method); ?></span>
                                </td>
                                <td>
                                    <div class="tracker-actions">
                                        <a href="<?php echo e(route('expenses.show', $expense)); ?>"
                                            class="tracker-action-btn tracker-action-view" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('expenses.edit', $expense)); ?>"
                                            class="tracker-action-btn tracker-action-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="<?php echo e(route('expenses.destroy', $expense)); ?>" method="POST"
                                            style="display:inline" onsubmit="return confirm('Delete this transaction?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="tracker-action-btn tracker-action-delete"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="tracker-empty">
                                        <div class="tracker-empty-icon"><i class="bi bi-inbox"></i></div>
                                        <h3 class="tracker-empty-title">No transactions found</h3>
                                        <p class="tracker-empty-desc">Start by adding your first transaction</p>
                                        <a href="<?php echo e(route('expenses.create')); ?>" class="btn-primary-custom">
                                            <i class="bi bi-plus-circle"></i> Add Transaction
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($expenses->hasPages()): ?>
            <div class="pagination-container">
                <?php echo e($expenses->links('pagination::bootstrap-5')); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/expenses/index.blade.php ENDPATH**/ ?>