@extends('layouts.app')

@section('title', 'System Settings - Base CRM')
@section('page-title', 'System Settings')

@section('header-actions')
    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
    </a>
@endsection

@push('styles')
<style>
    .section-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #E5DED4;
        padding: 24px;
        margin-bottom: 24px;
    }
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E5DED4;
        display: flex;
        align-items: center;
    }
    .section-title i {
        margin-right: 10px;
        color: var(--gradient-start);
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
    }
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    .btn-primary {
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-secondary:hover {
        background: #5a6268;
    }
    .file-upload-area {
        border: 2px dashed #E5DED4;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background: #F7F6F3;
        cursor: pointer;
        transition: all 0.3s;
    }
    .file-upload-area:hover {
        border-color: var(--gradient-start);
        background: #fff;
    }
    .file-upload-area.dragover {
        border-color: var(--gradient-start);
        background: #f0f9ff;
    }
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge.active {
        background: #d1fae5;
        color: #065f46;
    }
    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    .command-output {
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 16px;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        max-height: 400px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #86efac;
    }
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Success/Error Messages -->
    <div id="message-container" class="mb-6" style="display: none;">
        <div id="message-alert" class="alert"></div>
    </div>

    <!-- Maintenance Mode Section -->
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-tools"></i> Maintenance Mode
        </div>
        
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">System Maintenance</h3>
                <p class="text-sm text-gray-600">
                    When enabled, all users (except admin) will be logged out and unable to access the system.
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="status-badge {{ $maintenanceMode ? 'active' : 'inactive' }}">
                    {{ $maintenanceMode ? 'ENABLED' : 'DISABLED' }}
                </span>
                <label class="toggle-switch">
                    <input type="checkbox" id="maintenance-toggle" {{ $maintenanceMode ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>
        
        <div id="maintenance-message-section" class="mt-4" style="{{ !$maintenanceMode ? 'display: none;' : '' }}">
            <label for="maintenance-message" class="block text-sm font-medium text-gray-700 mb-2">
                Maintenance Message
            </label>
            <textarea id="maintenance-message" rows="3" 
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand"
                      placeholder="Enter a custom maintenance message...">{{ $maintenanceMessage }}</textarea>
        </div>
        
        <div class="mt-4">
            <button onclick="toggleMaintenanceMode()" class="btn-primary" id="maintenance-btn">
                {{ $maintenanceMode ? 'Disable' : 'Enable' }} Maintenance Mode
            </button>
        </div>
    </div>

    <!-- Database Settings Section -->
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-database"></i> Database Settings
        </div>
        
        <p class="text-sm text-gray-600 mb-4">
            Update your database connection settings. Changes will be saved to .env file.
        </p>
        
        <form id="database-settings-form" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Host *</label>
                    <input type="text" id="db-host" name="host" value="{{ env('DB_HOST', '127.0.0.1') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Port *</label>
                    <input type="number" id="db-port" name="port" value="{{ env('DB_PORT', '3306') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Database Name *</label>
                <input type="text" id="db-database" name="database" value="{{ env('DB_DATABASE', '') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Username *</label>
                    <input type="text" id="db-username" name="username" value="{{ env('DB_USERNAME', '') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Password</label>
                    <input type="password" id="db-password" name="password" value="{{ env('DB_PASSWORD', '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand"
                        placeholder="Leave empty to keep current">
                </div>
            </div>
            <div id="db-test-result" class="hidden"></div>
            <div class="flex space-x-4">
                <button type="button" onclick="testDatabaseConnection()" class="btn-secondary">
                    <i class="fas fa-plug mr-2"></i> Test Connection
                </button>
                <button type="button" onclick="updateDatabaseSettings()" class="btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Database Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Environment Variables Section -->
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-cog"></i> Environment Variables
        </div>
        
        <p class="text-sm text-gray-600 mb-4">
            Manage environment variables from .env file. Be careful when editing these settings.
        </p>
        
        <div class="mb-4">
            <button onclick="loadEnvSettings()" class="btn-secondary">
                <i class="fas fa-sync mr-2"></i> Load Environment Variables
            </button>
        </div>
        
        <div id="env-settings-container" class="hidden">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Warning:</strong> Changing environment variables can affect system functionality. Make sure you know what you're doing.
                </p>
            </div>
            
            <div id="env-settings-list" class="space-y-3 max-h-96 overflow-y-auto mb-4"></div>
            
            <div class="flex space-x-4">
                <button onclick="updateEnvSettings()" class="btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Environment Variables
                </button>
                <button onclick="document.getElementById('env-settings-container').classList.add('hidden')" class="btn-secondary">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- File Upload & Deploy Section -->
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-cloud-upload-alt"></i> File Upload & Deploy
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Upload Files (ZIP or Regular Files)
            </label>
            <div class="file-upload-area" id="file-upload-area" onclick="document.getElementById('file-input').click()">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 mb-2">Click to upload or drag and drop</p>
                <p class="text-sm text-gray-500">ZIP files, PHP files, JavaScript, CSS, etc. (Max 100MB per file)</p>
            </div>
            <input type="file" id="file-input" multiple accept=".zip,.php,.js,.css,.json,.env" style="display: none;">
            <div id="uploaded-files-list" class="mt-4 space-y-2"></div>
        </div>
        
        <div class="mb-6">
            <label for="deploy-destination" class="block text-sm font-medium text-gray-700 mb-2">
                Deploy Destination
            </label>
            <select id="deploy-destination" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand">
                <option value="app">app/ (Application files)</option>
                <option value="resources">resources/ (Views, assets)</option>
                <option value="public">public/ (Public assets)</option>
                <option value="database">database/ (Migrations, seeds)</option>
                <option value="config">config/ (Configuration files)</option>
                <option value="root">Root directory</option>
            </select>
        </div>
        
        <div class="flex space-x-4">
            <button onclick="uploadFiles()" class="btn-primary" id="upload-btn">
                <i class="fas fa-upload mr-2"></i> Upload Files
            </button>
            <button onclick="deployFiles()" class="btn-secondary" id="deploy-btn" disabled>
                <i class="fas fa-rocket mr-2"></i> Deploy Files
            </button>
        </div>
    </div>

    <!-- Database Migrations Section -->
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-database"></i> Database Migrations
        </div>
        
        <p class="text-sm text-gray-600 mb-4">
            Run pending database migrations. This will update your database schema.
        </p>
        
        <button onclick="runMigrations()" class="btn-primary" id="migrate-btn">
            <i class="fas fa-play mr-2"></i> Run Migrations
        </button>
        
        <div id="migration-output" class="mt-4" style="display: none;">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Migration Output:</h4>
            <div class="command-output" id="migration-output-content"></div>
        </div>
    </div>

    <!-- Artisan Commands Section -->
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-terminal"></i> Artisan Commands
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <button onclick="runCommand('migrate')" class="btn-secondary">
                <i class="fas fa-database mr-2"></i> Migrate
            </button>
            <button onclick="runCommand('optimize')" class="btn-secondary">
                <i class="fas fa-bolt mr-2"></i> Optimize
            </button>
            <button onclick="runCommand('clear-cache')" class="btn-secondary">
                <i class="fas fa-broom mr-2"></i> Clear Cache
            </button>
            <button onclick="runCommand('config-cache')" class="btn-secondary">
                <i class="fas fa-cog mr-2"></i> Cache Config
            </button>
            <button onclick="runCommand('route-cache')" class="btn-secondary">
                <i class="fas fa-route mr-2"></i> Cache Routes
            </button>
            <button onclick="runCommand('view-cache')" class="btn-secondary">
                <i class="fas fa-eye mr-2"></i> Cache Views
            </button>
        </div>
        
        <div id="command-output" class="mt-4" style="display: none;">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Command Output:</h4>
            <div class="command-output" id="command-output-content"></div>
        </div>
    </div>
</div>

<script>
let uploadedFiles = [];
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Maintenance Mode Toggle
document.getElementById('maintenance-toggle').addEventListener('change', function() {
    document.getElementById('maintenance-message-section').style.display = this.checked ? 'block' : 'none';
});

function toggleMaintenanceMode() {
    const enabled = document.getElementById('maintenance-toggle').checked;
    const message = document.getElementById('maintenance-message').value;
    
    const btn = document.getElementById('maintenance-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
    
    fetch('{{ route("admin.system-settings.maintenance.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            enabled: enabled,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            // Update status badge
            const badge = document.querySelector('.status-badge');
            if (data.maintenance_mode) {
                badge.classList.remove('inactive');
                badge.classList.add('active');
                badge.textContent = 'ENABLED';
                btn.textContent = 'Disable Maintenance Mode';
            } else {
                badge.classList.remove('active');
                badge.classList.add('inactive');
                badge.textContent = 'DISABLED';
                btn.textContent = 'Enable Maintenance Mode';
            }
        } else {
            showMessage(data.message || 'Error toggling maintenance mode', 'error');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
    });
}

// File Upload
const fileInput = document.getElementById('file-input');
const fileUploadArea = document.getElementById('file-upload-area');

fileInput.addEventListener('change', handleFiles);
fileUploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileUploadArea.classList.add('dragover');
});
fileUploadArea.addEventListener('dragleave', () => {
    fileUploadArea.classList.remove('dragover');
});
fileUploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    fileUploadArea.classList.remove('dragover');
    fileInput.files = e.dataTransfer.files;
    handleFiles();
});

