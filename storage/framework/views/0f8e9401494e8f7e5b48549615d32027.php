<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Base CRM'); ?></title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="api-token" content="<?php echo e(session('api_token') ?? (auth()->check() ? auth()->user()->createToken('web-token')->plainTextToken : '')); ?>">
    <meta name="user-id" content="<?php echo e(auth()->check() ? auth()->user()->id : ''); ?>">
    <meta name="pusher-key" content="<?php echo e(config('broadcasting.connections.pusher.key')); ?>">
    <meta name="pusher-cluster" content="<?php echo e(config('broadcasting.connections.pusher.options.cluster', 'mt1')); ?>">
    
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
            --primary-color: <?php echo e(primary_color()); ?>;
            --secondary-color: <?php echo e(secondary_color()); ?>;
            --accent-color: <?php echo e(accent_color()); ?>;
            --gradient-start: <?php echo e(gradient_start_color()); ?>;
            --gradient-end: <?php echo e(gradient_end_color()); ?>;
            --text-color: <?php echo e(text_color()); ?>;
            --link-color: <?php echo e(link_color()); ?>;
            --background-color: <?php echo e(background_color()); ?>;
            --text-primary: <?php echo e(text_color()); ?>;
            --text-secondary: <?php echo e(link_color()); ?>;
            --text-muted: #B3B5B4;
            --border-color: <?php echo e(primary_color()); ?>;
            --avatar-bg: <?php echo e(gradient_start_color()); ?>;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        .container { max-width: 100%; margin: 0 auto; padding: 20px; width: 100%; box-sizing: border-box; }
        .header { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.3s; }
        
        /* Branded Button Classes */
        .btn-brand-primary, .btn-brand-gradient {
            <?php if(use_gradient()): ?>
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            <?php else: ?>
                background-color: var(--primary-color);
            <?php endif; ?>
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-brand-primary:hover, .btn-brand-gradient:hover {
            <?php if(use_gradient()): ?>
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color));
            <?php else: ?>
                background-color: var(--secondary-color);
            <?php endif; ?>
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
            <?php if(use_gradient()): ?>
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            <?php else: ?>
                background-color: var(--primary-color) !important;
            <?php endif; ?>
            color: white !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary:hover, .btn-success:hover, .btn-secondary:hover, .btn-warning:hover {
            <?php if(use_gradient()): ?>
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
            <?php else: ?>
                background-color: var(--secondary-color) !important;
            <?php endif; ?>
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
            <?php if(use_gradient()): ?>
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            <?php else: ?>
                background-color: var(--primary-color) !important;
            <?php endif; ?>
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
            <?php if(use_gradient()): ?>
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            <?php else: ?>
                background-color: var(--primary-color) !important;
            <?php endif; ?>
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
            <?php if(use_gradient()): ?>
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
            <?php else: ?>
                background-color: var(--secondary-color) !important;
            <?php endif; ?>
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
        <?php echo branded_css(); ?>

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
    
    <?php echo $__env->yieldPushContent('styles'); ?>
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
                    <?php if(auth()->user()->isAdmin()): ?>
                        Admin
                    <?php elseif(auth()->user()->isCrm()): ?>
                        CRM
                    <?php elseif(auth()->user()->isSalesHead()): ?>
                        Sales Head
                    <?php elseif(auth()->user()->isSalesManager()): ?>
                        Sales Manager
                    <?php elseif(auth()->user()->isTelecaller()): ?>
                        Telecaller
                    <?php else: ?>
                        <?php echo e(auth()->user()->role->name ?? 'User'); ?>

                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Navigation -->
            <nav style="padding: 0 20px;">
                <?php if(auth()->user()->isAdmin()): ?>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                    <i class="fas fa-home" style="margin-right: 10px; width: 20px;"></i>
                    Dashboard
                </a>
                <a href="<?php echo e(route('users.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
                    <i class="fas fa-users" style="margin-right: 10px; width: 20px;"></i>
                    All Users
                </a>
                <div class="sidebar-link <?php echo e(request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : ''); ?>" style="cursor: pointer;" onclick="toggleLeadsMenu()">
                    <i class="fas fa-user-friends" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                    <i class="fas fa-chevron-down ml-auto" id="leadsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="leadsSubMenu" class="pl-8" style="display: <?php echo e(request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'block' : 'none'); ?>;">
                    <a href="<?php echo e(route('leads.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('leads.*') && !request()->routeIs('prospects.*') && !request()->routeIs('meetings.*') && !request()->routeIs('site-visits.*') && !request()->routeIs('closers.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Leads
                    </a>
                    <a href="<?php echo e(route('prospects.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('prospects.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-user-check" style="margin-right: 10px; width: 20px;"></i>
                        Prospects
                    </a>
                    <a href="<?php echo e(route('meetings.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('meetings.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-handshake" style="margin-right: 10px; width: 20px;"></i>
                        Meetings
                    </a>
                    <a href="<?php echo e(route('site-visits.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('site-visits.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 10px; width: 20px;"></i>
                        Visits
                    </a>
                    <a href="<?php echo e(route('closers.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('closers.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                        Closers
                    </a>
                </div>
                <div class="sidebar-link <?php echo e(request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'active' : ''); ?>" style="cursor: pointer;" onclick="toggleProjectsMenu()">
                    <i class="fas fa-project-diagram" style="margin-right: 10px; width: 20px;"></i>
                    Projects
                    <i class="fas fa-chevron-down ml-auto" id="projectsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="projectsSubMenu" class="pl-8" style="display: <?php echo e(request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'block' : 'none'); ?>;">
                    <a href="<?php echo e(route('projects.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('projects.*') && !request()->routeIs('builders.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Projects
                    </a>
                    <a href="<?php echo e(route('builders.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('builders.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-building" style="margin-right: 10px; width: 20px;"></i>
                        Builders
                    </a>
                </div>
                <a href="<?php echo e(route('calls.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('calls.*') ? 'active' : ''); ?>">
                    <i class="fas fa-phone" style="margin-right: 10px; width: 20px;"></i>
                    All Calls
                </a>
                <a href="<?php echo e(route('chat.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('chat.*') ? 'active' : ''); ?>">
                    <i class="fab fa-whatsapp" style="margin-right: 10px; width: 20px;"></i>
                    WhatsApp Chat
                </a>
                
                
                <a href="<?php echo e(route('export.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('export.*') ? 'active' : ''); ?>">
                    <i class="fas fa-download" style="margin-right: 10px; width: 20px;"></i>
                    Export
                </a>
                <a href="<?php echo e(route('admin.forms.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.forms.*') ? 'active' : ''); ?>">
                    <i class="fas fa-wpforms" style="margin-right: 10px; width: 20px;"></i>
                    Forms
                </a>
                <a href="<?php echo e(route('admin.company-settings.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.company-settings.*') ? 'active' : ''); ?>">
                    <i class="fas fa-cog" style="margin-right: 10px; width: 20px;"></i>
                    Company Settings
                </a>
                <a href="<?php echo e(route('admin.system-settings.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.system-settings.*') ? 'active' : ''); ?>">
                    <i class="fas fa-server" style="margin-right: 10px; width: 20px;"></i>
                    System Settings
                </a>
                <a href="<?php echo e(route('integrations.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('integrations.*') ? 'active' : ''); ?>">
                    <i class="fas fa-plug" style="margin-right: 10px; width: 20px;"></i>
                    Integration
                </a>
                <a href="<?php echo e(route('admin.profile')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.profile') ? 'active' : ''); ?>">
                    <i class="fas fa-user" style="margin-right: 10px; width: 20px;"></i>
                    Profile
                </a>
                <?php else: ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    <i class="fas fa-home" style="margin-right: 10px; width: 20px;"></i>
                    Dashboard
                </a>
                <?php if(!auth()->user()->isTelecaller()): ?>
                <a href="<?php echo e(route('users.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
                    <i class="fas fa-users" style="margin-right: 10px; width: 20px;"></i>
                    Users
                </a>
                <?php endif; ?>
                <?php if(auth()->user()->isAdmin() || auth()->user()->isCrm() || auth()->user()->isSalesManager() || auth()->user()->isSalesHead()): ?>
                <div class="sidebar-link <?php echo e(request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : ''); ?>" style="cursor: pointer;" onclick="toggleLeadsMenu()">
                    <i class="fas fa-filter" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                    <i class="fas fa-chevron-down ml-auto" id="leadsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="leadsSubMenu" class="pl-8" style="display: <?php echo e(request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'block' : 'none'); ?>;">
                    <a href="<?php echo e(route('leads.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('leads.*') && !request()->routeIs('prospects.*') && !request()->routeIs('meetings.*') && !request()->routeIs('site-visits.*') && !request()->routeIs('closers.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Leads
                    </a>
                    <a href="<?php echo e(route('prospects.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('prospects.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-user-check" style="margin-right: 10px; width: 20px;"></i>
                        Prospects
                    </a>
                    <a href="<?php echo e(route('meetings.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('meetings.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-handshake" style="margin-right: 10px; width: 20px;"></i>
                        Meetings
                    </a>
                    <a href="<?php echo e(route('site-visits.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('site-visits.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 10px; width: 20px;"></i>
                        Visits
                    </a>
                    <a href="<?php echo e(route('closers.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('closers.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                        Closers
                    </a>
                </div>
                <?php else: ?>
                <a href="<?php echo e(route('leads.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('leads.*') ? 'active' : ''); ?>">
                    <i class="fas fa-filter" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                </a>
                <?php endif; ?>
                <div class="sidebar-link <?php echo e(request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'active' : ''); ?>" style="cursor: pointer;" onclick="toggleProjectsMenu()">
                    <i class="fas fa-project-diagram" style="margin-right: 10px; width: 20px;"></i>
                    Projects
                    <i class="fas fa-chevron-down ml-auto" id="projectsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="projectsSubMenu" class="pl-8" style="display: <?php echo e(request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'block' : 'none'); ?>;">
                    <a href="<?php echo e(route('projects.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('projects.*') && !request()->routeIs('builders.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Projects
                    </a>
                    <?php if(auth()->user()->isAdmin() || auth()->user()->isCrm()): ?>
                    <a href="<?php echo e(route('builders.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('builders.*') ? 'active' : ''); ?>" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-building" style="margin-right: 10px; width: 20px;"></i>
                        Builders
                    </a>
                    <?php endif; ?>
                </div>
                <a href="<?php echo e(route('calls.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('calls.*') ? 'active' : ''); ?>">
                    <i class="fas fa-phone" style="margin-right: 10px; width: 20px;"></i>
                    <?php if(auth()->user()->isTelecaller() || auth()->user()->isSalesExecutive()): ?>
                        My Calls
                    <?php elseif(auth()->user()->isSalesManager() || auth()->user()->isSalesHead()): ?>
                        Team Calls
                    <?php else: ?>
                        All Calls
                    <?php endif; ?>
                </a>
                <a href="<?php echo e(route('chat.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('chat.*') ? 'active' : ''); ?>">
                    <i class="fab fa-whatsapp" style="margin-right: 10px; width: 20px;"></i>
                    WhatsApp Chat
                </a>
                <?php if(!auth()->user()->isAdmin() && !auth()->user()->isTelecaller() && !auth()->user()->isSalesHead()): ?>
                <a href="<?php echo e(route('lead-assignment.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('lead-assignment.*') ? 'active' : ''); ?>">
                    <i class="fas fa-clipboard" style="margin-right: 10px; width: 20px;"></i>
                    Lead Assignment
                </a>
                <?php endif; ?>
                <?php if(!auth()->user()->isAdmin() && auth()->user()->canManageUsers() && !auth()->user()->isSalesHead()): ?>
                <a href="<?php echo e(route('lead-import.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('lead-import.*') ? 'active' : ''); ?>">
                    <i class="fas fa-cloud-upload-alt" style="margin-right: 10px; width: 20px;"></i>
                    Lead Import
                </a>
                <?php endif; ?>
                <?php if(auth()->user()->isCrm() || auth()->user()->isAdmin() || auth()->user()->isSalesHead()): ?>
                <a href="<?php echo e(route('crm.verifications')); ?>" class="sidebar-link <?php echo e(request()->routeIs('crm.verifications') ? 'active' : ''); ?>">
                    <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                    Verifications
                </a>
                <?php endif; ?>
                <?php if(auth()->user()->isAdmin() || auth()->user()->isCrm() || auth()->user()->isSalesManager() || auth()->user()->isSalesHead()): ?>
                <a href="<?php echo e(route('export.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('export.*') ? 'active' : ''); ?>">
                    <i class="fas fa-download" style="margin-right: 10px; width: 20px;"></i>
                    Export
                </a>
                <?php endif; ?>
                <?php if(!auth()->user()->isAdmin() && auth()->user()->canManageUsers() && !auth()->user()->isSalesHead()): ?>
                <a href="<?php echo e(route('admin.dead-leads')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.dead-leads') ? 'active' : ''); ?>">
                    <i class="fas fa-trash" style="margin-right: 10px; width: 20px;"></i>
                    Dead Leads / Trash
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div id="mainContent" style="margin-left: 256px; flex: 1; overflow-y: auto; height: 100vh; background: #F7F6F3; transition: margin-left 0.3s ease-in-out;">
            <div class="container" style="padding: 20px; max-width: 100%; width: 100%;">
                <!-- Header -->
                <div class="header">
                    <div>
                        <h1 style="font-size: 28px; font-weight: 700; color: #063A1C;"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h1>
                        <?php if (! empty(trim($__env->yieldContent('page-subtitle')))): ?>
                            <p style="color: #B3B5B4; font-size: 14px; margin-top: 4px;"><?php echo $__env->yieldContent('page-subtitle'); ?></p>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <?php if (! empty(trim($__env->yieldContent('header-actions')))): ?>
                            <?php echo $__env->yieldContent('header-actions'); ?>
                        <?php endif; ?>
                        <span style="color: #B3B5B4; font-size: 14px;"><?php echo e(auth()->user()->name); ?></span>
                        <form action="<?php echo e(route('logout')); ?>" method="POST" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt" style="margin-right: 5px;"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script src="<?php echo e(asset('js/branding-update.js')); ?>"></script>
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
    <?php echo $__env->make('components.chatbot-widget', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <!-- Chatbot Assistant Script -->
    <script src="<?php echo e(asset('js/chatbot-assistant.js')); ?>"></script>
</body>
</html>

<?php /**PATH C:\Users\vivek\Pictures\Laravel crm fully functional\resources\views/layouts/app.blade.php ENDPATH**/ ?>