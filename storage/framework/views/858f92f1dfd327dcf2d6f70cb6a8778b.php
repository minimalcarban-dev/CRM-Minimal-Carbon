<?php
    $success = session('success');
    $error = session('error') ?? session('fail');
    $warning = session('warning');
?>

<?php if($success || $error || $warning || ($errors ?? null)?->any()): ?>
    <?php
        if ($success) {
            $type = 'success';
            $title = 'Success!';
            $icon = 'bi-check-circle-fill';
        } elseif ($warning) {
            $type = 'warning';
            $title = 'Warning!';
            $icon = 'bi-exclamation-triangle-fill';
        } else {
            $type = 'danger';
            $title = 'Error!';
            $icon = 'bi-x-circle-fill';
        }
    ?>
    <div class="alert-card <?php echo e($type); ?> mb-4">
        <div class="alert-icon">
            <i class="bi <?php echo e($icon); ?>"></i>
        </div>
        <div class="alert-content">
            <h5 class="alert-title"><?php echo e($title); ?></h5>
            <?php if($success): ?>
                <p class="alert-message"><?php echo $success; ?></p>
            <?php elseif($warning): ?>
                <div class="alert-message"><?php echo $warning; ?></div>
            <?php elseif($error): ?>
                <p class="alert-message"><?php echo e($error); ?></p>
            <?php elseif(($errors ?? null)?->any()): ?>
                <ul class="alert-message" style="margin:0; padding-left:1.25rem;">
                    <?php $__currentLoopData = ($errors ?? null)->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($message); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/partials/flash.blade.php ENDPATH**/ ?>