@extends('sales-manager.layout')

@section('title', 'My Team - Sales Manager')
@section('page-title', 'My Team')

@push('styles')
<style>
    .team-member-card {
        display: flex;
        align-items: center;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.3s;
        background: white;
    }
    .team-member-card:hover {
        border-color: #205A44;
        box-shadow: 0 4px 12px rgba(32, 90, 68, 0.1);
    }
    .team-member-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        font-weight: 600;
        margin-right: 16px;
        flex-shrink: 0;
    }
    .team-member-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    .team-member-info {
        flex: 1;
    }
    .team-member-name {
        font-weight: 600;
        color: #063A1C;
        font-size: 16px;
        margin-bottom: 4px;
    }
    .team-member-role {
        font-size: 13px;
        color: #9ca3af;
        margin-bottom: 4px;
    }
    .team-member-email {
        font-size: 13px;
        color: #6b7280;
    }
    .team-member-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
    }
    .status-available {
        background: #d1fae5;
        color: #065f46;
    }
    .status-absent {
        background: #fee2e2;
        color: #991b1b;
    }
    .stat-badge {
        display: inline-block;
        padding: 4px 10px;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 12px;
        color: #4b5563;
        margin-top: 8px;
    }
    .loading {
        text-align: center;
        padding: 40px;
        color: #9ca3af;
    }
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500 mb-1">Total Members</div>
        <div class="text-3xl font-bold text-gray-900" id="totalMembers">0</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500 mb-1">Available</div>
        <div class="text-3xl font-bold text-green-600" id="availableMembers">0</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500 mb-1">Absent</div>
        <div class="text-3xl font-bold text-red-600" id="absentMembers">0</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500 mb-1">Today's Prospects</div>
        <div class="text-3xl font-bold text-blue-600" id="todayProspects">0</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-6">Team Members</h2>
    
    <div id="teamMembersContainer">
        <div class="loading">
            <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
            <p>Loading team members...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    
    // Get token from localStorage or session
    function getToken() {
        const metaToken = document.querySelector('meta[name="api-token"]')?.content;
        return localStorage.getItem('sales_manager_token') || metaToken || '{{ session("api_token") ?? "" }}';
    }

    // API call helper
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
                localStorage.removeItem('sales_manager_token');
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

    // Load team data
    async function loadTeamData() {
        try {
            const data = await apiCall('/profile');
            
            if (!data) {
                console.error('Failed to load team data');
                showError();
                return;
            }

            // Update statistics
            if (data.team_stats) {
                document.getElementById('totalMembers').textContent = data.team_stats.total_members || 0;
                document.getElementById('availableMembers').textContent = data.team_stats.available_members || 0;
                document.getElementById('absentMembers').textContent = (data.team_stats.total_members - data.team_stats.available_members) || 0;
                document.getElementById('todayProspects').textContent = data.team_stats.today_prospects || 0;
            }

            // Display team members
            console.log('Team members data:', data.team_members); // Debug log
            if (data.team_members && Array.isArray(data.team_members)) {
                displayTeamMembers(data.team_members);
            } else {
                showNoMembers();
            }
        } catch (error) {
            console.error('Error loading team data:', error);
            showError();
        }
    }

    // Display team members
    function displayTeamMembers(teamMembers) {
        const container = document.getElementById('teamMembersContainer');
        
        if (!teamMembers || teamMembers.length === 0) {
            showNoMembers();
            return;
        }

        const html = teamMembers.map(member => `
            <div class="team-member-card">
                <div class="team-member-avatar">
                    ${member.profile_picture ? 
                        `<img src="${member.profile_picture}" alt="${member.name}">` : 
                        member.name.charAt(0).toUpperCase()
                    }
                </div>
                <div class="team-member-info">
                    <div class="team-member-name">${member.name}</div>
                    <div class="team-member-role">${member.role}</div>
                    <div class="team-member-email">
                        <i class="fas fa-envelope" style="margin-right: 4px;"></i>${member.email}
                    </div>
                    ${member.phone ? `
                        <div class="team-member-email" style="margin-top: 2px;">
                            <i class="fas fa-phone" style="margin-right: 4px;"></i>${member.phone}
                        </div>
                    ` : ''}
                </div>
                <div style="text-align: right;">
                    <span class="team-member-status ${member.is_absent ? 'status-absent' : 'status-available'}">
                        <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i>
                        ${member.is_absent ? 'Absent' : 'Available'}
                    </span>
                    ${member.is_absent && member.absent_reason ? `
                        <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                            ${member.absent_reason}
                        </div>
                    ` : ''}
                    ${member.today_prospects !== undefined ? `
                        <div class="stat-badge">
                            <i class="fas fa-star" style="font-size: 10px; margin-right: 4px;"></i>
                            Today: ${member.today_prospects} prospects
                        </div>
                    ` : ''}
                    <div style="font-size: 11px; color: #9ca3af; margin-top: 8px;">
                        <i class="fas fa-calendar" style="margin-right: 4px;"></i>
                        Joined ${member.joined_at}
                    </div>
                </div>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }

    // Show no members message
    function showNoMembers() {
        const container = document.getElementById('teamMembersContainer');
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Team Members</h3>
                <p class="text-gray-500">You don't have any team members assigned yet.</p>
                <p class="text-sm text-gray-400 mt-4">Contact your administrator to assign team members to you.</p>
            </div>
        `;
    }

    // Show error message
    function showError() {
        const container = document.getElementById('teamMembersContainer');
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-exclamation-triangle text-red-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Error Loading Team</h3>
                <p class="text-gray-500">Unable to load team data. Please try refreshing the page.</p>
                <button onclick="loadTeamData()" class="mt-4 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">
                    <i class="fas fa-sync-alt mr-2"></i>Retry
                </button>
            </div>
        `;
    }

    // Initialize on page load
    (function() {
        loadTeamData();
    })();
</script>
@endpush

