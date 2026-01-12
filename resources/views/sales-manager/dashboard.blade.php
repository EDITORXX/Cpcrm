@extends('sales-manager.layout')

@section('title', 'Dashboard - Sales Manager')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }
    .achievement-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .achievement-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .achievement-stats {
        display: flex;
        justify-content: space-around;
        margin-top: 16px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--link-color);
    }
    .stat-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }
    .pending-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #fef3c7;
        color: #92400e;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    /* Responsive Styles */
    @media (max-width: 767px) {
        .chart-container {
            height: 250px;
            margin: 16px 0;
        }
        
        .achievement-card {
            padding: 16px;
        }
        
        .achievement-title {
            font-size: 16px;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        
        .achievement-stats {
            flex-direction: column;
            gap: 12px;
        }
        
        .stat-value {
            font-size: 20px;
        }
        
        /* Stats cards responsive - 2 columns on mobile */
        .stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }
        
        .stats-grid > div {
            padding: 16px !important;
        }
        
        .stats-grid > div h3 {
            font-size: 20px !important;
        }
        
        .stats-grid > div p {
            font-size: 12px !important;
        }
        
        /* Hide Team Members card on mobile */
        .team-members-card {
            display: none !important;
        }
        
        /* Hide Pending Tasks card on mobile (keep only 4 cards: Leads, Prospects, Pending Verifications, Over Due) */
        .stats-grid > div:nth-child(6) {
            display: none !important;
        }
        
        /* Team call stats section */
        #teamCallStatsSection > div:first-child {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start !important;
        }
        
        #teamCallStatsSection > div:first-child > div {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            width: 100%;
        }
        
        #teamCallStatsSection > div:first-child button {
            flex: 1;
            min-width: 80px;
            padding: 8px 12px;
            font-size: 12px;
        }
        
        /* Table responsive */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
        
        .overflow-x-auto table {
            min-width: 600px;
        }
        
        .overflow-x-auto th,
        .overflow-x-auto td {
            padding: 8px 12px;
            font-size: 12px;
        }
        
        /* Quick actions buttons */
        .grid.grid-cols-1.md\:grid-cols-2 > a {
            padding: 16px;
        }
        
        .grid.grid-cols-1.md\:grid-cols-2 > a i {
            font-size: 24px !important;
        }
    }
    
    @media (min-width: 768px) and (max-width: 1023px) {
        .chart-container {
            height: 280px;
        }
    }
</style>
@endpush

@section('content')
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-6 stats-grid">
    <!-- Stats Cards - Reordered for mobile: Leads Received, Today Prospects, Pending Verifications, Over Due Task, Team Members -->
    
    <!-- 1. Leads Received -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Leads Received</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1" id="assignedLeads">0</h3>
            </div>
            <div class="bg-indigo-100 rounded-full p-3">
                <i class="fas fa-briefcase text-indigo-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- 2. Today's Prospects -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Today's Prospects</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1" id="todayProspects">0</h3>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-star text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- 3. Pending Verifications -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Pending Verifications</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1" id="pendingVerifications">0</h3>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- 4. Over Due Task -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Over Due Task</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1" id="overdueTasks">0</h3>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- 5. Team Members (hidden on mobile) -->
    <div class="bg-white rounded-lg shadow p-6 team-members-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Team Members</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1" id="teamMembersCount">0</h3>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- 6. Pending Tasks (kept for desktop) -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Pending Tasks</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1" id="pendingTasks">0</h3>
            </div>
            <div class="bg-orange-100 rounded-full p-3">
                <i class="fas fa-tasks text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Team Call Statistics -->
