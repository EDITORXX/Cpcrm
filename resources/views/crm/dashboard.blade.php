@extends('layouts.app')

@section('title', 'CRM - Base CRM')
@section('page-title', 'CRM')
@section('header-below-title')
    <label class="form-label small mb-0 fw-bold" for="date-range-filter">Date Range:</label>
    <select id="date-range-filter" class="form-select form-select-sm" style="max-width: 160px;">
        <option value="today">Today</option>
        <option value="this_week">This Week</option>
        <option value="this_month" selected>This Month</option>
        <option value="this_year">This Year</option>
        <option value="till_date">Till Date</option>
        <option value="all_time">All Time</option>
    </select>
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Notification Alert -->
    <div id="notification-alert" class="alert alert-success alert-dismissible fade d-none" role="alert">
        <span id="notification-message"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Top Stats Cards (4 Cards) - Phone: 50% x 2 -->
    <div class="row mb-4" id="stats-cards-container">
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="stats-card stats-card-gradient" data-filter="all">
                <h6 class="text-white-50 mb-2">Total Assigned Leads</h6>
                <h2 class="text-white mb-0" id="stat-total-assigned">0</h2>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="stats-card stats-card-gradient" data-filter="called">
                <h6 class="text-white-50 mb-2">Called Leads</h6>
                <h2 class="text-white mb-0" id="stat-called">0</h2>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="stats-card stats-card-gradient" data-filter="called_not_interested">
                <h6 class="text-white-50 mb-2">Not Interested</h6>
                <h2 class="text-white mb-0" id="stat-not-interested">0</h2>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6 mb-3">
            <div class="stats-card stats-card-gradient" data-filter="called_interested">
                <h6 class="text-white-50 mb-2">Interested</h6>
                <h2 class="text-white mb-0" id="stat-interested">0</h2>
            </div>
        </div>
    </div>

    <!-- Sales Executive Performance Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sales Executive Performance</h5>
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

