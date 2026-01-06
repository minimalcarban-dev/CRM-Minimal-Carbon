

<?php $__env->startSection('title', 'Admins'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admins-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="<?php echo e(url('/admin/dashboard')); ?>" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Admins</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-people-fill"></i>
                        Admin Management
                    </h1>
                    <p class="page-subtitle">Manage administrator accounts and their access</p>
                </div>
                <div class="header-right">
                    <?php if($currentAdmin && $currentAdmin->hasPermission('admins.create')): ?>
                        <a href="<?php echo e(route('admins.create')); ?>" class="btn-primary-custom">
                            <i class="bi bi-person-plus"></i>
                            <span>Add Admin</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="filter-section">
            <form method="GET" class="search-form">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search by name or email..."
                        value="<?php echo e(request('search')); ?>">
                </div>
                <button type="submit" class="btn-search">
                    <i class="bi bi-search"></i>
                    Search
                </button>
                <?php if(request('search')): ?>
                    <a href="<?php echo e(route('admins.index')); ?>" class="btn-reset">
                        <i class="bi bi-x-circle"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </form>
            <div class="filter-info">
                <span class="result-count">
                    Showing <?php echo e($admins->firstItem() ?? 0); ?> to <?php echo e($admins->lastItem() ?? 0); ?> of <?php echo e($admins->total()); ?>

                    admins
                </span>
            </div>
        </div>

        <!-- Admins Grid -->
        <div class="admins-grid">
            <?php $__empty_1 = true; $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="admin-card <?php echo e($admin->is_super ? 'admin-card-super' : ''); ?>">
                    <div class="admin-header">
                        <div class="admin-avatar">
                            <?php echo e(strtoupper(substr($admin->name, 0, 2))); ?>

                        </div>
                        <div class="admin-badge">
                            <?php if($admin->is_super): ?>
                                <span class="badge badge-super">
                                    <i class="bi bi-star-fill"></i> Super Admin
                                </span>
                            <?php else: ?>
                                <span class="badge badge-regular">
                                    <i class="bi bi-person-check"></i> Admin
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-content">
                        <h3 class="admin-name"><?php echo e($admin->name); ?></h3>
                        <div class="admin-info">
                            <div class="info-item">
                                <i class="bi bi-envelope"></i>
                                <span><?php echo e($admin->email); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-telephone"></i>
                                <span><?php echo e($admin->country_code); ?> <?php echo e($admin->phone_number); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="bi bi-geo-alt"></i>
                                <span><?php echo e($admin->country); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="admin-actions">
                        <?php if($currentAdmin && $currentAdmin->hasPermission('admins.view')): ?>
                            <a href="<?php echo e(route('admins.show', $admin)); ?>" class="action-btn action-btn-view" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        <?php endif; ?>
                        <?php if($currentAdmin && $currentAdmin->hasPermission('admins.edit')): ?>
                            <a href="<?php echo e(route('admins.edit', $admin)); ?>" class="action-btn action-btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        <?php endif; ?>
                        <?php if($currentAdmin && $currentAdmin->hasPermission('admins.assign_permissions')): ?>
                            <a href="<?php echo e(route('admins.permissions.show', $admin)); ?>" class="action-btn action-btn-permissions"
                                title="Permissions">
                                <i class="bi bi-shield-lock"></i>
                            </a>
                        <?php endif; ?>
                        <?php if($currentAdmin && $currentAdmin->hasPermission('admins.delete')): ?>
                            <form action="<?php echo e(route('admins.destroy', $admin)); ?>" method="POST" class="d-inline" class="delete-form">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="action-btn action-btn-delete" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="empty-title">No admins found</h3>
                    <p class="empty-description">
                        <?php if(request('search')): ?>
                            No admins match your search criteria. Try adjusting your search.
                        <?php else: ?>
                            Start by adding your first administrator.
                        <?php endif; ?>
                    </p>
                    <?php if($currentAdmin && $currentAdmin->hasPermission('admins.create')): ?>
                        <a href="<?php echo e(route('admins.create')); ?>" class="btn-primary-custom">
                            <i class="bi bi-person-plus"></i>
                            Add First Admin
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if($admins->hasPages()): ?>
            <div class="pagination-wrapper">
                <?php echo e($admins->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --purple: #a855f7;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
        }

        .admins-container {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .breadcrumb-link {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-link:hover {
            color: var(--primary);
        }

        .breadcrumb-separator {
            font-size: 0.75rem;
        }

        .breadcrumb-current {
            color: var(--dark);
            font-weight: 500;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary);
        }

        .page-subtitle {
            color: var(--gray);
            margin: 0;
            font-size: 1rem;
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            color: white;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .search-form {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: var(--light-gray);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-search,
        .btn-reset {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid var(--border);
            text-decoration: none;
        }

        .btn-search {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-search:hover {
            background: var(--primary-dark);
        }

        .btn-reset {
            background: white;
            color: var(--gray);
        }

        .btn-reset:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        .filter-info {
            display: flex;
            justify-content: flex-end;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border);
        }

        .result-count {
            color: var(--gray);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Admins Grid */
        .admins-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .admin-card {
            background: white;
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .admin-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        /* Super Admin Card - Golden Border Styling */
        .admin-card-super {
            border: 2px solid #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fef9c3 100%);
        }

        .admin-card-super .admin-avatar {
            background: linear-gradient(135deg, #f59e0b, #b45309);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .admin-avatar {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .admin-badge {
            display: flex;
        }

        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-super {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .badge-regular {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .admin-content {
            flex: 1;
        }

        .admin-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 1rem 0;
        }

        .admin-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .info-item i {
            color: var(--primary);
            font-size: 1rem;
        }

        .admin-actions {
            display: flex;
            gap: 0.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        /* Ensure forms inside the actions row behave like the other action buttons
                   so the delete button aligns and sizes consistently with anchors. */
        .admin-actions form.d-inline {
            display: flex;
            flex: 1;
            margin: 0;
            align-items: center;
        }

        .admin-actions form.d-inline .action-btn {
            width: 100%;
            flex: 1;
        }

        .action-btn {
            flex: 1;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border);
            background: white;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .action-btn-view:hover {
            border-color: var(--info);
            color: var(--info);
            background: rgba(59, 130, 246, 0.05);
        }

        .action-btn-edit:hover {
            border-color: var(--warning);
            color: var(--warning);
            background: rgba(245, 158, 11, 0.05);
        }

        .action-btn-permissions:hover {
            border-color: var(--purple);
            color: var(--purple);
            background: rgba(168, 85, 247, 0.05);
        }

        .action-btn-delete:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            background: white;
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary);
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
        }

        .empty-description {
            color: var(--gray);
            margin: 0 0 2rem 0;
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
            }

            .btn-primary-custom {
                width: 100%;
                justify-content: center;
            }

            .search-form {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            .admins-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const confirmed = await showConfirm('Delete this admin?', 'This action cannot be undone', 'Yes, Delete', 'Cancel');
                    if (confirmed) this.submit();
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/admins/index.blade.php ENDPATH**/ ?>