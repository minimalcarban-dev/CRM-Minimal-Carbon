<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'Admin'); ?> - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1e293b;
            --gray: #64748b;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
        }
        body, html { height: 100%; }
        .auth-center { min-height: 100vh; display:flex; align-items:center; justify-content:center; }
        .alert-card { background: #fff; border-radius: 16px; padding: 1rem 1.25rem; display:flex; gap:1rem; margin-bottom:1rem; box-shadow:0 1px 3px var(--shadow); border:2px solid var(--border); }
        .alert-card.success { background: linear-gradient(135deg, rgba(16,185,129,.05), rgba(5,150,105,.05)); border-color: rgba(16,185,129,.2); }
        .alert-card.danger { background: linear-gradient(135deg, rgba(239,68,68,.05), rgba(220,38,38,.05)); border-color: rgba(239,68,68,.2); }
        .alert-card .alert-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; }
        .alert-card.success .alert-icon { background: linear-gradient(135deg, var(--success), #059669); }
        .alert-card.danger .alert-icon { background: linear-gradient(135deg, var(--danger), #dc2626); }
        .alert-title { font-weight:700; color:var(--dark); margin:0 0 0.25rem 0; }
        .alert-message { margin:0; color:var(--gray); }
        .alert-card { transition: opacity .4s ease, transform .4s ease; opacity:1; }
        .alert-card.alert-hide { opacity:0; transform: translateY(-6px); pointer-events:none; }
    </style>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>

<body>
    <div class="container auth-center">
        <div class="w-100" style="max-width:720px;">
            <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide unified flash alerts after ~4.5s (login/auth pages)
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert-card');
            alerts.forEach(function (el) {
                setTimeout(function () {
                    el.classList.add('alert-hide');
                    setTimeout(function () { try { el.remove(); } catch (e) {} }, 600);
                }, 4500);
            });
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/layouts/auth.blade.php ENDPATH**/ ?>