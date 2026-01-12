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
                <chat :user-id="@json(auth()->guard('admin')->id())"
                    pusher-key="{{ config('broadcasting.connections.pusher.key') }}"
                    pusher-cluster="{{ config('broadcasting.connections.pusher.options.cluster') }}"></chat>
            </div>
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
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        }

        .chat-container-outer {
            height: 100%;
            background: white;
            border-radius: 20px;
            /* margin: 1rem; */
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 2px solid #e2e8f0;
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
            background: #f1f5f9;
            border-radius: 4px;
        }

        .chat-container ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .chat-container ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
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
    </style>
@endpush