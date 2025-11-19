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
            <chat 
                :user-id="{{ auth()->guard('admin')->id() }}"
                pusher-key="{{ config('broadcasting.connections.pusher.key') }}"
                pusher-cluster="{{ config('broadcasting.connections.pusher.options.cluster') }}"
            ></chat>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.userId = {{ auth()->guard('admin')->id() }};
    window.authAdminName = "{{ auth()->guard('admin')->user()->name ?? 'Admin' }}";
    // console.log('Chat view loaded, user ID: ' + {{ auth()->guard('admin')->id() }});
</script>
@endpush
@endsection

@push('styles')
<style>
    /* Reset and base styles for chat wrapper */
    .chat-wrapper {
        margin: -2rem;
        height: calc(100vh - 80px);
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    }

    .chat-container-outer {
        height: 100%;
        background: white;
        border-radius: 20px;
        margin: 1rem;
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