// CRM Dashboard JavaScript
// API Base URL
const API_BASE = '/api/crm';
let authToken = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Try to get auth token - will use session-based auth if not available
    authToken = localStorage.getItem('crm_token');
    
    // Initialize dashboard
    initDashboard();
});

// Initialize dashboard
function initDashboard() {
    // Load initial data
    loadStats();
    loadTelecallerStats();
    loadDailyProspects();
    loadUsers(); // For dropdowns
    loadBlacklist();
    loadPendingVerifications();
    loadImportedLeads();
    
    // Set up event listeners
    setupEventListeners();
}

// Setup event listeners
function setupEventListeners() {
    // Date range filter
    document.getElementById('date-range-filter')?.addEventListener('change', function() {
        loadStats();
        loadTelecallerStats();
    });
    
    // Stats card clicks
    document.querySelectorAll('.stats-card-gradient').forEach(card => {
        card.addEventListener('click', function() {
            const filter = this.dataset.filter;
            handleStatCardClick(filter);
        });
    });
    
    // Form submissions
    document.getElementById('csv-upload-form')?.addEventListener('submit', handleCsvUpload);
    document.getElementById('add-lead-form')?.addEventListener('submit', handleAddLead);
    document.getElementById('blacklist-add-form')?.addEventListener('submit', handleAddBlacklist);
    document.getElementById('create-user-form')?.addEventListener('submit', handleCreateUser);
    document.getElementById('edit-user-form')?.addEventListener('submit', handleUpdateUser);
    document.getElementById('transfer-leads-form')?.addEventListener('submit', handleTransferLeads);
    document.getElementById('target-form')?.addEventListener('submit', handleCreateTarget);
}

// API Helper Functions
async function apiRequest(url, options = {}) {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...options.headers
    };
    
    // Add CSRF token if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken;
    }
    
    // Add auth token if available
    if (authToken) {
        headers['Authorization'] = `Bearer ${authToken}`;
    }
    
    // Include credentials for session-based auth
    const fetchOptions = {
        ...options,
        headers,
        credentials: 'same-origin'
    };
    
    try {
        const response = await fetch(API_BASE + url, fetchOptions);
        
        if (!response.ok) {
            const error = await response.json().catch(() => ({ message: 'Request failed' }));
            throw new Error(error.message || 'Request failed');
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        showNotification('Error: ' + error.message, 'danger');
        throw error;
    }
}

