@extends('telecaller.layout')

@section('title', 'Dashboard - Telecaller')
@section('page-title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/telecaller-dashboard.css') }}">
<style>
    .dashboard-container {
        width: 100%;
        max-width: 100%;
    }

    /* Dashboard Cards Grid */
    .dashboard-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    /* Ensure equal width for cards in phone view */
    .dashboard-cards-grid .dashboard-card {
        width: 100%;
        min-width: 0;
    }

    .dashboard-card {
        background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);
        border-radius: 12px;
        padding: 16px;
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(6, 58, 28, 0.3);
    }

    .dashboard-card-title {
        font-size: 13px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dashboard-card-value {
        font-size: 28px;
        font-weight: 700;
        color: white;
        line-height: 1.2;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .dashboard-card-icon {
        display: none;
    }

    /* Target Card Specific Styles */
    .target-card {
        position: relative;
    }

    .period-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        margin-left: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress-bar-container {
        width: 100%;
        height: 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
        margin-top: 8px;
        margin-bottom: 4px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #4ade80 0%, #22c55e 100%);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    /* Color coding based on percentage */
    .progress-bar[data-percentage] {
        background: linear-gradient(90deg, #4ade80 0%, #22c55e 100%);
    }

    .progress-bar[data-percentage^="0"],
    .progress-bar[data-percentage^="1"],
    .progress-bar[data-percentage^="2"],
    .progress-bar[data-percentage^="3"],
    .progress-bar[data-percentage^="4"] {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
    }

    .progress-bar[data-percentage^="5"],
    .progress-bar[data-percentage^="6"],
    .progress-bar[data-percentage^="7"],
    .progress-bar[data-percentage^="8"],
    .progress-bar[data-percentage^="9"] {
        background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%);
    }

    .progress-percentage {
        font-size: 12px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        margin-top: 4px;
    }

    /* Desktop View */
    @media (min-width: 768px) {
        .dashboard-cards-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .dashboard-card {
            padding: 20px;
        }

        .dashboard-card-value {
            font-size: 32px;
        }

        .time-range-selector {
            justify-content: flex-end;
        }

        .time-range-selector select {
            max-width: 200px;
        }
    }

    /* Consolidated Target Card (Phone View) */
    .consolidated-target-card {
        background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);
        border-radius: 12px;
        padding: 16px;
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .target-filter-buttons {
        display: flex;
        gap: 6px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .filter-btn {
        flex: 1;
        padding: 6px 8px;
        border: 1.5px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .filter-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .filter-btn.active {
        background: white;
        color: #205A44;
        border-color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .target-section {
        margin-bottom: 20px;
    }

    .target-section:last-child {
        margin-bottom: 0;
    }

    .target-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .target-section-title {
        font-size: 13px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .target-section-value {
        font-size: 24px;
        font-weight: 700;
        color: white;
        line-height: 1.2;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        margin-bottom: 8px;
    }

    .desktop-only {
        display: none;
    }

    .mobile-only {
        display: block;
    }

    /* Mobile View - Ensure 2 columns with equal 50%-50% width */
    @media (max-width: 767px) {
        /* Hide month selector in phone view */
        .month-selector-container {
            display: none !important;
        }

        .dashboard-cards-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            width: 100%;
        }

        .dashboard-cards-grid .dashboard-card {
            width: 100%;
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Consolidated target card should be 100% width and span all grid columns */
        .consolidated-target-card {
            grid-column: 1 / -1;
            width: 100%;
            max-width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .target-card .dashboard-card-value {
            font-size: 20px;
        }

        .period-badge {
            font-size: 9px;
            padding: 1px 4px;
        }

        .dashboard-card {
            padding: 16px;
        }

        .dashboard-card-value {
            font-size: 24px;
        }

        .time-range-selector {
            flex-direction: column;
            align-items: stretch;
        }

        .time-range-selector select {
            width: 100%;
        }

        .custom-date-inputs {
            flex-direction: column;
        }
    }

    /* Desktop View */
    @media (min-width: 768px) {
        .desktop-only {
            display: block;
        }

        .mobile-only {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Month Selector for Targets -->
    <div class="month-selector-container" style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div>
            <label for="target_month" style="font-weight: 500; margin-right: 10px;">View Targets for Month:</label>
            <input type="month" id="target_month" name="target_month" value="{{ $targetMonth ?? now()->format('Y-m') }}" 
                   onchange="updateTargetMonth(this.value)" 
                   style="padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 14px;">
            @if(($targetMonth ?? now()->format('Y-m')) != now()->format('Y-m'))
                <span style="margin-left: 10px; color: #666; font-size: 12px;">(Current: {{ now()->format('M Y') }})</span>
            @else
                <span style="margin-left: 10px; color: #16a34a; font-size: 12px; font-weight: 600;">Current Month</span>
            @endif
        </div>
    </div>
    
    <!-- Dashboard Cards Grid -->
    <div class="dashboard-cards-grid">
        <!-- Today Lead Card -->
        <div class="dashboard-card">
            <div class="dashboard-card-title">Today Lead</div>
            <div class="dashboard-card-value">{{ $cardStats['today_leads'] ?? 0 }}</div>
        </div>

        <!-- Remaining Task Card -->
        <div class="dashboard-card">
            <div class="dashboard-card-title">Remaining Task</div>
            <div class="dashboard-card-value">{{ $cardStats['remaining_tasks'] ?? 0 }}</div>
        </div>

        <!-- Over Due Task Card -->
        <div class="dashboard-card">
            <div class="dashboard-card-title">Over Due Task</div>
            <div class="dashboard-card-value">{{ $cardStats['overdue_tasks'] ?? 0 }}</div>
        </div>

        <!-- Prospect Card -->
        <div class="dashboard-card">
            <div class="dashboard-card-title">Prospect</div>
            <div class="dashboard-card-value">{{ $cardStats['prospects'] ?? 0 }}</div>
        </div>

        @if(isset($cardStats['targets']))
        <!-- Desktop: Separate Target Cards -->
        <!-- Calling Target Card -->
        <div class="dashboard-card target-card desktop-only">
            <div class="dashboard-card-title">
                Calling Target 
                <span class="period-badge">{{ strtoupper($cardStats['targets']['calling']['period'] ?? 'daily') }}</span>
            </div>
            <div class="dashboard-card-value">
                {{ $cardStats['targets']['calling']['actual'] ?? 0 }} / {{ $cardStats['targets']['calling']['target'] ?? 0 }}
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" 
                     style="width: {{ min(100, $cardStats['targets']['calling']['percentage'] ?? 0) }}%"
                     data-percentage="{{ $cardStats['targets']['calling']['percentage'] ?? 0 }}"></div>
            </div>
            <div class="progress-percentage">{{ round($cardStats['targets']['calling']['percentage'] ?? 0, 1) }}%</div>
        </div>

        <!-- Prospect Target Card -->
        <div class="dashboard-card target-card desktop-only">
            <div class="dashboard-card-title">
                Prospect Target 
                <span class="period-badge">{{ strtoupper($cardStats['targets']['prospect']['period'] ?? 'daily') }}</span>
            </div>
            <div class="dashboard-card-value">
                {{ $cardStats['targets']['prospect']['actual'] ?? 0 }} / {{ $cardStats['targets']['prospect']['target'] ?? 0 }}
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" 
                     style="width: {{ min(100, $cardStats['targets']['prospect']['percentage'] ?? 0) }}%"
                     data-percentage="{{ $cardStats['targets']['prospect']['percentage'] ?? 0 }}"></div>
            </div>
            <div class="progress-percentage">{{ round($cardStats['targets']['prospect']['percentage'] ?? 0, 1) }}%</div>
        </div>

        <!-- Visit Target Card -->
        <div class="dashboard-card target-card desktop-only">
            <div class="dashboard-card-title">
                Visit Target 
                <span class="period-badge">{{ strtoupper($cardStats['targets']['visit']['period'] ?? 'weekly') }}</span>
            </div>
            <div class="dashboard-card-value">
                {{ $cardStats['targets']['visit']['actual'] ?? 0 }} / {{ $cardStats['targets']['visit']['target'] ?? 0 }}
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" 
                     style="width: {{ min(100, $cardStats['targets']['visit']['percentage'] ?? 0) }}%"
                     data-percentage="{{ $cardStats['targets']['visit']['percentage'] ?? 0 }}"></div>
            </div>
            <div class="progress-percentage">{{ round($cardStats['targets']['visit']['percentage'] ?? 0, 1) }}%</div>
        </div>

            <!-- Phone: Consolidated Target Card -->
        <div class="consolidated-target-card mobile-only">
            <!-- Filter Buttons -->
            <div class="target-filter-buttons">
                <button type="button" 
                        class="filter-btn {{ ($targetFilter ?? 'today') === 'today' ? 'active' : '' }}"
                        onclick="updateTargetFilter('today')">
                    Today
                </button>
                <button type="button" 
                        class="filter-btn {{ ($targetFilter ?? 'today') === 'this_week' ? 'active' : '' }}"
                        onclick="updateTargetFilter('this_week')">
                    This Week
                </button>
                <button type="button" 
                        class="filter-btn {{ ($targetFilter ?? 'today') === 'this_month' ? 'active' : '' }}"
                        onclick="updateTargetFilter('this_month')">
                    This Month
                </button>
            </div>

            <!-- Calling Target Section -->
            <div class="target-section">
                <div class="target-section-header">
                    <span class="target-section-title">CALLING TARGET</span>
                    <span class="period-badge">{{ strtoupper($cardStats['targets']['calling']['period'] ?? 'daily') }}</span>
                </div>
                <div class="target-section-value">
                    {{ $cardStats['targets']['calling']['actual'] ?? 0 }} / {{ $cardStats['targets']['calling']['target'] ?? 0 }}
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" 
                         style="width: {{ min(100, $cardStats['targets']['calling']['percentage'] ?? 0) }}%"
                         data-percentage="{{ $cardStats['targets']['calling']['percentage'] ?? 0 }}"></div>
                </div>
                <div class="progress-percentage">{{ round($cardStats['targets']['calling']['percentage'] ?? 0, 1) }}%</div>
            </div>

            <!-- Prospect Target Section -->
            <div class="target-section">
                <div class="target-section-header">
                    <span class="target-section-title">PROSPECT TARGET</span>
                    <span class="period-badge">{{ strtoupper($cardStats['targets']['prospect']['period'] ?? 'daily') }}</span>
                </div>
                <div class="target-section-value">
                    {{ $cardStats['targets']['prospect']['actual'] ?? 0 }} / {{ $cardStats['targets']['prospect']['target'] ?? 0 }}
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" 
                         style="width: {{ min(100, $cardStats['targets']['prospect']['percentage'] ?? 0) }}%"
                         data-percentage="{{ $cardStats['targets']['prospect']['percentage'] ?? 0 }}"></div>
                </div>
                <div class="progress-percentage">{{ round($cardStats['targets']['prospect']['percentage'] ?? 0, 1) }}%</div>
            </div>

            <!-- Visit Target Section -->
            <div class="target-section">
                <div class="target-section-header">
                    <span class="target-section-title">VISIT TARGET</span>
                    <span class="period-badge">{{ strtoupper($cardStats['targets']['visit']['period'] ?? 'weekly') }}</span>
                </div>
                <div class="target-section-value">
                    {{ $cardStats['targets']['visit']['actual'] ?? 0 }} / {{ $cardStats['targets']['visit']['target'] ?? 0 }}
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" 
                         style="width: {{ min(100, $cardStats['targets']['visit']['percentage'] ?? 0) }}%"
                         data-percentage="{{ $cardStats['targets']['visit']['percentage'] ?? 0 }}"></div>
                </div>
                <div class="progress-percentage">{{ round($cardStats['targets']['visit']['percentage'] ?? 0, 1) }}%</div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Incentives Section -->
<div class="bg-white rounded-lg shadow p-6 mb-6" id="telecallerIncentivesSection">
    <h2 class="text-xl font-bold text-gray-900 mb-4">
        <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>Earn Incentive
    </h2>
    
    <!-- Incentive Potential -->
    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border-2 border-green-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-green-900">Incentive Potential</span>
            <span class="text-2xl font-bold text-green-700" id="telecallerIncentivePotential">₹0</span>
        </div>
        <p class="text-xs text-green-700" id="telecallerIncentivePotentialDetails">Target: 0 Visits × ₹0 = ₹0</p>
    </div>

    <!-- Eligible Site Visits for Incentive -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Eligible Site Visits</h3>
        <div id="eligibleSiteVisitsList" class="space-y-2">
            <p class="text-gray-500 text-sm">Loading...</p>
        </div>
    </div>

    <!-- Pending Incentives -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Pending Incentives</h3>
        <div id="telecallerPendingIncentivesList" class="space-y-2">
            <p class="text-gray-500 text-sm">No pending incentives</p>
        </div>
    </div>

    <!-- Verified Incentives -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Earned Incentives</h3>
        <div class="mb-3 p-3 bg-green-50 rounded-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Total Earned</span>
                <span class="text-xl font-bold text-green-700" id="telecallerTotalEarnedIncentives">₹0</span>
            </div>
        </div>
        <div id="telecallerVerifiedIncentivesList" class="space-y-2">
            <p class="text-gray-500 text-sm">No verified incentives yet</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Date range selector is now in the header
    // Functions are defined in the layout file
    
    function updateTargetMonth(month) {
        const url = new URL(window.location.href);
        url.searchParams.set('target_month', month);
        // Preserve target_filter if it exists
        const currentFilter = url.searchParams.get('target_filter') || 'today';
        url.searchParams.set('target_filter', currentFilter);
        window.location.href = url.toString();
    }

    function updateTargetFilter(filter) {
        const url = new URL(window.location.href);
        url.searchParams.set('target_filter', filter);
        // Preserve target_month if it exists
        const currentMonth = url.searchParams.get('target_month') || '{{ now()->format("Y-m") }}';
        url.searchParams.set('target_month', currentMonth);
        window.location.href = url.toString();
    }

    // Load Telecaller Incentives and Eligible Site Visits
    async function loadTelecallerIncentives() {
        try {
            const API_BASE_URL = '{{ url("/api/telecaller") }}';
            const token = localStorage.getItem('telecaller_token') || '{{ session("telecaller_api_token") ?? session("api_token") ?? "" }}';

            // Load dashboard data for incentives
            const dashboardResponse = await fetch('{{ url("/api/dashboard") }}', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                }
            });

            if (dashboardResponse.ok) {
                const dashboardData = await dashboardResponse.json();
                
                // Load incentives
                if (dashboardData.incentives) {
                    loadTelecallerIncentivesData(dashboardData.incentives);
                }

                // Load incentive potential
                if (dashboardData.incentive_potential) {
                    loadTelecallerIncentivePotential(dashboardData.incentive_potential);
                }
            }

            // Load eligible site visits
            const eligibleResponse = await fetch(`${API_BASE_URL}/site-visits/eligible-for-incentive`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                }
            });

            if (eligibleResponse.ok) {
                const eligibleData = await eligibleResponse.json();
                if (eligibleData.success && eligibleData.data) {
                    loadEligibleSiteVisits(eligibleData.data);
                }
            }
        } catch (error) {
            console.error('Error loading telecaller incentives:', error);
        }
    }

    function loadTelecallerIncentivesData(incentives) {
        // Pending incentives
        const pendingList = document.getElementById('telecallerPendingIncentivesList');
        if (incentives.pending && incentives.pending.length > 0) {
            pendingList.innerHTML = incentives.pending.map(inc => `
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${inc.site_visit?.customer_name || 'N/A'}</p>
                            <p class="text-xs text-gray-600">Status: ${inc.status === 'pending_sales_head' ? 'Awaiting Sales Head' : 'Awaiting CRM'}</p>
                        </div>
                        <span class="text-lg font-bold text-yellow-700">₹${parseFloat(inc.amount).toFixed(2)}</span>
                    </div>
                </div>
            `).join('');
        } else {
            pendingList.innerHTML = '<p class="text-gray-500 text-sm">No pending incentives</p>';
        }

        // Total earned
        document.getElementById('telecallerTotalEarnedIncentives').textContent = `₹${parseFloat(incentives.total_earned || 0).toFixed(2)}`;

        // Verified incentives
        const verifiedList = document.getElementById('telecallerVerifiedIncentivesList');
        if (incentives.verified && incentives.verified.length > 0) {
            verifiedList.innerHTML = incentives.verified.slice(0, 5).map(inc => `
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${inc.site_visit?.customer_name || 'N/A'}</p>
                            <p class="text-xs text-gray-600">Verified on ${inc.crm_verified_at ? new Date(inc.crm_verified_at).toLocaleDateString('en-IN') : 'N/A'}</p>
                        </div>
                        <span class="text-lg font-bold text-green-700">₹${parseFloat(inc.amount).toFixed(2)}</span>
                    </div>
                </div>
            `).join('');
        } else {
            verifiedList.innerHTML = '<p class="text-gray-500 text-sm">No verified incentives yet</p>';
        }
    }

    function loadTelecallerIncentivePotential(potential) {
        document.getElementById('telecallerIncentivePotential').textContent = `₹${parseFloat(potential.potential || 0).toFixed(2)}`;
        document.getElementById('telecallerIncentivePotentialDetails').textContent = 
            `Target: ${potential.target_visits || 0} Visits × ₹${parseFloat(potential.incentive_per_visit || 0).toFixed(2)} = ₹${parseFloat(potential.potential || 0).toFixed(2)}`;
    }

    function loadEligibleSiteVisits(siteVisits) {
        const list = document.getElementById('eligibleSiteVisitsList');
        if (siteVisits && siteVisits.length > 0) {
            list.innerHTML = siteVisits.map(sv => `
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${sv.customer_name || 'N/A'}</p>
                            <p class="text-xs text-gray-600">Completed: ${sv.completed_at ? new Date(sv.completed_at).toLocaleDateString('en-IN') : 'N/A'}</p>
                        </div>
                        <button onclick="requestSiteVisitIncentive(${sv.id})" 
                                class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg text-sm font-medium hover:from-green-700 hover:to-green-800 transition-all">
                            <i class="fas fa-hand-holding-usd mr-1"></i>Request Incentive
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            list.innerHTML = '<p class="text-gray-500 text-sm">No eligible site visits for incentive</p>';
        }
    }

    async function requestSiteVisitIncentive(siteVisitId) {
        const amount = prompt('Enter incentive amount:');
        if (!amount || parseFloat(amount) <= 0) {
            alert('Please enter a valid incentive amount');
            return;
        }

        try {
            const API_BASE_URL = '{{ url("/api/telecaller") }}';
            const token = localStorage.getItem('telecaller_token') || '{{ session("telecaller_api_token") ?? session("api_token") ?? "" }}';

            const response = await fetch(`${API_BASE_URL}/site-visits/${siteVisitId}/request-incentive`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ amount: parseFloat(amount) })
            });

            const result = await response.json();

            if (result && result.success) {
                alert('Incentive requested successfully! Awaiting verification.');
                loadTelecallerIncentives();
            } else {
                alert(result.message || 'Failed to request incentive');
            }
        } catch (error) {
            console.error('Error requesting incentive:', error);
            alert('Network error. Please try again.');
        }
    }

    // Load on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTelecallerIncentives();
    });
</script>
@endpush
