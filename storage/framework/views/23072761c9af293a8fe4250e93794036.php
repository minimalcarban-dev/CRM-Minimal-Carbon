

<?php $__env->startSection('title', 'Lead - ' . $lead->name); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .lead-detail-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .lead-detail-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 1.5rem;
        }

        /* Lead Header Card */
        .lead-header-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .lead-header-top {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .lead-profile-pic {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .lead-profile-pic img {
            width: 100%;
            height: 100%;
            border-radius: 16px;
            object-fit: cover;
        }

        .lead-info-main {
            flex: 1;
        }

        .lead-name-large {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .platform-badge-large {
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }

        .platform-badge-large.facebook {
            background: #1877F2;
        }

        .platform-badge-large.instagram {
            background: linear-gradient(135deg, #F58529, #DD2A7B, #8134AF);
        }

        .lead-username-large {
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }

        .lead-contact-info {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .contact-item i {
            color: var(--primary);
        }

        .lead-actions-top {
            display: flex;
            gap: 0.5rem;
        }

        /* Chat Container */
        .chat-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px var(--shadow);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 280px);
            min-height: 500px;
        }

        .chat-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message-bubble {
            max-width: 75%;
            padding: 0.875rem 1.125rem;
            border-radius: 16px;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .message-bubble.incoming {
            background: var(--light-gray);
            color: var(--dark);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .message-bubble.outgoing {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .message-status {
            font-size: 0.8rem;
        }

        /* Quick Replies */
        .quick-replies {
            padding: 0.75rem 1rem;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .quick-reply-btn {
            padding: 0.4rem 0.75rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: white;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-reply-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Chat Input */
        .chat-input-container {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
        }

        .chat-input-container textarea {
            flex: 1;
            resize: none;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            max-height: 120px;
        }

        .chat-input-container textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .send-btn {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: transform 0.2s;
        }

        .send-btn:hover {
            transform: scale(1.05);
        }

        /* Sidebar */
        .sidebar-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 1px 3px var(--shadow);
            margin-bottom: 1rem;
        }

        .sidebar-card-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-card-title i {
            color: var(--primary);
        }

        /* Status Selector */
        .status-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .status-btn {
            padding: 0.5rem;
            border-radius: 8px;
            border: 2px solid var(--border);
            background: white;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .status-btn:hover {
            border-color: var(--primary);
        }

        .status-btn.active {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        /* Score Bar */
        .score-bar-container {
            margin-bottom: 1rem;
        }

        .score-bar {
            height: 8px;
            background: var(--light-gray);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .score-bar-fill {
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 4px;
            transition: width 0.3s;
        }

        .score-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Activity Timeline */
        .activity-timeline {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .activity-icon.primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .activity-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .activity-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .activity-icon.info {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-text {
            font-size: 0.85rem;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .activity-meta {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .lead-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="lead-detail-container">
        <!-- Breadcrumb -->
        <div class="page-header" style="padding: 1rem 1.5rem;">
            <div class="breadcrumb-nav">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="breadcrumb-link">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
                <i class="bi bi-chevron-right breadcrumb-separator"></i>
                <a href="<?php echo e(route('leads.index')); ?>" class="breadcrumb-link">Leads Inbox</a>
                <i class="bi bi-chevron-right breadcrumb-separator"></i>
                <span class="breadcrumb-current"><?php echo e($lead->name); ?></span>
            </div>
        </div>

        <!-- Lead Header -->
        <div class="lead-header-card">
            <div class="lead-header-top">
                <div class="lead-profile-pic">
                    <?php if($lead->profile_pic_url): ?>
                        <img src="<?php echo e($lead->profile_pic_url); ?>" alt="<?php echo e($lead->name); ?>">
                    <?php else: ?>
                        <?php echo e(substr($lead->name, 0, 2)); ?>

                    <?php endif; ?>
                </div>
                <div class="lead-info-main">
                    <div class="lead-name-large">
                        <?php echo e($lead->name); ?>

                        <span class="platform-badge-large <?php echo e($lead->platform); ?>">
                            <i class="bi <?php echo e($lead->platform_icon); ?>"></i> <?php echo e(ucfirst($lead->platform)); ?>

                        </span>
                    </div>
                    <?php if($lead->username): ?>
                        <div class="lead-username-large">{{ $lead->username }}</div>
                    <?php endif; ?>
                    <div class="lead-contact-info">
                        <?php if($lead->email): ?>
                            <span class="contact-item">
                                <i class="bi bi-envelope"></i> <?php echo e($lead->email); ?>

                            </span>
                        <?php endif; ?>
                        <?php if($lead->phone): ?>
                            <span class="contact-item">
                                <i class="bi bi-telephone"></i> <?php echo e($lead->phone); ?>

                            </span>
                        <?php endif; ?>
                        <span class="contact-item">
                            <i class="bi bi-calendar"></i> First contact:
                            <?php echo e($lead->first_contact_at?->format('M d, Y') ?? 'N/A'); ?>

                        </span>
                    </div>
                </div>
                <div class="lead-actions-top">
                    <a href="<?php echo e(route('leads.index')); ?>" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="lead-detail-grid">
            <!-- Main Content - Chat -->
            <div>
                <div class="chat-container">
                    <div class="chat-header">
                        <strong>Conversation</strong>
                        <span style="color: var(--gray); font-size: 0.85rem;">
                            <?php echo e($messages->count()); ?> messages
                        </span>
                    </div>

                    <div class="chat-messages" id="chatMessages">
                        <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="message-bubble <?php echo e($message->direction); ?>">
                                <?php echo nl2br(e($message->content)); ?>

                                <div class="message-time">
                                    <?php echo e($message->created_at->format('M d, g:i A')); ?>

                                    <?php if($message->isOutgoing()): ?>
                                        <i class="<?php echo e($message->status_icon); ?>"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div style="text-align: center; padding: 3rem; color: var(--gray);">
                                <i class="bi bi-chat-dots"
                                    style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                                <p>No messages yet</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Replies -->
                    <?php if($templates->count() > 0): ?>
                        <div class="quick-replies">
                            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="quick-reply-btn" data-template="<?php echo e($template->content); ?>">
                                    <i class="<?php echo e($template->category_icon); ?>"></i>
                                    <?php echo e($template->name); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Chat Input -->
                    <div class="chat-input-container">
                        <button type="button" class="btn-secondary-custom" style="padding: 0.6rem;">
                            <i class="bi bi-paperclip"></i>
                        </button>
                        <textarea id="messageInput" placeholder="Type your message..." rows="1"></textarea>
                        <button type="button" class="send-btn" id="sendMessageBtn">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lead-sidebar">
                <!-- Status -->
                <div class="sidebar-card">
                    <div class="sidebar-card-title">
                        <i class="bi bi-flag"></i> Status
                    </div>
                    <div class="status-selector">
                        <?php $__currentLoopData = ['new', 'in_process', 'completed', 'lost']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" class="status-btn <?php echo e($lead->status === $status ? 'active' : ''); ?>"
                                data-status="<?php echo e($status); ?>">
                                <?php echo e(ucfirst(str_replace('_', ' ', $status))); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Lead Score -->
                <div class="sidebar-card">
                    <div class="sidebar-card-title">
                        <i class="bi bi-star"></i> Lead Score
                    </div>
                    <div class="score-bar-container">
                        <div class="score-bar">
                            <div class="score-bar-fill" style="width: <?php echo e($lead->lead_score); ?>%;"></div>
                        </div>
                        <div class="score-label">
                            <span>Score</span>
                            <span><strong><?php echo e($lead->lead_score); ?></strong>/100</span>
                        </div>
                    </div>
                </div>

                <!-- Assignment -->
                <div class="sidebar-card">
                    <div class="sidebar-card-title">
                        <i class="bi bi-person"></i> Assignment
                    </div>
                    <select class="form-control" id="assignAgent">
                        <option value="">Unassigned</option>
                        <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($agent->id); ?>" <?php echo e($lead->assigned_to == $agent->id ? 'selected' : ''); ?>>
                                <?php echo e($agent->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Priority -->
                <div class="sidebar-card">
                    <div class="sidebar-card-title">
                        <i class="bi bi-exclamation-circle"></i> Priority
                    </div>
                    <select class="form-control" id="leadPriority">
                        <option value="low" <?php echo e($lead->priority === 'low' ? 'selected' : ''); ?>>Low</option>
                        <option value="medium" <?php echo e($lead->priority === 'medium' ? 'selected' : ''); ?>>Medium</option>
                        <option value="high" <?php echo e($lead->priority === 'high' ? 'selected' : ''); ?>>High</option>
                    </select>
                </div>

                <!-- SLA -->
                <?php if($lead->sla_deadline): ?>
                    <div class="sidebar-card">
                        <div class="sidebar-card-title">
                            <i class="bi bi-clock"></i> SLA Deadline
                        </div>
                        <p
                            style="margin: 0; font-size: 0.9rem; color: <?php echo e($lead->isSlAOverdue() ? 'var(--danger)' : 'var(--dark)'); ?>;">
                            <?php if($lead->isSlAOverdue()): ?>
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            <?php endif; ?>
                            <?php echo e($lead->getSlATimeRemaining()); ?>

                        </p>
                    </div>
                <?php endif; ?>

                <!-- Activity Timeline -->
                <div class="sidebar-card">
                    <div class="sidebar-card-title">
                        <i class="bi bi-activity"></i> Activity
                    </div>
                    <div class="activity-timeline">
                        <?php $__empty_1 = true; $__currentLoopData = $lead->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo e($activity->color); ?>">
                                    <i class="bi <?php echo e($activity->icon); ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text"><?php echo e($activity->description); ?></div>
                                    <div class="activity-meta">
                                        <?php echo e($activity->created_at->diffForHumans()); ?>

                                        <?php if($activity->admin): ?>
                                            by <?php echo e($activity->admin->name); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p style="color: var(--gray); font-size: 0.85rem;">No activity yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const leadId = <?php echo e($lead->id); ?>;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Scroll to bottom of chat
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Send message
            const messageInput = document.getElementById('messageInput');
            const sendBtn = document.getElementById('sendMessageBtn');

            sendBtn.addEventListener('click', sendMessage);
            messageInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            async function sendMessage() {
                const message = messageInput.value.trim();
                if (!message) return;

                sendBtn.disabled = true;

                try {
                    const response = await fetch(`/admin/leads/${leadId}/message`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ message }),
                    });

                    let data;
                    try {
                        data = await response.json();
                    } catch (parseError) {
                        // If JSON parsing fails, show generic error
                        throw new Error('Server error: ' + response.status);
                    }

                    if (response.ok && data.success) {
                        // Add message to chat
                        const bubble = document.createElement('div');
                        bubble.className = 'message-bubble outgoing';
                        bubble.innerHTML = `
                                ${message.replace(/\n/g, '<br>')}
                                <div class="message-time">
                                    Just now <i class="bi bi-check"></i>
                                </div>
                            `;
                        chatMessages.appendChild(bubble);
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                        messageInput.value = '';

                        // Remove "No messages yet" if present
                        const emptyState = chatMessages.querySelector('.no-messages');
                        if (emptyState) emptyState.remove();
                    } else {
                        alert(data.error || 'Failed to send message');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Failed to send message');
                }

                sendBtn.disabled = false;
            }

            // Quick replies
            document.querySelectorAll('.quick-reply-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    messageInput.value = this.dataset.template;
                    messageInput.focus();
                });
            });

            // Status change
            document.querySelectorAll('.status-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const status = this.dataset.status;

                    const response = await fetch(`/admin/leads/${leadId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ status }),
                    });

                    if (response.ok) {
                        document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                    }
                });
            });

            // Assignment change
            document.getElementById('assignAgent')?.addEventListener('change', async function () {
                await fetch(`/admin/leads/${leadId}/assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ agent_id: this.value || null }),
                });
            });

            // Real-time incoming messages via Pusher/Echo
            if (typeof Echo !== 'undefined') {
                // Listen on the general leads channel (for super admins)
                Echo.private('leads.inbox')
                    .listen('.lead.message.new', function (e) {
                        if (e.lead_id === leadId && e.message.direction === 'incoming') {
                            appendIncomingMessage(e.message);
                        }
                    });

                // Also listen on agent-specific channel
                const adminId = window.authAdminId;
                if (adminId) {
                    Echo.private(`admin.leads.${adminId}`)
                        .listen('.lead.message.new', function (e) {
                            if (e.lead_id === leadId && e.message.direction === 'incoming') {
                                appendIncomingMessage(e.message);
                            }
                        });
                }
            }

            function appendIncomingMessage(message) {
                // Check if message already exists
                if (document.querySelector(`[data-message-id="${message.id}"]`)) {
                    return;
                }

                const bubble = document.createElement('div');
                bubble.className = 'message-bubble incoming';
                bubble.setAttribute('data-message-id', message.id);
                bubble.innerHTML = `
                            ${message.content.replace(/\n/g, '<br>')}
                            <div class="message-time">
                                Just now
                            </div>
                        `;
                chatMessages.appendChild(bubble);
                chatMessages.scrollTop = chatMessages.scrollHeight;

                // Play notification sound (optional)
                // new Audio('/sounds/notification.mp3').play();
            }
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/leads/show.blade.php ENDPATH**/ ?>