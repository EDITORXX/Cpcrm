@php
    // Automation removed - using Lead Assignment system instead
    $automations = collect([]);
@endphp

<div id="google_sheets_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Google Sheets Configuration</h3>
            <button type="button" onclick="closeGoogleSheetsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form id="google_sheets_form" onsubmit="saveGoogleSheetsConfig(event)">
            @csrf
            <input type="hidden" id="config_id" name="config_id">
            
            <div class="space-y-4">
                <!-- Sheet ID/URL -->
                <div>
                    <label for="sheet_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Google Sheet ID or URL <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="sheet_id" 
                           name="sheet_id"
                           required
                           placeholder="https://docs.google.com/spreadsheets/d/... or Sheet ID"
                           class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Paste the full Google Sheets URL or just the Sheet ID</p>
                </div>

                <!-- Sheet Name -->
                <div>
                    <label for="sheet_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Sheet Name (Tab Name) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="sheet_name" 
                           name="sheet_name"
                           required
                           value="Sheet1"
                           placeholder="Sheet1"
                           class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Authentication -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">
                            API Key (Optional)
                        </label>
                        <input type="text" 
                               id="api_key" 
                               name="api_key"
                               placeholder="For public sheets"
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="service_account_json_path" class="block text-sm font-medium text-gray-700 mb-2">
                            Service Account JSON Path (Optional)
                        </label>
                        <input type="text" 
                               id="service_account_json_path" 
                               name="service_account_json_path"
                               value="{{ file_exists(storage_path('app/google-credentials/google-service-account.json')) ? 'google-credentials/google-service-account.json' : '' }}"
                               placeholder="google-credentials/google-service-account.json"
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Path relative to storage/app/ directory</p>
                    </div>
                </div>

                <!-- Column Mapping -->
                <div class="border-t pt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Column Mapping</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label for="range" class="block text-sm font-medium text-gray-700 mb-2">Range</label>
                            <input type="text" 
                                   id="range" 
                                   name="range"
                                   required
                                   value="A:Z"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="name_column" class="block text-sm font-medium text-gray-700 mb-2">Name Column</label>
                            <select id="name_column" 
                                    name="name_column"
                                    required
                                    class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach(range('A', 'Z') as $col)
                                    <option value="{{ $col }}" {{ $col === 'A' ? 'selected' : '' }}>{{ $col }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="phone_column" class="block text-sm font-medium text-gray-700 mb-2">Phone Column</label>
                            <select id="phone_column" 
                                    name="phone_column"
                                    required
                                    class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach(range('A', 'Z') as $col)
                                    <option value="{{ $col }}" {{ $col === 'B' ? 'selected' : '' }}>{{ $col }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="notes_column" class="block text-sm font-medium text-gray-700 mb-2">Notes Column (Optional)</label>
                            <select id="notes_column" 
                                    name="notes_column"
                                    class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">None</option>
                                @foreach(range('A', 'Z') as $col)
                                    <option value="{{ $col }}" {{ $col === 'C' ? 'selected' : '' }}>{{ $col }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status_column" class="block text-sm font-medium text-gray-700 mb-2">Status Column (Sync Back)</label>
                            <select id="status_column" 
                                    name="status_column"
                                    required
                                    class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach(range('A', 'Z') as $col)
                                    <option value="{{ $col }}" {{ $col === 'D' ? 'selected' : '' }}>{{ $col }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="notes_column_sync" class="block text-sm font-medium text-gray-700 mb-2">Notes Sync Column</label>
                            <select id="notes_column_sync" 
                                    name="notes_column_sync"
                                    required
                                    class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach(range('A', 'Z') as $col)
                                    <option value="{{ $col }}" {{ $col === 'E' ? 'selected' : '' }}>{{ $col }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sync Settings -->
                <div class="border-t pt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Sync Settings</h4>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="auto_sync_enabled" 
                                   name="auto_sync_enabled"
                                   checked
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="auto_sync_enabled" class="ml-2 text-sm font-medium text-gray-700">
                                Enable Auto-Sync
                            </label>
                        </div>
                        <div>
                            <label for="sync_interval_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                                Sync Interval (minutes) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="sync_interval_minutes" 
                                   name="sync_interval_minutes"
                                   required
                                   min="1"
                                   value="5"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Automation -->
                <div class="border-t pt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Lead Automation</h4>
                    <div>
                        <label for="automation_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Automation (Optional)
                        </label>
                        <select id="automation_id" 
                                name="automation_id"
                                class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Manual Assignment</option>
                            <option value="">Manual Assignment (Use Lead Assignment System)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Automation configuration is managed through Lead Assignment settings.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" 
                        onclick="closeGoogleSheetsModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function saveGoogleSheetsConfig(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    axios.post('{{ route('lead-import.google-sheets.config.save') }}', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(response => {
        alert(response.data.message);
        closeGoogleSheetsModal();
        location.reload();
    })
    .catch(error => {
        if (error.response?.data?.errors) {
            let errors = '';
            Object.values(error.response.data.errors).forEach(err => {
                errors += err.join('\n') + '\n';
            });
            alert('Validation Error:\n' + errors);
        } else {
            alert('Error: ' + (error.response?.data?.message || error.message));
        }
    });
}
</script>