// Load Stats
async function loadStats() {
    try {
        const dateRange = document.getElementById('date-range-filter')?.value || 'all_time';
        const data = await apiRequest(`/dashboard/stats?date_range=${dateRange}`);
        
        document.getElementById('stat-total-assigned').textContent = data.total_assigned || 0;
        document.getElementById('stat-called').textContent = data.called || 0;
        document.getElementById('stat-not-interested').textContent = data.not_interested || 0;
        document.getElementById('stat-interested').textContent = data.interested || 0;
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load Telecaller Stats
async function loadTelecallerStats() {
    const container = document.getElementById('telecaller-stats-container');
    if (!container) return;
    
    try {
        const dateRange = document.getElementById('date-range-filter')?.value || 'today';
        const data = await apiRequest(`/dashboard/telecaller-stats?date_range=${dateRange}`);
        
        // Always show cards even if data is empty (shouldn't happen, but handle it)
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-4">No sales executives found.</p>';
            return;
        }
        
        container.innerHTML = data.map(tc => {
            // Always show 0 if data is missing
            const allocated = (tc.allocated !== undefined && tc.allocated !== null) ? tc.allocated : 0;
            const called = (tc.called !== undefined && tc.called !== null) ? tc.called : 0;
            const remaining = (tc.remaining !== undefined && tc.remaining !== null) ? tc.remaining : 0;
            const interested = (tc.interested !== undefined && tc.interested !== null) ? tc.interested : 0;
            const notInterested = (tc.not_interested !== undefined && tc.not_interested !== null) ? tc.not_interested : 0;
            const cnp = (tc.cnp !== undefined && tc.cnp !== null) ? tc.cnp : 0;
            
            return `
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="telecaller-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="mb-0 fw-bold text-white">${tc.telecaller_name || tc.username || 'Unknown'}</h6>
                        <div>
                            ${tc.is_absent ? '<span class="badge bg-danger me-1">Absent</span>' : ''}
                            ${tc.daily_limit && tc.daily_limit > 0 ? `<span class="badge bg-info">Limit: ${tc.daily_limit}</span>` : ''}
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-white-50 d-block">Allocated</small>
                            <div class="fw-bold text-white">${allocated}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-white-50 d-block">Called</small>
                            <div class="fw-bold text-white">${called}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-white-50 d-block">Remaining</small>
                            <div class="fw-bold text-white">${remaining}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-white-50 d-block">Interested</small>
                            <div class="fw-bold" style="color: #90ee90;">${interested}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-white-50 d-block">Not Interested</small>
                            <div class="fw-bold" style="color: #ffb3b3;">${notInterested}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-white-50 d-block">CNP</small>
                            <div class="fw-bold text-white">${cnp}</div>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }).join('');
    } catch (error) {
        console.error('Error loading telecaller stats:', error);
        container.innerHTML = '<p class="text-danger text-center py-4">Error: ' + (error.message || 'Failed to load sales executive stats') + '</p>';
    }
}

// Load Daily Prospects
let currentProspectPage = 1;
let currentViewMode = localStorage.getItem('prospect-view-mode') || 'card';

async function loadDailyProspects(page = 1) {
    try {
        currentProspectPage = page;
        const dateRange = document.getElementById('prospects-date-range')?.value || 'this_month';
        const userId = document.getElementById('prospects-user-filter')?.value || 'all';
        const verificationStatus = document.getElementById('prospects-verification-filter')?.value || 'all';
        const perPage = document.getElementById('prospects-per-page')?.value || 50;
        
        const params = new URLSearchParams({
            date_range: dateRange,
            user_id: userId,
            verification_status: verificationStatus,
            page: page,
            per_page: perPage
        });
        
        const data = await apiRequest(`/dashboard/daily-prospects?${params}`);
        
        // Update stats
        updateProspectStats(data.stats);
        
        // Render prospects
        renderProspects(data.data, currentViewMode);
        
        // Render pagination
        renderPagination(data.pagination);
        
        // Update user filter dropdown
        updateUserFilterDropdown(data.stats_by_user);
    } catch (error) {
        console.error('Error loading prospects:', error);
    }
}

function updateProspectStats(stats) {
    const container = document.getElementById('prospect-stats-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Prospects</h6>
                    <h2 class="card-title">${stats.total || 0}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Pending Verification</h6>
                    <h2 class="card-title">${stats.pending_verification || 0}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Verified</h6>
                    <h2 class="card-title">${stats.verified || 0}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Rejected</h6>
                    <h2 class="card-title">${stats.rejected || 0}</h2>
                </div>
            </div>
        </div>
    `;
}

function renderProspects(prospects, viewMode) {
    const container = document.getElementById('prospects-container');
    if (!container) return;
    
    if (prospects.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-4">No prospects found.</p>';
        return;
    }
    
    if (viewMode === 'card') {
        container.innerHTML = prospects.map(p => `
            <div class="prospect-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0 fw-bold">${p.customer_name}</h6>
                    <span class="badge ${getVerificationBadgeClass(p.verification_status)}">${p.verification_status}</span>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6"><small class="text-muted">Phone:</small> ${p.phone}</div>
                    <div class="col-6"><small class="text-muted">Budget:</small> ${p.budget ? formatCurrency(p.budget) : 'N/A'}</div>
                    <div class="col-6"><small class="text-muted">Location:</small> ${p.preferred_location || 'N/A'}</div>
                    <div class="col-6"><small class="text-muted">Purpose:</small> ${p.purpose || 'N/A'}</div>
                    <div class="col-6"><small class="text-muted">Date:</small> ${formatDate(p.created_at)}</div>
                    ${p.response_time ? `<div class="col-6"><small class="text-muted">Response Time:</small> ${p.response_time}</div>` : ''}
                </div>
                <div class="mt-2">
                    <span class="badge bg-secondary">Created By: ${p.created_by_name || 'Unknown'}</span>
                </div>
                <div class="mt-2">
                    <button class="btn btn-sm btn-primary" onclick="showProspectDetails(${p.id})">
                        View Details
                    </button>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Budget</th>
                            <th>Location</th>
                            <th>Purpose</th>
                            <th>Created By</th>
                            <th>Verification Status</th>
                            <th>Response Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${prospects.map(p => `
                            <tr>
                                <td>${formatDate(p.created_at)}</td>
                                <td>${p.customer_name}</td>
                                <td>${p.phone}</td>
                                <td>${p.budget ? formatCurrency(p.budget) : 'N/A'}</td>
                                <td>${p.preferred_location || 'N/A'}</td>
                                <td>${p.purpose || 'N/A'}</td>
                                <td>${p.created_by_name || 'Unknown'}</td>
                                <td><span class="badge ${getVerificationBadgeClass(p.verification_status)}">${p.verification_status}</span></td>
                                <td>${p.response_time || 'N/A'}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="showProspectDetails(${p.id})">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
}

function renderPagination(pagination) {
    const container = document.getElementById('prospects-pagination');
    if (!container || !pagination) return;
    
    if (pagination.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<ul class="pagination justify-content-center">';
    
    // Previous
    html += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadDailyProspects(${pagination.current_page - 1}); return false;">Previous</a>
    </li>`;
    
    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadDailyProspects(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next
    html += `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadDailyProspects(${pagination.current_page + 1}); return false;">Next</a>
    </li>`;
    
    html += '</ul>';
    html += `<div class="text-center mt-2"><small class="text-muted">Showing ${pagination.from} to ${pagination.to} of ${pagination.total} records</small></div>`;
    
    container.innerHTML = html;
}

// Toggle View Mode
function toggleViewMode(mode) {
    currentViewMode = mode;
    localStorage.setItem('prospect-view-mode', mode);
    
    // Update button states
    document.querySelectorAll('.view-toggle-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === mode) {
            btn.classList.add('active');
        }
    });
    
    // Reload prospects with new view mode
    loadDailyProspects(currentProspectPage);
}

// Handle Stat Card Click
function handleStatCardClick(filter) {
    // Switch to prospects tab
    const prospectsTab = document.getElementById('prospects-tab');
    if (prospectsTab) {
        prospectsTab.click();
    }
    
    // Set filter
    const verificationFilter = document.getElementById('prospects-verification-filter');
    if (verificationFilter) {
        if (filter === 'all') {
            verificationFilter.value = 'all';
        } else if (filter === 'called') {
            // Filter by called status - this would need backend support
            verificationFilter.value = 'all';
        } else if (filter === 'called_not_interested') {
            verificationFilter.value = 'rejected'; // Approximate mapping
        } else if (filter === 'called_interested') {
            verificationFilter.value = 'verified'; // Approximate mapping
        }
    }
    
    // Reload prospects
    loadDailyProspects(1);
}

// Filter by Telecaller
function filterProspectsByTelecaller(telecallerId) {
    // Switch to prospects tab
    const prospectsTab = document.getElementById('prospects-tab');
    if (prospectsTab) {
        prospectsTab.click();
    }
    
    // Set user filter
    const userFilter = document.getElementById('prospects-user-filter');
    if (userFilter) {
        userFilter.value = telecallerId;
        loadDailyProspects(1);
    }
}

// Load Users
async function loadUsers() {
    try {
        const data = await apiRequest('/users');
        
        // Populate dropdowns
        updateTelecallerDropdowns(data.filter(u => u.role?.slug === 'sales_executive'));
        updateUserDropdowns(data);
        
        // Load roles for user management
        loadRoles();
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

// Load Roles
async function loadRoles() {
    try {
        const roles = await apiRequest('/roles');
        
        // Update role dropdowns
        const userRoleSelect = document.getElementById('user-role');
        const editUserRoleSelect = document.getElementById('edit-user-role');
        
        if (userRoleSelect) {
            userRoleSelect.innerHTML = '<option value="">Select Role...</option>' + 
                roles.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
        }
        
        if (editUserRoleSelect) {
            editUserRoleSelect.innerHTML = '<option value="">Select Role...</option>' + 
                roles.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading roles:', error);
    }
}

function updateTelecallerDropdowns(telecallers) {
    const selects = ['csv-telecaller-select', 'add-lead-assign-to', 'transfer-from-telecaller', 'transfer-to-telecaller'];
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            const currentValue = select.value;
            select.innerHTML = '<option value="">Select Sales Executive...</option>' + 
                telecallers.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
            select.value = currentValue;
        }
    });
}

function updateUserDropdowns(users) {
    // User management dropdown
    const targetUserSelect = document.getElementById('target-user-select');
    if (targetUserSelect) {
        targetUserSelect.innerHTML = '<option value="">Select User...</option>' + 
            users.map(u => `<option value="${u.id}">${u.name} (${u.role?.name || 'N/A'})</option>`).join('');
    }
}

function updateUserFilterDropdown(statsByUser) {
    const select = document.getElementById('prospects-user-filter');
    if (select) {
        const currentValue = select.value;
        select.innerHTML = '<option value="all">All Users</option>' + 
            statsByUser.map(u => `<option value="${u.user_id}">${u.username} (${u.count})</option>`).join('');
        select.value = currentValue;
    }
}

// User Management Modal
function showUserManagementModal() {
    const modal = new bootstrap.Modal(document.getElementById('userManagementModal'));
    modal.show();
    loadUsersList();
}

async function loadUsersList() {
    try {
        const data = await apiRequest('/users');
        const container = document.getElementById('users-list-container');
        if (!container) return;
        
        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Manager</th>
                            <th>Independent</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(u => `
                            <tr>
                                <td>${u.name}</td>
                                <td>${u.manager?.name || 'N/A'}</td>
                                <td>${u.independent ? 'Yes' : 'No'}</td>
                                <td>${formatDate(u.created_at)}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editUser(${u.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } catch (error) {
        console.error('Error loading users list:', error);
    }
}

function showCreateUserForm() {
    document.getElementById('create-user-form-container').classList.remove('d-none');
}

function cancelCreateUser() {
    document.getElementById('create-user-form-container').classList.add('d-none');
    document.getElementById('create-user-form').reset();
}

async function handleCreateUser(e) {
    e.preventDefault();
    try {
        const roleId = document.getElementById('user-role').value;
        const roleSelect = document.getElementById('user-role');
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption ? selectedOption.text.toLowerCase() : '';
        
        // Client-side validation: Check if CRM user is trying to create Admin or CRM role
        // Note: Backend also validates this, but this provides immediate feedback
        if (roleName.includes('admin') || roleName.includes('crm')) {
            // Check if this is a restricted role by checking the role slug or name
            // Since roles are already filtered by backend, if Admin/CRM appear, it means user is Admin
            // But for safety, we'll let backend handle the validation and show error message
        }
        
        const formData = {
            name: document.getElementById('user-username').value,
            email: document.getElementById('user-email').value,
            password: document.getElementById('user-password').value || '123456',
            role_id: roleId,
            manager_id: document.getElementById('user-manager')?.value || null,
        };
        
        await apiRequest('/users', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        showNotification('User created successfully', 'success');
        cancelCreateUser();
        loadUsersList();
        loadUsers(); // Reload for dropdowns
    } catch (error) {
        console.error('Error creating user:', error);
        const errorMessage = error.message || 'Failed to create user. Please check if you have permission to create this role.';
        showNotification(errorMessage, 'error');
    }
}

async function editUser(userId) {
    // Load user data and show edit modal
    // Implementation needed
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    try {
        await apiRequest(`/users/${userId}`, { method: 'DELETE' });
        showNotification('User deleted successfully', 'success');
        loadUsersList();
        loadUsers();
    } catch (error) {
        console.error('Error deleting user:', error);
        const errorMessage = error.message || 'Failed to delete user. Only administrators can delete users.';
        showNotification(errorMessage, 'error');
    }
}

// Transfer Leads Modal
function showTransferLeadsModal() {
    const modal = new bootstrap.Modal(document.getElementById('transferLeadsModal'));
    modal.show();
}

async function handleTransferLeads(e) {
    e.preventDefault();
    try {
        const formData = {
            from_telecaller_id: document.getElementById('transfer-from-telecaller').value,
            to_telecaller_id: document.getElementById('transfer-to-telecaller').value,
            transfer_not_interested: document.getElementById('transfer-not-interested').checked,
            transfer_cnp: document.getElementById('transfer-cnp').checked,
        };
        
        await apiRequest('/transfer-leads', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        showNotification('Leads transferred successfully', 'success');
        bootstrap.Modal.getInstance(document.getElementById('transferLeadsModal')).hide();
        loadTelecallerStats();
    } catch (error) {
        console.error('Error transferring leads:', error);
    }
}

// Prospect Details
async function showProspectDetails(prospectId) {
    const modal = new bootstrap.Modal(document.getElementById('prospectDetailsModal'));
    modal.show();
    
    // Load prospect details
    // This would need an API endpoint to get single prospect
    // For now, we'll show a placeholder
    const container = document.getElementById('prospect-details-content');
    container.innerHTML = '<p class="text-muted">Prospect details loading...</p>';
}

// Blacklist Functions
async function loadBlacklist() {
    try {
        const data = await apiRequest('/blacklist');
        renderBlacklist(data);
    } catch (error) {
        console.error('Error loading blacklist:', error);
    }
}

function renderBlacklist(blacklisted) {
    const container = document.getElementById('blacklist-list-container');
    if (!container) return;
    
    if (blacklisted.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-4">No blacklisted numbers.</p>';
        return;
    }
    
    container.innerHTML = `
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Phone</th>
                        <th>Reason</th>
                        <th>Blacklisted By</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    ${blacklisted.map(b => `
                        <tr>
                            <td>${b.phone}</td>
                            <td>${b.reason}</td>
                            <td>${b.blacklisted_by?.name || 'Unknown'}</td>
                            <td>${formatDate(b.blacklisted_at)}</td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="removeFromBlacklist(${b.id})">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

async function handleAddBlacklist(e) {
    e.preventDefault();
    try {
        const formData = {
            phone: document.getElementById('blacklist-phone').value,
            reason: document.getElementById('blacklist-reason').value,
        };
        
        await apiRequest('/blacklist', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        showNotification('Number added to blacklist', 'success');
        document.getElementById('blacklist-add-form').reset();
        loadBlacklist();
    } catch (error) {
        console.error('Error adding to blacklist:', error);
    }
}

async function removeFromBlacklist(id) {
    if (!confirm('Remove this number from blacklist?')) return;
    
    try {
        await apiRequest(`/blacklist/${id}`, { method: 'DELETE' });
        showNotification('Number removed from blacklist', 'success');
        loadBlacklist();
    } catch (error) {
        console.error('Error removing from blacklist:', error);
    }
}

// Imported Leads
async function loadImportedLeads() {
    // Implementation needed
}

async function assignSelectedLeads() {
    // Implementation needed
}

// CSV Upload
async function handleCsvUpload(e) {
    e.preventDefault();
    // Implementation needed
}

// Add Lead
async function handleAddLead(e) {
    e.preventDefault();
    try {
        const formData = {
            customer_name: document.getElementById('add-lead-name').value,
            phone: document.getElementById('add-lead-phone').value,
            notes: document.getElementById('add-lead-notes').value,
            assigned_to: document.getElementById('add-lead-assign-to').value,
        };
        
        await apiRequest('/add-lead', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        showNotification('Lead added successfully', 'success');
        clearAddLeadForm();
        loadStats();
        loadTelecallerStats();
    } catch (error) {
        console.error('Error adding lead:', error);
    }
}

function clearAddLeadForm() {
    document.getElementById('add-lead-form').reset();
}

// Target Management
async function handleCreateTarget(e) {
    e.preventDefault();
    try {
        const formData = {
            user_id: document.getElementById('target-user-select').value,
            target_month: document.getElementById('target-month').value,
            target_visits: document.getElementById('target-visits').value,
            target_meetings: document.getElementById('target-meetings').value,
            target_closers: document.getElementById('target-closers').value,
        };
        
        await apiRequest('/targets', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        showNotification('Target saved successfully', 'success');
        document.getElementById('target-form').reset();
        loadTargets();
    } catch (error) {
        console.error('Error creating target:', error);
    }
}

async function loadTargets() {
    // Implementation needed
}

// Pending Verifications
async function loadPendingVerifications() {
    try {
        const type = document.getElementById('verification-type-filter')?.value || 'all';
        const employeeType = document.getElementById('verification-employee-filter')?.value || 'all';
        
        const params = new URLSearchParams({
            type: type,
            employee_type: employeeType
        });
        
        const data = await apiRequest(`/pending-verifications?${params}`);
        renderPendingVerifications(data.data);
        
        // Update badge
        const badge = document.getElementById('pending-verification-count-badge');
        if (badge) {
            badge.textContent = data.total || 0;
        }
    } catch (error) {
        console.error('Error loading pending verifications:', error);
    }
}

function renderPendingVerifications(prospects) {
    const container = document.getElementById('pending-verifications-container');
    if (!container) return;
    
    if (!prospects || prospects.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-4">No pending verifications.</p>';
        return;
    }
    
    container.innerHTML = `
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Customer Name</th>
                        <th>Phone</th>
                        <th>Employee</th>
                        <th>Employee Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${prospects.map(p => `
                        <tr>
                            <td>${p.type || 'N/A'}</td>
                            <td>${p.customer_name}</td>
                            <td>${p.phone}</td>
                            <td>${p.created_by_name || 'Unknown'}</td>
                            <td>${p.employee_type || 'N/A'}</td>
                            <td>${formatDate(p.created_at)}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="showProspectDetails(${p.id})">
                                    View
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// Utility Functions
function showNotification(message, type = 'success') {
    const alert = document.getElementById('notification-alert');
    const messageEl = document.getElementById('notification-message');
    
    if (alert && messageEl) {
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        messageEl.textContent = message;
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(amount);
}

function getVerificationBadgeClass(status) {
    switch(status) {
        case 'verified': return 'badge-verified';
        case 'rejected': return 'badge-rejected';
        case 'pending_verification': return 'badge-pending';
        default: return 'badge bg-secondary';
    }
}

async function handleRoleChange() {
    const roleSelect = document.getElementById('user-role');
    const managerContainer = document.getElementById('user-manager-container');
    const independentContainer = document.getElementById('user-independent-container');
    
    if (!roleSelect.value) {
        managerContainer.style.display = 'none';
        independentContainer.style.display = 'none';
        return;
    }
    
    // Get role slug to determine what fields to show
    try {
        const roles = await apiRequest('/roles');
        const selectedRole = roles.find(r => r.id == roleSelect.value);
        
        if (selectedRole) {
            // Show manager dropdown for sales executive role
            if (selectedRole.slug === 'sales_executive') {
                managerContainer.style.display = 'block';
                independentContainer.style.display = 'none';
            } else if (selectedRole.slug === 'assistant_sales_manager') {
                managerContainer.style.display = 'none';
                independentContainer.style.display = 'block';
            } else {
                managerContainer.style.display = 'none';
                independentContainer.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error checking role:', error);
    }
}

function showAddSheetModal() {
    // Implementation needed - redirect to lead import page or show modal
    alert('Add Google Sheet functionality - to be implemented');
}

