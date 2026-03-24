@extends('layouts.admin')

@section('title', 'Security & Device Management')

@push('styles')
    <style>
        [data-theme="dark"] .settings-container {
            background: var(--bg-body, #0f172a);
        }

        [data-theme="dark"] .page-header,
        [data-theme="dark"] .settings-card {
            background: var(--bg-card, #1e293b) !important;
            border: 1.5px solid rgba(148, 163, 184, 0.34) !important;
            box-shadow: 0 6px 18px rgba(2, 6, 23, 0.18);
        }

        [data-theme="dark"] .settings-card-header {
            border-bottom-color: rgba(148, 163, 184, 0.24);
        }

        [data-theme="dark"] .settings-card-title,
        [data-theme="dark"] .ip-value,
        [data-theme="dark"] .add-ip-form .form-group label,
        [data-theme="dark"] .ip-address-cell {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .toggle-desc,
        [data-theme="dark"] .ip-label,
        [data-theme="dark"] .empty-state,
        [data-theme="dark"] .config-alert-content p,
        [data-theme="dark"] .ip-table th,
        [data-theme="dark"] .ip-table td {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .toggle-slider {
            background: rgba(148, 163, 184, 0.35);
        }

        [data-theme="dark"] .current-ip-card,
        [data-theme="dark"] .ip-table th,
        [data-theme="dark"] .add-ip-form .form-control {
            background: rgba(15, 23, 42, 0.62) !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] .add-ip-form .form-control::placeholder {
            color: var(--text-secondary, #94a3b8);
        }

        [data-theme="dark"] .add-ip-form .form-control:focus {
            border-color: rgba(129, 140, 248, 0.7);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
        }

        [data-theme="dark"] .ip-table th,
        [data-theme="dark"] .ip-table td {
            border-bottom-color: rgba(148, 163, 184, 0.22);
        }

        [data-theme="dark"] .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(148, 163, 184, 0.35);
            color: var(--text-secondary, #94a3b8);
        }

        [data-theme="dark"] .btn-secondary-custom:hover {
            color: var(--text-primary, #f1f5f9);
            border-color: rgba(129, 140, 248, 0.55);
            background: rgba(99, 102, 241, 0.12);
        }

        [data-theme="dark"] .btn-danger-custom {
            border-color: rgba(239, 68, 68, 0.4);
            background: rgba(239, 68, 68, 0.08);
        }

        [data-theme="dark"] .btn-success-sm {
            border-color: rgba(16, 185, 129, 0.4);
            background: rgba(16, 185, 129, 0.08);
        }

        [data-theme="dark"] .config-alert.warning {
            background: rgba(245, 158, 11, 0.14);
            border-color: rgba(245, 158, 11, 0.35);
        }

        [data-theme="dark"] .config-alert.info {
            background: rgba(59, 130, 246, 0.14);
            border-color: rgba(59, 130, 246, 0.35);
        }

        [data-theme="dark"] code {
            color: #c7d2fe;
            background: rgba(99, 102, 241, 0.18);
            border-radius: 6px;
            padding: 0.1rem 0.35rem;
        }

        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
            margin-bottom: 1.5rem;
        }

        .settings-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .settings-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .settings-card-title i {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .settings-card-title i.shield {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.1));
            color: var(--primary);
        }

        .settings-card-title i.network {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        /* Toggle Switch */
        .toggle-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .toggle-switch {
            position: relative;
            width: 52px;
            height: 28px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #cbd5e1;
            border-radius: 28px;
            transition: all 0.3s;
        }

        .toggle-slider:before {
            content: "";
            position: absolute;
            width: 22px;
            height: 22px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .toggle-switch input:checked+.toggle-slider {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .toggle-switch input:checked+.toggle-slider:before {
            transform: translateX(24px);
        }

        .toggle-label {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .toggle-desc {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Current IP Badge */
        .current-ip-card {
            background: var(--light-gray);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .ip-display {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .ip-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--info), #2563eb);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .ip-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray);
        }

        .ip-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            font-family: 'SF Mono', SFMono-Regular, Consolas, monospace;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-badge.inactive {
            background: rgba(100, 116, 139, 0.1);
            color: var(--gray);
        }

        .status-badge.restriction-on {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .status-badge.restriction-off {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        /* IP Table */
        .ip-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ip-table th,
        .ip-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .ip-table th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--gray);
            background: var(--light-gray);
        }

        .ip-table th:first-child {
            border-radius: 8px 0 0 8px;
        }

        .ip-table th:last-child {
            border-radius: 0 8px 8px 0;
        }

        .ip-table td {
            font-size: 0.9rem;
        }

        .ip-address-cell {
            font-family: 'SF Mono', SFMono-Regular, Consolas, monospace;
            font-weight: 600;
            color: var(--dark);
        }

        /* Add Form */
        .add-ip-form {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .add-ip-form .form-group {
            flex: 1;
            min-width: 200px;
        }

        .add-ip-form .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .add-ip-form .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        .add-ip-form .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Buttons */
        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-secondary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: white;
            color: var(--dark);
            border: 2px solid var(--border);
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-secondary-custom:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-danger-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: transparent;
            color: var(--danger);
            border: 2px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-danger-custom:hover {
            background: rgba(239, 68, 68, 0.05);
            border-color: var(--danger);
        }

        .btn-success-sm {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.75rem;
            background: transparent;
            color: var(--success);
            border: 2px solid rgba(16, 185, 129, 0.2);
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-success-sm:hover {
            background: rgba(16, 185, 129, 0.05);
            border-color: var(--success);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        /* Config Alert */
        .config-alert {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .config-alert.warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .config-alert.info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .config-alert i {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .config-alert.warning i {
            color: var(--warning);
        }

        .config-alert.info i {
            color: var(--info);
        }

        .config-alert-content h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark);
        }

        .config-alert-content p {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0;
        }

        /* Action buttons row */
        .action-row {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .add-ip-form {
                flex-direction: column;
            }

            .add-ip-form .form-group {
                min-width: 100%;
            }

            .current-ip-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .ip-table th,
            .ip-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }

            .settings-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="settings-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Security & Device Management</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-shield-lock"></i>
                        Security & Device Management
                    </h1>
                    <p class="page-subtitle">Manage device-based access control with IP, browser fingerprint, and geo-fencing</p>
                </div>
            </div>
        </div>

        <!-- IP Restriction Master Toggle -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h2 class="settings-card-title">
                    <i class="bi bi-shield-check shield"></i>
                    IP Restriction
                </h2>
                <span class="status-badge {{ $ipRestrictionEnabled ? 'restriction-on' : 'restriction-off' }}"
                    id="restrictionBadge">
                    <i class="bi bi-{{ $ipRestrictionEnabled ? 'lock' : 'unlock' }}"></i>
                    {{ $ipRestrictionEnabled ? 'ACTIVE — Only whitelisted IPs allowed' : 'INACTIVE — All IPs allowed' }}
                </span>
            </div>

            <div class="toggle-container">
                <label class="toggle-switch">
                    <input type="checkbox" id="ipRestrictionToggle" {{ $ipRestrictionEnabled ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
                <div>
                    <div class="toggle-label">Enable IP Restriction</div>
                    <div class="toggle-desc">When enabled, only whitelisted IP addresses can access the site</div>
                </div>
            </div>

            @if(!$ipRestrictionEnabled)
                <div class="config-alert info" style="margin-top: 1.25rem;">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="config-alert-content">
                        <h6>IP Restriction is Currently Off</h6>
                        <p>The site is accessible from any IP address. Add your IP to the whitelist below, then enable
                            restriction to secure your site.</p>
                    </div>
                </div>
            @else
                <div class="config-alert warning" style="margin-top: 1.25rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div class="config-alert-content">
                        <h6>IP Restriction is Active</h6>
                        <p>Only trusted devices can access the site. If locked out, use <code>php artisan device:approve {email}</code> via
                            SSH.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Current IP -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h2 class="settings-card-title">
                    <i class="bi bi-globe2 network"></i>
                    Your Network
                </h2>
            </div>

            <div class="current-ip-card">
                <div class="ip-display">
                    <div class="ip-icon">
                        <i class="bi bi-wifi"></i>
                    </div>
                    <div>
                        <div class="ip-label">Your Current IP Address</div>
                        <div class="ip-value" id="currentIpDisplay">{{ $currentIp }}</div>
                    </div>
                </div>
                <button type="button" class="btn-primary-custom" onclick="addMyIp()">
                    <i class="bi bi-plus-circle"></i> Add My IP to Whitelist
                </button>
            </div>
        </div>

        <!-- Trusted Devices -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h2 class="settings-card-title">
                    <i class="bi bi-list-check shield"></i>
                    Trusted Devices
                </h2>
                <span style="font-size: 0.85rem; color: var(--gray);">
                    {{ $allowedIps->count() }} device(s) registered
                </span>
            </div>

            <!-- Add IP Form -->
            <form action="{{ route('settings.security.ip.store') }}" method="POST" class="add-ip-form"
                style="margin-bottom: 1.5rem;">
                @csrf
                <div class="form-group">
                    <label for="ip_address">IP Address</label>
                    <input type="text" class="form-control" id="ip_address" name="ip_address"
                        placeholder="e.g., 192.168.1.100" required value="{{ old('ip_address') }}">
                    @error('ip_address')
                        <small style="color: var(--danger); margin-top: 0.25rem; display: block;">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="label">Label <small style="color: var(--gray);">(optional)</small></label>
                    <input type="text" class="form-control" id="label" name="label" placeholder="e.g., Office, Home, VPN"
                        value="{{ old('label') }}">
                </div>
                <button type="submit" class="btn-primary-custom" style="height: 48px; margin-bottom: 0;">
                    <i class="bi bi-plus-lg"></i> Add IP
                </button>
            </form>

            <!-- IP Table -->
            @if($allowedIps->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="ip-table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>IP Address</th>
                                <th>Browser</th>
                                <th>Location</th>
                                <th>Last Active</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allowedIps as $ip)
                                <tr id="ipRow{{ $ip->id }}">
                                    <td>
                                        <strong>{{ $ip->label ?? '—' }}</strong>
                                        @if($ip->ip_address === $currentIp)
                                            <span
                                                style="background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600; margin-left: 0.5rem;">YOU</span>
                                        @endif
                                        @if($ip->device_token)
                                            <span
                                                style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.65rem; font-weight: 600; margin-left: 0.25rem;">
                                                <i class="bi bi-shield-check"></i> DEVICE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="ip-address-cell">{{ $ip->ip_address }}</td>
                                    <td style="font-size: 0.85rem;">
                                        @if($ip->user_agent)
                                            <div style="display:flex; align-items:center; gap: 0.4rem;">
                                                <i class="bi bi-globe2" style="color: var(--info); font-size: 0.9rem;"></i>
                                                {{ \App\Models\AllowedIp::parseBrowserName($ip->user_agent) }}
                                            </div>
                                        @else
                                            <span style="color: var(--gray);">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.85rem;">
                                        @if($ip->city || $ip->country)
                                            <div style="display:flex; align-items:center; gap: 0.4rem;">
                                                <i class="bi bi-geo-alt" style="color: var(--danger); font-size: 0.9rem;"></i>
                                                {{ implode(', ', array_filter([$ip->city, $ip->country])) }}
                                            </div>
                                        @else
                                            <span style="color: var(--gray);">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--gray);">
                                        @if($ip->last_used_at)
                                            {{ $ip->last_used_at->diffForHumans() }}
                                        @else
                                            Never
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $ip->is_active ? 'active' : 'inactive' }}"
                                            id="statusBadge{{ $ip->id }}">
                                            <i class="bi bi-{{ $ip->is_active ? 'check-circle' : 'pause-circle' }}"></i>
                                            {{ $ip->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-row">
                                            <button type="button" class="btn-success-sm" onclick="toggleIp({{ $ip->id }})"
                                                title="Toggle Status">
                                                <i class="bi bi-{{ $ip->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                            <button type="button" class="btn-danger-custom"
                                                onclick="deleteIp({{ $ip->id }}, '{{ $ip->ip_address }}')" title="Remove Device">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-shield-slash"></i>
                    <p>No trusted devices registered</p>
                    <p style="font-size: 0.85rem;">Add your IP address above or approve device requests to get started</p>
                </div>
            @endif
        </div>

        <!-- Access Requests -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h2 class="settings-card-title">
                    <i class="bi bi-person-raised-hand shield"
                        style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(234, 179, 8, 0.1)); color: #f59e0b;"></i>
                    Access Requests
                </h2>
                <span style="font-size: 0.85rem; color: var(--gray);">
                    @if($pendingRequestCount > 0)
                        <span class="status-badge restriction-on" style="animation: pulse 2s infinite;">
                            <i class="bi bi-bell-fill"></i>
                            {{ $pendingRequestCount }} pending
                        </span>
                    @else
                        No pending requests
                    @endif
                </span>
            </div>

            @if($accessRequests->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="ip-table">
                        <thead>
                            <tr>
                                <th>Name / IP</th>
                                <th>Location</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accessRequests as $req)
                                <tr id="reqRow{{ $req->id }}">
                                    <td>
                                        <strong>{{ $req->name ?? 'Unknown' }}</strong>
                                        <div class="ip-address-cell" style="font-size: 0.8rem;">{{ $req->ip_address }}</div>
                                        @if($req->isp)
                                            <div style="font-size: 0.7rem; color: var(--gray);">{{ $req->isp }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem;">
                                            @if($req->city || $req->country)
                                                <i class="bi bi-geo-alt" style="color: var(--danger); margin-right:2px;"></i>
                                                {{ $req->location }}
                                            @else
                                                <span style="color: var(--gray);">—</span>
                                            @endif
                                        </div>
                                        @if($req->latitude && $req->longitude)
                                            <div style="font-size: 0.7rem; color: var(--gray);">
                                                {{ number_format($req->latitude, 4) }}, {{ number_format($req->longitude, 4) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="max-width: 200px; font-size: 0.85rem;">
                                        {{ $req->reason ?? '—' }}
                                    </td>
                                    <td>
                                        @if($req->status === 'pending')
                                            <span class="status-badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                        @elseif($req->status === 'approved')
                                            <span class="status-badge active">
                                                <i class="bi bi-check-circle"></i> Approved
                                            </span>
                                        @else
                                            <span class="status-badge inactive">
                                                <i class="bi bi-x-circle"></i> Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--gray);">
                                        {{ $req->created_at->format('d M Y, h:i A') }}
                                    </td>
                                    <td>
                                        @if($req->status === 'pending')
                                            <div class="action-row">
                                                <button type="button" class="btn-success-sm"
                                                    onclick="approveRequest({{ $req->id }}, '{{ $req->ip_address }}')"
                                                    title="Approve & Whitelist">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                                <button type="button" class="btn-danger-custom"
                                                    onclick="rejectRequest({{ $req->id }}, '{{ $req->ip_address }}')" title="Reject">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span style="font-size: 0.8rem; color: var(--gray);">
                                                {{ $req->reviewer ? $req->reviewer->name : '—' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-person-badge"></i>
                    <p>No access requests</p>
                    <p style="font-size: 0.85rem;">When blocked users click "Request Access" on the 403 page, their requests
                        will appear here</p>
                </div>
            @endif
        </div>

        <!-- Audit Logs (Blocked Attempts) -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h2 class="settings-card-title">
                    <i class="bi bi-journal-text shield"
                        style="background: rgba(239, 68, 68, 0.1); color: var(--danger);"></i>
                    Blocked IP Audit Log
                </h2>
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <span style="font-size: 0.85rem; color: var(--gray);">
                        {{ $accessLogs->count() }} entries
                    </span>
                    @if($accessLogs->count() > 0)
                        <button type="button" class="btn-danger-custom" onclick="clearLogs()" style="font-size: 0.75rem;">
                            <i class="bi bi-trash"></i> Clear All
                        </button>
                    @endif
                </div>
            </div>

            @if($accessLogs->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="ip-table">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>ISP</th>
                                <th>URL Attempted</th>
                                <th>Blocked At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accessLogs as $log)
                                <tr>
                                    <td class="ip-address-cell">{{ $log->ip_address }}</td>
                                    <td>
                                        <div style="font-size: 0.85rem;">
                                            @if($log->city || $log->country)
                                                <i class="bi bi-geo-alt" style="color: var(--danger); margin-right:2px;"></i>
                                                {{ $log->location }}
                                            @else
                                                <span style="color: var(--gray);">—</span>
                                            @endif
                                        </div>
                                        @if($log->latitude && $log->longitude)
                                            <div style="font-size: 0.7rem; color: var(--gray);">
                                                {{ number_format($log->latitude, 4) }}°, {{ number_format($log->longitude, 4) }}°
                                            </div>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.85rem;">{{ $log->isp ?? '—' }}</td>
                                    <td
                                        style="font-size: 0.8rem; color: var(--gray); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        {{ $log->url ?? '—' }}
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--gray);">
                                        {{ $log->blocked_at->format('d M Y, h:i A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-shield-check"></i>
                    <p>No blocked attempts recorded</p>
                    <p style="font-size: 0.85rem;">Blocked IP access attempts will appear here when IP restriction is active</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Toggle IP Restriction
        document.getElementById('ipRestrictionToggle').addEventListener('change', async function () {
            const isChecked = this.checked;

            try {
                const response = await fetch('{{ route("settings.security.ip-restriction.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: data.enabled ? 'IP Restriction Enabled' : 'IP Restriction Disabled',
                        text: data.message,
                        confirmButtonColor: '#6366f1',
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    // Revert toggle
                    this.checked = !isChecked;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Enable',
                        text: data.message,
                        confirmButtonColor: '#6366f1',
                    });
                }
            } catch (error) {
                this.checked = !isChecked;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to toggle IP restriction.',
                    confirmButtonColor: '#6366f1',
                });
            }
        });

        // Add My IP
        function addMyIp() {
            const currentIp = document.getElementById('currentIpDisplay').textContent.trim();
            document.getElementById('ip_address').value = currentIp;
            document.getElementById('label').value = 'My IP (Auto-added)';

            Swal.fire({
                icon: 'question',
                title: 'Add Your IP?',
                html: `Add <strong>${currentIp}</strong> to the whitelist?`,
                showCancelButton: true,
                confirmButtonText: 'Add IP',
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#64748b',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('.add-ip-form').submit();
                }
            });
        }

        // Toggle IP Active Status
        async function toggleIp(id) {
            try {
                const response = await fetch(`{{ url('admin/settings/security/ip') }}/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to toggle IP status.',
                    confirmButtonColor: '#6366f1',
                });
            }
        }

        // Delete IP
        function deleteIp(id, ipAddress) {
            Swal.fire({
                icon: 'warning',
                title: 'Remove IP?',
                html: `Are you sure you want to remove <strong>${ipAddress}</strong> from the whitelist?`,
                showCancelButton: true,
                confirmButtonText: 'Remove',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `{{ url('admin/settings/security/ip') }}/${id}`;

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        form.appendChild(methodInput);

                        document.body.appendChild(form);
                        form.submit();
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete IP.',
                            confirmButtonColor: '#6366f1',
                        });
                    }
                }
            });
        }

        // Approve Access Request
        function approveRequest(id, ipAddress) {
            Swal.fire({
                icon: 'question',
                title: 'Approve Request?',
                html: `Approve access for <strong>${ipAddress}</strong> and add to whitelist?`,
                showCancelButton: true,
                confirmButtonText: 'Approve',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`{{ url('admin/settings/security/request') }}/${id}/approve`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                text: data.message,
                                confirmButtonColor: '#6366f1',
                            }).then(() => window.location.reload());
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to approve request.', confirmButtonColor: '#6366f1' });
                    }
                }
            });
        }

        // Reject Access Request
        function rejectRequest(id, ipAddress) {
            Swal.fire({
                icon: 'warning',
                title: 'Reject Request?',
                html: `Reject access request from <strong>${ipAddress}</strong>?`,
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`{{ url('admin/settings/security/request') }}/${id}/reject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejected',
                                text: data.message,
                                confirmButtonColor: '#6366f1',
                            }).then(() => window.location.reload());
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to reject request.', confirmButtonColor: '#6366f1' });
                    }
                }
            });
        }

        // Clear All Audit Logs
        function clearLogs() {
            Swal.fire({
                icon: 'warning',
                title: 'Clear All Logs?',
                text: 'This will permanently delete all blocked IP audit logs.',
                showCancelButton: true,
                confirmButtonText: 'Clear All',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('{{ route("settings.security.logs.clear") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Logs Cleared',
                                text: data.message,
                                confirmButtonColor: '#6366f1',
                            }).then(() => window.location.reload());
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to clear logs.', confirmButtonColor: '#6366f1' });
                    }
                }
            });
        }
    </script>
@endpush
