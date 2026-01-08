@extends('telecaller.layout')

@section('title', 'Leads - Telecaller')
@section('page-title', 'Leads')

@push('styles')
<style>
    .leads-container {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .search-filter-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .search-input {
        flex: 1;
        min-width: 250px;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        background: #ffffff;
    }
    .search-input:focus {
        outline: none;
        border-color: #205A44;
    }
    .status-filter {
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        background: #ffffff;
        min-width: 150px;
    }
    .leads-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    
    /* Tablet view - 2 columns */
    @media (max-width: 1024px) {
        .leads-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    /* Mobile/Phone view - 2 columns */
    @media (max-width: 768px) {
        .leads-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
    }
    
    /* Small mobile view - 1 column */
    @media (max-width: 480px) {
        .leads-grid {
            grid-template-columns: 1fr;
        }
    }
    .lead-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
        cursor: pointer;
    }
    .lead-card:hover {
        border-color: #205A44;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    .lead-card-header {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .lead-avatar {
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
    .lead-name {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin: 0;
    }
    .lead-info {
        margin-bottom: 12px;
    }
    .lead-info-label {
        font-size: 12px;
        color: #B3B5B4;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .lead-info-value {
        font-size: 14px;
        color: #063A1C;
        font-weight: 500;
    }
    .lead-info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .lead-info-row i {
        color: #205A44;
        width: 16px;
    }
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        margin-top: 12px;
    }
    .status-new { background: #dbeafe; color: #1e40af; }
    .status-contacted { background: #fef3c7; color: #92400e; }
    .status-qualified { background: #e9d5ff; color: #6b21a8; }
    .status-site_visit_scheduled { background: #ddd6fe; color: #5b21b6; }
    .status-site_visit_completed { background: #fce7f3; color: #9f1239; }
    .status-negotiation { background: #fed7aa; color: #9a3412; }
    .status-closed_won { background: #d1fae5; color: #065f46; }
    .status-closed_lost { background: #fee2e2; color: #991b1b; }
    .status-on_hold { background: #f3f4f6; color: #374151; }
    .lead-card-footer {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #f0f0f0;
        display: flex;
        gap: 10px;
    }
    .lead-card-btn {
        flex: 1;
        padding: 10px 16px;
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
    .lead-card-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .btn-call {
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        color: white;
    }
    .btn-call:hover {
        background: linear-gradient(135deg, #5568d3 0%, #653a8f 100%);
    }
    .btn-whatsapp {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(21, 128, 61, 0.3);
    }
    .btn-whatsapp:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        box-shadow: 0 4px 8px rgba(21, 128, 61, 0.4);
        transform: translateY(-1px);
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #B3B5B4;
        grid-column: 1 / -1;
    }
    .empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 16px;
    }
    .empty-state h3 {
        font-size: 20px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .loading-state {
        text-align: center;
        padding: 40px;
        color: #B3B5B4;
        grid-column: 1 / -1;
    }
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 20px;
        grid-column: 1 / -1;
    }
    .pagination button {
        padding: 8px 16px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        background: white;
        color: #B3B5B4;
        cursor: pointer;
        font-size: 14px;
    }
    .pagination button:hover:not(:disabled) {
        background: #f0f0f0;
        border-color: #205A44;
    }
    .pagination button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .pagination .page-info {
        padding: 8px 16px;
        color: #B3B5B4;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
    <div class="leads-container">
        <!-- Search and Filter Bar -->
        <div class="search-filter-bar">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by name, phone, email, or city..." onkeyup="handleSearch()">
            <select id="statusFilter" class="status-filter" onchange="loadLeads()">
                <option value="">All Status</option>
                <option value="new">New</option>
                <option value="contacted">Contacted</option>
                <option value="qualified">Qualified</option>
                <option value="site_visit_scheduled">Site Visit Scheduled</option>
                <option value="site_visit_completed">Site Visit Completed</option>
                <option value="negotiation">Negotiation</option>
                <option value="closed_won">Closed Won</option>
                <option value="closed_lost">Closed Lost</option>
                <option value="on_hold">On Hold</option>
            </select>
        </div>

        <!-- Leads Grid -->
        <div id="leadsContent" class="leads-grid">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading leads...</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Override API_BASE_URL for telecaller-specific endpoints
    API_BASE_URL = '{{ url("/api/telecaller") }}';
    let currentPage = 1;
    let searchTimeout = null;

    // Get token from localStorage
    function getToken() {
        return localStorage.getItem('telecaller_token');
    }

    // API call helper
    async function apiCall(endpoint, options = {}) {
        const token = getToken();
        if (!token) {
            console.error('No token found, redirecting to login');
            // Clear any stale data
            localStorage.removeItem('telecaller_token');
            localStorage.removeItem('telecaller_user');
            // Redirect to login
            setTimeout(() => {
                window.location.href = '{{ route("login") }}';
            }, 100);
            return { success: false, message: 'Authentication required. Please login again.' };
        }

        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        };

        try {
            const fullUrl = `${API_BASE_URL}${endpoint}`;
            console.log('Making API call to:', fullUrl);
            console.log('Token present:', !!token);
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => {
                console.error('Request timeout after 30 seconds');
                controller.abort();
            }, 30000); // 30 second timeout

            const response = await fetch(fullUrl, {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers },
                signal: controller.signal,
            });

            clearTimeout(timeoutId);

            console.log('API Response status:', response.status);
            console.log('API Response headers:', Object.fromEntries(response.headers.entries()));

            if (response.status === 401) {
                console.error('Unauthorized - clearing token and redirecting');
                localStorage.removeItem('telecaller_token');
                localStorage.removeItem('telecaller_user');
                window.location.href = '{{ route("login") }}';
                return null;
            }

            let responseData;
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                try {
                    responseData = await response.json();
                } catch (e) {
                    console.error('Failed to parse JSON response:', e);
                    const text = await response.text();
                    console.error('Response text:', text);
                    return { success: false, message: 'Invalid JSON response from server' };
                }
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                return { success: false, message: text || `HTTP ${response.status}` };
            }

            if (!response.ok) {
                console.error('API Error Response:', responseData);
                return { 
                    success: false, 
                    message: responseData.message || responseData.error || `HTTP ${response.status}`,
                    errors: responseData.errors || null
                };
            }

            console.log('API Response data:', responseData); // Debug log
            return responseData;
        } catch (error) {
            console.error('API Call Error:', error);
            console.error('Error stack:', error.stack);
            if (error.name === 'AbortError') {
                return { success: false, message: 'Request timeout. Please try again.' };
            }
            if (error.message.includes('Failed to fetch')) {
                return { success: false, message: 'Network error: Unable to connect to server. Please check your internet connection.' };
            }
            return { success: false, message: error.message || 'Network error occurred' };
        }
    }

    // Format status for display
    function formatStatus(status) {
        const statusMap = {
            'new': 'New',
            'contacted': 'Contacted',
            'qualified': 'Qualified',
            'site_visit_scheduled': 'Site Visit Scheduled',
            'site_visit_completed': 'Site Visit Completed',
            'negotiation': 'Negotiation',
            'closed_won': 'Closed Won',
            'closed_lost': 'Closed Lost',
            'on_hold': 'On Hold',
        };
        return statusMap[status] || status;
    }

    // Format date
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    // Load leads
    async function loadLeads(page = 1) {
        currentPage = page;
        const contentDiv = document.getElementById('leadsContent');
        if (!contentDiv) {
            console.error('leadsContent element not found!');
            return;
        }
        
        contentDiv.className = 'leads-grid';
        
        // Check token first before showing loading
        const token = getToken();
        if (!token) {
            console.error('No token found! User needs to login.');
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-lock"></i>
                    <h3>Authentication Required</h3>
                    <p>You need to login to view leads.</p>
                    <p style="font-size: 12px; color: #999; margin-top: 8px;">Redirecting to login page...</p>
                    <a href="{{ route('login') }}" style="margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;">Go to Login</a>
                </div>
            `;
            setTimeout(() => {
                window.location.href = '{{ route("login") }}';
            }, 2000);
            return;
        }
        
        contentDiv.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading leads...</p></div>';

        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const search = searchInput ? searchInput.value : '';
        const status = statusFilter ? statusFilter.value : '';

        let endpoint = `/leads?per_page=50&page=${page}`;
        if (search) {
            endpoint += `&search=${encodeURIComponent(search)}`;
        }
        if (status) {
            endpoint += `&status=${encodeURIComponent(status)}`;
        }

        console.log('=== LOADING LEADS ===');
        console.log('Endpoint:', endpoint);
        console.log('API Base URL:', API_BASE_URL);
        console.log('Full URL:', `${API_BASE_URL}${endpoint}`);
        console.log('Token exists:', !!token);
        console.log('Token length:', token ? token.length : 0);
        
        // Add a timeout fallback
        const timeoutId = setTimeout(() => {
            console.error('=== TIMEOUT: API call took too long ===');
            if (contentDiv.innerHTML.includes('Loading leads...')) {
                contentDiv.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Request Timeout</h3>
                        <p>The request is taking too long. Please check your connection and try again.</p>
                        <button onclick="loadLeads(${page})" style="margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
                    </div>
                `;
            }
        }, 30000); // 30 second timeout
        
        let result;
        try {
            console.log('Calling apiCall...');
            result = await apiCall(endpoint);
            clearTimeout(timeoutId);
            console.log('=== API CALL COMPLETED ===');
            console.log('Result:', result);
            console.log('Result type:', typeof result);
            console.log('Result success:', result?.success);
            console.log('Result data:', result?.data);
        } catch (error) {
            clearTimeout(timeoutId);
            console.error('=== ERROR IN LOADLEADS ===');
            console.error('Error:', error);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Leads</h3>
                    <p>An error occurred: ${error.message}</p>
                    <p style="font-size: 12px; color: #999; margin-top: 8px;">Please check the browser console (F12) for details.</p>
                    <button onclick="loadLeads(${page})" style="margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
                </div>
            `;
            return;
        }

        if (!result) {
            console.error('=== NO RESULT RETURNED ===');
            console.error('Result is null or undefined');
            // Check if token exists
            const token = getToken();
            if (!token) {
                contentDiv.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-lock"></i>
                        <h3>Authentication Required</h3>
                        <p>You need to login to view leads.</p>
                        <p style="font-size: 12px; color: #999; margin-top: 8px;">Redirecting to login page...</p>
                        <a href="{{ route('login') }}" style="margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;">Go to Login</a>
                    </div>
                `;
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 2000);
            } else {
                contentDiv.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Error Loading Leads</h3>
                        <p>No response from server. Please check your connection.</p>
                        <p style="font-size: 12px; color: #999; margin-top: 8px;">Check browser console (F12) for details.</p>
                        <button onclick="loadLeads(${page})" style="margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
                    </div>
                `;
            }
            return;
        }

        console.log('=== CHECKING RESULT ===');
        console.log('Result success:', result.success);
        console.log('Result data:', result.data);
        console.log('Result message:', result.message);

        if (result.success === false || !result.success) {
            console.error('=== API RETURNED ERROR ===');
            console.error('Error result:', result);
            const errorMsg = result.message || result.error || 'Failed to load leads. Please try again.';
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Leads</h3>
                    <p>${errorMsg}</p>
                    <p style="font-size: 12px; color: #999; margin-top: 8px;">Response: ${JSON.stringify(result).substring(0, 200)}...</p>
                    <button onclick="loadLeads(${page})" style="margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
                </div>
            `;
            return;
        }

        const leads = result.data || [];
        const pagination = result.pagination || {};

        console.log('Leads count:', leads.length); // Debug log
        console.log('Leads data:', leads); // Debug log
        console.log('Pagination:', pagination); // Debug log
        console.log('Full result:', result); // Debug log

        if (!Array.isArray(leads)) {
            console.error('Leads is not an array:', leads);
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Data Format Error</h3>
                    <p>Invalid response format. Check console for details.</p>
                    <p style="font-size: 12px; color: #999; margin-top: 8px;">Response type: ${typeof leads}</p>
                </div>
            `;
            return;
        }

        if (leads.length === 0) {
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-user-friends"></i>
                    <h3>No Leads Found</h3>
                    <p>You don't have any assigned leads at the moment.</p>
                    <p style="font-size: 12px; color: #999; margin-top: 8px;">Total in system: ${pagination.total || 0}</p>
                </div>
            `;
            return;
        }

        // Build cards
        let cardsHTML = '';

        leads.forEach(lead => {
            const assignedDate = lead.assigned_at ? formatDate(lead.assigned_at) : '-';
            const lastContacted = lead.last_contacted_at ? formatDate(lead.last_contacted_at) : '-';
            const location = lead.city ? `${lead.city}${lead.state ? ', ' + lead.state : ''}` : '-';
            const statusClass = `status-${lead.status}`;
            const initial = lead.name ? lead.name.charAt(0).toUpperCase() : 'L';

            cardsHTML += `
                <div class="lead-card">
                    <div class="lead-card-header">
                        <div class="lead-avatar">${initial}</div>
                        <h3 class="lead-name">${lead.name || '-'}</h3>
                    </div>
                    <div class="lead-info">
                        <div class="lead-info-row">
                            <i class="fas fa-phone"></i>
                            <span class="lead-info-value">${lead.phone || '-'}</span>
                        </div>
                        ${lead.email ? `
                        <div class="lead-info-row">
                            <i class="fas fa-envelope"></i>
                            <span class="lead-info-value">${lead.email}</span>
                        </div>
                        ` : ''}
                        <div class="lead-info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="lead-info-value">${location}</span>
                        </div>
                        <div class="lead-info-row">
                            <i class="fas fa-calendar"></i>
                            <span class="lead-info-value">Assigned: ${assignedDate}</span>
                        </div>
                        ${lastContacted !== '-' ? `
                        <div class="lead-info-row">
                            <i class="fas fa-clock"></i>
                            <span class="lead-info-value">Last Contacted: ${lastContacted}</span>
                        </div>
                        ` : ''}
                        <span class="status-badge ${statusClass}">${formatStatus(lead.status)}</span>
                    </div>
                    <div class="lead-card-footer">
                        <button class="lead-card-btn btn-call" onclick="initiateCall(${lead.id}, ${JSON.stringify(lead.phone || '')})">
                            <i class="fas fa-phone"></i>
                            Call
                        </button>
                        <button class="lead-card-btn btn-whatsapp" onclick="openWhatsApp(${lead.id}, ${JSON.stringify(lead.phone || '')})">
                            <i class="fab fa-whatsapp"></i>
                            WhatsApp
                        </button>
                    </div>
                </div>
            `;
        });

        // Add pagination
        if (pagination.last_page > 1) {
            cardsHTML += `
                <div class="pagination">
                    <button onclick="loadLeads(${pagination.current_page - 1})" ${pagination.current_page === 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="page-info">
                        Page ${pagination.current_page} of ${pagination.last_page} (${pagination.total} total)
                    </div>
                    <button onclick="loadLeads(${pagination.current_page + 1})" ${pagination.current_page === pagination.last_page ? 'disabled' : ''}>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            `;
        }

        contentDiv.innerHTML = cardsHTML;
    }

    // Handle search with debounce
    function handleSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadLeads(1);
        }, 500);
    }

    // Call button functions
    function initiateCall(leadId, phoneNumber) {
        console.log('Initiating call for lead:', leadId, 'Phone:', phoneNumber);
        if (phoneNumber && phoneNumber !== '-') {
            // Open tel: link to initiate call
            window.location.href = `tel:${phoneNumber}`;
            // You can also add API call here to log the call initiation
            // apiCall(`/leads/${leadId}/call`, { method: 'POST' });
        } else {
            alert('Phone number not available for this lead.');
        }
    }

    function openWhatsApp(leadId, phoneNumber) {
        console.log('Opening WhatsApp for lead:', leadId, 'Phone:', phoneNumber);
        if (phoneNumber && phoneNumber !== '-') {
            // Remove any non-digit characters except + for international format
            const cleanPhone = phoneNumber.replace(/[^\d+]/g, '');
            // Open WhatsApp with the phone number
            const whatsappUrl = `https://wa.me/${cleanPhone}`;
            window.open(whatsappUrl, '_blank');
        } else {
            alert('Phone number not available for this lead.');
        }
    }

    // Initialize on page load
    console.log('Leads script loaded');
    
    function initializeLeads() {
        console.log('Initializing leads page...');
        const contentDiv = document.getElementById('leadsContent');
        if (!contentDiv) {
            console.error('leadsContent element not found, retrying...');
            setTimeout(initializeLeads, 100);
            return;
        }
        console.log('leadsContent found, loading leads...');
        loadLeads();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeLeads);
    } else {
        // DOM already loaded
        setTimeout(initializeLeads, 100);
    }
</script>
@endpush

