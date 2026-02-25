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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
            --shadow-md: rgba(0, 0, 0, 0.1);
            --shadow-lg: rgba(0, 0, 0, 0.15);
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
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
            right: 0;
            left: var(--sidebar-width);
            height: 70px;
            background: white;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            z-index: 500;
            transition: left 0.3s;
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
            gap: 1.5rem;
        }

        /* ── NOTIFICATION DROPDOWN ── */
        .notification-dropdown {
            position: relative;
        }

        .notification-btn {
            position: relative;
            width: 44px;
            height: 44px;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 10px;
            border: 2px solid var(--border);
            background: white;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1.1rem;
        }

        .profile-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
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
                font-size: 1.25rem;
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

            .notification-btn {
                width: 34px;
                height: 34px;
            }

            .notification-badge {
                font-size: 7px;
                padding: 1px 5px;
            }

            .profile-btn {
                padding: 5px 7px;
                font-size: 13px;
            }

            .profile-avatar {
                width: 23px;
                height: 23px;
                font-size: 11px;
                border-radius: 50%;
            }

            .navbar-right {
                gap: 10px;
            }

            .notification-menu {
                width: calc(100vw - 28px);
                left: 0;
                transform: translateX(-63%);
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

            #mainContent .welcome-actions {
                display: unset;
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
    </style>

    @stack('head')
</head>

<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" id="mobileToggle">
        <i class="bi bi-list" style="font-size: 1.5rem;"></i>
    </button>

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">

        <!-- Sidebar Header -->
        <!-- <div class="sidebar-header">
            <div class="logo-section">
                <a href="{{ route('admin.dashboard') }}">
                    <img src="{{ asset('images/Luxurious-Logo.png') }}" alt="Logo" class="logo-icon">
                </a>
            </div>
        </div> -->

        <!-- Navigation -->
        <div class="nav-section">
            <ul class="nav">
                <li>
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}" data-tooltip="Dashboard">
                        <i class="bi bi-house"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['admins.view', 'admins.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('admins.*') ? 'active' : '' }}"
                            href="{{ route('admins.index') }}" data-tooltip="Admins">
                            <i class="bi bi-people"></i>
                            <span>Admins</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['permissions.view', 'permissions.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"
                            href="{{ route('permissions.index') }}" data-tooltip="Permissions">
                            <i class="bi bi-shield-lock"></i>
                            <span>Permissions</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['chat.access']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"
                            href="{{ route('chat.index') }}" data-tooltip="Chat" id="chatSidebarLink">
                            <i class="bi bi-chat-dots"></i>
                            <span>Chat</span>
                            <span id="chatUnreadBadge" class="hidden"></span>
                        </a>
                    </li>
                @endif



                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['orders.view', 'orders.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('orders.*') && !request()->routeIs('orders.drafts.*') ? 'active' : '' }}"
                            href="{{ route('orders.index') }}" data-tooltip="Orders">
                            <i class="bi bi-basket"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['sales.view_all']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('companies.all-sales-dashboard') ? 'active' : '' }}"
                            href="{{ route('companies.all-sales-dashboard') }}" data-tooltip="All Sales">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>All Sales</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamonds.view', 'diamonds.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('diamond.index') || (request()->routeIs('diamond.*') && !request()->routeIs('diamond.job.*')) ? 'active' : '' }}"
                            href="{{ route('diamond.index') }}" data-tooltip="Diamonds">
                            <i class="bi bi-gem"></i>
                            <span>Stock List</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['melee_diamonds.view', 'melee_diamonds.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('melee.*') ? 'active' : '' }}"
                            href="{{ route('melee.index') }}" data-tooltip="Melee Inventory">
                            <i class="bi bi-grid-3x3-gap"></i>
                            <span>Melee Inventory</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['packages.view', 'packages.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}"
                            href="{{ route('packages.index') }}" data-tooltip="Packages">
                            <i class="bi bi-box-seam"></i>
                            <span>Packages</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['diamond_jobs.view']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('diamond.job.*') ? 'active' : '' }}"
                            href="{{ route('diamond.job.history') }}" data-tooltip="Job History">
                            <i class="bi bi-clock-history"></i>
                            <span>Job History</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['invoices.view', 'invoices.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                            href="{{ route('invoices.index') }}" data-tooltip="Invoices">
                            <i class="bi bi-receipt"></i>
                            <span>Invoices</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['parties.view', 'parties.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('parties.*') ? 'active' : '' }}"
                            href="{{ route('parties.index') }}" data-tooltip="Parties">
                            <i class="bi bi-people"></i>
                            <span>Vendors</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['clients.view']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}"
                            href="{{ route('clients.index') }}" data-tooltip="Clients">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Shoppers</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['leads.view', 'leads.create']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}"
                            href="{{ route('leads.index') }}" data-tooltip="Leads Inbox">
                            <i class="bi bi-inbox"></i>
                            <span>Leads Inbox</span>
                        </a>
                    </li>
                @endif

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['meta_leads.settings']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('settings.meta.*') ? 'active' : '' }}"
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
                                        <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}"
                                            href="{{ route('purchases.index') }}" data-tooltip="Purchase Tracker">
                                            <i class="bi bi-cart-check"></i>
                                            <span>Purchase Tracker</span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->guard('admin')->user()->canAccessAny(['expenses.view', 'expenses.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}"
                                            href="{{ route('expenses.index') }}" data-tooltip="Office Expenses">
                                            <i class="bi bi-wallet2"></i>
                                            <span>Office Expenses</span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->guard('admin')->user()->canAccessAny(['gold-tracking.view', 'gold-tracking.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('gold-tracking.*') ? 'active' : '' }}"
                                            href="{{ route('gold-tracking.index') }}" data-tooltip="Gold Tracking">
                                            <i class="bi bi-coin"></i>
                                            <span>Gold Tracking</span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->guard('admin')->user()->canAccessAny(['factories.view', 'factories.create']))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('factories.*') ? 'active' : '' }}"
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

                @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['settings.manage']))
                    <li>
                        <a class="nav-link {{ request()->routeIs('settings.security.*') ? 'active' : '' }}"
                            href="{{ route('settings.security.index') }}" data-tooltip="IP Security">
                            <i class="bi bi-shield-lock"></i>
                            <span>IP Security</span>
                        </a>
                    </li>
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
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-panel-left text-muted-foreground" aria-hidden="true"
                        data-replit-metadata="client/src/components/layouts/AdminLayout.tsx:78:14"
                        data-component-name="PanelLeft">
                        <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                        <path d="M9 3v18"></path>
                    </svg>
                </button>
                <h2 class="navbar-title">@yield('title', 'Admin Panel')</h2>
            </div>

            <div class="navbar-right">
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
                        <i class="bi bi-chevron-down"></i>
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
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        topToggleBtn?.addEventListener('click', toggleSidebar);
        mobileToggle?.addEventListener('click', toggleMobileSidebar);

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

    @stack('scripts')
</body>

</html>