function handleFiles() {
    const files = Array.from(fileInput.files);
    if (files.length === 0) return;
    
    // Show file list
    const list = document.getElementById('uploaded-files-list');
    list.innerHTML = '<p class="text-sm font-medium text-gray-700 mb-2">Selected Files:</p>' +
        files.map(f => `<div class="text-sm text-gray-600 p-2 bg-gray-50 rounded">${f.name} (${(f.size / 1024 / 1024).toFixed(2)} MB)</div>`).join('');
}

function uploadFiles() {
    const files = fileInput.files;
    if (files.length === 0) {
        showMessage('Please select files to upload', 'error');
        return;
    }
    
    const formData = new FormData();
    for (let file of files) {
        formData.append('files[]', file);
    }
    
    const btn = document.getElementById('upload-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Uploading...';
    
    fetch('{{ route("admin.system-settings.files.upload") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            uploadedFiles = data.files || [];
            document.getElementById('deploy-btn').disabled = false;
        } else {
            showMessage(data.message || 'Error uploading files', 'error');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload mr-2"></i> Upload Files';
    });
}

function deployFiles() {
    if (uploadedFiles.length === 0) {
        showMessage('Please upload files first', 'error');
        return;
    }
    
    const destination = document.getElementById('deploy-destination').value;
    
    if (!confirm(`Are you sure you want to deploy ${uploadedFiles.length} file(s) to ${destination}?`)) {
        return;
    }
    
    const btn = document.getElementById('deploy-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deploying...';
    
    fetch('{{ route("admin.system-settings.files.deploy") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            files: uploadedFiles,
            destination: destination
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            uploadedFiles = [];
            document.getElementById('deploy-btn').disabled = true;
            fileInput.value = '';
            document.getElementById('uploaded-files-list').innerHTML = '';
        } else {
            showMessage(data.message || 'Error deploying files', 'error');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-rocket mr-2"></i> Deploy Files';
    });
}

