@extends('layouts.admin')

@section('title', 'Chat')

@section('content')
    <div class="chat-wrapper">
        <div class="chat-container-outer">
            <!-- Debug info (hidden) -->
            <div class="debug-info" style="display: none;">
                User ID: {{ auth()->guard('admin')->id() }}<br>
                Pusher Key: {{ config('broadcasting.connections.pusher.key') }}<br>
                Cluster: {{ config('broadcasting.connections.pusher.options.cluster') }}
            </div>

            <div id="app" class="h-100">
                <div id="chat-loading-fallback" v-if="false"
                    style="display: flex; justify-content: center; align-items: center; height: 100%; flex-direction: column; background: #f8fafc;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h4 class="mt-4 text-secondary">Initializing Chat System...</h4>
                    <p class="text-muted">Connecting to secure channels</p>

                    <div id="chat-loading-error" class="mt-4 alert alert-warning"
                        style="display: none; max-width: 400px; text-align: center;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>System did not load?</strong><br>
                        Please run this command in your terminal:<br>
                        <code
                            class="d-block mt-2 p-2 bg-dark text-light rounded">& "C:\Program Files\nodejs\npm.cmd" run build</code>

                        <pre id="vue-error-output"
                            class="mt-3 text-start text-danger border border-danger bg-white p-2 rounded"
                            style="display:none; font-size: 11px; max-height: 200px; overflow-y: auto; white-space: pre-wrap; word-break: break-word;"></pre>
                    </div>
                </div>

                <chat :user-id="@json(auth()->guard('admin')->id())"
                    pusher-key="{{ config('broadcasting.connections.pusher.key') }}"
                    pusher-cluster="{{ config('broadcasting.connections.pusher.options.cluster') }}"></chat>
            </div>

            <script>
                // Show help message if Vue doesn't mount within 5 seconds
                setTimeout(() => {
                    const fallback = document.getElementById('chat-loading-fallback');
                    const errorMsg = document.getElementById('chat-loading-error');
                    // If the fallback element still exists, it means Vue hasn't replaced it yet
                    if (fallback && errorMsg) {
                        errorMsg.style.display = 'block';
                    }
                }, 5000);
            </script>
        </div>
    </div>

    @push('scripts')
        <script>
            window.userId = @json(auth()->guard('admin')->id());
            window.authAdminName = @json(auth()->guard('admin')->user()->name ?? 'Admin');
            console.log('Chat view loaded, user ID:', window.userId);
            // Fallbacks for Echo when VITE_* env vars are missing at build time
            window.chatPusherKey = "{{ config('broadcasting.connections.pusher.key') }}";
            window.chatPusherCluster = "{{ config('broadcasting.connections.pusher.options.cluster') }}";
        </script>
    @endpush
@endsection

