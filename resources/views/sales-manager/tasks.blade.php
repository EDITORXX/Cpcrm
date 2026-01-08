@extends('sales-manager.layout')

@section('title', 'Tasks - Sales Manager')
@section('page-title', 'Tasks')

@push('styles')
<style>
    .tasks-container {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        width: 100%;
        box-sizing: border-box;
        max-width: 100%;
        overflow-x: hidden;
    }
    .filter-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .filter-btn {
        padding: 10px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: white;
        color: #063A1C;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .filter-btn:hover {
        border-color: #205A44;
        color: #205A44;
    }
    .filter-btn.active {
        background: #205A44;
        color: white;
        border-color: #205A44;
    }
    .tasks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    @media (max-width: 768px) {
        .tasks-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .tasks-container {
            padding: 16px !important;
        }
    }
    .task-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
    }
    .task-card:hover {
        border-color: #205A44;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    .task-card.overdue {
        border-color: #ef4444;
        border-width: 3px;
        background: #fef2f2;
    }
    .task-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #f0f0f0;
    }
    .task-action-btn {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .task-action-btn i {
        font-size: 14px;
    }
    .btn-call {
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        color: white;
    }
    .btn-call:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(32, 90, 68, 0.3);
    }
    .btn-whatsapp {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        color: white;
    }
    .btn-whatsapp:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
    }
    .btn-view-detail {
        background: #e0e0e0;
        color: #063A1C;
    }
    .btn-view-detail:hover {
        background: #d0d0d0;
        transform: translateY(-1px);
    }
    .task-header {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .task-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        font-weight: 700;
        margin-right: 12px;
    }
    .task-name {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin: 0;
    }
    .task-info {
        margin-bottom: 12px;
    }
    .task-info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 14px;
        color: #063A1C;
    }
    .task-info-row i {
        color: #205A44;
        width: 16px;
    }
    .overdue-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #ef4444;
        color: white;
        margin-top: 8px;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        margin-top: 8px;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-in_progress { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .loading-state, .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #B3B5B4;
    }
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }
    .modal.active {
        display: flex;
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        margin: auto;
        position: relative;
    }
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            padding: 20px;
            margin: 20px auto;
        }
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 24px;
        color: #063A1C;
    }
    .close-modal {
        background: none;
        border: none;
        font-size: 28px;
        color: #B3B5B4;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .close-modal:hover {
        color: #063A1C;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #063A1C;
    }
    .form-group label .required {
        color: #ef4444;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        background: #ffffff;
        color: #063A1C;
        box-sizing: border-box;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #205A44;
    }
    .form-footer {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 2px solid #f0f0f0;
    }
    .btn-save {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .btn-cancel {
        flex: 1;
        padding: 12px;
        background: #e0e0e0;
        color: #063A1C;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-cancel:hover {
        background: #d0d0d0;
    }
    .btn-verify {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-verify:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .btn-verify:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-reject {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-reject:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    .btn-reject:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .form-group input[readonly],
    .form-group select[readonly],
    .form-group textarea[readonly] {
        background: #f5f5f5;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
<div class="tasks-container">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <button class="filter-btn active" data-status="all" onclick="filterTasks('all')">All Tasks</button>
        <button class="filter-btn" data-status="pending" onclick="filterTasks('pending')">Pending</button>
        <button class="filter-btn" data-status="completed" onclick="filterTasks('completed')">Completed</button>
    </div>

    <!-- Tasks Grid -->
    <div id="tasksGrid" class="tasks-grid">
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading tasks...</p>
        </div>
    </div>
</div>

<!-- Prospect Detail Form Modal -->
<div id="prospectDetailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Prospect Verification</h3>
            <button class="close-modal" onclick="closeProspectDetailModal()">&times;</button>
        </div>
        <form id="prospectDetailForm">
            <input type="hidden" id="taskId" name="task_id">
            <input type="hidden" id="prospectId" name="prospect_id">
            <input type="hidden" id="leadId" name="lead_id">
            <input type="hidden" id="isViewMode" name="is_view_mode" value="false">
            
            <div class="form-group">
                <label for="customerName">Customer Name <span class="required">*</span></label>
                <input type="text" id="customerName" name="customer_name" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city">
            </div>

            <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state">
            </div>

            <div class="form-group">
                <label for="pincode">Pincode</label>
                <input type="text" id="pincode" name="pincode">
            </div>

            <div class="form-group">
                <label for="budget">Budget</label>
                <input type="number" id="budget" name="budget" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="preferredLocation">Preferred Location</label>
                <input type="text" id="preferredLocation" name="preferred_location" placeholder="e.g., South Mumbai, Bandra">
            </div>

            <div class="form-group">
                <label for="size">Size</label>
                <input type="text" id="size" name="size" placeholder="e.g., 2 BHK, 1200 sqft">
            </div>

            <div class="form-group">
                <label for="purpose">Purpose</label>
                <select id="purpose" name="purpose">
                    <option value="">Select Purpose</option>
                    <option value="end_user">End User</option>
                    <option value="investment">Investment</option>
                </select>
            </div>

            <div class="form-group">
                <label for="possession">Possession</label>
                <input type="text" id="possession" name="possession">
            </div>

            <div class="form-group">
                <label for="leadStatus">Lead Status <span class="required">*</span></label>
                <select id="leadStatus" name="lead_status" required>
                    <option value="">Select Lead Status</option>
                    <option value="hot">Hot</option>
                    <option value="warm">Warm</option>
                    <option value="cold">Cold</option>
                    <option value="junk">Junk</option>
                </select>
            </div>

            <div class="form-group">
                <label for="interestedProjects">Interested Projects <span class="required">*</span></label>
                <select id="interestedProjects" name="interested_projects[]" multiple required style="width: 100%; min-height: 100px;">
                    <!-- Options will be loaded dynamically -->
                </select>
                <small class="form-text text-muted">Select one or more projects. Hold Ctrl/Cmd to select multiple.</small>
            </div>

            <div class="form-group">
                <label for="managerRemark">Manager Remark</label>
                <textarea id="managerRemark" name="manager_remark" rows="3" placeholder="Enter remarks or notes..."></textarea>
            </div>

            <div class="form-footer" id="formFooter">
                <button type="button" class="btn-cancel" onclick="closeProspectDetailModal()">Cancel</button>
                <button type="button" class="btn-reject" onclick="submitProspectAction('reject')" id="btnReject">Reject</button>
                <button type="button" class="btn-verify" onclick="submitProspectAction('verify')" id="btnVerify">Verify</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '/api/sales-manager';
    const API_TOKEN = '{{ $api_token }}';
    let currentStatus = 'all';
    let currentTaskId = null;

    function getAuthHeaders() {
        return {
            'Authorization': `Bearer ${API_TOKEN}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        };
    }

    async function apiCall(endpoint, options = {}) {
        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                ...options,
                headers: {
                    ...getAuthHeaders(),
                    ...(options.headers || {})
                }
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Error:', error);
            showAlert('An error occurred: ' + error.message, 'error');
            return { success: false, message: error.message };
        }
    }

    function showAlert(message, type = 'info', duration = 3000) {
        const notification = document.getElementById('customNotification');
        const messageEl = document.getElementById('notificationMessage');
        const overlay = document.getElementById('notificationOverlay');
        
        if (notification && messageEl && overlay) {
            messageEl.textContent = message;
            overlay.style.display = 'flex';
            notification.classList.remove('hide');
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
                notification.classList.add('hide');
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }, duration);
        } else {
            alert(message);
        }
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('en-IN', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function filterTasks(status) {
        currentStatus = status;
        
        // Update button states
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-status') === status) {
                btn.classList.add('active');
            }
        });

        loadTasks();
    }

    async function loadTasks() {
        const tasksGrid = document.getElementById('tasksGrid');
        tasksGrid.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading tasks...</p></div>';

        try {
            const params = new URLSearchParams();
            if (currentStatus !== 'all') {
                params.append('status', currentStatus);
            }
            
            const result = await apiCall(`/tasks?${params.toString()}`);
            
            if (result && result.success && result.data) {
                const tasks = result.data;
                renderTasks(tasks);
            } else {
                tasksGrid.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><h3>No tasks found</h3><p>You don\'t have any tasks yet.</p></div>';
            }
        } catch (error) {
            console.error('Error loading tasks:', error);
            tasksGrid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error loading tasks</h3><p>Please refresh the page.</p></div>';
        }
    }

    function renderTasks(tasks) {
        const tasksGrid = document.getElementById('tasksGrid');
        
        if (tasks.length === 0) {
            tasksGrid.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><h3>No tasks found</h3><p>You don\'t have any tasks matching the filter.</p></div>';
            return;
        }

        tasksGrid.innerHTML = tasks.map(task => {
            const lead = task.lead || {};
            const leadName = lead.name || task.title?.replace('Call for prospect verification: ', '') || 'N/A';
            const leadPhone = lead.phone || 'N/A';
            const initial = leadName.charAt(0).toUpperCase();
            const isOverdue = task.is_overdue || false;
            const scheduledDate = formatDate(task.scheduled_at_formatted || task.scheduled_at);
            const overdueClass = isOverdue ? 'overdue' : '';
            const statusClass = `status-${task.status}`;

            const cleanPhone = leadPhone.replace(/[^0-9]/g, '');
            return `
                <div class="task-card ${overdueClass}">
                    <div class="task-header">
                        <div class="task-avatar">${initial}</div>
                        <div>
                            <h3 class="task-name">${leadName}</h3>
                            ${isOverdue ? '<span class="overdue-badge">OVERDUE</span>' : ''}
                            <span class="status-badge ${statusClass}">${task.status.replace('_', ' ').toUpperCase()}</span>
                        </div>
                    </div>
                    <div class="task-info">
                        <div class="task-info-row">
                            <i class="fas fa-phone"></i>
                            <span>${leadPhone}</span>
                        </div>
                        <div class="task-info-row">
                            <i class="fas fa-calendar"></i>
                            <span>${scheduledDate}</span>
                        </div>
                    </div>
                    <div class="task-actions">
                        <button class="task-action-btn btn-call" onclick="openProspectDetailModal(${task.id}, false)" title="Call">
                            <i class="fas fa-phone"></i>
                            <span>Call</span>
                        </button>
                        <button class="task-action-btn btn-whatsapp" onclick="openWhatsApp('${cleanPhone}')" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </button>
                        <button class="task-action-btn btn-view-detail" onclick="openProspectDetailModal(${task.id}, true)" title="View Detail">
                            <i class="fas fa-eye"></i>
                            <span>Detail</span>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function openWhatsApp(phone) {
        if (!phone || phone === 'N/A') {
            showAlert('Phone number not available', 'warning');
            return;
        }
        // Remove all non-numeric characters and ensure it starts with country code
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        if (cleanPhone.length < 10) {
            showAlert('Invalid phone number', 'warning');
            return;
        }
        // If phone doesn't start with country code, assume it's Indian (+91)
        const phoneWithCountryCode = cleanPhone.startsWith('91') && cleanPhone.length === 12 
            ? cleanPhone 
            : (cleanPhone.length === 10 ? '91' + cleanPhone : cleanPhone);
        window.open(`https://wa.me/${phoneWithCountryCode}`, '_blank');
    }

    async function loadInterestedProjects() {
        try {
            const response = await fetch('/api/interested-project-names', {
                headers: getAuthHeaders(),
            });
            const result = await response.json();
            
            if (result && result.success && result.data) {
                const projectSelect = document.getElementById('interestedProjects');
                projectSelect.innerHTML = ''; // Clear existing options
                
                result.data.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.id;
                    option.textContent = project.name;
                    projectSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading interested projects:', error);
        }
    }

    async function openProspectDetailModal(taskId, viewMode = false) {
        currentTaskId = taskId;
        const modal = document.getElementById('prospectDetailModal');
        const form = document.getElementById('prospectDetailForm');
        const modalTitle = document.getElementById('modalTitle');
        const isViewModeInput = document.getElementById('isViewMode');
        
        // Set view mode
        isViewModeInput.value = viewMode ? 'true' : 'false';
        
        // Update modal title
        modalTitle.textContent = viewMode ? 'View Prospect Details' : 'Prospect Verification';
        
        // Reset form
        form.reset();
        
        // Show loading
        modal.classList.add('active');
        form.style.opacity = '0.5';
        form.style.pointerEvents = 'none';
        
        // Enable/disable form fields based on view mode
        const formFields = form.querySelectorAll('input, select, textarea');
        formFields.forEach(field => {
            if (field.id !== 'taskId' && field.id !== 'prospectId' && field.id !== 'leadId' && field.id !== 'isViewMode') {
                field.readOnly = viewMode;
                field.disabled = viewMode;
            }
        });
        
        // Show/hide action buttons
        const footer = document.getElementById('formFooter');
        const verifyBtn = document.getElementById('btnVerify');
        const rejectBtn = document.getElementById('btnReject');
        
        if (viewMode) {
            verifyBtn.style.display = 'none';
            rejectBtn.style.display = 'none';
        } else {
            verifyBtn.style.display = 'block';
            rejectBtn.style.display = 'block';
        }

        try {
            // Load projects first
            await loadInterestedProjects();
            
            const result = await apiCall(`/tasks/${taskId}`);
            
            if (result && result.success && result.data) {
                const task = result.data;
                const lead = task.lead || {};
                const prospect = task.prospect || {};

                // Populate form
                document.getElementById('taskId').value = taskId;
                document.getElementById('prospectId').value = prospect.id || '';
                document.getElementById('leadId').value = lead.id || task.lead_id || '';
                
                // Populate from prospect first, then lead
                document.getElementById('customerName').value = prospect.customer_name || lead.name || '';
                document.getElementById('phone').value = prospect.phone || lead.phone || '';
                document.getElementById('email').value = lead.email || '';
                document.getElementById('address').value = lead.address || '';
                document.getElementById('city').value = lead.city || '';
                document.getElementById('state').value = lead.state || '';
                document.getElementById('pincode').value = lead.pincode || '';
                document.getElementById('budget').value = prospect.budget || lead.budget || lead.investment || '';
                document.getElementById('preferredLocation').value = prospect.preferred_location || lead.preferred_location || '';
                document.getElementById('size').value = prospect.size || lead.preferred_size || '';
                document.getElementById('purpose').value = prospect.purpose || '';
                document.getElementById('possession').value = prospect.possession || '';
                document.getElementById('leadStatus').value = prospect.lead_status || lead.lead_status || '';
                document.getElementById('managerRemark').value = prospect.manager_remark || '';
                
                // Populate interested projects if available
                if (prospect.interested_projects && prospect.interested_projects.length > 0) {
                    const projectSelect = document.getElementById('interestedProjects');
                    const projectIds = prospect.interested_projects.map(p => p.id || p);
                    Array.from(projectSelect.options).forEach(option => {
                        if (projectIds.includes(parseInt(option.value))) {
                            option.selected = true;
                        }
                    });
                }
            } else {
                showAlert('Failed to load task details', 'error');
                closeProspectDetailModal();
            }
        } catch (error) {
            console.error('Error loading task details:', error);
            showAlert('Error loading task details', 'error');
            closeProspectDetailModal();
        } finally {
            form.style.opacity = '1';
            form.style.pointerEvents = 'auto';
        }
    }

    function closeProspectDetailModal() {
        const modal = document.getElementById('prospectDetailModal');
        const form = document.getElementById('prospectDetailForm');
        
        modal.classList.remove('active');
        form.reset();
        currentTaskId = null;
        
        // Re-enable all fields
        const formFields = form.querySelectorAll('input, select, textarea');
        formFields.forEach(field => {
            field.readOnly = false;
            field.disabled = false;
        });
    }

    async function submitProspectAction(action) {
        const leadStatus = document.getElementById('leadStatus').value;
        if (!leadStatus) {
            showAlert('Please select a Lead Status', 'warning');
            return;
        }

        if (!currentTaskId) {
            showAlert('Task ID not found', 'error');
            return;
        }

        const customerName = document.getElementById('customerName').value;
        const phone = document.getElementById('phone').value;
        
        if (!customerName || !phone) {
            showAlert('Please fill Customer Name and Phone', 'warning');
            return;
        }

        const formData = {
            action: action, // 'verify' or 'reject'
            customer_name: customerName,
            phone: phone,
            email: document.getElementById('email').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            state: document.getElementById('state').value,
            pincode: document.getElementById('pincode').value,
            budget: document.getElementById('budget').value || null,
            preferred_location: document.getElementById('preferredLocation').value,
            size: document.getElementById('size').value,
            purpose: document.getElementById('purpose').value,
            possession: document.getElementById('possession').value,
            lead_status: leadStatus,
            manager_remark: document.getElementById('managerRemark').value,
            interested_projects: Array.from(document.getElementById('interestedProjects').selectedOptions).map(option => parseInt(option.value)),
        };

        if (action === 'reject' && !formData.manager_remark) {
            showAlert('Please enter a rejection reason', 'warning');
            return;
        }

        // Validate interested projects for verify action
        if (action === 'verify' && (!formData.interested_projects || formData.interested_projects.length === 0)) {
            showAlert('Please select at least one Interested Project', 'warning');
            return;
        }

        // Disable buttons during submission
        const verifyBtn = document.getElementById('btnVerify');
        const rejectBtn = document.getElementById('btnReject');
        verifyBtn.disabled = true;
        rejectBtn.disabled = true;

        try {
            const result = await apiCall(`/tasks/${currentTaskId}/update-lead`, {
                method: 'POST',
                body: JSON.stringify(formData),
            });

            if (result && result.success) {
                const message = action === 'verify' 
                    ? 'Prospect verified and task marked as completed successfully!' 
                    : 'Prospect rejected and task marked as completed successfully!';
                showAlert(message, 'success');
                closeProspectDetailModal();
                loadTasks();
            } else {
                const errorMsg = result?.message || 'Failed to process request';
                showAlert(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error processing request:', error);
            showAlert('Error processing request: ' + error.message, 'error');
        } finally {
            verifyBtn.disabled = false;
            rejectBtn.disabled = false;
        }
    }

    // Close modal on outside click
    document.getElementById('prospectDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeProspectDetailModal();
        }
    });

    // Load tasks on page load
    loadTasks();
</script>
@endpush
