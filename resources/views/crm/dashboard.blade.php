@extends('layouts.app')

@section('title', 'CRM - Base CRM')
@section('page-title', 'CRM')
@section('header-below-title')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <label class="form-label small mb-0 fw-bold" for="date-range-filter">Date Range:</label>
        <select id="date-range-filter" class="form-select form-select-sm" style="max-width: 160px;">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="this_week">This Week</option>
            <option value="this_month" selected>This Month</option>
            <option value="this_year">This Year</option>
            <option value="all_time">All Time</option>
            <option value="custom">Custom</option>
        </select>
        <span id="custom-date-wrap" class="d-none align-middle">
            <input type="date" id="date-range-start" class="form-control form-control-sm d-inline-block" style="max-width: 140px;" title="From">
            <span class="mx-1">–</span>
            <input type="date" id="date-range-end" class="form-control form-control-sm d-inline-block" style="max-width: 140px;" title="To">
        </span>
    </div>
@endsection

@push('styles')
<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    :root {
        --primary-green: #063A1C;
        --secondary-green: #205A44;
        --bg-beige: #F7F6F3;
        --border-color: #E5DED4;
    }
    
    body {
        background-color: var(--bg-beige);
    }
    
    .stats-card-gradient {
        background: linear-gradient(135deg, var(--secondary-green) 0%, var(--primary-green) 100%);
        color: white;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .stats-card-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .stats-card {
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .telecaller-card {
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--secondary-green) 0%, var(--primary-green) 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .telecaller-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .telecaller-card .text-muted {
        color: rgba(255, 255, 255, 0.7) !important;
    }
    
    .telecaller-card .fw-bold {
        color: white;
    }
    
    .telecaller-card .text-success {
        color: #90ee90 !important;
    }
    
    .telecaller-card .text-danger {
        color: #ffb3b3 !important;
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background-color: var(--primary-green);
        border-color: var(--primary-green);
    }
    
    .btn-primary:hover {
        background-color: var(--secondary-green);
        border-color: var(--secondary-green);
    }
    
    /* Phone / small screen responsive – side blank space na aaye */
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 0;
            padding-right: 0;
            max-width: 100%;
            width: 100%;
            overflow-x: hidden;
        }
        .container-fluid .row { margin-left: 0; margin-right: 0; }
        .container-fluid .card { max-width: 100%; }
        .card-body { padding: 0.75rem; overflow-x: hidden; }
        .card-header { padding: 0.75rem 1rem; }
        .card-header h5 { font-size: 1rem; }
    }
    
    /* Sales Executive Performance table: scroll andar, page full width */
    .crm-perf-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    .crm-perf-table-wrap .table {
        min-width: 640px;
        margin-bottom: 0;
    }
    .crm-perf-table-wrap .table th,
    .crm-perf-table-wrap .table td {
        white-space: nowrap;
        vertical-align: middle;
    }
    @media (max-width: 768px) {
        .crm-perf-table-wrap .table { font-size: 0.8rem; min-width: 560px; }
        .crm-perf-table-wrap .table th,
        .crm-perf-table-wrap .table td { padding: 0.4rem 0.5rem; }
        .crm-perf-table-wrap .table th:first-child,
        .crm-perf-table-wrap .table td:first-child {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 1;
            box-shadow: 2px 0 4px rgba(0,0,0,0.06);
            min-width: 90px;
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .crm-perf-table-wrap .table thead th:first-child { background: #f8f9fa; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Notification Alert -->
    <div id="notification-alert" class="alert alert-success alert-dismissible fade d-none" role="alert">
        <span id="notification-message"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Sales Executive Performance Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0">Sales Executive Performance</h5>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <select id="perf-role-filter" class="form-select form-select-sm" style="max-width: 180px;" title="User type">
                            <option value="all">All</option>
                        </select>
                        <select id="perf-date-range" class="form-select form-select-sm" style="max-width: 160px;" title="Date range">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="this_year">This Year</option>
                            <option value="all_time">All Time</option>
                            <option value="custom">Custom</option>
                        </select>
                        <span id="perf-custom-date-wrap" class="d-none align-middle">
                            <input type="date" id="perf-date-start" class="form-control form-control-sm d-inline-block" style="max-width: 130px;" title="From">
                            <span class="mx-1">–</span>
                            <input type="date" id="perf-date-end" class="form-control form-control-sm d-inline-block" style="max-width: 130px;" title="To">
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="telecaller-stats-container">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Allocated (75% No Response Yet + 25% Average Response Time) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Leads Allocated</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-9">
                            <h6 class="text-muted small mb-1">No Response Yet</h6>
                            <p class="text-muted small mb-2">Users with leads on which no call outcome has been recorded.</p>
                            <div class="table-responsive crm-perf-table-wrap">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;"></th>
                                            <th>User Name</th>
                                            <th class="text-center">Pending Count</th>
                                            <th>Oldest Assign</th>
                                        </tr>
                                    </thead>
                                    <tbody id="leads-pending-response-tbody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-lg-3">
                            <h6 class="text-muted small mb-1">Average Response Time</h6>
                            <p class="text-muted small mb-2">Avg time from assign to first response (this period).</p>
                            <div id="average-response-time-panel" style="min-height: 60px;">
                                <p class="text-muted small mb-0">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('crm.modals.user-management')
@include('crm.modals.transfer-leads')

@push('scripts')
<!-- Bootstrap 5.3.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/crm-dashboard.js') }}"></script>
@endpush

@endsection