@push('styles')
    <style>
        /* Prevent page scroll for chat - only allow internal message scrolling */
        html,
        body {
            overflow: hidden !important;
            height: 100% !important;
        }

        #mainContent {
            overflow: hidden !important;
            height: calc(100dvh - 70px) !important;
            min-height: calc(100dvh - 70px) !important;
            max-height: calc(100dvh - 70px) !important;
            margin-top: 70px !important;
            padding: 0rem !important;
            box-sizing: border-box !important;
        }

        /* Reset and base styles for chat wrapper */
        .chat-wrapper {
            margin: 0;
            height: 100%;
            background: linear-gradient(135deg, var(--bg-body) 0%, var(--light-gray) 100%);
        }

        .chat-container-outer {
            height: 100%;
            background: var(--bg-card);
            border-radius: 20px;
            /* margin: 1rem; */
            box-shadow: 0 10px 40px var(--shadow-md);
            overflow: hidden;
            border: 2px solid var(--border);
        }

        #app {
            height: 100%;
        }

        /* Override default styles */
        .chat-container {
            height: 100% !important;
            min-height: 0;
            border-radius: 20px;
            overflow: hidden;
        }

        @media (max-width: 768px) {
            #mainContent {
                height: calc(100dvh - 70px - 60px) !important;
                min-height: calc(100dvh - 70px - 60px) !important;
                max-height: calc(100dvh - 70px - 60px) !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .chat-container-outer {
                border-radius: 0;
                border-left: 0;
                border-right: 0;
                border-bottom: 0;
            }

            .chat-container {
                border-radius: 0;
            }
        }

        /* Fix chat main area flex layout */
        .chat-main {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
            overflow: hidden !important;
        }

        /* Messages area should scroll; mobile padding is handled in media query */
        .messages-container {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            min-height: 0 !important;
        }

        /* Mobile composer positioning is handled natively by Chat.vue component CSS */

        /* Modern scrollbar */
        .chat-container ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .chat-container ::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 4px;
        }

        .chat-container ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
            transition: background 0.2s;
        }

        .chat-container ::-webkit-scrollbar-thumb:hover {
            background: var(--gray);
        }

        /* ── Dark mode overrides for Vue chat component internals ── */
        [data-theme="dark"] .chat-sidebar {
            background: var(--bg-card) !important;
            border-color: var(--border) !important;
        }

        [data-theme="dark"] .chat-header {
            background: var(--bg-card) !important;
            border-color: var(--border) !important;
            color: var(--text-primary) !important;
        }

        [data-theme="dark"] .conversation-item {
            color: var(--text-primary) !important;
        }

        [data-theme="dark"] .conversation-item:hover,
        [data-theme="dark"] .conversation-item.active {
            background: rgba(99, 102, 241, 0.1) !important;
        }

        [data-theme="dark"] .conversation-name,
        [data-theme="dark"] .conversation-preview,
        [data-theme="dark"] .message-sender {
            color: var(--text-primary) !important;
        }

        [data-theme="dark"] .conversation-preview,
        [data-theme="dark"] .message-time {
            color: var(--text-secondary) !important;
        }

        [data-theme="dark"] .messages-container {
            background: var(--bg-body) !important;
        }

        [data-theme="dark"] .message-bubble.received {
            background: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border-color: var(--border) !important;
        }

        [data-theme="dark"] .message-input-container {
            background: var(--bg-card) !important;
            border-color: var(--border) !important;
        }

        [data-theme="dark"] .message-input {
            background: var(--bg-body) !important;
            color: var(--text-primary) !important;
            border-color: var(--border) !important;
        }

        [data-theme="dark"] .message-input::placeholder {
            color: var(--muted) !important;
        }

        [data-theme="dark"] .send-btn {
            background: var(--primary) !important;
        }

        [data-theme="dark"] .chat-empty-state {
            background: var(--bg-body) !important;
            color: var(--text-secondary) !important;
        }

        /* Extended dark theme alignment for Vue Chat component */
        [data-theme="dark"] .chat-wrapper {
            background: var(--bg-body, #0f172a) !important;
        }

        [data-theme="dark"] .chat-container-outer {
            background: var(--bg-card, #1e293b) !important;
            border-color: rgba(148, 163, 184, 0.34) !important;
            box-shadow: 0 8px 24px rgba(2, 6, 23, 0.22);
        }

        [data-theme="dark"] .channels-sidebar,
        [data-theme="dark"] .chat-header,
        [data-theme="dark"] .message-input-container,
        [data-theme="dark"] .info-sidebar,
        [data-theme="dark"] .thread-panel,
        [data-theme="dark"] .modal-container,
        [data-theme="dark"] .pdf-modal-container {
            background: var(--bg-card, #1e293b) !important;
            border-color: rgba(148, 163, 184, 0.34) !important;
        }

        [data-theme="dark"] .sidebar-header,
        [data-theme="dark"] .section-header,
        [data-theme="dark"] .section-toggle,
        [data-theme="dark"] .thread-header,
        [data-theme="dark"] .modal-header,
        [data-theme="dark"] .modal-footer,
        [data-theme="dark"] .pdf-modal-header {
            background: rgba(15, 23, 42, 0.46) !important;
            border-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .channels-scroll,
        [data-theme="dark"] .messages-container,
        [data-theme="dark"] .thread-content,
        [data-theme="dark"] .section-content,
        [data-theme="dark"] .modal-body {
            background: var(--bg-body, #0f172a) !important;
        }

        [data-theme="dark"] .channel-item,
        [data-theme="dark"] .conversation-item,
        [data-theme="dark"] .member-item,
        [data-theme="dark"] .search-result-item,
        [data-theme="dark"] .file-link,
        [data-theme="dark"] .member-checkbox-item {
            background: transparent !important;
            border-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .channel-item:hover,
        [data-theme="dark"] .channel-item.active,
        [data-theme="dark"] .conversation-item:hover,
        [data-theme="dark"] .conversation-item.active,
        [data-theme="dark"] .member-item:hover,
        [data-theme="dark"] .search-result-item:hover {
            background: rgba(99, 102, 241, 0.14) !important;
        }

        [data-theme="dark"] .search-input,
        [data-theme="dark"] .form-input,
        [data-theme="dark"] .form-textarea,
        [data-theme="dark"] .message-textarea {
            background: rgba(15, 23, 42, 0.62) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .search-input::placeholder,
        [data-theme="dark"] .form-input::placeholder,
        [data-theme="dark"] .form-textarea::placeholder,
        [data-theme="dark"] .message-textarea::placeholder {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .header-title,
        [data-theme="dark"] .channel-title,
        [data-theme="dark"] .message-sender,
        [data-theme="dark"] .thread-title,
        [data-theme="dark"] .modal-title,
        [data-theme="dark"] .mention-name,
        [data-theme="dark"] .member-name,
        [data-theme="dark"] .empty-title {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .header-subtitle,
        [data-theme="dark"] .channel-preview,
        [data-theme="dark"] .channel-time,
        [data-theme="dark"] .message-time,
        [data-theme="dark"] .message-time-outside,
        [data-theme="dark"] .thread-subtitle,
        [data-theme="dark"] .member-email,
        [data-theme="dark"] .empty-subtitle,
        [data-theme="dark"] .empty-section,
        [data-theme="dark"] .results-info {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .message-wrapper.received .message-bubble,
        [data-theme="dark"] .parent-bubble {
            background: var(--bg-card, #1e293b) !important;
            color: var(--text-primary, #f1f5f9) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
        }

        [data-theme="dark"] .message-text,
        [data-theme="dark"] .reply-inline-text {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .empty-state {
            background: var(--bg-body, #0f172a) !important;
        }

        [data-theme="dark"] .btn-icon-secondary,
        [data-theme="dark"] .btn-attach,
        [data-theme="dark"] .btn-secondary,
        [data-theme="dark"] .modal-close,
        [data-theme="dark"] .pdf-action-btn {
            background: rgba(15, 23, 42, 0.62) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .btn-icon-secondary:hover,
        [data-theme="dark"] .btn-attach:hover,
        [data-theme="dark"] .btn-secondary:hover,
        [data-theme="dark"] .modal-close:hover,
        [data-theme="dark"] .pdf-action-btn:hover {
            background: rgba(99, 102, 241, 0.16) !important;
            border-color: rgba(129, 140, 248, 0.55) !important;
            color: #c7d2fe !important;
        }

        [data-theme="dark"] .btn-icon-primary,
        [data-theme="dark"] .btn-send,
        [data-theme="dark"] .btn-primary {
            background: linear-gradient(135deg, var(--primary, #6366f1), var(--primary-dark, #4f46e5)) !important;
            color: #fff !important;
            border-color: transparent !important;
        }

        [data-theme="dark"] .count-badge,
        [data-theme="dark"] .unread-badge {
            background: rgba(99, 102, 241, 0.18) !important;
            color: #c7d2fe !important;
            border-color: rgba(129, 140, 248, 0.35) !important;
        }

        [data-theme="dark"] .info-profile {
            background: linear-gradient(180deg, rgba(99, 102, 241, 0.16) 0%, var(--bg-card, #1e293b) 100%) !important;
            border-bottom-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .info-section {
            background: rgba(15, 23, 42, 0.52) !important;
            border-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .section-toggle {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .section-toggle:hover {
            background: rgba(99, 102, 241, 0.14) !important;
        }

        [data-theme="dark"] .section-content {
            background: rgba(15, 23, 42, 0.62) !important;
            border-top-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .chat-container .input-row {
            background: rgba(15, 23, 42, 0.62) !important;
            border-color: rgba(148, 163, 184, 0.34) !important;
            box-shadow: none !important;
        }

        [data-theme="dark"] .chat-container .message-textarea {
            color: var(--text-primary, #f1f5f9) !important;
            caret-color: #c7d2fe !important;
        }

        [data-theme="dark"] .chat-container .btn-attach {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .chat-container .btn-attach:hover {
            background: rgba(99, 102, 241, 0.16) !important;
            color: #c7d2fe !important;
        }

        [data-theme="dark"] .chat-container .message-bubble {
            background: rgba(30, 41, 59, 0.9) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
        }

        [data-theme="dark"] .chat-container .own-message .message-bubble {
            background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
            border-color: transparent !important;
            color: #ffffff !important;
        }

        [data-theme="dark"] .chat-container .own-message .message-text,
        [data-theme="dark"] .chat-container .own-message .message-meta,
        [data-theme="dark"] .chat-container .own-message .message-time,
        [data-theme="dark"] .chat-container .own-message .message-time-outside {
            color: #e5e7eb !important;
        }

        [data-theme="dark"] .chat-container .message-text {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .info-close-btn {
            background: rgba(15, 23, 42, 0.62) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
            box-shadow: none !important;
        }

        [data-theme="dark"] .info-close-btn i {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .chat-container .thread-panel {
            background: var(--bg-card, #1e293b) !important;
            border-left-color: rgba(148, 163, 184, 0.34) !important;
        }

        [data-theme="dark"] .chat-container .profile-name {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .chat-container .profile-type {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .chat-container .thread-header {
            background: rgba(15, 23, 42, 0.46) !important;
            border-bottom-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .chat-container .thread-content {
            background: var(--bg-body, #0f172a) !important;
        }

        [data-theme="dark"] .chat-container .thread-parent-message {
            background: rgba(15, 23, 42, 0.62) !important;
            border-bottom-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .chat-container .thread-content .message-bubble {
            background: transparent !important;
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .chat-container .thread-divider {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .chat-container .thread-divider::before,
        [data-theme="dark"] .chat-container .thread-divider::after {
            border-top-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .chat-container .thread-input-area {
            background: var(--bg-card, #1e293b) !important;
            border-top-color: rgba(148, 163, 184, 0.24) !important;
            box-shadow: none !important;
        }

        [data-theme="dark"] .chat-container .thread-input-area .input-row {
            background: rgba(15, 23, 42, 0.62) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
            box-shadow: none !important;
        }

        [data-theme="dark"] .chat-container .thread-input-area .thread-textarea {
            color: var(--text-primary, #f1f5f9) !important;
            caret-color: #c7d2fe !important;
        }

        [data-theme="dark"] .chat-container .thread-input-area .thread-textarea::placeholder {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .chat-container .btn-attach-thread {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .chat-container .btn-attach-thread:hover {
            color: #c7d2fe !important;
        }

        [data-theme="dark"] .chat-container .btn-send-thread {
            background: linear-gradient(135deg, var(--primary, #6366f1), var(--primary-dark, #4f46e5)) !important;
            color: #fff !important;
        }

        [data-theme="dark"] .chat-container .search-results-area {
            background: rgba(15, 23, 42, 0.62) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
        }

        [data-theme="dark"] .chat-container .search-results-header {
            border-bottom-color: rgba(148, 163, 184, 0.24) !important;
        }

        [data-theme="dark"] .chat-container .result-channel,
        [data-theme="dark"] .chat-container .result-time {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .chat-container .result-text,
        [data-theme="dark"] .chat-container .result-content {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .chat-container .btn-clear {
            background: rgba(15, 23, 42, 0.62) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
            color: #c7d2fe !important;
        }

        [data-theme="dark"] .chat-container .btn-clear:hover {
            background: rgba(99, 102, 241, 0.16) !important;
            border-color: rgba(129, 140, 248, 0.55) !important;
            color: #e0e7ff !important;
        }

        @media (max-width: 768px) {
            .chat-wrapper {
                margin: 0;
                height: 100%;
            }

            .chat-container-outer {
                margin: 0;
                border-radius: 0;
            }
        }

        @media (max-width: 575px) {
            .chat-main {
                width: 100% !important;
            }
        }
    </style>
@endpush