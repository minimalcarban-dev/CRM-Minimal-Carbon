

<?php $__env->startSection('title', 'Diamonds'); ?>

<?php $__env->startSection('content'); ?>
<div class="diamond-management-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="breadcrumb-nav">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="breadcrumb-link">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Diamonds</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-gem"></i>
                    Diamond Management
                </h1>
                <p class="page-subtitle">Manage your diamond inventory and listings</p>
            </div>
            <div class="header-right">
                <div class="actions-stack">
                    <a href="<?php echo e(route('diamond.create')); ?>" class="btn-primary-custom actions-btn">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Diamond</span>
                    </a>

                    <div class="actions-row">
                        <form id="importForm" action="<?php echo e(route('diamonds.import')); ?>" method="POST"
                            enctype="multipart/form-data" class="actions-inline-form">
                            <?php echo csrf_field(); ?>
                            <input id="importFile" type="file" name="file" accept=".xlsx,.xls,.csv" required
                                style="display:none;">

                            <button type="button" id="importTrigger" class="btn-secondary-custom actions-btn">
                                <i class="bi bi-upload"></i>
                                <span>Import</span>
                            </button>
                        </form>

                        <button type="button" id="exportTrigger" class="btn-secondary-custom actions-btn">
                            <i class="bi bi-file-earmark-arrow-down"></i>
                            <span>Export</span>
                        </button>

                        <button type="button" id="bulkEditTrigger" class="btn-secondary-custom actions-btn">
                            <i class="bi bi-pencil-square"></i>
                            <span>Bulk Edit</span>
                        </button>
                    </div>
                </div>
            </div>

            <script>
                // Import and Export modals
                document.addEventListener('DOMContentLoaded', function () {
                    const importTrigger = document.getElementById('importTrigger');
                    const exportTrigger = document.getElementById('exportTrigger');
                    const importModal = document.getElementById('importModal');
                    const exportModal = document.getElementById('exportModal');
                    const fileInput = document.getElementById('importFile');
                    const form = document.getElementById('importForm');

                    // Import Modal
                    importTrigger?.addEventListener('click', function () {
                        importModal?.classList.remove('d-none');
                    });

                    document.getElementById('closeImportModal')?.addEventListener('click', () => {
                        importModal?.classList.add('d-none');
                    });

                    document.getElementById('cancelImport')?.addEventListener('click', () => {
                        importModal?.classList.add('d-none');
                    });

                    document.getElementById('proceedImport')?.addEventListener('click', () => {
                        importModal?.classList.add('d-none');
                        fileInput?.click();
                    });

                    // Export Modal
                    exportTrigger?.addEventListener('click', function () {
                        exportModal?.classList.remove('d-none');
                    });

                    document.getElementById('closeExportModal')?.addEventListener('click', () => {
                        exportModal?.classList.add('d-none');
                    });

                    document.getElementById('cancelExport')?.addEventListener('click', () => {
                        exportModal?.classList.add('d-none');
                    });

                    // Close modals on overlay click
                    importModal?.addEventListener('click', (e) => {
                        if (e.target === importModal) {
                            importModal.classList.add('d-none');
                        }
                    });

                    exportModal?.addEventListener('click', (e) => {
                        if (e.target === exportModal) {
                            exportModal.classList.add('d-none');
                        }
                    });

                    // Auto-submit on file selection
                    fileInput?.addEventListener('change', function () {
                        if (this.files && this.files.length) {
                            form?.submit();
                        }
                    });
                });
            </script>
        </div>
    </div>

    <!-- Stats Cards (visible to Super Admin only) -->
    <?php if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->is_super): ?>
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-gem"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Diamonds</div>
                    <div class="stat-value"><?php echo e($allDiamondsCount ?? 0); ?></div>
                    <div class="stat-breakdown">
                        <span class="breakdown-item breakdown-success">
                            <i class="bi bi-check-circle"></i> In Stock: <?php echo e($inStockCount ?? 0); ?>

                        </span>
                        <span class="breakdown-item breakdown-sold">
                            <i class="bi bi-tag"></i> Sold: <?php echo e($soldCount ?? 0); ?>

                        </span>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Value</div>
                    <div class="stat-value">$<?php echo e(number_format($totalValue ?? 0, 2)); ?></div>
                    <div class="stat-trend">
                        <i class="bi bi-graph-up"></i> Inventory
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-tag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Avg. Price</div>
                    <div class="stat-value">
                        $<?php echo e(number_format($avgPrice ?? 0, 2)); ?>

                    </div>
                    <div class="stat-trend">
                        <i class="bi bi-calculator"></i> Per Item
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-header" id="toggleFilters">
            <div class="filter-title">
                <i class="bi bi-funnel"></i>
                <span>Advanced Filters</span>
            </div>
            <button type="button" class="btn-toggle-filters" id="toggleFilters">
                <i class="bi bi-chevron-up"></i>
            </button>
        </div>

        <form method="GET" action="<?php echo e(route('diamond.index')); ?>" class="filter-form" id="filterForm">
            <!-- Search Section -->
            <div class="filter-section-group">
                <div class="filter-section-title">Search Criteria</div>
                <div class="filter-row-3">
                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-search"></i>
                            <span>Search SKU</span>
                        </label>
                        <input type="text" name="sku" class="filter-input" placeholder="Enter SKU..."
                            value="<?php echo e(request('sku')); ?>">
                    </div>

                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-hash"></i>
                            <span>Lot Number</span>
                        </label>
                        <input type="text" name="lot_no" class="filter-input" placeholder="Enter Lot No..."
                            value="<?php echo e(request('lot_no')); ?>">
                    </div>

                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-flag"></i>
                            <span>Status</span>
                        </label>
                        <select name="status" class="filter-select">
                            <option value="">All Status</option>
                            <option value="IN Stock" <?php echo e(request('status') === 'IN Stock' ? 'selected' : ''); ?>>In Stock
                            </option>
                            <option value="Sold" <?php echo e(request('status') === 'Sold' ? 'selected' : ''); ?>>Sold</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Diamond Properties Section -->
            <div class="filter-section-group">
                <div class="filter-section-title">Diamond Properties</div>
                <div class="filter-row-4">
                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-diamond"></i>
                            <span>Shape</span>
                        </label>
                        <select name="shape" class="filter-select">
                            <option value="">All Shapes</option>
                            <?php $__currentLoopData = $shapes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shape): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($shape); ?>" <?php echo e(request('shape') === $shape ? 'selected' : ''); ?>>
                                    <?php echo e($shape); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-gem"></i>
                            <span>Cut</span>
                        </label>
                        <select name="cut" class="filter-select">
                            <option value="">All Cuts</option>
                            <?php $__currentLoopData = $cuts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cut): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cut); ?>" <?php echo e(request('cut') === $cut ? 'selected' : ''); ?>>
                                    <?php echo e($cut); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-eye"></i>
                            <span>Clarity</span>
                        </label>
                        <select name="clarity" class="filter-select">
                            <option value="">All Clarities</option>
                            <?php $__currentLoopData = $clarities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clarity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($clarity); ?>" <?php echo e(request('clarity') === $clarity ? 'selected' : ''); ?>>
                                    <?php echo e($clarity); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-palette"></i>
                            <span>Color</span>
                        </label>
                        <select name="color" class="filter-select">
                            <option value="">All Colors</option>
                            <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($color); ?>" <?php echo e(request('color') === $color ? 'selected' : ''); ?>>
                                    <?php echo e($color); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Material & Type Section -->
            <div class="filter-section-group">
                <div class="filter-section-title">Material & Classification</div>
                <div class="filter-row-3">
                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-layers"></i>
                            <span>Material</span>
                        </label>
                        <select name="material" class="filter-select">
                            <option value="">All Materials</option>
                            <?php $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($material); ?>" <?php echo e(request('material') === $material ? 'selected' : ''); ?>>
                                    <?php echo e($material); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label class="filter-label">
                            <i class="bi bi-tag"></i>
                            <span>Diamond Type</span>
                        </label>
                        <select name="diamond_type" class="filter-select">
                            <option value="">All Types</option>
                            <?php $__currentLoopData = $diamondTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>" <?php echo e(request('diamond_type') === $type ? 'selected' : ''); ?>>
                                    <?php echo e($type); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <?php if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->is_super): ?>
                        <div class="filter-field">
                            <label class="filter-label">
                                <i class="bi bi-person"></i>
                                <span>Assigned To</span>
                            </label>
                            <select name="admin_id" class="filter-select">
                                <option value="">All Admins</option>
                                <?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($admin->id); ?>" <?php echo e(request('admin_id') == $admin->id ? 'selected' : ''); ?>>
                                        <?php echo e($admin->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Price & Weight Range Section -->
            <div class="filter-section-group">
                <div class="filter-section-title">Price & Weight Range</div>
                <div class="filter-row-2">
                    <div class="filter-field-range">
                        <label class="filter-label">
                            <i class="bi bi-currency-dollar"></i>
                            <span>Price Range</span>
                        </label>
                        <div class="filter-range-inputs">
                            <input type="number" name="min_price" class="filter-input filter-input-small"
                                placeholder="Min Price" step="0.01" value="<?php echo e(request('min_price')); ?>">
                            <span class="range-separator">—</span>
                            <input type="number" name="max_price" class="filter-input filter-input-small"
                                placeholder="Max Price" step="0.01" value="<?php echo e(request('max_price')); ?>">
                        </div>
                    </div>

                    <div class="filter-field-range">
                        <label class="filter-label">
                            <i class="bi bi-weight"></i>
                            <span>Weight Range (Carat)</span>
                        </label>
                        <div class="filter-range-inputs">
                            <input type="number" name="min_weight" class="filter-input filter-input-small"
                                placeholder="Min Carat" step="0.01" value="<?php echo e(request('min_weight')); ?>">
                            <span class="range-separator">—</span>
                            <input type="number" name="max_weight" class="filter-input filter-input-small"
                                placeholder="Max Carat" step="0.01" value="<?php echo e(request('max_weight')); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="filter-actions">
                <div class="filter-actions-left">
                    <span class="result-count">
                        <i class="bi bi-info-circle"></i>
                        Showing <strong><?php echo e($diamonds->count()); ?></strong> of <strong><?php echo e($totalDiamonds); ?></strong>
                        diamonds
                    </span>
                    <div class="per-page-filter">
                        <label class="per-page-label">
                            <i class="bi bi-list-ol"></i> Per Page:
                        </label>
                        <select name="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="20" <?php echo e(request('per_page', 20) == 20 ? 'selected' : ''); ?>>20</option>
                            <option value="50" <?php echo e(request('per_page') == 50 ? 'selected' : ''); ?>>50</option>
                            <option value="100" <?php echo e(request('per_page') == 100 ? 'selected' : ''); ?>>100</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions-right">
                    <a href="<?php echo e(route('diamond.index')); ?>" class="btn-filter-reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Clear All</span>
                    </a>
                    <button type="submit" class="btn-filter-apply">
                        <i class="bi bi-funnel"></i>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Diamonds Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-hash"></i>
                                <span>Lot NO</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-upc"></i>
                                <span>SKU</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-currency-dollar"></i>
                                <span>Price</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-tag"></i>
                                <span>Listing Price</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-flag"></i>
                                <span>Status</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-diamond"></i>
                                <span>Shape</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-upc-scan"></i>
                                <span>Barcode</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-person-badge"></i>
                                <span>Assigned By</span>
                            </div>
                        </th>
                        <th>
                            <div class="th-content">
                                <i class="bi bi-person-check"></i>
                                <span>Assigned To</span>
                            </div>
                        </th>
                        <th class="text-center">
                            <div class="th-content th-content-center">
                                <i class="bi bi-gear"></i>
                                <span>Actions</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $diamonds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php ($isSold = !empty($d->sold_out_date)); ?>
                    <tr class="diamond-row <?php echo e($isSold ? 'sold-row' : ''); ?>"
                        data-search="<?php echo e(strtolower($d->lot_no . ' ' . $d->sku . ' ' . $d->barcode_number)); ?>">
                        <td>
                            <div class="cell-content">
                                <span class="badge-custom badge-primary"><?php echo e($d->lot_no); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content">
                                <span class="text-semibold"><?php echo e($d->sku); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content">
                                <span class="price-value">$<?php echo e(number_format($d->purchase_price ?? 0, 2)); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content">
                                <span class="price-value listing">$<?php echo e(number_format($d->listing_price, 2)); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content">
                                <?php if($d->sold_out_date): ?>
                                    <span class="status-pill status-sold">Sold Out</span>
                                <?php else: ?>
                                    <span class="status-pill status-instock">In Stock</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content">
                                <span class="text-muted"><?php echo e($d->shape ?: '—'); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content">
                                <?php if($d->barcode_image_url): ?>
                                    <img src="<?php echo e($d->barcode_image_url); ?>" alt="barcode" class="barcode-image">
                                <?php else: ?>
                                    <span class="badge-custom badge-secondary"><?php echo e($d->barcode_number); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content">
                                <?php if($d->assignedByAdmin): ?>
                                    <span class="badge-custom badge-info"><?php echo e($d->assignedByAdmin->name); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content">
                                <div class="admin-assignment">
                                    <?php if($d->assignedAdmin): ?>
                                        <span
                                            class="admin-name badge-custom badge-success"><?php echo e($d->assignedAdmin->name); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                    <?php endif; ?>
                                    <button type="button" class="btn-reassign" data-diamond-id="<?php echo e($d->id); ?>"
                                        title="Reassign to another admin">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="cell-content cell-content-center">
                                <div class="action-buttons">
                                    <?php ($sold = !empty($d->sold_out_date)); ?>

                                    <?php if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.view'])): ?>
                                        <a href="<?php echo e(route('diamond.show', $d)); ?>" class="action-btn action-btn-view"
                                            title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.edit'])): ?>
                                        <a href="<?php echo e(route('diamond.edit', $d)); ?>" class="action-btn action-btn-edit"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if($sold && auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.edit'])): ?>
                                        <form action="<?php echo e(route('diamond.restock', $d)); ?>" method="POST"
                                            class="d-inline restock-form">
                                            <?php echo csrf_field(); ?>
                                            <button type="button" class="action-btn action-btn-restock restock-btn"
                                                title="Restock" data-diamond-id="<?php echo e($d->id); ?>"
                                                data-diamond-sku="<?php echo e($d->sku); ?>">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.delete'])): ?>
                                        <form action="<?php echo e(route('diamond.destroy', $d)); ?>" method="POST"
                                            class="d-inline delete-form">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="button" class="action-btn action-btn-delete delete-btn"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10">
                            <div class="empty-state-inline">
                                <div class="empty-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <h3 class="empty-title">No diamonds found</h3>
                                <p class="empty-description">Start by adding your first diamond to the inventory</p>
                                <a href="<?php echo e(route('diamond.create')); ?>" class="btn-primary-custom">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Add First Diamond</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if($diamonds->hasPages()): ?>
        <div class="pagination-container">
            <?php echo e($diamonds->links('pagination::bootstrap-5')); ?>

        </div>
    <?php endif; ?>
</div>

<style>
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --primary-light: #818cf8;
        --success: #10b981;
        --success-light: #34d399;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --dark: #1e293b;
        --gray: #64748b;
        --light-gray: #f8fafc;
        --border: #e2e8f0;
        --shadow: rgba(0, 0, 0, 0.04);
        --shadow-md: rgba(0, 0, 0, 0.08);
        --shadow-lg: rgba(0, 0, 0, 0.12);
    }

    * {
        box-sizing: border-box;
    }

    .diamond-management-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    /* Sold-out highlighting */
    .data-table tbody tr.sold-row {
        background: rgba(254, 202, 202, 0.15);
    }

    .data-table tbody tr.sold-row:hover {
        background: rgba(254, 202, 202, 0.25);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px var(--shadow);
        border: 1px solid var(--border);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .header-left {
        flex: 1;
        min-width: 300px;
    }

    .header-right {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
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
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .breadcrumb-link:hover {
        color: var(--primary);
    }

    .breadcrumb-separator {
        font-size: 0.75rem;
    }

    .breadcrumb-current {
        color: var(--dark);
        font-weight: 600;
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

    /* Buttons */
    .btn-primary-custom {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
    }

    .btn-primary-custom:hover {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        color: white;
    }

    .btn-secondary-custom {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        color: var(--gray);
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: all 0.3s ease;
        border: 2px solid var(--border);
        cursor: pointer;
    }

    .btn-secondary-custom:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
        transform: translateY(-2px);
    }

    /* Actions Toolbar */
    .actions-stack {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .actions-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .actions-btn {
        min-width: 140px;
        justify-content: center;
    }

    .actions-inline-form {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 1px 3px var(--shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px var(--shadow-md);
    }

    .stat-card-primary {
        background: linear-gradient(135deg, #ffffff 0%, rgba(99, 102, 241, 0.02) 100%);
        border-color: rgba(99, 102, 241, 0.1);
    }

    .stat-card-primary:hover {
        border-color: rgba(99, 102, 241, 0.3);
    }

    .stat-card-success {
        background: linear-gradient(135deg, #ffffff 0%, rgba(16, 185, 129, 0.02) 100%);
        border-color: rgba(16, 185, 129, 0.1);
    }

    .stat-card-success:hover {
        border-color: rgba(16, 185, 129, 0.3);
    }

    .stat-card-info {
        background: linear-gradient(135deg, #ffffff 0%, rgba(59, 130, 246, 0.02) 100%);
        border-color: rgba(59, 130, 246, 0.1);
    }

    .stat-card-info:hover {
        border-color: rgba(59, 130, 246, 0.3);
    }

    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
    }

    .stat-card-primary .stat-icon {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
        color: var(--primary);
    }

    .stat-card-success .stat-icon {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
        color: var(--success);
    }

    .stat-card-info .stat-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
        color: var(--info);
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--gray);
        margin-bottom: 0.5rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        line-height: 1.2;
        margin-bottom: 0.5rem;
    }

    .stat-trend {
        font-size: 0.875rem;
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-breakdown {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .breakdown-item {
        font-size: 0.8rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .breakdown-success {
        color: var(--success);
    }

    .breakdown-sold {
        color: var(--danger);
    }

    /* Filter Section - Professional Enterprise Design */
    .filter-section {
        background: white;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px var(--shadow);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.75rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border);
    }

    .filter-title {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-title i {
        color: var(--primary);
        font-size: 1.125rem;
    }

    .btn-toggle-filters {
        background: white;
        border: 2px solid var(--border);
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--gray);
    }

    .btn-toggle-filters:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
    }

    .btn-toggle-filters i {
        transition: transform 0.3s ease;
    }

    .btn-toggle-filters.collapsed i {
        transform: rotate(180deg);
    }

    .filter-form {
        padding: 1.75rem;
        display: block;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .filter-section-group {
        margin-bottom: 2rem;
    }

    .filter-section-group:last-of-type {
        margin-bottom: 0;
    }

    .filter-section-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        padding-bottom: 0.625rem;
        border-bottom: 2px solid var(--border);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-section-title::before {
        content: '';
        width: 4px;
        height: 16px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 2px;
    }

    /* Specific grid layouts for different rows */
    .filter-row-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }

    .filter-row-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }

    .filter-row-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-field-range {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .filter-label i {
        color: var(--primary);
        font-size: 0.875rem;
    }

    .filter-label span {
        color: var(--dark);
    }

    .filter-input,
    .filter-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.875rem;
        background: white;
        transition: all 0.2s ease;
        color: var(--dark);
        font-weight: 500;
    }

    .filter-input::placeholder {
        color: #94a3b8;
    }

    .filter-input:hover,
    .filter-select:hover {
        border-color: #cbd5e1;
    }

    .filter-input:focus,
    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .filter-range-inputs {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 0.75rem;
        align-items: center;
    }

    .filter-input-small {
        padding: 0.75rem 1rem;
    }

    .range-separator {
        color: var(--gray);
        font-weight: 600;
        font-size: 1rem;
        text-align: center;
    }

    .filter-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1.75rem;
        margin-top: 1.75rem;
        border-top: 2px solid var(--border);
    }

    .filter-actions-left {
        flex: 1;
    }

    .filter-actions-right {
        display: flex;
        gap: 0.875rem;
        align-items: center;
    }

    .result-count {
        color: var(--gray);
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .result-count i {
        color: var(--info);
        font-size: 1rem;
    }

    .result-count strong {
        color: var(--dark);
        font-weight: 700;
    }

    .per-page-filter {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 1.5rem;
        padding-left: 1.5rem;
        border-left: 2px solid var(--border);
    }

    .per-page-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .per-page-select {
        padding: 0.4rem 0.75rem;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--dark);
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .per-page-select:hover {
        border-color: var(--primary);
    }

    .per-page-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .filter-actions-left {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .btn-filter-apply {
        padding: 0.875rem 2rem;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
    }

    .btn-filter-apply:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
    }

    .btn-filter-reset {
        padding: 0.875rem 1.75rem;
        background: white;
        color: var(--gray);
        border: 2px solid var(--border);
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-filter-reset:hover {
        background: var(--light-gray);
        border-color: var(--gray);
        color: var(--dark);
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.875rem;
        border-radius: 20px;
        font-size: 0.8125rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        border: 2px solid transparent;
    }

    .status-instock {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-color: #6ee7b7;
    }

    .status-sold {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-color: #fca5a5;
    }

    /* Table Card */
    .table-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 1px 3px var(--shadow);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 2px solid var(--border);
    }

    .data-table th {
        padding: 1.125rem 1.25rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.8125rem;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .th-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .th-content-center {
        justify-content: center;
    }

    .th-content i {
        color: var(--primary);
        font-size: 1rem;
    }

    .data-table tbody tr {
        border-bottom: 1px solid var(--border);
        transition: all 0.2s ease;
    }

    .data-table tbody tr:hover {
        background: rgba(99, 102, 241, 0.02);
    }

    .data-table td {
        padding: 1.125rem 1.25rem;
        vertical-align: middle;
    }

    .cell-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cell-content-center {
        justify-content: center;
    }

    .text-semibold {
        font-weight: 600;
        color: var(--dark);
    }

    .text-muted {
        color: var(--gray);
    }

    .price-value {
        font-weight: 700;
        color: var(--success);
        font-family: 'SF Mono', 'Monaco', 'Cascadia Code', monospace;
        font-size: 0.9375rem;
    }

    .price-value.listing {
        color: var(--info);
    }

    .badge-custom {
        padding: 0.375rem 0.875rem;
        border-radius: 10px;
        font-size: 0.8125rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-primary {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.08));
        color: var(--primary-dark);
    }

    .badge-secondary {
        background: var(--light-gray);
        color: var(--gray);
        border: 1px solid var(--border);
    }

    .badge-info {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.08));
        color: #1e40af;
    }

    .badge-success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.08));
        color: #065f46;
    }

    .barcode-image {
        height: 40px;
        max-width: 120px;
        border-radius: 8px;
        border: 1px solid var(--border);
    }

    /* Admin Assignment */
    .admin-assignment {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .admin-name {
        white-space: nowrap;
    }

    .btn-reassign {
        background: white;
        border: 2px solid var(--border);
        color: var(--primary);
        cursor: pointer;
        padding: 0.375rem 0.5rem;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-reassign:hover {
        background: rgba(99, 102, 241, 0.08);
        border-color: var(--primary);
        transform: scale(1.05);
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--border);
        background: white;
        color: var(--gray);
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-size: 0.9375rem;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px var(--shadow-md);
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

    .action-btn-restock:hover {
        border-color: var(--success);
        color: var(--success);
        background: rgba(16, 185, 129, 0.05);
    }

    .action-btn-delete:hover {
        border-color: var(--danger);
        color: var(--danger);
        background: rgba(239, 68, 68, 0.05);
    }

    /* Empty States */
    .empty-state-inline {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
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

    /* Reassign Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }

    .modal-overlay.d-none {
        display: none;
    }

    .reassign-modal {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-header i {
        color: var(--primary);
    }

    .modal-body {
        margin-bottom: 1.5rem;
    }

    .modal-label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--gray);
        font-size: 0.875rem;
        font-weight: 600;
    }

    .modal-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.9375rem;
        background: var(--light-gray);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .modal-select:hover {
        border-color: var(--primary);
        background: white;
    }

    .modal-select:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .modal-footer {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .btn-modal {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.9375rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-modal-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
    }

    .btn-modal-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
    }

    .btn-modal-primary:disabled {
        background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
        cursor: not-allowed;
        transform: none;
    }

    .btn-modal-cancel {
        background: white;
        color: var(--gray);
        border: 2px solid var(--border);
    }

    .btn-modal-cancel:hover {
        background: var(--light-gray);
        border-color: var(--gray);
    }

    .btn-modal-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-size: 0.9375rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-modal-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    }

    /* Tutorial Modal Styles */
    .tutorial-modal {
        background: white;
        border-radius: 20px;
        max-width: 800px;
        width: 95%;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
    }

    .tutorial-modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem 2rem;
        font-size: 1.375rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
    }

    .btn-close-modal {
        position: absolute;
        right: 1.5rem;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: white;
        transition: all 0.2s;
    }

    .btn-close-modal:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .tutorial-modal-body {
        padding: 2rem;
        overflow-y: auto;
        flex: 1;
    }

    .tutorial-step {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .tutorial-step:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .step-number {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .step-content {
        flex: 1;
    }

    .step-content h4 {
        margin: 0 0 0.75rem 0;
        color: #1e293b;
        font-size: 1.125rem;
        font-weight: 700;
    }

    .step-content p {
        margin: 0 0 1rem 0;
        color: #64748b;
        line-height: 1.6;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .field-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .field-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 4px solid #10b981;
    }

    .field-item.required {
        border-left-color: #ef4444;
    }

    .field-item i {
        color: #ef4444;
        font-size: 0.625rem;
    }

    .field-item strong {
        color: #1e293b;
        font-family: 'Courier New', monospace;
    }

    .field-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .field-tag {
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        text-align: center;
        font-family: 'Courier New', monospace;
    }

    .help-text {
        font-size: 0.875rem;
        color: #94a3b8;
        font-style: italic;
    }

    .rules-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .rules-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem;
        background: #f0fdf4;
        border-radius: 8px;
        color: #166534;
        line-height: 1.5;
    }

    .rules-list i {
        color: #10b981;
        margin-top: 0.125rem;
        flex-shrink: 0;
    }

    .export-columns {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin: 1.5rem 0;
    }

    .column-group {
        padding: 1.25rem;
        background: #f8fafc;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
    }

    .column-group h5 {
        margin: 0 0 1rem 0;
        color: #1e293b;
        font-size: 0.9375rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .column-group h5 i {
        color: #667eea;
    }

    .column-group ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .column-group li {
        color: #64748b;
        font-size: 0.875rem;
        padding-left: 1rem;
        position: relative;
    }

    .column-group li::before {
        content: '•';
        position: absolute;
        left: 0;
        color: #667eea;
        font-weight: bold;
    }

    .export-info-box {
        display: flex;
        gap: 1rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        border-left: 4px solid #f59e0b;
        margin-top: 1.5rem;
    }

    .export-info-box i {
        color: #f59e0b;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .export-info-box strong {
        color: #92400e;
    }

    .export-info-box div {
        color: #78350f;
        line-height: 1.6;
    }

    .tutorial-modal-footer {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        border-top: 2px solid #e2e8f0;
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    @media (max-width: 768px) {
        .tutorial-modal {
            width: 98%;
            max-height: 95vh;
        }

        .tutorial-step {
            flex-direction: column;
            gap: 1rem;
        }

        .export-columns {
            grid-template-columns: 1fr;
        }

        .tutorial-modal-footer {
            flex-direction: column;
        }

        .btn-modal,
        .btn-modal-success {
            width: 100%;
            justify-content: center;
        }
    }

    /* Pagination */
    .pagination-container {
        margin-top: 1.5rem;
    }

    /* Utility Classes */
    .text-center {
        text-align: center;
    }

    .d-inline {
        display: inline;
    }

    .d-none {
        display: none;
    }

    /* Responsive Design */
    @media (max-width: 1400px) {
        .filter-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
    }

    @media (max-width: 1200px) {
        .data-table {
            font-size: 0.875rem;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
        }
    }

    @media (max-width: 768px) {
        .diamond-management-container {
            padding: 1rem;
        }

        .page-header {
            border-radius: 16px;
            padding: 1.5rem;
        }

        .header-content {
            flex-direction: column;
            align-items: stretch;
        }

        .header-right {
            width: 100%;
        }

        .actions-stack {
            width: 100%;
        }

        .actions-row {
            flex-direction: column;
        }

        .btn-primary-custom,
        .btn-secondary-custom,
        .actions-btn {
            width: 100%;
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-filter-apply,
        .btn-filter-reset {
            width: 100%;
            justify-content: center;
        }

        .table-card {
            border-radius: 12px;
        }

        .data-table th,
        .data-table td {
            padding: 0.875rem;
            font-size: 0.8125rem;
        }

        .th-content i {
            font-size: 0.875rem;
        }

        .action-buttons {
            flex-wrap: wrap;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            font-size: 0.875rem;
        }
    }

    @media (max-width: 480px) {
        .page-title {
            font-size: 1.5rem;
        }

        .stat-card {
            padding: 1.25rem;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 1.75rem;
        }
    }
</style>

<!-- Reassign Modal -->
<div id="reassignModal" class="modal-overlay d-none">
    <div class="reassign-modal">
        <div class="modal-header">
            <i class="bi bi-arrow-repeat"></i>
            <span>Reassign Diamond</span>
        </div>
        <div class="modal-body">
            <div>
                <label class="modal-label">SKU: <span id="modalDiamondSku" class="text-semibold">—</span></label>
            </div>
            <div style="margin-top: 1rem;">
                <label class="modal-label" for="adminSelect">Select Admin</label>
                <select id="adminSelect" name="admin_id" class="modal-select">
                    <option value="">— Choose Admin —</option>
                    <?php $__currentLoopData = $admins ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($admin->id); ?>"><?php echo e($admin->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-modal-cancel" id="cancelReassign">Cancel</button>
            <button type="button" class="btn-modal btn-modal-primary" id="confirmReassign">Reassign</button>
        </div>
    </div>
</div>

<!-- Import Tutorial Modal -->
<div id="importModal" class="modal-overlay d-none">
    <div class="tutorial-modal">
        <div class="tutorial-modal-header">
            <i class="bi bi-upload"></i>
            <span>Import Diamonds - Tutorial</span>
            <button type="button" class="btn-close-modal" id="closeImportModal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="tutorial-modal-body">
            <div class="tutorial-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Download Sample Template</h4>
                    <p>Download our sample Excel template to see the correct format:</p>
                    <a href="<?php echo e(asset('uploads/diamonds_import_sample.csv')); ?>" class="btn-download" download>
                        <i class="bi bi-download"></i> Download Sample Template
                    </a>
                </div>
            </div>

            <div class="tutorial-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Required Fields</h4>
                    <p>Your Excel file MUST contain these columns:</p>
                    <div class="field-list">
                        <div class="field-item required">
                            <i class="bi bi-asterisk"></i>
                            <strong>lot_no</strong> - Unique lot number (e.g., 10001, L10001)
                        </div>
                        <div class="field-item required">
                            <i class="bi bi-asterisk"></i>
                            <strong>sku</strong> - Unique SKU identifier (e.g., DIA-001)
                        </div>
                    </div>
                </div>
            </div>

            <div class="tutorial-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Optional Fields</h4>
                    <div class="field-grid">
                        <span class="field-tag">material</span>
                        <span class="field-tag">cut</span>
                        <span class="field-tag">clarity</span>
                        <span class="field-tag">color</span>
                        <span class="field-tag">shape</span>
                        <span class="field-tag">weight</span>
                        <span class="field-tag">purchase_price</span>
                        <span class="field-tag">listing_price</span>
                        <span class="field-tag">diamond_type</span>
                    </div>
                    <p class="help-text">See sample file for all available fields</p>
                </div>
            </div>

            <div class="tutorial-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Important Rules</h4>
                    <ul class="rules-list">
                        <li><i class="bi bi-check-circle"></i> First row must contain column headers</li>
                        <li><i class="bi bi-check-circle"></i> lot_no and sku must be unique</li>
                        <li><i class="bi bi-check-circle"></i> Use YYYY-MM-DD format for dates</li>
                        <li><i class="bi bi-check-circle"></i> Numbers only for prices (no currency symbols)</li>
                        <li><i class="bi bi-check-circle"></i> Status must be "IN Stock" or "Sold"</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="tutorial-modal-footer">
            <button type="button" class="btn-modal btn-modal-cancel" id="cancelImport">Cancel</button>
            <button type="button" class="btn-modal btn-modal-primary" id="proceedImport">
                <i class="bi bi-upload"></i> Proceed to Upload
            </button>
        </div>
    </div>
</div>

<!-- Export Tutorial Modal -->
<div id="exportModal" class="modal-overlay d-none">
    <div class="tutorial-modal">
        <div class="tutorial-modal-header">
            <i class="bi bi-file-earmark-arrow-down"></i>
            <span>Export Diamonds - Information</span>
            <button type="button" class="btn-close-modal" id="closeExportModal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="tutorial-modal-body">
            <div class="tutorial-step">
                <div class="step-number">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div class="step-content">
                    <h4>What Will Be Exported?</h4>
                    <p>The export will include all diamonds based on your current filters with these columns:</p>
                </div>
            </div>

            <div class="export-columns">
                <div class="column-group">
                    <h5><i class="bi bi-card-list"></i> Basic Information</h5>
                    <ul>
                        <li>ID, Lot Number, SKU</li>
                        <li>Material, Cut, Clarity, Color</li>
                        <li>Shape, Measurement, Weight</li>
                    </ul>
                </div>
                <div class="column-group">
                    <h5><i class="bi bi-cash-coin"></i> Pricing Details</h5>
                    <ul>
                        <li>Purchase Price, Margin, Listing Price</li>
                        <li>Shipping Price, Duration Price</li>
                        <li>Sold Out Price, Profit</li>
                    </ul>
                </div>
                <div class="column-group">
                    <h5><i class="bi bi-calendar-check"></i> Dates & Status</h5>
                    <ul>
                        <li>Purchase Date, Sold Out Date</li>
                        <li>Status (IN Stock / Sold)</li>
                        <li>Duration Days, Sold Out Month</li>
                    </ul>
                </div>
                <div class="column-group">
                    <h5><i class="bi bi-person"></i> Additional Info</h5>
                    <ul>
                        <li>Barcode Number, Description, Note</li>
                        <li>Diamond Type, Assigned Admin</li>
                        <li>Created At, Updated At</li>
                    </ul>
                </div>
            </div>

            <div class="export-info-box">
                <i class="bi bi-lightbulb"></i>
                <div>
                    <strong>Pro Tip:</strong> Apply filters before exporting to download specific diamonds.
                    Your current filters will be respected in the export.
                </div>
            </div>
        </div>
        <div class="tutorial-modal-footer">
            <button type="button" class="btn-modal btn-modal-cancel" id="cancelExport">Cancel</button>
            <a href="<?php echo e(route('diamonds.export', request()->query())); ?>" class="btn-modal btn-modal-success"
                id="proceedExport">
                <i class="bi bi-download"></i> Download Excel
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Filters Functionality
        const toggleBtn = document.getElementById('toggleFilters');
        const filterForm = document.getElementById('filterForm');

        if (toggleBtn && filterForm) {

            // Set initial state to closed
            filterForm.style.display = 'none';
            toggleBtn.classList.add('collapsed');

            toggleBtn.addEventListener('click', function () {
                const isVisible = filterForm.style.display !== 'none';

                if (isVisible) {
                    filterForm.style.display = 'none';
                    toggleBtn.classList.add('collapsed');
                } else {
                    filterForm.style.display = 'block';
                    toggleBtn.classList.remove('collapsed');
                }
            });
        }

        // Admin Reassignment Logic
        const modal = document.getElementById('reassignModal');
        const adminSelect = document.getElementById('adminSelect');
        const cancelBtn = document.getElementById('cancelReassign');
        const confirmBtn = document.getElementById('confirmReassign');
        const modalDiamondSku = document.getElementById('modalDiamondSku');
        let currentDiamondId = null;

        // Open modal when reassign button clicked
        document.querySelectorAll('.btn-reassign').forEach(btn => {
            btn.addEventListener('click', function () {
                currentDiamondId = this.dataset.diamondId;
                const row = this.closest('tr');
                const sku = row.querySelector('td:nth-child(2) .text-semibold').textContent;

                modalDiamondSku.textContent = sku;
                adminSelect.value = '';
                modal.classList.remove('d-none');
            });
        });

        // Close modal
        cancelBtn?.addEventListener('click', () => {
            modal.classList.add('d-none');
            currentDiamondId = null;
        });

        // Close modal on overlay click
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('d-none');
                currentDiamondId = null;
            }
        });

        // Confirm reassignment
        confirmBtn?.addEventListener('click', async function () {
            const adminId = adminSelect.value;

            if (!adminId) {
                showAlert('Please select an admin', 'warning', 'Select Admin');
                return;
            }

            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Reassigning...';

            try {
                const response = await fetch(`/admin/diamonds/${currentDiamondId}/assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        admin_id: adminId
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Show success message
                    showAlert(data.message, 'success', 'Success');

                    // Close modal
                    modal.classList.add('d-none');

                    // Reload the page to see updated assignments
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message || 'Failed to reassign diamond', 'error', 'Error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred while reassigning', 'error', 'Error');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Reassign';
            }
        });
    });

    // Restock button with SweetAlert confirmation
    document.querySelectorAll('.restock-btn').forEach(button => {
        button.addEventListener('click', async function (e) {
            e.preventDefault();
            const sku = this.dataset.diamondSku;
            const form = this.closest('form');

            const confirmed = await showConfirm(
                `This will create a new copy of diamond ${sku} with status "IN Stock" while keeping the sold record intact.`,
                'Restock Diamond?',
                'Yes, Restock',
                'Cancel'
            );

            if (confirmed) {
                form.submit();
            }
        });
    });

    // Delete button with SweetAlert confirmation
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async function (e) {
            e.preventDefault();
            const form = this.closest('form');

            const confirmed = await showConfirm(
                'This action cannot be undone. All diamond data will be permanently deleted.',
                'Delete Diamond?',
                'Yes, Delete',
                'Cancel'
            );

            if (confirmed) {
                form.submit();
            }
        });
    });
</script>


<?php echo $__env->make('diamonds.partials._bulk-edit-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<link rel="stylesheet" href="<?php echo e(asset('css/bulk-edit.css')); ?>">


<script src="<?php echo e(asset('js/bulk-edit.js')); ?>"></script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/diamonds/index.blade.php ENDPATH**/ ?>