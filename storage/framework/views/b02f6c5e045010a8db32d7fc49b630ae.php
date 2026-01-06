

<?php $__env->startSection('title', 'Add Transaction'); ?>

<?php $__env->startSection('content'); ?>
<div class="diamond-management-container tracker-page">
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="breadcrumb-nav">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="breadcrumb-link">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <a href="<?php echo e(route('expenses.index')); ?>" class="breadcrumb-link">Expenses</a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Add New</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-plus-circle"></i>
                    Add Transaction
                </h1>
            </div>
            <div class="header-right">
                <a href="<?php echo e(route('expenses.index')); ?>" class="btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('expenses.store')); ?>" method="POST" id="expenseForm">
        <?php echo csrf_field(); ?>

        <!-- Transaction Type -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-arrow-down-up"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Transaction Type</h5>
                        <p class="section-description">Select if money is coming in or going out</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="transaction-type-toggle">
                    <label class="type-option type-in">
                        <input type="radio" name="transaction_type" value="in" <?php echo e(old('transaction_type', 'in') == 'in' ? 'checked' : ''); ?>>
                        <span class="type-btn">
                            <i class="bi bi-arrow-down-circle"></i>
                            <strong>Money In</strong>
                            <small>Income / Payment Received</small>
                        </span>
                    </label>
                    <label class="type-option type-out">
                        <input type="radio" name="transaction_type" value="out" <?php echo e(old('transaction_type') == 'out' ? 'checked' : ''); ?>>
                        <span class="type-btn">
                            <i class="bi bi-arrow-up-circle"></i>
                            <strong>Money Out</strong>
                            <small>Expense / Payment Made</small>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="form-section-card">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon"><i class="bi bi-card-text"></i></div>
                    <div class="section-text">
                        <h5 class="section-title">Transaction Details</h5>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="date" class="form-label">Date <span class="required">*</span></label>
                        <input type="date" id="date" name="date" class="form-control <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('date', date('Y-m-d'))); ?>" required>
                        <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label for="title" class="form-label">Title / Purpose <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('title')); ?>" placeholder="e.g., Electricity Bill, Customer Payment" required>
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label for="amount" class="form-label">Amount (₹) <span class="required">*</span></label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                            class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('amount')); ?>" placeholder="0.00" required
                            style="font-size: 1.125rem; font-weight: 600;">
                        <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Category <span class="required">*</span></label>
                        <select id="category" name="category" class="form-control <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <option value="">Select Category</option>
                            <optgroup label="Income" id="incomeCategories">
                                <?php $__currentLoopData = $incomeCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(old('category') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </optgroup>
                            <optgroup label="Expense" id="expenseCategories" style="display:none;">
                                <?php $__currentLoopData = $expenseCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(old('category') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </optgroup>
                        </select>
                        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label for="payment_method" class="form-label">Payment Method <span class="required">*</span></label>
                        <select id="payment_method" name="payment_method" class="form-control <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(old('payment_method', 'cash') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label for="paid_to_received_from" class="form-label">Paid To / Received From</label>
                        <input type="text" id="paid_to_received_from" name="paid_to_received_from" class="form-control"
                            value="<?php echo e(old('paid_to_received_from')); ?>" placeholder="Person or Company name">
                    </div>

                    <div class="form-group">
                        <label for="reference_number" class="form-label">Reference / Receipt No.</label>
                        <input type="text" id="reference_number" name="reference_number" class="form-control"
                            value="<?php echo e(old('reference_number')); ?>" placeholder="Optional">
                    </div>

                    <div class="form-group form-group-full">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Additional details..."><?php echo e(old('notes')); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
            <a href="<?php echo e(route('expenses.index')); ?>" class="btn-secondary-custom">
                <i class="bi bi-x-lg"></i> Cancel
            </a>
            <button type="submit" class="btn-primary-custom">
                <i class="bi bi-check-lg"></i> Save Transaction
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="transaction_type"]');
    const incomeGroup = document.getElementById('incomeCategories');
    const expenseGroup = document.getElementById('expenseCategories');
    const categorySelect = document.getElementById('category');

    function updateCategories() {
        const selected = document.querySelector('input[name="transaction_type"]:checked');
        if (selected && selected.value === 'in') {
            incomeGroup.style.display = '';
            expenseGroup.style.display = 'none';
            const currentVal = categorySelect.value;
            if (currentVal && (currentVal.includes('_out') || currentVal.includes('bill') || currentVal.includes('expense'))) {
                categorySelect.value = '';
            }
        } else {
            incomeGroup.style.display = 'none';
            expenseGroup.style.display = '';
            const currentVal = categorySelect.value;
            if (currentVal && (currentVal.includes('_in') || currentVal.includes('income'))) {
                categorySelect.value = '';
            }
        }
    }

    typeRadios.forEach(r => r.addEventListener('change', updateCategories));
    updateCategories();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/expenses/create.blade.php ENDPATH**/ ?>