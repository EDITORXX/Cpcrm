<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sales Manager - Base CRM')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ session('api_token') ?? (auth()->check() ? auth()->user()->createToken('web-token')->plainTextToken : '') }}">
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
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        
        /* Mobile First - Base Styles */
        .container { max-width: 100%; width: 100%; padding: 12px; }
        .header { 
            background: white; 
            padding: 16px; 
            border-radius: 12px; 
            margin-bottom: 16px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            display: flex; 
            flex-direction: column;
            gap: 12px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .header-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }
        .header-actions-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn { 
            padding: 10px 16px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 500; 
            transition: all 0.3s; 
            white-space: nowrap;
        }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        
        /* Sidebar Styles - Always Icon-Only (64px) - Desktop Only */
        #sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 64px !important; /* Always icon-only */
            background: white;
            border-right: 1px solid #e0e0e0;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            transform: translateX(0);
        }
        
        /* Mobile Footer Navigation - Hidden by default */
        #mobileFooterNav {
            display: none;
        }
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Force icon-only - remove expanded state */
        #sidebar.sidebar-expanded {
            width: 64px !important; /* Always icon-only */
        }
        
        /* Remove hidden state - always visible */
        #sidebar.sidebar-hidden {
            width: 64px !important; /* Always icon-only, never hidden */
            transform: translateX(0);
        }
        
        /* Sidebar Link Styles - Always Icon-Only */
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
            justify-content: center !important; /* Always center icons */
        }
        
        .sidebar-link i {
            font-size: 18px;
            width: 20px;
            text-align: center;
            margin-right: 0 !important; /* No margin, always centered */
        }
        
        /* Always hide text labels */
        .sidebar-link span {
            display: none !important; /* Permanently hidden */
            font-size: 14px;
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
        
        /* Sidebar Logo Area - Always Hidden in Icon-Only Mode */
        #sidebar > div:first-child {
            padding: 20px 12px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        /* Always hide logo text */
        #sidebar > div:first-child h2 {
            display: none !important; /* Permanently hidden */
        }
        
        #sidebar > div:first-child p {
            display: none !important; /* Permanently hidden */
        }
        
        /* Sidebar Navigation */
        #sidebar nav {
            padding: 0 12px;
        }
        
        /* Sidebar Toggle Button - Hidden (not needed for icon-only mode) */
        .sidebar-toggle {
            display: none !important; /* Hide toggle button - sidebar always icon-only */
        }
        
        /* Main Content - Mobile First */
        #mainContent {
            margin-left: 64px !important;
            min-height: 100vh;
            width: calc(100% - 64px) !important;
            background: #F7F6F3;
        }
        
        /* Clock Widget Responsive */
        #datetimeClock {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 12px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 12px;
            color: #063A1C;
            min-width: 140px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        #clockTime {
            font-size: 14px;
            color: #205A44;
        }
        
        #clockDate {
            font-size: 10px;
            color: #B3B5B4;
            margin-top: 2px;
        }
        
        /* Tablet Styles */
        @media (min-width: 768px) {
            .container { padding: 20px; }
            .header { 
                flex-direction: row; 
                padding: 20px;
                margin-bottom: 20px;
            }
            .header-actions {
                flex-direction: row;
                width: auto;
                align-items: center;
            }
            .header-actions-row {
                flex-wrap: nowrap;
            }
            .btn { 
                padding: 12px 24px; 
                font-size: 16px;
                width: auto;
            }
            .header form {
                width: auto;
            }
            /* Always icon-only (64px) on desktop/tablet */
            #sidebar {
                width: 64px !important;
                display: block !important;
            }
            #sidebar.sidebar-expanded {
                width: 64px !important;
            }
            #mainContent {
                margin-left: 64px !important;
                width: calc(100% - 64px) !important;
                padding-bottom: 0 !important; /* No footer padding on desktop */
            }
            /* Hide mobile footer on desktop */
            #mobileFooterNav {
                display: none !important;
            }
            .sidebar-toggle {
                top: 50%;
                left: 52px; /* Position on right edge of 64px sidebar */
                transform: translateY(-50%);
                border-radius: 50%;
            }
            #datetimeClock {
                font-size: 14px;
                min-width: 160px;
            }
            #clockTime {
                font-size: 16px;
            }
            #clockDate {
                font-size: 11px;
            }
            .sidebar-overlay {
                display: none !important;
            }
        }
        
        /* Desktop Styles */
        @media (min-width: 1024px) {
            .container { padding: 20px; }
        }
        
        /* Mobile Specific - Hide Sidebar, Show Footer */
        @media (max-width: 767px) {
            /* Hide sidebar on mobile */
            #sidebar {
                display: none !important;
            }
            
            /* Main content full width on mobile */
            #mainContent {
                margin-left: 0 !important;
                width: 100% !important;
                padding-bottom: 70px; /* Space for footer */
            }
            
            /* Remove left padding to eliminate blank sidebar area */
            .container {
                padding-left: 12px !important;
                padding-right: 12px !important;
                margin-left: 0 !important;
            }
            
            /* Footer Navigation for Mobile */
            #mobileFooterNav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                background: white;
                border-top: 1px solid #e0e0e0;
                box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
                z-index: 1000;
                padding: 8px 0;
                justify-content: space-around;
                align-items: center;
            }
            
            .footer-nav-link {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                color: #666;
                padding: 6px 4px;
                border-radius: 8px;
                transition: all 0.3s;
                flex: 1;
                max-width: 60px;
            }
            
            .footer-nav-link i {
                font-size: 18px;
                margin-bottom: 2px;
            }
            
            .footer-nav-link span {
                font-size: 9px;
                color: #666;
                text-align: center;
                line-height: 1.2;
            }
            
            .footer-nav-link:hover,
            .footer-nav-link.active {
                background: #F7F6F3;
                color: #205A44;
            }
            
            .footer-nav-link.active {
                color: #205A44;
            }
            
            .footer-nav-link.active span {
                color: #205A44;
            }
        }
        
        /* Desktop - Show Sidebar, Hide Footer */
        @media (min-width: 768px) {
            #mobileFooterNav {
                display: none !important;
            }
            
            #sidebar {
                display: block !important;
            }
        }
        
        /* Prevent sidebar flash on page load */
        html.sidebar-mobile-collapsed #sidebar {
            width: 64px !important;
        }
        
        html.sidebar-mobile-collapsed #mainContent {
            margin-left: 64px !important;
            width: calc(100% - 64px) !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    <script>
        // Mobile detection and initial sidebar state
        (function() {
            const isMobile = window.innerWidth < 768;
            const sidebar = document.getElementById('sidebar');
            const html = document.documentElement;
            
            if (isMobile) {
                // Mobile: Force collapsed by default
                html.classList.add('sidebar-mobile-collapsed');
                if (sidebar) {
                    sidebar.classList.remove('sidebar-expanded');
                }
                // Store mobile state
                localStorage.setItem('salesManagerSidebarMobile', 'collapsed');
            } else {
                // Desktop: Check saved state
                const isExpanded = localStorage.getItem('salesManagerSidebarExpanded') !== 'false';
                if (sidebar) {
                    if (isExpanded) {
                        sidebar.classList.add('sidebar-expanded');
                    } else {
                        sidebar.classList.remove('sidebar-expanded');
                    }
                }
            }
        })();
    </script>
    
    <!-- Sidebar Overlay (Mobile Only) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle" class="sidebar-toggle" title="Toggle Sidebar">
        <i class="fas fa-chevron-left sidebar-toggle-icon" id="sidebarToggleIcon"></i>
    </button>
    
    <!-- Sidebar -->
    <aside id="sidebar">
        <div>
            <h2>Base CRM</h2>
            <p>Sales Manager</p>
        </div>
        <nav>
            <a href="{{ route('sales-manager.dashboard') }}" class="sidebar-link {{ request()->routeIs('sales-manager.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('sales-manager.tasks') }}" class="sidebar-link {{ request()->routeIs('sales-manager.tasks*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span>Tasks</span>
            </a>
            <a href="{{ route('sales-manager.prospects') }}" class="sidebar-link {{ request()->routeIs('sales-manager.prospects') ? 'active' : '' }}">
                <i class="fas fa-star"></i>
                <span>Prospects</span>
            </a>
            <a href="{{ route('sales-manager.leads') }}" class="sidebar-link {{ request()->routeIs('sales-manager.leads') || (request()->routeIs('leads.show') && auth()->check() && auth()->user()->isSalesManager()) ? 'active' : '' }}">
                <i class="fas fa-user-friends"></i>
                <span>Leads</span>
            </a>
            <a href="{{ route('sales-manager.meetings') }}" class="sidebar-link {{ request()->routeIs('sales-manager.meetings*') ? 'active' : '' }}">
                <i class="fas fa-handshake"></i>
                <span>Meetings</span>
            </a>
            <a href="{{ route('sales-manager.site-visits') }}" class="sidebar-link {{ request()->routeIs('sales-manager.site-visits*') ? 'active' : '' }}">
                <i class="fas fa-map-marker-alt"></i>
                <span>Site Visits</span>
            </a>
            <a href="{{ route('sales-manager.team') }}" class="sidebar-link {{ request()->routeIs('sales-manager.team') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>My Team</span>
            </a>
            <a href="{{ route('sales-manager.profile') }}" class="sidebar-link {{ request()->routeIs('sales-manager.profile') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div id="mainContent" style="margin-left: 64px; min-height: 100vh; width: calc(100% - 64px); background: #F7F6F3;">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="header-top">
                    <h1 style="font-size: 24px; font-weight: 700; color: #063A1C;">@yield('page-title', 'Sales Manager')</h1>
                </div>
                <div class="header-actions">
                    <div class="header-actions-row">
                        <!-- Date/Time Clock -->
                        <div id="datetimeClock">
                            <div id="clockTime">--:--:--</div>
                            <div id="clockDate">-- -- ----</div>
                        </div>
                        <span style="color: #B3B5B4; font-size: 14px; white-space: nowrap;">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            @yield('content')
        </div>
    </div>

    <!-- Mobile Footer Navigation -->
    <nav id="mobileFooterNav">
        <a href="{{ route('sales-manager.dashboard') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('sales-manager.tasks') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.tasks*') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('sales-manager.prospects') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.prospects') ? 'active' : '' }}">
            <i class="fas fa-star"></i>
            <span>Prospects</span>
        </a>
        <a href="{{ route('sales-manager.leads') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.leads') || (request()->routeIs('leads.show') && auth()->check() && auth()->user()->isSalesManager()) ? 'active' : '' }}">
            <i class="fas fa-user-friends"></i>
            <span>Leads</span>
        </a>
        <a href="{{ route('sales-manager.meetings') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.meetings*') ? 'active' : '' }}">
            <i class="fas fa-handshake"></i>
            <span>Meetings</span>
        </a>
        <a href="{{ route('sales-manager.site-visits') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.site-visits*') ? 'active' : '' }}">
            <i class="fas fa-map-marker-alt"></i>
            <span>Visits</span>
        </a>
        <a href="{{ route('sales-manager.team') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.team') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Team</span>
        </a>
        <a href="{{ route('sales-manager.profile') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <!-- Custom Notification System -->
    <div id="notificationOverlay" class="fixed inset-0 z-[9999] pointer-events-none flex items-center justify-center" style="display: none;">
        <div id="customNotification" class="bg-white rounded-lg shadow-2xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-0" style="pointer-events: auto;">
            <div class="flex items-center justify-center mb-4">
                <div class="tick-icon w-16 h-16 rounded-full flex items-center justify-center bg-green-100">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
            </div>
            <p id="notificationMessage" class="text-center text-gray-800 font-medium text-lg"></p>
        </div>
    </div>

    <style>
        #customNotification.show {
            animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }
        
        #customNotification.hide {
            animation: popOut 0.3s ease-in forwards;
        }
        
        @keyframes popIn {
            0% {
                transform: scale(0) translateY(-20px);
                opacity: 0;
            }
            50% {
                transform: scale(1.05) translateY(0);
            }
            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes popOut {
            0% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
            100% {
                transform: scale(0.8) translateY(-20px);
                opacity: 0;
            }
        }
        
        .tick-icon {
            animation: tickAnimation 0.6s ease-in-out;
        }
        
        @keyframes tickAnimation {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }
        
        #customNotification.error .tick-icon {
            background: #fee2e2;
        }
        
        #customNotification.error .tick-icon i {
            color: #dc2626;
        }
        
        #customNotification.error .tick-icon i:before {
            content: '\f00d';
        }
        
        #customNotification.warning .tick-icon {
            background: #fef3c7;
        }
        
        #customNotification.warning .tick-icon i {
            color: #d97706;
        }
        
        #customNotification.warning .tick-icon i:before {
            content: '\f071';
        }
    </style>

    <script>
        function showNotification(message, type = 'success', duration = 3000) {
            const overlay = document.getElementById('notificationOverlay');
            const notification = document.getElementById('customNotification');
            const messageEl = document.getElementById('notificationMessage');
            const tickIcon = notification.querySelector('.tick-icon');
            
            // Remove previous type classes
            notification.classList.remove('success', 'error', 'warning');
            
            // Set message and type
            messageEl.textContent = message;
            notification.classList.add(type);
            
            // Show overlay and notification
            overlay.style.display = 'flex';
            notification.style.transform = 'scale(0)';
            
            // Trigger animation
            setTimeout(() => {
                notification.classList.remove('hide');
                notification.classList.add('show');
            }, 10);
            
            // Hide after duration
            setTimeout(() => {
                notification.classList.remove('show');
                notification.classList.add('hide');
                
                setTimeout(() => {
                    overlay.style.display = 'none';
                    notification.classList.remove('hide');
                }, 300);
            }, duration);
        }
        
        // Auto-close on click
        document.getElementById('customNotification')?.addEventListener('click', function() {
            const overlay = document.getElementById('notificationOverlay');
            const notification = document.getElementById('customNotification');
            notification.classList.remove('show');
            notification.classList.add('hide');
            
            setTimeout(() => {
                overlay.style.display = 'none';
                notification.classList.remove('hide');
            }, 300);
        });
    </script>

    @stack('scripts')
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
        
        // Sidebar always icon-only - no toggle functionality needed
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (sidebar) {
                // Ensure sidebar is always 64px (icon-only)
                sidebar.classList.remove('sidebar-expanded', 'sidebar-hidden');
                sidebar.style.width = '64px';
            }
            
            if (mainContent) {
                // Ensure main content always has 64px margin
                mainContent.style.marginLeft = '64px';
                mainContent.style.width = 'calc(100% - 64px)';
            }
        });
    </script>
</body>
</html>

