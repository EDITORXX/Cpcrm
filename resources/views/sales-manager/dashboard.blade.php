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
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Stats Cards -->
    <div class="bg-white rounded-lg shadow p-6">
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
</div>

<!-- Achievement Charts -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Meetings Chart -->
    <div class="achievement-card">
        <div class="achievement-title">
            <span><i class="fas fa-handshake mr-2"></i>Meetings</span>
            <span class="pending-badge" id="meetingsPending">0 pending</span>
        </div>
        <div class="chart-container">
            <canvas id="meetingsChart"></canvas>
        </div>
        <div class="achievement-stats">
            <div class="stat-item">
                <div class="stat-value" id="meetingsTarget">0</div>
                <div class="stat-label">Target</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="meetingsAchieved">0</div>
                <div class="stat-label">Achieved</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="meetingsPercentage">0%</div>
                <div class="stat-label">Progress</div>
            </div>
        </div>
    </div>

    <!-- Site Visits Chart -->
    <div class="achievement-card">
        <div class="achievement-title">
            <span><i class="fas fa-map-marker-alt mr-2"></i>Site Visits</span>
            <span class="pending-badge" id="visitsPending">0 pending</span>
        </div>
        <div class="chart-container">
            <canvas id="visitsChart"></canvas>
        </div>
        <div class="achievement-stats">
            <div class="stat-item">
                <div class="stat-value" id="visitsTarget">0</div>
                <div class="stat-label">Target</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="visitsAchieved">0</div>
                <div class="stat-label">Achieved</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="visitsPercentage">0%</div>
                <div class="stat-label">Progress</div>
            </div>
        </div>
    </div>

    <!-- Closers Chart -->
    <div class="achievement-card">
        <div class="achievement-title">
            <span><i class="fas fa-check-circle mr-2"></i>Closers</span>
        </div>
        <div class="chart-container">
            <canvas id="closersChart"></canvas>
        </div>
        <div class="achievement-stats">
            <div class="stat-item">
                <div class="stat-value" id="closersTarget">0</div>
                <div class="stat-label">Target</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="closersAchieved">0</div>
                <div class="stat-label">Achieved</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="closersPercentage">0%</div>
                <div class="stat-label">Progress</div>
            </div>
        </div>
    </div>
</div>

<!-- Team Call Statistics -->
<div class="bg-white rounded-lg shadow p-6 mb-6" id="teamCallStatsSection" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="text-xl font-bold text-gray-900">
            <i class="fas fa-phone mr-2"></i>Team Call Statistics
        </h2>
        <div style="display: flex; gap: 10px;">
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
    <div style="display: flex; gap: 10px; margin-top: 20px;">
        <a href="{{ route('calls.index') }}" class="px-4 py-2 bg-[#205A44] text-white rounded-lg hover:bg-[#15803d] transition-colors duration-200 text-sm font-medium">
            <i class="fas fa-list mr-2"></i> View All Team Calls
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('sales-manager.meetings.create') }}" class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all text-center">
            <i class="fas fa-handshake text-3xl text-blue-600 mb-2"></i>
            <p class="font-semibold text-gray-900">Schedule Meeting</p>
        </a>
        <a href="{{ route('sales-manager.site-visits.create') }}" class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-all text-center">
            <i class="fas fa-map-marker-alt text-3xl text-green-600 mb-2"></i>
            <p class="font-semibold text-gray-900">Schedule Site Visit</p>
        </a>
        <a href="{{ route('sales-manager.meetings') }}" class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all text-center">
            <i class="fas fa-list text-3xl text-purple-600 mb-2"></i>
            <p class="font-semibold text-gray-900">View Meetings</p>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    
    function getToken() {
        return localStorage.getItem('sales_manager_token') || '{{ session("api_token") }}';
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

    function createPieChart(canvasId, target, achieved, label) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        const remaining = Math.max(0, target - achieved);
        
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Achieved', 'Remaining'],
                datasets: [{
                    data: [achieved, remaining],
                    backgroundColor: ['#10b981', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed;
                            }
                        }
                    }
                }
            }
        });
    }

    async function loadDashboardData() {
        try {
            // Load achievements
            const achievements = await apiCall('/achievements');
            if (achievements && achievements.success) {
                // Meetings
                const meetings = achievements.meetings || {target: 0, achieved: 0, percentage: 0};
                document.getElementById('meetingsTarget').textContent = meetings.target;
                document.getElementById('meetingsAchieved').textContent = meetings.achieved;
                document.getElementById('meetingsPercentage').textContent = meetings.percentage + '%';
                createPieChart('meetingsChart', meetings.target, meetings.achieved, 'Meetings');

                // Site Visits
                const visits = achievements.site_visits || {target: 0, achieved: 0, percentage: 0};
                document.getElementById('visitsTarget').textContent = visits.target;
                document.getElementById('visitsAchieved').textContent = visits.achieved;
                document.getElementById('visitsPercentage').textContent = visits.percentage + '%';
                createPieChart('visitsChart', visits.target, visits.achieved, 'Site Visits');

                // Closers
                const closers = achievements.closers || {target: 0, achieved: 0, percentage: 0};
                document.getElementById('closersTarget').textContent = closers.target;
                document.getElementById('closersAchieved').textContent = closers.achieved;
                document.getElementById('closersPercentage').textContent = closers.percentage + '%';
                createPieChart('closersChart', closers.target, closers.achieved, 'Closers');
            }

            // Load profile for team stats
            const profile = await apiCall('/profile');
            if (profile && profile.team_stats) {
                document.getElementById('teamMembersCount').textContent = profile.team_stats.total_members || 0;
                document.getElementById('todayProspects').textContent = profile.team_stats.today_prospects || 0;
            }

            // Load pending verifications
            const meetings = await apiCall('/meetings?verification_status=pending&status=completed');
            const siteVisits = await apiCall('/site-visits?verification_status=pending&status=completed');
            
            const pendingCount = (meetings?.data?.length || 0) + (siteVisits?.data?.length || 0);
            document.getElementById('pendingVerifications').textContent = pendingCount;
            
            // Update pending badges
            document.getElementById('meetingsPending').textContent = (meetings?.data?.length || 0) + ' pending';
            document.getElementById('visitsPending').textContent = (siteVisits?.data?.length || 0) + ' pending';
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    // Initialize on page load
    (function() {
        loadDashboardData();
    })();
</script>
@endpush

