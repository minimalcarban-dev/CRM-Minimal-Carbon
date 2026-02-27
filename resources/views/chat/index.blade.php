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
            height: calc(100vh - 70px) !important;
            max-height: calc(100vh - 70px) !important;
            /* min-height: auto !important; */
            margin-top: 70px !important;
            padding: 0rem !important;
            box-sizing: border-box !important;
        }

        /* Reset and base styles for chat wrapper */
        .chat-wrapper {
            margin: 0;
            height: calc(100% - 5rem);
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
            min-height: 500px;
            border-radius: 20px;
            overflow: hidden;
        }

        /* Fix chat main area flex layout */
        .chat-main {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
            overflow: hidden !important;
        }

        /* Messages area should scroll but not have excessive bottom padding */
        .messages-container {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding-bottom: 1.5rem !important;
            min-height: 0 !important;
        }

        /* Message input should stay at bottom without being cut off */
        .message-input-container {
            flex-shrink: 0 !important;
            position: relative !important;
            bottom: auto !important;
        }

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

        @media (max-width: 768px) {
            .chat-wrapper {
                margin: -1rem;
                height: calc(100vh - 60px);
            }

            .chat-container-outer {
                margin: 0.5rem;
                border-radius: 12px;
            }
        }

        @media (max-width: 575px) {
            .chat-main {
                width: 100% !important;
            }
        }
    </style>
@endpush