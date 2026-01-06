
<div class="lead-card" draggable="true" data-lead-id="<?php echo e($lead->id); ?>" data-status="<?php echo e($lead->status); ?>">

    <div class="lead-card-header">
        <div class="lead-platform-badge <?php echo e($lead->platform); ?>">
            <i class="bi <?php echo e($lead->platform_icon); ?>"></i>
        </div>
        <div style="flex: 1; margin-left: 0.75rem;">
            <div class="lead-name"><?php echo e($lead->name); ?></div>
            <?php if($lead->username): ?>
                <div class="lead-username">{{ $lead->username }}</div>
            <?php endif; ?>
        </div>
        <?php if($lead->unread_messages_count > 0): ?>
            <span class="lead-unread"><?php echo e($lead->unread_messages_count); ?></span>
        <?php endif; ?>
    </div>

    <div class="lead-card-meta">
        <span class="lead-score">
            <i class="bi bi-star-fill"></i>
            <?php echo e($lead->lead_score); ?>

        </span>
        <span class="lead-priority <?php echo e($lead->priority); ?>">
            <?php echo e(ucfirst($lead->priority)); ?>

        </span>
        <?php if($lead->isSlAOverdue()): ?>
            <span class="sla-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Overdue
            </span>
        <?php endif; ?>
        <span class="lead-time">
            <?php echo e($lead->last_contact_at?->diffForHumans() ?? $lead->created_at->diffForHumans()); ?>

        </span>
    </div>

    <div class="lead-card-footer">
        <?php if($lead->assignedAdmin): ?>
            <div class="lead-assigned">
                <span class="lead-avatar">
                    <?php echo e(substr($lead->assignedAdmin->name, 0, 2)); ?>

                </span>
                <?php echo e($lead->assignedAdmin->name); ?>

            </div>
        <?php else: ?>
            <div class="lead-assigned">
                <i class="bi bi-person-dash"></i>
                Unassigned
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/leads/partials/lead-card.blade.php ENDPATH**/ ?>