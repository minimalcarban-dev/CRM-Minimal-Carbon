<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            justify-content: space-between;
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
            gap: 0.75rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            flex-shrink: 0;
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
            padding: 1rem 0.5rem;
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
            gap: 0.875rem;
            flex: 1;
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
        }

        .toast {
            border-radius: 12px;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
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
        <div class="sidebar-header">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="bi bi-gem" id="sidebarToggle"></i>
                </div>
                <span class="logo-text">Admin Panel</span>
            </div>
            {{-- <button class="toggle-btn" id="sidebarToggle">
                <i class="bi bi-chevron-left"></i>
            </button> --}}
        </div>

        <!-- User Info -->
        <div class="user-info">
            @if (isset($currentAdmin) && $currentAdmin)
                <div class="user-card">
                    <div class="user-avatar">
                        {{ strtoupper(substr($currentAdmin->name, 0, 2)) }}
                    </div>
                    <div class="user-details">
                        <p class="user-name">{{ $currentAdmin->name }}</p>
                        <p class="user-email">{{ $currentAdmin->email }}</p>
                    </div>
                </div>
            @else
                <a href="{{ route('admin.login') }}" class="btn btn-primary w-100">Login</a>
            @endif
        </div>

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
                <li>
                    <a class="nav-link {{ request()->routeIs('admins.*') ? 'active' : '' }}"
                        href="{{ route('admins.index') }}" data-tooltip="Admins">
                        <i class="bi bi-people"></i>
                        <span>Admins</span>
                    </a>
                </li>

                @php
                    $canSeePermissions =
                        isset($currentAdmin) &&
                        $currentAdmin &&
                        ($currentAdmin->is_super ||
                            $currentAdmin->hasPermission('permissions.view') ||
                            $currentAdmin->hasPermission('permissions.create'));
                @endphp

                @if ($canSeePermissions)
                    <li>
                        <a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"
                            href="{{ route('permissions.index') }}" data-tooltip="Permissions">
                            <i class="bi bi-shield-lock"></i>
                            <span>Permissions</span>
                        </a>
                    </li>
                @endif

                @php
                    $canSeeOrders =
                        isset($currentAdmin) &&
                        $currentAdmin &&
                        ($currentAdmin->is_super ||
                            $currentAdmin->hasPermission('orders.view') ||
                            $currentAdmin->hasPermission('orders.create'));
                @endphp

                @if ($canSeeOrders)
                    <li>
                        <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"
                            href="{{ route('orders.index') }}" data-tooltip="Orders">
                            <i class="bi bi-basket"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"
                        href="{{ route('chat.index') }}" data-tooltip="Chat">
                        <i class="bi bi-chat-dots"></i>
                        <span>Chat</span>
                    </a>
                </li>
            </ul>

            @php
                $canSeeAttributes =
                    isset($currentAdmin) &&
                    $currentAdmin &&
                    ($currentAdmin->is_super ||
                        $currentAdmin->hasPermission('metal_types.view') ||
                        $currentAdmin->hasPermission('stone_colors.view'));
            @endphp

            @if ($canSeeAttributes)
                <div class="nav-dropdown">
                    <button class="dropdown-toggle-link" id="attributesDropdown" data-tooltip="Attributes">
                        <div class="left-content">
                            <i class="bi bi-grid main-icon"></i>
                            <span>Attributes</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </button>
                    <div class="dropdown-menu-custom" id="attributesMenu">
                        <ul class="nav">
                            <li>
                                <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}"
                                    href="{{ route('companies.index') }}" data-tooltip="Companies">
                                    <i class="bi bi-buildings"></i>
                                    <span>Company</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('metal_types.*') ? 'active' : '' }}"
                                    href="{{ route('metal_types.index') }}" data-tooltip="Metal Types">
                                    <i class="bi bi-award"></i>
                                    <span>Metal Types</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('setting_types.*') ? 'active' : '' }}"
                                    href="{{ route('setting_types.index') }}" data-tooltip="Setting Types">
                                    <i class="bi bi-gear"></i>
                                    <span>Setting Types</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('closure_types.*') ? 'active' : '' }}"
                                    href="{{ route('closure_types.index') }}" data-tooltip="Closure Types">
                                    <i class="bi bi-link-45deg"></i>
                                    <span>Closure Types</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('ring_sizes.*') ? 'active' : '' }}"
                                    href="{{ route('ring_sizes.index') }}" data-tooltip="Ring Sizes">
                                    <i class="bi bi-circle"></i>
                                    <span>Ring Sizes</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('stone_types.*') ? 'active' : '' }}"
                                    href="{{ route('stone_types.index') }}" data-tooltip="Stone Types">
                                    <i class="bi bi-gem"></i>
                                    <span>Stone Types</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('stone_shapes.*') ? 'active' : '' }}"
                                    href="{{ route('stone_shapes.index') }}" data-tooltip="Stone Shapes">
                                    <i class="bi bi-square"></i>
                                    <span>Stone Shapes</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('stone_colors.*') ? 'active' : '' }}"
                                    href="{{ route('stone_colors.index') }}" data-tooltip="Stone Colors">
                                    <i class="bi bi-droplet"></i>
                                    <span>Stone Colors</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <!-- Logout -->
        @if (isset($currentAdmin) && $currentAdmin)
            <div class="logout-section">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        @endif
    </nav>

    <!-- Main Content -->
    <main id="mainContent">
        @if (session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Error:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Toast Container -->
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toast Helper
        function showToast(message, delay = 3000) {
            try {
                const container = document.getElementById('toast-container');
                const toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-bg-primary border-0';
                toastEl.setAttribute('role', 'alert');
                toastEl.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
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
                alert(message);
            }
        }

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const toggleIcon = toggleBtn?.querySelector('i');
        const mobileToggle = document.getElementById('mobileToggle');

        function toggleSidebar() {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            if (toggleIcon) {
                toggleIcon.className = isCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
            }
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        function toggleMobileSidebar() {
            sidebar.classList.toggle('show');
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        mobileToggle?.addEventListener('click', toggleMobileSidebar);

        // Load saved state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            if (toggleIcon) toggleIcon.className = 'bi bi-chevron-right';
        }

        // Dropdown Toggle Functionality
        const attributesDropdown = document.getElementById('attributesDropdown');
        const attributesMenu = document.getElementById('attributesMenu');

        if (attributesDropdown && attributesMenu) {
            // Check if any attribute route is active and open dropdown
            const isAttributeActive = attributesMenu.querySelector('.nav-link.active');
            if (isAttributeActive) {
                attributesDropdown.classList.add('active');
                attributesMenu.classList.add('show');
            }

            // Load saved dropdown state
            const savedState = localStorage.getItem('attributesDropdownOpen');
            if (savedState === 'true' || isAttributeActive) {
                attributesDropdown.classList.add('active');
                attributesMenu.classList.add('show');
            }

            // Toggle dropdown on click
            attributesDropdown.addEventListener('click', function () {
                const isOpen = attributesMenu.classList.toggle('show');
                this.classList.toggle('active');
                localStorage.setItem('attributesDropdownOpen', isOpen);
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
    </script>

    @stack('scripts')
</body>

</html>