function runMigrations() {
    if (!confirm('Are you sure you want to run database migrations?')) {
        return;
    }
    
    const btn = document.getElementById('migrate-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Running...';
    
    fetch('{{ route("admin.system-settings.migrations.run") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            force: true
        })
    })
    .then(response => response.json())
    .then(data => {
        const outputDiv = document.getElementById('migration-output');
        const contentDiv = document.getElementById('migration-output-content');
        outputDiv.style.display = 'block';
        
        if (data.success) {
            contentDiv.textContent = data.output || 'Migrations ran successfully.';
            showMessage(data.message, 'success');
        } else {
            contentDiv.textContent = data.output || data.message || 'Error running migrations.';
            showMessage(data.message || 'Error running migrations', 'error');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-play mr-2"></i> Run Migrations';
    });
}

function runCommand(command) {
    if (!confirm(`Are you sure you want to run: ${command}?`)) {
        return;
    }
    
    fetch('{{ route("admin.system-settings.command.run") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            command: command
        })
    })
    .then(response => response.json())
    .then(data => {
        const outputDiv = document.getElementById('command-output');
        const contentDiv = document.getElementById('command-output-content');
        outputDiv.style.display = 'block';
        
        if (data.success) {
            contentDiv.textContent = data.output || 'Command executed successfully.';
            showMessage(data.message, 'success');
        } else {
            contentDiv.textContent = data.output || data.message || 'Error executing command.';
            showMessage(data.message || 'Error executing command', 'error');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, 'error');
    });
}

