@extends('telecaller.layout')

@section('title', 'Verification Pending - Telecaller')
@section('page-title', 'Verification Pending')

@push('styles')
<style>
    .verification-container {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    .prospects-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    @media (max-width: 1024px) {
        .prospects-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 768px) {
        .prospects-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
    }
    @media (max-width: 480px) {
        .prospects-grid {
            grid-template-columns: 1fr;
        }
    }
    .prospect-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
    }
    .prospect-card:hover {
        border-color: #205A44;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    .prospect-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .prospect-name {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin: 0;
    }
    .prospect-info {
        margin-bottom: 12px;
    }
    .prospect-info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 14px;
        color: #063A1C;
    }
    .prospect-info-row i {
        color: #205A44;
        width: 16px;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        margin-top: 8px;
    }
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .prospect-footer {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #f0f0f0;
        font-size: 12px;
        color: #B3B5B4;
    }
    .prospect-footer-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
    }
    .btn-whatsapp-prospect {
        width: 100%;
        padding: 8px;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        border: none;
        box-shadow: 0 2px 4px rgba(21, 128, 61, 0.3);
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 12px;
        transition: all 0.3s;
    }
    .btn-whatsapp-prospect:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        box-shadow: 0 4px 8px rgba(21, 128, 61, 0.4);
        transform: translateY(-1px);
    }
    .loading-state, .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #B3B5B4;
    }
    .loading-state i, .empty-state i {
        font-size: 48px;
        color: #205A44;
        margin-bottom: 16px;
    }
    .empty-state h3 {
        font-size: 24px;
        margin-bottom: 8px;
        color: #063A1C;
    }
    .rejection-reason {
        margin-top: 12px;
        padding: 12px;
        background: #fee2e2;
        border-left: 4px solid #ef4444;
        border-radius: 4px;
        font-size: 13px;
        color: #991b1b;
    }
</style>
@endpush

