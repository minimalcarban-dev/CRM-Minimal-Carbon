@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="notif-container">

        {{-- ── HEADER ── --}}
        <div class="notif-header">
            <div class="notif-header-left">
                <div class="breadcrumb-nav">
                    <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right breadcrumb-sep"></i>
                    <span class="breadcrumb-current">Notifications</span>
                </div>
                <h1 class="notif-title">
                    <i class="bi bi-bell-fill"></i> Notifications
                </h1>
                <p class="notif-subtitle">Stay updated with the latest alerts and messages</p>
            </div>

            <div class="notif-header-right">
                {{-- Filter Pills --}}
                <div class="filter-pills">
                    <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
                        class="filter-pill {{ ($filter ?? 'all') === 'all' ? 'active' : '' }}">All</a>
                    <a href="{{ route('notifications.index', ['filter' => 'created']) }}"
                        class="filter-pill {{ ($filter ?? 'all') === 'created' ? 'active' : '' }}">Created</a>
                    <a href="{{ route('notifications.index', ['filter' => 'cancelled']) }}"
                        class="filter-pill {{ ($filter ?? 'all') === 'cancelled' ? 'active' : '' }}">Cancelled</a>
                    <a href="{{ route('notifications.index', ['filter' => 'updated']) }}"
                        class="filter-pill {{ ($filter ?? 'all') === 'updated' ? 'active' : '' }}">Updated</a>
                    <a href="{{ route('notifications.index', ['filter' => 'other']) }}"
                        class="filter-pill {{ ($filter ?? 'all') === 'other' ? 'active' : '' }}">Other</a>
                </div>

                {{-- Inline stats pill --}}
                <div class="stats-pill">
                    <div class="stat-item">
                        <span class="stat-num">{{ $notifications->total() }}</span>
                        <span class="stat-lbl">Total</span>
                    </div>
                    <div class="stat-sep"></div>
                    <div class="stat-item">
                        <span class="stat-num warning">{{ $notifications->where('read_at', null)->count() }}</span>
                        <span class="stat-lbl">
                            Unread
                            @if($notifications->where('read_at', null)->count() > 0)
                                <span class="blink-dot"></span>
                            @endif
                        </span>
                    </div>
                    <div class="stat-sep"></div>
                    <div class="stat-item">
                        <span class="stat-num success">{{ $notifications->where('read_at', '!=', null)->count() }}</span>
                        <span class="stat-lbl">Read</span>
                    </div>
                </div>

                @if(auth()->guard('admin')->user()->unreadNotifications->count() > 0)
                    <button class="btn-mark-all" id="markAllRead">
                        <i class="bi bi-check2-all"></i>
                        <span>Mark All Read</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- ── FEED ── --}}
        @if($notifications->count() > 0)
            <div class="notif-feed">
                @foreach($notifications as $notification)
                    @php
                        $isUnread = !$notification->read_at;
                        $title = strtolower($notification->data['title'] ?? '');
                        $isExport = $notification->type === 'App\Notifications\ExportCompleted';
                        $isImport = $notification->type === 'App\Notifications\ImportCompleted';

                        // Detect notification category from title
                        if (str_contains($title, 'cancel')) {
                            $nType = 'cancelled';
                            $icon = 'bi-x-circle-fill';
                            $color = 'red';
                        } elseif (str_contains($title, 'diamond') && str_contains($title, 'sold')) {
                            $nType = 'soldout';
                            $icon = 'bi-gem';
                            $color = 'purple';
                        } elseif (str_contains($title, 'diamond') || str_contains($title, 'melee')) {
                            $nType = 'diamond';
                            $icon = 'bi-gem';
                            $color = 'purple';
                        } elseif (str_contains($title, 'reminder')) {
                            $nType = 'reminder';
                            $icon = 'bi-alarm-fill';
                            $color = 'amber';
                        } elseif (str_contains($title, 'updated') || str_contains($title, 'update')) {
                            $nType = 'updated';
                            $icon = 'bi-arrow-repeat';
                            $color = 'blue';
                        } elseif (str_contains($title, 'created') || str_contains($title, 'new order')) {
                            $nType = 'created';
                            $icon = 'bi-plus-circle-fill';
                            $color = 'green';
                        } elseif ($isExport) {
                            $nType = 'export';
                            $icon = 'bi-download';
                            $color = 'blue';
                        } elseif ($isImport) {
                            $nType = 'import';
                            $icon = 'bi-upload';
                            $color = 'blue';
                        } else {
                            $nType = 'bell';
                            $icon = 'bi-bell-fill';
                            $color = 'gray';
                        }
                    @endphp
                    <div class="notif-row {{ $isUnread ? 'is-unread' : 'is-read' }}">

                        {{-- Unread dot --}}
                        <div class="unread-dot {{ $isUnread ? 'active' : '' }}"></div>

                        {{-- Icon --}}
                        <div class="notif-avatar avatar-{{ $color }} {{ $isUnread ? 'avatar-active' : '' }}">
                            <i class="bi {{ $icon }}"></i>
                        </div>

                        {{-- Body --}}
                        <div class="notif-body">
                            <div class="notif-row-top">
                                <div class="notif-title-group">
                                    <span class="notif-type-badge badge-{{ $color }}">
                                        @if($nType === 'cancelled') Cancelled
                                        @elseif($nType === 'soldout') Sold Out
                                        @elseif($nType === 'diamond') Diamond
                                        @elseif($nType === 'reminder') Reminder
                                        @elseif($nType === 'updated') Updated
                                        @elseif($nType === 'created') New Order
                                        @elseif($nType === 'export') Export
                                        @elseif($nType === 'import') Import
                                        @else Alert @endif
                                    </span>
                                    <span class="notif-row-title">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                </div>
                                <div class="notif-row-meta">
                                    <span class="notif-time">
                                        <i class="bi bi-clock"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    <span class="notif-date">{{ $notification->created_at->format('M d, Y · h:i A') }}</span>

                                    {{-- Action link (export/import) — small text link, NOT a big green button --}}
                                    @if(isset($notification->data['action_url']))
                                        <a href="{{ $notification->data['action_url'] }}" class="notif-action-link"
                                            title="{{ $notification->data['action_text'] ?? 'Open' }}">
                                            <i
                                                class="bi {{ $isExport ? 'bi-download' : ($isImport ? 'bi-eye' : 'bi-box-arrow-up-right') }}"></i>
                                            {{ $notification->data['action_text'] ?? 'Open' }}
                                        </a>
                                    @endif

                                    {{-- Mark as read — icon-only circle button --}}
                                    @if($isUnread)
                                        <button class="mark-read-btn icon-btn" data-notification-id="{{ $notification->id }}"
                                            title="Mark as read">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @else
                                        <span class="read-check" title="Read"><i class="bi bi-check-circle-fill"></i></span>
                                    @endif
                                </div>
                            </div>

                            <p class="notif-msg">{!! $notification->data['message'] ?? '' !!}</p>

                            @if(isset($notification->data['message_preview']))
                                <div class="notif-preview">{!! $notification->data['message_preview'] ?? '' !!}</div>
                            @endif

                            @if(isset($notification->data['details']))
                                <div class="notif-details">
                                    @foreach($notification->data['details'] as $key => $value)
                                        <span class="detail-chip">
                                            <span class="detail-key">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="detail-val">{{ $value }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- ── PAGINATION ── --}}
            @if($notifications->hasPages())
                <div class="pagination-container">
                    {{ $notifications->links('pagination::bootstrap-5') }}
                </div>
            @endif

        @else
            {{-- ── EMPTY STATE ── --}}
            <div class="notif-empty">
                <div class="empty-ring">
                    <i class="bi bi-bell-slash"></i>
                </div>
                <h3>All caught up!</h3>
                <p>No notifications here. New alerts will appear when something happens.</p>
                <a href="{{ route('admin.dashboard') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        @endif

    </div>

    {{-- Toast --}}
    <div class="notif-toast" id="toast-notification">
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
            --dark: #1e293b;
            --gray: #64748b;
            --light: #f8fafc;
            --border: #e2e8f0;
            --muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
        }

        /* ── CONTAINER ── */
        .notif-container {
            width: 100%;
            padding: 0;
        }

        /* ── HEADER ── */
        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            background: white;
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1.5px solid var(--border);
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
        }

        .notif-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #818cf8, #6366f1, #4f46e5);
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            color: var(--muted);
            margin-bottom: 0.6rem;
        }

        .breadcrumb-link {
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-link:hover {
            color: var(--primary);
        }

        .breadcrumb-sep {
            font-size: 0.6rem;
        }

        .breadcrumb-current {
            color: var(--dark);
            font-weight: 500;
        }

        .notif-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--dark);
            margin: 0 0 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            letter-spacing: -0.02em;
        }

        .notif-title i {
            color: var(--primary);
        }

        .notif-subtitle {
            color: var(--muted);
            font-size: 0.8rem;
            margin: 0;
        }

        .notif-header-right {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        /* Stats pill */
        .filter-pills {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--light);
            padding: 0.35rem;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            flex-wrap: wrap;
        }

        .filter-pill {
            padding: 0.45rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .filter-pill:hover {
            color: var(--dark);
            background: rgba(0, 0, 0, 0.03);
        }

        .filter-pill.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stats-pill {
            display: flex;
            align-items: center;
            background: var(--light);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.45rem 1rem;
            gap: 0.02rem;
        }

        .stat-sep {
            width: 1px;
            height: 28px;
            background: var(--border);
            flex-shrink: 0;
        }

        .stat-num {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .stat-num.warning {
            color: var(--warning);
        }

        .stat-num.success {
            color: var(--success);
        }

        .stat-lbl {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 0.22rem;
        }

        .blink-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--warning);
            display: inline-block;
            animation: blink 1.5s ease-in-out infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .2
            }
        }

        /* Mark all btn */
        .btn-mark-all {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--primary);
            color: white;
            padding: 0.58rem 1.15rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.82rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 3px 10px rgba(99, 102, 241, 0.28);
            white-space: nowrap;
        }

        .btn-mark-all:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(99, 102, 241, 0.38);
        }

        /* ── FEED ── */
        .notif-feed {
            background: white;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .notif-row {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            padding: 1rem 2.5rem 1rem 1.4rem;
            border-bottom: 1px solid var(--border);
            position: relative;
            transition: background 0.15s;
        }

        .notif-row:last-child {
            border-bottom: none;
        }

        .notif-row:hover {
            background: #fafbff;
        }

        .notif-row.is-read {
            background: white;
        }

        .notif-row.is-read .notif-row-title,
        .notif-row.is-read .notif-msg {
            opacity: 0.6;
        }

        /* Unread dot */
        .unread-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: transparent;
            flex-shrink: 0;
            margin-top: 1rem;
        }

        .unread-dot.active {
            background: var(--primary);
        }

        /* Avatar */
        .notif-avatar {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: var(--light);
            border: 1.5px solid var(--border);
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        /* Color variants — muted (read) state */
        .notif-avatar.avatar-red {
            background: rgba(239, 68, 68, 0.07);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.18);
        }

        .notif-avatar.avatar-amber {
            background: rgba(245, 158, 11, 0.07);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.18);
        }

        .notif-avatar.avatar-blue {
            background: rgba(59, 130, 246, 0.07);
            color: #3b82f6;
            border-color: rgba(59, 130, 246, 0.18);
        }

        .notif-avatar.avatar-green {
            background: rgba(16, 185, 129, 0.07);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.18);
        }

        .notif-avatar.avatar-purple {
            background: rgba(139, 92, 246, 0.07);
            color: #8b5cf6;
            border-color: rgba(139, 92, 246, 0.18);
        }

        .notif-avatar.avatar-gray {
            background: var(--light);
            color: var(--muted);
        }

        /* Active (unread) — solid gradient */
        .avatar-red.avatar-active {
            background: linear-gradient(135deg, #f87171, #ef4444);
            color: white;
            border-color: transparent;
            box-shadow: 0 3px 8px rgba(239, 68, 68, 0.3);
        }

        .avatar-amber.avatar-active {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            border-color: transparent;
            box-shadow: 0 3px 8px rgba(245, 158, 11, 0.3);
        }

        .avatar-blue.avatar-active {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            color: white;
            border-color: transparent;
            box-shadow: 0 3px 8px rgba(59, 130, 246, 0.3);
        }

        .avatar-green.avatar-active {
            background: linear-gradient(135deg, #34d399, #10b981);
            color: white;
            border-color: transparent;
            box-shadow: 0 3px 8px rgba(16, 185, 129, 0.3);
        }

        .avatar-purple.avatar-active {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            color: white;
            border-color: transparent;
            box-shadow: 0 3px 8px rgba(139, 92, 246, 0.3);
        }

        .avatar-gray.avatar-active {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            color: white;
            border-color: transparent;
            box-shadow: 0 3px 8px rgba(100, 116, 139, 0.3);
        }

        /* Title group */
        .notif-title-group {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex: 1;
            min-width: 0;
        }

        /* Type badge */
        .notif-type-badge {
            font-size: 0.62rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 0.15rem 0.45rem;
            border-radius: 6px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .badge-red {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .badge-amber {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .badge-blue {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .badge-green {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .badge-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .badge-gray {
            background: var(--light);
            color: var(--muted);
        }

        /* Body */
        .notif-body {
            flex: 1;
            min-width: 0;
        }

        .notif-row-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .notif-row-title {
            font-size: 0.865rem;
            font-weight: 700;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notif-row-meta {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .notif-time {
            font-size: 0.73rem;
            color: var(--muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.22rem;
            white-space: nowrap;
        }

        .notif-time i {
            font-size: 0.65rem;
            color: var(--primary);
        }

        .notif-date {
            font-size: 0.7rem;
            color: #c4cdd8;
            white-space: nowrap;
        }

        /* Action link — minimal, NOT a big green button */
        .notif-action-link {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            padding: 0.18rem 0.5rem;
            border-radius: 7px;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.15);
            white-space: nowrap;
            transition: all 0.2s;
        }

        .notif-action-link:hover {
            background: rgba(99, 102, 241, 0.14);
            color: var(--primary-dark);
        }

        /* Mark read icon button */
        .icon-btn {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            padding: 0;
            line-height: 1;
        }

        .icon-btn:hover {
            background: rgba(99, 102, 241, 0.07);
            border-color: var(--primary);
            color: var(--primary);
        }

        .icon-btn:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        /* Read checkmark */
        .read-check {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: rgba(16, 185, 129, 0.07);
            border: 1.5px solid rgba(16, 185, 129, 0.18);
            color: var(--success);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            flex-shrink: 0;
        }

        /* Message */
        .notif-msg {
            font-size: 0.8rem;
            color: var(--gray);
            margin: 0;
            line-height: 1.55;
        }

        .notif-msg strong {
            color: var(--dark);
            font-weight: 600;
        }

        /* Preview */
        .notif-preview {
            font-size: 0.76rem;
            color: var(--muted);
            font-style: italic;
            padding: 0.45rem 0.7rem;
            background: var(--light);
            border-radius: 7px;
            border-left: 2px solid var(--primary);
            margin-top: 0.4rem;
            line-height: 1.5;
        }

        .notif-preview .text-primary {
            color: var(--primary);
            font-weight: 600;
            font-style: normal;
        }

        .notif-preview .fw-semibold {
            font-weight: 600;
        }

        /* Details chips */
        .notif-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.45rem;
        }

        .detail-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.22rem;
            font-size: 0.7rem;
            padding: 0.18rem 0.5rem;
            background: var(--light);
            border: 1px solid var(--border);
            border-radius: 7px;
        }

        .detail-key {
            font-weight: 700;
            color: var(--dark);
        }

        .detail-val {
            color: var(--gray);
        }

        /* ── PAGINATION ── */
        .pagination-container {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
        }

        /* ── EMPTY ── */
        .notif-empty {
            text-align: center;
            padding: 4.5rem 2rem;
            background: white;
            border-radius: 20px;
            border: 1.5px solid var(--border);
        }

        .empty-ring {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.07);
            border: 2px solid rgba(99, 102, 241, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.9rem;
            color: var(--primary);
            margin: 0 auto 1.1rem;
        }

        .notif-empty h3 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.35rem;
        }

        .notif-empty p {
            color: var(--muted);
            font-size: 0.82rem;
            margin: 0 0 1.5rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.55rem 1.1rem;
            border-radius: 10px;
            background: var(--light);
            border: 1.5px solid var(--border);
            color: var(--gray);
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* ── TOAST ── */
        .notif-toast {
            position: fixed;
            bottom: -80px;
            right: 2rem;
            background: var(--success);
            color: white;
            padding: 0.75rem 1.2rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.32);
            transition: bottom 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.35s;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
        }

        .notif-toast.show {
            bottom: 2rem;
            opacity: 1;
            pointer-events: auto;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .notif-container {
                padding: 0;
                width: 100%;
            }

            .notif-header {
                flex-direction: column;
                align-items: stretch;
                padding: 1.1rem 1.25rem;
            }

            .notif-header-right {
                flex-direction: column;
                gap: 0.65rem;
                align-items: stretch;
            }

            .filter-pills {
                justify-content: center;
            }

            .btn-mark-all {
                width: 100%;
                justify-content: center;
            }

            .notif-row {
                padding: 0.85rem 1.75rem 0.85rem 1rem;
            }

            .notif-date {
                display: none;
            }

            .notif-details {
                flex-direction: column;
            }
        }
    </style>

    <script>
        function showPageToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const msg = document.getElementById('toast-message');
            msg.textContent = message;
            toast.style.background = type === 'error' ? 'var(--danger)' : 'var(--success)';
            toast.querySelector('i').className = type === 'error' ? 'bi bi-x-circle-fill' : 'bi bi-check-circle-fill';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Mark all read
        const markAllBtn = document.getElementById('markAllRead');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', async function () {
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Processing...</span>';
                try {
                    const res = await fetch('{{ route("notifications.mark-all-read") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        showPageToast('All notifications marked as read');
                        setTimeout(() => window.location.reload(), 900);
                    } else throw new Error(data.error);
                } catch {
                    showPageToast('Failed to mark notifications', 'error');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check2-all"></i><span>Mark All Read</span>';
                }
            });
        }

        // Mark single read
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const id = this.dataset.notificationId;
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                try {
                    const baseUrl = "{{ route('notifications.read', ['notification' => ':id']) }}";
                    const url = baseUrl.replace(':id', id);
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        showPageToast('Notification marked as read');
                        setTimeout(() => window.location.reload(), 700);
                    } else throw new Error();
                } catch {
                    showPageToast('Failed to mark notification', 'error');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-circle"></i>';
                }
            });
        });
    </script>
@endsection