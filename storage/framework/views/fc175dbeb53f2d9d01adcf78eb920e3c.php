

<?php $__env->startSection('title', 'Background Jobs History'); ?>

<?php $__env->startSection('content'); ?>
    <div class="job-history-container">
        <div class="page-header">
            <h1><i class="bi bi-clock-history"></i> Background Jobs History</h1>
            <a href="<?php echo e(route('diamond.index')); ?>" class="btn-back">
                <i class="bi bi-arrow-left"></i> Back to Diamonds
            </a>
        </div>

        <div class="jobs-table-card">
            <table class="jobs-table">
                <thead>
                    <tr>
                        <th>Job ID</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Rows</th>
                        <th>Started</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong>#<?php echo e($job->id); ?></strong></td>
                            <td>
                                <?php if($job->type === 'import'): ?>
                                    <span class="type-badge import">
                                        <i class="bi bi-cloud-upload"></i> Import
                                    </span>
                                <?php else: ?>
                                    <span class="type-badge export">
                                        <i class="bi bi-cloud-download"></i> Export
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($job->status === 'queued'): ?>
                                    <span class="status-badge queued">⏳ Queued</span>
                                <?php elseif($job->status === 'processing'): ?>
                                    <span class="status-badge processing">⚙️ Processing</span>
                                <?php elseif($job->status === 'completed'): ?>
                                    <span class="status-badge completed">✅ Completed</span>
                                <?php elseif($job->status === 'failed'): ?>
                                    <span class="status-badge failed">❌ Failed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="progress-mini">
                                    <div class="progress-mini-bar" style="width: <?php echo e($job->progress_percentage); ?>%;"></div>
                                    <span><?php echo e($job->progress_percentage); ?>%</span>
                                </div>
                            </td>
                            <td>
                                <div class="rows-info">
                                    <span class="success-count">✅ <?php echo e(number_format($job->successful_rows)); ?></span>
                                    <?php if($job->failed_rows > 0): ?>
                                        <span class="failed-count">❌ <?php echo e(number_format($job->failed_rows)); ?></span>
                                    <?php endif; ?>
                                    <span class="total-count">/ <?php echo e(number_format($job->total_rows)); ?></span>
                                </div>
                            <td>
                                <div class="time-info">
                                    <?php echo e($job->created_at->format('M d, Y')); ?><br>
                                    <small><?php echo e($job->created_at->format('h:i:s A')); ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if($job->started_at && $job->completed_at): ?>
                                    <?php echo e($job->started_at->diffForHumans($job->completed_at, true)); ?>

                                <?php elseif($job->started_at): ?>
                                    <?php echo e($job->started_at->diffForHumans()); ?>

                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="<?php echo e(route('diamond.job.status', $job->id)); ?>" class="btn-action view"
                                        title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if($job->status === 'completed' && $job->type === 'export'): ?>
                                        <a href="<?php echo e(route('diamond.job.download', $job->id)); ?>" class="btn-action download"
                                            title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if($job->status === 'completed' && $job->type === 'import' && $job->failed_rows > 0): ?>
                                        <a href="<?php echo e(route('diamond.download-errors', basename($job->error_file_path))); ?>"
                                            class="btn-action error" title="Download Errors">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>No background jobs found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <?php echo e($jobs->links()); ?>

        </div>
    </div>

    <style>
        .job-history-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .btn-back {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            background: white;
            color: #6366f1;
            border: 2px solid #6366f1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #f8f9ff;
        }

        .jobs-table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .jobs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .jobs-table thead {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .jobs-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .jobs-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .jobs-table tbody tr:hover {
            background: #f8fafc;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .type-badge.import {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-badge.export {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge.queued {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-badge.completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .progress-mini {
            position: relative;
            width: 100px;
            height: 24px;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-mini-bar {
            height: 100%;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            transition: width 0.3s;
        }

        .progress-mini span {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: #1e293b;
        }

        .rows-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.875rem;
        }

        .success-count {
            color: #065f46;
        }

        .failed-count {
            color: #991b1b;
        }

        .total-count {
            color: #6b7280;
        }

        .time-info {
            font-size: 0.875rem;
        }

        .time-info small {
            color: #6b7280;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-action.view {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-action.view:hover {
            background: #bfdbfe;
        }

        .btn-action.download {
            background: #d1fae5;
            color: #065f46;
        }

        .btn-action.download:hover {
            background: #a7f3d0;
        }

        .btn-action.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-action.error:hover {
            background: #fecaca;
        }

        .empty-state {
            text-align: center;
            padding: 3rem !important;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 1.125rem;
            margin: 0;
        }

        .pagination-container {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/diamonds/job-history.blade.php ENDPATH**/ ?>