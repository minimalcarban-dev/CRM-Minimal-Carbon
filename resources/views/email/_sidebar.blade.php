<!-- Email Navigation Sidebar -->
<div class="email-sidebar-card">
    <div class="sidebar-section">
        <h6 class="sidebar-heading">
            <i class="bi bi-envelope-open"></i>
            Email Module
        </h6>
        <nav class="sidebar-nav">
            <a href="{{ route('email.accounts.list') }}"
                class="nav-item {{ request()->routeIs('email.accounts.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>Accounts</span>
                @if(isset($account))
                    <i class="bi bi-chevron-right nav-arrow"></i>
                @endif
            </a>

            @if(isset($account))

                    <a href="{{ route('email.inbox', $account->id) }}"
                        class="nav-item {{ request()->routeIs('email.inbox') ? 'active' : '' }}">
                        <i class="bi bi-inbox"></i>
                        <span>Inbox</span>
                        <span class="nav-badge">{{ $account->emails()->whereJsonContains('labels', 'INBOX')->count() }}</span>
                    </a>

                    <a href="{{ route('email.sent', $account->id) }}"
                        class="nav-item {{ request()->routeIs('email.sent') ? 'active' : '' }}">
                        <i class="bi bi-send"></i>
                        <span>Sent</span>
                        <span class="nav-badge">{{ $account->emails()->whereJsonContains('labels', 'SENT')->count() }}</span>
                    </a>

                    <a href="{{ route('email.drafts', $account->id) }}"
                        class="nav-item {{ request()->routeIs('email.drafts') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Drafts</span>
                        <span class="nav-badge">{{ $account->emails()->whereJsonContains('labels', 'DRAFT')->count() }}</span>
                    </a>

                    <a href="{{ route('email.starred', $account->id) }}"
                        class="nav-item {{ request()->routeIs('email.starred') ? 'active' : '' }}">
                        <i class="bi bi-star"></i>
                        <span>Starred</span>
                        <span class="nav-badge">{{ $account->emails()->whereHas('userStates', function ($q) {
                $q->where('user_id', auth()->guard('admin')->id())->where('is_starred', true); })->count() }}</span>
                    </a>

                    <a href="{{ route('email.trash', $account->id) }}"
                        class="nav-item {{ request()->routeIs('email.trash') ? 'active' : '' }}">
                        <i class="bi bi-trash"></i>
                        <span>Trash</span>
                        <span class="nav-badge">{{ $account->emails()->whereJsonContains('labels', 'TRASH')->count() }}</span>
                    </a>
            @endif
        </nav>
    </div>

    @if(isset($account))
        <div class="sidebar-section">
            <h6 class="sidebar-heading">
                <i class="bi bi-info-circle"></i>
                Account Info
            </h6>
            <div class="account-info-card">
                <div class="info-item">
                    <div class="info-label">Sync Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $account->sync_status === 'error' ? 'error' : 'success' }}">
                            <i class="bi {{ $account->sync_status === 'error' ? 'bi-exclamation-circle' : 'bi-check-circle' }}"></i>
                            {{ ucfirst($account->sync_status) }}
                        </span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Messages</div>
                    <div class="info-value">
                        <span class="info-count">{{ $account->emails()->count() }}</span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Last Synced</div>
                    <div class="info-value">
                        <span
                            class="info-time">{{ $account->last_sync_at ? $account->last_sync_at->diffForHumans() : 'Never' }}</span>
                    </div>
                </div>

                <div class="info-actions">
                    <a href="{{ route('email.sync', $account->id) }}" class="btn-sync">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Sync Now</span>
                    </a>
                </div>
            </div>
        </div>
    @endif
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
    }

    .email-sidebar-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px var(--shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    /* Sidebar Section */
    .sidebar-section {
        padding: 1.5rem;
    }

    .sidebar-section:not(:last-child) {
        border-bottom: 2px solid var(--border);
    }

    .sidebar-heading {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sidebar-heading i {
        color: var(--primary);
        font-size: 1rem;
    }

    /* Navigation */
    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .sidebar-actions-container {
        padding-bottom: 1rem;
        margin-bottom: 0.5rem;
        border-bottom: 2px solid var(--border);
    }

    .btn-compose {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.875rem 1rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .btn-compose:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        filter: brightness(1.1);
    }

    .btn-compose i {
        font-size: 1.25rem;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        text-decoration: none;
        color: var(--gray);
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.2s;
        position: relative;
    }

    .nav-item:hover {
        background: var(--primary);
        color: white;
    }

    .nav-item.active {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .nav-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .nav-item i:first-child {
        font-size: 1.125rem;
        flex-shrink: 0;
    }

    .nav-item span:not(.nav-badge):not(.coming-soon-badge) {
        flex: 1;
    }

    .nav-arrow {
        margin-left: auto;
        font-size: 0.875rem;
        opacity: 0.6;
    }

    .nav-badge {
        display: inline-flex;   
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 0.5rem;
        background: rgba(100, 116, 139, 0.1);
        color: var(--gray);
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-left: auto;
    }

    .nav-item.active .nav-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    .nav-item:hover .nav-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .coming-soon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.25rem 0.5rem;
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-left: auto;
    }

    /* Account Info Card */
    .account-info-card {
        background: linear-gradient(135deg, var(--light-gray), white);
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .info-label {
        font-size: 0.8rem;
        color: var(--gray);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .info-value {
        font-size: 0.95rem;
        color: var(--dark);
        font-weight: 600;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-success {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .status-error {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .status-badge i {
        font-size: 0.875rem;
    }

    .info-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 0.75rem;
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 700;
    }

    .info-time {
        font-size: 0.9rem;
        color: var(--gray);
    }

    .info-actions {
        margin-top: 0.5rem;
        padding-top: 1rem;
        border-top: 2px solid var(--border);
    }

    .btn-sync {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.75rem 1rem;
        background: white;
        border: 2px solid var(--border);
        border-radius: 10px;
        color: var(--gray);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .btn-sync:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
        transform: translateY(-2px);
    }

    .btn-sync i {
        font-size: 1.125rem;
    }

    /* Responsive */
    @media (max-width: 968px) {
        .email-sidebar-card {
            display: none;
        }
    }
</style>