<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Realtor CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            height: 100vh;
        }

        /* Left Section - Gradient with Analytics */
        .left-section {
            flex: 0 0 40%;
            background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 50px;
            color: white;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .analytics-widgets {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            width: 100%;
            max-width: 500px;
            margin-bottom: 40px;
            z-index: 1;
        }

        .widget-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .widget-card.large {
            grid-column: 1 / -1;
        }

        .widget-title {
            font-size: 14px;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 16px;
        }

        .chart-controls {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .chart-btn {
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .chart-btn.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            height: 120px;
        }

        .bar {
            flex: 1;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 4px 4px 0 0;
            min-height: 20px;
            transition: all 0.3s;
        }

        .bar:nth-child(1) { height: 60%; }
        .bar:nth-child(2) { height: 80%; }
        .bar:nth-child(3) { height: 45%; }
        .bar:nth-child(4) { height: 90%; }
        .bar:nth-child(5) { height: 70%; }
        .bar:nth-child(6) { height: 55%; }
        .bar:nth-child(7) { height: 75%; }

        .bar-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 11px;
            opacity: 0.8;
        }

        .progress-circle {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            position: relative;
        }

        .circle-bg {
            fill: none;
            stroke: rgba(255, 255, 255, 0.2);
            stroke-width: 8;
        }

        .circle-progress {
            fill: none;
            stroke: white;
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 251.2;
            stroke-dashoffset: 145.7;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset 0.5s;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: 700;
        }

        .left-content {
            z-index: 1;
            text-align: center;
        }

        .headline {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .subtext {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 500px;
        }

        /* Right Section - Login Form */
        .right-section {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow-y: auto;
        }

        .login-form-container {
            width: 100%;
            max-width: 420px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(6, 58, 28, 0.3);
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #063A1C;
            margin-bottom: 8px;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            color: #063A1C;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            font-size: 14px;
            color: #B3B5B4;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #2d3748;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E5DED4;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #205A44;
            box-shadow: 0 0 0 3px rgba(32, 90, 68, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #205A44;
        }

        .btn-signin {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(6, 58, 28, 0.4);
        }

        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 58, 28, 0.5);
        }

        .btn-signin:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c33;
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .login-container {
                flex-direction: column;
            }

            .left-section {
                flex: 0 0 auto;
                min-height: 40vh;
                padding: 40px 30px;
            }

            .headline {
                font-size: 28px;
            }

            .analytics-widgets {
                max-width: 100%;
            }

            .right-section {
                flex: 1;
            }
        }

        @media (max-width: 640px) {
            .left-section {
                padding: 30px 20px;
            }

            .headline {
                font-size: 24px;
            }

            .subtext {
                font-size: 14px;
            }

            .analytics-widgets {
                grid-template-columns: 1fr;
            }

            .widget-card.large {
                grid-column: 1;
            }

            .right-section {
                padding: 20px;
            }

            .welcome-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Section - Gradient with Analytics -->
        <div class="left-section">
            <div class="analytics-widgets">
                <div class="widget-card large">
                    <div class="widget-title">Weekly Sales</div>
                    <div class="chart-controls">
                        <button class="chart-btn active">Weekly</button>
                        <button class="chart-btn">Monthly</button>
                        <button class="chart-btn">Yearly</button>
                    </div>
                    <div class="bar-chart">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                    <div class="bar-labels">
                        <span>MON</span>
                        <span>TUE</span>
                        <span>WED</span>
                        <span>THU</span>
                        <span>FRI</span>
                        <span>SAT</span>
                        <span>SUN</span>
                    </div>
                </div>
                <div class="widget-card">
                    <div class="widget-title">Total Performance</div>
                    <div class="progress-circle">
                        <svg width="100" height="100">
                            <circle class="circle-bg" cx="50" cy="50" r="40"></circle>
                            <circle class="circle-progress" cx="50" cy="50" r="40"></circle>
                        </svg>
                        <div class="progress-text">42%</div>
                    </div>
                </div>
            </div>

            <div class="left-content">
                <h1 class="headline">Effortlessly manage your real estate business</h1>
                <p class="subtext">Manage leads, properties, clients and deals all in one powerful Realtor CRM.</p>
            </div>
        </div>

        <!-- Right Section - Login Form -->
        <div class="right-section">
            <div class="login-form-container">
                <div class="logo-section">
                    <div class="logo-icon">B</div>
                    <div class="logo-text">Brickly CRM</div>
                </div>

                <h2 class="welcome-title">Welcome Back</h2>
                <p class="welcome-subtitle">Login to manage your real estate operations</p>

                @php
                    use App\Models\SystemSettings;
                    $isMaintenanceMode = SystemSettings::isMaintenanceMode();
                @endphp

                @if($isMaintenanceMode)
                    <div class="maintenance-warning" style="background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 20px; margin-right: 12px;"></i>
                        <div>
                            <strong style="display: block; margin-bottom: 4px;">System Under Maintenance</strong>
                            <span style="font-size: 14px;">{{ SystemSettings::get('maintenance_message', 'System is under maintenance. Only admin can login.') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="error-message">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus
                                placeholder="Enter your email"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                placeholder="Enter your password"
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 14px;">
                        <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="remember" style="width: auto; margin-right: 8px; cursor: pointer;">
                            <span style="color: #4a5568;">Remember me</span>
                        </label>
                        <a href="#" style="color: #205A44; text-decoration: none;">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-signin">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Setup CSRF token for all AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            // Update form CSRF token
            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput) {
                csrfInput.value = csrfToken;
            }
        }

        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'password') {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });

        // Refresh CSRF token before form submission to prevent 419 errors
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', function(e) {
            // Ensure CSRF token is up to date
            const csrfInput = form.querySelector('input[name="_token"]');
            if (csrfInput && csrfToken) {
                csrfInput.value = csrfToken;
            }
            
            // Allow normal form submission
            // If 419 error occurs, Laravel will show error page which user can refresh
        });

        // Chart button interactions
        document.querySelectorAll('.chart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>

