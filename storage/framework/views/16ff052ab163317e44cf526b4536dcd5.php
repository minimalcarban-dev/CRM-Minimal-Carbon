

<?php $__env->startSection('title', 'Leads Inbox'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        /* Kanban Board Styles */
        .leads-inbox-container {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Quick Stats Bar */
        .quick-stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .quick-stat-card {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quick-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        .quick-stat-icon.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .quick-stat-icon.success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .quick-stat-icon.warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        .quick-stat-icon.danger {
            background: linear-gradient(135deg, var(--danger), #dc2626);
        }

        .quick-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
        }

        .quick-stat-label {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 0.25rem;
        }

        /* Kanban Board */
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            min-height: 600px;
        }

        .kanban-column {
            background: var(--light-gray);
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
        }

        .kanban-column-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            background: white;
        }

        .column-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .column-count {
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .kanban-column.new .column-count {
            background: var(--primary);
        }

        .kanban-column.in-process .column-count {
            background: var(--info);
        }

        .kanban-column.completed .column-count {
            background: var(--success);
        }

        .kanban-column.lost .column-count {
            background: var(--gray);
        }

        .kanban-cards {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            min-height: 200px;
            padding: 0.5rem;
        }

        .kanban-cards.drag-over {
            background: rgba(99, 102, 241, 0.1);
            border-radius: 10px;
        }

        /* Lead Card */
        .lead-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 1px 3px var(--shadow);
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .lead-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-md);
            border-color: var(--primary);
        }

        .lead-card.dragging {
            opacity: 0.5;
            transform: scale(1.02);
        }

        .lead-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .lead-platform-badge {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: white;
        }

        .lead-platform-badge.facebook {
            background: #1877F2;
        }

        .lead-platform-badge.instagram {
            background: linear-gradient(135deg, #F58529, #DD2A7B, #8134AF);
        }

        .lead-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .lead-username {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .lead-card-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .lead-score {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--warning);
        }

        .lead-priority {
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .lead-priority.high {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .lead-priority.medium {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .lead-priority.low {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .lead-time {
            font-size: 0.75rem;
            color: var(--gray);
            margin-left: auto;
        }

        .lead-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border);
        }

        .lead-assigned {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .lead-avatar {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .lead-unread {
            background: var(--danger);
            color: white;
            padding: 0.1rem 0.4rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .sla-warning {
            color: var(--danger);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Filter Section */
        .filter-bar {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            border: 1px solid var(--border);
        }

        .filter-search {
            flex: 1;
            min-width: 200px;
            position: relative;
        }

        .filter-search input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .filter-search input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .filter-search i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .filter-select {
            padding: 0.6rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            background: white;
            min-width: 150px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Empty State */
        .kanban-empty {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        .kanban-empty i {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .kanban-board {
                grid-template-columns: 1fr;
            }

            .kanban-column {
                max-height: 400px;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="leads-inbox-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Leads Inbox</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-chat-dots"></i>
                        Leads Inbox
                    </h1>
                    <p class="page-subtitle">Manage conversations from Facebook & Instagram</p>
                </div>
                <div class="header-right">
                    <a href="<?php echo e(route('leads.analytics')); ?>" class="btn-secondary-custom">
                        <i class="bi bi-graph-up"></i>
                        <span>Analytics</span>
                    </a>
                    <button type="button" class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#newLeadModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>New Lead</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats-bar">
            <div class="quick-stat-card">
                <div class="quick-stat-icon primary">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="quick-stat-value"><?php echo e($stats['today_new'] ?? 0); ?></div>
                    <div class="quick-stat-label">New Today</div>
                </div>
            </div>
            <div class="quick-stat-card">
                <div class="quick-stat-icon success">
                    <i class="bi bi-clock"></i>
                </div>
                <div>
                    <div class="quick-stat-value"><?php echo e($stats['avg_response_time'] ?? '—'); ?></div>
                    <div class="quick-stat-label">Avg Response</div>
                </div>
            </div>
            <div class="quick-stat-card">
                <div class="quick-stat-icon warning">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <div class="quick-stat-value"><?php echo e($stats['conversion_rate'] ?? 0); ?>%</div>
                    <div class="quick-stat-label">Conversion Rate</div>
                </div>
            </div>
            <div class="quick-stat-card">
                <div class="quick-stat-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="quick-stat-value"><?php echo e($counts['overdue_sla'] ?? 0); ?></div>
                    <div class="quick-stat-label">SLA Overdue</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-search">
                <i class="bi bi-search"></i>
                <input type="text" id="leadSearch" placeholder="Search leads..." value="<?php echo e(request('search')); ?>">
            </div>
            <select class="filter-select" id="filterPlatform">
                <option value="">All Platforms</option>
                <option value="facebook" <?php echo e(request('platform') === 'facebook' ? 'selected' : ''); ?>>Facebook</option>
                <option value="instagram" <?php echo e(request('platform') === 'instagram' ? 'selected' : ''); ?>>Instagram</option>
            </select>
            <select class="filter-select" id="filterPriority">
                <option value="">All Priorities</option>
                <option value="high" <?php echo e(request('priority') === 'high' ? 'selected' : ''); ?>>High</option>
                <option value="medium" <?php echo e(request('priority') === 'medium' ? 'selected' : ''); ?>>Medium</option>
                <option value="low" <?php echo e(request('priority') === 'low' ? 'selected' : ''); ?>>Low</option>
            </select>
            <?php if(auth('admin')->user()->is_super): ?>
                <select class="filter-select" id="filterAgent">
                    <option value="">All Agents</option>
                    <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($agent->id); ?>" <?php echo e(request('assigned_to') == $agent->id ? 'selected' : ''); ?>>
                            <?php echo e($agent->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php endif; ?>
        </div>

        <!-- Kanban Board -->
        <div class="kanban-board">
            <!-- New Column -->
            <div class="kanban-column new" data-status="new">
                <div class="kanban-column-header">
                    <span class="column-title">
                        <i class="bi bi-inbox"></i> New
                    </span>
                    <span class="column-count"><?php echo e($kanbanData['new']->count()); ?></span>
                </div>
                <div class="kanban-cards" data-status="new" id="column-new">
                    <?php $__currentLoopData = $kanbanData['new']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('leads.partials.lead-card', ['lead' => $lead], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="kanban-empty" style="<?php echo e($kanbanData['new']->count() > 0 ? 'display: none;' : ''); ?>">
                        <i class="bi bi-inbox"></i>
                        <p>No new leads</p>
                    </div>
                </div>
            </div>

            <!-- In Process Column -->
            <div class="kanban-column in-process" data-status="in_process">
                <div class="kanban-column-header">
                    <span class="column-title">
                        <i class="bi bi-hourglass-split"></i> In Process
                    </span>
                    <span class="column-count"><?php echo e($kanbanData['in_process']->count()); ?></span>
                </div>
                <div class="kanban-cards" data-status="in_process" id="column-in_process">
                    <?php $__currentLoopData = $kanbanData['in_process']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('leads.partials.lead-card', ['lead' => $lead], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="kanban-empty" style="<?php echo e($kanbanData['in_process']->count() > 0 ? 'display: none;' : ''); ?>">
                        <i class="bi bi-hourglass"></i>
                        <p>No leads in process</p>
                    </div>
                </div>
            </div>

            <!-- Completed Column -->
            <div class="kanban-column completed" data-status="completed">
                <div class="kanban-column-header">
                    <span class="column-title">
                        <i class="bi bi-check-circle"></i> Completed
                    </span>
                    <span class="column-count"><?php echo e($kanbanData['completed']->count()); ?></span>
                </div>
                <div class="kanban-cards" data-status="completed" id="column-completed">
                    <?php $__currentLoopData = $kanbanData['completed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('leads.partials.lead-card', ['lead' => $lead], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="kanban-empty" style="<?php echo e($kanbanData['completed']->count() > 0 ? 'display: none;' : ''); ?>">
                        <i class="bi bi-check2-all"></i>
                        <p>No completed leads</p>
                    </div>
                </div>
            </div>

            <!-- Lost Column -->
            <div class="kanban-column lost" data-status="lost">
                <div class="kanban-column-header">
                    <span class="column-title">
                        <i class="bi bi-x-circle"></i> Lost
                    </span>
                    <span class="column-count"><?php echo e($kanbanData['lost']->count()); ?></span>
                </div>
                <div class="kanban-cards" data-status="lost" id="column-lost">
                    <?php $__currentLoopData = $kanbanData['lost']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('leads.partials.lead-card', ['lead' => $lead], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="kanban-empty" style="<?php echo e($kanbanData['lost']->count() > 0 ? 'display: none;' : ''); ?>">
                        <i class="bi bi-x-circle"></i>
                        <p>No lost leads</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('leads.partials.new-lead-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Drag and Drop
            const cards = document.querySelectorAll('.lead-card');
            const columns = document.querySelectorAll('.kanban-cards');

            cards.forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });

            columns.forEach(column => {
                column.addEventListener('dragover', handleDragOver);
                column.addEventListener('dragenter', handleDragEnter);
                column.addEventListener('dragleave', handleDragLeave);
                column.addEventListener('drop', handleDrop);
            });

            function handleDragStart(e) {
                e.target.classList.add('dragging');
                e.dataTransfer.setData('text/plain', e.target.dataset.leadId);
            }

            function handleDragEnd(e) {
                e.target.classList.remove('dragging');
            }

            function handleDragOver(e) {
                e.preventDefault();
            }

            function handleDragEnter(e) {
                e.target.closest('.kanban-cards')?.classList.add('drag-over');
            }

            function handleDragLeave(e) {
                if (!e.relatedTarget?.closest('.kanban-cards')) {
                    e.target.closest('.kanban-cards')?.classList.remove('drag-over');
                }
            }

            async function handleDrop(e) {
                e.preventDefault();
                const column = e.target.closest('.kanban-cards');
                column?.classList.remove('drag-over');

                const leadId = e.dataTransfer.getData('text/plain');
                const newStatus = column.dataset.status;

                if (!leadId || !newStatus) return;

                const card = document.querySelector(`[data-lead-id="${leadId}"]`);
                if (card) {
                    // Get the source column before moving
                    const sourceColumn = card.closest('.kanban-cards');
                    
                    // Hide empty state in target column if exists
                    const targetEmpty = column.querySelector('.kanban-empty');
                    if (targetEmpty) {
                        targetEmpty.style.display = 'none';
                    }
                    
                    // Move the card - insert at beginning, not end
                    column.insertBefore(card, column.firstChild);

                    // Show empty state in source column if now empty
                    updateEmptyStates();

                    // Update status via AJAX
                    try {
                        const response = await fetch(`/admin/leads/${leadId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ status: newStatus }),
                        });

                        if (!response.ok) {
                            throw new Error('Failed to update status');
                        }

                        // Update column counts
                        updateColumnCounts();
                    } catch (error) {
                        console.error('Error updating lead status:', error);
                        alert('Failed to update lead status');
                    }
                }
            }

            function updateColumnCounts() {
                columns.forEach(column => {
                    const count = column.querySelectorAll('.lead-card').length;
                    const header = column.closest('.kanban-column').querySelector('.column-count');
                    if (header) header.textContent = count;
                });
            }

            function updateEmptyStates() {
                columns.forEach(column => {
                    const cards = column.querySelectorAll('.lead-card').length;
                    const emptyState = column.querySelector('.kanban-empty');
                    
                    if (emptyState) {
                        emptyState.style.display = cards === 0 ? 'block' : 'none';
                    }
                });
            }

            // Lead card click - navigate to detail
            cards.forEach(card => {
                card.addEventListener('click', function (e) {
                    if (e.target.tagName !== 'BUTTON') {
                        window.location.href = `/admin/leads/${this.dataset.leadId}`;
                    }
                });
            });

            // Filter handling
            const searchInput = document.getElementById('leadSearch');
            const filterPlatform = document.getElementById('filterPlatform');
            const filterPriority = document.getElementById('filterPriority');
            const filterAgent = document.getElementById('filterAgent');

            function applyFilters() {
                const params = new URLSearchParams();
                if (searchInput?.value) params.set('search', searchInput.value);
                if (filterPlatform?.value) params.set('platform', filterPlatform.value);
                if (filterPriority?.value) params.set('priority', filterPriority.value);
                if (filterAgent?.value) params.set('assigned_to', filterAgent.value);

                window.location.href = '<?php echo e(route("leads.index")); ?>?' + params.toString();
            }

            let searchTimeout;
            searchInput?.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 500);
            });

            filterPlatform?.addEventListener('change', applyFilters);
            filterPriority?.addEventListener('change', applyFilters);
            filterAgent?.addEventListener('change', applyFilters);
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/leads/index.blade.php ENDPATH**/ ?>