<div class="bg-white rounded-lg shadow p-6 mb-6" id="teamCallStatsSection" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <h2 class="text-xl font-bold text-gray-900">
            <i class="fas fa-phone mr-2"></i>Team Call Statistics
        </h2>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="loadTeamCallStats('today')" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg text-sm font-medium hover:from-[#205A44] hover:to-[#15803d]">Today</button>
            <button onclick="loadTeamCallStats('this_week')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">This Week</button>
            <button onclick="loadTeamCallStats('this_month')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">This Month</button>
        </div>
    </div>
    
    <!-- Team Call Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="text-sm text-blue-600 mb-1">Team Total Calls</div>
            <div class="text-2xl font-bold text-blue-900" id="teamTotalCalls">0</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="text-sm text-green-600 mb-1">Total Duration</div>
            <div class="text-2xl font-bold text-green-900" id="teamTotalDuration">0s</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="text-sm text-purple-600 mb-1">Average Duration</div>
            <div class="text-2xl font-bold text-purple-900" id="teamAvgDuration">0s</div>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
            <div class="text-sm text-orange-600 mb-1">Connection Rate</div>
            <div class="text-2xl font-bold text-orange-900" id="teamConnectionRate">0%</div>
        </div>
    </div>

    <!-- Top Performers -->
    <div id="topPerformersSection" style="display: none;">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Performers</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Calls</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Duration</th>
                    </tr>
                </thead>
                <tbody id="topPerformersTable" class="bg-white divide-y divide-gray-200">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Team Call Breakdown Chart -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Calls by Team Member</h3>
        <div class="chart-container" style="position: relative; height: 300px;">
            <canvas id="teamCallsChart"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap;">
        <a href="{{ route('calls.index') }}" class="px-4 py-2 bg-[#205A44] text-white rounded-lg hover:bg-[#15803d] transition-colors duration-200 text-sm font-medium">
            <i class="fas fa-list mr-2"></i> View All Team Calls
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('sales-manager.leads') }}" class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-all text-center">
            <i class="fas fa-briefcase text-3xl text-indigo-600 mb-2"></i>
            <p class="font-semibold text-gray-900">View Leads</p>
        </a>
        <a href="{{ route('sales-manager.tasks') }}" class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all text-center">
            <i class="fas fa-tasks text-3xl text-orange-600 mb-2"></i>
            <p class="font-semibold text-gray-900">View Tasks</p>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    const API_TOKEN = '{{ $api_token ?? session("api_token") ?? "" }}';
    
    // Store token in localStorage if available
    if (API_TOKEN) {
        localStorage.setItem('sales_manager_token', API_TOKEN);
    }
    
    function getToken() {
        return API_TOKEN || localStorage.getItem('sales_manager_token') || '{{ session("api_token") ?? "" }}';
    }

    async function apiCall(endpoint, options = {}) {
        const token = getToken();
        if (!token) {
            console.error('No API token available');
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
            console.log(`API Call: ${API_BASE_URL}${endpoint}`);
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers },
                credentials: 'same-origin',
            });

            console.log(`API Response Status: ${response.status} for ${endpoint}`);

            if (response.status === 401) {
                console.error('Unauthorized - token invalid');
                localStorage.removeItem('sales_manager_token');
                window.location.href = '{{ route("login") }}';
                return null;
            }

            if (!response.ok) {
                const errorText = await response.text();
                console.error(`API Error (${response.status}):`, errorText);
                try {
                    return JSON.parse(errorText);
                } catch (e) {
                    return { success: false, message: errorText };
                }
            }

            const data = await response.json();
            console.log(`API Success for ${endpoint}:`, data);
            return data;
        } catch (error) {
            console.error('API Call Error:', error);
            console.error('Error details:', error.message, error.stack);
            return { success: false, message: error.message };
        }
    }

    async function loadDashboardData() {
        try {
            console.log('Loading dashboard data...');
            console.log('API Token available:', !!getToken());
            
            // Load profile for team stats first
            const profile = await apiCall('/profile');
            console.log('Profile API response:', profile);
            
            if (profile && profile.team_stats) {
                document.getElementById('teamMembersCount').textContent = profile.team_stats.total_members || 0;
                document.getElementById('todayProspects').textContent = profile.team_stats.today_prospects || 0;
                // Get pending verifications from team_stats
                document.getElementById('pendingVerifications').textContent = profile.team_stats.pending_verifications || 0;
                // Get assigned leads count
                document.getElementById('assignedLeads').textContent = profile.team_stats.assigned_leads || 0;
                // Get pending tasks count
                document.getElementById('pendingTasks').textContent = profile.team_stats.pending_tasks || 0;
                // Get overdue tasks count
                document.getElementById('overdueTasks').textContent = profile.team_stats.overdue_tasks || 0;
                console.log('Team stats updated:', profile.team_stats);
            } else {
                console.error('Profile API failed or no team_stats:', profile);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            console.error('Error details:', error.message, error.stack);
        }
    }

    // Initialize on page load
    (function() {
        loadDashboardData();
    })();
</script>
@endpush

