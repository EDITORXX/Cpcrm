@extends('layouts.app')

@section('title', 'Create Automation - Base CRM')
@section('page-title', 'Create New Automation')
@section('page-subtitle', 'Simple 3-step wizard to set up lead automation')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <div id="step-1-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white font-semibold">1</div>
                    <span class="ml-2 text-sm font-medium text-gray-900">Lead Source</span>
                </div>
                <div class="w-16 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <div id="step-2-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-600">Distribution</span>
                </div>
                <div class="w-16 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <div id="step-3-indicator" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-600">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Step Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <form id="automation-form" method="POST" action="{{ route('crm.automation.store-simple') }}">
            @csrf

            <!-- Step 1: Lead Source Selection -->
            <div id="step-1" class="step-content">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 1: Select Lead Source</h3>
                <p class="text-gray-600 mb-6">Choose where your leads will come from</p>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lead Source <span class="text-red-500">*</span></label>
                    <select name="lead_source" id="lead_source" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" required>
                        <option value="">-- Select Source --</option>
                        <option value="google_sheet">Google Sheet</option>
                        <option value="csv_excel">CSV/Excel File</option>
                    </select>
                </div>

                <!-- Google Sheet Configuration -->
                <div id="google-sheet-config" class="hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Google Sheet URL or ID <span class="text-red-500">*</span></label>
                        <input type="text" name="sheet_id" id="sheet_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" placeholder="Enter Google Sheet URL or ID" required>
                        <p class="mt-1 text-xs text-gray-500">Example: https://docs.google.com/spreadsheets/d/SHEET_ID/edit or just the SHEET_ID</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sheet Name (Tab Name)</label>
                        <input type="text" name="sheet_name" id="sheet_name" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" placeholder="Sheet1" value="Sheet1">
                        <p class="mt-1 text-xs text-gray-500">The name of the tab/sheet within the spreadsheet (default: Sheet1)</p>
                    </div>
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> Service account file is configured at <code class="bg-blue-100 px-1 rounded">storage/app/google-service-account.json</code>
                        </p>
                    </div>
                </div>

                <!-- CSV/Excel File Upload -->
                <div id="csv-excel-config" class="hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload File</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                                        <span>Upload a file</span>
                                        <input id="file-upload" name="file" type="file" accept=".csv,.xlsx,.xls" class="sr-only">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">CSV, XLSX, XLS up to 10MB</p>
                            </div>
                        </div>
                        <div id="file-name" class="mt-2 text-sm text-gray-600 hidden"></div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" onclick="nextStep()" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                        Continue →
                    </button>
                </div>
            </div>

            <!-- Step 2: Distribution Configuration -->
            <div id="step-2" class="step-content hidden">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 2: Configure Distribution</h3>
                <p class="text-gray-600 mb-6">How should leads be distributed to users?</p>

                <div class="space-y-4 mb-6">
                    <!-- Distribution Mode Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Distribution Mode <span class="text-red-500">*</span></label>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300 transition-colors">
                                <input type="radio" name="distribution_mode" value="percentage" class="mr-3" required>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Percentage Based (100% to One User)</div>
                                    <div class="text-sm text-gray-500">All leads will go to one selected user</div>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300 transition-colors">
                                <input type="radio" name="distribution_mode" value="random" class="mr-3">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Random Distribution</div>
                                    <div class="text-sm text-gray-500">Leads will be randomly distributed among selected users</div>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300 transition-colors">
                                <input type="radio" name="distribution_mode" value="one_sheet_per_user" class="mr-3">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">One Sheet Per User</div>
                                    <div class="text-sm text-gray-500">Each sheet/tab will be assigned to one user (Google Sheets only)</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- User Selection for Percentage -->
                    <div id="percentage-config" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User (100% allocation)</label>
                        <select name="percentage_user_id" id="percentage_user_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900">
                            <option value="">-- Select User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Selection for Random -->
                    <div id="random-config" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Users (Random Distribution)</label>
                        <div class="space-y-2">
                            @foreach($users as $user)
                                <label class="flex items-center">
                                    <input type="checkbox" name="random_users[]" value="{{ $user->id }}" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $user->name }} ({{ $user->role->name ?? 'N/A' }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Sheet-User Mapping for One Sheet Per User -->
                    <div id="sheet-user-config" class="hidden">
                        <p class="text-sm text-gray-600 mb-4">This option will be configured after Google Sheet connection is established.</p>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        ← Previous
                    </button>
                    <button type="button" onclick="nextStep()" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                        Continue →
                    </button>
                </div>
            </div>

            <!-- Step 3: Review & Create -->
            <div id="step-3" class="step-content hidden">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Step 3: Review & Create</h3>
                <p class="text-gray-600 mb-6">Review your automation settings and create</p>

                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Automation Summary</h4>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-600">Lead Source:</span>
                            <span id="review-lead-source" class="ml-2 text-sm text-gray-900">-</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Distribution Mode:</span>
                            <span id="review-distribution-mode" class="ml-2 text-sm text-gray-900">-</span>
                        </div>
                        <div id="review-user-selection" class="hidden">
                            <span class="text-sm font-medium text-gray-600">Selected Users:</span>
                            <span id="review-users" class="ml-2 text-sm text-gray-900">-</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Automation Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="automation_name" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" placeholder="e.g., Daily Lead Import" required>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                    <textarea name="description" id="description" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white text-gray-900" placeholder="Brief description of this automation"></textarea>
                </div>

                <div class="flex items-center mb-6">
                    <input type="checkbox" name="auto_create_call_task" id="auto_create_call_task" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="auto_create_call_task" class="ml-2 text-sm text-gray-700">Automatically create phone call tasks after lead assignment</label>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        ← Previous
                    </button>
                    <button type="submit" class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                        Create Automation
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 3;

    function updateStepIndicator(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step-${i}-indicator`);
            const stepContent = document.getElementById(`step-${i}`);
            
            if (i < step) {
                indicator.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold';
            } else if (i === step) {
                indicator.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white font-semibold';
                if (stepContent) stepContent.classList.remove('hidden');
            } else {
                indicator.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold';
                if (stepContent) stepContent.classList.add('hidden');
            }
        }
    }

    function nextStep() {
        if (currentStep < totalSteps) {
            // Validate current step
            if (currentStep === 1) {
                const leadSource = document.getElementById('lead_source').value;
                if (!leadSource) {
                    alert('Please select a lead source');
                    return;
                }
                if (leadSource === 'google_sheet') {
                    const sheetId = document.getElementById('sheet_id').value;
                    if (!sheetId) {
                        alert('Please enter Google Sheet URL or ID');
                        return;
                    }
                } else if (leadSource === 'csv_excel') {
                    const file = document.getElementById('file-upload').files[0];
                    if (!file) {
                        alert('Please upload a file');
                        return;
                    }
                }
            } else if (currentStep === 2) {
                const distributionMode = document.querySelector('input[name="distribution_mode"]:checked');
                if (!distributionMode) {
                    alert('Please select a distribution mode');
                    return;
                }
                updateReview();
            }
            
            currentStep++;
            updateStepIndicator(currentStep);
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepIndicator(currentStep);
        }
    }

    // Lead source change handler
    document.getElementById('lead_source').addEventListener('change', function() {
        const value = this.value;
        document.getElementById('google-sheet-config').classList.toggle('hidden', value !== 'google_sheet');
        document.getElementById('csv-excel-config').classList.toggle('hidden', value !== 'csv_excel');
    });

    // Distribution mode change handler
    document.querySelectorAll('input[name="distribution_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const value = this.value;
            document.getElementById('percentage-config').classList.toggle('hidden', value !== 'percentage');
            document.getElementById('random-config').classList.toggle('hidden', value !== 'random');
            document.getElementById('sheet-user-config').classList.toggle('hidden', value !== 'one_sheet_per_user');
        });
    });

    // File upload handler
    document.getElementById('file-upload').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            document.getElementById('file-name').textContent = `Selected: ${fileName}`;
            document.getElementById('file-name').classList.remove('hidden');
        }
    });

    function updateReview() {
        const leadSource = document.getElementById('lead_source').value;
        const distributionMode = document.querySelector('input[name="distribution_mode"]:checked')?.value;
        
        document.getElementById('review-lead-source').textContent = 
            leadSource === 'google_sheet' ? 'Google Sheet' : 'CSV/Excel';
        
        if (distributionMode) {
            let modeText = '';
            let usersText = '';
            
            if (distributionMode === 'percentage') {
                modeText = 'Percentage Based (100% to One User)';
                const userId = document.getElementById('percentage_user_id').value;
                if (userId) {
                    const userSelect = document.getElementById('percentage_user_id');
                    usersText = userSelect.options[userSelect.selectedIndex].text;
                }
            } else if (distributionMode === 'random') {
                modeText = 'Random Distribution';
                const selectedUsers = Array.from(document.querySelectorAll('input[name="random_users[]"]:checked'))
                    .map(cb => {
                        const label = cb.nextElementSibling;
                        return label ? label.textContent.trim() : '';
                    })
                    .filter(Boolean);
                usersText = selectedUsers.length > 0 ? selectedUsers.join(', ') : 'No users selected';
            } else if (distributionMode === 'one_sheet_per_user') {
                modeText = 'One Sheet Per User';
                usersText = 'To be configured after sheet connection';
            }
            
            document.getElementById('review-distribution-mode').textContent = modeText;
            if (usersText) {
                document.getElementById('review-users').textContent = usersText;
                document.getElementById('review-user-selection').classList.remove('hidden');
            } else {
                document.getElementById('review-user-selection').classList.add('hidden');
            }
        }
    }

    // Initialize
    updateStepIndicator(1);
</script>
@endpush
@endsection

