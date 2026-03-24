@extends('layouts.admin')

@section('title', 'Email Accounts')

@section('content')
    <div class="email-accounts-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('email.accounts.list') }}" class="breadcrumb-link">
                            Email System
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Accounts</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-envelope-at-fill"></i>
                        Email Integrations
                    </h1>
                    <p class="page-subtitle">Connect and manage your Gmail accounts for seamless communication</p>
                </div>
                <div class="header-right">
                    <button type="button" class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#connectModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>Connect Gmail Account</span>
                    </button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert-card success">
                <div class="alert-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Success</div>
                    <div class="alert-message">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-card danger">
                <div class="alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Error</div>
                    <div class="alert-message">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-envelope-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Connected Accounts</div>
                    <div class="stat-value">{{ $accounts->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-link-45deg"></i> Active integrations
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Active Accounts</div>
                    <div class="stat-value">{{ $accounts->where('is_active', true)->count() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-circle-fill live-indicator"></i> Syncing
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Messages</div>
                    <div class="stat-value">
                        {{ $accounts->sum(function ($account) {
                            return $account->emails()->count();
                        }) }}
                    </div>
                    <div class="stat-trend">
                        <i class="bi bi-envelope"></i> Across all accounts
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Grid -->
        <div class="accounts-section">
            @forelse($accounts as $account)
                <div class="account-card">
                    <div class="account-header">
                        <div class="account-avatar">
                            <i class="bi bi-envelope-at"></i>
                        </div>
                        <div class="account-info">
                            <h3 class="account-email">{{ $account->email_address }}</h3>
                            <span class="account-status status-{{ $account->is_active ? 'active' : 'inactive' }}">
                                <i class="bi bi-circle-fill"></i>
                                {{ $account->is_active ? 'Connected' : 'Disconnected' }}
                            </span>
                        </div>
                        <div class="account-menu">
                            <button class="menu-btn" type="button" id="dropdownMenu{{ $account->id }}"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenu{{ $account->id }}">
                                <li>
                                    <a class="dropdown-item" href="{{ route('email.inbox', $account->id) }}">
                                        <i class="bi bi-inbox"></i> Open Inbox
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item sync-trigger" href="{{ route('email.sync', $account->id) }}">
                                        <i class="bi bi-arrow-repeat"></i> Sync Now
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('email.account.revoke', $account->id) }}" method="POST"
                                        class="confirm-action-form" 
                                        data-confirm-title="Disconnect Account?" 
                                        data-confirm-text="Are you sure you want to disconnect this account? This will revoke access tokens and stop email synchronization.">

                                        @csrf
                                        <button type="button" class="dropdown-item text-danger confirm-btn">
                                            <i class="bi bi-link-45deg"></i> Disconnect
                                        </button>
                                    </form>
                                </li>

                                <li>
                                    <form action="{{ route('email.account.delete', $account->id) }}" method="POST"
                                        class="confirm-action-form"
                                        data-confirm-title="Remove Account Permanently?"
                                        data-confirm-text="Are you sure you want to remove this account? All locally stored emails, threads, and attachments will be permanently deleted. This action cannot be undone.">

                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger confirm-btn">
                                            <i class="bi bi-trash"></i> Remove Account
                                        </button>
                                    </form>
                                </li>


                            </ul>
                        </div>
                    </div>

                    <div class="account-stats">
                        <div class="stat-item">
                            <div class="stat-item-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="stat-item-content">
                                <span class="stat-item-value">{{ $account->emails()->count() }}</span>
                                <span class="stat-item-label">Messages</span>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div
                                class="stat-item-icon {{ $account->sync_status === 'error' ? 'status-error' : 'status-success' }}">
                                <i
                                    class="bi {{ $account->sync_status === 'error' ? 'bi-exclamation-circle' : 'bi-check-circle' }}"></i>
                            </div>
                            <div class="stat-item-content">
                                <span class="stat-item-value">{{ ucfirst($account->sync_status) }}</span>
                                <span class="stat-item-label">Sync Status</span>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-item-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="stat-item-content">
                                <span
                                    class="stat-item-value">{{ $account->last_sync_at ? $account->last_sync_at->diffForHumans() : 'Never' }}</span>
                                <span class="stat-item-label">Last Synced</span>
                            </div>
                        </div>
                    </div>

                    <div class="account-actions">
                        <a href="{{ route('email.inbox', $account->id) }}" class="btn-action btn-action-primary">
                            <i class="bi bi-inbox"></i>
                            <span>Open Inbox</span>
                        </a>
                        <a href="{{ route('email.sync', $account->id) }}"
                            class="btn-action btn-action-secondary sync-trigger">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Sync</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-envelope-x"></i>
                    </div>
                    <h3 class="empty-title">No Email Accounts Connected</h3>
                    <p class="empty-description">
                        Connect your Gmail account to manage communications directly from the CRM.
                        Start by clicking the "Connect Gmail Account" button above.
                    </p>
                    <button type="button" class="btn-primary-custom" data-bs-toggle="modal"
                        data-bs-target="#connectModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>Connect Your First Account</span>
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Connect Modal -->
    <div class="modal fade" id="connectModal" tabindex="-1" aria-labelledby="connectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-header-content">
                        <div class="modal-icon">
                            <i class="bi bi-google"></i>
                        </div>
                        <div>
                            <h5 class="modal-title" id="connectModalLabel">Connect Gmail Account</h5>
                            <p class="modal-subtitle">Link your Gmail to manage emails within the CRM</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-info-box">
                        <i class="bi bi-info-circle"></i>
                        <p>Choose the company context for this email account. This helps in categorizing and limiting
                            access.</p>
                    </div>
                    <form id="connectForm">
                        <div class="form-group">
                            <label class="form-label">Company Context</label>
                            <select class="form-select" id="companySelect">
                                @foreach (\App\Models\Company::all() as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Select the company this email account belongs to</div>
                        </div>
                        <button type="button" onclick="startOAuth()" class="btn-oauth">
                            <i class="bi bi-google"></i>
                            <span>Continue with Google</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function startOAuth() {
            const companyId = document.getElementById('companySelect').value;
            const url = "{{ route('email.oauth.redirect', ':id') }}".replace(':id', companyId);
            window.location.href = url;
        }

        // --- Sync Loader Logic ---
        document.addEventListener('DOMContentLoaded', function() {
            const syncTriggers = document.querySelectorAll('.sync-trigger');
            syncTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(e) {
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
            
            // --- Confirmation Logic ---
            const confirmButtons = document.querySelectorAll('.confirm-btn');
            confirmButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const form = this.closest('.confirm-action-form');
                    const title = form.dataset.confirmTitle || 'Are you sure?';
                    const text = form.dataset.confirmText || 'Do you want to proceed?';
                    
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes, proceed',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

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

        .email-accounts-container {
            padding: 2rem;
            max-width: 1800px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

        /* Page Header */
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

        /* Alert Cards */
        .alert-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
            border-left: 4px solid;
            animation: slideIn 0.3s ease;
        }

        .alert-card.success {
            border-color: var(--success);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), white);
        }

        .alert-card.danger {
            border-color: var(--danger);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), white);
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .alert-card.success .alert-icon {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .alert-card.danger .alert-icon {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .alert-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .alert-message {
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* Stats Cards */
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
            gap: 1.25rem;
            box-shadow: 0 1px 3px var(--shadow);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px var(--shadow-md);
            border-color: var(--border);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .stat-card-primary .stat-icon {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
            color: var(--primary);
        }

        .stat-card-success .stat-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .stat-card-info .stat-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
            color: var(--info);
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-trend {
            font-size: 0.85rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .live-indicator {
            color: var(--success);
            font-size: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Accounts Section */
        .accounts-section {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1.5rem;
        }

        .account-card {
            background: white;
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 1px 3px var(--shadow);
            transition: all 0.3s;
            border: 2px solid transparent;
            overflow: hidden;
        }

        .account-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px var(--shadow-md);
            border-color: var(--primary);
        }

        .account-header {
            padding: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            border-bottom: 2px solid var(--border);
            background: linear-gradient(135deg, var(--light-gray), white);
        }

        .account-avatar {
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

        .account-info {
            flex: 1;
            min-width: 0;
        }

        .account-email {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .account-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .status-active i {
            font-size: 0.5rem;
            animation: pulse 2s infinite;
        }

        .account-menu {
            position: relative;
        }

        .menu-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 2px solid var(--border);
            background: white;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .account-stats {
            padding: 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            border-bottom: 2px solid var(--border);
            background: rgba(248, 250, 252, 0.5);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.5rem 0;
        }

        .stat-item:not(:last-child) {
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.875rem;
        }

        .stat-item-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--light-gray);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .stat-item-icon.status-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .stat-item-icon.status-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-item-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .stat-item-value {
            display: block;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .stat-item-label {
            display: block;
            font-size: 0.75rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 500;
        }

        .account-actions {
            padding: 1.5rem;
            display: flex;
            gap: 0.75rem;
        }

        .btn-action {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            border: 2px solid;
        }

        .btn-action-primary {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .btn-action-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .btn-action-secondary {
            background: white;
            border-color: var(--border);
            color: var(--gray);
        }

        .btn-action-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            padding: 4rem 2rem;
            text-align: center;
            background: white;
            border-radius: 16px;
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
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px var(--shadow-lg);
        }

        .modal-header {
            border-bottom: 2px solid var(--border);
            padding: 1.5rem;
        }

        .modal-header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(66, 133, 244, 0.15), rgba(66, 133, 244, 0.05));
            color: #4285f4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .modal-subtitle {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0.25rem 0 0 0;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-info-box {
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.05);
            border-left: 4px solid var(--info);
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .modal-info-box i {
            color: var(--info);
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .modal-info-box p {
            margin: 0;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-text {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        .btn-oauth {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #4285f4, #357ae8);
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
        }

        .btn-oauth:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(66, 133, 244, 0.4);
        }

        .btn-oauth i {
            font-size: 1.25rem;
        }

        /* Dropdown Menu */
        .dropdown-menu {
            border: 2px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 8px 24px var(--shadow-md);
            padding: 0.5rem;
        }

        .dropdown-item {
            padding: 0.625rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: var(--light-gray);
        }

        .dropdown-item.text-danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-color: var(--border);
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .accounts-section {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .email-accounts-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary-custom {
                width: 100%;
                justify-content: center;
                min-height: 48px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .accounts-section {
                grid-template-columns: 1fr;
            }

            .account-stats {
                grid-template-columns: 1fr;
            }

            .account-actions {
                flex-direction: column;
            }

            .account-actions .btn-action {
                min-height: 48px;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .menu-btn {
                min-width: 44px;
                min-height: 44px;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }

            .stat-card {
                flex-direction: column;
                text-align: center;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
        }
    </style>
@endsection
