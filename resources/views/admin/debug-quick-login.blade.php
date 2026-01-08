<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quick Login Debug - Base CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            border-bottom: 3px solid #205A44;
            padding-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .debug-section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .debug-section h2 {
            color: #205A44;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .debug-item {
            padding: 10px;
            margin-bottom: 10px;
            background: white;
            border-left: 4px solid #205A44;
            border-radius: 3px;
        }
        
        .debug-item.success {
            border-left-color: #28a745;
        }
        
        .debug-item.error {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        
        .debug-item.warning {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        
        .debug-label {
            font-weight: 600;
            color: #333;
            display: inline-block;
            min-width: 200px;
        }
        
        .debug-value {
            color: #666;
        }
        
        .debug-value.true {
            color: #28a745;
            font-weight: 600;
        }
        
        .debug-value.false {
            color: #dc3545;
            font-weight: 600;
        }
        
        .test-form {
            background: #e8f5e9;
            border: 2px solid #4caf50;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .test-form h2 {
            color: #2e7d32;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #205A44, #063A1C);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #063A1C, #205A44);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .json-view {
            background: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .error-trace {
            background: #fff5f5;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-bug"></i> Quick Login Debug Tool</h1>
        <p class="subtitle">This page helps debug Quick Login issues during maintenance mode</p>
        
        <!-- Test Form -->
        <div class="test-form">
            <h2><i class="fas fa-flask"></i> Test Quick Login</h2>
            <form id="testForm">
                <div class="form-group">
                    <label for="user_id">User ID to Test:</label>
                    <input type="number" id="user_id" name="user_id" value="{{ $userId ?? '' }}" placeholder="Enter user ID">
                </div>
                <button type="button" onclick="testQuickLogin()" class="btn btn-primary">
                    <i class="fas fa-search"></i> Test Quick Login
                </button>
                <button type="button" onclick="attemptLogin()" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Attempt Login
                </button>
            </form>
            <div id="testResult" style="margin-top: 20px;"></div>
        </div>
        
        <!-- Debug Information -->
        <div class="debug-section">
            <h2><i class="fas fa-info-circle"></i> Debug Information</h2>
            
            @if(isset($debug))
                @foreach($debug as $key => $value)
                    <div class="debug-item {{ is_bool($value) ? ($value ? 'success' : 'error') : '' }}">
                        <span class="debug-label">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                        <span class="debug-value {{ is_bool($value) ? ($value ? 'true' : 'false') : '' }}">
                            @if(is_bool($value))
                                {{ $value ? 'TRUE' : 'FALSE' }}
                            @elseif(is_array($value))
                                <pre style="display: inline; background: #f0f0f0; padding: 5px; border-radius: 3px;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                            @elseif(is_null($value))
                                <span style="color: #999;">NULL</span>
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                @endforeach
            @else
                <div class="debug-item warning">
                    <span class="debug-label">Status:</span>
                    <span class="debug-value">No debug data available. Enter a User ID and click "Test Quick Login"</span>
                </div>
            @endif
        </div>
        
        <!-- Full Debug JSON -->
        @if(isset($debug))
        <div class="debug-section">
            <h2><i class="fas fa-code"></i> Full Debug JSON</h2>
            <div class="json-view">{{ json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</div>
        </div>
        @endif
    </div>
    
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function testQuickLogin() {
            const userId = document.getElementById('user_id').value;
            if (!userId) {
                alert('Please enter a User ID');
                return;
            }
            
            window.location.href = '{{ route("admin.debug.test-quick-login") }}?user_id=' + userId;
        }
        
        function attemptLogin() {
            const userId = document.getElementById('user_id').value;
            if (!userId) {
                alert('Please enter a User ID');
                return;
            }
            
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div class="alert alert-info">Attempting login... Please wait.</div>';
            
            fetch(`/admin/debug/quick-login/${userId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>Success!</strong> ${data.message}<br>
                            <strong>Redirecting to:</strong> ${data.redirect}
                            <pre class="error-trace">${JSON.stringify(data.debug, null, 2)}</pre>
                        </div>
                    `;
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>Error:</strong> ${data.error || 'Unknown error'}<br>
                            <pre class="error-trace">${JSON.stringify(data.debug || data, null, 2)}</pre>
                            ${data.trace ? `<pre class="error-trace">Trace:\n${data.trace}</pre>` : ''}
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Request Error:</strong> ${error.message}
                    </div>
                `;
            });
        }
    </script>
</body>
</html>
