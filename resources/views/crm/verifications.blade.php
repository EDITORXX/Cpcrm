@if(auth()->user()->isAdmin() || auth()->user()->isCrm())
    @extends('layouts.app')
@elseif(auth()->user()->isSalesHead())
    @extends('sales-head.layout')
@else
    @extends('layouts.app')
@endif

@section('title', 'Verifications - CRM')
@section('page-title', 'Pending Verifications')

@push('styles')
<style>
    .verification-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #f59e0b;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.3s ease;
    }
    .verification-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .card-detail-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
        gap: 8px;
        font-size: 14px;
    }
    .btn-view-details {
        width: 100%;
        padding: 10px 16px;
        background: #063A1C;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-view-details:hover {
        background: #205A44;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .prospects-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        padding: 20px 0;
    }
    @media (max-width: 1400px) {
        .prospects-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 1024px) {
        .prospects-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 768px) {
        .prospects-grid {
            grid-template-columns: 1fr;
        }
    }
    .verification-card.verified {
        border-left-color: #10b981;
    }
    .verification-card.rejected {
        border-left-color: #ef4444;
    }
    .verification-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 16px;
    }
    .verification-info h3 {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin-bottom: 8px;
    }
    .verification-info p {
        color: #6b7280;
        font-size: 14px;
        margin: 4px 0;
    }
    .verification-actions {
        display: flex;
        gap: 10px;
    }
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background: #10b981;
        color: white;
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        background: #059669;
    }
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    .btn-danger:hover {
        background: #dc2626;
    }
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-verified {
        background: #d1fae5;
        color: #065f46;
    }
    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
    }
    .tab {
        padding: 12px 24px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
    }
    .tab.active {
        color: #205A44;
        border-bottom-color: #205A44;
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal.show {
        display: flex;
    }
    .modal-content {
        background: white;
        padding: 24px;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        position: relative;
    }
    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .photo-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        aspect-ratio: 1;
    }
    .photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .photo-item:hover img {
        transform: scale(1.05);
    }
    .detail-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #374151;
        min-width: 180px;
    }
    .detail-value {
        color: #6b7280;
        flex: 1;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        min-height: 100px;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
    }
    .details-modal {
        max-width: 900px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .details-section {
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    .details-section:last-child {
        border-bottom: none;
    }
    .details-section h4 {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin-bottom: 16px;
    }
    .detail-row {
        display: flex;
        margin-bottom: 12px;
        align-items: start;
    }
    .detail-label {
        font-weight: 600;
        color: #6b7280;
        min-width: 150px;
        margin-right: 16px;
    }
    .detail-value {
        flex: 1;
        color: #333;
    }
    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 16px;
        margin-top: 12px;
    }
    .photo-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        aspect-ratio: 1;
    }
    .photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .photo-item:hover img {
        transform: scale(1.05);
    }
    .btn-primary {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        color: white;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        background: #2563eb;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="tabs">
        <button class="tab active" onclick="switchTab('prospects', event)">
            <i class="fas fa-user-check mr-2"></i>Prospects
            <span class="badge badge-pending" id="prospectsCount">0</span>
        </button>
        <button class="tab" onclick="switchTab('meetings', event)">
            <i class="fas fa-handshake mr-2"></i>Meetings
            <span class="badge badge-pending" id="meetingsCount">0</span>
        </button>
        <button class="tab" onclick="switchTab('site-visits', event)">
            <i class="fas fa-map-marker-alt mr-2"></i>Site Visits
            <span class="badge badge-pending" id="visitsCount">0</span>
        </button>
        <button class="tab" onclick="switchTab('closers', event)">
            <i class="fas fa-trophy mr-2"></i>Closer Requests
            <span class="badge badge-pending" id="closersCount">0</span>
        </button>
    </div>

    <!-- Prospects Tab (Read-only for CRM/Admin) -->
    <div id="prospectsTab" class="tab-content">
        <div style="background: #fef3c7; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; color: #92400e;">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Note:</strong> Prospects can only be verified by their respective Sales Managers. This is a read-only view.
            </p>
        </div>
        <div id="prospectsContainer">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading prospects...</p>
            </div>
        </div>
    </div>

    <!-- Meetings Tab -->
    <div id="meetingsTab" class="tab-content" style="display: none;">
        <div id="meetingsContainer">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading meetings...</p>
            </div>
        </div>
    </div>

    <!-- Site Visits Tab -->
    <div id="siteVisitsTab" class="tab-content" style="display: none;">
        <div id="siteVisitsContainer">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading site visits...</p>
            </div>
        </div>
    </div>

    <!-- Closer Requests Tab -->
    <div id="closersTab" class="tab-content" style="display: none;">
        <div id="closersContainer">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading closer requests...</p>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">Reject Verification</h3>
        <form id="rejectForm">
            <div class="form-group">
                <label for="rejectionReason">Rejection Reason <span style="color: #ef4444;">*</span></label>
                <textarea id="rejectionReason" name="reason" required placeholder="Enter reason for rejection..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>

<!-- Verify Site Visit Modal -->
<div id="verifySiteVisitModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">Verify Site Visit</h3>
        <div class="form-group">
            <label for="verifySiteVisitLeadStatus">Lead Status <span style="color: #ef4444;">*</span></label>
            <select id="verifySiteVisitLeadStatus" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Lead Status</option>
                <option value="hot">Hot</option>
                <option value="warm">Warm</option>
                <option value="cold">Cold</option>
                <option value="junk">Junk</option>
            </select>
            <small style="color: #6b7280; font-size: 12px;">Classify this lead based on their interest level.</small>
        </div>
        <div class="form-group">
            <label for="verifySiteVisitNotes">Notes (Optional)</label>
            <textarea id="verifySiteVisitNotes" placeholder="Enter any additional notes..." rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeVerifySiteVisitModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="verifySiteVisit()">Verify</button>
        </div>
    </div>
</div>

<!-- Verify Closer Modal -->
<div id="verifyCloserModal" class="modal">
    <div class="modal-content">
        <h3 class="text-xl font-bold mb-4">Verify Closer</h3>
        <div class="form-group">
            <label for="verifyCloserLeadStatus">Lead Status <span style="color: #ef4444;">*</span></label>
            <select id="verifyCloserLeadStatus" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Lead Status</option>
                <option value="hot">Hot</option>
                <option value="warm">Warm</option>
                <option value="cold">Cold</option>
                <option value="junk">Junk</option>
            </select>
            <small style="color: #6b7280; font-size: 12px;">Classify this lead based on their interest level.</small>
        </div>
        <div class="form-group">
            <label for="verifyCloserNotes">Notes (Optional)</label>
            <textarea id="verifyCloserNotes" placeholder="Enter any additional notes..." rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeVerifyCloserModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="verifyCloser()">Verify</button>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div id="detailsModal" class="modal">
    <div class="modal-content" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="detailsModalTitle" style="font-size: 24px; font-weight: 600; color: #063A1C;">View Details</h3>
            <button onclick="closeDetailsModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
        </div>
        <div id="detailsModalContent">
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #6b7280;"></i>
                <p style="margin-top: 16px; color: #6b7280;">Loading details...</p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; padding-top: 24px; border-top: 2px solid #e5e7eb;">
            <button type="button" class="btn btn-secondary" onclick="closeDetailsModal()">Close</button>
            <button type="button" class="btn btn-danger" id="detailsModalRejectBtn" style="display: none;" onclick="rejectFromDetails()">Reject</button>
            <button type="button" class="btn btn-success" id="detailsModalVerifyBtn" style="display: none;" onclick="verifyFromDetails()">Verify</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/crm") }}';
    let currentType = 'meetings';
    let currentItemId = null;

    function getToken() {
        // Try multiple sources for token
        const tokenFromBlade = '{{ $api_token ?? "" }}';
        const tokenFromLocalStorage = localStorage.getItem('crm_token');
        const tokenFromSession = '{{ session("api_token") ?? "" }}';
        
        if (tokenFromBlade) {
            localStorage.setItem('crm_token', tokenFromBlade); // Store for future use
            return tokenFromBlade;
        }
        if (tokenFromLocalStorage) {
            return tokenFromLocalStorage;
        }
        if (tokenFromSession) {
            localStorage.setItem('crm_token', tokenFromSession); // Store for future use
            return tokenFromSession;
        }
        
        console.error('No API token found! Please refresh the page or log in again.');
        return null;
    }

    /**
     * Format duration in seconds to human-readable format
     * @param {number} seconds - Duration in seconds
     * @param {boolean} isPending - Whether the prospect is pending verification
     * @returns {string} Formatted duration string
     */
    function formatDuration(seconds, isPending) {
        if (!seconds || seconds < 0) {
            return isPending ? 'Awaiting Response: < 1m' : 'N/A';
        }

        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        let durationStr = '';
        const prefix = isPending ? 'Awaiting Response: ' : 'Response Time: ';

        if (days > 0) {
            durationStr = `${days}d`;
            if (hours > 0) {
                durationStr += ` ${hours}h`;
            }
            if (minutes > 0 && hours === 0) {
                durationStr += ` ${minutes}m`;
            }
        } else if (hours > 0) {
            durationStr = `${hours}h`;
            if (minutes > 0) {
                durationStr += ` ${minutes}m`;
            }
        } else if (minutes > 0) {
            durationStr = `${minutes}m`;
        } else {
            durationStr = '< 1m';
        }

        return prefix + durationStr;
    }

    // Render star rating
    function renderStarRating(rating) {
        if (!rating || rating < 1 || rating > 5) {
            return '<span style="color: #9ca3af;">No rating</span>';
        }
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<span style="color: #fbbf24; font-size: 16px;">★</span>';
            } else {
                stars += '<span style="color: #d1d5db; font-size: 16px;">☆</span>';
            }
        }
        return stars;
    }

    async function apiCall(endpoint, options = {}) {
        const token = getToken();
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return null;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers },
                credentials: 'same-origin',
            });

            if (response.status === 401) {
                window.location.href = '{{ route("login") }}';
                return null;
            }

            if (!response.ok) {
                const errorText = await response.text();
                try {
                    return JSON.parse(errorText);
                } catch (e) {
                    return { success: false, message: errorText };
                }
            }

            return await response.json();
        } catch (error) {
            console.error('API Call Error:', error);
            return { success: false, message: error.message };
        }
    }

    function switchTab(tab, evt) {
        currentType = tab;
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.getElementById('prospectsTab').style.display = tab === 'prospects' ? 'block' : 'none';
        document.getElementById('meetingsTab').style.display = tab === 'meetings' ? 'block' : 'none';
        document.getElementById('siteVisitsTab').style.display = tab === 'site-visits' ? 'block' : 'none';
        document.getElementById('closersTab').style.display = tab === 'closers' ? 'block' : 'none';
        
        if (evt && evt.target) {
            evt.target.closest('.tab').classList.add('active');
        } else {
            // Fallback: find and activate the correct tab
            document.querySelectorAll('.tab').forEach(t => {
                const tabText = t.textContent.toLowerCase();
                if ((tab === 'prospects' && tabText.includes('prospect')) ||
                    (tab === 'meetings' && tabText.includes('meeting')) ||
                    (tab === 'site-visits' && tabText.includes('site')) ||
                    (tab === 'closers' && tabText.includes('closer'))) {
                    t.classList.add('active');
                }
            });
        }
        
        // Load content immediately when tab is switched
        if (tab === 'prospects') {
            loadProspects();
        } else if (tab === 'meetings') {
            loadMeetings();
        } else if (tab === 'site-visits') {
            loadSiteVisits();
        } else if (tab === 'closers') {
            loadClosers();
        }
    }

    async function loadProspects() {
        const container = document.getElementById('prospectsContainer');
        container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading prospects...</p></div>';

        try {
            // Load all prospects (pending and verified) for CRM with pagination
            const response = await apiCall('/verifications/pending-prospects?per_page=100');
            
            // Handle paginated response
            const allProspects = response?.data || (Array.isArray(response) ? response : []);
            // Filter to show only pending and verified prospects (exclude rejected)
            const prospects = allProspects.filter(p => 
                p.verification_status === 'pending' || 
                p.verification_status === 'pending_verification' ||
                p.verification_status === 'verified' ||
                p.verification_status === 'approved'
            );
            
            // Update count from response total if available, otherwise use filtered length
            const totalCount = response?.total || prospects.length;
            document.getElementById('prospectsCount').textContent = totalCount;

            if (prospects.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3 style="font-size: 18px; font-weight: 600; color: #333; margin: 16px 0 8px;">No Prospects Found</h3>
                        <p>No prospects available to display.</p>
                    </div>
                `;
                return;
            }

            const html = prospects.map(prospect => {
                const statusClass = prospect.verification_status === 'verified' || prospect.verification_status === 'approved' ? 'verified' : 
                                  prospect.verification_status === 'rejected' ? 'rejected' : '';
                const statusBadge = prospect.verification_status === 'verified' || prospect.verification_status === 'approved' ? 'badge-verified' :
                                  prospect.verification_status === 'rejected' ? 'badge-rejected' :
                                  'badge-pending';
                const statusText = prospect.verification_status === 'verified' || prospect.verification_status === 'approved' ? 'Verified' :
                                 prospect.verification_status === 'rejected' ? 'Rejected' :
                                 'Pending';

                // Calculate duration
                let durationDisplay = '';
                const isPending = prospect.verification_status === 'pending' || prospect.verification_status === 'pending_verification';
                
                if (prospect.created_at) {
                    const createdDate = new Date(prospect.created_at);
                    let endDate;
                    
                    if (isPending) {
                        // For pending: time elapsed from creation to now
                        endDate = new Date();
                    } else if (prospect.verified_at) {
                        // For verified/rejected: time from creation to verification
                        endDate = new Date(prospect.verified_at);
                    }
                    
                    if (endDate) {
                        const seconds = Math.floor((endDate - createdDate) / 1000);
                        durationDisplay = formatDuration(seconds, isPending);
                    }
                }

                return `
                    <div class="verification-card ${statusClass}">
                        <div class="verification-info" style="flex: 1;">
                            <h3 style="font-size: 18px; font-weight: 600; color: #063A1C; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">${prospect.customer_name || 'N/A'}</h3>
                            ${prospect.lead_score ? `
                            <div class="card-detail-row" style="margin-bottom: 8px;">
                                <i class="fas fa-star" style="color: #fbbf24; width: 20px;"></i>
                                <span style="color: #374151;">Lead Score: ${renderStarRating(prospect.lead_score)} <span style="color: #6b7280; font-size: 12px;">(${prospect.lead_score}/5)</span></span>
                            </div>
                            ` : ''}
                            <div class="card-detail-row">
                                <i class="fas fa-phone" style="color: #6b7280; width: 20px;"></i>
                                <span style="color: #374151;">${prospect.phone || 'N/A'}</span>
                            </div>
                            <div class="card-detail-row">
                                <i class="fas fa-user" style="color: #6b7280; width: 20px;"></i>
                                <span style="color: #374151;">Telecaller: ${prospect.telecaller?.name || prospect.createdBy?.name || 'N/A'}</span>
                            </div>
                            <div class="card-detail-row">
                                <i class="fas fa-user-tie" style="color: #6b7280; width: 20px;"></i>
                                <span style="color: #374151;">Manager: ${prospect.assignedManager?.name || prospect.manager?.name || 'Not Assigned'}</span>
                            </div>
                            ${prospect.budget ? `
                            <div class="card-detail-row">
                                <i class="fas fa-rupee-sign" style="color: #6b7280; width: 20px;"></i>
                                <span style="color: #374151;">Budget: ₹${prospect.budget.toLocaleString()}</span>
                            </div>` : ''}
                            ${prospect.preferred_location ? `
                            <div class="card-detail-row">
                                <i class="fas fa-map-marker-alt" style="color: #6b7280; width: 20px;"></i>
                                <span style="color: #374151;">${prospect.preferred_location}</span>
                            </div>` : ''}
                            ${prospect.manager_remark ? `
                            <div class="card-detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                                <strong style="color: #374151;">Manager Remark:</strong>
                                <span style="color: #6b7280; margin-left: 8px;">${prospect.manager_remark}</span>
                            </div>` : ''}
                            ${prospect.rejection_reason ? `
                            <div class="card-detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                                <strong style="color: #ef4444;">Rejection Reason:</strong>
                                <span style="color: #ef4444; margin-left: 8px;">${prospect.rejection_reason}</span>
                            </div>` : ''}
                            ${prospect.verified_at ? `
                            <div class="card-detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                                <i class="fas fa-check-circle" style="color: #10b981; width: 20px;"></i>
                                <span style="color: #10b981; font-size: 12px;">Verified: ${new Date(prospect.verified_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                            </div>` : ''}
                            ${durationDisplay ? `
                            <div class="card-detail-row">
                                <i class="fas fa-clock" style="color: #6b7280; width: 20px;"></i>
                                <span style="color: #6b7280; font-size: 13px;">${durationDisplay}</span>
                            </div>` : ''}
                            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                                <span class="badge ${statusBadge}">${statusText}</span>
                            </div>
                        </div>
                        <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                            <button class="btn-view-details" onclick="showDetailsModal('prospect', ${prospect.id})">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = `<div class="prospects-grid">${html}</div>`;
        } catch (error) {
            console.error('Error loading prospects:', error);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading prospects</p></div>';
        }
    }

    async function loadMeetings() {
        const container = document.getElementById('meetingsContainer');
        container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';

        try {
            // Get pending meetings from admin endpoint - only pending verification
            const response = await fetch('{{ url("/api/admin/verifications/pending") }}', {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            // Meetings are already filtered by API to have verification_status = 'pending' and status = 'completed'
            const meetings = data.meetings || [];

            document.getElementById('meetingsCount').textContent = meetings.length;
            // Removed console.log for performance

            if (meetings.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3 style="font-size: 18px; font-weight: 600; color: #333; margin: 16px 0 8px;">No Pending Meetings</h3>
                        <p>All meetings have been verified.</p>
                    </div>
                `;
                return;
            }

            const html = meetings.map(meeting => `
                <div class="verification-card">
                    <div class="verification-info" style="flex: 1;">
                        <h3 style="font-size: 18px; font-weight: 600; color: #063A1C; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">${meeting.customer_name || 'N/A'}</h3>
                        <div class="card-detail-row">
                            <i class="fas fa-phone" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">${meeting.phone || 'N/A'}</span>
                        </div>
                        <div class="card-detail-row">
                            <i class="fas fa-calendar" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">${new Date(meeting.scheduled_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                        <div class="card-detail-row">
                            <i class="fas fa-user" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Created by: ${meeting.creator?.name || 'N/A'}</span>
                        </div>
                        ${meeting.budget_range ? `
                        <div class="card-detail-row">
                            <i class="fas fa-tag" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Budget: ${meeting.budget_range}</span>
                        </div>` : ''}
                        ${meeting.property_type ? `
                        <div class="card-detail-row">
                            <i class="fas fa-building" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Property: ${meeting.property_type}</span>
                        </div>` : ''}
                        ${meeting.meeting_notes ? `
                        <div class="card-detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                            <strong style="color: #374151;">Notes:</strong>
                            <span style="color: #6b7280; margin-left: 8px;">${meeting.meeting_notes.substring(0, 100)}${meeting.meeting_notes.length > 100 ? '...' : ''}</span>
                        </div>` : ''}
                    </div>
                    <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; flex-direction: column; gap: 8px;">
                        <button class="btn-view-details" onclick="showDetailsModal('meeting', ${meeting.id})">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </button>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-success" style="flex: 1; padding: 8px 12px; font-size: 13px;" onclick="verifyMeeting(${meeting.id})">
                                <i class="fas fa-check mr-2"></i>Verify
                            </button>
                            <button class="btn btn-danger" style="flex: 1; padding: 8px 12px; font-size: 13px;" onclick="showRejectModal('meeting', ${meeting.id})">
                                <i class="fas fa-times mr-2"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = `<div class="prospects-grid">${html}</div>`;
        } catch (error) {
            console.error('Error loading meetings:', error);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading meetings</p></div>';
        }
    }

    async function loadSiteVisits() {
        const container = document.getElementById('siteVisitsContainer');
        container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';

        try {
            const response = await fetch('{{ url("/api/admin/verifications/pending") }}', {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            // Only show site visits with pending verification (not closer)
            const siteVisits = (data.site_visits || []).filter(sv => 
                sv.verification_status === 'pending' && sv.status === 'completed' && sv.closer_status !== 'pending'
            );

                document.getElementById('visitsCount').textContent = siteVisits.length;

            if (siteVisits.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3 style="font-size: 18px; font-weight: 600; color: #333; margin: 16px 0 8px;">No Pending Site Visits</h3>
                        <p>All site visits have been verified.</p>
                    </div>
                `;
                return;
            }

            const html = siteVisits.map(visit => `
                <div class="verification-card">
                    <div class="verification-info" style="flex: 1;">
                        <h3 style="font-size: 18px; font-weight: 600; color: #063A1C; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">${visit.customer_name || visit.lead?.name || 'N/A'}</h3>
                        <div class="card-detail-row">
                            <i class="fas fa-phone" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">${visit.phone || visit.lead?.phone || 'N/A'}</span>
                        </div>
                        <div class="card-detail-row">
                            <i class="fas fa-calendar" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">${new Date(visit.scheduled_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                        <div class="card-detail-row">
                            <i class="fas fa-user" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Created by: ${visit.creator?.name || 'N/A'}</span>
                        </div>
                        ${visit.property_name ? `
                        <div class="card-detail-row">
                            <i class="fas fa-map-marker-alt" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Property: ${visit.property_name}</span>
                        </div>` : ''}
                        ${visit.budget_range ? `
                        <div class="card-detail-row">
                            <i class="fas fa-tag" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Budget: ${visit.budget_range}</span>
                        </div>` : ''}
                        ${visit.visit_notes ? `
                        <div class="card-detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                            <strong style="color: #374151;">Notes:</strong>
                            <span style="color: #6b7280; margin-left: 8px;">${visit.visit_notes.substring(0, 100)}${visit.visit_notes.length > 100 ? '...' : ''}</span>
                        </div>` : ''}
                    </div>
                    <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; flex-direction: column; gap: 8px;">
                        <button class="btn-view-details" onclick="showDetailsModal('site-visit', ${visit.id})">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </button>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-success" style="flex: 1; padding: 8px 12px; font-size: 13px;" onclick="showVerifySiteVisitModal(${visit.id})">
                                <i class="fas fa-check mr-2"></i>Verify
                            </button>
                            <button class="btn btn-danger" style="flex: 1; padding: 8px 12px; font-size: 13px;" onclick="showRejectModal('site-visit', ${visit.id})">
                                <i class="fas fa-times mr-2"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = `<div class="prospects-grid">${html}</div>`;
        } catch (error) {
            console.error('Error loading site visits:', error);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading site visits</p></div>';
        }
    }

    async function verifyMeeting(id) {
        if (!confirm('Are you sure you want to verify this meeting?')) return;

        const result = await apiCall(`/meetings/${id}/verify`, {
            method: 'POST',
        });

        if (result && result.success) {
            if (typeof showNotification === 'function') {
                showNotification('Meeting verified successfully!', 'success', 3000);
            } else {
                alert('Meeting verified successfully!');
            }
            loadMeetings();
        } else {
            alert(result.message || 'Failed to verify meeting');
        }
    }

    let currentVerifySiteVisitId = null;
    
    function showVerifySiteVisitModal(id) {
        currentVerifySiteVisitId = id;
        document.getElementById('verifySiteVisitModal').classList.add('show');
        document.getElementById('verifySiteVisitLeadStatus').value = '';
        document.getElementById('verifySiteVisitNotes').value = '';
    }
    
    function closeVerifySiteVisitModal() {
        document.getElementById('verifySiteVisitModal').classList.remove('show');
        currentVerifySiteVisitId = null;
    }
    
    async function verifySiteVisit(id) {
        // If id is provided directly, show modal
        if (id) {
            showVerifySiteVisitModal(id);
            return;
        }
        
        // Otherwise use currentVerifySiteVisitId from modal
        if (!currentVerifySiteVisitId) return;
        
        const leadStatus = document.getElementById('verifySiteVisitLeadStatus').value;
        const notes = document.getElementById('verifySiteVisitNotes').value.trim();
        
        if (!leadStatus) {
            alert('Please select a lead status');
            return;
        }

        const result = await apiCall(`/site-visits/${currentVerifySiteVisitId}/verify`, {
            method: 'POST',
            body: JSON.stringify({
                lead_status: leadStatus,
                notes: notes
            }),
        });

        if (result && result.success) {
            if (typeof showNotification === 'function') {
                showNotification('Site visit verified successfully!', 'success', 3000);
            } else {
                alert('Site visit verified successfully!');
            }
            closeVerifySiteVisitModal();
            loadSiteVisits();
        } else {
            alert(result.message || 'Failed to verify site visit');
        }
    }

    function showRejectModal(type, id) {
        currentType = type;
        currentItemId = id;
        document.getElementById('rejectModal').classList.add('show');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('show');
        document.getElementById('rejectForm').reset();
        currentItemId = null;
    }

    document.getElementById('rejectForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const reason = document.getElementById('rejectionReason').value;
        if (!reason.trim()) {
            alert('Please enter a rejection reason');
            return;
        }

        let endpoint;
        if (currentType === 'meeting') {
            endpoint = `/meetings/${currentItemId}/reject`;
        } else if (currentType === 'closer') {
            endpoint = `/site-visits/${currentItemId}/reject-closer`;
        } else {
            endpoint = `/site-visits/${currentItemId}/reject`;
        }

        const result = await apiCall(endpoint, {
            method: 'POST',
            body: JSON.stringify({ reason }),
        });

        if (result && result.success) {
            if (typeof showNotification === 'function') {
                showNotification('Rejected successfully', 'success', 3000);
            } else {
                alert('Rejected successfully');
            }
            closeRejectModal();
            if (currentType === 'meeting') {
                loadMeetings();
            } else if (currentType === 'closer') {
                loadClosers();
            } else {
                loadSiteVisits();
            }
        } else {
            alert(result.message || 'Failed to reject');
        }
    });

    // Close modals on outside click
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
    
    document.getElementById('verifySiteVisitModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeVerifySiteVisitModal();
        }
    });
    
    document.getElementById('verifyCloserModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeVerifyCloserModal();
        }
    });

    let currentDetailsId = null;
    let currentDetailsType = null;

    async function showDetailsModal(type, id) {
        currentDetailsType = type;
        currentDetailsId = id;
        
        const modal = document.getElementById('detailsModal');
        const title = document.getElementById('detailsModalTitle');
        const content = document.getElementById('detailsModalContent');
        const verifyBtn = document.getElementById('detailsModalVerifyBtn');
        const rejectBtn = document.getElementById('detailsModalRejectBtn');

        // Set title
        if (type === 'meeting') {
            title.textContent = 'Meeting Details';
        } else if (type === 'site-visit') {
            title.textContent = 'Site Visit Details';
        } else if (type === 'closer') {
            title.textContent = 'Closer Request Details';
        } else if (type === 'prospect') {
            title.textContent = 'Prospect Details';
        }

        modal.classList.add('show');
        
        // Show loading state
        content.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #6b7280;"></i>
                <p style="margin-top: 16px; color: #6b7280;">Loading details...</p>
            </div>
        `;
        
        // Show verify/reject buttons only for non-prospects
        if (type === 'prospect') {
            verifyBtn.style.display = 'none';
            rejectBtn.style.display = 'none';
        } else {
            verifyBtn.style.display = 'inline-block';
            rejectBtn.style.display = 'inline-block';
        }

        try {
            let endpoint;
            // Use admin endpoint for CRM/Admin users
            if (type === 'meeting') {
                endpoint = `/api/admin/meetings/${id}`;
            } else if (type === 'prospect') {
                endpoint = `/api/admin/prospects/${id}`;
            } else {
                endpoint = `/api/admin/site-visits/${id}`;
            }

            const response = await fetch(`{{ url("") }}${endpoint}`, {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error:', response.status, errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            const item = result.data || result;

            // Build details HTML
            let html = '<div style="padding: 20px;">';
            
            if (type === 'meeting') {
                html += generateMeetingDetailsHTML(item);
            } else if (type === 'closer') {
                html += generateCloserDetailsHTML(item);
            } else if (type === 'prospect') {
                html += generateProspectDetailsHTML(item);
            } else {
                html += generateSiteVisitDetailsHTML(item);
            }
            
            html += '</div>';
            content.innerHTML = html;
        } catch (error) {
            console.error('Error loading details:', error);
            content.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 32px; margin-bottom: 16px;"></i>
                    <p>Error loading details. Please try again.</p>
                    <p style="font-size: 12px; color: #6b7280; margin-top: 8px;">${error.message}</p>
                    <button class="btn" style="background: #6b7280; color: white; margin-top: 16px;" onclick="closeDetailsModal()">Close</button>
                </div>
            `;
        }
    }

    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.remove('show');
        currentDetailsId = null;
        currentDetailsType = null;
    }

    function verifyFromDetails() {
        if (currentDetailsType === 'meeting') {
            verifyMeeting(currentDetailsId);
        } else if (currentDetailsType === 'site-visit') {
            showVerifySiteVisitModal(currentDetailsId);
        } else if (currentDetailsType === 'closer') {
            showVerifyCloserModal(currentDetailsId);
        }
        closeDetailsModal();
    }

    function rejectFromDetails() {
        if (currentDetailsType === 'meeting') {
            showRejectModal('meeting', currentDetailsId);
        } else if (currentDetailsType === 'site-visit') {
            showRejectModal('site-visit', currentDetailsId);
        } else if (currentDetailsType === 'closer') {
            showRejectModal('closer', currentDetailsId);
        }
        closeDetailsModal();
    }

    function generateMeetingDetailsHTML(meeting) {
        const photos = meeting.photos || [];
        const completionProofPhotos = meeting.completion_proof_photos || [];
        
        return `
            <div class="details-section">
                <h4><i class="fas fa-user mr-2"></i>Customer Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Customer Name:</div>
                    <div class="detail-value">${meeting.customer_name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value">${meeting.phone || meeting.lead?.phone || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Employee:</div>
                    <div class="detail-value">${meeting.employee || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Occupation:</div>
                    <div class="detail-value">${meeting.occupation || 'N/A'}</div>
                </div>
            </div>

            <div class="details-section">
                <h4><i class="fas fa-calendar mr-2"></i>Scheduling Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Scheduled At:</div>
                    <div class="detail-value">${new Date(meeting.scheduled_at).toLocaleString()}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date of Visit:</div>
                    <div class="detail-value">${meeting.date_of_visit ? new Date(meeting.date_of_visit).toLocaleDateString() : 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><span class="badge badge-pending">${meeting.status || 'N/A'}</span></div>
                </div>
                ${meeting.completed_at ? `
                <div class="detail-row">
                    <div class="detail-label">Completed At:</div>
                    <div class="detail-value">${new Date(meeting.completed_at).toLocaleString()}</div>
                </div>
                ` : ''}
            </div>

            <div class="details-section">
                <h4><i class="fas fa-building mr-2"></i>Property Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Project:</div>
                    <div class="detail-value">${meeting.project || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Property Type:</div>
                    <div class="detail-value">${meeting.property_type || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Budget Range:</div>
                    <div class="detail-value">${meeting.budget_range || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Payment Mode:</div>
                    <div class="detail-value">${meeting.payment_mode || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Tentative Period:</div>
                    <div class="detail-value">${meeting.tentative_period || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Lead Type:</div>
                    <div class="detail-value">${meeting.lead_type || 'N/A'}</div>
                </div>
            </div>

            <div class="details-section">
                <h4><i class="fas fa-users mr-2"></i>Team Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Created By:</div>
                    <div class="detail-value">${meeting.creator?.name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Assigned To:</div>
                    <div class="detail-value">${meeting.assignedTo?.name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Team Leader:</div>
                    <div class="detail-value">${meeting.team_leader || 'N/A'}</div>
                </div>
            </div>

            ${meeting.meeting_notes ? `
            <div class="details-section">
                <h4><i class="fas fa-sticky-note mr-2"></i>Notes</h4>
                <div class="detail-value" style="white-space: pre-wrap; padding: 12px; background: #f9fafb; border-radius: 8px;">${meeting.meeting_notes}</div>
            </div>
            ` : ''}

            ${photos.length > 0 ? `
            <div class="details-section">
                <h4><i class="fas fa-images mr-2"></i>Photos</h4>
                <div class="photo-gallery">
                    ${photos.map(photo => {
                        const photoUrl = typeof photo === 'string' && photo.startsWith('http') ? photo : '{{ url("/storage") }}/' + photo;
                        return `
                            <div class="photo-item" onclick="window.open('${photoUrl}', '_blank')">
                                <img src="${photoUrl}" alt="Meeting Photo" />
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            ` : ''}

            ${completionProofPhotos.length > 0 ? `
            <div class="details-section">
                <h4><i class="fas fa-check-circle mr-2"></i>Completion Proof Photos</h4>
                <div class="photo-gallery">
                    ${completionProofPhotos.map(photo => {
                        const photoUrl = typeof photo === 'string' && photo.startsWith('http') ? photo : '{{ url("/storage") }}/' + photo;
                        return `
                            <div class="photo-item" onclick="window.open('${photoUrl}', '_blank')">
                                <img src="${photoUrl}" alt="Completion Proof" />
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            ` : ''}
        `;
    }

    function generateSiteVisitDetailsHTML(visit) {
        const photos = visit.photos || [];
        const completionProofPhotos = visit.completion_proof_photos || [];
        
        return `
            <div class="details-section">
                <h4><i class="fas fa-user mr-2"></i>Customer Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Customer Name:</div>
                    <div class="detail-value">${visit.customer_name || visit.lead?.name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value">${visit.phone || visit.lead?.phone || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Employee:</div>
                    <div class="detail-value">${visit.employee || 'N/A'}</div>
                </div>
            </div>

            <div class="details-section">
                <h4><i class="fas fa-map-marker-alt mr-2"></i>Property Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Property Name:</div>
                    <div class="detail-value">${visit.property_name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Property Address:</div>
                    <div class="detail-value">${visit.property_address || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Project:</div>
                    <div class="detail-value">${visit.project || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Property Type:</div>
                    <div class="detail-value">${visit.property_type || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Budget Range:</div>
                    <div class="detail-value">${visit.budget_range || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Lead Type:</div>
                    <div class="detail-value">${visit.lead_type || 'N/A'}</div>
                </div>
            </div>

            <div class="details-section">
                <h4><i class="fas fa-calendar mr-2"></i>Scheduling Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Scheduled At:</div>
                    <div class="detail-value">${new Date(visit.scheduled_at).toLocaleString()}</div>
                </div>
                ${visit.completed_at ? `
                <div class="detail-row">
                    <div class="detail-label">Completed At:</div>
                    <div class="detail-value">${new Date(visit.completed_at).toLocaleString()}</div>
                </div>
                ` : ''}
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><span class="badge badge-pending">${visit.status || 'N/A'}</span></div>
                </div>
                ${visit.lead_status ? `
                <div class="detail-row">
                    <div class="detail-label">Lead Status:</div>
                    <div class="detail-value"><span class="badge ${getLeadStatusBadgeClass(visit.lead_status)}">${getLeadStatusLabel(visit.lead_status)}</span></div>
                </div>
                ` : ''}
            </div>

            <div class="details-section">
                <h4><i class="fas fa-users mr-2"></i>Team Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Created By:</div>
                    <div class="detail-value">${visit.creator?.name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Assigned To:</div>
                    <div class="detail-value">${visit.assignedTo?.name || 'N/A'}</div>
                </div>
            </div>

            ${visit.visit_notes ? `
            <div class="details-section">
                <h4><i class="fas fa-sticky-note mr-2"></i>Visit Notes</h4>
                <div class="detail-value" style="white-space: pre-wrap; padding: 12px; background: #f9fafb; border-radius: 8px;">${visit.visit_notes}</div>
            </div>
            ` : ''}

            ${photos.length > 0 ? `
            <div class="details-section">
                <h4><i class="fas fa-images mr-2"></i>Photos</h4>
                <div class="photo-gallery">
                    ${photos.map(photo => {
                        const photoUrl = typeof photo === 'string' && photo.startsWith('http') ? photo : '{{ url("/storage") }}/' + photo;
                        return `
                            <div class="photo-item" onclick="window.open('${photoUrl}', '_blank')">
                                <img src="${photoUrl}" alt="Site Visit Photo" />
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            ` : ''}

            ${completionProofPhotos.length > 0 ? `
            <div class="details-section">
                <h4><i class="fas fa-check-circle mr-2"></i>Completion Proof Photos</h4>
                <div class="photo-gallery">
                    ${completionProofPhotos.map(photo => {
                        const photoUrl = typeof photo === 'string' && photo.startsWith('http') ? photo : '{{ url("/storage") }}/' + photo;
                        return `
                            <div class="photo-item" onclick="window.open('${photoUrl}', '_blank')">
                                <img src="${photoUrl}" alt="Completion Proof" />
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            ` : ''}
        `;
    }

    function generateProspectDetailsHTML(prospect) {
        const statusBadge = prospect.verification_status === 'verified' || prospect.verification_status === 'approved' ? 'badge-verified' :
                          prospect.verification_status === 'rejected' ? 'badge-rejected' :
                          'badge-pending';
        const statusText = prospect.verification_status === 'verified' || prospect.verification_status === 'approved' ? 'Verified' :
                         prospect.verification_status === 'rejected' ? 'Rejected' :
                         'Pending Verification';

        // Calculate duration
        let durationDisplay = '';
        const isPending = prospect.verification_status === 'pending' || prospect.verification_status === 'pending_verification';
        
        if (prospect.created_at) {
            const createdDate = new Date(prospect.created_at);
            let endDate;
            
            if (isPending) {
                // For pending: time elapsed from creation to now
                endDate = new Date();
            } else if (prospect.verified_at) {
                // For verified/rejected: time from creation to verification
                endDate = new Date(prospect.verified_at);
            }
            
            if (endDate) {
                const seconds = Math.floor((endDate - createdDate) / 1000);
                durationDisplay = formatDuration(seconds, isPending);
            }
        }

        return `
            <div class="details-section">
                <h4><i class="fas fa-user mr-2"></i>Customer Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Customer Name:</div>
                    <div class="detail-value">${prospect.customer_name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value">${prospect.phone || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Budget:</div>
                    <div class="detail-value">${prospect.budget ? '₹' + parseFloat(prospect.budget).toLocaleString('en-IN') : 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Preferred Location:</div>
                    <div class="detail-value">${prospect.preferred_location || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Size:</div>
                    <div class="detail-value">${prospect.size || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Purpose:</div>
                    <div class="detail-value">${prospect.purpose || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Possession:</div>
                    <div class="detail-value">${prospect.possession || 'N/A'}</div>
                </div>
            </div>

            <div class="details-section">
                <h4><i class="fas fa-info-circle mr-2"></i>Verification Status</h4>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><span class="badge ${statusBadge}">${statusText}</span></div>
                </div>
                ${durationDisplay ? `
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-clock mr-2"></i>${isPending ? 'Awaiting Response' : 'Response Time'}:</div>
                    <div class="detail-value">${durationDisplay.replace(isPending ? 'Awaiting Response: ' : 'Response Time: ', '')}</div>
                </div>
                ` : ''}
                ${prospect.verified_at ? `
                <div class="detail-row">
                    <div class="detail-label">Verified At:</div>
                    <div class="detail-value">${new Date(prospect.verified_at).toLocaleString()}</div>
                </div>
                ` : ''}
                ${prospect.verified_by || (prospect.verifiedBy && prospect.verifiedBy.name) ? `
                <div class="detail-row">
                    <div class="detail-label">Verified By:</div>
                    <div class="detail-value">${prospect.verifiedBy?.name || 'N/A'}</div>
                </div>
                ` : ''}
                ${prospect.rejection_reason ? `
                <div class="detail-row">
                    <div class="detail-label">Rejection Reason:</div>
                    <div class="detail-value" style="color: #ef4444;">${prospect.rejection_reason}</div>
                </div>
                ` : ''}
                ${prospect.manager_remark ? `
                <div class="detail-row">
                    <div class="detail-label">Manager Remark:</div>
                    <div class="detail-value">${prospect.manager_remark}</div>
                </div>
                ` : ''}
            </div>

            <div class="details-section">
                <h4><i class="fas fa-users mr-2"></i>Team Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Created By (Telecaller):</div>
                    <div class="detail-value">${prospect.telecaller?.name || prospect.createdBy?.name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Manager:</div>
                    <div class="detail-value">${prospect.manager?.name || prospect.assignedManager?.name || 'Not Assigned'}</div>
                </div>
                ${prospect.lead ? `
                <div class="detail-row">
                    <div class="detail-label">Associated Lead:</div>
                    <div class="detail-value">${prospect.lead.name || 'Lead #' + prospect.lead_id}</div>
                </div>
                ` : ''}
                <div class="detail-row">
                    <div class="detail-label">Created At:</div>
                    <div class="detail-value">${prospect.created_at ? new Date(prospect.created_at).toLocaleString() : 'N/A'}</div>
                </div>
            </div>

            ${prospect.remark ? `
            <div class="details-section">
                <h4><i class="fas fa-comment mr-2"></i>Telecaller Remark</h4>
                <div class="detail-value" style="white-space: pre-wrap; padding: 12px; background: #f9fafb; border-radius: 8px;">${prospect.remark}</div>
            </div>
            ` : ''}

            ${prospect.employee_remark ? `
            <div class="details-section">
                <h4><i class="fas fa-comment-dots mr-2"></i>Employee Remark</h4>
                <div class="detail-value" style="white-space: pre-wrap; padding: 12px; background: #f9fafb; border-radius: 8px;">${prospect.employee_remark}</div>
            </div>
            ` : ''}

            ${prospect.notes ? `
            <div class="details-section">
                <h4><i class="fas fa-sticky-note mr-2"></i>Additional Notes</h4>
                <div class="detail-value" style="white-space: pre-wrap; padding: 12px; background: #f9fafb; border-radius: 8px;">${prospect.notes}</div>
            </div>
            ` : ''}
        `;
    }

    function generateCloserDetailsHTML(visit) {
        const closerProofPhotos = visit.closer_request_proof_photos || [];
        const photos = visit.photos || [];
        const completionProofPhotos = visit.completion_proof_photos || [];
        
        return `
            <div class="details-section">
                <h4><i class="fas fa-user mr-2"></i>Customer Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Customer Name:</div>
                    <div class="detail-value">${visit.customer_name || visit.lead?.name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value">${visit.phone || visit.lead?.phone || 'N/A'}</div>
                </div>
            </div>

            <div class="details-section">
                <h4><i class="fas fa-map-marker-alt mr-2"></i>Property Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Property Name:</div>
                    <div class="detail-value">${visit.property_name || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Property Address:</div>
                    <div class="detail-value">${visit.property_address || 'N/A'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Budget Range:</div>
                    <div class="detail-value">${visit.budget_range || 'N/A'}</div>
                </div>
            </div>

            <div class="details-section">
                <h4><i class="fas fa-calendar mr-2"></i>Scheduling Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Scheduled At:</div>
                    <div class="detail-value">${new Date(visit.scheduled_at).toLocaleString()}</div>
                </div>
                ${visit.completed_at ? `
                <div class="detail-row">
                    <div class="detail-label">Completed At:</div>
                    <div class="detail-value">${new Date(visit.completed_at).toLocaleString()}</div>
                </div>
                ` : ''}
            </div>

            ${visit.visit_notes ? `
            <div class="details-section">
                <h4><i class="fas fa-sticky-note mr-2"></i>Visit Notes</h4>
                <div class="detail-value" style="white-space: pre-wrap; padding: 12px; background: #f9fafb; border-radius: 8px;">${visit.visit_notes}</div>
            </div>
            ` : ''}

            ${closerProofPhotos.length > 0 ? `
            <div class="details-section">
                <h4><i class="fas fa-trophy mr-2"></i>Closer Request Proof Photos</h4>
                <div class="photo-gallery">
                    ${closerProofPhotos.map(photo => {
                        const photoUrl = typeof photo === 'string' && photo.startsWith('http') ? photo : '{{ url("/storage") }}/' + photo;
                        return `
                            <div class="photo-item" onclick="window.open('${photoUrl}', '_blank')">
                                <img src="${photoUrl}" alt="Closer Proof" />
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            ` : ''}

            ${photos.length > 0 ? `
            <div class="details-section">
                <h4><i class="fas fa-images mr-2"></i>Site Visit Photos</h4>
                <div class="photo-gallery">
                    ${photos.map(photo => {
                        const photoUrl = typeof photo === 'string' && photo.startsWith('http') ? photo : '{{ url("/storage") }}/' + photo;
                        return `
                            <div class="photo-item" onclick="window.open('${photoUrl}', '_blank')">
                                <img src="${photoUrl}" alt="Site Visit Photo" />
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            ` : ''}

            ${completionProofPhotos.length > 0 ? `
            <div class="details-section">
                <h4><i class="fas fa-check-circle mr-2"></i>Completion Proof Photos</h4>
                <div class="photo-gallery">
                    ${completionProofPhotos.map(photo => {
                        const photoUrl = typeof photo === 'string' && photo.startsWith('http') ? photo : '{{ url("/storage") }}/' + photo;
                        return `
                            <div class="photo-item" onclick="window.open('${photoUrl}', '_blank')">
                                <img src="${photoUrl}" alt="Completion Proof" />
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            ` : ''}
        `;
    }

    // Close details modal on outside click
    document.getElementById('detailsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDetailsModal();
        }
    });

    async function loadClosers() {
        const container = document.getElementById('closersContainer');
        container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';

        try {
            // Get pending closer requests
            const response = await fetch('{{ url("/api/admin/verifications/pending-closers") }}', {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();
            const closers = data?.data || [];

            document.getElementById('closersCount').textContent = closers.length;

            if (closers.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3 style="font-size: 18px; font-weight: 600; color: #333; margin: 16px 0 8px;">No Pending Closer Requests</h3>
                        <p>All closer requests have been verified.</p>
                    </div>
                `;
                return;
            }

            const html = closers.map(visit => {
                const proofPhotos = Array.isArray(visit.closer_request_proof_photos) ? visit.closer_request_proof_photos : 
                                   (visit.closer_request_proof_photos ? [visit.closer_request_proof_photos] : []);
                const hasPhotos = proofPhotos.length > 0;
                
                return `
                <div class="verification-card">
                    <div class="verification-info" style="flex: 1;">
                        <h3 style="font-size: 18px; font-weight: 600; color: #063A1C; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">${visit.customer_name || visit.lead?.name || 'N/A'}</h3>
                        <div class="card-detail-row">
                            <i class="fas fa-phone" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">${visit.phone || visit.lead?.phone || 'N/A'}</span>
                        </div>
                        <div class="card-detail-row">
                            <i class="fas fa-calendar" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">${new Date(visit.scheduled_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                        <div class="card-detail-row">
                            <i class="fas fa-user" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Created by: ${visit.creator?.name || 'N/A'}</span>
                        </div>
                        ${visit.property_name ? `
                        <div class="card-detail-row">
                            <i class="fas fa-map-marker-alt" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Property: ${visit.property_name}</span>
                        </div>` : ''}
                        ${visit.budget_range ? `
                        <div class="card-detail-row">
                            <i class="fas fa-tag" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">Budget: ${visit.budget_range}</span>
                        </div>` : ''}
                        ${visit.visit_notes ? `
                        <div class="card-detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                            <strong style="color: #374151;">Visit Notes:</strong>
                            <span style="color: #6b7280; margin-left: 8px;">${visit.visit_notes.substring(0, 100)}${visit.visit_notes.length > 100 ? '...' : ''}</span>
                        </div>` : ''}
                        ${hasPhotos ? `
                        <div class="card-detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                            <i class="fas fa-images" style="color: #6b7280; width: 20px;"></i>
                            <span style="color: #374151;">${proofPhotos.length} Proof Photo${proofPhotos.length > 1 ? 's' : ''}</span>
                        </div>` : ''}
                    </div>
                    <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; flex-direction: column; gap: 8px;">
                        <button class="btn-view-details" onclick="showDetailsModal('closer', ${visit.id})">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </button>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-success" style="flex: 1; padding: 8px 12px; font-size: 13px;" onclick="showVerifyCloserModal(${visit.id})">
                                <i class="fas fa-check mr-2"></i>Verify Closer
                            </button>
                            <button class="btn btn-danger" style="flex: 1; padding: 8px 12px; font-size: 13px;" onclick="showRejectModal('closer', ${visit.id})">
                                <i class="fas fa-times mr-2"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
                `;
            }).join('');

            container.innerHTML = `<div class="prospects-grid">${html}</div>`;
        } catch (error) {
            console.error('Error loading closers:', error);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading closer requests</p></div>';
        }
    }

    let currentVerifyCloserId = null;
    
    function showVerifyCloserModal(id) {
        currentVerifyCloserId = id;
        document.getElementById('verifyCloserModal').classList.add('show');
        document.getElementById('verifyCloserLeadStatus').value = '';
        document.getElementById('verifyCloserNotes').value = '';
    }
    
    function closeVerifyCloserModal() {
        document.getElementById('verifyCloserModal').classList.remove('show');
        currentVerifyCloserId = null;
    }
    
    async function verifyCloser(id) {
        // If id is provided directly, show modal
        if (id) {
            showVerifyCloserModal(id);
            return;
        }
        
        // Otherwise use currentVerifyCloserId from modal
        if (!currentVerifyCloserId) return;
        
        const leadStatus = document.getElementById('verifyCloserLeadStatus').value;
        const notes = document.getElementById('verifyCloserNotes').value.trim();
        
        if (!leadStatus) {
            alert('Please select a lead status');
            return;
        }

        const result = await apiCall(`/site-visits/${currentVerifyCloserId}/verify-closer`, {
            method: 'POST',
            body: JSON.stringify({ 
                lead_status: leadStatus,
                notes: notes 
            }),
        });

        if (result && result.success) {
            if (typeof showNotification === 'function') {
                showNotification('Closer request verified successfully!', 'success', 3000);
            } else {
                alert('Closer request verified successfully!');
            }
            closeVerifyCloserModal();
            loadClosers();
        } else {
            alert(result.message || 'Failed to verify closer request');
        }
    }

    function getLeadStatusLabel(status) {
        const labels = {
            'hot': 'Hot',
            'warm': 'Warm',
            'cold': 'Cold',
            'junk': 'Junk'
        };
        return labels[status] || status;
    }
    
    function getLeadStatusBadgeClass(status) {
        const classes = {
            'hot': 'badge-verified',
            'warm': 'badge-pending',
            'cold': 'badge-rejected',
            'junk': 'badge-cancelled'
        };
        return classes[status] || 'badge-pending';
    }
    
    // Load counts for all tabs on page load
    async function loadAllCounts() {
        try {
            // Check which tab is active
            const activeTab = document.querySelector('.tab.active');
            const activeTabText = activeTab ? activeTab.textContent.toLowerCase() : '';
            const isProspectsActive = activeTabText.includes('prospect');
            
            // Load prospects count first if prospects tab is active (for faster initial load)
            if (isProspectsActive) {
                try {
                    const prospectsResponse = await apiCall('/verifications/pending-prospects');
                    const prospectsCount = (prospectsResponse?.data || []).filter(p => 
                        p.verification_status === 'pending' || 
                        p.verification_status === 'pending_verification' ||
                        p.verification_status === 'verified' ||
                        p.verification_status === 'approved'
                    ).length;
                    document.getElementById('prospectsCount').textContent = prospectsCount;
                    // Load prospects immediately if tab is active
                    loadProspects();
                } catch (e) {
                    console.error('Error loading prospects:', e);
                }
            }
            
            // Load meetings and site visits count from same endpoint (in parallel)
            const meetingsResponse = await fetch('{{ url("/api/admin/verifications/pending") }}', {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                },
            });
            
            if (!meetingsResponse.ok) {
                console.error('Failed to load pending verifications:', meetingsResponse.status);
                if (!isProspectsActive) {
                    loadMeetings();
                }
                return;
            }
            
            const meetingsData = await meetingsResponse.json();
            
            // Count pending meetings - already filtered by API
            const meetingsCount = (meetingsData.meetings || []).length;
            document.getElementById('meetingsCount').textContent = meetingsCount;

            // Count pending site visits (excluding closers)
            const visitsCount = (meetingsData.site_visits || []).filter(sv => 
                sv.verification_status === 'pending' && sv.status === 'completed' && (!sv.closer_status || sv.closer_status !== 'pending')
            ).length;
            document.getElementById('visitsCount').textContent = visitsCount;

            // Load closers count
            try {
                const closersResponse = await fetch('{{ url("/api/admin/verifications/pending-closers") }}', {
                    headers: {
                        'Authorization': `Bearer ${getToken()}`,
                        'Accept': 'application/json',
                    },
                });
                if (closersResponse.ok) {
                    const closersData = await closersResponse.json();
                    const closersCount = (closersData?.data || []).length;
                    document.getElementById('closersCount').textContent = closersCount;
                }
            } catch (e) {
                console.error('Error loading closers count:', e);
            }

            // Load prospects count if not already loaded
            if (!isProspectsActive) {
                try {
                    const prospectsResponse = await apiCall('/verifications/pending-prospects');
                    const prospectsCount = (prospectsResponse?.data || []).filter(p => 
                        p.verification_status === 'pending' || 
                        p.verification_status === 'pending_verification' ||
                        p.verification_status === 'verified' ||
                        p.verification_status === 'approved'
                    ).length;
                    document.getElementById('prospectsCount').textContent = prospectsCount;
                } catch (e) {
                    console.error('Error loading prospects count:', e);
                }
            }

            // Load default tab (meetings) only if prospects tab is not active
            if (!isProspectsActive) {
                loadMeetings();
            }
        } catch (error) {
            console.error('Error loading counts:', error);
            // Load default tab based on active tab
            const activeTab = document.querySelector('.tab.active');
            const activeTabText = activeTab ? activeTab.textContent.toLowerCase() : '';
            if (activeTabText.includes('prospect')) {
                loadProspects();
            } else {
                loadMeetings();
            }
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadAllCounts();
    });
    
    // Also call immediately if DOM is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadAllCounts);
    } else {
        loadAllCounts();
    }
</script>
@endpush

