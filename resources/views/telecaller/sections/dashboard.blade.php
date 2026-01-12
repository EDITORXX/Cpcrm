@extends('telecaller.layout')

@section('title', 'Dashboard - Telecaller')
@section('page-title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/telecaller-dashboard.css') }}">
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Date Filter Section -->
    <div class="dashboard-filters mb-6">
        <div class="filter-buttons">
            <button class="filter-btn {{ ($dateRange ?? 'today') === 'today' ? 'active' : '' }}" data-filter="today" onclick="applyDateFilter('today')">
                Today
            </button>
            <button class="filter-btn {{ ($dateRange ?? 'today') === 'this_week' ? 'active' : '' }}" data-filter="this_week" onclick="applyDateFilter('this_week')">
                This Week
            </button>
            <button class="filter-btn {{ ($dateRange ?? 'today') === 'this_month' ? 'active' : '' }}" data-filter="this_month" onclick="applyDateFilter('this_month')">
                This Month
            </button>
            <button class="filter-btn {{ ($dateRange ?? 'today') === 'custom' ? 'active' : '' }}" data-filter="custom" id="custom-filter-btn" onclick="toggleCustomDateInputs()">
                Custom Date
            </button>
        </div>
        <div class="custom-date-inputs" id="custom-date-inputs" style="display: {{ ($dateRange ?? 'today') === 'custom' ? 'flex' : 'none' }}; align-items: center; gap: 10px; margin-top: 15px;">
            <input type="date" id="start-date" name="start_date" value="{{ $startDate ?? '' }}" class="date-input">
            <span>to</span>
            <input type="date" id="end-date" name="end_date" value="{{ $endDate ?? '' }}" class="date-input">
            <button onclick="applyCustomDate()" class="apply-btn">Apply</button>
        </div>
    </div>

    <!-- Hero Section - Today's KPIs -->
    <div class="kpi-section">
        <h2 class="section-title">Performance</h2>
        <div class="kpi-grid">
            <div class="kpi-card kpi-primary">
                <div class="kpi-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Total Leads Assigned</div>
                    <div class="kpi-value">{{ $data['today_stats']['total_leads_assigned'] ?? 0 }}</div>
                    <div class="kpi-subtitle">
                        Daily Limit: {{ $data['daily_limit']['assigned_count_today'] ?? 0 }}/{{ $data['daily_limit']['overall_daily_limit'] ?? 0 }}
                    </div>
                </div>
                <div class="kpi-progress">
                    <div class="progress-bar" style="width: {{ min($data['daily_limit']['percentage_used'] ?? 0, 100) }}%"></div>
                </div>
            </div>

            <div class="kpi-card kpi-success">
                <div class="kpi-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Calls Made Today</div>
                    <div class="kpi-value">{{ $data['today_stats']['calls_made_today'] ?? 0 }}</div>
                    <div class="kpi-subtitle">Connected: {{ $data['today_stats']['connected_calls'] ?? 0 }} ({{ $data['today_stats']['connection_rate'] ?? 0 }}%)</div>
                </div>
            </div>

            <div class="kpi-card kpi-info">
                <div class="kpi-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Status Updates</div>
                    <div class="kpi-value">{{ $data['today_stats']['lead_status_updates'] ?? 0 }}</div>
                    <div class="kpi-subtitle">Today's activity</div>
                </div>
            </div>

            <div class="kpi-card kpi-warning">
                <div class="kpi-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Site Visits Scheduled</div>
                    <div class="kpi-value">{{ $data['today_stats']['site_visits_scheduled'] ?? 0 }}</div>
                    <div class="kpi-subtitle">Today</div>
                </div>
            </div>

            <div class="kpi-card kpi-purple">
                <div class="kpi-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Follow-ups Completed</div>
                    <div class="kpi-value">{{ $data['today_stats']['followups_completed'] ?? 0 }}</div>
                    <div class="kpi-subtitle">Today</div>
                </div>
            </div>

            <div class="kpi-card kpi-danger">
                <div class="kpi-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Urgent Tasks</div>
                    <div class="kpi-value">{{ ($data['urgent_tasks']['overdue_followups_count'] ?? 0) + ($data['urgent_tasks']['sla_risks_count'] ?? 0) }}</div>
                    <div class="kpi-subtitle">Requires attention</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent Tasks Alert Panel -->
    @if(($data['urgent_tasks']['overdue_followups_count'] ?? 0) > 0 || ($data['urgent_tasks']['sla_risks_count'] ?? 0) > 0)
    <div class="urgent-tasks-section">
        <h2 class="section-title">
            <i class="fas fa-exclamation-circle text-danger"></i>
            Urgent Tasks
        </h2>
        <div class="urgent-tasks-grid">
            @if(($data['urgent_tasks']['overdue_followups_count'] ?? 0) > 0)
            <div class="urgent-card urgent-overdue">
                <div class="urgent-header">
                    <i class="fas fa-clock"></i>
                    <span>Overdue Follow-ups ({{ $data['urgent_tasks']['overdue_followups_count'] }})</span>
                </div>
                <div class="urgent-list">
                    @foreach(array_slice($data['urgent_tasks']['overdue_followups'] ?? [], 0, 5) as $followup)
                    <div class="urgent-item">
                        <strong>{{ $followup['title'] ?? 'Unknown' }}</strong>
                        <span class="urgent-time">
                            @if(isset($followup['scheduled_at']) && $followup['scheduled_at'])
                                {{ is_object($followup['scheduled_at']) ? $followup['scheduled_at']->diffForHumans() : \Carbon\Carbon::parse($followup['scheduled_at'])->diffForHumans() }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(($data['urgent_tasks']['sla_risks_count'] ?? 0) > 0)
            <div class="urgent-card urgent-sla">
                <div class="urgent-header">
                    <i class="fas fa-hourglass-half"></i>
                    <span>SLA Breach Risk ({{ $data['urgent_tasks']['sla_risks_count'] }})</span>
                </div>
                <div class="urgent-list">
                    @foreach(array_slice($data['urgent_tasks']['sla_risks'] ?? [], 0, 5) as $risk)
                    <div class="urgent-item">
                        <strong>{{ $risk['title'] ?? 'Unknown' }}</strong>
                        <span class="urgent-time">{{ $risk['minutes_remaining'] ?? 0 }} mins remaining</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Today's Schedule Timeline -->
    <div class="schedule-section">
        <h2 class="section-title">
            <i class="fas fa-calendar-day"></i>
            Today's Schedule
        </h2>
        <div class="schedule-timeline">
            @if(count($data['today_schedule'] ?? []) > 0)
                @foreach($data['today_schedule'] as $item)
                <div class="schedule-item schedule-{{ $item['type'] ?? 'default' }}">
                    <div class="schedule-time">
                        @if(isset($item['scheduled_at']) && $item['scheduled_at'])
                            {{ is_object($item['scheduled_at']) ? $item['scheduled_at']->format('h:i A') : \Carbon\Carbon::parse($item['scheduled_at'])->format('h:i A') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="schedule-content">
                        <div class="schedule-title">
                            @if($item['type'] === 'call')
                                <i class="fas fa-phone"></i>
                            @elseif($item['type'] === 'site_visit')
                                <i class="fas fa-map-marker-alt"></i>
                            @else
                                <i class="fas fa-calendar-check"></i>
                            @endif
                            {{ $item['title'] ?? 'Unknown' }}
                        </div>
                        @if(isset($item['property_name']))
                        <div class="schedule-subtitle">{{ $item['property_name'] }}</div>
                        @endif
                        <div class="schedule-status badge-{{ $item['status'] ?? 'pending' }}">{{ ucfirst($item['status'] ?? 'pending') }}</div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No scheduled activities for today</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Lead Breakdown -->
    <div class="leads-section">
        <h2 class="section-title">
            <i class="fas fa-chart-pie"></i>
            My Leads Breakdown
        </h2>
        <div class="leads-grid">
            <div class="lead-stat-card">
                <div class="stat-icon stat-new"><i class="fas fa-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $data['lead_breakdown']['new'] ?? 0 }}</div>
                    <div class="stat-label">New Leads</div>
                </div>
            </div>
            <div class="lead-stat-card">
                <div class="stat-icon stat-contacted"><i class="fas fa-phone"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $data['lead_breakdown']['contacted'] ?? 0 }}</div>
                    <div class="stat-label">Contacted</div>
                </div>
            </div>
            <div class="lead-stat-card">
                <div class="stat-icon stat-qualified"><i class="fas fa-star"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $data['lead_breakdown']['qualified'] ?? 0 }}</div>
                    <div class="stat-label">Qualified</div>
                </div>
            </div>
            <div class="lead-stat-card">
                <div class="stat-icon stat-visit"><i class="fas fa-map-marker-alt"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $data['lead_breakdown']['site_visit_scheduled'] ?? 0 }}</div>
                    <div class="stat-label">Site Visit Scheduled</div>
                </div>
            </div>
            <div class="lead-stat-card stat-hot">
                <div class="stat-icon stat-hot"><i class="fas fa-fire"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $data['lead_breakdown']['hot_leads'] ?? 0 }}</div>
                    <div class="stat-label">Hot Leads</div>
                </div>
            </div>
            <div class="lead-stat-card stat-followup">
                <div class="stat-icon stat-followup"><i class="fas fa-bell"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $data['lead_breakdown']['followup_required'] ?? 0 }}</div>
                    <div class="stat-label">Follow-up Required</div>
                </div>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="leadDistributionChart"></canvas>
        </div>
    </div>

    <!-- Performance Tracker -->
    @if($data['performance_metrics']['has_target'] ?? false)
    <div class="performance-section">
        <h2 class="section-title">
            <i class="fas fa-bullseye"></i>
            Target vs Achievement (This Month)
        </h2>
        <div class="performance-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="performance-item">
                <div class="performance-header">
                    <span class="performance-label">Calls Target</span>
                    <span class="performance-value">
                        {{ $data['performance_metrics']['achievements']['calls'] ?? 0 }} / {{ $data['performance_metrics']['targets']['calls'] ?? 0 }}
                    </span>
                </div>
                <div class="progress-container">
                    <div class="progress-bar-custom" style="width: {{ min($data['performance_metrics']['percentages']['calls'] ?? 0, 100) }}%"></div>
                </div>
                <div class="performance-percentage">{{ $data['performance_metrics']['percentages']['calls'] ?? 0 }}%</div>
            </div>
            <div class="performance-item">
                <div class="performance-header">
                    <span class="performance-label">Verified Prospects</span>
                    <span class="performance-value">
                        {{ $data['performance_metrics']['achievements']['prospects_verified'] ?? 0 }} / {{ $data['performance_metrics']['targets']['prospects_verified'] ?? 0 }}
                    </span>
                </div>
                <div class="progress-container">
                    <div class="progress-bar-custom" style="width: {{ min($data['performance_metrics']['percentages']['prospects_verified'] ?? 0, 100) }}%"></div>
                </div>
                <div class="performance-percentage">{{ $data['performance_metrics']['percentages']['prospects_verified'] ?? 0 }}%</div>
            </div>
        </div>
        
        <!-- Pie Charts Section -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; margin-top: 30px;">
            <!-- Calls Target vs Achievement Pie Chart -->
            <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-phone"></i> Calls Target vs Achievement
                </h3>
                <canvas id="callsTargetChart" style="max-height: 300px;"></canvas>
                <div style="margin-top: 15px; text-align: center; font-size: 14px; color: #666;">
                    <strong>Achieved:</strong> {{ $data['performance_metrics']['achievements']['calls'] ?? 0 }} / 
                    <strong>Target:</strong> {{ $data['performance_metrics']['targets']['calls'] ?? 0 }}
                </div>
            </div>
            
            <!-- Prospects Target vs Achievement Pie Chart -->
            <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-check-circle"></i> Verified Prospects Target vs Achievement
                </h3>
                <canvas id="prospectsTargetChart" style="max-height: 300px;"></canvas>
                <div style="margin-top: 15px; text-align: center; font-size: 14px; color: #666;">
                    <strong>Achieved:</strong> {{ $data['performance_metrics']['achievements']['prospects_verified'] ?? 0 }} / 
                    <strong>Target:</strong> {{ $data['performance_metrics']['targets']['prospects_verified'] ?? 0 }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- SLA Compliance & Call Quality -->
    <div class="metrics-grid">
        <div class="metric-card">
            <h3 class="metric-title">
                <i class="fas fa-shield-alt"></i>
                SLA Compliance
            </h3>
            <div class="metric-value-large">{{ $data['sla_compliance']['compliance_rate'] ?? 0 }}%</div>
            <div class="metric-details">
                <div class="metric-detail-item">
                    <span>On-time Contacts:</span>
                    <strong>{{ $data['sla_compliance']['met'] ?? 0 }}/{{ $data['sla_compliance']['total'] ?? 0 }}</strong>
                </div>
                <div class="metric-detail-item">
                    <span>Breached:</span>
                    <strong class="text-danger">{{ $data['sla_compliance']['breached'] ?? 0 }}</strong>
                </div>
                <div class="metric-detail-item">
                    <span>Pending:</span>
                    <strong>{{ $data['sla_compliance']['pending'] ?? 0 }}</strong>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <h3 class="metric-title">
                <i class="fas fa-phone-alt"></i>
                Call Quality Metrics (30 Days)
            </h3>
            <div class="metric-value-large">{{ $data['call_quality_metrics']['connection_rate'] ?? 0 }}%</div>
            <div class="metric-subtitle">Connection Rate</div>
            <div class="metric-details">
                <div class="metric-detail-item">
                    <span>Avg Duration:</span>
                    <strong>{{ $data['call_quality_metrics']['average_duration_minutes'] ?? 0 }} mins</strong>
                </div>
                <div class="metric-detail-item">
                    <span>Conversion Rate:</span>
                    <strong>{{ $data['call_quality_metrics']['conversion_rate'] ?? 0 }}%</strong>
                </div>
                <div class="metric-detail-item">
                    <span>Not Interested:</span>
                    <strong>{{ $data['call_quality_metrics']['not_interested_percentage'] ?? 0 }}%</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Call Statistics Section -->
    @if(isset($data['call_statistics']))
    <div class="call-statistics-section" style="margin-top: 30px;">
        <h2 class="section-title">
            <i class="fas fa-phone"></i>
            Call Statistics
        </h2>
        
        <!-- Call Stats Cards -->
        <div class="kpi-grid" style="margin-bottom: 20px;">
            <div class="kpi-card kpi-primary">
                <div class="kpi-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Total Calls Today</div>
                    <div class="kpi-value">{{ $data['call_statistics']['today']['total_calls'] ?? 0 }}</div>
                    <div class="kpi-subtitle">
                        Incoming: {{ $data['call_statistics']['today']['incoming_calls'] ?? 0 }} | 
                        Outgoing: {{ $data['call_statistics']['today']['outgoing_calls'] ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-success">
                <div class="kpi-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Total Duration</div>
                    <div class="kpi-value">{{ $data['call_statistics']['today']['formatted_duration'] ?? '0s' }}</div>
                    <div class="kpi-subtitle">Today</div>
                </div>
            </div>

            <div class="kpi-card kpi-info">
                <div class="kpi-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Average Duration</div>
                    <div class="kpi-value">{{ $data['call_statistics']['today']['formatted_average_duration'] ?? '0s' }}</div>
                    <div class="kpi-subtitle">Per call</div>
                </div>
            </div>

            <div class="kpi-card kpi-warning">
                <div class="kpi-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="kpi-content">
                    <div class="kpi-label">Connection Rate</div>
                    <div class="kpi-value">{{ number_format($data['call_statistics']['today']['connection_rate'] ?? 0, 1) }}%</div>
                    <div class="kpi-subtitle">Completed calls</div>
                </div>
            </div>
        </div>

        <!-- Calls Per Hour Chart -->
        @if(isset($data['call_statistics']['calls_per_hour']))
        <div class="chart-container" style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-color); margin-bottom: 15px;">Calls Per Hour (Today)</h3>
            <canvas id="callsPerHourChart" style="max-height: 300px;"></canvas>
        </div>
        @endif

        <!-- Recent Calls List -->
        @if(isset($data['call_statistics']['recent_calls']) && count($data['call_statistics']['recent_calls']) > 0)
        <div class="section-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="font-size: 16px; font-weight: 600; color: var(--text-color);">Recent Calls</h3>
                <a href="{{ route('calls.index') }}" style="color: var(--link-color); text-decoration: none; font-size: 14px;">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="activity-list">
                @foreach($data['call_statistics']['recent_calls'] as $call)
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-phone {{ $call['call_type'] == 'Incoming' ? 'text-blue-600' : 'text-green-600' }}"></i>
                    </div>
                    <div class="activity-content" style="flex: 1;">
                        <div class="activity-description">
                            <strong>{{ $call['lead_name'] }}</strong> - {{ $call['phone_number'] }}
                            <span class="badge badge-{{ $call['status'] == 'Completed' ? 'success' : 'warning' }}" style="margin-left: 10px;">
                                {{ $call['status'] }}
                            </span>
                        </div>
                        <div class="activity-time">
                            {{ $call['duration'] }} • {{ \Carbon\Carbon::parse($call['start_time'])->format('h:i A') }}
                        </div>
                    </div>
                    <a href="{{ route('calls.show', $call['id']) }}" style="color: var(--link-color); text-decoration: none;">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <a href="{{ route('calls.index') }}" class="quick-action-btn" style="flex: 1; text-align: center; padding: 12px; background: #205A44; color: white; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-list mr-2"></i> View All Calls
            </a>
            <a href="{{ route('calls.create') }}" class="quick-action-btn" style="flex: 1; text-align: center; padding: 12px; background: #063A1C; color: white; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-plus mr-2"></i> Add Manual Call
            </a>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="activity-section">
        <h2 class="section-title">
            <i class="fas fa-history"></i>
            Recent Activities
        </h2>
        <div class="activity-list">
            @if(count($data['recent_activity'] ?? []) > 0)
                @foreach($data['recent_activity'] as $activity)
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-description">{{ $activity['description'] ?? $activity['action'] ?? 'Activity' }}</div>
                        <div class="activity-time">{{ $activity['created_at_human'] ?? 'Just now' }}</div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No recent activities</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <h2 class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h2>
        <div class="quick-actions-grid">
            <a href="{{ route('telecaller.leads') }}" class="quick-action-btn">
                <i class="fas fa-user-friends"></i>
                <span>View Leads</span>
            </a>
            <a href="{{ route('telecaller.tasks') }}" class="quick-action-btn">
                <i class="fas fa-tasks"></i>
                <span>View Tasks</span>
            </a>
            <button class="quick-action-btn" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if(config('broadcasting.default') === 'pusher')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endif
<script src="{{ asset('js/telecaller-dashboard.js') }}"></script>
<script>
    // Pass data to JavaScript
    window.dashboardData = @json($data);
    
    // Initialize Calls Per Hour Chart
    @if(isset($data['call_statistics']['calls_per_hour']))
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('callsPerHourChart');
        if (ctx) {
            const callsPerHour = @json($data['call_statistics']['calls_per_hour'] ?? []);
            const hours = Array.from({length: 24}, (_, i) => i);
            const data = hours.map(h => callsPerHour[h] || 0);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: hours.map(h => h + ':00'),
                    datasets: [{
                        label: 'Calls',
                        data: data,
                        backgroundColor: 'rgba(32, 90, 68, 0.6)',
                        borderColor: 'rgba(32, 90, 68, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
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
                            display: false
                        }
                    }
                }
            });
        }
    });
    @endif
    
    // Real-time call log updates via Pusher
    @if(config('broadcasting.default') === 'pusher')
    document.addEventListener('DOMContentLoaded', function() {
        const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
            encrypted: true
        });
        
        const callLogsChannel = pusher.subscribe('call-logs');
        callLogsChannel.bind('call-log.created', function(data) {
            // Refresh call statistics if it's the current user's call
            if (data.user_name === '{{ auth()->user()->name }}') {
                // Reload dashboard or update stats
                if (typeof refreshDashboard === 'function') {
                    refreshDashboard();
                } else {
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            }
        });
        
        // Subscribe to user's private channel
        const userChannel = pusher.subscribe('private-user.{{ auth()->user()->id }}');
        userChannel.bind('call-log.created', function(data) {
            if (typeof refreshDashboard === 'function') {
                refreshDashboard();
            } else {
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        });
    });
    @endif
    
    // Date Filter Functions
    function applyDateFilter(filter) {
        const url = new URL(window.location.href);
        url.searchParams.set('date_range', filter);
        url.searchParams.delete('start_date');
        url.searchParams.delete('end_date');
        window.location.href = url.toString();
    }

    function toggleCustomDateInputs() {
        const customInputs = document.getElementById('custom-date-inputs');
        const customBtn = document.getElementById('custom-filter-btn');
        
        if (customInputs.style.display === 'none' || !customInputs.style.display) {
            customInputs.style.display = 'flex';
            customBtn.classList.add('active');
            // Update active state for buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn !== customBtn) {
                    btn.classList.remove('active');
                }
            });
        }
    }

    function applyCustomDate() {
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;

        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date cannot be after end date');
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('date_range', 'custom');
        url.searchParams.set('start_date', startDate);
        url.searchParams.set('end_date', endDate);
        window.location.href = url.toString();
    }

    // Initialize Target vs Achievement Pie Charts
    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($data['performance_metrics']) && $data['performance_metrics']['has_target'])
        const performanceData = @json($data['performance_metrics']);
        
        // Calls Target vs Achievement Pie Chart
        const callsCtx = document.getElementById('callsTargetChart');
        if (callsCtx) {
            const callsTarget = performanceData.targets?.calls ?? 0;
            const callsAchieved = performanceData.achievements?.calls ?? 0;
            const callsRemaining = Math.max(0, callsTarget - callsAchieved);
            
            new Chart(callsCtx, {
                type: 'pie',
                data: {
                    labels: ['Achieved', 'Remaining'],
                    datasets: [{
                        data: [callsAchieved, callsRemaining],
                        backgroundColor: [
                            'rgba(32, 90, 68, 0.8)', // Green for achieved
                            'rgba(220, 220, 220, 0.8)' // Gray for remaining
                        ],
                        borderColor: [
                            'rgba(32, 90, 68, 1)',
                            'rgba(220, 220, 220, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = callsTarget || 1;
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Prospects Target vs Achievement Pie Chart
        const prospectsCtx = document.getElementById('prospectsTargetChart');
        if (prospectsCtx) {
            const prospectsTarget = performanceData.targets?.prospects_verified ?? 0;
            const prospectsAchieved = performanceData.achievements?.prospects_verified ?? 0;
            const prospectsRemaining = Math.max(0, prospectsTarget - prospectsAchieved);
            
            new Chart(prospectsCtx, {
                type: 'pie',
                data: {
                    labels: ['Achieved (Verified)', 'Remaining'],
                    datasets: [{
                        data: [prospectsAchieved, prospectsRemaining],
                        backgroundColor: [
                            'rgba(21, 128, 61, 0.8)', // Darker green for verified prospects
                            'rgba(220, 220, 220, 0.8)' // Gray for remaining
                        ],
                        borderColor: [
                            'rgba(21, 128, 61, 1)',
                            'rgba(220, 220, 220, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = prospectsTarget || 1;
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif
    });
</script>
@endpush

