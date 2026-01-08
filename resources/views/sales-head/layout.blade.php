<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sales Head - Base CRM')</title>
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
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
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
        @media (max-width: 768px) {
            .container { margin-left: 0; padding: 10px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-full w-64 bg-white border-r border-gray-200 shadow-sm z-30" style="overflow-y: auto;">
        <div style="padding: 20px; margin-bottom: 30px;">
            <h2 style="font-size: 24px; font-weight: 700; color: #063A1C; margin-bottom: 10px;">Base CRM</h2>
            <p style="font-size: 12px; color: #B3B5B4;">Sales Head</p>
        </div>
        <nav style="padding: 0 20px;">
            <a href="{{ route('sales-head.dashboard') }}" class="sidebar-link {{ request()->routeIs('sales-head.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home" style="margin-right: 10px; width: 20px;"></i>
                Dashboard
            </a>
            <a href="{{ route('users.index') }}" class="sidebar-link">
                <i class="fas fa-users" style="margin-right: 10px; width: 20px;"></i>
                All Users
            </a>
            <a href="{{ route('leads.index') }}" class="sidebar-link">
                <i class="fas fa-user-friends" style="margin-right: 10px; width: 20px;"></i>
                All Leads
            </a>
            <a href="{{ route('crm.verifications') }}" class="sidebar-link">
                <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                Verifications
            </a>
            <a href="{{ route('admin.targets.index') }}" class="sidebar-link">
                <i class="fas fa-bullseye" style="margin-right: 10px; width: 20px;"></i>
                Targets
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div style="margin-left: 256px; min-height: 100vh;">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1 style="font-size: 28px; font-weight: 700; color: #063A1C;">@yield('page-title', 'Sales Head Dashboard')</h1>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
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

    @stack('scripts')
    
    <!-- Chatbot Assistant Widget -->
    @include('components.chatbot-widget')
    
    <!-- Chatbot Assistant Script -->
    <script src="{{ asset('js/chatbot-assistant.js') }}"></script>
</body>
</html>
