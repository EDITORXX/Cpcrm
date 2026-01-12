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
                    </div>
                    
                    <!-- Custom Column Mappings -->
                    <div class="mt-4 pt-4 border-t">
                        <div class="flex justify-between items-center mb-3">
                            <h5 class="text-sm font-semibold text-gray-700">Custom Column Mappings</h5>
                            <button type="button" onclick="addCustomColumnRow()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                <i class="fas fa-plus mr-1"></i>Add Custom Column
                            </button>
                        </div>
                        <div id="custom-columns-list" class="space-y-2">
                            <!-- Custom columns will be dynamically added here -->
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Map additional columns from your sheet to custom fields. These will be stored separately and displayed on lead detail page.
                        </p>
                    </div>
                </div>

                <!-- Sync Settings -->
                <div class="border-t pt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Sync Settings</h4>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="hidden" name="auto_sync_enabled" value="0">
                            <input type="checkbox" 
                                   id="auto_sync_enabled" 
                                   name="auto_sync_enabled"
                                   value="1"
                                   checked
                                   onchange="this.previousElementSibling.value = this.checked ? '1' : '0'"
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
                                   value="2"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Automation -->
                <div class="border-t pt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Lead Assignment</h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Assignment Configuration
                        </label>
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800 mb-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Lead assignment is configured through the Sheet Assignments system.
                            </p>
                            <a href="{{ route('lead-assignment.sheet-assignments') }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                Configure Sheet Assignment
                            </a>
                            <p class="text-xs text-blue-600 mt-2">
                                After saving this configuration, go to Sheet Assignments to set up automatic lead distribution.
                            </p>
                        </div>
                        <input type="hidden" id="automation_id" name="automation_id" value="">
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
let customColumnRows = [];
const standardLeadFields = [
    { key: 'email', label: 'Email' },
    { key: 'address', label: 'Address' },
    { key: 'city', label: 'City' },
    { key: 'state', label: 'State' },
    { key: 'pincode', label: 'Pincode' },
    { key: 'preferred_location', label: 'Preferred Location' },
    { key: 'preferred_size', label: 'Preferred Size' },
    { key: 'property_type', label: 'Property Type' },
    { key: 'budget', label: 'Budget' },
    { key: 'use_end_use', label: 'Use/End Use' },
    { key: 'possession_status', label: 'Possession Status' }
];

function addCustomColumnRow(mapping = null) {
    const row = document.createElement('div');
    row.className = 'flex gap-2 items-end p-3 bg-gray-50 rounded-lg border border-gray-200 custom-column-row';
    
    const rowId = mapping ? mapping.id : 'new_' + Date.now();
    row.dataset.rowId = rowId;
    
    // Get available columns (excluding standard mapped columns)
    const standardColumns = ['A', 'B']; // name, phone only
    const usedColumns = Array.from(document.querySelectorAll('#name_column, #phone_column')).map(s => s.value).filter(v => v);
    const customUsedColumns = Array.from(document.querySelectorAll('.custom-column-row select[name="custom_columns[][sheet_column]"]')).map(s => s.value).filter(v => v);
    const allUsedColumns = [...usedColumns, ...customUsedColumns.filter(c => c !== mapping?.sheet_column)];
    
    let columnOptions = '<option value="">Select Column</option>';
    for (let col of 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')) {
        if (!allUsedColumns.includes(col)) {
            columnOptions += `<option value="${col}" ${mapping && mapping.sheet_column === col ? 'selected' : ''}>${col}</option>`;
        } else if (mapping && mapping.sheet_column === col) {
            columnOptions += `<option value="${col}" selected>${col}</option>`;
        }
    }
    
    let fieldTypeOptions = '<option value="custom">Custom Field</option><option value="form_field">Form Field (Meta/Facebook)</option>';
    
    let standardFieldOptions = '<option value="">Custom Field Name</option>';
    standardLeadFields.forEach(field => {
        standardFieldOptions += `<option value="${field.key}" ${mapping && mapping.lead_field_key === field.key ? 'selected' : ''}>${field.label}</option>`;
    });
    
    row.innerHTML = `
        <input type="hidden" name="custom_columns[${rowId}][id]" value="${mapping?.id || ''}">
        <select name="custom_columns[${rowId}][sheet_column]" required class="flex-1 px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" onchange="updateCustomColumnOptions(this)">
            ${columnOptions}
        </select>
        <select name="custom_columns[${rowId}][field_type]" required class="w-32 px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" onchange="toggleFieldKeyInput(this)">
            ${fieldTypeOptions.replace('value="custom"', `value="custom" ${mapping && mapping.field_type === 'custom' ? 'selected' : ''}`).replace('value="form_field"', `value="form_field" ${mapping && mapping.field_type === 'form_field' ? 'selected' : ''}`)}
        </select>
        <input type="text" name="custom_columns[${rowId}][lead_field_key]" placeholder="Field Key" value="${mapping?.lead_field_key || ''}" required class="flex-1 px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        <input type="text" name="custom_columns[${rowId}][field_label]" placeholder="Field Label" value="${mapping?.field_label || ''}" required class="flex-1 px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        <button type="button" onclick="removeCustomColumnRow(this)" class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 text-sm font-medium">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.getElementById('custom-columns-list').appendChild(row);
    customColumnRows.push(row);
    
    // Initialize field key input based on field type
    const fieldTypeSelect = row.querySelector('select[name*="[field_type]"]');
    toggleFieldKeyInput(fieldTypeSelect);
}

function removeCustomColumnRow(button) {
    const row = button.closest('.custom-column-row');
    row.remove();
    customColumnRows = customColumnRows.filter(r => r !== row);
}

function toggleFieldKeyInput(select) {
    const row = select.closest('.custom-column-row');
    const fieldKeyInput = row.querySelector('input[name*="[lead_field_key]"]');
    const fieldType = select.value;
    
    if (fieldType === 'form_field') {
        fieldKeyInput.placeholder = 'e.g., meta_question_budget';
    } else {
        fieldKeyInput.placeholder = 'Field Key (e.g., custom_field_1)';
    }
}

function updateCustomColumnOptions(select) {
    // This can be used to update available columns when a column is selected
    // For now, we'll handle uniqueness validation on submit
}

function saveGoogleSheetsConfig(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    // Collect custom column mappings
    const customColumns = [];
    document.querySelectorAll('.custom-column-row').forEach(row => {
        const sheetColumn = row.querySelector('select[name*="[sheet_column]"]').value;
        const fieldType = row.querySelector('select[name*="[field_type]"]').value;
        const leadFieldKey = row.querySelector('input[name*="[lead_field_key]"]').value;
        const fieldLabel = row.querySelector('input[name*="[field_label]"]').value;
        const idInput = row.querySelector('input[name*="[id]"]');
        const id = idInput ? idInput.value : null;
        
        if (sheetColumn && leadFieldKey && fieldLabel) {
            customColumns.push({
                id: id || null,
                sheet_column: sheetColumn,
                field_type: fieldType,
                lead_field_key: leadFieldKey,
                field_label: fieldLabel,
            });
        }
    });
    
    // Add custom columns as JSON
    if (customColumns.length > 0) {
        formData.append('custom_columns_json', JSON.stringify(customColumns));
    }
    
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

function closeGoogleSheetsModal() {
    document.getElementById('google_sheets_modal').classList.add('hidden');
    document.getElementById('google_sheets_form').reset();
    customColumnRows = [];
    document.getElementById('custom-columns-list').innerHTML = '';
}
</script>

