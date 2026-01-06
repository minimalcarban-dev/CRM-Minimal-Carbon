

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-title">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </h1>
                <p class="dashboard-subtitle">Welcome back, <?php echo e($currentAdmin->name ?? 'Admin'); ?>! Here's what's happening
                    today.</p>
            </div>
        </div>

        <!-- Quick Stats -->
        

        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-content">
                <div class="welcome-icon">
                    <i class="bi bi-house"></i>
                </div>
                <div>
                    <h2 class="welcome-title">Welcome to the Admin Dashboard</h2>
                    <p class="welcome-text">
                        You can only access the modules assigned to you.
                    </p>
                </div>
            </div>

            <?php if(session('error')): ?>
                <div class="warning-message">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span><strong>Access Denied:</strong> <?php echo session('error'); ?></span>
                </div>
            <?php endif; ?>

            <div class="welcome-actions">
                <?php if(
                        auth()->guard('admin')->user() && auth()->guard('admin')
                            ->user()->canAccessAny(['admins.view', 'admins.create'])
                    ): ?>
                    <a href="<?php echo e(route('admins.index')); ?>" class="btn-primary-modern">
                        <i class="bi bi-people"></i>
                        Manage Admins
                    </a>
                <?php endif; ?>

                <?php if(
                        auth()->guard('admin')->user() && auth()->guard('admin')
                            ->user()->canAccessAny(['permissions.view', 'permissions.create'])
                    ): ?>
                    <a href="<?php echo e(route('permissions.index')); ?>" class="btn-secondary-modern">
                        <i class="bi bi-shield-lock"></i>
                        View Permissions
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="section-header">
            <h3 class="section-title">Quick Access</h3>
            <p class="section-subtitle">Frequently used features</p>
        </div>

        <div class="quick-links-grid">

            <?php if(
                    auth()->guard('admin')->user() && auth()->guard('admin')
                        ->user()->canAccessAny(['admins.create'])
                ): ?>
                <a href="<?php echo e(route('admins.create')); ?>" class="quick-link-card">
                    <div class="quick-link-icon icon-primary">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="quick-link-content">
                        <h4 class="quick-link-title">Add New Admin</h4>
                        <p class="quick-link-description">Create a new administrator account</p>
                    </div>
                    <i class="bi bi-arrow-right quick-link-arrow"></i>
                </a>
            <?php endif; ?>

            <?php if(
                    auth()->guard('admin')->user() && auth()->guard('admin')
                        ->user()->canAccessAny(['permissions.create'])
                ): ?>
                <a href="<?php echo e(route('permissions.create')); ?>" class="quick-link-card">
                    <div class="quick-link-icon icon-success">
                        <i class="bi bi-shield-plus"></i>
                    </div>
                    <div class="quick-link-content">
                        <h4 class="quick-link-title">Create Permission</h4>
                        <p class="quick-link-description">Define new system permissions</p>
                    </div>
                    <i class="bi bi-arrow-right quick-link-arrow"></i>
                </a>
            <?php endif; ?>

            <?php if(
                    auth()->guard('admin')->user() && auth()->guard('admin')
                        ->user()->canAccessAny(['orders.view', 'orders.create'])
                ): ?>
                <a href="<?php echo e(route('orders.index')); ?>" class="quick-link-card">
                    <div class="quick-link-icon icon-warning">
                        <i class="bi bi-basket"></i>
                    </div>
                    <div class="quick-link-content">
                        <h4 class="quick-link-title">View Orders</h4>
                        <p class="quick-link-description">Manage customer orders</p>
                    </div>
                    <i class="bi bi-arrow-right quick-link-arrow"></i>
                </a>
            <?php endif; ?>

            <?php if(
                    auth()->guard('admin')->user() && auth()->guard('admin')
                        ->user()->canAccessAny(['chat.access'])
                ): ?>
                <a href="<?php echo e(route('chat.index')); ?>" class="quick-link-card">
                    <div class="quick-link-icon icon-info">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="quick-link-content">
                        <h4 class="quick-link-title">Chat Support</h4>
                        <p class="quick-link-description">Customer support messages</p>
                    </div>
                    <i class="bi bi-arrow-right quick-link-arrow"></i>
                </a>
            <?php endif; ?>
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

        .dashboard-container {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dashboard-title i {
            color: var(--primary);
        }

        .dashboard-subtitle {
            color: var(--gray);
            margin: 0;
            font-size: 1rem;
        }

        .dashboard-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-action {
            position: relative;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            border: 2px solid var(--border);
            background: white;
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1.25rem;
        }

        .btn-action:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .badge-notification {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-primary {
            border-color: rgba(99, 102, 241, 0.1);
        }

        .stat-primary:hover {
            border-color: var(--primary);
        }

        .stat-success {
            border-color: rgba(16, 185, 129, 0.1);
        }

        .stat-success:hover {
            border-color: var(--success);
        }

        .stat-warning {
            border-color: rgba(245, 158, 11, 0.1);
        }

        .stat-warning:hover {
            border-color: var(--warning);
        }

        .stat-info {
            border-color: rgba(59, 130, 246, 0.1);
        }

        .stat-info:hover {
            border-color: var(--info);
        }

        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .stat-primary .stat-icon {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
        }

        .stat-success .stat-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .stat-warning .stat-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            color: var(--warning);
        }

        .stat-info .stat-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            color: var(--info);
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        .stat-change.neutral {
            color: var(--gray);
        }

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }

        .welcome-content {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .welcome-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            flex-shrink: 0;
        }

        .welcome-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .welcome-text {
            opacity: 0.9;
            margin: 0;
            line-height: 1.6;
        }

        .warning-message {
            background: rgba(245, 11, 11, 0.2);
            border-left: 4px solid var(--danger);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .welcome-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-primary-modern,
        .btn-secondary-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary-modern {
            background: white;
            color: var(--primary);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 255, 255, 0.3);
        }

        .btn-secondary-modern {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary-modern:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* Section Header */
        .section-header {
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.25rem 0;
        }

        .section-subtitle {
            color: var(--gray);
            margin: 0;
        }

        /* Quick Links */
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 3rem;
        }

        .quick-link-card {
            background: white;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            transition: all 0.3s;
        }

        .quick-link-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .quick-link-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .icon-primary {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
        }

        .icon-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .icon-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            color: var(--warning);
        }

        .icon-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            color: var(--info);
        }

        .quick-link-content {
            flex: 1;
        }

        .quick-link-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.25rem 0;
        }

        .quick-link-description {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0;
        }

        .quick-link-arrow {
            color: var(--gray);
            font-size: 1.25rem;
            transition: transform 0.2s;
        }

        .quick-link-card:hover .quick-link-arrow {
            transform: translateX(4px);
            color: var(--primary);
        }

        /* Activity Card */
        .activity-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .activity-item:hover {
            background: var(--light-gray);
        }

        .activity-item:not(:last-child) {
            border-bottom: 1px solid var(--border);
        }

        .activity-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .activity-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .activity-primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .activity-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: var(--dark);
            margin: 0 0 0.25rem 0;
        }

        .activity-description {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0;
        }

        .activity-time {
            font-size: 0.875rem;
            color: var(--gray);
            white-space: nowrap;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .welcome-content {
                flex-direction: column;
            }

            .quick-links-grid {
                grid-template-columns: 1fr;
            }

            .activity-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .activity-time {
                align-self: flex-end;
            }
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>