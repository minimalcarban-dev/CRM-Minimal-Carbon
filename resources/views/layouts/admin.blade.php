<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Luxurious-Logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/Luxurious-Logo.png') }}">

    <!-- Set global admin data for JavaScript -->
    <script>
        window.authAdminId = @json(Auth::guard('admin')->user()?->id ?? null);
        window.authAdminIsSuper = @json(Auth::guard('admin')->user()?->is_super ?? false);
        window.authAdminName = @json(Auth::guard('admin')->user()?->name ?? 'Admin');

        // Pusher credentials for Echo
        window.chatPusherKey = @json(config('broadcasting.connections.pusher.key'));
        window.chatPusherCluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    @php
        $viteHotPath = public_path('hot');
        $useViteDevServer = false;
        $viteManifest = null;

        if (file_exists($viteHotPath)) {
            $hotUrl = trim((string) file_get_contents($viteHotPath));
            $hotHost = parse_url($hotUrl, PHP_URL_HOST);
            $requestHost = request()->getHost();
            $localHosts = ['localhost', '127.0.0.1', '::1'];

            $useViteDevServer = (bool) $hotHost && (
                $hotHost === $requestHost ||
                (in_array($hotHost, $localHosts, true) && in_array($requestHost, $localHosts, true))
            );
        }

        if (!$useViteDevServer) {
            $manifestPath = public_path('build/manifest.json');
            if (file_exists($manifestPath)) {
                $viteManifest = json_decode((string) file_get_contents($manifestPath), true);
            }
        }
    @endphp

    @if($useViteDevServer)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @elseif(is_array($viteManifest))
        @php
            $cssFiles = [];
            $mainCss = $viteManifest['resources/css/app.css']['file'] ?? null;
            $mainJs = $viteManifest['resources/js/app.js']['file'] ?? null;
            $jsCss = $viteManifest['resources/js/app.js']['css'] ?? [];

            if ($mainCss) {
                $cssFiles[] = $mainCss;
            }
            foreach ($jsCss as $cssFile) {
                if (!in_array($cssFile, $cssFiles, true)) {
                    $cssFiles[] = $cssFile;
                }
            }
        @endphp

        @foreach($cssFiles as $cssFile)
            <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
        @endforeach
        @if($mainJs)
            <script type="module" src="{{ asset('build/' . $mainJs) }}"></script>
        @endif
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link href="{{ asset('css/diamond.css') }}" rel="stylesheet">
    <link href="{{ asset('css/attributes.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tracker.css') }}" rel="stylesheet">

    @stack('styles')

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;

            /* Theme tokens — light mode */
            --dark: #1e293b;
            --gray: #64748b;
            --muted: #94a3b8;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-sidebar: #ffffff;
            --bg-navbar: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow: rgba(0, 0, 0, 0.05);
            --shadow-md: rgba(0, 0, 0, 0.1);
            --shadow-lg: rgba(0, 0, 0, 0.15);
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
        }

        /* ── DARK MODE ── */
        [data-theme="dark"] {
            --dark: #f1f5f9;
            --gray: #94a3b8;
            --muted: #64748b;
            --light-gray: #1e293b;
            --border: #334155;
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-sidebar: #1e293b;
            --bg-navbar: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --shadow: rgba(0, 0, 0, 0.3);
            --shadow-md: rgba(0, 0, 0, 0.4);
        }

        [data-theme="dark"] body {
            background: var(--bg-body);
            color: var(--text-primary);
        }

        [data-theme="dark"] .sidebar,
        [data-theme="dark"] .top-navbar {
            background: var(--bg-sidebar);
            border-color: var(--border);
        }

        [data-theme="dark"] .nav-link {
            color: var(--gray);
        }

        [data-theme="dark"] .nav-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: var(--dark);
        }

        [data-theme="dark"] .user-card {
            background: rgba(255, 255, 255, 0.04);
        }

        [data-theme="dark"] .notification-btn,
        [data-theme="dark"] .profile-btn,
        [data-theme="dark"] .top-sidebar-toggle {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border);
            color: var(--gray);
        }

        [data-theme="dark"] .notification-menu,
        [data-theme="dark"] .profile-menu {
            background: #1e293b;
            border-color: var(--border);
        }

        [data-theme="dark"] .notification-header {
            background: #1e293b;
        }

        [data-theme="dark"] .notification-item:hover,
        [data-theme="dark"] .notification-item.unread {
            background: rgba(99, 102, 241, 0.08);
        }

        [data-theme="dark"] .notification-divider {
            background: #0f172a;
            color: var(--muted);
        }

        [data-theme="dark"] .notification-message strong {
            color: #f1f5f9;
        }

        [data-theme="dark"] .notification-empty {
            background: transparent;
        }

        [data-theme="dark"] #mainContent {
            background: var(--bg-body);
        }

        [data-theme="dark"] .dropdown-toggle-link {
            color: var(--gray);
        }

        [data-theme="dark"] .dropdown-toggle-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: var(--dark);
        }

        [data-theme="dark"] .logout-btn {
            background: rgba(255, 255, 255, 0.08);
        }

        [data-theme="dark"] .logout-btn:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        [data-theme="dark"] .navbar-title {
            color: var(--text-primary);
        }

        [data-theme="dark"] .stats-pill {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border);
        }

        [data-theme="dark"] .inline-stats {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            color: var(--dark);
        }

        /* Unified Alert Card */
        .alert-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            gap: 1.25rem;
            margin: 1rem 1rem 0 1rem;
            box-shadow: 0 1px 3px var(--shadow);
            border: 2px solid var(--border);
            transition: opacity 0.4s ease, transform 0.4s ease;
            opacity: 1;
        }

        .alert-card.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.05));
            border-color: rgba(16, 185, 129, 0.2);
        }

        .alert-card.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05));
            border-color: rgba(239, 68, 68, 0.2);
        }

        .alert-card.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(217, 119, 6, 0.05));
            border-color: rgba(245, 158, 11, 0.2);
        }

        .alert-card .alert-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            color: #fff;
        }

        .alert-card.success .alert-icon {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .alert-card.danger .alert-icon {
            background: linear-gradient(135deg, var(--danger), #dc2626);
        }

        .alert-card.warning .alert-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .alert-card .alert-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.25rem 0;
        }

        .alert-card .alert-message {
            color: var(--gray);
            margin: 0;
            font-size: 0.95rem;
        }

        .alert-card.alert-hide {
            opacity: 0;
            transform: translateY(-6px);
            pointer-events: none;
        }

        /* Global Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 0;
            padding-bottom: 6rem !important;
            margin-top: 2rem;
        }

        .pagination-container nav {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .pagination-container .pagination {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .pagination-container .page-item {
            list-style: none;
        }

        .pagination-container .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark);
            background: white;
            border: 2px solid var(--border);
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .pagination-container .page-link:hover {
            background: var(--light-gray);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .pagination-container .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-color: var(--primary);
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .pagination-container .page-item.disabled .page-link {
            color: var(--gray);
            background: var(--light-gray);
            border-color: var(--border);
            cursor: not-allowed;
            opacity: 0.5;
        }

        .pagination-container .page-item.disabled .page-link:hover {
            transform: none;
            border-color: var(--border);
        }

        .pagination-container .page-link[rel="prev"],
        .pagination-container .page-link[rel="next"] {
            font-size: 1rem;
            font-weight: 600;
        }

        .pagination-container .page-link[aria-label*="Previous"],
        .pagination-container .page-link[aria-label*="Next"] {
            padding: 0.5rem 1rem;
        }

        /* Pagination Responsive */
        @media (max-width: 768px) {
            .pagination-container {
                padding: 1.5rem 0;
            }

            .pagination-container .page-link {
                min-width: 36px;
                height: 36px;
                padding: 0.4rem 0.6rem;
                font-size: 0.8rem;
            }

            .pagination-container .page-link[aria-label*="Previous"],
            .pagination-container .page-link[aria-label*="Next"] {
                padding: 0.4rem 0.75rem;
            }

            .pagination-container .pagination {
                gap: 0.35rem;
            }
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: white;
            border-right: 2px solid var(--border);
            overflow-x: hidden;
            overflow-y: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80px;
            flex-shrink: 0;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center;
            padding: 1.5rem 0.5rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            /* gap: 0.75rem; */
        }

        .logo-icon {
            width: 100px;
            /* height: 40px; */
            object-fit: contain;
            display: block;
            margin: 0 auto;
            /* Center the block element */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            /* Add transition for smooth change */
        }

        .sidebar.collapsed .logo-icon {
            width: 40px;
            height: 40px;
            margin: 0;
            /* Remove auto margin when collapsed */
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            white-space: nowrap;
            transition: opacity 0.2s;
        }

        .sidebar.collapsed .logo-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .toggle-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 2px solid var(--border);
            background: white;
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .toggle-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .sidebar.collapsed .toggle-btn {
            display: flex;
        }

        /* User Info */
        .user-info {
            /* padding: 1rem; */
            border-bottom: 2px solid var(--border);
            flex-shrink: 0;
        }

        .user-card {
            background: linear-gradient(135deg, var(--light-gray), white);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
        }

        .user-card:hover {
            background: linear-gradient(135deg, #e0e7ff, white);
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .user-details {
            flex: 1;
            min-width: 0;
            transition: opacity 0.2s;
        }

        .sidebar.collapsed .user-details {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .sidebar.collapsed .user-card {
            justify-content: center;
            padding: 0.75rem;
            gap: 0;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 0.8rem;
            color: var(--gray);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Navigation */
        .nav-section {
            flex: 1;
            padding: 1rem 0.3rem;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 0.5rem;
        }

        .nav-section::-webkit-scrollbar {
            width: 4px;
        }

        .nav-section::-webkit-scrollbar-track {
            background: transparent;
            margin: 4px 0;
        }

        .nav-section::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 2px;
        }

        .nav-section::-webkit-scrollbar-thumb:hover {
            background: var(--gray);
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            color: var(--gray);
            font-weight: 500;
            font-size: 0.9375rem;
            transition: all 0.2s;
            position: relative;
            min-height: 44px;
            line-height: 1;
        }

        .nav-link:hover {
            background: var(--light-gray);
            color: var(--dark);
            transform: translateX(2px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .nav-link i {
            font-size: 1.25rem;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            line-height: 1;
        }

        .nav-link span {
            white-space: nowrap;
            transition: opacity 0.2s;
            line-height: 1;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.875rem 0.5rem;
            gap: 0;
        }

        .sidebar.collapsed .nav-link span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Tooltip for collapsed sidebar */
        .sidebar.collapsed .nav-link::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            margin-left: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: var(--dark);
            color: white;
            border-radius: 6px;
            font-size: 0.875rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 1000;
        }

        .sidebar.collapsed .nav-link:hover::after {
            opacity: 1;
        }

        /* Hidden utility class */
        .hidden {
            display: none !important;
        }

        /* Chat Unread Badge Styles */
        #chatUnreadBadge {
            position: absolute;
            right: 14px;
            top: 10px;
            background: var(--danger);
            color: #fff;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.3s;
        }

        #chatUnreadBadge:not(.hidden) {
            display: inline-block;
        }

        /* When sidebar is collapsed, show badge as a dot on top-right of icon */
        .sidebar.collapsed #chatUnreadBadge:not(.hidden) {
            display: block;
            right: 12px;
            top: 12px;
            padding: 0;
            width: 8px;
            height: 8px;
            min-width: 8px;
            min-height: 8px;
            font-size: 0;
            line-height: 0;
            border: 2px solid white;
            box-shadow: 0 0 0 1px var(--danger);
        }

        /* Nav Badge for Drafts */
        .nav-badge {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--warning);
            color: var(--dark);
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            transition: all 0.3s;
        }

        .nav-link.active .nav-badge {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        .sidebar.collapsed .nav-badge {
            display: block;
            right: 10px;
            top: 8px;
            transform: none;
            padding: 0;
            width: 8px;
            height: 8px;
            min-width: 8px;
            min-height: 8px;
            font-size: 0;
            line-height: 0;
            border: 2px solid white;
            box-shadow: 0 0 0 1px var(--warning);
        }

        /* Dropdown Menu Styles */
        .nav-dropdown {
            margin-top: 0.5rem;
        }

        .dropdown-toggle-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            color: var(--gray);
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.2s;
            position: relative;
            min-height: 44px;
            line-height: 1;
            cursor: pointer;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-toggle-link:hover {
            background: var(--light-gray);
            color: var(--dark);
        }

        .dropdown-toggle-link.active {
            background: var(--light-gray);
            color: var(--primary);
        }

        .dropdown-toggle-link .left-content {
            display: flex;
            align-items: center;
            gap: 0rem;
            /* flex: 1; */
        }

        .dropdown-toggle-link i.main-icon {
            font-size: 1.25rem;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            line-height: 1;
        }

        .dropdown-toggle-link .chevron-icon {
            font-size: 1rem;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .dropdown-toggle-link.active .chevron-icon {
            transform: rotate(180deg);
        }

        .dropdown-menu-custom {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding-left: 0.5rem;
        }

        .dropdown-menu-custom.show {
            max-height: 600px;
        }

        .dropdown-menu-custom .nav {
            margin-top: 0.375rem;
        }

        .dropdown-menu-custom .nav-link {
            padding-left: 2.5rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .sidebar.collapsed .dropdown-toggle-link {
            justify-content: center;
            padding: 0.875rem 0.5rem;
            gap: 0;
        }

        .sidebar.collapsed .dropdown-toggle-link .left-content span,
        .sidebar.collapsed .dropdown-toggle-link .chevron-icon {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .sidebar.collapsed .dropdown-menu-custom {
            display: none;
        }

        /* Tooltip for collapsed dropdown */
        .sidebar.collapsed .dropdown-toggle-link::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            margin-left: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: var(--dark);
            color: white;
            border-radius: 6px;
            font-size: 0.875rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 1000;
        }

        .sidebar.collapsed .dropdown-toggle-link:hover::after {
            opacity: 1;
        }

        /* Logout Section */
        .logout-section {
            padding: 1rem;
            border-top: 2px solid var(--border);
            flex-shrink: 0;
        }

        .logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            border-radius: 10px;
            border: none;
            background: var(--dark);
            color: white;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s;
            min-height: 44px;
            line-height: 1;
        }

        .logout-btn:hover {
            background: #2d2d38;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .logout-btn i {
            font-size: 1.25rem;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            line-height: 1;
        }

        .logout-btn span {
            transition: opacity 0.2s;
            line-height: 1;
        }

        .sidebar.collapsed .logout-btn {
            padding: 0.875rem 0.5rem;
            gap: 0;
        }

        .sidebar.collapsed .logout-btn span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Main Content */
        #mainContent {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 2rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed~#mainContent {
            margin-left: var(--sidebar-collapsed);
        }

        /* Toast */
        #toast-container {
            z-index: 9999;
            inset: 16px 16px auto auto;
            /* top-right */
        }

        .toast {
            border-radius: 12px;
            border: none;
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.18);
            overflow: hidden;
            backdrop-filter: blur(8px);
        }

        /* Sleek app-themed toast */
        .toast.custom-toast {
            background: linear-gradient(120deg, #4f46e5, #6c5ce7);
            color: #fff;
            min-width: 280px;
        }

        .toast.custom-toast .toast-body {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .toast.custom-toast .toast-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.14);
            display: grid;
            place-items: center;
            font-size: 16px;
            color: #fff;
        }

        .toast.custom-toast .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }

        .toast.custom-toast .btn-close:hover {
            opacity: 1;
        }

        /* Alert Styles */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
                z-index: 1050 !important;
                /* Above navbar */
            }

            .sidebar.show {
                transform: translateX(0);
            }

            #mainContent {
                margin-left: 0;
            }

            .sidebar.collapsed~#mainContent {
                margin-left: 0;
            }

            /* FORCE disable collapsed styles on mobile */
            .sidebar.collapsed .logo-text,
            .sidebar.collapsed .user-details,
            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .dropdown-toggle-link .left-content span,
            .sidebar.collapsed .dropdown-toggle-link .chevron-icon {
                opacity: 1 !important;
                width: auto !important;
                overflow: visible !important;
                display: block !important;
            }

            .sidebar.collapsed .user-card,
            .sidebar.collapsed .nav-link {
                justify-content: flex-start !important;
                padding: 0.875rem 1rem !important;
                gap: 0.875rem !important;
            }

            .sidebar.collapsed .dropdown-toggle-link {
                justify-content: space-between !important;
                padding: 0.875rem 1rem !important;
                gap: 0.875rem !important;
            }

            .sidebar.collapsed .nav-link::after,
            .sidebar.collapsed .dropdown-toggle-link::after {
                display: none !important;
            }

            .sidebar.collapsed .nav-section-label {
                opacity: 1 !important;
                width: auto !important;
                height: auto !important;
                margin: 1rem 1rem 0.5rem !important;
            }

            .sidebar.collapsed .logo-icon {
                width: 100px !important;
                height: auto !important;
                margin: 0 auto !important;
            }

            .sidebar.collapsed .sidebar-header {
                justify-content: center !important;
                padding: 1.5rem !important;
            }

            /* Mobile close button and overlay */
            .mobile-close-sidebar {
                display: flex !important;
                position: absolute;
                top: 24px;
                right: 15px;
                background: var(--light-gray);
                border: 1px solid var(--border);
                width: 32px;
                height: 32px;
                border-radius: 8px;
                align-items: center;
                justify-content: center;
                color: var(--dark);
                cursor: pointer;
                z-index: 1051;
            }

            [data-theme="dark"] .mobile-close-sidebar {
                background: #1e293b;
                color: #f1f5f9;
            }

            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }
        }

        .mobile-close-sidebar {
            display: none;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1040;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
            z-index: 999;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Top Navbar */
        .top-navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: 72px;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* background: rgba(255, 255, 255, 0.75); */
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            /* border-bottom: 1px solid var(--border); */
            box-shadow: 0 4px 20px var(--shadow);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed~.top-navbar {
            left: var(--sidebar-collapsed);
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            min-width: 0;
        }

        .navbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            line-height: 1;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        /* ── NOTIFICATION DROPDOWN ── */
        .notification-dropdown {
            position: relative;
        }

        .notification-btn {
            position: relative;
            width: 40px;
            height: 38px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: white;
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1.2rem;
        }

        .notification-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            border: 2px solid white;
            line-height: 1.4;
        }

        /* Dropdown panel */
        .notification-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 380px;
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(99, 102, 241, 0.08);
            z-index: 1000;
            display: none;
            flex-direction: column;
            max-height: 520px;
            overflow: hidden;
        }

        .notification-menu.show {
            display: flex;
        }

        /* Header */
        .notification-header {
            padding: 0.9rem 1.1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            background: white;
        }

        .notification-header h6 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .notification-header h6 i {
            color: var(--primary);
            font-size: 1rem;
        }

        .mark-all-read {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            padding: 0.25rem 0.6rem;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.08);
            transition: all 0.2s;
        }

        .mark-all-read:hover {
            background: rgba(99, 102, 241, 0.15);
            color: var(--primary-dark);
        }

        /* Scrollable list */
        .notification-list {
            flex: 1;
            overflow-y: auto;
            max-height: 400px;
        }

        .notification-list::-webkit-scrollbar {
            width: 4px;
        }

        .notification-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .notification-list::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 2px;
        }

        /* Notification row */
        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            padding: 0.8rem 1rem;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
            cursor: pointer;
            position: relative;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: #fafbff;
        }

        .notification-item.unread {
            background: rgba(99, 102, 241, 0.025);
        }

        .notification-item.read {
            opacity: 0.65;
        }

        .notification-item.read:hover {
            opacity: 1;
        }

        /* Unread left bar */
        .notification-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2.5px;
            background: var(--primary);
            border-radius: 0 2px 2px 0;
        }

        /* Color-coded icon */
        .notification-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
            border: 1px solid transparent;
        }

        /* Icon color variants */
        .notif-icon-red {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.18);
        }

        .notif-icon-amber {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.18);
        }

        .notif-icon-blue {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-color: rgba(59, 130, 246, 0.18);
        }

        .notif-icon-green {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.18);
        }

        .notif-icon-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
            border-color: rgba(139, 92, 246, 0.18);
        }

        .notif-icon-gray {
            background: rgba(100, 116, 139, 0.08);
            color: var(--gray);
            border-color: var(--border);
        }

        /* Active (unread) icons — solid gradient */
        .notification-item.unread .notif-icon-red {
            background: linear-gradient(135deg, #f87171, #ef4444);
            color: white;
            border-color: transparent;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
        }

        .notification-item.unread .notif-icon-amber {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            border-color: transparent;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
        }

        .notification-item.unread .notif-icon-blue {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            color: white;
            border-color: transparent;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
        }

        .notification-item.unread .notif-icon-green {
            background: linear-gradient(135deg, #34d399, #10b981);
            color: white;
            border-color: transparent;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
        }

        .notification-item.unread .notif-icon-purple {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            color: white;
            border-color: transparent;
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3);
        }

        .notification-item.unread .notif-icon-gray {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            color: white;
            border-color: transparent;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        /* Type badge */
        .notif-type-tag {
            display: inline-flex;
            align-items: center;
            font-size: 0.6rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 0.1rem 0.38rem;
            border-radius: 5px;
            margin-bottom: 0.18rem;
        }

        .tag-red {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .tag-amber {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .tag-blue {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .tag-green {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .tag-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .tag-gray {
            background: var(--light-gray);
            color: var(--gray);
        }

        .notification-title-row {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-bottom: 0.18rem;
            flex-wrap: wrap;
        }

        .notification-message {
            margin: 0 0 0.2rem 0;
            font-size: 0.8rem;
            color: var(--gray);
            line-height: 1.45;
            word-wrap: break-word;
        }

        .notification-message strong {
            color: var(--dark);
            font-weight: 700;
            font-size: 0.82rem;
        }

        .notification-preview {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.2rem;
            font-style: italic;
            line-height: 1.3;
        }

        .notification-preview .text-primary {
            color: #6366f1 !important;
        }

        .notification-preview .fw-semibold {
            font-weight: 600;
        }

        .notification-time {
            font-size: 0.7rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 0.2rem;
            margin-top: 0.25rem;
        }

        .notification-time i {
            font-size: 0.65rem;
            color: var(--primary);
        }

        /* Close btn */
        .notification-close {
            border: none;
            background: none;
            color: #c4cdd8;
            cursor: pointer;
            font-size: 0.85rem;
            padding: 0.2rem;
            flex-shrink: 0;
            transition: color 0.2s;
            border-radius: 5px;
            line-height: 1;
        }

        .notification-close:hover {
            color: var(--danger);
            background: rgba(239, 68, 68, 0.08);
        }

        /* Empty state */
        .notification-empty {
            padding: 2.5rem 1rem;
            text-align: center;
            color: var(--gray);
        }

        .notification-empty .empty-icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.07);
            border: 1.5px solid rgba(99, 102, 241, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--primary);
            margin: 0 auto 0.75rem;
        }

        .notification-empty p {
            margin: 0;
            font-size: 0.82rem;
            color: var(--muted, #94a3b8);
        }

        /* Divider */
        .notification-divider {
            padding: 0.4rem 1rem;
            background: #f8fafc;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        /* Download link inside notification */
        .notif-download-link {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            padding: 0.15rem 0.45rem;
            border-radius: 6px;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.15);
            margin-top: 0.3rem;
            transition: all 0.2s;
        }

        .notif-download-link:hover {
            background: rgba(99, 102, 241, 0.15);
            color: var(--primary-dark);
        }

        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
        }

        .profile-btn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px var(--shadow);
        }

        .profile-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .profile-avatar {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            /* font-size: 0.85rem; */
        }

        .profile-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            width: 250px;
            background: white;
            border: 2px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
            flex-direction: column;
        }

        .top-sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--gray);
            cursor: pointer;
            margin-right: 0.5rem;
            padding: 0;
            line-height: 1;
            font-size: 1rem;
        }

        .top-sidebar-toggle i {
            font-size: 1.1rem;
            line-height: 1;
        }

        .top-sidebar-toggle:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .profile-menu.show {
            display: flex;
        }

        .profile-header {
            padding: 1rem;
            border-bottom: 2px solid var(--border);
        }

        /* .profile-name {
            margin: 0 0 0.25rem 0;
            font-weight: 600;
            color: var(--light-gray);
            font-size: 0.95rem;
        } */

        /* .profile-email { */
        /* margin: 0; */
        /* font-size: 0.85rem;
            color: var(--light-gray);
            } */

        .profile-divider {
            height: 2px;
            background: var(--border);
        }

        .profile-logout {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            border: none;
            background: transparent;
            color: var(--danger);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        .profile-logout:hover {
            background: rgba(239, 68, 68, 0.05);
        }

        /* Adjust main content for navbar */
        #mainContent {
            margin-left: var(--sidebar-width);
            margin-top: 70px;
            padding: 2rem;
            transition: margin-left 0.3s;
        }

        .sidebar.collapsed~.top-navbar~#mainContent {
            margin-left: var(--sidebar-collapsed);
        }

        /* Responsive for top navbar */
        @media (max-width: 768px) {
            .top-navbar {
                left: 0;
                padding: 0 1rem;
            }

            .sidebar.collapsed~.top-navbar {
                left: 0;
            }

            .notification-menu {
                width: calc(100vw - 2rem);
                left: 50%;
                right: auto;
                transform: translateX(-50%);
            }

            .profile-menu {
                width: calc(100vw - 2rem);
                left: 50%;
                right: auto;
                transform: translateX(-50%);
            }

            .navbar-title {
                font-size: 0.95rem;
                font-weight: 600;
                line-height: 1;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: clamp(90px, 34vw, 150px);
            }

            /* Keep right controls compact on mobile */
            .greeting-wrapper,
            .live-clock {
                display: none !important;
            }

            .navbar-right {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: nowrap;
            }

            .navbar-left {
                min-width: 0;
                gap: 0.5rem;
            }

            .navbar-right .dark-mode-btn,
            .navbar-right .notification-btn,
            .navbar-right>a.dark-mode-btn,
            .navbar-right .profile-btn {
                width: 40px;
                height: 40px;
                min-width: 40px;
                padding: 0;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
            }

            .navbar-right .dark-mode-btn i,
            .navbar-right .notification-btn i,
            .navbar-right>a.dark-mode-btn i {
                width: 18px;
                height: 18px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                line-height: 1;
            }

            .navbar-right .profile-btn {
                overflow: hidden;
            }

            .navbar-right .profile-avatar {
                width: 30px;
                height: 30px;
                font-size: 12px;
                line-height: 1;
            }

            .navbar-right #darkModeBtn {
                order: 1;
            }

            .navbar-right .notification-dropdown {
                order: 2;
                display: block !important;
            }

            .navbar-right>a.dark-mode-btn {
                order: 3;
            }

            .navbar-right .profile-dropdown {
                order: 4;
            }
        }

        @media (max-width: 575px) {
            #mainContent {
                margin-left: 0px;
                padding: 0 10px;
            }

            h2.navbar-title,
            .navbar-right .notification-btn i,
            .notification-header h6,
            .notification-empty p,
            .notification-icon,
            .page-subtitle {
                font-size: 12px;
            }

            .navbar-left button#topSidebarToggle {
                display: none;
            }

            .notification-badge {
                font-size: 7px;
                padding: 1px 5px;
            }

            .navbar-right {
                gap: 10px;
            }

            /* In small mobile, always show bell next to dark mode and hide date/clock */
            .greeting-wrapper,
            .live-clock {
                display: none !important;
            }

            .navbar-right {
                gap: 8px;
                flex-wrap: nowrap;
            }

            .navbar-right .dark-mode-btn,
            .navbar-right .notification-btn,
            .navbar-right>a.dark-mode-btn,
            .navbar-right .profile-btn {
                width: 38px;
                height: 38px;
                min-width: 38px;
                padding: 0;
            }

            .navbar-right .dark-mode-btn i,
            .navbar-right .notification-btn i,
            .navbar-right>a.dark-mode-btn i {
                width: 17px;
                height: 17px;
                font-size: 17px;
                line-height: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .navbar-right .profile-avatar {
                width: 28px;
                height: 28px;
                font-size: 11px;
                line-height: 1;
                border-radius: 50%;
            }

            .navbar-right #darkModeBtn {
                order: 1;
            }

            .navbar-right .notification-dropdown {
                order: 2;
                display: block !important;
            }

            .navbar-right .notification-btn {
                display: flex !important;
            }

            .navbar-right>a.dark-mode-btn {
                order: 3;
            }

            .navbar-right .profile-dropdown {
                order: 4;
            }

            .notification-menu {
                position: fixed;
                top: 74px;
                left: 8px;
                right: 8px;
                width: auto;
                max-height: calc(100vh - 86px);
                transform: none !important;
                border-radius: 14px;
                z-index: 2005;
            }

            .notification-list {
                max-height: calc(100vh - 170px);
            }

            .notification-header {
                padding: 10px 10px;
            }

            .notification-empty i {
                font-size: 23px;
            }

            .notification-divider {
                padding: 6px 4px;
                font-size: 13px;
            }

            .notification-item {
                gap: 8px;
                padding: 8px;
            }

            .notification-icon {
                width: 30px;
                height: 30px;
            }

            .notification-message {
                font-size: 11PX;
            }

            .notification-time {
                font-size: 10PX;
            }

            .notification-footer a {
                font-size: 12px !important;
            }

            .profile-menu {
                width: calc(100vw - 25px);
                left: 0;
                right: 0;
                transform: translateX(-78%);
            }

            .breadcrumb-current,
            .breadcrumb-link {
                font-size: 11px;
            }

            .page-title {
                font-size: 17px;
            }

            .page-header {
                padding: 15px;
                margin-bottom: 15px;
            }

            .toast.custom-toast {
                min-width: 100%;
                font-size: 19px;
                width: 100%;
            }

            .toast.custom-toast .toast-body,
            .toast.custom-toast .btn-close {
                font-size: 12px;
            }

            .toast.custom-toast .toast-icon {
                font-size: 14px;
            }

            h1.dashboard-title {
                font-size: 16px;
                margin-top: 6px;
            }

            p.dashboard-subtitle {
                font-size: 12px;
            }

            .welcome-card .welcome-content {
                gap: 15px;
                margin-bottom: 15px;
            }

            .welcome-card .welcome-title {
                font-size: 14px;
            }

            #mainContent .welcome-card {
                padding: 15px;
                margin-bottom: 13px;
            }

            #mainContent .btn-primary-modern,
            #mainContent .btn-secondary-modern {
                padding: 5px 9px;
                font-size: 12px;
            }

            .section-header {
                padding: 10px;
            }

            #mainContent .section-title,
            #mainContent .quick-link-title {
                font-size: 14px;
            }

            #mainContent .section-subtitle,
            #mainContent .quick-link-description {
                font-size: 11px;
            }

            #mainContent .welcome-icon,
            #mainContent .quick-link-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            #mainContent .quick-link-card {
                padding: 10px;
                gap: 10px;
            }
        }

        /* Mobile dropdown UX fix: stable, reachable, and non-overlapping with bottom nav */
        @media (max-width: 768px) {
            .notification-menu {
                position: fixed;
                top: calc(72px + 0.5rem);
                left: 0.75rem;
                right: 0.75rem;
                width: auto;
                margin-top: 0;
                transform: none !important;
                border-radius: 14px;
                z-index: 2005;
                max-height: calc(100dvh - 72px - 72px - 1rem);
            }

            .notification-list {
                max-height: calc(100dvh - 72px - 72px - 140px);
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
            }

            .profile-menu {
                position: fixed;
                top: calc(72px + 0.5rem);
                right: 0.75rem;
                left: auto;
                width: min(320px, calc(100vw - 1.5rem));
                margin-top: 0;
                transform: none !important;
                border-radius: 14px;
                z-index: 2006;
            }
        }

        @media (max-width: 575px) {
            .notification-menu {
                left: 0.5rem;
                right: 0.5rem;
                max-height: calc(100dvh - 72px - 72px - 0.75rem);
            }

            .notification-list {
                max-height: calc(100dvh - 72px - 72px - 145px);
            }

            .profile-menu {
                right: 0.5rem;
                width: min(300px, calc(100vw - 1rem));
            }
        }

        /* ══════════════════════════════════════════════
            NEW ENHANCEMENTS
        ══════════════════════════════════════════════ */

        /* ── SIDEBAR NAV SECTION LABELS ── */
        .nav-section-label {
            font-size: 0.6rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted, #94a3b8);
            padding: 1rem 1rem 0.35rem;
            margin-top: 0.25rem;
            opacity: 1;
            transition: opacity 0.2s;
        }

        .sidebar.collapsed .nav-section-label {
            opacity: 0;
            height: 0;
            padding: 0;
            overflow: hidden;
        }

        /* ── Active nav links — premium 3-stop gradient finish ── */
        .nav-link.active.cat-orders,
        .nav-link.active.cat-inventory,
        .nav-link.active.cat-finance,
        .nav-link.active.cat-clients,
        .nav-link.active.cat-system {
            background: linear-gradient(135deg, #71c0f8 0%, #2053c7 45%, #1b2f99 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.5);
        }

        .nav-link.active {
            padding-left: 1rem;
        }

        /* ── GLOBAL SEARCH ── */
        .global-search-wrapper {
            position: relative;
            flex: 1;
            max-width: 340px;
            margin: 0 1rem;
        }

        .global-search-input {
            width: 100%;
            height: 38px;
            padding: 0 1rem 0 2.5rem;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: var(--bg-body, #f8fafc);
            color: var(--text-primary, #1e293b);
            font-size: 0.85rem;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        .global-search-input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .global-search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted, #94a3b8);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .global-search-kbd {
            position: absolute;
            right: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            font-weight: 700;
            color: var(--muted, #94a3b8);
            background: var(--border);
            padding: 0.1rem 0.35rem;
            border-radius: 6px;
            letter-spacing: 0.02em;
        }

        /* ── MOBILE SEARCH BUTTON (visible only on mobile) ── */
        .mobile-search-btn {
            display: none;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: transparent;
            color: var(--gray);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .mobile-search-btn:hover,
        .mobile-search-btn:active {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.08);
        }

        /* ── MOBILE SEARCH HINT TOAST ── */
        .mob-search-hint {
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            padding: 0.65rem 1.2rem;
            border-radius: 14px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            pointer-events: none;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .mob-search-hint.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        @media (max-width: 768px) {
            .global-search-wrapper {
                display: none !important;
            }

            .mobile-search-btn {
                display: none !important;
            }
        }

        /* ── COMMAND PALETTE ── */
        .cmd-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 9998;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding-top: 12vh;
        }

        .cmd-overlay.open {
            display: flex;
        }

        .cmd-palette {
            width: 580px;
            max-width: calc(100vw - 2rem);
            background: white;
            border-radius: 20px;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            border: 1.5px solid var(--border);
        }

        [data-theme="dark"] .cmd-palette {
            background: #1e293b;
            border-color: #334155;
        }

        .cmd-search-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .cmd-search-row i {
            color: var(--primary);
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .cmd-input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 1rem;
            color: var(--text-primary, #1e293b);
            background: transparent;
            font-family: inherit;
        }

        [data-theme="dark"] .cmd-input {
            color: #f1f5f9;
        }

        .cmd-close-hint {
            font-size: 0.7rem;
            color: var(--muted);
            background: var(--light-gray);
            padding: 0.15rem 0.4rem;
            border-radius: 5px;
            white-space: nowrap;
        }

        .cmd-results {
            max-height: 360px;
            overflow-y: auto;
            padding: 0.5rem 0;
        }

        .cmd-group-label {
            font-size: 0.62rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            padding: 0.6rem 1.25rem 0.25rem;
        }

        .cmd-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1.25rem;
            cursor: pointer;
            transition: background 0.15s;
            color: var(--text-primary, #1e293b);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .cmd-item:hover,
        .cmd-item.active {
            background: rgba(99, 102, 241, 0.07);
        }

        [data-theme="dark"] .cmd-item:hover,
        [data-theme="dark"] .cmd-item.active {
            background: rgba(99, 102, 241, 0.15);
        }

        .cmd-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .cmd-item-text {
            flex: 1;
        }

        .cmd-item-hint {
            font-size: 0.7rem;
            color: var(--muted);
        }

        .cmd-footer {
            border-top: 1px solid var(--border);
            padding: 0.6rem 1.25rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            font-size: 0.7rem;
            color: var(--muted);
        }

        .cmd-key {
            background: var(--light-gray);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 0.1rem 0.35rem;
            font-size: 0.65rem;
            font-weight: 700;
        }

        /* ── SPEED DIAL (floating +) ── */
        .speed-dial {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 900;
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            gap: 0.75rem;
        }

        .speed-dial-main {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 2;
        }

        .speed-dial-main:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 28px rgba(99, 102, 241, 0.55);
        }

        .speed-dial-main.open {
            transform: rotate(45deg);
        }

        .speed-dial-items {
            display: flex;
            flex-direction: column-reverse;
            gap: 0.6rem;
            align-items: center;
            pointer-events: none;
            opacity: 0;
            transform: translateY(8px);
            transition: all 0.25s ease;
        }

        .speed-dial-items.open {
            pointer-events: all;
            opacity: 1;
            transform: translateY(0);
        }

        .sd-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .sd-label {
            background: var(--bg-card, white);
            color: var(--text-primary, #1e293b);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            white-space: nowrap;
            border: 1px solid var(--border);
        }

        .sd-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
            text-decoration: none;
        }

        .sd-btn:hover {
            transform: scale(1.12);
            color: white;
        }

        .sd-btn.order {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
        }

        .sd-btn.diamond {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
        }

        .sd-btn.client {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
        }

        /* ── DARK MODE TOGGLE ── */
        .dark-mode-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: transparent;
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1rem;
        }

        .dark-mode-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.06);
        }

        /* ── MOBILE BOTTOM NAV ── */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--bg-card, white);
            border-top: 1.5px solid var(--border);
            z-index: 1100;
            padding: 0 0.5rem;
            align-items: center;
            justify-content: space-around;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
        }

        .greeting-wrapper {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .greeting-chip {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .live-clock {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .dark-mode-btn,
        .notification-btn,
        .top-sidebar-toggle {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px var(--shadow);
        }

        .dark-mode-btn:hover,
        .notification-btn:hover,
        .top-sidebar-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--shadow-md);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 50px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .navbar-title {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        @media (max-width: 768px) {
            .mobile-bottom-nav {
                display: flex;
            }

            #mainContent {
                padding-bottom: 70px;
            }

            .speed-dial {
                bottom: 5rem;
            }
        }

        .mob-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.15rem;
            color: var(--muted, #94a3b8);
            text-decoration: none;
            padding: 0.4rem 0.75rem;
            border-radius: 12px;
            transition: all 0.2s;
            font-size: 0.6rem;
            font-weight: 600;
            min-width: 52px;
        }

        .mob-nav-item i {
            font-size: 1.25rem;
        }

        .mob-nav-item.active,
        .mob-nav-item:hover {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.08);
        }

        .mob-nav-item.active i {
            color: var(--primary);
        }

        /* ── GREETING CHIP in navbar ── */
        .greeting-chip {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray);
            white-space: nowrap;
            display: none;
        }

        @media (min-width: 1100px) {
            .greeting-chip {
                display: block;
            }
        }

        /* ── ACTIVITY TIME CHIP ── */
        .live-clock {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--muted, #94a3b8);
            letter-spacing: 0.03em;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            text-align: end;
        }
    </style>
    @stack('head')
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <!-- Close button visible only on mobile -->
        <button id="mobileCloseSidebar" class="mobile-close-sidebar">
            <i class="bi bi-x-lg"></i>
        </button>

        <!-- Sidebar Header -->
        <!-- <div class="sidebar-header">
            <div class="logo-section">
                <a href="{{ route('admin.dashboard') }}"
                    style="display:flex; align-items:center; gap:0.5rem; text-decoration:none;">
                    <img src="{{ asset('images/Luxurious-Logo.png') }}" alt="Logo" class="logo-icon">
                    <span class="logo-text">Carbon</span>
                </a>
            </div>
        </div> -->

        <!-- Navigation -->
        <div class="nav-section">
            <ul class="nav">

                {{-- ── MAIN ── --}}
                <div class="nav-section-label">Main</div>
                <li>
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active cat-system' : '' }}"
                        href="{{ route('admin.dashboard') }}" data-tooltip="Dashboard">
                        <i class="bi bi-house"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['chat.access']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('chat.*') ? 'active cat-system' : '' }}"
                            href="{{ route('chat.index') }}" data-tooltip="Chat" id="chatSidebarLink">
                            <i class="bi bi-chat-dots"></i>
                            <span>Chat</span>
                            <span id="chatUnreadBadge" class="hidden"></span>
                        </a>
                    </li>
                @endif

                {{-- ── ORDERS & SALES ── --}}
                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['orders.view', 'orders.create', 'sales.view_all', 'invoices.view', 'invoices.create', 'purchases.view', 'purchases.create', 'expenses.view', 'expenses.create', 'gold-tracking.view', 'gold-tracking.create', 'factories.view', 'factories.create']))
                    <div class="nav-section-label">Orders & Sales</div>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['orders.view', 'orders.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('orders.*') && !request()->routeIs('orders.drafts.*') ? 'active cat-orders' : '' }}"
                            href="{{ route('orders.index') }}" data-tooltip="Orders">
                            <i class="bi bi-basket"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['sales.view_all']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('companies.all-sales-dashboard') ? 'active cat-orders' : '' }}"
                            href="{{ route('companies.all-sales-dashboard') }}" data-tooltip="All Sales">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>All Sales</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['invoices.view', 'invoices.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active cat-finance' : '' }}"
                            href="{{ route('invoices.index') }}" data-tooltip="Invoices">
                            <i class="bi bi-receipt"></i>
                            <span>Invoices</span>
                        </a>
                    </li>
                @endif

                {{-- ── EXPENSES (moved under Orders & Sales) ── --}}
                @php
                    $expensesActive = request()->routeIs(['purchases.*', 'expenses.*', 'gold-tracking.*', 'factories.*']);
                @endphp
                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['purchases.view', 'purchases.create', 'expenses.view', 'expenses.create', 'gold-tracking.view', 'gold-tracking.create', 'factories.view', 'factories.create']))
                    <div class="nav-dropdown">
                        <button class="dropdown-toggle-link {{ $expensesActive ? 'active' : '' }}" id="expensesDropdown"
                            data-tooltip="Expenses" data-initial-open="{{ $expensesActive ? '1' : '0' }}" type="button"
                            aria-expanded="false" style="padding-left: 23px;">
                            <div class="left-content">
                                <i class="bi bi-cash-stack main-icon"></i>
                                <span style="padding-left: 15px">Expenses</span>
                            </div>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="dropdown-menu-custom {{ $expensesActive ? 'show' : '' }}" id="expensesMenu">
                            <ul class="nav">
                                @if (auth()->guard('admin')->user()->canAccessAny(['purchases.view', 'purchases.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active cat-finance' : '' }}"
                                            href="{{ route('purchases.index') }}" data-tooltip="Purchase Tracker">
                                            <i class="bi bi-cart-check"></i>
                                            <span>Purchase Tracker</span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->guard('admin')->user()->canAccessAny(['expenses.view', 'expenses.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active cat-finance' : '' }}"
                                            href="{{ route('expenses.index') }}" data-tooltip="Office Expenses">
                                            <i class="bi bi-wallet2"></i>
                                            <span>Office Expenses</span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->guard('admin')->user()->canAccessAny(['gold-tracking.view', 'gold-tracking.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('gold-tracking.*') ? 'active cat-finance' : '' }}"
                                            href="{{ route('gold-tracking.index') }}" data-tooltip="Gold Tracking">
                                            <i class="bi bi-coin"></i>
                                            <span>Gold Tracking</span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->guard('admin')->user()->canAccessAny(['factories.view', 'factories.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('factories.*') ? 'active cat-finance' : '' }}"
                                            href="{{ route('factories.index') }}" data-tooltip="Factories">
                                            <i class="bi bi-buildings"></i>
                                            <span>Factories</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- ── INVENTORY ── --}}
                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.view', 'diamonds.create', 'melee_diamonds.view', 'melee_diamonds.create', 'packages.view', 'packages.create', 'diamond_jobs.view']))
                    <div class="nav-section-label">Inventory</div>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.view', 'diamonds.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('diamond.index') || (request()->routeIs('diamond.*') && !request()->routeIs('diamond.job.*')) ? 'active cat-inventory' : '' }}"
                            href="{{ route('diamond.index') }}" data-tooltip="Diamonds">
                            <i class="bi bi-gem"></i>
                            <span>Stock List</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['melee_diamonds.view', 'melee_diamonds.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('melee.*') ? 'active cat-inventory' : '' }}"
                            href="{{ route('melee.index') }}" data-tooltip="Melee Inventory">
                            <i class="bi bi-grid-3x3-gap"></i>
                            <span>Melee Inventory</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['packages.view', 'packages.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('packages.*') ? 'active cat-inventory' : '' }}"
                            href="{{ route('packages.index') }}" data-tooltip="Packages">
                            <i class="bi bi-box-seam"></i>
                            <span>Packages</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamond_jobs.view']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('diamond.job.*') ? 'active cat-inventory' : '' }}"
                            href="{{ route('diamond.job.history') }}" data-tooltip="Job History">
                            <i class="bi bi-clock-history"></i>
                            <span>Job History</span>
                        </a>
                    </li>
                @endif

                {{-- ── CLIENTS ── --}}
                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['parties.view', 'parties.create', 'clients.view', 'leads.view', 'leads.create']))
                    <div class="nav-section-label">Clients</div>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['parties.view', 'parties.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('parties.*') ? 'active cat-clients' : '' }}"
                            href="{{ route('parties.index') }}" data-tooltip="Parties">
                            <i class="bi bi-people"></i>
                            <span>Vendors</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['clients.view']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('clients.*') ? 'active cat-clients' : '' }}"
                            href="{{ route('clients.index') }}" data-tooltip="Clients">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Shoppers</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['leads.view', 'leads.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('leads.*') ? 'active cat-clients' : '' }}"
                            href="{{ route('leads.index') }}" data-tooltip="Leads Inbox">
                            <i class="bi bi-inbox"></i>
                            <span>Leads Inbox</span>
                        </a>
                    </li>
                @endif

                {{-- ── SYSTEM ── --}}
                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['admins.view', 'admins.create', 'permissions.view', 'permissions.create', 'meta_leads.settings', 'settings.manage']))
                    <div class="nav-section-label">System</div>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['admins.view', 'admins.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('admins.*') ? 'active cat-system' : '' }}"
                            href="{{ route('admins.index') }}" data-tooltip="Admins">
                            <i class="bi bi-people"></i>
                            <span>Admins</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['permissions.view', 'permissions.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('permissions.*') ? 'active cat-system' : '' }}"
                            href="{{ route('permissions.index') }}" data-tooltip="Permissions">
                            <i class="bi bi-shield-lock"></i>
                            <span>Permissions</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['meta_leads.settings']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('settings.meta.*') ? 'active cat-system' : '' }}"
                            href="{{ route('settings.meta.index') }}" data-tooltip="Meta Settings">
                            <i class="bi bi-gear"></i>
                            <span>Meta Settings</span>
                        </a>
                    </li>
                @endif

                {{-- Tools Dropdown --}}
                @php
                    $toolsActive = request()->routeIs('tools.*');
                @endphp
                <div class="nav-dropdown">
                    <button class="dropdown-toggle-link {{ $toolsActive ? 'active' : '' }}" id="toolsDropdown"
                        data-tooltip="Tools" data-initial-open="{{ $toolsActive ? '1' : '0' }}" type="button"
                        aria-expanded="false" style="padding-left: 23px;">
                        <div class="left-content">
                            <i class="bi bi-tools main-icon"></i>
                            <span style="padding-left: 15px">Tools</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </button>
                    <div class="dropdown-menu-custom {{ $toolsActive ? 'show' : '' }}" id="toolsMenu">
                        <ul class="nav">
                            <li>
                                <a class="nav-link {{ request()->routeIs('tools.jewellery-calculator') ? 'active' : '' }}"
                                    href="{{ route('tools.jewellery-calculator') }}"
                                    data-tooltip="Jewellery Calculator">
                                    <i class="bi bi-calculator"></i>
                                    <span>Jewellery Calc</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>


                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['mail.access']))
                    @php
                        $emailActive = request()->routeIs('email.*');
                    @endphp
                    <div class="nav-dropdown">
                        <button class="dropdown-toggle-link {{ $emailActive ? 'active' : '' }}" id="emailDropdown"
                            data-tooltip="Email System" data-initial-open="{{ $emailActive ? '1' : '0' }}" type="button"
                            aria-expanded="false" style="padding-left: 23px;">
                            <div class="left-content">
                                <i class="bi bi-envelope-check main-icon"></i>
                                <span style="padding-left: 15px">Email System</span>
                            </div>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="dropdown-menu-custom {{ $emailActive ? 'show' : '' }}" id="emailMenu">
                            <ul class="nav">
                                <li>
                                    <a class="nav-link {{ request()->routeIs('email.accounts.list') ? 'active' : '' }}"
                                        href="{{ route('email.accounts.list') }}" data-tooltip="Manage Accounts">
                                        <i class="bi bi-person-gear"></i>
                                        <span>Accounts</span>
                                    </a>
                                </li>
                                @php
                                    $recentAccount = \App\Modules\Email\Models\EmailAccount::where('is_active', true)->first();
                                @endphp
                                @if($recentAccount)
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('email.inbox') ? 'active' : '' }}"
                                            href="{{ route('email.inbox', $recentAccount->id) }}" data-tooltip="Inbox">
                                            <i class="bi bi-inbox-fill"></i>
                                            <span>Inbox</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endif




                @php
                    $attributesActive = request()->routeIs(['companies.*', 'metal_types.*', 'setting_types.*', 'closure_types.*', 'ring_sizes.*', 'stone_types.*', 'stone_shapes.*', 'stone_colors.*', 'diamond_clarities.*', 'diamond_cuts.*']);
                @endphp
                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['companies.view', 'companies.create', 'metal_types.view', 'metal_types.create', 'setting_types.view', 'setting_types.create', 'closure_types.view', 'closure_types.create', 'ring_sizes.view', 'ring_sizes.create', 'stone_types.view', 'stone_types.create', 'stone_shapes.view', 'stone_shapes.create', 'stone_colors.view', 'stone_colors.create', 'diamond_clarities.view', 'diamond_clarities.create', 'diamond_cuts.view', 'diamond_cuts.create']))
                    <div class="nav-dropdown">
                        <button class="dropdown-toggle-link {{ $attributesActive ? 'active' : '' }}" id="attributesDropdown"
                            data-tooltip="Attributes" data-initial-open="{{ $attributesActive ? '1' : '0' }}" type="button"
                            aria-expanded="false" style="padding-left: 23px;">
                            <div class="left-content">
                                <i class="bi bi-grid main-icon"></i>
                                <span style="padding-left: 15px">Attributes</span>
                            </div>
                            <i class="bi bi-chevron-down chevron-icon"></i>
                        </button>
                        <div class="dropdown-menu-custom {{ $attributesActive ? 'show' : '' }}" id="attributesMenu">
                            <ul class="nav">
                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['companies.view', 'companies.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}"
                                            href="{{ route('companies.index') }}" data-tooltip="Companies">
                                            <i class="bi bi-buildings"></i>
                                            <span>Company</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['metal_types.view', 'metal_types.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('metal_types.*') ? 'active' : '' }}"
                                            href="{{ route('metal_types.index') }}" data-tooltip="Metal Types">
                                            <i class="bi bi-award"></i>
                                            <span>Metal Types</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['setting_types.view', 'setting_types.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('setting_types.*') ? 'active' : '' }}"
                                            href="{{ route('setting_types.index') }}" data-tooltip="Setting Types">
                                            <i class="bi bi-gear"></i>
                                            <span>Setting Types</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['closure_types.view', 'closure_types.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('closure_types.*') ? 'active' : '' }}"
                                            href="{{ route('closure_types.index') }}" data-tooltip="Closure Types">
                                            <i class="bi bi-link-45deg"></i>
                                            <span>Closure Types</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['ring_sizes.view', 'ring_sizes.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('ring_sizes.*') ? 'active' : '' }}"
                                            href="{{ route('ring_sizes.index') }}" data-tooltip="Ring Sizes">
                                            <i class="bi bi-circle"></i>
                                            <span>Ring Sizes</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['stone_types.view', 'stone_types.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('stone_types.*') ? 'active' : '' }}"
                                            href="{{ route('stone_types.index') }}" data-tooltip="Stone Types">
                                            <i class="bi bi-gem"></i>
                                            <span>Stone Types</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['stone_shapes.view', 'stone_shapes.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('stone_shapes.*') ? 'active' : '' }}"
                                            href="{{ route('stone_shapes.index') }}" data-tooltip="Stone Shapes">
                                            <i class="bi bi-square"></i>
                                            <span>Stone Shapes</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['stone_colors.view', 'stone_colors.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('stone_colors.*') ? 'active' : '' }}"
                                            href="{{ route('stone_colors.index') }}" data-tooltip="Stone Colors">
                                            <i class="bi bi-droplet"></i>
                                            <span>Stone Colors</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamond_clarities.view', 'diamond_clarities.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('diamond_clarities.*') ? 'active' : '' }}"
                                            href="{{ route('diamond_clarities.index') }}" data-tooltip="Diamond Clarities">
                                            <i class="bi bi-card-list"></i>
                                            <span>Diamond Clarities</span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamond_cuts.view', 'diamond_cuts.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('diamond_cuts.*') ? 'active' : '' }}"
                                            href="{{ route('diamond_cuts.index') }}" data-tooltip="Diamond Cuts">
                                            <i class="bi bi-scissors"></i>
                                            <span>Diamond Cuts</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endif

            </ul>

        </div>
    </nav>

    <!-- Top Navbar with Notifications -->
    <div class="top-navbar">
        <div class="navbar-content">

            <div class="navbar-left">
                <button class="top-sidebar-toggle" id="topSidebarToggle" title="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                        <path d="M9 3v18"></path>
                    </svg>
                </button>
                <h2 class="navbar-title">@yield('title', 'Admin Panel')</h2>
                {{-- Global Search (desktop) --}}
                <div class="global-search-wrapper" id="globalSearchWrapper">
                    <i class="bi bi-search global-search-icon"></i>
                    <input type="text" class="global-search-input" id="globalSearchInput"
                        placeholder="Search anything..." readonly onclick="openCommandPalette()" />
                    <span class="global-search-kbd">⌘K</span>
                </div>

            </div>

            <div class="navbar-right">
                {{-- Greeting + Clock --}}
                <div class="greeting-wrapper">
                    <span class="greeting-chip" id="greetingChip"></span>
                    <span class="live-clock" id="liveClock"></span>
                </div>

                {{-- Dark Mode Toggle --}}
                <button class="dark-mode-btn" id="darkModeBtn" title="Toggle dark mode">
                    <i class="bi bi-moon-fill" id="darkModeIcon"></i>
                </button>

                {{-- IP Security --}}
                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['settings.manage']))
                    <a href="{{ route('settings.security.index') }}"
                        class="dark-mode-btn {{ request()->routeIs('settings.security.*') ? 'active' : '' }}"
                        title="IP Security"
                        style="text-decoration:none;{{ request()->routeIs('settings.security.*') ? 'border-color:var(--primary);color:var(--primary);background:rgba(99,102,241,0.06);' : '' }}">
                        <i class="bi bi-shield-lock"></i>
                    </a>
                @endif

                <!-- Notifications Dropdown -->
                <div class="notification-dropdown">
                    <button class="notification-btn" id="notificationBtn">
                        <i class="bi bi-bell"></i>
                        @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->unreadNotifications->count() > 0)
                            <span
                                class="notification-badge">{{ auth()->guard('admin')->user()->unreadNotifications->count() }}</span>
                        @endif
                    </button>
                    <div class="notification-menu" id="notificationMenu">

                        {{-- Header --}}
                        <div class="notification-header">
                            <h6><i class="bi bi-bell-fill"></i> Notifications</h6>
                            @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->unreadNotifications->count() > 0)
                                <a href="#" class="mark-all-read" id="markAllReadDropdown">
                                    <i class="bi bi-check2-all"></i> Mark all read
                                </a>
                            @endif
                        </div>

                        {{-- List --}}
                        <div class="notification-list">
                            @if(auth()->guard('admin')->user())

                                {{-- UNREAD --}}
                                @if(auth()->guard('admin')->user()->unreadNotifications->count() > 0)
                                    @foreach(auth()->guard('admin')->user()->unreadNotifications as $notification)
                                        @php
                                            $t = strtolower($notification->data['title'] ?? '');
                                            $nType = $notification->type;
                                            if (str_contains($t, 'cancel')) {
                                                $ic = 'bi-x-circle-fill';
                                                $cl = 'red';
                                                $tag = 'Cancelled';
                                            } elseif ($nType === 'App\Notifications\DiamondSoldNotification' || (str_contains($t, 'diamond') && str_contains($t, 'sold'))) {
                                                $ic = 'bi-gem';
                                                $cl = 'green';
                                                $tag = 'Sold Out';
                                            } elseif (in_array($nType, ['App\Notifications\DiamondAssignedNotification', 'App\Notifications\DiamondReassignedNotification']) || str_contains($t, 'diamond') || str_contains($t, 'melee')) {
                                                $ic = 'bi-gem';
                                                $cl = 'purple';
                                                $tag = 'Diamond';
                                            } elseif ($nType === 'App\Notifications\ChatMentionNotification') {
                                                $ic = 'bi-at';
                                                $cl = 'blue';
                                                $tag = 'Mention';
                                            } elseif ($nType === 'App\Notifications\ExportCompleted') {
                                                $ic = 'bi-download';
                                                $cl = 'blue';
                                                $tag = 'Export';
                                            } elseif ($nType === 'App\Notifications\ImportCompleted') {
                                                $ic = 'bi-upload';
                                                $cl = 'blue';
                                                $tag = 'Import';
                                            } elseif (str_contains($t, 'reminder')) {
                                                $ic = 'bi-alarm-fill';
                                                $cl = 'amber';
                                                $tag = 'Reminder';
                                            } elseif (str_contains($t, 'updated') || str_contains($t, 'update')) {
                                                $ic = 'bi-arrow-repeat';
                                                $cl = 'blue';
                                                $tag = 'Updated';
                                            } elseif (str_contains($t, 'created') || str_contains($t, 'new order')) {
                                                $ic = 'bi-plus-circle-fill';
                                                $cl = 'green';
                                                $tag = 'New Order';
                                            } else {
                                                $ic = 'bi-bell-fill';
                                                $cl = 'gray';
                                                $tag = 'Alert';
                                            }
                                        @endphp
                                        <div class="notification-item unread" data-notification-id="{{ $notification->id }}"
                                            data-url="{{ $notification->data['url'] ?? '#' }}">

                                            <div class="notification-icon notif-icon-{{ $cl }}">
                                                <i class="bi {{ $ic }}"></i>
                                            </div>

                                            <div class="notification-content">
                                                <div class="notification-title-row">
                                                    <span class="notif-type-tag tag-{{ $cl }}">{{ $tag }}</span>
                                                    <strong
                                                        style="font-size:0.8rem;color:var(--dark);">{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                                </div>
                                                <p class="notification-message">{!! $notification->data['message'] ?? '' !!}</p>
                                                @if(isset($notification->data['message_preview']) && $nType === 'App\Notifications\ChatMentionNotification')
                                                    <p class="notification-preview">{!! $notification->data['message_preview'] !!}</p>
                                                @endif
                                                @if($nType === 'App\Notifications\ExportCompleted' && isset($notification->data['action_url']))
                                                    <a href="{{ $notification->data['action_url'] }}" class="notif-download-link"
                                                        onclick="event.stopPropagation();">
                                                        <i class="bi bi-download"></i> Download File
                                                    </a>
                                                @endif
                                                <div class="notification-time">
                                                    <i class="bi bi-clock"></i> {{ $notification->created_at->diffForHumans() }}
                                                </div>
                                            </div>

                                            <button class="notification-close"
                                                onclick="closeNotification('{{ $notification->id }}')">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    @endforeach

                                @else
                                    <div class="notification-empty">
                                        <div class="empty-icon-wrap"><i class="bi bi-bell-slash"></i></div>
                                        <p>All caught up! No new notifications.</p>
                                    </div>
                                @endif

                                {{-- READ (Earlier) --}}
                                @if(auth()->guard('admin')->user()->readNotifications->count() > 0)
                                    <div class="notification-divider">Earlier</div>
                                    @foreach(auth()->guard('admin')->user()->readNotifications->take(5) as $notification)
                                        @php
                                            $t = strtolower($notification->data['title'] ?? '');
                                            $nType = $notification->type;
                                            if (str_contains($t, 'cancel')) {
                                                $ic = 'bi-x-circle-fill';
                                                $cl = 'red';
                                                $tag = 'Cancelled';
                                            } elseif ($nType === 'App\Notifications\DiamondSoldNotification' || (str_contains($t, 'diamond') && str_contains($t, 'sold'))) {
                                                $ic = 'bi-gem';
                                                $cl = 'green';
                                                $tag = 'Sold Out';
                                            } elseif (in_array($nType, ['App\Notifications\DiamondAssignedNotification', 'App\Notifications\DiamondReassignedNotification']) || str_contains($t, 'diamond') || str_contains($t, 'melee')) {
                                                $ic = 'bi-gem';
                                                $cl = 'purple';
                                                $tag = 'Diamond';
                                            } elseif ($nType === 'App\Notifications\ChatMentionNotification') {
                                                $ic = 'bi-at';
                                                $cl = 'blue';
                                                $tag = 'Mention';
                                            } elseif ($nType === 'App\Notifications\ExportCompleted') {
                                                $ic = 'bi-download';
                                                $cl = 'blue';
                                                $tag = 'Export';
                                            } elseif ($nType === 'App\Notifications\ImportCompleted') {
                                                $ic = 'bi-upload';
                                                $cl = 'blue';
                                                $tag = 'Import';
                                            } elseif (str_contains($t, 'reminder')) {
                                                $ic = 'bi-alarm-fill';
                                                $cl = 'amber';
                                                $tag = 'Reminder';
                                            } elseif (str_contains($t, 'updated') || str_contains($t, 'update')) {
                                                $ic = 'bi-arrow-repeat';
                                                $cl = 'blue';
                                                $tag = 'Updated';
                                            } elseif (str_contains($t, 'created') || str_contains($t, 'new order')) {
                                                $ic = 'bi-plus-circle-fill';
                                                $cl = 'green';
                                                $tag = 'New Order';
                                            } else {
                                                $ic = 'bi-bell-fill';
                                                $cl = 'gray';
                                                $tag = 'Alert';
                                            }
                                        @endphp
                                        <div class="notification-item read" data-url="{{ $notification->data['url'] ?? '#' }}">
                                            <div class="notification-icon notif-icon-{{ $cl }}">
                                                <i class="bi {{ $ic }}"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div class="notification-title-row">
                                                    <span class="notif-type-tag tag-{{ $cl }}">{{ $tag }}</span>
                                                    <strong
                                                        style="font-size:0.8rem;color:var(--dark);">{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                                </div>
                                                <p class="notification-message">{!! $notification->data['message'] ?? '' !!}</p>
                                                @if(isset($notification->data['message_preview']) && $nType === 'App\Notifications\ChatMentionNotification')
                                                    <p class="notification-preview">{!! $notification->data['message_preview'] !!}</p>
                                                @endif
                                                @if($nType === 'App\Notifications\ExportCompleted' && isset($notification->data['action_url']))
                                                    <a href="{{ $notification->data['action_url'] }}" class="notif-download-link"
                                                        onclick="event.stopPropagation();">
                                                        <i class="bi bi-download"></i> Download File
                                                    </a>
                                                @endif
                                                <div class="notification-time">
                                                    <i class="bi bi-clock"></i> {{ $notification->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                            @else
                                <div class="notification-empty">
                                    <div class="empty-icon-wrap"><i class="bi bi-lock"></i></div>
                                    <p>Please login to see notifications</p>
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div style="border-top:1px solid var(--border);padding:0.65rem 1rem;flex-shrink:0;">
                            <a href="{{ route('notifications.index') }}"
                                style="display:flex;align-items:center;justify-content:center;gap:0.4rem;color:var(--primary);text-decoration:none;font-size:0.8rem;font-weight:600;padding:0.45rem;border-radius:10px;background:rgba(99,102,241,0.05);transition:background 0.2s;"
                                onmouseover="this.style.background='rgba(99,102,241,0.1)'"
                                onmouseout="this.style.background='rgba(99,102,241,0.05)'">
                                <i class="bi bi-arrow-right-circle"></i> View All Notifications
                            </a>
                        </div>

                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div class="profile-dropdown">
                    <button class="profile-btn" id="profileBtn">
                        <div class="profile-avatar">
                            {{ strtoupper(substr(auth()->guard('admin')->user()->name ?? 'A', 0, 2)) }}
                        </div>
                    </button>
                    <div class="profile-menu" id="profileMenu">
                        <div class="profile-header">
                            <p class="profile-name">{{ auth()->guard('admin')->user()->name ?? 'Admin' }}</p>
                            <p class="profile-email">{{ auth()->guard('admin')->user()->email ?? '' }}</p>
                        </div>
                        <div class="profile-divider"></div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="profile-logout">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main id="mainContent">
        @include('partials.flash')
        @yield('content')
    </main>

    <!-- Toast Container -->
    <div id="toast-container" class="position-fixed top-0 end-0 p-3"></div>

    <!-- Mobile Search Hint (one-time) -->
    <div class="mob-search-hint" id="mobSearchHint">
        <i class="bi bi-search"></i> Tap <strong style="margin:0 3px;">🔍</strong> in the header to search all modules
    </div>

    <!-- ── COMMAND PALETTE ── -->
    <div class="cmd-overlay" id="cmdOverlay" onclick="closeCmdIfOutside(event)">
        <div class="cmd-palette" id="cmdPalette">
            <div class="cmd-search-row">
                <i class="bi bi-search"></i>
                <input type="text" class="cmd-input" id="cmdInput" placeholder="Search pages, actions..."
                    autocomplete="off" />
                <span class="cmd-close-hint">ESC to close</span>
            </div>
            <div class="cmd-results" id="cmdResults"></div>
            <div class="cmd-footer">
                <span><span class="cmd-key">↑↓</span> navigate</span>
                <span><span class="cmd-key">↵</span> open</span>
                <span><span class="cmd-key">ESC</span> close</span>
            </div>
        </div>
    </div>

    <!-- ── SPEED DIAL ── -->
    <!-- <div class="speed-dial" id="speedDial">
        <button class="speed-dial-main" id="speedDialBtn" title="Quick actions">
            <i class="bi bi-plus-lg"></i>
        </button>
        <div class="speed-dial-items" id="speedDialItems">
            @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['orders.create']))
                <div class="sd-item">
                    <span class="sd-label">New Order</span>
                    <a href="{{ route('orders.create') }}" class="sd-btn order" title="New Order">
                        <i class="bi bi-basket-fill"></i>
                    </a>
                </div>
            @endif
            @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.create']))
                <div class="sd-item">
                    <span class="sd-label">Add Diamond</span>
                    <a href="{{ route('diamond.create') }}" class="sd-btn diamond" title="Add Diamond">
                        <i class="bi bi-gem"></i>
                    </a>
                </div>
            @endif
            @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['clients.view']))
                <div class="sd-item">
                    <span class="sd-label">View Clients</span>
                    <a href="{{ route('clients.index') }}" class="sd-btn client" title="Clients">
                        <i class="bi bi-people-fill"></i>
                    </a>
                </div>
            @endif
        </div>
    </div> -->

    <!-- ── MOBILE BOTTOM NAV ── -->
    <nav class="mobile-bottom-nav">
        <button class="mob-nav-item" id="mobileToggle" style="border:none;background:none;cursor:pointer;">
            <i class="bi bi-list"></i>
            <span>Menu</span>
        </button>
        <a href="{{ route('admin.dashboard') }}"
            class="mob-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-fill"></i>
            <span>Home</span>
        </a>
        @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['orders.view']))
            <a href="{{ route('orders.index') }}" class="mob-nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <i class="bi bi-basket2-fill"></i>
                <span>Orders</span>
            </a>
        @endif
        @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.view']))
            <a href="{{ route('diamond.index') }}"
                class="mob-nav-item {{ request()->routeIs('diamond.*') ? 'active' : '' }}">
                <i class="bi bi-gem"></i>
                <span>Diamonds</span>
            </a>
        @endif
        @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['clients.view']))
            <a href="{{ route('clients.index') }}"
                class="mob-nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Clients</span>
            </a>
        @endif
        <button class="mob-nav-item" onclick="openCommandPalette()" style="border:none;background:none;cursor:pointer;">
            <i class="bi bi-search"></i>
            <span>Search</span>
        </button>
    </nav>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>

    <!-- Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        // Initialize Select2 for multi and single selects
        jQuery(function ($) {
            try {
                // Multi-selects
                $('.select2-multiple').each(function () {
                    const $el = $(this);
                    const placeholder = $el.data('placeholder') || 'Select options';
                    $el.select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        closeOnSelect: false,
                        width: '100%'
                    });
                });

                // Single-selects
                $('.select2-single').each(function () {
                    const $el = $(this);
                    const placeholder = $el.data('placeholder') || 'Select an option';
                    $el.select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        closeOnSelect: true,
                        width: '100%'
                    });
                });
            } catch (e) {
                console.warn('Select2 init failed:', e);
            }
        });

        // Auto-hide unified flash alerts after ~4.5s
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert-card');
            alerts.forEach(function (el) {
                setTimeout(function () {
                    el.classList.add('alert-hide');
                    setTimeout(function () {
                        try {
                            el.remove();
                        } catch (e) { }
                    }, 600);
                }, 4500);
            });

            // Show draft reminder toast on login (from session flash)
            @if(session('draft_reminder'))
                setTimeout(function () {
                    if (typeof showToast === 'function') {
                        showToast('📋 {{ session("draft_reminder.message") }}', 8000);
                    }
                }, 1000);
            @endif
        });

        // ── LIVE CLOCK (ticks every second, no page refresh needed) ──
        (function () {
            const clockEl = document.getElementById('liveClock');
            const greetingEl = document.getElementById('greetingChip');

            const DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            function tick() {
                const now = new Date();
                const h24 = now.getHours();
                const min = String(now.getMinutes()).padStart(2, '0');
                const sec = String(now.getSeconds()).padStart(2, '0');
                const ampm = h24 >= 12 ? 'PM' : 'AM';
                const h12 = h24 % 12 || 12;
                const dayName = DAYS[now.getDay()];
                const monthStr = MONTHS[now.getMonth()];
                const date = now.getDate();

                // e.g. "Thu, Feb 26 · 1:48:34 PM"
                if (clockEl) { clockEl.textContent = dayName + ', ' + monthStr + ' ' + date + ' \u00b7 ' + h12 + ':' + min + ':' + sec + ' ' + ampm; }

                if (greetingEl) {
                    const greeting = h24 < 12 ? '\u2600\ufe0f Good Morning' :
                        h24 < 17 ? '\ud83c\udf24\ufe0f Good Afternoon' :
                            '\ud83c\udf19 Good Evening';
                    const name = '{{ auth()->guard("admin")->user()->name ?? "" }}'.split(' ')[0];
                    greetingEl.textContent = greeting + (name ? ', ' + name : '') + '!';
                }
            }

            tick(); // run immediately on load
            setInterval(tick, 1000); // tick every second
        })();

        // ── MOBILE SEARCH HINT (show once per session on mobile) ──
        (function () {
            if (window.innerWidth <= 768 && !sessionStorage.getItem('mob_search_hint_shown')) {
                sessionStorage.setItem('mob_search_hint_shown', '1');
                var hint = document.getElementById('mobSearchHint');
                if (hint) {
                    setTimeout(function () { hint.classList.add('show'); }, 1200);
                    setTimeout(function () { hint.classList.remove('show'); }, 5200);
                }
            }
        })();

        // Toast Helper
        window.showToast = function (message, delay = 3000) {
            try {
                const container = document.getElementById('toast-container');
                if (!container) return;

                const toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center custom-toast border-0';
                toastEl.setAttribute('role', 'alert');
                toastEl.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            <span class="toast-icon">🔔</span>
                            <span>${message}</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                container.appendChild(toastEl);
                const bsToast = new bootstrap.Toast(toastEl, {
                    delay
                });
                bsToast.show();
                toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
            } catch (e) {
                console.error('[Toast Error]', e);
            }
        }

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const toggleIcon = toggleBtn?.querySelector('i');
        const topToggleBtn = document.getElementById('topSidebarToggle');
        const topToggleIcon = topToggleBtn?.querySelector('i');
        const mobileToggle = document.getElementById('mobileToggle');

        function toggleSidebar() {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            if (toggleIcon) {
                toggleIcon.className = isCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
            }
            if (topToggleIcon) {
                topToggleIcon.className = isCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
            }
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        function toggleMobileSidebar() {
            sidebar.classList.toggle('show');
            const overlay = document.getElementById('sidebarOverlay');
            if (overlay) {
                overlay.classList.toggle('show', sidebar.classList.contains('show'));
            }
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        topToggleBtn?.addEventListener('click', toggleSidebar);
        mobileToggle?.addEventListener('click', toggleMobileSidebar);

        const mobileCloseBtn = document.getElementById('mobileCloseSidebar');
        mobileCloseBtn?.addEventListener('click', toggleMobileSidebar);

        const sidebarOverlay = document.getElementById('sidebarOverlay');
        sidebarOverlay?.addEventListener('click', toggleMobileSidebar);

        // Load saved state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            if (toggleIcon) toggleIcon.className = 'bi bi-chevron-right';
            if (topToggleIcon) topToggleIcon.className = 'bi bi-chevron-right';
        }

        // Dropdown Toggle Functionality
        const attributesDropdown = document.getElementById('attributesDropdown');
        const attributesMenu = document.getElementById('attributesMenu');

        if (attributesDropdown && attributesMenu) {
            // Determine initial state: prefer saved value, else server-provided initial
            const savedState = localStorage.getItem('attributesDropdownOpen');
            const defaultOpen = (attributesDropdown.getAttribute('data-initial-open') === '1');
            const initialOpen = savedState === null ? defaultOpen : (savedState === 'true');

            if (initialOpen) {
                attributesDropdown.classList.add('active');
                attributesMenu.classList.add('show');
            } else {
                attributesDropdown.classList.remove('active');
                attributesMenu.classList.remove('show');
            }

            // Toggle dropdown on click (always respects manual toggle)
            attributesDropdown.addEventListener('click', function (e) {
                try {
                    e.preventDefault();
                    e.stopPropagation();
                } catch (err) { }
                const isOpen = attributesMenu.classList.toggle('show');
                this.classList.toggle('active');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                localStorage.setItem('attributesDropdownOpen', isOpen);
            });
        }

        // Expenses Dropdown Handler
        const expensesDropdown = document.getElementById('expensesDropdown');
        const expensesMenu = document.getElementById('expensesMenu');

        if (expensesDropdown && expensesMenu) {
            const savedState = localStorage.getItem('expensesDropdownOpen');
            const defaultOpen = (expensesDropdown.getAttribute('data-initial-open') === '1');
            const initialOpen = savedState === null ? defaultOpen : (savedState === 'true');

            if (initialOpen) {
                expensesDropdown.classList.add('active');
                expensesMenu.classList.add('show');
            } else {
                expensesDropdown.classList.remove('active');
                expensesMenu.classList.remove('show');
            }

            expensesDropdown.addEventListener('click', function (e) {
                try {
                    e.preventDefault();
                    e.stopPropagation();
                } catch (err) { }
                const isOpen = expensesMenu.classList.toggle('show');
                this.classList.toggle('active');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                localStorage.setItem('expensesDropdownOpen', isOpen);
            });
        }

        // Email Dropdown Handler
        const emailDropdown = document.getElementById('emailDropdown');
        const emailMenu = document.getElementById('emailMenu');

        if (emailDropdown && emailMenu) {
            const savedState = localStorage.getItem('emailDropdownOpen');
            const defaultOpen = (emailDropdown.getAttribute('data-initial-open') === '1');
            const initialOpen = savedState === null ? defaultOpen : (savedState === 'true');

            if (initialOpen) {
                emailDropdown.classList.add('active');
                emailMenu.classList.add('show');
            } else {
                emailDropdown.classList.remove('active');
                emailMenu.classList.remove('show');
            }

            emailDropdown.addEventListener('click', function (e) {
                try {
                    e.preventDefault();
                    e.stopPropagation();
                } catch (err) { }
                const isOpen = emailMenu.classList.toggle('show');
                this.classList.toggle('active');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                localStorage.setItem('emailDropdownOpen', isOpen);
            });
        }


        // Tools Dropdown Handler
        const toolsDropdown = document.getElementById('toolsDropdown');
        const toolsMenu = document.getElementById('toolsMenu');

        if (toolsDropdown && toolsMenu) {
            const savedState = localStorage.getItem('toolsDropdownOpen');
            const defaultOpen = (toolsDropdown.getAttribute('data-initial-open') === '1');
            const initialOpen = savedState === null ? defaultOpen : (savedState === 'true');

            if (initialOpen) {
                toolsDropdown.classList.add('active');
                toolsMenu.classList.add('show');
            } else {
                toolsDropdown.classList.remove('active');
                toolsMenu.classList.remove('show');
            }

            toolsDropdown.addEventListener('click', function (e) {
                try {
                    e.preventDefault();
                    e.stopPropagation();
                } catch (err) { }
                const isOpen = toolsMenu.classList.toggle('show');
                this.classList.toggle('active');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                localStorage.setItem('toolsDropdownOpen', isOpen);
            });
        }


        // Close mobile sidebar on link click
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    sidebar.classList.remove('show');
                });
            });
        }

        // Notification Dropdown Handler
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationMenu = document.getElementById('notificationMenu');
        const markAllReadDropdown = document.getElementById('markAllReadDropdown');

        if (notificationBtn && notificationMenu) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationMenu.classList.toggle('show');
                profileMenu.classList.remove('show');
            });

            if (markAllReadDropdown) {
                markAllReadDropdown.addEventListener('click', (e) => {
                    e.preventDefault();
                    markAllReadNotifications();
                });
            }
        }

        // Profile Dropdown Handler
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileMenu.classList.toggle('show');
                notificationMenu.classList.remove('show');
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (notificationMenu && !notificationMenu.parentElement.contains(e.target)) {
                notificationMenu.classList.remove('show');
            }
            if (profileMenu && !profileMenu.parentElement.contains(e.target)) {
                profileMenu.classList.remove('show');
            }
        });

        // Close notification function
        function closeNotification(notificationId) {
            event.stopPropagation(); // Prevent notification click
            const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => {
                    notificationItem.remove();
                    // Mark as read via AJAX
                    fetch(`/admin/notifications/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    }).catch(err => console.error('Error marking notification as read:', err));
                }, 300);
            }
        }

        // Handle notification item clicks for navigation
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function (e) {
                    // Don't navigate if clicking the close button
                    if (e.target.closest('.notification-close')) {
                        return;
                    }

                    const url = this.getAttribute('data-url');
                    const notificationId = this.getAttribute('data-notification-id');

                    if (url && url !== '#') {
                        // Mark as read if unread
                        if (notificationId && this.classList.contains('unread')) {
                            fetch(`/admin/notifications/${notificationId}/read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json'
                                }
                            }).catch(err => console.error('Error marking notification as read:', err));
                        }

                        // Navigate to the URL
                        window.location.href = url;
                    }
                });

                // Add cursor pointer style
                if (item.getAttribute('data-url') && item.getAttribute('data-url') !== '#') {
                    item.style.cursor = 'pointer';
                }
            });
        });

        // Mark all as read function
        function markAllReadNotifications() {
            fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(err => console.error('Error:', err));
        }

        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
        `;
        document.head.appendChild(style);

        // Global alert helper function
        window.showAlert = function (message, type = 'info', title = null) {
            const typeConfig = {
                'success': {
                    icon: 'success',
                    background: '#d4edda',
                    color: '#155724'
                },
                'error': {
                    icon: 'error',
                    background: '#f8d7da',
                    color: '#721c24'
                },
                'warning': {
                    icon: 'warning',
                    background: '#fff3cd',
                    color: '#856404'
                },
                'info': {
                    icon: 'info',
                    background: '#d1ecf1',
                    color: '#0c5460'
                }
            };

            const config = typeConfig[type] || typeConfig['info'];

            Swal.fire({
                icon: config.icon,
                title: title || (type.charAt(0).toUpperCase() + type.slice(1)),
                text: message,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                background: config.background,
                color: config.color,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        };

        // Global confirm helper function
        window.showConfirm = function (message, title = 'Are you sure?', confirmButtonText = 'Yes', cancelButtonText = 'Cancel') {
            return Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: 'rgba(255, 147, 147, 1)',
                confirmButtonText: confirmButtonText,
                cancelButtonText: cancelButtonText,
                background: '#fff',
                backdrop: 'rgba(0,0,0,0.4)'
            }).then((result) => {
                return result.isConfirmed;
            });
        };

        // Global error handler for fetch requests
        document.addEventListener('DOMContentLoaded', function () {
            // Intercept fetch calls to handle errors globally
            const originalFetch = window.fetch;
            window.fetch = function (...args) {
                return originalFetch.apply(this, args)
                    .then(response => {
                        if (!response.ok && response.status !== 422) {
                            response.json().then(data => {
                                const message = data.message || `Error: ${response.status}`;
                                showAlert(message, 'error', 'Error');
                            }).catch(() => {
                                showAlert(`HTTP Error: ${response.status}`, 'error', 'Error');
                            });
                        }
                        return response;
                    })
                    .catch(error => {
                        showAlert(error.message || 'An error occurred', 'error', 'Error');
                        throw error;
                    });
            };
        });
    </script>

    <!-- Draft Notification Popup on Login -->
    @if(auth()->guard('admin')->check())
        <script>
            (function () {
                // Check if we should show the draft notification
                // Only show once per session (use sessionStorage)
                const DRAFT_POPUP_KEY = 'draft_popup_shown_{{ auth()->guard('admin')->id() }}';

                // Check if popup was already shown this session
                if (sessionStorage.getItem(DRAFT_POPUP_KEY)) {
                    return;
                }

                // Fetch drafts for current admin
                fetch('{{ route("orders.drafts.my-drafts") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.count > 0) {
                            // Mark as shown for this session
                            sessionStorage.setItem(DRAFT_POPUP_KEY, 'true');

                            // Build the drafts list HTML
                            let draftsHtml = '<div style="text-align: left; max-height: 200px; overflow-y: auto;">';
                            data.drafts.forEach(draft => {
                                const hasError = draft.has_error
                                    ? '<span style="color: #ef4444; font-size: 0.75rem;"><i class="bi bi-exclamation-triangle"></i> Error</span>'
                                    : '';
                                draftsHtml += `
                                                                                                                                                    <div style="padding: 0.75rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                                                                                                                                        <div>
                                                                                                                                                            <strong style="color: #1e293b;">${draft.order_type || 'No Type'}</strong>
                                                                                                                                                            <div style="font-size: 0.8rem; color: #64748b;">
                                                                                                                                                                ${draft.client_name || 'No client'} • ${draft.time_ago} ${hasError}
                                                                                                                                                            </div>
                                                                                                                                                        </div>
                                                                                                                                                        <a href="${draft.resume_url}"
                                                                                                                                                            style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.75rem; text-decoration: none; font-weight: 600;">
                                                                                                                                                            Resume
                                                                                                                                                        </a>    
                                                                                                                                                    </div>
                                                                                                                                                `;
                            });
                            draftsHtml += '</div>';

                            // Show the popup
                            Swal.fire({
                                title: '<span style="color: #1e293b; font-weight: 700;"><i class="bi bi-file-earmark-text" style="color: #6366f1;"></i> Pending Drafts</span>',
                                html: `
                                                                                                                                                    <p style="color: #64748b; margin-bottom: 1rem;">
                                                                                                                                                        You have <strong style="color: #6366f1;">${data.count}</strong> pending order draft${data.count > 1 ? 's' : ''} that need attention.
                                                                                                                                                    </p>
                                                                                                                                                    ${draftsHtml}
                                                                                                                                                `,
                                showCancelButton: true,
                                confirmButtonText: '<i class="bi bi-collection"></i> View All Drafts',
                                cancelButtonText: 'Dismiss',
                                confirmButtonColor: '#6366f1',
                                cancelButtonColor: '#64748b',
                                width: 450,
                                customClass: {
                                    popup: 'draft-notification-popup'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '{{ route("orders.drafts.index") }}';
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.log('[DraftNotification] Error fetching drafts:', error);
                    });
            })();
        </script>
    @endif

    <script>
        /* ══════════════════════════════════════
           ENHANCEMENTS JS
        ══════════════════════════════════════ */

        // ── DARK MODE ──
        const darkBtn = document.getElementById('darkModeBtn');
        const darkIcon = document.getElementById('darkModeIcon');

        function applyTheme(dark) {
            document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
            document.body.style.background = dark ? 'var(--bg-body)' : '';
            if (darkIcon) darkIcon.className = dark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            localStorage.setItem('adminTheme', dark ? 'dark' : 'light');
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('adminTheme');
        applyTheme(savedTheme === 'dark');

        darkBtn?.addEventListener('click', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            applyTheme(!isDark);
        });

        // ── COMMAND PALETTE ──
        const CMD_ITEMS = [
            { label: 'Dashboard', group: 'Navigation', icon: 'bi-house', bg: 'rgba(99,102,241,0.1)', color: 'var(--primary)', url: '{{ route("admin.dashboard") }}', hint: 'Go to Home' },
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['orders.view']))
                { label: 'Orders', group: 'Navigation', icon: 'bi-basket', bg: 'rgba(16,185,129,0.1)', color: '#10b981', url: '{{ route("orders.index") }}', hint: 'View all orders' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.view']))
                { label: 'Stock List', group: 'Navigation', icon: 'bi-gem', bg: 'rgba(139,92,246,0.1)', color: '#8b5cf6', url: '{{ route("diamond.index") }}', hint: 'View diamonds' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['clients.view']))
                { label: 'Clients', group: 'Navigation', icon: 'bi-people', bg: 'rgba(245,158,11,0.1)', color: '#f59e0b', url: '{{ route("clients.index") }}', hint: 'Manage clients' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['invoices.view']))
                { label: 'Invoices', group: 'Finance', icon: 'bi-receipt', bg: 'rgba(16,185,129,0.1)', color: '#10b981', url: '{{ route("invoices.index") }}', hint: 'Manage invoices' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['purchases.view']))
                { label: 'Purchases', group: 'Finance', icon: 'bi-cart', bg: 'rgba(59,130,246,0.1)', color: '#3b82f6', url: '{{ route("purchases.index") }}', hint: 'Manage purchases' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['expenses.view']))
                { label: 'Expenses', group: 'Finance', icon: 'bi-wallet2', bg: 'rgba(239,68,68,0.1)', color: '#ef4444', url: '{{ route("expenses.index") }}', hint: 'Manage expenses' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['gold-tracking.view']))
                { label: 'Gold Tracking', group: 'Tracking', icon: 'bi-bar-chart', bg: 'rgba(245,158,11,0.1)', color: '#f59e0b', url: '{{ route("gold-tracking.index") }}', hint: 'Manage gold tracking' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['parties.view']))
                { label: 'Parties', group: 'Tracking', icon: 'bi-briefcase', bg: 'rgba(168,85,247,0.1)', color: '#a855f7', url: '{{ route("parties.index") }}', hint: 'Manage parties' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['factories.view']))
                { label: 'Factories', group: 'Tracking', icon: 'bi-building', bg: 'rgba(99,102,241,0.1)', color: '#6366f1', url: '{{ route("factories.index") }}', hint: 'Manage factories' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['leads.view']))
                { label: 'Leads', group: 'Navigation', icon: 'bi-magnet', bg: 'rgba(14,165,233,0.1)', color: '#0ea5e9', url: '{{ route("leads.index") }}', hint: 'Manage leads' },
            @endif
            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['settings.manage']))
                { label: 'Settings', group: 'System', icon: 'bi-gear', bg: 'rgba(100,116,139,0.1)', color: '#64748b', url: '{{ route("settings.security.index") }}', hint: 'System configuration' }
            @endif
        ];

        let cmdActive = -1;

        function openCommandPalette() {
            document.getElementById('cmdOverlay').classList.add('open');
            setTimeout(() => document.getElementById('cmdInput')?.focus(), 50);
            cmdActive = -1;
            renderCmdResults('');
        }

        function closeCommandPalette() {
            document.getElementById('cmdOverlay').classList.remove('open');
            if (document.getElementById('cmdInput')) document.getElementById('cmdInput').value = '';
        }

        function closeCmdIfOutside(e) {
            if (e.target === document.getElementById('cmdOverlay')) closeCommandPalette();
        }

        function renderCmdResults(q) {
            const filtered = q
                ? CMD_ITEMS.filter(i => i.label.toLowerCase().includes(q) || i.hint.toLowerCase().includes(q) || i.group.toLowerCase().includes(q))
                : CMD_ITEMS;

            const groups = [...new Set(filtered.map(i => i.group))];
            const container = document.getElementById('cmdResults');
            if (!container) return;

            container.innerHTML = groups.map(g => `
            <div class="cmd-group-label">${g}</div>
            ${filtered.filter(i => i.group === g).map((item, idx) => `
                <a href="${item.url}" class="cmd-item" data-idx="${filtered.indexOf(item)}">
                    <div class="cmd-item-icon" style="background:${item.bg};color:${item.color};">
                        <i class="bi ${item.icon}"></i>
                    </div>
                    <div class="cmd-item-text">
                        <div>${item.label}</div>
                        <div class="cmd-item-hint">${item.hint}</div>
                    </div>
                </a>
            `).join('')}
        `).join('') || '<div style="padding:2rem;text-align:center;color:#94a3b8;font-size:0.875rem;">No results found</div>';
        }

        document.getElementById('cmdInput')?.addEventListener('input', function () {
            cmdActive = -1;
            renderCmdResults(this.value.toLowerCase());
        });

        document.getElementById('cmdInput')?.addEventListener('keydown', function (e) {
            const items = document.querySelectorAll('.cmd-item');
            if (e.key === 'ArrowDown') { e.preventDefault(); cmdActive = Math.min(cmdActive + 1, items.length - 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); cmdActive = Math.max(cmdActive - 1, 0); }
            else if (e.key === 'Enter' && cmdActive >= 0) { e.preventDefault(); items[cmdActive]?.click(); return; }
            else if (e.key === 'Escape') { closeCommandPalette(); return; }
            items.forEach((el, i) => el.classList.toggle('active', i === cmdActive));
            if (cmdActive >= 0) items[cmdActive]?.scrollIntoView({ block: 'nearest' });
        });

        // Ctrl+K / Cmd+K shortcut
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const overlay = document.getElementById('cmdOverlay');
                overlay.classList.contains('open') ? closeCommandPalette() : openCommandPalette();
            }
            if (e.key === 'Escape') closeCommandPalette();
        });

        // ── SPEED DIAL ──
        const sdBtn = document.getElementById('speedDialBtn');
        const sdItems = document.getElementById('speedDialItems');

        sdBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = sdItems.classList.toggle('open');
            sdBtn.classList.toggle('open', open);
        });

        document.addEventListener('click', () => {
            sdItems?.classList.remove('open');
            sdBtn?.classList.remove('open');
        });

        // ── REAL-TIME NOTIFICATION BADGE via Pusher ──
        (function () {
            if (!window.chatPusherKey || !window.authAdminId) return;
            try {
                // Only if Pusher/Echo is available
                if (typeof Echo !== 'undefined') {
                    Echo.private(`App.Models.Admin.${window.authAdminId}`)
                        .notification((notif) => {
                            // Update badge count
                            const badge = document.querySelector('.notification-badge');
                            if (badge) {
                                badge.textContent = parseInt(badge.textContent || 0) + 1;
                                badge.style.display = '';
                            } else {
                                const btn = document.getElementById('notificationBtn');
                                if (btn) {
                                    const b = document.createElement('span');
                                    b.className = 'notification-badge';
                                    b.textContent = '1';
                                    btn.appendChild(b);
                                }
                            }
                            // Play subtle sound
                            try {
                                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.connect(gain); gain.connect(ctx.destination);
                                osc.frequency.value = 880;
                                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                                osc.start(); osc.stop(ctx.currentTime + 0.4);
                            } catch (e) { }
                        });
                }
            } catch (e) { }
        })();

    </script>
    @stack('scripts')
</body>

</html>