function showMessage(message, type) {
    const container = document.getElementById('message-container');
    const alert = document.getElementById('message-alert');
    
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    container.style.display = 'block';
    
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}

// Database Settings
async function testDatabaseConnection() {
    const form = document.getElementById('database-settings-form');
    const formData = new FormData(form);
    const resultDiv = document.getElementById('db-test-result');
    
    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4"><i class="fas fa-spinner fa-spin mr-2"></i>Testing connection...</div>';

    try {
        const response = await fetch('{{ route("admin.system-settings.database.test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                host: formData.get('host'),
                port: parseInt(formData.get('port')),
                database: formData.get('database'),
                username: formData.get('username'),
                password: formData.get('password'),
            }),
        });

        const data = await response.json();
        
        if (data.success) {
            resultDiv.innerHTML = '<div class="bg-green-50 border border-green-200 rounded-lg p-4"><i class="fas fa-check-circle text-green-600 mr-2"></i>Database connection successful!</div>';
        } else {
            resultDiv.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4"><i class="fas fa-times-circle text-red-600 mr-2"></i>${data.message}</div>`;
        }
    } catch (error) {
        resultDiv.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4"><i class="fas fa-times-circle text-red-600 mr-2"></i>Error: ${error.message}</div>`;
    }
}

async function updateDatabaseSettings() {
    if (!confirm('Are you sure you want to update database settings? This will modify your .env file.')) {
        return;
    }

    const form = document.getElementById('database-settings-form');
    const formData = new FormData(form);

    try {
        const response = await fetch('{{ route("admin.system-settings.database.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                host: formData.get('host'),
                port: parseInt(formData.get('port')),
                database: formData.get('database'),
                username: formData.get('username'),
                password: formData.get('password'),
            }),
        });

        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message, 'success');
            // Test connection after update
            setTimeout(() => testDatabaseConnection(), 1000);
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
    }
}

// Environment Variables
async function loadEnvSettings() {
    try {
        const response = await fetch('{{ route("admin.system-settings.env.get") }}');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('env-settings-container');
            const list = document.getElementById('env-settings-list');
            
            list.innerHTML = '';
            
            // Filter out sensitive keys or show all
            const sensitiveKeys = ['APP_KEY', 'DB_PASSWORD', 'PUSHER_APP_SECRET'];
            
            Object.keys(data.settings).sort().forEach(key => {
                const isSensitive = sensitiveKeys.includes(key);
                const value = isSensitive && data.settings[key] ? '••••••••' : data.settings[key];
                
                const div = document.createElement('div');
                div.className = 'border border-gray-200 rounded-lg p-3';
                div.innerHTML = `
                    <label class="block text-sm font-medium text-gray-700 mb-1">${key}</label>
                    <input type="${isSensitive ? 'password' : 'text'}" 
                           name="env_${key}" 
                           value="${data.settings[key]}" 
                           data-key="${key}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand text-sm">
                    ${isSensitive ? '<p class="text-xs text-gray-500 mt-1">Sensitive value - enter new password to change</p>' : ''}
                `;
                list.appendChild(div);
            });
            
            container.classList.remove('hidden');
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        showMessage('Error loading environment variables: ' + error.message, 'error');
    }
}

async function updateEnvSettings() {
    if (!confirm('Are you sure you want to update environment variables? This will modify your .env file.')) {
        return;
    }

    const inputs = document.querySelectorAll('#env-settings-list input[data-key]');
    const settings = {};

    inputs.forEach(input => {
        const key = input.getAttribute('data-key');
        const value = input.value;
        if (key && value !== undefined) {
            settings[key] = value;
        }
    });

    try {
        const response = await fetch('{{ route("admin.system-settings.env.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                settings: settings
            }),
        });

        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => {
                document.getElementById('env-settings-container').classList.add('hidden');
            }, 2000);
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
    }
}
</script>
@endsection
