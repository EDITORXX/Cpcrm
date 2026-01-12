<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Login - All Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-card {
            transition: all 0.3s ease;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-admin { background: #fef3c7; color: #92400e; }
        .role-crm { background: #dbeafe; color: #1e40af; }
        .role-sales_manager { background: #d1fae5; color: #065f46; }
        .role-telecaller { background: #e0e7ff; color: #3730a3; }
        .role-sales_executive { background: #fce7f3; color: #9f1239; }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-bolt text-yellow-500"></i> Quick Login - All Users
                </h1>
                <p class="text-gray-600">Click on any user card to login instantly</p>
            </div>

            <!-- Users Grid -->
            @foreach($users as $roleName => $roleUsers)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-users mr-2"></i>
                    {{ $roleName }} ({{ $roleUsers->count() }})
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($roleUsers as $user)
                    <div class="user-card bg-white rounded-lg shadow p-4 cursor-pointer border-2 border-transparent hover:border-blue-500" 
                         onclick="quickLogin({{ $user->id }}, this)">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-600 mb-2">{{ $user->email }}</p>
                            </div>
                            <div class="role-badge role-{{ $user->role->slug ?? 'unknown' }}">
                                {{ $user->role->slug ?? 'N/A' }}
                            </div>
                        </div>
                        @if($user->phone)
                        <p class="text-xs text-gray-500 mb-2">
                            <i class="fas fa-phone mr-1"></i> {{ $user->phone }}
                        </p>
                        @endif
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xs text-gray-500">ID: {{ $user->id }}</span>
                            <button class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-sign-in-alt mr-1"></i> Login
                            </button>
                        </div>
                        <div class="login-status mt-2 hidden">
                            <div class="flex items-center text-sm">
                                <div class="loading mr-2"></div>
                                <span class="text-blue-600">Logging in...</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <!-- Empty State -->
            @if($users->isEmpty())
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Users Found</h3>
                <p class="text-gray-500">No active users available for quick login.</p>
            </div>
            @endif
        </div>
    </div>

    <script>
        async function quickLogin(userId, cardElement) {
            const statusDiv = cardElement.querySelector('.login-status');
            const button = cardElement.querySelector('button');
            
            // Show loading state
            statusDiv.classList.remove('hidden');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Logging in...';
            
            try {
                const response = await fetch(`/quick-login/${userId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success && data.redirect) {
                    // Show success message briefly
                    statusDiv.innerHTML = '<div class="text-green-600 text-sm"><i class="fas fa-check-circle mr-1"></i> Success! Redirecting...</div>';
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 500);
                } else {
                    // Show error
                    statusDiv.innerHTML = `<div class="text-red-600 text-sm"><i class="fas fa-exclamation-circle mr-1"></i> ${data.error || 'Login failed'}</div>`;
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-sign-in-alt mr-1"></i> Login';
                    
                    // Hide error after 3 seconds
                    setTimeout(() => {
                        statusDiv.classList.add('hidden');
                    }, 3000);
                }
            } catch (error) {
                console.error('Quick login error:', error);
                statusDiv.innerHTML = `<div class="text-red-600 text-sm"><i class="fas fa-exclamation-circle mr-1"></i> Error: ${error.message}</div>`;
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-sign-in-alt mr-1"></i> Login';
                
                setTimeout(() => {
                    statusDiv.classList.add('hidden');
                }, 3000);
            }
        }

        // Add keyboard shortcut (Ctrl+K or Cmd+K) to focus search
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                // Could add search functionality here
            }
        });
    </script>
</body>
</html>
