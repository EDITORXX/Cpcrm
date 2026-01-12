<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Telecaller - Base CRM')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ session('telecaller_api_token') ?? session('api_token') ?? (auth()->check() ? auth()->user()->createToken('web-token')->plainTextToken : '') }}">
    <meta name="user-id" content="{{ auth()->check() ? auth()->user()->id : '' }}">
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; overflow-x: hidden; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; width: 100%; max-width: 100vw; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; width: 100%; box-sizing: border-box; overflow-x: hidden; }
        .header { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; width: 100%; box-sizing: border-box; max-width: 100%; overflow-x: hidden; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.3s; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
        }
        .sidebar-link:hover {
            background: #F7F6F3 !important;
            color: #205A44 !important;
        }
        .sidebar-link.active {
            background: #F7F6F3 !important;
            color: #205A44 !important;
            font-weight: 500 !important;
        }
        .coming-soon {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            text-align: center;
            padding: 40px;
        }
        .coming-soon-icon {
            font-size: 64px;
            color: #205A44;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        .coming-soon h2 {
            font-size: 28px;
            font-weight: 700;
            color: #063A1C;
            margin-bottom: 12px;
        }
        .coming-soon p {
            font-size: 16px;
            color: #B3B5B4;
            max-width: 500px;
        }
        /* Mobile responsive styles */
        @media (max-width: 1024px) {
            .container { margin-left: 0 !important; padding: 15px; width: 100% !important; }
            aside { transform: translateX(-100%); transition: transform 0.3s ease; }
            aside.sidebar-open { transform: translateX(0); }
            .header { padding: 15px !important; }
        }
        
        @media (max-width: 768px) {
            .container { margin-left: 0 !important; padding: 10px; width: 100% !important; }
            .header { flex-direction: column; gap: 15px; align-items: flex-start !important; padding: 15px !important; }
            .header h1 { font-size: 20px !important; margin: 0 !important; }
            .header > div:first-child { width: 100%; }
            .header > div:last-child { width: 100%; justify-content: space-between; }
            aside { width: 280px; z-index: 1000; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }
            .sidebar-overlay.active { display: block; }
        }
        
        @media (max-width: 480px) {
            aside { width: 100%; max-width: 300px; }
            .container { padding: 8px !important; }
            .header { padding: 12px !important; }
        }
        
        /* Sidebar toggle button */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #205A44;
            color: white;
            border: none;
            border-radius: 8px;
            width: 44px;
            height: 44px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .sidebar-toggle:hover {
            background: #063A1C;
            transform: scale(1.05);
        }
        
        .sidebar-close-btn:hover {
            background: #e0e0e0;
        }
        
        @media (max-width: 1024px) {
            .sidebar-toggle { display: flex; }
            .sidebar-close-btn { display: flex !important; }
        }
        
        /* Prevent body scroll when sidebar is open on mobile */
        body.sidebar-open-mobile {
            overflow: hidden;
        }
        
        /* Custom Notification Styles */
        .custom-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10000;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            padding: 40px 50px;
            min-width: 400px;
            max-width: 500px;
            text-align: center;
            opacity: 0;
            animation: fadeInScale 0.3s ease-out forwards;
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        
        .custom-notification.hide {
            animation: fadeOutScale 0.3s ease-in forwards;
        }
        
        @keyframes fadeOutScale {
            from {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            to {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
        }
        
        .notification-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            opacity: 0;
            animation: fadeIn 0.3s ease-out forwards;
        }
        
        .notification-overlay.hide {
            animation: fadeOut 0.3s ease-in forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .tick-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            position: relative;
        }
        
        .tick-icon svg {
            width: 100%;
            height: 100%;
        }
        
        .tick-circle {
            fill: #10b981;
            animation: scaleIn 0.4s ease-out;
        }
        
        .tick-path {
            stroke: white;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            stroke-dasharray: 50;
            stroke-dashoffset: 50;
            animation: drawTick 0.6s ease-out 0.3s forwards;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        @keyframes drawTick {
            to {
                stroke-dashoffset: 0;
            }
        }
        
        .notification-message {
            font-size: 18px;
            font-weight: 600;
            color: #063A1C;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .notification-button {
            background: #205A44;
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .notification-button:hover {
            background: #063A1C;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(32, 90, 68, 0.3);
        }
        
        .error-notification .tick-circle {
            fill: #ef4444;
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.4s ease-out;
        }
        
        .error-icon::before,
        .error-icon::after {
            content: '';
            position: absolute;
            width: 3px;
            height: 40px;
            background: white;
            border-radius: 2px;
        }
        
        .error-icon::before {
            transform: rotate(45deg);
        }
        
        .error-icon::after {
            transform: rotate(-45deg);
        }
        
        .warning-notification .tick-circle {
            fill: #f59e0b;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
        <i class="fas fa-bars" id="sidebarToggleIcon"></i>
    </button>
    
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-[#F7F6F3] border-r border-[#E5DED4] shadow-sm z-30" style="overflow-y: auto;">
        <div style="padding: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 24px; font-weight: 700; color: #063A1C; margin-bottom: 0;">Base CRM</h2>
            <button onclick="closeSidebar()" class="sidebar-close-btn" id="sidebarCloseBtn" style="display: none; background: none; border: none; font-size: 24px; color: #063A1C; cursor: pointer; padding: 5px; width: 32px; height: 32px; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.3s;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav style="padding: 0 20px;">
            <a href="{{ route('telecaller.dashboard') }}" class="sidebar-link {{ request()->routeIs('telecaller.dashboard') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: {{ request()->routeIs('telecaller.dashboard') ? '#205A44' : '#063A1C' }}; transition: all 0.3s; {{ request()->routeIs('telecaller.dashboard') ? 'background: #F7F6F3; font-weight: 500;' : '' }}">
                <i class="fas fa-home" style="margin-right: 10px; width: 20px;"></i>
                Dashboard
            </a>
            <a href="{{ route('telecaller.tasks') }}" class="sidebar-link {{ request()->routeIs('telecaller.tasks') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: {{ request()->routeIs('telecaller.tasks') ? '#205A44' : '#063A1C' }}; transition: all 0.3s; {{ request()->routeIs('telecaller.tasks') ? 'background: #F7F6F3; font-weight: 500;' : '' }}">
                <i class="fas fa-tasks" style="margin-right: 10px; width: 20px;"></i>
                Task
            </a>
            <a href="{{ route('telecaller.leads') }}" class="sidebar-link {{ request()->routeIs('telecaller.leads') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: {{ request()->routeIs('telecaller.leads') ? '#205A44' : '#063A1C' }}; transition: all 0.3s; {{ request()->routeIs('telecaller.leads') ? 'background: #F7F6F3; font-weight: 500;' : '' }}">
                <i class="fas fa-user-friends" style="margin-right: 10px; width: 20px;"></i>
                Lead
            </a>
            {{-- Reports section hidden --}}
            {{-- <a href="{{ route('telecaller.reports') }}" class="sidebar-link {{ request()->routeIs('telecaller.reports') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: {{ request()->routeIs('telecaller.reports') ? '#205A44' : '#063A1C' }}; transition: all 0.3s; {{ request()->routeIs('telecaller.reports') ? 'background: #F7F6F3; font-weight: 500;' : '' }}">
                <i class="fas fa-chart-bar" style="margin-right: 10px; width: 20px;"></i>
                Report
            </a> --}}
            <a href="{{ route('telecaller.verification-pending') }}" class="sidebar-link {{ request()->routeIs('telecaller.verification-pending') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: {{ request()->routeIs('telecaller.verification-pending') ? '#205A44' : '#063A1C' }}; transition: all 0.3s; {{ request()->routeIs('telecaller.verification-pending') ? 'background: #F7F6F3; font-weight: 500;' : '' }}">
                <i class="fas fa-clock" style="margin-right: 10px; width: 20px;"></i>
                Verification Pending
            </a>
            <a href="{{ route('telecaller.profile') }}" class="sidebar-link {{ request()->routeIs('telecaller.profile') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 16px; margin-bottom: 8px; border-radius: 8px; text-decoration: none; color: {{ request()->routeIs('telecaller.profile') ? '#205A44' : '#063A1C' }}; transition: all 0.3s; {{ request()->routeIs('telecaller.profile') ? 'background: #F7F6F3; font-weight: 500;' : '' }}">
                <i class="fas fa-user" style="margin-right: 10px; width: 20px;"></i>
                Profile
            </a>
        </nav>
    </aside>
    
    <div class="container" id="mainContainer" style="margin-left: 256px;">
        <!-- Header -->
        <div class="header">
            <div>
                <h1 style="font-size: 24px; font-weight: 700; color: #063A1C;">@yield('page-title', 'Telecaller Dashboard')</h1>
                <p style="color: #B3B5B4; margin-top: 4px;" id="userName">Loading...</p>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <!-- Date/Time Clock -->
                <div id="datetimeClock" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 8px 12px; font-family: 'Courier New', monospace; font-weight: 600; font-size: 14px; color: #063A1C; min-width: 160px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div id="clockTime" style="font-size: 16px; color: #205A44;">--:--:--</div>
                    <div id="clockDate" style="font-size: 11px; color: #B3B5B4; margin-top: 2px;">-- -- ----</div>
                </div>
                <!-- Notification Bell -->
                <div style="position: relative;">
                    <button id="notificationBell" onclick="toggleNotificationDropdown()" style="position: relative; background: #F7F6F3; border: none; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-bell" style="font-size: 20px; color: #063A1C;"></i>
                        <span id="notificationBadge" style="position: absolute; top: -2px; right: -2px; background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; display: none; align-items: center; justify-content: center; font-size: 11px; font-weight: 600;">0</span>
                    </button>
                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown" style="position: absolute; top: 50px; right: 0; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); width: 380px; max-height: 500px; overflow-y: auto; z-index: 1000; display: none;">
                        <div style="padding: 16px; border-bottom: 2px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="font-size: 18px; font-weight: 600; color: #063A1C; margin: 0;">Notifications</h3>
                            <button onclick="markAllNotificationsRead()" style="background: none; border: none; color: #205A44; font-size: 14px; cursor: pointer; font-weight: 500;">Mark all read</button>
                        </div>
                        <div id="notificationList" style="padding: 8px;">
                            <div style="text-align: center; padding: 40px 20px; color: #B3B5B4;">
                                <i class="fas fa-bell-slash" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                                <p>No notifications</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button onclick="logout()" class="btn btn-danger">Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Custom Notification Component -->
    <div id="notificationOverlay" class="notification-overlay" style="display: none;"></div>
    <div id="customNotification" class="custom-notification" style="display: none;">
        <div class="tick-icon">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle class="tick-circle" cx="50" cy="50" r="45"/>
                <path class="tick-path" d="M 30 50 L 45 65 L 70 35"/>
            </svg>
        </div>
        <div class="notification-message" id="notificationMessage"></div>
        <button class="notification-button" onclick="closeNotification()">OK</button>
    </div>

    <script>
        var API_BASE_URL = '{{ url("/api") }}';
        
        // Initialize token from session on page load for web-logged-in telecallers
        @if(auth()->check() && auth()->user()->isTelecaller())
            @php
                $token = session('telecaller_api_token');
                $user = auth()->user()->load('role', 'manager');
            @endphp
            @if($token)
                localStorage.setItem('telecaller_token', '{{ $token }}');
                try {
                    const userData = @json($user);
                    localStorage.setItem('telecaller_user', JSON.stringify(userData));
                    // Store password for auto-fill in change password form
                    @php
                        $storedPassword = session('user_password_for_change');
                    @endphp
                    @if($storedPassword)
                        localStorage.setItem('user_current_password', '{{ $storedPassword }}');
                    @endif
                    console.log('Token initialized from session');
                } catch (e) {
                    console.error('Error setting user data in localStorage:', e);
                }
            @endif
        @endif
        
        // Get token from localStorage
        function getToken() {
            return localStorage.getItem('telecaller_token');
        }

        // Load user info
        function loadUserInfo() {
            const userStr = localStorage.getItem('telecaller_user');
            if (userStr) {
                try {
                    // Check if it's already an object or a string
                    let user;
                    if (typeof userStr === 'string') {
                        user = JSON.parse(userStr);
                    } else {
                        user = userStr; // Already an object
                    }
                    const userNameEl = document.getElementById('userName');
                    if (userNameEl && user) {
                        userNameEl.textContent = user.name || 'User';
                    }
                } catch (e) {
                    console.error('Error parsing user data:', e);
                    // Try to get user from session as fallback
                    @if(auth()->check() && auth()->user()->isTelecaller())
                        @php
                            $user = auth()->user();
                        @endphp
                        const userNameEl = document.getElementById('userName');
                        if (userNameEl) {
                            userNameEl.textContent = '{{ $user->name }}';
                        }
                    @endif
                }
            } else {
                // If no user in localStorage but user is logged in via session, use session data
                @if(auth()->check() && auth()->user()->isTelecaller())
                    @php
                        $user = auth()->user();
                    @endphp
                    const userNameEl = document.getElementById('userName');
                    if (userNameEl) {
                        userNameEl.textContent = '{{ $user->name }}';
                    }
                @endif
            }
        }

        // Logout function
        async function logout() {
            try {
                const token = getToken();
                if (token) {
                    try {
                        await fetch(`${API_BASE_URL}/telecaller/logout`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${token}`,
                            },
                        });
                    } catch (error) {
                        console.error('Logout API call failed:', error);
                    }
                }
            } catch (error) {
                console.error('Error during logout:', error);
            } finally {
                localStorage.removeItem('telecaller_token');
                localStorage.removeItem('telecaller_user');
                localStorage.removeItem('user_current_password');
                window.location.href = '{{ route("login") }}';
            }
        }

        // Custom Notification Functions
        let notificationTimeout = null;
        
        function showNotification(message, type = 'success', duration = 3000) {
            const overlay = document.getElementById('notificationOverlay');
            const notification = document.getElementById('customNotification');
            const messageEl = document.getElementById('notificationMessage');
            const tickIcon = notification.querySelector('.tick-icon');
            
            // Clear any existing timeout
            if (notificationTimeout) {
                clearTimeout(notificationTimeout);
            }
            
            // Remove previous type classes
            notification.classList.remove('success-notification', 'error-notification', 'warning-notification');
            notification.classList.add(type + '-notification');
            
            // Update message
            messageEl.textContent = message;
            
            // Update icon based on type
            if (type === 'error') {
                tickIcon.innerHTML = `
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <circle class="tick-circle" cx="50" cy="50" r="45" fill="#ef4444"/>
                        <path d="M 30 30 L 70 70 M 70 30 L 30 70" stroke="white" stroke-width="6" stroke-linecap="round" stroke-dasharray="50" stroke-dashoffset="50" class="tick-path"/>
                    </svg>
                `;
            } else if (type === 'warning') {
                tickIcon.innerHTML = `
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <circle class="tick-circle" cx="50" cy="50" r="45"/>
                        <text x="50" y="70" text-anchor="middle" fill="white" font-size="60" font-weight="bold">!</text>
                    </svg>
                `;
            } else {
                // Success tick
                tickIcon.innerHTML = `
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <circle class="tick-circle" cx="50" cy="50" r="45"/>
                        <path class="tick-path" d="M 30 50 L 45 65 L 70 35"/>
                    </svg>
                `;
            }
            
            // Show notification
            overlay.style.display = 'block';
            notification.style.display = 'block';
            overlay.classList.remove('hide');
            notification.classList.remove('hide');
            
            // Auto hide after duration
            if (duration > 0) {
                notificationTimeout = setTimeout(() => {
                    closeNotification();
                }, duration);
            }
        }
        
        function closeNotification() {
            const overlay = document.getElementById('notificationOverlay');
            const notification = document.getElementById('customNotification');
            
            overlay.classList.add('hide');
            notification.classList.add('hide');
            
            setTimeout(() => {
                overlay.style.display = 'none';
                notification.style.display = 'none';
                overlay.classList.remove('hide');
                notification.classList.remove('hide');
            }, 300);
            
            if (notificationTimeout) {
                clearTimeout(notificationTimeout);
                notificationTimeout = null;
            }
        }
        
        // Override browser alert for better UX
        window.customAlert = function(message, type = 'success') {
            showNotification(message, type, 3000);
        };

        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleIcon = document.getElementById('sidebarToggleIcon');
            const body = document.body;
            
            sidebar.classList.toggle('sidebar-open');
            overlay.classList.toggle('active');
            body.classList.toggle('sidebar-open-mobile');
            
            // Change icon
            if (sidebar.classList.contains('sidebar-open')) {
                toggleIcon.classList.remove('fa-bars');
                toggleIcon.classList.add('fa-times');
            } else {
                toggleIcon.classList.remove('fa-times');
                toggleIcon.classList.add('fa-bars');
            }
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleIcon = document.getElementById('sidebarToggleIcon');
            const body = document.body;
            
            sidebar.classList.remove('sidebar-open');
            overlay.classList.remove('active');
            body.classList.remove('sidebar-open-mobile');
            
            toggleIcon.classList.remove('fa-times');
            toggleIcon.classList.add('fa-bars');
        }
        
        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    setTimeout(closeSidebar, 300);
                }
            });
        });
        
        // Responsive container margin
        function adjustContainerMargin() {
            const container = document.getElementById('mainContainer');
            const sidebar = document.getElementById('sidebar');
            
            if (window.innerWidth <= 1024) {
                container.style.marginLeft = '0';
            } else {
                container.style.marginLeft = '256px';
            }
        }
        
        // Adjust on load and resize
        window.addEventListener('resize', adjustContainerMargin);
        adjustContainerMargin();

        // Initialize on page load
        (function() {
            loadUserInfo();
        })();
    </script>
    
    @stack('scripts')
    
    <!-- Load notification script after API_BASE_URL is defined -->
    <script>
        // Ensure API_BASE_URL is available globally before notification script loads
        if (typeof API_BASE_URL === 'undefined') {
            var API_BASE_URL = '{{ url("/api") }}';
        }
    </script>
    <script src="{{ asset('js/telecaller-notifications.js') }}"></script>
    
    <!-- Chatbot Assistant Widget -->
    @include('components.chatbot-widget')
    
    <!-- Chatbot Assistant Script -->
    <script src="{{ asset('js/chatbot-assistant.js') }}"></script>
    
    <!-- Live Clock Functionality -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeElement = document.getElementById('clockTime');
            const dateElement = document.getElementById('clockDate');
            
            if (timeElement && dateElement) {
                // Format time: HH:MM:SS
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                timeElement.textContent = `${hours}:${minutes}:${seconds}`;
                
                // Format date: DD MMM YYYY
                const date = now.toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
                dateElement.textContent = date;
            }
        }
        
        // Update clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>
</html>