@section('content')
    <div class="verification-container">
        <div class="filter-bar">
            <button class="filter-btn active" onclick="filterProspects('pending')">Pending</button>
            <button class="filter-btn" onclick="filterProspects('approved')">Approved</button>
            <button class="filter-btn" onclick="filterProspects('rejected')">Rejected</button>
            <button class="filter-btn" onclick="filterProspects('all')">All</button>
        </div>

        <div id="prospectsContent" class="prospects-grid">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading prospects...</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    var API_BASE_URL = '{{ url("/api/telecaller") }}';
    let currentStatus = 'pending';

    function getToken() {
        return localStorage.getItem('telecaller_token');
    }

    async function apiCall(endpoint, options = {}) {
        const token = getToken();
        if (!token) {
            console.error('No token found, redirecting to login');
            setTimeout(() => {
                window.location.href = '{{ route("login") }}';
            }, 2000);
            return { success: false, message: 'Authentication required. Redirecting to login...' };
        }

        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        };

        const url = `${API_BASE_URL}${endpoint}`;
        console.log('Making API call to:', url);

        try {
            const fetchOptions = {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers },
            };
            
            fetchOptions.method = options.method || 'GET';
            
            if (options.body && (fetchOptions.method === 'POST' || fetchOptions.method === 'PUT' || fetchOptions.method === 'PATCH')) {
                fetchOptions.body = typeof options.body === 'string' ? options.body : JSON.stringify(options.body);
            }
            
            const response = await fetch(url, fetchOptions);

            console.log('Response status:', response.status);

            if (response.status === 401) {
                console.error('Unauthorized - clearing token');
                localStorage.removeItem('telecaller_token');
                localStorage.removeItem('telecaller_user');
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 2000);
                return { success: false, message: 'Session expired. Redirecting to login...' };
            }

            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error Response:', errorText);
                try {
                    const errorJson = JSON.parse(errorText);
                    return { success: false, message: errorJson.message || errorText };
                } catch (e) {
                    return { success: false, message: errorText || `HTTP ${response.status}: ${response.statusText}` };
                }
            }

            const data = await response.json();
            console.log('API Success Response:', data);
            return data;
        } catch (error) {
            console.error('API Call Error:', error);
            return { success: false, message: error.message || 'Network error. Please check your connection.' };
        }
    }

    async function loadProspects(status = 'pending') {
        currentStatus = status;
        const contentDiv = document.getElementById('prospectsContent');
        if (!contentDiv) {
            console.error('prospectsContent element not found');
            return;
        }
        contentDiv.className = 'prospects-grid';
        contentDiv.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading prospects...</p></div>';

        const endpoint = `/prospects?status=${status}&per_page=20`;
        console.log('Loading prospects from:', API_BASE_URL + endpoint);
        
        const result = await apiCall(endpoint);
        console.log('API Result:', result);

        if (!result) {
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Prospects</h3>
                    <p>No response from server. Please check your connection.</p>
                    <button onclick="loadProspects('${status}')" style="margin-top: 16px; padding: 10px 20px; background: #205A44; color: white; border: none; border-radius: 8px; cursor: pointer;">Retry</button>
                </div>
            `;
            return;
        }

        if (!result.success) {
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Prospects</h3>
                    <p>${result.message || 'Failed to load prospects'}</p>
                    <button onclick="loadProspects('${status}')" style="margin-top: 16px; padding: 10px 20px; background: #205A44; color: white; border: none; border-radius: 8px; cursor: pointer;">Retry</button>
                </div>
            `;
            return;
        }

        const prospects = result.data || [];

        if (prospects.length === 0) {
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>No Prospects Found</h3>
                    <p>You don't have any ${status === 'all' ? '' : status} prospects at the moment.</p>
                </div>
            `;
            return;
        }

        let cardsHTML = '';
        prospects.forEach(prospect => {
            const statusClass = `status-${prospect.verification_status}`;
            const statusText = formatStatus(prospect.verification_status);
            const createdDate = formatDate(prospect.created_at);
            const verifiedDate = prospect.verified_at ? formatDate(prospect.verified_at) : null;

            cardsHTML += `
                <div class="prospect-card">
                    <div class="prospect-header">
                        <h3 class="prospect-name">${prospect.customer_name || '-'}</h3>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="prospect-info">
                        <div class="prospect-info-row">
                            <i class="fas fa-phone"></i>
                            <span>${prospect.phone || '-'}</span>
                        </div>
                        ${prospect.budget ? `
                        <div class="prospect-info-row">
                            <i class="fas fa-rupee-sign"></i>
                            <span>Budget: ${prospect.budget}</span>
                        </div>
                        ` : ''}
                        ${prospect.preferred_location ? `
                        <div class="prospect-info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Location: ${prospect.preferred_location}</span>
                        </div>
                        ` : ''}
                        ${prospect.size ? `
                        <div class="prospect-info-row">
                            <i class="fas fa-ruler-combined"></i>
                            <span>Size: ${prospect.size}</span>
                        </div>
                        ` : ''}
                        <div class="prospect-info-row">
                            <i class="fas fa-tag"></i>
                            <span>Purpose: ${prospect.purpose === 'end_user' ? 'End User' : 'Investment'}</span>
                        </div>
                        ${prospect.remark ? `
                        <div class="prospect-info-row" style="margin-top: 12px;">
                            <i class="fas fa-comment"></i>
                            <span style="font-style: italic; color: #B3B5B4;">${prospect.remark}</span>
                        </div>
                        ` : ''}
                        ${prospect.manager_remark ? `
                        <div class="prospect-info-row" style="margin-top: 12px; padding: 12px; background: #d1fae5; border-left: 4px solid #10b981; border-radius: 4px;">
                            <i class="fas fa-user-tie" style="color: #065f46;"></i>
                            <div>
                                <strong style="color: #065f46; display: block; margin-bottom: 4px;">Manager Remark:</strong>
                                <span style="color: #065f46;">${prospect.manager_remark}</span>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    <div class="prospect-footer">
                        ${prospect.verification_status === 'pending' ? `
                            <div class="prospect-footer-row">
                                <span><i class="fas fa-user-tie"></i> Sent to:</span>
                                <span style="font-weight: 600;">${prospect.manager_name || 'Not Assigned'}</span>
                            </div>
                            <div class="prospect-footer-row">
                                <span><i class="fas fa-calendar"></i> Sent on:</span>
                                <span>${createdDate}</span>
                            </div>
                        ` : ''}
                        ${prospect.verification_status === 'approved' ? `
                            <div class="prospect-footer-row">
                                <span><i class="fas fa-check-circle"></i> Verified by:</span>
                                <span style="font-weight: 600; color: #10b981;">${prospect.verified_by_name || 'Manager'}</span>
                            </div>
                            <div class="prospect-footer-row">
                                <span><i class="fas fa-calendar"></i> Verified on:</span>
                                <span>${verifiedDate || '-'}</span>
                            </div>
                        ` : ''}
                        ${prospect.verification_status === 'rejected' ? `
                            <div class="prospect-footer-row">
                                <span><i class="fas fa-times-circle"></i> Rejected by:</span>
                                <span style="font-weight: 600; color: #ef4444;">${prospect.verified_by_name || 'Manager'}</span>
                            </div>
                            <div class="prospect-footer-row">
                                <span><i class="fas fa-calendar"></i> Rejected on:</span>
                                <span>${verifiedDate || '-'}</span>
                            </div>
                            ${prospect.rejection_reason ? `
                            <div class="rejection-reason">
                                <strong>Reason:</strong> ${prospect.rejection_reason}
                            </div>
                            ` : ''}
                        ` : ''}
                    </div>
                    <button class="btn-whatsapp-prospect" onclick="openWhatsApp('${prospect.phone || ''}')">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp
                    </button>
                </div>
            `;
        });

        contentDiv.innerHTML = cardsHTML;
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function formatStatus(status) {
        const statusMap = {
            'pending': 'Pending Verification',
            'approved': 'Approved',
            'rejected': 'Rejected',
        };
        return statusMap[status] || status;
    }

    function filterProspects(status) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        loadProspects(status);
    }

    function openWhatsApp(phoneNumber) {
        if (!phoneNumber || phoneNumber === '-') {
            alert('Phone number not available for this prospect.');
            return;
        }
        const cleanedPhoneNumber = phoneNumber.replace(/[^\d+]/g, '');
        if (!cleanedPhoneNumber) {
            alert('Invalid phone number for WhatsApp.');
            return;
        }
        window.open(`https://wa.me/${cleanedPhoneNumber}`, '_blank');
    }

    // Initialize on page load
    function initializeProspects() {
        console.log('Verification Pending page loaded, initializing...');
        console.log('API_BASE_URL:', API_BASE_URL);
        console.log('Token:', getToken() ? 'Exists' : 'Missing');
        
        const contentDiv = document.getElementById('prospectsContent');
        if (contentDiv) {
            loadProspects('pending');
        } else {
            console.error('prospectsContent element not found, retrying...');
            setTimeout(initializeProspects, 200);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeProspects);
    } else {
        // DOM already loaded
        initializeProspects();
    }
</script>
@endpush

