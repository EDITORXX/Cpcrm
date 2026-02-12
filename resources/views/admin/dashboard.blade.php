@extends('layouts.app')

@section('title', 'Admin Dashboard - Base CRM')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Welcome, ' . (auth()->user()->name ?? 'Admin') . ' (Admin)')

@section('header-actions')
<div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <a href="{{ route('admin.flow-test') }}" class="btn btn-brand-gradient" style="color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center;">
        <i class="fas fa-check-circle" style="margin-right: 5px;"></i> Flow Testing
    </a>
    <div class="dropdown" style="position: relative;">
        <button class="btn btn-brand-gradient" style="color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
            <i class="fas fa-download" style="margin-right: 5px;"></i> Export Leads
            <i class="fas fa-chevron-down" style="margin-left: 5px;"></i>
        </button>
        <div class="dropdown-menu" style="position: absolute; top: 100%; left: 0; background: white; border: 1px solid #E5DED4; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 8px; min-width: 200px; z-index: 1000; display: none;">
            <a href="{{ route('export.index') }}" style="display: block; padding: 10px; color: var(--text-color); text-decoration: none; border-radius: 4px;" onmouseover="this.style.background='#F7F6F3'" onmouseout="this.style.background='transparent'">
                <i class="fas fa-file-csv" style="margin-right: 8px;"></i> Custom Export
            </a>
            <form action="{{ route('export.prospects') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" style="width: 100%; text-align: left; padding: 10px; background: none; border: none; color: var(--text-color); cursor: pointer; border-radius: 4px;" onmouseover="this.style.background='#F7F6F3'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-user-check" style="margin-right: 8px;"></i> Export Prospects
                </button>
            </form>
            <form action="{{ route('export.meetings') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" style="width: 100%; text-align: left; padding: 10px; background: none; border: none; color: var(--text-color); cursor: pointer; border-radius: 4px;" onmouseover="this.style.background='#F7F6F3'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-calendar-check" style="margin-right: 8px;"></i> Export Meetings
                </button>
            </form>
            <form action="{{ route('export.site-visits') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" style="width: 100%; text-align: left; padding: 10px; background: none; border: none; color: var(--text-color); cursor: pointer; border-radius: 4px;" onmouseover="this.style.background='#F7F6F3'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> Export Site Visits
                </button>
            </form>
            <form action="{{ route('export.closed-leads') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" style="width: 100%; text-align: left; padding: 10px; background: none; border: none; color: var(--text-color); cursor: pointer; border-radius: 4px;" onmouseover="this.style.background='#F7F6F3'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i> Export Closed Leads
                </button>
            </form>
            <form action="{{ route('export.dead-leads') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" style="width: 100%; text-align: left; padding: 10px; background: none; border: none; color: var(--text-color); cursor: pointer; border-radius: 4px;" onmouseover="this.style.background='#F7F6F3'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> Export Dead Leads
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #E5DED4;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-color);
        margin-top: 8px;
    }
    .stat-label {
        font-size: 14px;
        color: #B3B5B4;
        font-weight: 500;
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
        background: white;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #E5DED4;
    }
    .section-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #E5DED4;
        padding: 24px;
        margin-bottom: 24px;
    }
    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E5DED4;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #E5DED4;
    }
    th {
        background: #F7F6F3;
        font-weight: 600;
        color: var(--text-color);
        font-size: 14px;
    }
    td {
        color: var(--text-color);
        font-size: 14px;
    }
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-new { background: #dbeafe; color: #1e40af; }
    .badge-connected { background: #bfdbfe; color: #1e3a8a; }
    .badge-verified-prospect { background: #e9d5ff; color: #6b21a8; }
    .badge-meeting-scheduled { background: #ddd6fe; color: #5b21b6; }
    .badge-meeting-completed { background: #cffafe; color: #155e75; }
    .badge-visit-scheduled { background: #ede9fe; color: #5b21b6; }
    .badge-visit-done { background: #fce7f3; color: #9f1239; }
    .badge-revisited-scheduled { background: #fce7f3; color: #9f1239; }
    .badge-revisited-completed { background: #fecdd3; color: #881337; }
    .badge-closed { background: #d1fae5; color: #065f46; }
    .badge-dead { background: #fee2e2; color: #991b1b; }
    .badge-on-hold { background: #f3f4f6; color: #374151; }
    .badge-contacted { background: #fef3c7; color: #92400e; }
    .badge-default { background: #f3f4f6; color: #6b7280; }
    .badge-qualified { background: #e9d5ff; color: #6b21a8; } /* Backward compatibility */
    .visits-meetings-filter-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .visits-meetings-filter-btn:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }
    .visits-meetings-filter-btn.active {
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .quick-action-btn {
        display: inline-block;
        padding: 12px 24px;
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        margin-right: 12px;
        margin-bottom: 12px;
        transition: opacity 0.2s;
    }
    .quick-action-btn:hover {
        opacity: 0.9;
    }
</style>
@endpush

@section('content')
<div id="dashboard-content">
    <!-- Loading State -->
    <div id="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2" style="border-color: var(--primary-color);"></div>
        <p class="mt-4 text-[#B3B5B4]">Loading dashboard data...</p>
    </div>

    <!-- Date Range Filter -->
    <div class="section-card mb-6">
        <div class="section-title mb-4">Filter Dashboard</div>
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex gap-2 flex-wrap">
                <button onclick="applyDateFilter('today')" id="filter-today" class="date-filter-btn">Today</button>
                <button onclick="applyDateFilter('week')" id="filter-week" class="date-filter-btn">This Week</button>
                <button onclick="applyDateFilter('month')" id="filter-month" class="date-filter-btn active">This Month</button>
                <button onclick="applyDateFilter('year')" id="filter-year" class="date-filter-btn">This Year</button>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-brand-primary font-medium">Custom:</label>
                <input type="date" id="custom-start-date" class="px-3 py-2 border border-[#E5DED4] rounded-lg text-sm bg-white text-brand-primary" onchange="applyCustomDateFilter()">
                <span class="text-[#B3B5B4]">to</span>
                <input type="date" id="custom-end-date" class="px-3 py-2 border border-[#E5DED4] rounded-lg text-sm bg-white text-brand-primary" onchange="applyCustomDateFilter()">
            </div>
        </div>
    </div>

    <!-- Main Stats Grid -->
    <div id="main-stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6 hidden">
        <div class="stat-card">
            <div class="stat-label">Total Leads</div>
            <div class="stat-value" id="total-leads">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Visits</div>
            <div class="stat-value" id="total-visits">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Meetings</div>
            <div class="stat-value" id="total-meetings">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Closers</div>
            <div class="stat-value" id="total-closers">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Dead</div>
            <div class="stat-value" id="total-dead">0</div>
        </div>
    </div>

    <!-- Lead Statistics and Property Segments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Lead Statistics -->
        <div class="section-card">
            <div class="section-title">Lead Statistics</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <div class="text-2xl font-bold text-brand-primary" id="leads-today">0</div>
                    <div class="text-sm text-[#B3B5B4]">New Leads (Today)</div>
                </div>
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <div class="text-2xl font-bold text-brand-primary" id="leads-week">0</div>
                    <div class="text-sm text-[#B3B5B4]">New Leads (This Week)</div>
                </div>
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <div class="text-2xl font-bold text-brand-primary" id="leads-month">0</div>
                    <div class="text-sm text-[#B3B5B4]">New Leads (This Month)</div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="leadStatusChart"></canvas>
            </div>
        </div>

        <!-- Property Segments -->
        <div class="section-card">
            <div class="section-title">Property Segments</div>
            <div class="chart-container">
                <canvas id="propertySegmentsChart"></canvas>
            </div>
            <div id="property-segments-legend" class="mt-4 flex flex-wrap gap-4 justify-center">
                <p class="text-[#B3B5B4]">Loading...</p>
            </div>
        </div>
    </div>

    <!-- Agents Visits vs Meetings -->
    <div class="section-card">
        <div class="section-title">Agents Visits vs Meetings</div>
        <div class="chart-container">
            <canvas id="agentsVisitsMeetingsChart"></canvas>
        </div>
        <div id="agents-visits-meetings-table" class="mt-4">
            <p class="text-[#B3B5B4]">Loading...</p>
        </div>
    </div>

    <!-- Sales Executive Performance -->
    <div class="section-card">
        <div class="section-title">Sales Executive Performance</div>
        <div id="telecaller-performance-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <p class="text-[#B3B5B4]">Loading...</p>
        </div>
    </div>

    <!-- Leads Allocated (75% No Response Yet + 25% Average Response Time) -->
    <div class="section-card">
        <div class="section-title">Leads Allocated</div>
        <div style="display: flex; gap: 24px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 0; min-height: 0; width: 75%;">
                <div class="section-title" style="font-size: 1rem; margin-bottom: 12px;">Leads Allocated</div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #F7F6F3; border-bottom: 2px solid #E5DED4;">
                                <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-color); width: 40px;"> </th>
                                <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-color);">User Name</th>
                                <th style="padding: 12px; text-align: center; font-weight: 600; color: var(--text-color);">Pending Count</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-color);">Oldest Assign</th>
                            </tr>
                        </thead>
                        <tbody id="leads-pending-response-tbody">
                            <tr>
                                <td colspan="4" style="padding: 20px; text-align: center; color: #B3B5B4;">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="width: 25%; min-width: 200px;">
                <div class="section-title" style="font-size: 1rem; margin-bottom: 12px;">Average Response</div>
                <div id="average-response-time-panel" style="min-height: 60px;">
                    <p class="text-sm text-[#B3B5B4]">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Visits & Meetings -->
    <div class="section-card">
        <div class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span>User Visits & Meetings</span>
            <a href="{{ route('export.index') }}" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; text-decoration: none;">
                <i class="fas fa-download" style="margin-right: 4px;"></i> Export
            </a>
        </div>
        
        <!-- Filter Buttons -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <button class="visits-meetings-filter-btn active" data-filter="today" onclick="filterVisitsMeetings('today', this)">
                Today
            </button>
            <button class="visits-meetings-filter-btn" data-filter="tomorrow" onclick="filterVisitsMeetings('tomorrow', this)">
                Tomorrow
            </button>
            <button class="visits-meetings-filter-btn" data-filter="this_weekend" onclick="filterVisitsMeetings('this_weekend', this)">
                This Weekend
            </button>
            <button class="visits-meetings-filter-btn" data-filter="this_month" onclick="filterVisitsMeetings('this_month', this)">
                This Month
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="visits-meetings-total-users">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Total Users</div>
            </div>
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="visits-meetings-total-visits">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Total Visits</div>
            </div>
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="visits-meetings-total-meetings">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Total Meetings</div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #F7F6F3; border-bottom: 2px solid #E5DED4;">
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-color); cursor: pointer;" onclick="sortTable('user_name')">
                            User Name <i class="fas fa-sort" style="margin-left: 5px;"></i>
                        </th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-color); cursor: pointer;" onclick="sortTable('role')">
                            Role <i class="fas fa-sort" style="margin-left: 5px;"></i>
                        </th>
                        <th style="padding: 12px; text-align: center; font-weight: 600; color: var(--text-color); cursor: pointer;" onclick="sortTable('visits_count')">
                            Visits Count <i class="fas fa-sort" style="margin-left: 5px;"></i>
                        </th>
                        <th style="padding: 12px; text-align: center; font-weight: 600; color: var(--text-color); cursor: pointer;" onclick="sortTable('meetings_count')">
                            Meetings Count <i class="fas fa-sort" style="margin-left: 5px;"></i>
                        </th>
                        <th style="padding: 12px; text-align: center; font-weight: 600; color: var(--text-color); cursor: pointer;" onclick="sortTable('total')">
                            Total <i class="fas fa-sort" style="margin-left: 5px;"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="visits-meetings-table-body">
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: #B3B5B4;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Call Statistics -->
    <div class="section-card" id="call-statistics-section">
        <div class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fas fa-phone mr-2"></i>System Call Statistics</span>
            <div style="display: flex; gap: 10px;">
                <button class="call-stats-filter-btn active" data-filter="today" onclick="loadCallStatistics('today', this)" style="padding: 6px 12px; border: 1px solid var(--border-color); background: var(--primary-color); color: white; border-radius: 6px; cursor: pointer; font-size: 12px;">Today</button>
                <button class="call-stats-filter-btn" data-filter="this_week" onclick="loadCallStatistics('this_week', this)" style="padding: 6px 12px; border: 1px solid var(--border-color); background: white; color: var(--text-color); border-radius: 6px; cursor: pointer; font-size: 12px;">This Week</button>
                <button class="call-stats-filter-btn" data-filter="this_month" onclick="loadCallStatistics('this_month', this)" style="padding: 6px 12px; border: 1px solid var(--border-color); background: white; color: var(--text-color); border-radius: 6px; cursor: pointer; font-size: 12px;">This Month</button>
                <a href="{{ route('calls.index') }}" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; text-decoration: none;">
                    <i class="fas fa-list" style="margin-right: 4px;"></i> View All
                </a>
            </div>
        </div>
        
        <!-- Call Stats Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="call-stats-total">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Total Calls</div>
            </div>
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="call-stats-duration">0s</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Total Duration</div>
            </div>
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="call-stats-avg-duration">0s</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Avg Duration</div>
            </div>
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="call-stats-connection-rate">0%</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Connection Rate</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Calls by Role Chart -->
            <div class="chart-container">
                <h3 style="font-size: 16px; font-weight: 600; color: var(--text-color); margin-bottom: 15px;">Calls by Role</h3>
                <canvas id="callsByRoleChart"></canvas>
            </div>

            <!-- Outcome Distribution Chart -->
            <div class="chart-container">
                <h3 style="font-size: 16px; font-weight: 600; color: var(--text-color); margin-bottom: 15px;">Call Outcome Distribution</h3>
                <canvas id="outcomeDistributionChart"></canvas>
            </div>
        </div>

        <!-- Top Users Table -->
        <div id="top-users-section" style="display: none;">
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-color); margin-bottom: 15px;">Top Users by Calls</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F7F6F3; border-bottom: 2px solid #E5DED4;">
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-color);">User Name</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600; color: var(--text-color);">Total Calls</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600; color: var(--text-color);">Total Duration</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600; color: var(--text-color);">Avg Duration</th>
                        </tr>
                    </thead>
                    <tbody id="top-users-table-body">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Calls -->
        <div id="recent-calls-section" style="margin-top: 20px; display: none;">
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-color); margin-bottom: 15px;">Recent Calls</h3>
            <div id="recent-calls-list" style="max-height: 300px; overflow-y: auto;">
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="section-card">
        <div class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span>User Statistics</span>
            <a href="{{ route('export.index') }}" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; text-decoration: none;">
                <i class="fas fa-download" style="margin-right: 4px;"></i> Export
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
            <div class="text-center p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="users-admin">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Admin</div>
            </div>
            <div class="text-center p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="users-crm">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">CRM</div>
            </div>
            <div class="text-center p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="users-sales-manager">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Senior Manager</div>
            </div>
            <div class="text-center p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="users-sales-executive">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Sales Executive</div>
            </div>
            <div class="text-center p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-2xl font-bold text-brand-primary" id="users-telecaller">0</div>
                <div class="text-sm text-[#B3B5B4] mt-1">Sales Executive</div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-lg font-semibold text-brand-primary" id="users-new-month">0</div>
                <div class="text-sm text-[#B3B5B4]">New Users (This Month)</div>
            </div>
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-lg font-semibold text-brand-primary" id="users-active-24h">0</div>
                <div class="text-sm text-[#B3B5B4]">Active Users (24h)</div>
            </div>
            <div class="p-4 bg-[#F7F6F3] rounded-lg">
                <div class="text-lg font-semibold text-brand-primary" id="users-total">0</div>
                <div class="text-sm text-[#B3B5B4]">Total Active Users</div>
            </div>
        </div>
    </div>

    <!-- System Health Stats -->
    <div id="health-stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 hidden">
        <div class="stat-card">
            <div class="stat-label">Pending Verifications</div>
            <div class="stat-value text-yellow-600" id="pending-verifications">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Automations</div>
            <div class="stat-value" id="active-automations">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Imports</div>
            <div class="stat-value" id="pending-imports">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Failed Imports (7d)</div>
            <div class="stat-value text-red-600" id="failed-imports">0</div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="section-card">
        <div class="section-title">Export Data</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
            <a href="{{ route('export.index') }}" class="quick-action-btn" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));">
                <i class="fas fa-file-csv" style="margin-right: 8px;"></i> Custom Export
            </a>
            <form action="{{ route('export.prospects') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" class="quick-action-btn" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); width: 100%;">
                    <i class="fas fa-user-check" style="margin-right: 8px;"></i> Export Prospects
                </button>
            </form>
            <form action="{{ route('export.meetings') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" class="quick-action-btn" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); width: 100%;">
                    <i class="fas fa-calendar-check" style="margin-right: 8px;"></i> Export Meetings
                </button>
            </form>
            <form action="{{ route('export.site-visits') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" class="quick-action-btn" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); width: 100%;">
                    <i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> Export Site Visits
                </button>
            </form>
            <form action="{{ route('export.closed-leads') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" class="quick-action-btn" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); width: 100%;">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i> Export Closed Leads
                </button>
            </form>
            <form action="{{ route('export.dead-leads') }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" class="quick-action-btn" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); width: 100%;">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> Export Dead Leads
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section-card">
        <div class="section-title">Quick Actions</div>
        <div>
            <a href="{{ route('users.create') }}" class="quick-action-btn">Create User</a>
            <a href="{{ route('lead-import.index') }}" class="quick-action-btn">Import Leads</a>
            <a href="{{ route('admin.targets.index') }}" class="quick-action-btn">View Targets</a>
            <a href="{{ route('admin.dead-leads') }}" class="quick-action-btn">Dead Leads</a>
            <a href="{{ route('smart-import.index') }}" class="quick-action-btn">Smart Import</a>
        </div>
    </div>

    <!-- System Targets Overview -->
    <div id="target-overview-section" class="section-card hidden">
        <div class="section-title">System Targets Overview</div>
        <div id="target-overview-content">
            <p class="text-gray-600">Loading targets...</p>
        </div>
    </div>

    <!-- Recent Leads -->
    <div class="section-card">
        <div class="section-title">Recent Leads</div>
        <div id="recent-leads">
            <p class="text-gray-600">Loading...</p>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="section-card">
        <div class="section-title">Recent System Activities</div>
        <div id="recent-activities">
            <p class="text-gray-600">Loading...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if(config('broadcasting.default') === 'pusher')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endif
<script>
    const API_BASE_URL = '/admin/dashboard/data';
    let leadStatusChart = null;
    let agentsVisitsMeetingsChart = null;
    let propertySegmentsChart = null;
    let currentFilter = localStorage.getItem('dashboardFilter') || 'month';
    let customStartDate = localStorage.getItem('dashboardStartDate') || '';
    let customEndDate = localStorage.getItem('dashboardEndDate') || '';

    // Initialize filter UI on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set active filter button
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const activeBtn = document.getElementById('filter-' + currentFilter);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        // Set custom dates if available
        if (customStartDate && customEndDate) {
            document.getElementById('custom-start-date').value = customStartDate;
            document.getElementById('custom-end-date').value = customEndDate;
        }
    });

    function applyDateFilter(filter) {
        currentFilter = filter;
        customStartDate = '';
        customEndDate = '';
        
        // Update active button
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById('filter-' + filter).classList.add('active');

        // Clear custom date inputs
        document.getElementById('custom-start-date').value = '';
        document.getElementById('custom-end-date').value = '';

        // Save to localStorage
        localStorage.setItem('dashboardFilter', filter);
        localStorage.removeItem('dashboardStartDate');
        localStorage.removeItem('dashboardEndDate');

        // Reload dashboard data
        loadDashboardData();
    }

    function applyCustomDateFilter() {
        const startDate = document.getElementById('custom-start-date').value;
        const endDate = document.getElementById('custom-end-date').value;

        if (!startDate || !endDate) {
            return;
        }

        customStartDate = startDate;
        customEndDate = endDate;
        currentFilter = 'custom';

        // Update active button
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Save to localStorage
        localStorage.setItem('dashboardFilter', 'custom');
        localStorage.setItem('dashboardStartDate', startDate);
        localStorage.setItem('dashboardEndDate', endDate);

        // Reload dashboard data
        loadDashboardData();
    }

    async function loadDashboardData() {
        try {
            // Show loading state
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('main-stats').classList.add('hidden');
            document.getElementById('health-stats').classList.add('hidden');

            // Build query parameters
            const params = new URLSearchParams();
            params.append('filter', currentFilter);
            if (customStartDate && customEndDate) {
                params.append('start_date', customStartDate);
                params.append('end_date', customEndDate);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(API_BASE_URL + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorText = await response.text();
                let errorData;
                try {
                    errorData = JSON.parse(errorText);
                } catch (e) {
                    errorData = { message: errorText || 'Failed to load dashboard data' };
                }
                throw new Error(errorData.message || errorData.error || 'Failed to load dashboard data');
            }

            const data = await response.json();
            
            // Debug: Log data to console
            console.log('Dashboard data received:', data);
            
            // Validate data structure
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid data received from server');
            }
            
            renderDashboard(data);
        } catch (error) {
            console.error('Error loading dashboard:', error);
            const loadingEl = document.getElementById('loading');
            if (loadingEl) {
                loadingEl.innerHTML = `<p class="text-red-600">Error: ${error.message}. Please refresh the page.</p>`;
            }
        }
    }

    function renderDashboard(data) {
        // Hide loading, show content
        const loadingEl = document.getElementById('loading');
        const mainStatsEl = document.getElementById('main-stats');
        const healthStatsEl = document.getElementById('health-stats');
        
        if (loadingEl) loadingEl.classList.add('hidden');
        if (mainStatsEl) mainStatsEl.classList.remove('hidden');
        if (healthStatsEl) healthStatsEl.classList.remove('hidden');

        // System Stats - with null checks
        const systemStats = data.system_stats || {};
        
        const leadsEl = document.getElementById('total-leads');
        if (leadsEl) leadsEl.textContent = systemStats.total_leads || 0;
        
        const visitsEl = document.getElementById('total-visits');
        if (visitsEl) visitsEl.textContent = systemStats.total_visits || 0;
        
        const meetingsEl = document.getElementById('total-meetings');
        if (meetingsEl) meetingsEl.textContent = systemStats.total_meetings || 0;
        
        const closersEl = document.getElementById('total-closers');
        if (closersEl) closersEl.textContent = systemStats.total_closers || 0;
        
        const deadEl = document.getElementById('total-dead');
        if (deadEl) deadEl.textContent = systemStats.total_dead || 0;

        // System Health - with null checks
        const systemHealth = data.system_health || {};
        const pendingVerEl = document.getElementById('pending-verifications');
        if (pendingVerEl) pendingVerEl.textContent = systemHealth.pending_verifications || 0;
        
        const activeAutoEl = document.getElementById('active-automations');
        if (activeAutoEl) activeAutoEl.textContent = systemHealth.active_automations || 0;
        
        const pendingImpEl = document.getElementById('pending-imports');
        if (pendingImpEl) pendingImpEl.textContent = systemHealth.pending_imports || 0;
        
        const failedImpEl = document.getElementById('failed-imports');
        if (failedImpEl) failedImpEl.textContent = systemHealth.failed_imports || 0;

        // User Stats - with null checks
        const userStats = data.user_stats || {};
        const byRole = userStats.by_role || {};
        const adminEl = document.getElementById('users-admin');
        if (adminEl) adminEl.textContent = byRole.admin || 0;
        
        const crmEl = document.getElementById('users-crm');
        if (crmEl) crmEl.textContent = byRole.crm || 0;
        
        const smEl = document.getElementById('users-sales-manager');
        if (smEl) smEl.textContent = byRole.sales_manager || 0;
        
        const seEl = document.getElementById('users-sales-executive');
        if (seEl) seEl.textContent = byRole.sales_executive || 0;
        
        const telEl = document.getElementById('users-telecaller');
        if (telEl) telEl.textContent = byRole.telecaller || 0;
        
        const newMonthEl = document.getElementById('users-new-month');
        if (newMonthEl) newMonthEl.textContent = userStats.new_this_month || 0;
        
        const active24hEl = document.getElementById('users-active-24h');
        if (active24hEl) active24hEl.textContent = userStats.active_24h || 0;
        
        const totalEl = document.getElementById('users-total');
        if (totalEl) totalEl.textContent = userStats.total || 0;

        // Lead Stats - with null checks
        const leadStats = data.lead_stats || {};
        const todayEl = document.getElementById('leads-today');
        if (todayEl) todayEl.textContent = leadStats.new_today || 0;
        
        const weekEl = document.getElementById('leads-week');
        if (weekEl) weekEl.textContent = leadStats.new_this_week || 0;
        
        const monthEl = document.getElementById('leads-month');
        if (monthEl) monthEl.textContent = leadStats.new_this_month || 0;

        // Lead Status Chart
        if (leadStats.by_status) {
            renderLeadStatusChart(leadStats.by_status);
        }

        // Target Overview
        if (data.target_overview) {
            renderTargetOverview(data.target_overview);
        }

        // Recent Leads
        if (data.recent_leads && Array.isArray(data.recent_leads)) {
            renderRecentLeads(data.recent_leads);
        } else {
            renderRecentLeads([]);
        }

        // Recent Activities
        if (data.recent_activities && Array.isArray(data.recent_activities)) {
            renderRecentActivities(data.recent_activities);
        } else {
            renderRecentActivities([]);
        }

        // Agents Visits vs Meetings
        if (data.agents_visits_meetings && Array.isArray(data.agents_visits_meetings)) {
            renderAgentsVisitsVsMeetings(data.agents_visits_meetings);
        } else {
            renderAgentsVisitsVsMeetings([]);
        }

        // Property Segments
        if (data.property_segments) {
            renderPropertySegments(data.property_segments);
        } else {
            renderPropertySegments({});
        }

        // Telecaller Performance
        console.log('Telecaller Performance Data:', data.telecaller_performance);
        if (data.telecaller_performance && Array.isArray(data.telecaller_performance)) {
            renderTelecallerPerformance(data.telecaller_performance);
        } else {
            console.warn('No telecaller performance data or invalid format');
            renderTelecallerPerformance([]);
        }

        // Leads Pending Response
        if (data.leads_pending_response && Array.isArray(data.leads_pending_response)) {
            renderLeadsPendingResponse(data.leads_pending_response, data.server_now);
        } else {
            renderLeadsPendingResponse([], data.server_now);
        }

        // Average Response Time
        if (data.average_response_time_by_user && Array.isArray(data.average_response_time_by_user)) {
            renderAverageResponseTime(data.average_response_time_by_user);
        } else {
            renderAverageResponseTime([]);
        }

        // Call Statistics
        if (data.call_statistics) {
            updateCallStatistics(data.call_statistics);
        }

        // User Visits & Meetings - Load with default filter
        const visitsMeetingsFilter = localStorage.getItem('visitsMeetingsFilter') || 'this_month';
        loadUserVisitsMeetings(visitsMeetingsFilter);
    }

    function renderLeadStatusChart(statusData) {
        const ctx = document.getElementById('leadStatusChart');
        if (leadStatusChart) {
            leadStatusChart.destroy();
        }

        const labels = Object.keys(statusData);
        const values = Object.values(statusData);

        leadStatusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.map(label => label.replace(/_/g, ' ').toUpperCase()),
                datasets: [{
                    label: 'Leads by Status',
                    data: values,
                    backgroundColor: 'var(--primary-color)',
                    borderColor: 'var(--secondary-color)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function renderTargetOverview(overview) {
        const section = document.getElementById('target-overview-section');
        const content = document.getElementById('target-overview-content');
        
        if (!overview || !overview.targets) {
            section.classList.add('hidden');
            return;
        }

        section.classList.remove('hidden');
        const ov = overview;
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <h4 class="font-semibold text-brand-primary mb-2">Total Prospects Extract</h4>
                    <div class="text-2xl font-bold text-gray-700">${ov.actuals?.prospects_extract || 0} / ${ov.targets?.prospects_extract || 0}</div>
                    <div class="text-sm text-gray-600">${Math.round(ov.percentages?.prospects_extract || 0)}% Complete</div>
                </div>
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <h4 class="font-semibold text-brand-primary mb-2">Total Prospects Verified</h4>
                    <div class="text-2xl font-bold text-gray-700">${ov.actuals?.prospects_verified || 0} / ${ov.targets?.prospects_verified || 0}</div>
                    <div class="text-sm text-gray-600">${Math.round(ov.percentages?.prospects_verified || 0)}% Complete</div>
                </div>
                <div class="p-4 bg-[#F7F6F3] rounded-lg">
                    <h4 class="font-semibold text-brand-primary mb-2">Total Calls</h4>
                    <div class="text-2xl font-bold text-gray-700">${ov.actuals?.calls || 0} / ${ov.targets?.calls || 0}</div>
                    <div class="text-sm text-gray-600">${Math.round(ov.percentages?.calls || 0)}% Complete</div>
                </div>
            </div>
            <p class="text-sm text-gray-600">Total Users: ${ov.total_users || 0} | Month: ${ov.month || 'N/A'}</p>
        `;
    }

    // Status mapping function for display
    function getStatusDisplay(status) {
        const statusMap = {
            // New statuses
            'new': { label: 'New', class: 'badge-new' },
            'connected': { label: 'Connected', class: 'badge-connected' },
            'verified_prospect': { label: 'Verified Prospect', class: 'badge-verified-prospect' },
            'meeting_scheduled': { label: 'Meeting Scheduled', class: 'badge-meeting-scheduled' },
            'meeting_completed': { label: 'Meeting Completed', class: 'badge-meeting-completed' },
            'visit_scheduled': { label: 'Visit Scheduled', class: 'badge-visit-scheduled' },
            'visit_done': { label: 'Visit Done', class: 'badge-visit-done' },
            'revisited_scheduled': { label: 'Revisit Scheduled', class: 'badge-revisited-scheduled' },
            'revisited_completed': { label: 'Revisit Completed', class: 'badge-revisited-completed' },
            'closed': { label: 'Closed', class: 'badge-closed' },
            'dead': { label: 'Dead', class: 'badge-dead' },
            'on_hold': { label: 'On Hold', class: 'badge-on-hold' },
            // Old statuses (for backward compatibility during migration)
            'contacted': { label: 'Contacted', class: 'badge-contacted' },
            'qualified': { label: 'Verified Prospect', class: 'badge-verified-prospect' },
            'site_visit_scheduled': { label: 'Visit Scheduled', class: 'badge-visit-scheduled' },
            'site_visit_completed': { label: 'Visit Done', class: 'badge-visit-done' },
            'closed_won': { label: 'Closed', class: 'badge-closed' },
            'closed_lost': { label: 'Dead', class: 'badge-dead' },
            'negotiation': { label: 'Negotiation', class: 'badge-negotiation' },
        };
        
        const statusInfo = statusMap[status] || { label: status || 'N/A', class: 'badge-default' };
        return statusInfo;
    }

    function renderRecentLeads(leads) {
        const container = document.getElementById('recent-leads');
        
        if (!leads || !Array.isArray(leads) || leads.length === 0) {
            container.innerHTML = '<p class="text-gray-600">No recent leads found</p>';
            return;
        }

        const tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    ${leads.filter(lead => lead !== null && lead !== undefined).map(lead => {
                        const statusInfo = getStatusDisplay(lead?.status);
                        return `
                        <tr>
                            <td>${lead?.name || 'N/A'}</td>
                            <td>${lead?.phone || 'N/A'}</td>
                            <td><span class="badge ${statusInfo.class}">${statusInfo.label}</span></td>
                            <td>${lead?.created_by || 'System'}</td>
                            <td>${lead?.created_at ? new Date(lead.created_at).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                    `;
                    }).join('')}
                </tbody>
            </table>
        `;
        container.innerHTML = tableHtml;
    }

    function renderRecentActivities(activities) {
        const container = document.getElementById('recent-activities');
        
        if (!activities || !Array.isArray(activities) || activities.length === 0) {
            container.innerHTML = '<p class="text-gray-600">No recent activities found</p>';
            return;
        }

        const tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    ${activities.filter(activity => activity !== null && activity !== undefined).map(activity => `
                        <tr>
                            <td>${activity?.user_name || 'System'}</td>
                            <td><span class="badge">${activity?.action || 'N/A'}</span></td>
                            <td>${activity?.description || 'N/A'}</td>
                            <td>${activity?.created_at ? new Date(activity.created_at).toLocaleString() : 'N/A'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        container.innerHTML = tableHtml;
    }

    function renderAgentsVisitsVsMeetings(agentsData) {
        const chartContainer = document.getElementById('agentsVisitsMeetingsChart');
        const tableContainer = document.getElementById('agents-visits-meetings-table');
        
        if (!agentsData || !Array.isArray(agentsData) || agentsData.length === 0) {
            if (tableContainer) {
                tableContainer.innerHTML = '<p class="text-[#B3B5B4]">No agent data available</p>';
            }
            return;
        }

        // Destroy existing chart if it exists
        if (agentsVisitsMeetingsChart) {
            agentsVisitsMeetingsChart.destroy();
        }

        // Prepare data for chart
        const labels = agentsData.map(agent => agent.agent_name || 'Unknown');
        const meetingsData = agentsData.map(agent => agent.meetings || 0);
        const visitsData = agentsData.map(agent => agent.visits || 0);
        const closersData = agentsData.map(agent => agent.closers || 0);

        // Create chart
        if (chartContainer) {
            agentsVisitsMeetingsChart = new Chart(chartContainer, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Meetings',
                            data: meetingsData,
                            backgroundColor: '#3B82F6',
                            borderColor: '#2563EB',
                            borderWidth: 1
                        },
                        {
                            label: 'Visits',
                            data: visitsData,
                            backgroundColor: '#10B981',
                            borderColor: '#059669',
                            borderWidth: 1
                        },
                        {
                            label: 'Closers',
                            data: closersData,
                            backgroundColor: '#F59E0B',
                            borderColor: '#D97706',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                },
                plugins: [{
                    id: 'showValues',
                    afterDatasetsDraw: (chart) => {
                        const ctx = chart.ctx;
                        ctx.save();
                        ctx.font = 'bold 12px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim() || '#063A1C';
                        
                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const value = dataset.data[index];
                                if (value > 0) {
                                    ctx.fillText(value, bar.x, bar.y - 5);
                                }
                            });
                        });
                        ctx.restore();
                    }
                }]
            });
        }

        // Render table
        if (tableContainer) {
            const tableHtml = `
                <table>
                    <thead>
                        <tr>
                            <th>Agent Name</th>
                            <th>Role</th>
                            <th>Meetings</th>
                            <th>Visits</th>
                            <th>Closers</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${agentsData.map(agent => {
                            const total = (agent.meetings || 0) + (agent.visits || 0) + (agent.closers || 0);
                            return `
                                <tr>
                                    <td>${agent.agent_name || 'Unknown'}</td>
                                    <td><span class="badge">${agent.role || 'N/A'}</span></td>
                                    <td>${agent.meetings || 0}</td>
                                    <td>${agent.visits || 0}</td>
                                    <td>${agent.closers || 0}</td>
                                    <td class="font-semibold">${total}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            `;
            tableContainer.innerHTML = tableHtml;
        }
    }

    function renderPropertySegments(segmentsData) {
        const chartContainer = document.getElementById('propertySegmentsChart');
        const legendContainer = document.getElementById('property-segments-legend');
        
        if (!segmentsData || Object.keys(segmentsData).length === 0) {
            if (legendContainer) {
                legendContainer.innerHTML = '<p class="text-[#B3B5B4]">No property segment data available</p>';
            }
            return;
        }

        // Destroy existing chart if it exists
        if (propertySegmentsChart) {
            propertySegmentsChart.destroy();
        }

        // Prepare data
        const labels = ['Plot', 'Commercial', 'Residential', 'Other'];
        const data = [
            segmentsData.plot || 0,
            segmentsData.commercial || 0,
            segmentsData.residential || 0,
            segmentsData.other || 0
        ];
        const colors = ['#F59E0B', '#3B82F6', '#10B981', '#6B7280'];
        const total = data.reduce((sum, val) => sum + val, 0);

        // Create donut chart
        if (chartContainer) {
            propertySegmentsChart = new Chart(chartContainer, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2
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
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Render legend
        if (legendContainer) {
            const legendHtml = labels.map((label, index) => {
                const value = data[index];
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                return `
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded" style="background-color: ${colors[index]}"></div>
                        <span class="text-sm text-brand-primary font-medium">${label}:</span>
                        <span class="text-sm font-bold text-brand-primary">${value}</span>
                        <span class="text-xs text-[#B3B5B4]">(${percentage}%)</span>
                    </div>
                `;
            }).join('');
            legendContainer.innerHTML = legendHtml;
        }
    }

    function renderTelecallerPerformance(telecallersData) {
        const container = document.getElementById('telecaller-performance-cards');
        
        if (!telecallersData || !Array.isArray(telecallersData) || telecallersData.length === 0) {
            if (container) {
                container.innerHTML = '<p class="text-[#B3B5B4]">No sales executive performance data available</p>';
            }
            return;
        }

        const cardsHtml = telecallersData.map(telecaller => {
            // Use same card style as CRM dashboard
            const allocated = (telecaller.allocated !== undefined && telecaller.allocated !== null) ? telecaller.allocated : 0;
            const called = (telecaller.called !== undefined && telecaller.called !== null) ? telecaller.called : 0;
            const remaining = (telecaller.remaining !== undefined && telecaller.remaining !== null) ? telecaller.remaining : 0;
            const interested = (telecaller.interested !== undefined && telecaller.interested !== null) ? telecaller.interested : 0;
            const notInterested = (telecaller.not_interested !== undefined && telecaller.not_interested !== null) ? telecaller.not_interested : 0;
            const cnp = (telecaller.cnp !== undefined && telecaller.cnp !== null) ? telecaller.cnp : 0;
            
            return `
                <div class="rounded-lg p-6 text-white shadow-md hover:shadow-lg transition-shadow" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));">
                    <h3 class="text-xl font-bold mb-4 text-center text-white">${telecaller.telecaller_name || 'Unknown'}</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="text-sm opacity-90 block">Allocated</span>
                            <span class="text-lg font-bold block">${allocated}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">Called</span>
                            <span class="text-lg font-bold block">${called}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">Remaining</span>
                            <span class="text-lg font-bold block">${remaining}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">Interested</span>
                            <span class="text-lg font-bold block" style="color: #90ee90;">${interested}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">Not Interested</span>
                            <span class="text-lg font-bold block" style="color: #ffb3b3;">${notInterested}</span>
                        </div>
                        <div>
                            <span class="text-sm opacity-90 block">CNP</span>
                            <span class="text-lg font-bold block text-white">${cnp}</span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        if (container) {
            container.innerHTML = cardsHtml;
        }
    }

    function formatAssignedAt(isoString, nowIso) {
        if (!isoString) return '—';
        const d = new Date(isoString);
        if (isNaN(d.getTime())) return isoString;
        const now = nowIso ? new Date(nowIso) : new Date();
        const diffMs = now - d;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        if (diffMins < 60) return diffMins <= 1 ? '1m ago' : diffMins + 'm ago';
        if (diffHours < 24) return diffHours === 1 ? '1h ago' : diffHours + 'h ago';
        if (diffDays < 7) return diffDays === 1 ? '1d ago' : diffDays + 'd ago';
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function formatAssignedAtFull(isoString) {
        if (!isoString) return '—';
        const d = new Date(isoString);
        if (isNaN(d.getTime())) return isoString;
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function maskPhone(phone) {
        if (!phone || typeof phone !== 'string') return '—';
        const digits = phone.replace(/\D/g, '');
        if (digits.length < 4) return '****';
        return digits.slice(0, 2) + '****' + digits.slice(-4);
    }

    function formatAvgResponseTime(avgResponseMinutes) {
        if (avgResponseMinutes == null || avgResponseMinutes === 0 || isNaN(avgResponseMinutes)) return '0 min';
        const m = Math.round(Number(avgResponseMinutes));
        if (m < 60) return m + ' min';
        const h = Math.floor(m / 60);
        const min = m % 60;
        return min > 0 ? (h + 'h ' + min + 'm') : (h + 'h');
    }

    function renderAverageResponseTime(list) {
        const panel = document.getElementById('average-response-time-panel');
        if (!panel) return;
        let html = '<table style="width: 100%; border-collapse: collapse; font-size: 14px;"><thead><tr style="background: #F7F6F3; border-bottom: 2px solid #E5DED4;"><th style="padding: 8px 12px; text-align: left; font-weight: 600; color: var(--text-color);">User Name</th><th style="padding: 8px 12px; text-align: right; font-weight: 600; color: var(--text-color);">Avg Time</th></tr></thead><tbody>';
        if (!list || list.length === 0) {
            html += '<tr><td colspan="2" style="padding: 12px; text-align: center; color: #B3B5B4;">No users in this role.</td></tr>';
        } else {
            list.forEach(function(row) {
                const name = escapeHtml(row.user_name || '');
                const timeStr = formatAvgResponseTime(row.avg_response_minutes);
                html += '<tr style="border-bottom: 1px solid #eee;"><td style="padding: 8px 12px;">' + name + '</td><td style="padding: 8px 12px; text-align: right; font-weight: 600; color: var(--text-color);">' + timeStr + '</td></tr>';
            });
        }
        html += '</tbody></table>';
        panel.innerHTML = html;
    }

    function renderLeadsPendingResponse(data, serverNow) {
        const tbody = document.getElementById('leads-pending-response-tbody');
        if (!tbody) return;

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #B3B5B4;">No leads pending response.</td></tr>';
            return;
        }

        const leadShowBase = "{{ url('/leads') }}";
        let html = '';
        data.forEach((row, index) => {
            const leads = row.leads || [];
            const oldestAssignedAt = leads.length > 0
                ? leads.reduce((min, l) => (!l.assigned_at ? min : (!min || l.assigned_at < min ? l.assigned_at : min)), null)
                : null;
            const oldestAssign = oldestAssignedAt ? formatAssignedAt(oldestAssignedAt, serverNow) : '—';
            const rowId = 'pending-row-' + row.user_id;
            const detailId = 'pending-detail-' + row.user_id;
            html += `
                <tr class="leads-pending-user-row" data-user-id="${row.user_id}" style="background: white; border-bottom: 1px solid #E5DED4; cursor: pointer;" onclick="toggleLeadsPendingDetail('${detailId}', '${rowId}')">
                    <td style="padding: 12px;"><i class="fas fa-chevron-right leads-pending-chevron" id="chevron-${rowId}" style="color: #B3B5B4;"></i></td>
                    <td style="padding: 12px; font-weight: 500;">${escapeHtml(row.user_name || '')}</td>
                    <td style="padding: 12px; text-align: center;">${row.pending_count || 0}</td>
                    <td style="padding: 12px; color: #666;">${oldestAssign}</td>
                </tr>
                <tr id="${detailId}" class="leads-pending-detail-row" style="display: none;">
                    <td colspan="4" style="padding: 0; border-bottom: 1px solid #E5DED4; background: #FAFAF9;">
                        <div style="padding: 12px 12px 12px 48px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 1px solid #E5DED4;">
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 600; color: #666; font-size: 12px;">Lead Name</th>
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 600; color: #666; font-size: 12px;">Phone</th>
                                        <th style="padding: 8px 12px; text-align: left; font-weight: 600; color: #666; font-size: 12px;">Assigned At</th>
                                        <th style="padding: 8px 12px; width: 80px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${(row.leads || []).length === 0 ? '<tr><td colspan="4" style="padding: 12px; text-align: center; color: #B3B5B4;">No pending leads.</td></tr>' : (row.leads || []).map(lead => `
                                        <tr style="border-bottom: 1px solid #eee;">
                                            <td style="padding: 8px 12px;">${escapeHtml(lead.name || '—')}</td>
                                            <td style="padding: 8px 12px;">${maskPhone(lead.phone)}</td>
                                            <td style="padding: 8px 12px;">${formatAssignedAtFull(lead.assigned_at)}</td>
                                            <td style="padding: 8px 12px;"><a href="${leadShowBase}/${lead.lead_id}" style="color: var(--primary-color); font-size: 12px;">View</a></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function toggleLeadsPendingDetail(detailId, rowId) {
        const detailRow = document.getElementById(detailId);
        const chevron = document.getElementById('chevron-' + rowId);
        if (!detailRow || !chevron) return;
        const isHidden = detailRow.style.display === 'none';
        detailRow.style.display = isHidden ? 'table-row' : 'none';
        chevron.className = isHidden ? 'fas fa-chevron-down leads-pending-chevron' : 'fas fa-chevron-right leads-pending-chevron';
        if (chevron.style) chevron.style.color = '#B3B5B4';
    }

    function escapeHtml(text) {
        if (text == null) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // User Visits & Meetings Functions
    let currentVisitsMeetingsFilter = localStorage.getItem('visitsMeetingsFilter') || 'this_month';
    let visitsMeetingsSortColumn = 'total';
    let visitsMeetingsSortDirection = 'desc';

    // Initialize filter button on page load
    document.addEventListener('DOMContentLoaded', function() {
        const activeBtn = document.querySelector(`.visits-meetings-filter-btn[data-filter="${currentVisitsMeetingsFilter}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    });

    async function loadUserVisitsMeetings(filter) {
        try {
            currentVisitsMeetingsFilter = filter;
            localStorage.setItem('visitsMeetingsFilter', filter);

            // Update active button
            document.querySelectorAll('.visits-meetings-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            const activeBtn = document.querySelector(`.visits-meetings-filter-btn[data-filter="${filter}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            // Show loading state
            const tableBody = document.getElementById('visits-meetings-table-body');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #B3B5B4;">Loading...</td></tr>';
            }

            // Build query parameters
            const params = new URLSearchParams();
            params.append('visits_meetings_filter', filter);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(API_BASE_URL + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load visits/meetings data');
            }

            const data = await response.json();
            
            if (data.user_visits_meetings) {
                renderVisitsMeetingsSummary(data.user_visits_meetings.summary);
                renderVisitsMeetingsTable(data.user_visits_meetings.users);
            } else {
                renderVisitsMeetingsSummary({ total_users: 0, total_visits: 0, total_meetings: 0 });
                renderVisitsMeetingsTable([]);
            }
        } catch (error) {
            console.error('Error loading visits/meetings data:', error);
            const tableBody = document.getElementById('visits-meetings-table-body');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #ef4444;">Error loading data. Please try again.</td></tr>';
            }
        }
    }

    function filterVisitsMeetings(filter, buttonElement) {
        loadUserVisitsMeetings(filter);
    }

    function renderVisitsMeetingsSummary(summary) {
        const totalUsersEl = document.getElementById('visits-meetings-total-users');
        const totalVisitsEl = document.getElementById('visits-meetings-total-visits');
        const totalMeetingsEl = document.getElementById('visits-meetings-total-meetings');

        if (totalUsersEl) totalUsersEl.textContent = summary.total_users || 0;
        if (totalVisitsEl) totalVisitsEl.textContent = summary.total_visits || 0;
        if (totalMeetingsEl) totalMeetingsEl.textContent = summary.total_meetings || 0;
    }

    function renderVisitsMeetingsTable(users) {
        const tableBody = document.getElementById('visits-meetings-table-body');
        
        if (!tableBody) return;

        if (!users || users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #B3B5B4;">No data available</td></tr>';
            return;
        }

        // Sort data
        const sortedUsers = [...users].sort((a, b) => {
            let aVal, bVal;
            switch(visitsMeetingsSortColumn) {
                case 'user_name':
                    aVal = a.user_name || '';
                    bVal = b.user_name || '';
                    return visitsMeetingsSortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                case 'role':
                    aVal = a.role || '';
                    bVal = b.role || '';
                    return visitsMeetingsSortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                case 'visits_count':
                    aVal = a.visits_count || 0;
                    bVal = b.visits_count || 0;
                    return visitsMeetingsSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                case 'meetings_count':
                    aVal = a.meetings_count || 0;
                    bVal = b.meetings_count || 0;
                    return visitsMeetingsSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                case 'total':
                default:
                    aVal = a.total || 0;
                    bVal = b.total || 0;
                    return visitsMeetingsSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
            }
        });

        const rowsHtml = sortedUsers.map(user => `
            <tr style="border-bottom: 1px solid #E5DED4;" onmouseover="this.style.background='#F7F6F3'" onmouseout="this.style.background='transparent'">
                <td style="padding: 12px; color: var(--text-color);">${user.user_name || 'N/A'}</td>
                <td style="padding: 12px; color: var(--text-color);">${user.role || 'N/A'}</td>
                <td style="padding: 12px; text-align: center; color: var(--text-color); font-weight: 600;">${user.visits_count || 0}</td>
                <td style="padding: 12px; text-align: center; color: var(--text-color); font-weight: 600;">${user.meetings_count || 0}</td>
                <td style="padding: 12px; text-align: center; color: var(--text-color); font-weight: 700;">${user.total || 0}</td>
            </tr>
        `).join('');

        tableBody.innerHTML = rowsHtml;
    }

    function sortTable(column) {
        if (visitsMeetingsSortColumn === column) {
            visitsMeetingsSortDirection = visitsMeetingsSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            visitsMeetingsSortColumn = column;
            visitsMeetingsSortDirection = 'desc';
        }

        // Reload table with new sort
        loadUserVisitsMeetings(currentVisitsMeetingsFilter);
    }

    // Initial load
    loadDashboardData();

    // Auto-refresh every 30 seconds
    setInterval(loadDashboardData, 30000);

    // Export dropdown toggle
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.querySelector('.dropdown button');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        
        if (exportBtn && dropdownMenu) {
            exportBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!exportBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.style.display = 'none';
                }
            });
        }
    });
    
    // Call Statistics Functions
    let callsByRoleChart = null;
    let outcomeDistributionChart = null;
    let currentCallStatsFilter = 'today';
    
    async function loadCallStatistics(filter = 'today', buttonElement = null) {
        currentCallStatsFilter = filter;
        localStorage.setItem('callStatsFilter', filter);
        
        // Update button states
        if (buttonElement) {
            document.querySelectorAll('.call-stats-filter-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.style.background = 'white';
                btn.style.color = 'var(--text-color)';
            });
            buttonElement.classList.add('active');
            buttonElement.style.background = 'var(--primary-color)';
            buttonElement.style.color = 'white';
        }
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const params = new URLSearchParams();
            params.append('date_range', filter);
            
            const response = await fetch(API_BASE_URL + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            
            if (!response.ok) {
                throw new Error('Failed to load call statistics');
            }
            
            const data = await response.json();
            
            if (data.call_statistics) {
                updateCallStatistics(data.call_statistics);
            }
        } catch (error) {
            console.error('Error loading call statistics:', error);
        }
    }
    
    function updateCallStatistics(stats) {
        // Update summary cards
        const totalEl = document.getElementById('call-stats-total');
        if (totalEl) totalEl.textContent = stats.total_calls || 0;
        
        const durationEl = document.getElementById('call-stats-duration');
        if (durationEl) durationEl.textContent = stats.formatted_duration || '0s';
        
        const avgDurationEl = document.getElementById('call-stats-avg-duration');
        if (avgDurationEl) avgDurationEl.textContent = stats.formatted_average_duration || '0s';
        
        const connectionRateEl = document.getElementById('call-stats-connection-rate');
        if (connectionRateEl) connectionRateEl.textContent = (stats.connection_rate || 0).toFixed(1) + '%';
        
        // Update Calls by Role Chart
        if (stats.calls_by_role && stats.calls_by_role.length > 0) {
            updateCallsByRoleChart(stats.calls_by_role);
        }
        
        // Update Outcome Distribution Chart
        if (stats.outcome_distribution && stats.outcome_distribution.length > 0) {
            updateOutcomeDistributionChart(stats.outcome_distribution);
        }
        
        // Update Top Users Table
        if (stats.top_users && stats.top_users.length > 0) {
            const tbody = document.getElementById('top-users-table-body');
            if (tbody) {
                tbody.innerHTML = stats.top_users.map(user => `
                    <tr style="border-bottom: 1px solid #E5DED4;">
                        <td style="padding: 12px; color: var(--text-color);">${user.user_name}</td>
                        <td style="padding: 12px; text-align: center; color: var(--text-color);">${user.total_calls}</td>
                        <td style="padding: 12px; text-align: center; color: var(--text-color);">${user.formatted_duration}</td>
                        <td style="padding: 12px; text-align: center; color: var(--text-color);">${user.formatted_average_duration}</td>
                    </tr>
                `).join('');
            }
            const topUsersSection = document.getElementById('top-users-section');
            if (topUsersSection) topUsersSection.style.display = 'block';
        }
        
        // Update Recent Calls
        if (stats.recent_calls && stats.recent_calls.length > 0) {
            const list = document.getElementById('recent-calls-list');
            if (list) {
                list.innerHTML = stats.recent_calls.map(call => `
                    <div style="padding: 12px; border-bottom: 1px solid #E5DED4; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600; color: var(--text-color);">${call.lead_name}</div>
                            <div style="font-size: 12px; color: #B3B5B4;">${call.phone_number} • ${call.duration} • ${new Date(call.start_time).toLocaleString()}</div>
                        </div>
                        <a href="/calls/${call.id}" style="color: var(--link-color); text-decoration: none;">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                `).join('');
            }
            const recentCallsSection = document.getElementById('recent-calls-section');
            if (recentCallsSection) recentCallsSection.style.display = 'block';
        }
    }
    
    function updateCallsByRoleChart(data) {
        const ctx = document.getElementById('callsByRoleChart');
        if (!ctx) return;
        
        if (callsByRoleChart) {
            callsByRoleChart.destroy();
        }
        
        const labels = data.map(item => item.role_name);
        const callCounts = data.map(item => item.call_count);
        
        callsByRoleChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Calls',
                    data: callCounts,
                    backgroundColor: [
                        'rgba(32, 90, 68, 0.6)',
                        'rgba(6, 58, 28, 0.6)',
                        'rgba(21, 128, 61, 0.6)',
                        'rgba(179, 181, 180, 0.6)',
                    ],
                    borderColor: [
                        'rgba(32, 90, 68, 1)',
                        'rgba(6, 58, 28, 1)',
                        'rgba(21, 128, 61, 1)',
                        'rgba(179, 181, 180, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    function updateOutcomeDistributionChart(data) {
        const ctx = document.getElementById('outcomeDistributionChart');
        if (!ctx) return;
        
        if (outcomeDistributionChart) {
            outcomeDistributionChart.destroy();
        }
        
        const labels = data.map(item => item.label);
        const counts = data.map(item => item.count);
        
        outcomeDistributionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        'rgba(32, 90, 68, 0.6)',
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(234, 179, 8, 0.6)',
                        'rgba(168, 85, 247, 0.6)',
                        'rgba(107, 114, 128, 0.6)',
                    ],
                    borderColor: [
                        'rgba(32, 90, 68, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(234, 179, 8, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(107, 114, 128, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Real-time call log updates via Pusher
    @if(config('broadcasting.default') === 'pusher')
    document.addEventListener('DOMContentLoaded', function() {
        const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
            encrypted: true
        });
        
        const callLogsChannel = pusher.subscribe('call-logs');
        callLogsChannel.bind('call-log.created', function(data) {
            // Reload call statistics
            if (typeof loadCallStatistics === 'function') {
                loadCallStatistics(currentCallStatsFilter);
            }
        });
        
        // Subscribe to admin channel
        const adminChannel = pusher.subscribe('private-admin');
        adminChannel.bind('call-log.created', function(data) {
            if (typeof loadCallStatistics === 'function') {
                loadCallStatistics(currentCallStatsFilter);
            }
        });
    });
    @endif
</script>
@endpush

