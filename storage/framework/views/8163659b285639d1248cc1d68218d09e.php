

<?php $__env->startSection('title', 'Purchase Tracker'); ?>

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
                        <span class="breadcrumb-current">Purchase Tracker</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-cart-check"></i>
                        Diamond Purchases
                    </h1>
                    <p class="page-subtitle">Track all your diamond purchases</p>
                </div>
                <div class="header-right">
                    <a href="<?php echo e(route('purchases.create')); ?>" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Purchase</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-cart"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Purchases</div>
                    <div class="stat-value"><?php echo e($totalPurchases); ?></div>
                </div>
            </div>
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Amount</div>
                    <div class="stat-value">₹<?php echo e(number_format($totalAmount, 0)); ?></div>
                </div>
            </div>
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="bi bi-calendar-month"></i></div>
                <div class="stat-content">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">₹<?php echo e(number_format($thisMonthAmount, 0)); ?></div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="tracker-filter">
            <form method="GET" action="<?php echo e(route('purchases.index')); ?>" class="tracker-filter-form">
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-calendar"></i> From</label>
                    <input type="date" name="from_date" class="tracker-filter-input" value="<?php echo e(request('from_date')); ?>">
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-calendar"></i> To</label>
                    <input type="date" name="to_date" class="tracker-filter-input" value="<?php echo e(request('to_date')); ?>">
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-gem"></i> Diamond Type</label>
                    <input type="text" name="diamond_type" class="tracker-filter-input" placeholder="Search..."
                        value="<?php echo e(request('diamond_type')); ?>">
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-credit-card"></i> Payment</label>
                    <select name="payment_mode" class="tracker-filter-select">
                        <option value="">All</option>
                        <option value="upi" <?php echo e(request('payment_mode') == 'upi' ? 'selected' : ''); ?>>UPI</option>
                        <option value="cash" <?php echo e(request('payment_mode') == 'cash' ? 'selected' : ''); ?>>Cash</option>
                    </select>
                </div>
                <div class="tracker-filter-actions">
                    <span class="tracker-result-count">
                        <i class="bi bi-info-circle"></i>
                        <strong><?php echo e($purchases->count()); ?></strong> items
                    </span>
                    <a href="<?php echo e(route('purchases.index')); ?>" class="btn-tracker-reset">
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
                            <th><i class="bi bi-gem"></i> Diamond Type</th>
                            <th>Weight</th>
                            <th>₹ Per CT</th>
                            <th>% Discount</th>
                            <th>₹ Total</th>
                            <th><i class="bi bi-credit-card"></i> Payment</th>
                            <th><i class="bi bi-person"></i> Party</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purchase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($purchase->purchase_date->format('d-M-Y')); ?></td>
                                <td><span class="tracker-badge tracker-badge-primary"><?php echo e($purchase->diamond_type); ?></span></td>
                                <td><?php echo e(number_format($purchase->weight, 2)); ?> ct</td>
                                <td>₹<?php echo e(number_format($purchase->per_ct_price, 0)); ?></td>
                                <td><?php echo e($purchase->discount_percent); ?>%</td>
                                <td><strong style="color: #10b981;">₹<?php echo e(number_format($purchase->total_price, 0)); ?></strong>
                                </td>
                                <td>
                                    <span
                                        class="tracker-badge <?php echo e($purchase->payment_mode == 'upi' ? 'tracker-badge-info' : 'tracker-badge-secondary'); ?>">
                                        <?php echo e(strtoupper($purchase->payment_mode)); ?>

                                    </span>
                                </td>
                                <td><?php echo e($purchase->party_name); ?></td>
                                <td>
                                    <div class="tracker-actions">
                                        <a href="<?php echo e(route('purchases.show', $purchase)); ?>"
                                            class="tracker-action-btn tracker-action-view" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('purchases.edit', $purchase)); ?>"
                                            class="tracker-action-btn tracker-action-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="<?php echo e(route('purchases.destroy', $purchase)); ?>" method="POST"
                                            style="display:inline" onsubmit="return confirm('Delete this purchase?')">
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
                                <td colspan="9">
                                    <div class="tracker-empty">
                                        <div class="tracker-empty-icon"><i class="bi bi-inbox"></i></div>
                                        <h3 class="tracker-empty-title">No purchases found</h3>
                                        <p class="tracker-empty-desc">Start by adding your first purchase</p>
                                        <a href="<?php echo e(route('purchases.create')); ?>" class="btn-primary-custom">
                                            <i class="bi bi-plus-circle"></i> Add Purchase
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($purchases->hasPages()): ?>
            <div class="pagination-container">
                <?php echo e($purchases->links('pagination::bootstrap-5')); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/purchases/index.blade.php ENDPATH**/ ?>