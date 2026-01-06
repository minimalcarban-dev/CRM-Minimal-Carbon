

<?php $__env->startSection('title', 'Metal Types'); ?>

<?php $__env->startSection('content'); ?>
    <div class="attr-list-container">
        <div class="attr-list-header">
            <div class="attr-header-content">
                <div class="attr-header-left">
                    <div class="attr-breadcrumb-nav">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="attr-breadcrumb-link"><i
                                class="bi bi-house-door"></i> Dashboard</a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <span class="attr-breadcrumb-current">Metal Types</span>
                    </div>
                    <h1 class="attr-list-title"><i class="bi bi-award"></i> Metal Types</h1>
                    <p class="attr-list-subtitle">Manage all metal types in your inventory</p>
                </div>
                <?php if($currentAdmin && $currentAdmin->hasPermission('metal_types.create')): ?>
                    <div class="attr-header-right">
                        <a href="<?php echo e(route('metal_types.create')); ?>" class="attr-btn-create"><i class="bi bi-plus-circle"></i>
                            Add Metal Type</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="attr-filter-section">
            <form method="GET" class="attr-filter-form">
                <div class="attr-search-box"><i class="bi bi-search attr-search-icon"></i><input type="text" name="search"
                        class="attr-search-input" placeholder="Search..." value="<?php echo e(request('search')); ?>"></div>
                <button type="submit" class="attr-btn-filter"><i class="bi bi-funnel"></i> Filter</button>
                <?php if(request('search')): ?><a href="<?php echo e(route('metal_types.index')); ?>" class="attr-btn-reset"><i
                class="bi bi-arrow-counterclockwise"></i> Reset</a><?php endif; ?>
            </form>
        </div>

        <div class="attr-table-card">
            <?php if($items->count() > 0): ?>
                <table class="attr-table">
                    <thead>
                        <tr>
                            <th>
                                <div class="attr-th"><i class="bi bi-hash"></i> ID</div>
                            </th>
                            <th>
                                <div class="attr-th"><i class="bi bi-tag"></i> Name</div>
                            </th>
                            <th>
                                <div class="attr-th"><i class="bi bi-toggle-on"></i> Status</div>
                            </th>
                            <th>
                                <div class="attr-th"><i class="bi bi-calendar"></i> Created</div>
                            </th>
                            <th class="attr-th-actions">
                                <div class="attr-th"><i class="bi bi-gear"></i> Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="attr-row">
                                <td><span class="attr-id-badge">#<?php echo e($item->id); ?></span></td>
                                <td><span class="attr-name"><?php echo e($item->name); ?></span></td>
                                <td><span class="attr-status <?php echo e($item->is_active ? 'active' : 'inactive'); ?>"><i
                                            class="bi bi-<?php echo e($item->is_active ? 'check-circle' : 'x-circle'); ?>"></i>
                                        <?php echo e($item->is_active ? 'Active' : 'Inactive'); ?></span></td>
                                <td><span class="attr-date"><?php echo e($item->created_at?->format('M d, Y') ?? '—'); ?></span></td>
                                <td class="attr-actions">
                                    <?php if($currentAdmin && $currentAdmin->hasPermission('metal_types.view')): ?><a
                                        href="<?php echo e(route('metal_types.show', $item->id)); ?>" class="attr-action-btn view"><i
                                    class="bi bi-eye"></i></a><?php endif; ?>
                                    <?php if($currentAdmin && $currentAdmin->hasPermission('metal_types.edit')): ?><a
                                        href="<?php echo e(route('metal_types.edit', $item->id)); ?>" class="attr-action-btn edit"><i
                                    class="bi bi-pencil"></i></a><?php endif; ?>
                                    <?php if($currentAdmin && $currentAdmin->hasPermission('metal_types.delete')): ?>
                                        <form action="<?php echo e(route('metal_types.destroy', $item->id)); ?>" method="POST"
                                            class="d-inline delete-form"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit"
                                    class="attr-action-btn delete"><i class="bi bi-trash"></i></button></form><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="attr-empty-state">
                    <div class="attr-empty-icon"><i class="bi bi-inbox"></i></div>
                    <h3>No metal types found</h3>
                    <p>Get started by adding your first metal type.</p><a href="<?php echo e(route('metal_types.create')); ?>"
                        class="attr-btn-create"><i class="bi bi-plus-circle"></i> Add Metal Type</a>
                </div>
            <?php endif; ?>
            <?php if($items->hasPages()): ?>
            <div class="attr-pagination"><?php echo e($items->links()); ?></div><?php endif; ?>
        </div>
    </div>
    <?php echo $__env->make('partials.attribute-index-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>document.querySelectorAll('.delete-form').forEach(form => { form.addEventListener('submit', function (e) { e.preventDefault(); if (confirm('Delete this item?')) this.submit(); }); });</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/metal_types/index.blade.php ENDPATH**/ ?>