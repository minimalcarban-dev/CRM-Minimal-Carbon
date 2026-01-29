@extends('layouts.admin')

@section('title', ($folderTitle ?? 'Inbox') . ' - ' . $account->email_address)

@section('content')
    <div class="email-inbox-container">
        <div class="inbox-layout">
            <!-- Sidebar -->
            <aside class="inbox-sidebar">
                @include('email._sidebar')
            </aside>

            <!-- Main Content -->
            <main class="inbox-main">
                <!-- Header -->
                <div class="inbox-header">
                    <div class="inbox-header-left">
                        <div class="account-badge">
                            <div class="account-avatar-small">
                                {{ strtoupper(substr($account->email_address, 0, 2)) }}
                            </div>
                            <div class="account-details">
                                <h2 class="inbox-title">{{ $folderTitle ?? 'Inbox' }}: {{ $account->email_address }}</h2>
                                <p class="inbox-subtitle">{{ $emails->total() }} conversations</p>
                            </div>
                        </div>
                    </div>
                    <div class="inbox-header-right">
                        @php
                            $searchRoute = route('email.inbox', $account->id);
                            if (isset($folder)) {
                                if ($folder === 'sent')
                                    $searchRoute = route('email.sent', $account->id);
                                elseif ($folder === 'starred')
                                    $searchRoute = route('email.starred', $account->id);
                            }
                        @endphp
                        <form action="{{ $searchRoute }}" method="GET" class="search-form">
                            <div class="search-box">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" name="q" class="search-input"
                                    placeholder="Search in {{ strtolower($folderTitle ?? 'inbox') }}..."
                                    value="{{ request('q') }}">
                                @if(request('q'))
                                    <a href="{{ $searchRoute }}" class="search-clear">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                        <button class="btn-compose-main" id="btnCompose">
                            <i class="bi bi-pencil-square"></i>
                            <span>Compose</span>
                        </button>
                        <a href="{{ route('email.sync', $account->id) }}" class="btn-icon sync-trigger"
                            title="Refresh Inbox">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>

                <!-- Email List -->
                <div class="email-list-container">
                    @forelse($emails as $email)
                        @php
                            $userState = $email->userStates->first();
                            $isRead = $userState ? $userState->is_read : false;
                            $isStarred = $userState ? $userState->is_starred : false;
                        @endphp
                        <div class="email-item {{ $isRead ? 'read' : 'unread' }}"
                            onclick="window.location='{{ route('email.show', [$account->id, $email->id]) }}'">
                            <div class="email-star" onclick="event.stopPropagation()">
                                <button class="star-btn {{ $isStarred ? 'starred' : '' }}" data-email-id="{{ $email->id }}">
                                    <i class="bi {{ $isStarred ? 'bi-star-fill' : 'bi-star' }}"></i>
                                </button>
                            </div>

                            <div class="email-sender">
                                <div class="sender-avatar">
                                    @php
                                        $senderEmail = $email->from_email;
                                        if (isset($folder) && $folder === 'sent' && $email->to_recipients) {
                                            preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $email->to_recipients, $matches);
                                            $senderEmail = $matches[0] ?? $email->to_recipients;
                                        }
                                        $domain = Str::after($senderEmail, '@');
                                        $parts = explode('.', $domain);
                                        $brandDomain = count($parts) > 2 ? $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1] : $domain;
                                        $logoUrl = "https://logo.clearbit.com/{$brandDomain}";
                                        $fallbackUrl = "https://www.google.com/s2/favicons?domain={$brandDomain}&sz=128";
                                    @endphp
                                    <img src="{{ $logoUrl }}" alt="{{ substr($brandDomain, 0, 1) }}" class="brand-logo"
                                        onerror="this.src='{{ $fallbackUrl }}'; this.onerror=function(){this.style.display='none'; this.nextElementSibling.style.display='flex';};">
                                    <span class="avatar-initial" style="display: none;">
                                        @if(isset($folder) && $folder === 'sent')
                                            {{ strtoupper(substr($email->to_recipients ?: '?', 0, 1)) }}
                                        @else
                                            {{ strtoupper(substr($email->from_name ?: $email->from_email, 0, 1)) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="sender-info">
                                    <span class="sender-name">
                                        @if(isset($folder) && $folder === 'sent')
                                            To: {{ $email->to_recipients }}
                                        @else
                                            {{ $email->from_name ?: $email->from_email }}
                                        @endif
                                    </span>
                                    @if(isset($email->thread_count) && $email->thread_count > 1)
                                        <span class="thread-badge" title="{{ $email->thread_count }} messages">
                                            {{ $email->thread_count }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="email-content">
                                <div class="email-subject-line">
                                    @if($email->has_attachments)
                                        <i class="bi bi-paperclip attachment-icon"></i>
                                    @endif
                                    <span class="email-subject">{{ $email->subject }}</span>
                                </div>
                                <div class="email-preview">
                                    {{ Str::limit(strip_tags($email->body_plain ?? ''), 100) }}
                                </div>
                            </div>

                            <div class="email-meta">
                                <span class="email-time">
                                    {{ $email->received_at->isToday() ? $email->received_at->format('H:i') : $email->received_at->format('M d') }}
                                </span>
                                @if(!$isRead)
                                    <span class="unread-dot"></span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h3 class="empty-title">
                                @if(request('q'))
                                    No emails found
                                @else
                                    Inbox is empty
                                @endif
                            </h3>
                            <p class="empty-description">
                                @if(request('q'))
                                    No messages match your search "{{ request('q') }}". Try a different search term.
                                @else
                                    You don't have any messages in this folder yet.
                                @endif
                            </p>
                            @if(request('q'))
                                <a href="{{ route('email.inbox', $account->id) }}" class="btn-primary-custom">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    Clear Search
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($emails->hasPages())
                    <div class="inbox-pagination">
                        <div class="pagination-info">
                            <span class="result-count">Showing {{ $emails->firstItem() ?? 0 }} to {{ $emails->lastItem() ?? 0 }}
                                of <strong>{{ $emails->total() }}</strong> results</span>
                        </div>
                        <div class="pagination-controls">
                            {{ $emails->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>

    @include('email._compose_modal')

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
        }

        * {
            box-sizing: border-box;
        }

        .email-inbox-container {
            padding: 2rem;
            max-width: 1800px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

        .inbox-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
            height: calc(100vh - 140px);
        }

        /* Sidebar */
        .inbox-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* Main Content */
        .inbox-main {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px var(--shadow);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header */
        .inbox-header {
            padding: 1.5rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            background: linear-gradient(135deg, var(--light-gray), white);
        }

        .inbox-header-left {
            flex: 1;
            min-width: 0;
        }

        .account-badge {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .account-avatar-small {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .account-details {
            flex: 1;
            min-width: 0;
        }

        .inbox-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .inbox-subtitle {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0.25rem 0 0 0;
        }

        .inbox-header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .search-form {
            position: relative;
        }

        .search-box {
            position: relative;
            width: 300px;
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
            padding: 0.625rem 2.75rem 0.625rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.2s;
            background: white;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .search-clear {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .search-clear:hover {
            color: var(--danger);
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 2px solid var(--border);
            background: white;
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 1.125rem;
        }

        .btn-icon:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .btn-compose-main {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .btn-compose-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.35);
            filter: brightness(1.1);
        }

        .btn-compose-main i {
            font-size: 1.125rem;
        }

        @media (max-width: 768px) {
            .btn-compose-main span {
                display: none;
            }

            .btn-compose-main {
                padding: 0.625rem;
                width: 40px;
                height: 40px;
                justify-content: center;
            }
        }

        /* Email List */
        .email-list-container {
            flex: 1;
            overflow-y: auto;
        }

        .email-list-container::-webkit-scrollbar {
            width: 8px;
        }

        .email-list-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .email-list-container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        .email-list-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray);
        }

        .email-item {
            display: grid;
            grid-template-columns: auto 200px 1fr auto;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s;
            align-items: center;
        }

        .email-item:hover {
            background: var(--light-gray);
        }

        .email-item.unread {
            background: rgba(99, 102, 241, 0.03);
            font-weight: 600;
        }

        .email-item.unread:hover {
            background: rgba(99, 102, 241, 0.08);
        }

        /* Email Star */
        .email-star {
            display: flex;
            align-items: center;
        }

        .star-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--gray);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 1.125rem;
        }

        .star-btn:hover {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .star-btn.starred {
            color: var(--warning);
        }

        /* Email Sender */
        .email-sender {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }

        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--info), #2563eb);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .brand-logo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: white;
        }

        .avatar-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .sender-info {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sender-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--dark);
        }

        .thread-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 0.375rem;
            background: rgba(100, 116, 139, 0.1);
            color: var(--gray);
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        /* Email Content */
        .email-content {
            flex: 1;
            min-width: 0;
        }

        .email-subject-line {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .attachment-icon {
            color: var(--gray);
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .email-subject {
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .email-item.read .email-subject {
            font-weight: 500;
        }

        .email-preview {
            font-size: 0.875rem;
            color: var(--gray);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 400;
        }

        /* Email Meta */
        .email-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .email-time {
            font-size: 0.875rem;
            color: var(--gray);
            white-space: nowrap;
        }

        .unread-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            flex-shrink: 0;
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
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

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
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
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            color: white;
        }

        /* Pagination */
        .inbox-pagination {
            padding: 1.25rem 1.5rem;
            border-top: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            background: linear-gradient(135deg, var(--light-gray), white);
        }

        .pagination-info {
            display: flex;
            align-items: center;
        }

        .result-count {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .result-count strong {
            color: var(--primary);
            font-weight: 700;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
        }

        /* Hide the extra "Showing X to Y results" text from Bootstrap pagination */
        .pagination-controls nav>div:first-child {
            display: none !important;
        }

        .pagination-controls nav>div:last-child>div:first-child {
            display: none !important;
        }

        .pagination-controls nav p {
            display: none !important;
        }

        .pagination-controls nav>div:last-child {
            display: flex !important;
            justify-content: flex-end !important;
            width: 100% !important;
        }

        .pagination-controls>nav {
            display: flex;
            justify-content: flex-end;
            width: 100%;
        }

        .pagination-controls .pagination {
            margin: 0;
            gap: 0.25rem;
            display: flex;
            flex-wrap: wrap;
        }

        .pagination-controls .page-item .page-link {
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border);
            border-radius: 8px;
            color: var(--gray);
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0 0.5rem;
            background: white;
            transition: all 0.2s;
            text-decoration: none;
        }

        .pagination-controls .page-item .page-link:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .pagination-controls .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-color: var(--primary);
            color: white;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .pagination-controls .page-item.disabled .page-link {
            background: var(--light-gray);
            border-color: var(--border);
            color: #cbd5e1;
            cursor: not-allowed;
        }

        .pagination-controls .page-item:first-child .page-link,
        .pagination-controls .page-item:last-child .page-link {
            padding: 0 0.625rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .inbox-layout {
                grid-template-columns: 240px 1fr;
            }

            .search-box {
                width: 240px;
            }
        }

        @media (max-width: 968px) {
            .inbox-layout {
                grid-template-columns: 1fr;
            }

            .inbox-sidebar {
                display: none;
            }

            .email-item {
                grid-template-columns: auto 1fr auto;
                gap: 0.75rem;
            }

            .email-sender {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .email-inbox-container {
                padding: 1rem;
            }

            .inbox-header {
                padding: 1rem;
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
            }

            .inbox-header-right {
                width: 100%;
            }

            .search-form {
                flex: 1;
            }

            .email-item {
                padding: 0.875rem 1rem;
            }

            .inbox-pagination {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .pagination-info {
                order: 2;
            }

            .pagination-controls {
                order: 1;
                justify-content: center;
                width: 100%;
            }

            .pagination-controls .pagination {
                justify-content: center;
            }

            .pagination-controls .page-item .page-link {
                min-width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }
        }

        /* Modal Specific */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1.5rem;
        }

        .modal-content-card {
            background: white;
            width: 100%;
            max-width: 600px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalFadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-title i {
            color: var(--primary);
        }

        .btn-close-modal {
            background: white;
            border: 2px solid var(--border);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--gray);
        }

        .btn-close-modal:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        .modal-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-group-custom {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group-custom label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray);
            padding-left: 0.25rem;
        }

        .form-control-custom {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            width: 100%;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .textarea-custom {
            min-height: 200px;
            resize: vertical;
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-top: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-right {
            display: flex;
            gap: 0.75rem;
        }

        .btn-secondary-custom {
            background: white;
            color: var(--gray);
            border: 2px solid var(--border);
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary-custom:hover {
            border-color: var(--gray);
            color: var(--dark);
            background: var(--light-gray);
        }

        @media (max-width: 480px) {
            .inbox-title {
                font-size: 1rem;
            }

            .email-meta {
                flex-direction: column;
                align-items: flex-end;
                gap: 0.375rem;
            }
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Handle Star Toggling
                const starButtons = document.querySelectorAll('.star-btn');
                starButtons.forEach(btn => {
                    btn.addEventListener('click', async function (e) {
                        e.stopPropagation();
                        const emailId = this.dataset.emailId;
                        const btnElement = this;
                        const icon = this.querySelector('i');

                        try {
                            const response = await fetch(`/admin/email/{{ $account->id }}/email/${emailId}/star`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            });

                            const data = await response.json();
                            if (data.success) {
                                if (data.is_starred) {
                                    btnElement.classList.add('starred');
                                    icon.classList.replace('bi-star', 'bi-star-fill');
                                } else {
                                    btnElement.classList.remove('starred');
                                    icon.classList.replace('bi-star-fill', 'bi-star');

                                    // If we are in the starred folder, remove the item from view
                                    if (window.location.pathname.includes('/starred')) {
                                        const emailItem = btnElement.closest('.email-item');
                                        if (emailItem) {
                                            emailItem.style.opacity = '0';
                                            emailItem.style.transform = 'translateX(20px)';
                                            setTimeout(() => emailItem.remove(), 300);
                                        }
                                    }
                                }
                            }
                        } catch (error) {
                            console.error('Error toggling star:', error);
                        }
                    });
                });

                // --- Sync Loader Logic ---
                const syncTriggers = document.querySelectorAll('.sync-trigger');
                syncTriggers.forEach(trigger => {
                    trigger.addEventListener('click', function (e) {
                        Swal.fire({
                            title: 'Syncing Emails...',
                            text: 'Please wait while we connect to Gmail and update your inbox.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    });
                });

                // Mark as read indicator handling
                const emailItems = document.querySelectorAll('.email-item.unread');
                emailItems.forEach(item => {
                    item.addEventListener('click', function () {
                        this.classList.replace('unread', 'read');
                        const dot = this.querySelector('.unread-dot');
                        if (dot) dot.remove();
                    });
                });

                // --- Compose Modal Logic ---
                const composeModal = document.getElementById('composeModal');
                const btnCompose = document.getElementById('btnCompose');
                const btnCloseCompose = document.getElementById('btnCloseCompose');
                const composeForm = document.getElementById('composeForm');
                const modalTitle = document.getElementById('composeModalTitle');
                const inputTo = document.getElementById('to');
                const inputSubject = document.getElementById('subject');
                const inputBody = document.getElementById('body');

                if (btnCompose) {
                    btnCompose.addEventListener('click', () => {
                        modalTitle.innerText = 'New Message';
                        composeForm.reset();
                        composeModal.style.display = 'flex';
                    });
                }

                if (btnCloseCompose) {
                    btnCloseCompose.addEventListener('click', () => {
                        composeModal.style.display = 'none';
                    });
                }

                // Close modal on outside click
                window.addEventListener('click', (e) => {
                    if (e.target === composeModal) {
                        composeModal.style.display = 'none';
                    }
                });

                // Handle Sending
                composeForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const btnSend = document.getElementById('btnSend');
                    const originalContent = btnSend.innerHTML;

                    btnSend.disabled = true;
                    btnSend.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';

                    const formData = new FormData(this);

                    try {
                        const response = await fetch(`/admin/email/{{ $account->id }}/compose/send`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (data.success) {
                            Swal.fire({
                                title: 'Sent!',
                                text: 'Email sent successfully!',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                composeModal.style.display = 'none';
                                composeForm.reset();
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Send error:', error);
                        Swal.fire('Error', 'Failed to send email.', 'error');
                    } finally {
                        btnSend.disabled = false;
                        btnSend.innerHTML = originalContent;
                    }
                });

                // Handle Draft Saving
                const btnSaveDraft = document.getElementById('btnSaveDraft');
                btnSaveDraft.addEventListener('click', async function () {
                    const originalContent = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';

                    const formData = new FormData(composeForm);

                    try {
                        const response = await fetch(`/admin/email/{{ $account->id }}/compose/draft`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (data.success) {
                            Swal.fire({
                                title: 'Draft Saved',
                                text: 'Your message has been saved as a draft.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', 'Error saving draft: ' + data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Draft error:', error);
                        Swal.fire('Error', 'Failed to save draft', 'error');
                    } finally {
                        this.disabled = false;
                        this.innerHTML = originalContent;
                    }
                });
            });
        </script>
    @endpush
@endsection