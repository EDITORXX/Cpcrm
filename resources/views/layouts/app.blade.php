<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Base CRM')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ session('api_token') ?? (auth()->check() ? auth()->user()->createToken('web-token')->plainTextToken : '') }}">
    <meta name="user-id" content="{{ auth()->check() ? auth()->user()->id : '' }}">
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Poppins', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        'primary-dark': 'var(--text-color)',
                        'primary': 'var(--text-color)',
                        'secondary': 'var(--link-color)',
                        'brand-bg': '#F7F6F3',
                        'brand-border': '#E5DED4',
                        'text-muted': '#B3B5B4',
                    },
                },
            },
        }
    </script>
    
    <!-- Additional scripts -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: {{ primary_color() }};
            --secondary-color: {{ secondary_color() }};
            --accent-color: {{ accent_color() }};
            --gradient-start: {{ gradient_start_color() }};
            --gradient-end: {{ gradient_end_color() }};
            --text-color: {{ text_color() }};
            --link-color: {{ link_color() }};
            --background-color: {{ background_color() }};
            --text-primary: {{ text_color() }};
            --text-secondary: {{ link_color() }};
            --text-muted: #B3B5B4;
            --border-color: {{ primary_color() }};
            --avatar-bg: {{ gradient_start_color() }};
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        .container { max-width: 100%; margin: 0 auto; padding: 20px; width: 100%; box-sizing: border-box; }
        .header { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.3s; }
        
        /* Branded Button Classes */
        .btn-brand-primary, .btn-brand-gradient {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            @else
                background-color: var(--primary-color);
            @endif
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-brand-primary:hover, .btn-brand-gradient:hover {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color));
            @else
                background-color: var(--secondary-color);
            @endif
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn-brand-secondary {
            background-color: var(--secondary-color);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-brand-secondary:hover {
            background-color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Legacy button classes - now use branding */
        .btn-primary, .btn-success, .btn-secondary, .btn-warning {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            @else
                background-color: var(--primary-color) !important;
            @endif
            color: white !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary:hover, .btn-success:hover, .btn-secondary:hover, .btn-warning:hover {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
            @else
                background-color: var(--secondary-color) !important;
            @endif
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Dynamic gradient button class - uses CSS variables */
        .btn-gradient-dynamic {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            color: white !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-gradient-dynamic:hover {
            background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Override all gradient buttons to use CSS variables - High specificity */
        body a.bg-gradient-to-r,
        body button.bg-gradient-to-r,
        body a[class*="from-[#063A1C]"],
        body button[class*="from-[#063A1C]"],
        body a[class*="from-[#205A44]"],
        body button[class*="from-[#205A44]"] {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            border: none !important;
        }
        body a.bg-gradient-to-r:hover,
        body button.bg-gradient-to-r:hover,
        body a[class*="from-[#063A1C]"]:hover,
        body button[class*="from-[#063A1C]"]:hover,
        body a[class*="from-[#205A44]"]:hover,
        body button[class*="from-[#205A44]"]:hover {
            background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
        }
        
        /* Override hover classes */
        body a[class*="hover:from-[#205A44]"]:hover,
        body button[class*="hover:from-[#205A44]"]:hover,
        body a[class*="hover:to-[#15803d]"]:hover,
        body button[class*="hover:to-[#15803d]"]:hover {
            background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
        }
        
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        
        /* Branding Utility Classes */
        .text-brand-primary {
            color: var(--text-color) !important;
        }
        .text-brand-secondary {
            color: var(--link-color) !important;
        }
        .text-brand-muted {
            color: var(--text-muted) !important;
        }
        .border-brand {
            border-color: var(--primary-color) !important;
        }
        .border-brand-secondary {
            border-color: var(--secondary-color) !important;
        }
        .bg-brand-avatar {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            @else
                background-color: var(--primary-color) !important;
            @endif
        }
        
        /* Global CSS Override Rules - Force hardcoded Tailwind classes to use CSS variables */
        /* Override hardcoded text colors */
        [class*="text-[#063A1C]"], [class*="text-[#205A44]"] {
            color: var(--text-color) !important;
        }
        
        /* Override hardcoded border colors */
        [class*="border-[#063A1C]"], [class*="border-[#205A44]"] {
            border-color: var(--primary-color) !important;
        }
        
        /* Override hardcoded background colors for avatars and brand elements */
        [class*="bg-[#063A1C]"], [class*="bg-[#205A44]"] {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            @else
                background-color: var(--primary-color) !important;
            @endif
        }
        
        /* Override hardcoded gradient classes for cards */
        [class*="from-[#205A44]"], [class*="from-[#063A1C]"], 
        [class*="to-[#063A1C]"], [class*="to-[#205A44]"],
        [class*="bg-gradient-to-br"][class*="from-[#205A44]"],
        [class*="bg-gradient-to-br"][class*="from-[#063A1C]"] {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
        }
        
        /* Override hover states for hardcoded backgrounds */
        [class*="hover:bg-[#205A44]"], [class*="hover:bg-[#063A1C]"], [class*="hover:bg-[#15803d]"] {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
            @else
                background-color: var(--secondary-color) !important;
            @endif
        }
        
        /* Override focus ring colors */
        [class*="focus:ring-[#205A44]"], [class*="focus:ring-[#063A1C]"] {
            --tw-ring-color: var(--primary-color) !important;
        }
        
        /* Override focus border colors */
        [class*="focus:border-[#205A44]"], [class*="focus:border-[#063A1C]"] {
            border-color: var(--primary-color) !important;
        }
        
        /* Override hover text colors */
        [class*="hover:text-[#205A44]"], [class*="hover:text-[#063A1C]"] {
            color: var(--link-color) !important;
        }
        
        /* Override ring colors (for focus states) */
        [class*="ring-[#063A1C]"], [class*="ring-[#205A44]"] {
            --tw-ring-color: var(--primary-color) !important;
        }
        
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
            color: var(--primary-color) !important;
        }
        .sidebar-link.active {
            background: #F7F6F3 !important;
            color: var(--primary-color) !important;
            font-weight: 500 !important;
        }
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            z-index: 50;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.3s;
        }
        .sidebar-toggle:hover {
            background: var(--secondary-color);
            transform: scale(1.05);
        }
        
        /* Custom CSS from branding settings */
        {!! branded_css() !!}
        aside.sidebar-hidden {
            transform: translateX(-100%);
        }
        aside.sidebar-hidden ~ div {
            margin-left: 0 !important;
        }
        .sidebar-toggle-icon {
            font-size: 18px;
            transition: transform 0.3s;
        }
        /* When sidebar is visible, position button on right side of header */
        .sidebar-toggle {
            left: 236px; /* 256px (sidebar width) - 20px (padding) */
        }
        /* When sidebar is hidden, position button on left */
        body.sidebar-hidden .sidebar-toggle {
            left: 20px;
        }
        @media (max-width: 768px) {
            .container { margin-left: 0; padding: 10px; }
            aside.sidebar-hidden ~ div {
                margin-left: 0 !important;
            }
        }
        
    </style>
    
    @stack('styles')
</head>
<body class="bg-[#F7F6F3] font-sans antialiased" style="margin: 0; padding: 0; overflow: hidden;">
    <!-- Sidebar Toggle Button - Always visible -->
    <button id="sidebarToggle" class="sidebar-toggle" title="Toggle Sidebar">
        <i class="fas fa-chevron-left sidebar-toggle-icon" id="sidebarToggleIcon"></i>
    </button>
    
    <div style="display: flex; height: 100vh; overflow: hidden;">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white border-r border-gray-200 shadow-sm z-30" style="overflow-y: auto; transition: transform 0.3s ease-in-out;">
            <!-- Logo and Role -->
            <div style="padding: 20px; margin-bottom: 30px;">
                <h2 style="font-size: 24px; font-weight: 700; color: var(--text-color); margin-bottom: 10px;">Base CRM</h2>
                <p style="font-size: 12px; color: #B3B5B4;">
                    @if(auth()->user()->isAdmin())
                        Admin
                    @elseif(auth()->user()->isCrm())
                        CRM
                    @elseif(auth()->user()->isSalesHead())
                        Sales Head
                    @elseif(auth()->user()->isSalesManager())
                        Sales Manager
                    @elseif(auth()->user()->isTelecaller())
                        Telecaller
                    @else
                        {{ auth()->user()->role->name ?? 'User' }}
                    @endif
                </p>
            </div>
            
            <!-- Navigation -->
            <nav style="padding: 0 20px;">
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home" style="margin-right: 10px; width: 20px;"></i>
                    Dashboard
                </a>
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users" style="margin-right: 10px; width: 20px;"></i>
                    All Users
                </a>
                <div class="sidebar-link {{ request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : '' }}" style="cursor: pointer;" onclick="toggleLeadsMenu()">
                    <i class="fas fa-user-friends" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                    <i class="fas fa-chevron-down ml-auto" id="leadsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="leadsSubMenu" class="pl-8" style="display: {{ request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'block' : 'none' }};">
                    <a href="{{ route('leads.index') }}" class="sidebar-link {{ request()->routeIs('leads.*') && !request()->routeIs('prospects.*') && !request()->routeIs('meetings.*') && !request()->routeIs('site-visits.*') && !request()->routeIs('closers.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Leads
                    </a>
                    <a href="{{ route('prospects.index') }}" class="sidebar-link {{ request()->routeIs('prospects.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-user-check" style="margin-right: 10px; width: 20px;"></i>
                        Prospects
                    </a>
                    <a href="{{ route('meetings.index') }}" class="sidebar-link {{ request()->routeIs('meetings.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-handshake" style="margin-right: 10px; width: 20px;"></i>
                        Meetings
                    </a>
                    <a href="{{ route('site-visits.index') }}" class="sidebar-link {{ request()->routeIs('site-visits.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 10px; width: 20px;"></i>
                        Visits
                    </a>
                    <a href="{{ route('closers.index') }}" class="sidebar-link {{ request()->routeIs('closers.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                        Closers
                    </a>
                </div>
                <div class="sidebar-link {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'active' : '' }}" style="cursor: pointer;" onclick="toggleProjectsMenu()">
                    <i class="fas fa-project-diagram" style="margin-right: 10px; width: 20px;"></i>
                    Projects
                    <i class="fas fa-chevron-down ml-auto" id="projectsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="projectsSubMenu" class="pl-8" style="display: {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'block' : 'none' }};">
                    <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->routeIs('projects.*') && !request()->routeIs('builders.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Projects
                    </a>
                    <a href="{{ route('builders.index') }}" class="sidebar-link {{ request()->routeIs('builders.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-building" style="margin-right: 10px; width: 20px;"></i>
                        Builders
                    </a>
                </div>
                <a href="{{ route('calls.index') }}" class="sidebar-link {{ request()->routeIs('calls.*') ? 'active' : '' }}">
                    <i class="fas fa-phone" style="margin-right: 10px; width: 20px;"></i>
                    All Calls
                </a>
                <a href="{{ route('chat.index') }}" class="sidebar-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp" style="margin-right: 10px; width: 20px;"></i>
                    WhatsApp Chat
                </a>
                {{-- Reports section hidden --}}
                {{-- <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') && request()->get('tab') == 'reports' ? 'active' : '' }}">
                    <i class="fas fa-chart-bar" style="margin-right: 10px; width: 20px;"></i>
                    Reports
                </a> --}}
                <a href="{{ route('export.index') }}" class="sidebar-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
                    <i class="fas fa-download" style="margin-right: 10px; width: 20px;"></i>
                    Export
                </a>
                <a href="{{ route('admin.forms.index') }}" class="sidebar-link {{ request()->routeIs('admin.forms.*') ? 'active' : '' }}">
                    <i class="fas fa-wpforms" style="margin-right: 10px; width: 20px;"></i>
                    Forms
                </a>
                <a href="{{ route('admin.company-settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.company-settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog" style="margin-right: 10px; width: 20px;"></i>
                    Company Settings
                </a>
                <a href="{{ route('admin.system-settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.system-settings.*') ? 'active' : '' }}">
                    <i class="fas fa-server" style="margin-right: 10px; width: 20px;"></i>
                    System Settings
                </a>
                <a href="{{ route('integrations.index') }}" class="sidebar-link {{ request()->routeIs('integrations.*') ? 'active' : '' }}">
                    <i class="fas fa-plug" style="margin-right: 10px; width: 20px;"></i>
                    Integration
                </a>
                <a href="{{ route('admin.profile') }}" class="sidebar-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                    <i class="fas fa-user" style="margin-right: 10px; width: 20px;"></i>
                    Profile
                </a>
                @else
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home" style="margin-right: 10px; width: 20px;"></i>
                    Dashboard
                </a>
                @if(!auth()->user()->isTelecaller())
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users" style="margin-right: 10px; width: 20px;"></i>
                    Users
                </a>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->isCrm() || auth()->user()->isSalesManager() || auth()->user()->isSalesHead())
                <div class="sidebar-link {{ request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : '' }}" style="cursor: pointer;" onclick="toggleLeadsMenu()">
                    <i class="fas fa-filter" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                    <i class="fas fa-chevron-down ml-auto" id="leadsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="leadsSubMenu" class="pl-8" style="display: {{ request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'block' : 'none' }};">
                    <a href="{{ route('leads.index') }}" class="sidebar-link {{ request()->routeIs('leads.*') && !request()->routeIs('prospects.*') && !request()->routeIs('meetings.*') && !request()->routeIs('site-visits.*') && !request()->routeIs('closers.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Leads
                    </a>
                    <a href="{{ route('prospects.index') }}" class="sidebar-link {{ request()->routeIs('prospects.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-user-check" style="margin-right: 10px; width: 20px;"></i>
                        Prospects
                    </a>
                    <a href="{{ route('meetings.index') }}" class="sidebar-link {{ request()->routeIs('meetings.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-handshake" style="margin-right: 10px; width: 20px;"></i>
                        Meetings
                    </a>
                    <a href="{{ route('site-visits.index') }}" class="sidebar-link {{ request()->routeIs('site-visits.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 10px; width: 20px;"></i>
                        Visits
                    </a>
                    <a href="{{ route('closers.index') }}" class="sidebar-link {{ request()->routeIs('closers.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                        Closers
                    </a>
                </div>
                @else
                <a href="{{ route('leads.index') }}" class="sidebar-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <i class="fas fa-filter" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                </a>
                @endif
                <div class="sidebar-link {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'active' : '' }}" style="cursor: pointer;" onclick="toggleProjectsMenu()">
                    <i class="fas fa-project-diagram" style="margin-right: 10px; width: 20px;"></i>
                    Projects
                    <i class="fas fa-chevron-down ml-auto" id="projectsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="projectsSubMenu" class="pl-8" style="display: {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'block' : 'none' }};">
                    <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->routeIs('projects.*') && !request()->routeIs('builders.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Projects
                    </a>
                    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                    <a href="{{ route('builders.index') }}" class="sidebar-link {{ request()->routeIs('builders.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-building" style="margin-right: 10px; width: 20px;"></i>
                        Builders
                    </a>
                    @endif
                </div>
                <a href="{{ route('calls.index') }}" class="sidebar-link {{ request()->routeIs('calls.*') ? 'active' : '' }}">
                    <i class="fas fa-phone" style="margin-right: 10px; width: 20px;"></i>
                    @if(auth()->user()->isTelecaller() || auth()->user()->isSalesExecutive())
                        My Calls
                    @elseif(auth()->user()->isSalesManager() || auth()->user()->isSalesHead())
                        Team Calls
                    @else
                        All Calls
                    @endif
                </a>
                <a href="{{ route('chat.index') }}" class="sidebar-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp" style="margin-right: 10px; width: 20px;"></i>
                    WhatsApp Chat
                </a>
                @if(!auth()->user()->isAdmin() && !auth()->user()->isTelecaller() && !auth()->user()->isSalesHead())
                <a href="{{ route('lead-assignment.index') }}" class="sidebar-link {{ request()->routeIs('lead-assignment.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard" style="margin-right: 10px; width: 20px;"></i>
                    Lead Assignment
                </a>
                @endif
                @if(!auth()->user()->isAdmin() && auth()->user()->canManageUsers() && !auth()->user()->isSalesHead())
                <a href="{{ route('lead-import.index') }}" class="sidebar-link {{ request()->routeIs('lead-import.*') ? 'active' : '' }}">
                    <i class="fas fa-cloud-upload-alt" style="margin-right: 10px; width: 20px;"></i>
                    Lead Import
                </a>
                @endif
                @if(auth()->user()->isCrm() || auth()->user()->isAdmin() || auth()->user()->isSalesHead())
                <a href="{{ route('crm.verifications') }}" class="sidebar-link {{ request()->routeIs('crm.verifications') ? 'active' : '' }}">
                    <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                    Verifications
                </a>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->isCrm() || auth()->user()->isSalesManager() || auth()->user()->isSalesHead())
                <a href="{{ route('export.index') }}" class="sidebar-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
                    <i class="fas fa-download" style="margin-right: 10px; width: 20px;"></i>
                    Export
                </a>
                @endif
                @if(!auth()->user()->isAdmin() && auth()->user()->canManageUsers() && !auth()->user()->isSalesHead())
                <a href="{{ route('admin.dead-leads') }}" class="sidebar-link {{ request()->routeIs('admin.dead-leads') ? 'active' : '' }}">
                    <i class="fas fa-trash" style="margin-right: 10px; width: 20px;"></i>
                    Dead Leads / Trash
                </a>
                @endif
                @endif
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div id="mainContent" style="margin-left: 256px; flex: 1; overflow-y: auto; height: 100vh; background: #F7F6F3; transition: margin-left 0.3s ease-in-out;">
            <div class="container" style="padding: 20px; max-width: 100%; width: 100%;">
                <!-- Header -->
                <div class="header">
                    <div>
                        <h1 style="font-size: 28px; font-weight: 700; color: #063A1C;">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('page-subtitle')
                            <p style="color: #B3B5B4; font-size: 14px; margin-top: 4px;">@yield('page-subtitle')</p>
                        @endif
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        @hasSection('header-actions')
                            @yield('header-actions')
                        @endif
                        <span style="color: #B3B5B4; font-size: 14px;">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt" style="margin-right: 5px;"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                @yield('content')
            </div>
        </div>
    </div>
    
    @stack('scripts')
    <script src="{{ asset('js/branding-update.js') }}"></script>
    <script>
        // Sidebar Toggle Functionality - Make it globally accessible
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleIcon = document.getElementById('sidebarToggleIcon');
            const toggleButton = document.getElementById('sidebarToggle');
            const body = document.body;
            
            // Check if elements exist
            if (!sidebar || !toggleIcon) {
                console.error('Sidebar elements not found');
                return;
            }
            
            if (sidebar.classList.contains('sidebar-hidden')) {
                // Show sidebar
                sidebar.classList.remove('sidebar-hidden');
                body.classList.remove('sidebar-hidden');
                if (mainContent) {
                    mainContent.style.marginLeft = '256px';
                }
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
                if (toggleButton) {
                    toggleButton.style.left = '236px'; // Position on right side of sidebar header
                }
                localStorage.setItem('sidebarHidden', 'false');
            } else {
                // Hide sidebar
                sidebar.classList.add('sidebar-hidden');
                body.classList.add('sidebar-hidden');
                if (mainContent) {
                    mainContent.style.marginLeft = '0';
                }
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
                if (toggleButton) {
                    toggleButton.style.left = '20px'; // Position on left when sidebar hidden
                }
                localStorage.setItem('sidebarHidden', 'true');
            }
        };
        
        // Initialize sidebar functionality when DOM is ready
        function initSidebar() {
            // Restore sidebar state on page load
            const sidebarHidden = localStorage.getItem('sidebarHidden');
            if (sidebarHidden === 'true') {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                const toggleIcon = document.getElementById('sidebarToggleIcon');
                const body = document.body;
                
                if (sidebar && toggleIcon) {
                    sidebar.classList.add('sidebar-hidden');
                    body.classList.add('sidebar-hidden');
                    if (mainContent) {
                        mainContent.style.marginLeft = '0';
                    }
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    const toggleButton = document.getElementById('sidebarToggle');
                    if (toggleButton) {
                        toggleButton.style.left = '20px';
                    }
                }
            }
            
            // Add event listener to the button
            const toggleButton = document.getElementById('sidebarToggle');
            if (toggleButton) {
                toggleButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.toggleSidebar();
                    return false;
                });
            }
        }
        
        // Run when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSidebar);
        } else {
            // DOM is already ready
            initSidebar();
        }
        
        // Toggle Projects sub-menu
        function toggleProjectsMenu() {
            const subMenu = document.getElementById('projectsSubMenu');
            const icon = document.getElementById('projectsMenuIcon');
            if (subMenu && icon) {
                if (subMenu.style.display === 'none') {
                    subMenu.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    subMenu.style.display = 'none';
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        }
        
        function toggleLeadsMenu() {
            const subMenu = document.getElementById('leadsSubMenu');
            const icon = document.getElementById('leadsMenuIcon');
            if (subMenu && icon) {
                if (subMenu.style.display === 'none') {
                    subMenu.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    subMenu.style.display = 'none';
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        }
        
    </script>
    
    <!-- Chatbot Assistant Widget -->
    @include('components.chatbot-widget')
    
    <!-- Chatbot Assistant Script -->
    <script src="{{ asset('js/chatbot-assistant.js') }}"></script>
</body>
</html>

