@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="notifications-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Notifications</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-bell-fill"></i>
                        Notifications
                    </h1>
                    <p class="page-subtitle">Stay updated with the latest alerts and messages</p>
                </div>
                <div class="header-spacer">
                    <a href="{{ route('admin.dashboard') }}" class="btn-primary-custom" style="margin-bottom: 15px;">
                        <i class="bi bi-arrow-left"></i>
                        Back to Dashboard
                    </a>
                    <div class="header-right">
                        @if($notifications->count() > 0)
                            <button class="btn-primary-custom" id="markAllRead">
                                <i class="bi bi-check2-all"></i>
                                <span>Mark All Read</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-bell"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Notifications</div>
                    <div class="stat-value">{{ $notifications->total() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-inbox"></i> All Time
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-envelope-open"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Unread</div>
                    <div class="stat-value">{{ $notifications->where('read_at', null)->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-exclamation-circle"></i> Pending
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Read</div>
                    <div class="stat-value">{{ $notifications->where('read_at', '!=', null)->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-check2"></i> Completed
                    </div>
                </div>
            </div>
        </div>

        @if($notifications->count() > 0)
            <!-- Notifications List -->
            <div class="notifications-list">
                @foreach($notifications as $notification)
                    <div class="notification-card {{ $notification->read_at ? 'read' : 'unread' }}">
                        <div class="notification-indicator"></div>

                        <div class="notification-icon">
                            @if(isset($notification->data['type']))
                                @if($notification->data['type'] === 'order')
                                    <i class="bi bi-basket"></i>
                                @elseif($notification->data['type'] === 'user')
                                    <i class="bi bi-person"></i>
                                @elseif($notification->data['type'] === 'system')
                                    <i class="bi bi-gear"></i>
                                @else
                                    <i class="bi bi-bell"></i>
                                @endif
                            @else
                                <i class="bi bi-bell"></i>
                            @endif
                        </div>

                        <div class="notification-content">
                            <div class="notification-header">
                                <h4 class="notification-title">{{ $notification->data['title'] ?? 'Notification' }}</h4>
                                <div class="notification-meta">
                                    @if(!$notification->read_at)
                                        <span class="badge-status unread">
                                            <i class="bi bi-circle-fill"></i>
                                            Unread
                                        </span>
                                    @else
                                        <span class="badge-status read">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Read
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="notification-message">
                                {{ $notification->data['message'] ?? '' }}
                            </p>

                            @if(isset($notification->data['details']))
                                <div class="notification-details">
                                    @foreach($notification->data['details'] as $key => $value)
                                        <div class="detail-item">
                                            <span class="detail-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="detail-value">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="notification-footer">
                                <div class="notification-time">
                                    <i class="bi bi-clock"></i>
                                    <span class="time-relative">{{ $notification->created_at->diffForHumans() }}</span>
                                    <span class="time-separator">•</span>
                                    <span class="time-exact">{{ $notification->created_at->format('M d, Y h:i A') }}</span>
                                </div>

                                <div class="notification-actions">
                                    @if(!$notification->read_at)
                                        <button class="action-btn mark-read-btn" data-notification-id="{{ $notification->id }}"
                                            title="Mark as read">
                                            <i class="bi bi-check-circle"></i>
                                            <span>Mark as Read</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="pagination-container">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 class="empty-title">No notifications yet</h3>
                <p class="empty-description">You're all caught up! New notifications will appear here.</p>
                <a href="{{ route('admin.dashboard') }}" class="btn-primary-custom">
                    <i class="bi bi-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        @endif
    </div>

    <!-- Toast Notification -->
    <div class="toast-notification" id="toast-notification">
        <i class="bi bi-check-circle-fill"></i>
        <span id="toast-message">Success!</span>
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
            --shadow: rgba(0, 0, 0, 0.05);
            --shadow-md: rgba(0, 0, 0, 0.1);
            --shadow-lg: rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
        }

        .notifications-management-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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
            box-shadow: 0 1px 3px var(--shadow);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px var(--shadow-md);
        }

        .stat-card-primary {
            border-color: rgba(99, 102, 241, 0.1);
        }

        .stat-card-primary:hover {
            border-color: var(--primary);
        }

        .stat-card-warning {
            border-color: rgba(245, 158, 11, 0.1);
        }

        .stat-card-warning:hover {
            border-color: var(--warning);
        }

        .stat-card-success {
            border-color: rgba(16, 185, 129, 0.1);
        }

        .stat-card-success:hover {
            border-color: var(--success);
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

        .stat-card-primary .stat-icon {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
        }

        .stat-card-warning .stat-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            color: var(--warning);
        }

        .stat-card-success .stat-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-trend {
            font-size: 0.875rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Notifications List */
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .notification-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
            display: flex;
            gap: 1.25rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            border: 2px solid var(--border);
        }

        .notification-card:hover {
            box-shadow: 0 4px 12px var(--shadow-md);
            transform: translateY(-2px);
        }

        .notification-card.unread {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.02), white);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .notification-indicator {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: transparent;
            transition: all 0.3s;
        }

        .notification-card.unread .notification-indicator {
            background: var(--primary);
        }

        .notification-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .notification-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge-status {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            white-space: nowrap;
        }

        .badge-status.unread {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .badge-status.unread i {
            font-size: 0.5rem;
        }

        .badge-status.read {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .notification-message {
            color: var(--gray);
            margin: 0 0 1rem 0;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .notification-details {
            background: var(--light-gray);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item {
            display: flex;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .detail-label {
            font-weight: 600;
            color: var(--dark);
        }

        .detail-value {
            color: var(--gray);
        }

        .notification-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .notification-time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
            flex-wrap: wrap;
        }

        .notification-time i {
            color: var(--primary);
        }

        .time-relative {
            font-weight: 500;
        }

        .time-separator {
            color: var(--border);
        }

        .time-exact {
            color: #9ca3af;
        }

        .notification-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 2px solid var(--border);
            background: white;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 16px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 1px 3px var(--shadow);
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
        .pagination-container {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
            display: flex;
            justify-content: center;
        }

        /* Toast */
        .toast-notification {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
            transform: translateY(150%);
            transition: transform 0.3s ease;
            z-index: 9999;
        }

        .toast-notification.show {
            transform: translateY(0);
        }

        .toast-notification i {
            font-size: 1.25rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .notifications-management-container {
                padding: 0;
            }

            .page-header {
                border-radius: 12px;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
            }

            .header-right {
                width: 100%;
            }

            .btn-primary-custom {
                width: 100%;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .notification-card {
                flex-direction: column;
                padding: 1.25rem;
            }

            .notification-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .notification-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .notification-time {
                flex-direction: column;
                align-items: flex-start;
            }

            .time-separator {
                display: none;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const toastMessage = document.getElementById('toast-message');

            toastMessage.textContent = message;

            if (type === 'error') {
                toast.style.background = 'var(--danger)';
                toast.querySelector('i').className = 'bi bi-x-circle-fill';
            } else {
                toast.style.background = 'var(--success)';
                toast.querySelector('i').className = 'bi bi-check-circle-fill';
            }

            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Mark all notifications as read
        document.getElementById('markAllRead')?.addEventListener('click', async function () {
            this.disabled = true;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Processing...</span>';

            try {
                const response = await fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showToast('All notifications marked as read', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error('Failed to mark notifications');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Failed to mark notifications as read', 'error');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-check2-all"></i><span>Mark All Read</span>';
            }
        });

        // Mark individual notification as read
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const notificationId = this.dataset.notificationId;
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Processing...</span>';

                try {
                    const response = await fetch(`/admin/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast('Notification marked as read', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 800);
                    } else {
                        throw new Error('Failed to mark notification');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Failed to mark notification as read', 'error');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-circle"></i><span>Mark as Read</span>';
                }
            });
        });
    </script>
@endsection