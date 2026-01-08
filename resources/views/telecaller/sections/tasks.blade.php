@extends('telecaller.layout')

@section('title', 'Tasks - Telecaller')
@section('page-title', 'Tasks')

@push('styles')
<style>
    .tasks-container {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        width: 100%;
        box-sizing: border-box;
        max-width: 100%;
        overflow-x: hidden;
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
    .tasks-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 20px;
        width: 100%;
        box-sizing: border-box;
    }
    @media (max-width: 1200px) {
        .tasks-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 1024px) {
        .tasks-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
    }
    @media (max-width: 768px) {
        .tasks-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .tasks-container {
            padding: 16px !important;
            margin: 0 !important;
        }
    }
    @media (max-width: 480px) {
        .tasks-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .tasks-container {
            padding: 12px !important;
        }
        .filter-bar {
            gap: 8px !important;
        }
        .filter-btn {
            padding: 8px 16px !important;
            font-size: 13px !important;
        }
    }
    .task-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
    }
    .task-card:hover {
        border-color: #205A44;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    .task-header {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .task-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        font-weight: 700;
        margin-right: 12px;
    }
    .task-name {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin: 0;
    }
    .task-info {
        margin-bottom: 12px;
    }
    .task-info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 14px;
        color: #063A1C;
    }
    .task-info-row i {
        color: #205A44;
        width: 16px;
    }
    .task-footer {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #f0f0f0;
    }
    .btn-call-task {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-call-task:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        margin-top: 8px;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-in_progress { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-rescheduled { background: #e9d5ff; color: #6b21a8; }
    .overdue-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #ef4444;
        color: white;
        margin-top: 8px;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    }
    .task-card.overdue {
        border-color: #ef4444;
        border-width: 3px;
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
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }
    .modal.active {
        display: flex;
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        margin: auto;
        position: relative;
    }
    
    /* Confirmation modal should be above other modals */
    #confirmModal {
        z-index: 10001;
    }
    
    #confirmModal .modal-content {
        z-index: 10002;
    }
    
    /* Ensure modal is centered on all screen sizes */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            padding: 20px;
            margin: 20px auto;
        }
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 24px;
        color: #063A1C;
    }
    .close-modal {
        background: none;
        border: none;
        font-size: 28px;
        color: #B3B5B4;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .close-modal:hover {
        color: #063A1C;
    }
    .outcome-buttons {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-top: 20px;
    }
    .outcome-btn {
        padding: 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .outcome-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .btn-interested { border-color: #10b981; color: #10b981; }
    .btn-interested:hover { background: #10b981; color: white; }
    .btn-not-interested { border-color: #ef4444; color: #ef4444; }
    .btn-not-interested:hover { background: #ef4444; color: white; }
    .btn-cnp { border-color: #f59e0b; color: #f59e0b; }
    .btn-cnp:hover { background: #f59e0b; color: white; }
    .btn-call-again { border-color: #3b82f6; color: #3b82f6; }
    .btn-call-again:hover { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; }
    .btn-block { border-color: #dc2626; color: #dc2626; }
    .btn-block:hover { background: #dc2626; color: white; }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #063A1C;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        background: #ffffff;
        color: #063A1C;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #205A44;
    }
    .form-group input[readonly] {
        background: #f5f5f5;
        cursor: not-allowed;
    }
    .form-footer {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 2px solid #f0f0f0;
    }
    .btn-whatsapp-form {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        border: none;
        box-shadow: 0 2px 4px rgba(21, 128, 61, 0.3);
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-whatsapp-form:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        box-shadow: 0 4px 8px rgba(21, 128, 61, 0.4);
        transform: translateY(-1px);
    }
    .btn-save {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    /* View Switcher Styles */
    .view-switcher {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .view-btn {
        white-space: nowrap;
    }
    .view-btn:hover {
        background: #F7F6F3 !important;
    }
    .view-btn.active {
        background: #205A44 !important;
        color: white !important;
    }
    .view-btn i {
        margin-right: 5px;
    }
    
    /* Calendar View Styles */
    #calendarContainer {
        width: 100%;
        min-height: 700px;
    }
    
    /* FullCalendar Custom Styles */
    .fc {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    .fc-header-toolbar {
        margin-bottom: 20px !important;
        padding: 15px !important;
        background: #F7F6F3 !important;
        border-radius: 8px !important;
        flex-wrap: wrap !important;
    }
    
    .fc-toolbar-chunk {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .fc-button {
        background: #205A44 !important;
        border-color: #205A44 !important;
        color: white !important;
        padding: 8px 16px !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        font-size: 14px !important;
        height: auto !important;
        line-height: normal !important;
    }
    
    .fc-button:hover {
        background: #063A1C !important;
        border-color: #063A1C !important;
    }
    
    .fc-button-active {
        background: #063A1C !important;
        border-color: #063A1C !important;
    }
    
    .fc-today-button {
        background: #205A44 !important;
        border-color: #205A44 !important;
        margin: 0 5px !important;
    }
    
    .fc-toolbar-title {
        font-size: 24px !important;
        font-weight: 700 !important;
        color: #063A1C !important;
        margin: 0 10px !important;
    }
    
    .fc-daygrid-day {
        border-color: #E5DED4 !important;
        min-height: 100px !important;
    }
    
    .fc-daygrid-day-number {
        padding: 8px !important;
        color: #063A1C !important;
        font-weight: 500 !important;
        font-size: 14px !important;
        display: inline-block !important;
        width: auto !important;
        min-width: 24px !important;
        text-align: left !important;
    }
    
    .fc-day-today {
        background: #F7F6F3 !important;
    }
    
    .fc-day-today .fc-daygrid-day-number {
        color: #205A44 !important;
        font-weight: 700 !important;
        font-size: 16px !important;
    }
    
    .fc-daygrid-day-frame {
        padding: 8px !important;
    }
    
    .fc-daygrid-day-top {
        flex-direction: row !important;
        justify-content: flex-start !important;
        padding: 4px 8px !important;
    }
    
    .fc-daygrid-day {
        padding: 4px !important;
        position: relative !important;
    }
    
    .fc-scrollgrid-sync-table {
        width: 100% !important;
        table-layout: fixed !important;
    }
    
    .fc-col-header-cell,
    .fc-daygrid-day {
        width: 14.28% !important;
    }
    
    .fc-daygrid-day-events {
        margin-top: 4px !important;
    }
    
    .fc-daygrid-event-harness {
        margin: 2px 4px !important;
    }
    
    .fc-col-header-cell {
        background: #F7F6F3 !important;
        padding: 12px 0 !important;
        border-color: #E5DED4 !important;
    }
    
    .fc-col-header-cell-cushion {
        color: #063A1C !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        text-decoration: none !important;
    }
    
    .fc-event {
        border-radius: 4px !important;
        padding: 4px 6px !important;
        cursor: pointer !important;
        border: none !important;
        margin: 2px 0 !important;
    }
    
    .fc-event:hover {
        opacity: 0.9 !important;
        transform: scale(1.02) !important;
    }
    
    .fc-event-title {
        font-size: 12px !important;
        font-weight: 600 !important;
        padding: 2px 4px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    
    .fc-more-link {
        color: #205A44 !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        margin-top: 4px !important;
    }
    
    .fc-more-link:hover {
        color: #063A1C !important;
    }
    
    .fc-daygrid-event {
        white-space: nowrap !important;
        overflow: hidden !important;
    }
    
    /* Kanban View Styles */
    .kanban-column {
        min-width: 300px;
        background: #F7F6F3;
        border-radius: 12px;
        padding: 16px;
    }
    .kanban-column-header {
        font-size: 16px;
        font-weight: 600;
        color: #063A1C;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E5DED4;
    }
    .kanban-task-card {
        background: white;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        cursor: move;
        transition: all 0.3s;
    }
    .kanban-task-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .kanban-task-card.overdue {
        border-left: 4px solid #ef4444;
    }
    
    /* Responsive View Switcher */
    @media (max-width: 768px) {
        .view-switcher {
            width: 100%;
        }
        .view-btn {
            flex: 1;
            padding: 8px 12px !important;
            font-size: 12px !important;
        }
        .view-btn i {
            display: none;
        }
        .filter-bar {
            width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .view-btn {
            padding: 6px 8px !important;
            font-size: 11px !important;
        }
    }
</style>
@endpush

@section('content')
    <div class="tasks-container">
        <!-- Filter and View Switcher Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <div class="filter-bar" style="flex: 1; min-width: 200px;">
                <button class="filter-btn active" onclick="filterTasks('pending')">Pending</button>
                <button class="filter-btn" onclick="filterTasks('completed')">Completed</button>
                <button class="filter-btn" onclick="filterTasks('rescheduled')">Rescheduled</button>
                <button class="filter-btn" onclick="filterTasks('all')">All</button>
            </div>
            
            <!-- View Switcher -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 14px; color: #063A1C; font-weight: 500;">View:</span>
                <div class="view-switcher" style="display: inline-flex; border: 2px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: white;">
                    <button class="view-btn" id="view-list" onclick="switchView('list')" style="padding: 8px 16px; border: none; background: #205A44; color: white; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.3s;">
                        <i class="fas fa-list"></i> List
                    </button>
                    <button class="view-btn" id="view-kanban" onclick="switchView('kanban')" style="padding: 8px 16px; border: none; background: white; color: #063A1C; cursor: pointer; font-size: 14px; font-weight: 500; border-left: 1px solid #e0e0e0; transition: all 0.3s;">
                        <i class="fas fa-columns"></i> Kanban
                    </button>
                    <button class="view-btn" id="view-calendar" onclick="switchView('calendar')" style="padding: 8px 16px; border: none; background: white; color: #063A1C; cursor: pointer; font-size: 14px; font-weight: 500; border-left: 1px solid #e0e0e0; transition: all 0.3s;">
                        <i class="fas fa-calendar"></i> Calendar
                    </button>
                </div>
            </div>
        </div>

        <!-- Task Views Container -->
        <div id="list-view-container">
            <div id="tasksContent" class="tasks-grid">
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading tasks...</p>
                </div>
            </div>
        </div>
        
        <!-- Kanban View Container (Hidden by default) -->
        <div id="kanban-view-container" style="display: none;">
            <div id="kanbanBoard" style="display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px;">
                <!-- Columns will be added dynamically -->
            </div>
        </div>
        
        <!-- Calendar View Container (Hidden by default) -->
        <div id="calendar-view-container" style="display: none; width: 100%; overflow-x: auto;">
            <div id="calendarContainer" style="width: 100%; min-height: 700px; background: white; border-radius: 12px;"></div>
        </div>
    </div>

    <!-- Post-Call Popup Modal -->
    <div id="postCallModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="postCallTitle">Call Outcome</h3>
                <button class="close-modal" onclick="closePostCallModal()">&times;</button>
            </div>
            <div id="postCallBody">
                <p>Select the outcome of this call:</p>
                <div class="outcome-buttons">
                    <button class="outcome-btn btn-interested" onclick="handleInterested()">
                        <i class="fas fa-check-circle"></i>
                        Interested
                    </button>
                    <button class="outcome-btn btn-not-interested" onclick="handleNotInterested()">
                        <i class="fas fa-times-circle"></i>
                        Not Interested
                    </button>
                    <button class="outcome-btn btn-cnp" onclick="handleCNP()">
                        <i class="fas fa-phone-slash"></i>
                        CNP
                    </button>
                    <button class="outcome-btn btn-call-again" onclick="handleCallAgain()">
                        <i class="fas fa-redo"></i>
                        Call Again
                    </button>
                    <button class="outcome-btn btn-block" onclick="handleBlock()">
                        <i class="fas fa-ban"></i>
                        Block
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Prospect Form Modal -->
    <div id="prospectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Prospect Details</h3>
                <button class="close-modal" onclick="closeProspectModal()">&times;</button>
            </div>
            <form id="prospectForm">
                <div class="form-group">
                    <label>Customer Name *</label>
                    <input type="text" id="customerName" name="customer_name" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" id="prospectPhone" name="phone" readonly>
                </div>
                <div class="form-group">
                    <label>Budget</label>
                    <input type="text" id="budget" name="budget">
                </div>
                <div class="form-group">
                    <label>Preferred Location</label>
                    <input type="text" id="preferredLocation" name="preferred_location">
                </div>
                <div class="form-group">
                    <label>Size</label>
                    <input type="text" id="size" name="size">
                </div>
                <div class="form-group">
                    <label>Purpose *</label>
                    <select id="purpose" name="purpose" required>
                        <option value="end_user">End User</option>
                        <option value="investment">Investment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Possession</label>
                    <input type="text" id="possession" name="possession">
                </div>
                <div class="form-group">
                    <label>Assign To</label>
                    <input type="text" id="assignTo" name="assign_to" readonly>
                </div>
                <div class="form-group">
                    <label>Lead Score (Rating) *</label>
                    <div class="star-rating-container" style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                        <div class="star-rating" id="starRating" style="display: flex; gap: 4px; font-size: 28px; cursor: pointer;">
                            <span class="star" data-rating="1" style="color: #d1d5db;">☆</span>
                            <span class="star" data-rating="2" style="color: #d1d5db;">☆</span>
                            <span class="star" data-rating="3" style="color: #d1d5db;">☆</span>
                            <span class="star" data-rating="4" style="color: #d1d5db;">☆</span>
                            <span class="star" data-rating="5" style="color: #d1d5db;">☆</span>
                        </div>
                        <span id="ratingText" style="color: #6b7280; font-size: 14px; margin-left: 8px;"></span>
                    </div>
                    <input type="hidden" id="leadScore" name="lead_score" required>
                    <small style="color: #6b7280; font-size: 12px; display: block; margin-top: 4px;">Click on stars to rate the lead quality (1 = lowest, 5 = highest)</small>
                </div>
                <div class="form-group">
                    <label>Remark *</label>
                    <textarea id="remark" name="remark" rows="4" required></textarea>
                </div>
                <div class="form-footer">
                    <button type="button" class="btn-whatsapp-form" onclick="openWhatsAppFromForm()">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp
                    </button>
                    <button type="submit" class="btn-save">Save To Manager</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Call Again DateTime Picker Modal -->
    <div id="rescheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reschedule Call</h3>
                <button class="close-modal" onclick="closeRescheduleModal()">&times;</button>
            </div>
            <div class="form-group">
                <label>Select Date & Time *</label>
                <input type="datetime-local" id="rescheduleDateTime" required>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea id="rescheduleNotes" rows="3"></textarea>
            </div>
            <div class="form-footer">
                <button type="button" class="btn-save" onclick="saveReschedule()" style="width: 100%;">
                    Schedule Call
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 id="confirmModalTitle">Confirm Action</h3>
                <button class="close-modal" onclick="closeConfirmModal()">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p id="confirmModalMessage" style="font-size: 16px; color: #063A1C; margin-bottom: 24px; line-height: 1.6;"></p>
                <div class="form-footer" style="justify-content: flex-end;">
                    <button type="button" onclick="closeConfirmModal()" style="padding: 12px 24px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; color: #063A1C; font-size: 16px; font-weight: 600; cursor: pointer; margin-right: 12px;">
                        Cancel
                    </button>
                    <button type="button" id="confirmModalOkBtn" onclick="executeConfirmAction()" style="padding: 12px 24px; border: none; border-radius: 8px; background: linear-gradient(135deg, #205A44 0%, #063A1C 100%); color: white; font-size: 16px; font-weight: 600; cursor: pointer;">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
<script>
    var API_BASE_URL = '{{ url("/api/telecaller") }}';
    let currentTaskId = null;
    let currentLeadData = null;
    let currentStatus = 'pending';
    let currentTasksArray = []; // Store tasks array globally for button clicks
    let calendarInstance = null; // FullCalendar instance

    // Helper function to show notifications (fallback to alert if custom notification not available)
    function showAlert(message, type = 'success', duration = 2500) {
        if (typeof showNotification === 'function') {
            showNotification(message, type, duration);
        } else {
            alert(message);
        }
    }

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
            
            // Set method
            fetchOptions.method = options.method || 'GET';
            
            // Set body for POST/PUT/PATCH
            if (options.body && (fetchOptions.method === 'POST' || fetchOptions.method === 'PUT' || fetchOptions.method === 'PATCH')) {
                if (typeof options.body === 'string') {
                    fetchOptions.body = options.body;
                } else {
                    fetchOptions.body = JSON.stringify(options.body);
                    // Ensure Content-Type is set to JSON
                    if (!fetchOptions.headers['Content-Type'] && !fetchOptions.headers['content-type']) {
                        fetchOptions.headers['Content-Type'] = 'application/json';
                    }
                }
            }
            
            const response = await fetch(url, fetchOptions);

            console.log('Response status:', response.status);

            // Only logout on 401 (Unauthorized), not on 419 (CSRF token)
            if (response.status === 401) {
                console.error('Unauthorized - clearing token');
                localStorage.removeItem('telecaller_token');
                localStorage.removeItem('telecaller_user');
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 2000);
                return { success: false, message: 'Session expired. Redirecting to login...' };
            }

            // For 419 (CSRF token mismatch), don't logout - just return error with detailed debug info
            if (response.status === 419) {
                const errorText = await response.text();
                console.error('=== CSRF TOKEN MISMATCH (419) ERROR DEBUG ===');
                console.error('Request URL:', url);
                console.error('Request Method:', fetchOptions.method);
                console.error('Request Headers:', fetchOptions.headers);
                console.error('Request Body:', fetchOptions.body);
                console.error('Response Status:', response.status);
                console.error('Response Headers:', Object.fromEntries(response.headers.entries()));
                console.error('Response Text:', errorText);
                console.error('Has Bearer Token:', !!getToken());
                console.error('Bearer Token Preview:', getToken() ? getToken().substring(0, 30) + '...' : 'None');
                console.error('Is API Route:', url.includes('/api/'));
                console.error('===========================================');
                
                try {
                    const errorJson = JSON.parse(errorText);
                    return { 
                        success: false, 
                        message: errorJson.message || 'CSRF token mismatch. Please refresh the page and try again.',
                        error: errorJson,
                        debug_info: {
                            url: url,
                            method: fetchOptions.method,
                            has_bearer_token: !!getToken(),
                            is_api_route: url.includes('/api/')
                        }
                    };
                } catch (e) {
                    return { 
                        success: false, 
                        message: 'CSRF token mismatch. Please refresh the page and try again.',
                        raw_response: errorText.substring(0, 500),
                        debug_info: {
                            url: url,
                            method: fetchOptions.method,
                            has_bearer_token: !!getToken(),
                            is_api_route: url.includes('/api/')
                        }
                    };
                }
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

    async function loadTasks(status = 'pending') {
        currentStatus = status;
        const contentDiv = document.getElementById('tasksContent');
        if (!contentDiv) {
            console.error('tasksContent element not found');
            return;
        }
        contentDiv.className = 'tasks-grid';
        contentDiv.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading tasks...</p></div>';

        const endpoint = `/tasks?status=${status}&per_page=50`;
        console.log('Loading tasks from:', API_BASE_URL + endpoint);
        console.log('Token exists:', !!getToken());
        
        const result = await apiCall(endpoint);
        console.log('API Result:', result);

        if (!result) {
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Tasks</h3>
                    <p>No response from server. Please check your connection.</p>
                    <button onclick="loadTasks('${status}')" style="margin-top: 16px; padding: 10px 20px; background: #205A44; color: white; border: none; border-radius: 8px; cursor: pointer;">Retry</button>
                </div>
            `;
            return;
        }

        if (!result.success) {
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Tasks</h3>
                    <p>${result.message || 'Failed to load tasks'}</p>
                    <button onclick="loadTasks('${status}')" style="margin-top: 16px; padding: 10px 20px; background: #205A44; color: white; border: none; border-radius: 8px; cursor: pointer;">Retry</button>
                </div>
            `;
            return;
        }

        const tasks = result.data || [];
        currentTasksArray = tasks; // Store tasks for later use in button clicks

        if (tasks.length === 0) {
            contentDiv.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-tasks"></i>
                    <h3>No Tasks Found</h3>
                    <p>You don't have any ${status === 'all' ? '' : status} tasks at the moment.</p>
                </div>
            `;
            return;
        }

        let cardsHTML = '';
        tasks.forEach(task => {
            const scheduledDate = task.scheduled_at ? formatDate(task.scheduled_at) : '-';
            const statusClass = `status-${task.status}`;
            const initial = task.lead_name ? task.lead_name.charAt(0).toUpperCase() : 'T';
            
            // Check if task is overdue
            const isOverdue = task.scheduled_at && new Date(task.scheduled_at) < new Date() && task.status !== 'completed';
            const overdueClass = isOverdue ? 'overdue' : '';

            cardsHTML += `
                <div class="task-card ${overdueClass}">
                    <div class="task-header">
                        <div class="task-avatar">${initial}</div>
                        <h3 class="task-name">${task.lead_name || '-'}</h3>
                    </div>
                    <div class="task-info">
                        <div class="task-info-row">
                            <i class="fas fa-phone"></i>
                            <span>${task.lead_phone || '-'}</span>
                        </div>
                        <div class="task-info-row">
                            <i class="fas fa-calendar"></i>
                            <span>Scheduled: ${scheduledDate}</span>
                        </div>
                        ${isOverdue ? '<span class="overdue-badge">OVERDUE</span>' : ''}
                        <span class="status-badge ${statusClass}">${formatStatus(task.status)}</span>
                    </div>
                    <div class="task-footer">
                        ${task.status === 'pending' || task.status === 'rescheduled' ? `
                            <button class="btn-call-task" 
                                    data-task-id="${task.id}" 
                                    data-phone="${task.lead_phone || ''}">
                                <i class="fas fa-phone"></i>
                                Call
                            </button>
                            ${task.task_type === 'cnp_retry' ? '<p style="text-align: center; color: #f59e0b; font-size: 12px; margin-top: 8px;">CNP Retry Call</p>' : ''}
                        ` : '<p style="text-align: center; color: #999; font-size: 14px;">Task Completed</p>'}
                    </div>
                </div>
            `;
        });

        contentDiv.innerHTML = cardsHTML;
        
        // Add event listeners for call buttons using event delegation
        contentDiv.querySelectorAll('.btn-call-task').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const taskId = parseInt(this.getAttribute('data-task-id'));
                const phoneNumber = this.getAttribute('data-phone') || '';
                
                // Find task data from stored array
                const taskData = currentTasksArray.find(t => t.id === taskId) || {
                    id: taskId,
                    lead_phone: phoneNumber,
                    lead_name: 'Lead',
                    task_type: 'calling',
                    status: 'pending'
                };
                
                initiateCall(taskId, phoneNumber, taskData);
            });
        });
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function formatStatus(status) {
        const statusMap = {
            'pending': 'Pending',
            'in_progress': 'In Progress',
            'completed': 'Completed',
            'rescheduled': 'Rescheduled',
        };
        return statusMap[status] || status;
    }

    function filterTasks(status) {
        currentStatus = status;
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        // Reload current view
        if (currentView === 'list') {
            loadTasks(status);
        } else if (currentView === 'kanban') {
            loadKanbanView();
        } else if (currentView === 'calendar') {
            loadCalendarView();
        }
    }

    function initiateCall(taskId, phoneNumber, taskData) {
        currentTaskId = taskId;
        currentLeadData = taskData;
        
        // Open phone dialer
        if (phoneNumber && phoneNumber !== '-') {
            window.location.href = `tel:${phoneNumber}`;
        }
        
        // Show post-call popup
        document.getElementById('postCallTitle').textContent = `Call Outcome - ${taskData.lead_name || 'Lead'}`;
        document.getElementById('postCallModal').classList.add('active');
    }

    function closePostCallModal() {
        document.getElementById('postCallModal').classList.remove('active');
    }

    function handleInterested() {
        closePostCallModal();
        // Show prospect form
        if (currentLeadData) {
            document.getElementById('customerName').value = currentLeadData.lead_name || '';
            document.getElementById('prospectPhone').value = currentLeadData.lead_phone || '';
            document.getElementById('assignTo').value = currentLeadData.manager_name || 'Not Assigned';
            if (typeof initializeStarRating === 'function') {
                initializeStarRating();
            }
            document.getElementById('prospectModal').classList.add('active');
        }
    }

    function closeProspectModal() {
        document.getElementById('prospectModal').classList.remove('active');
        document.getElementById('prospectForm').reset();
        if (typeof initializeStarRating === 'function') {
            initializeStarRating();
        }
    }

    async function handleNotInterested() {
        showConfirmModal(
            'Mark this lead as Not Interested?',
            function() {
                executeNotInterested();
            }
        );
    }
    
    async function executeNotInterested() {
        closeConfirmModal();
        
        const result = await apiCall(`/tasks/${currentTaskId}/outcome`, {
            method: 'POST',
            body: { outcome: 'not_interested' },
        });

        if (result && result.success) {
            showAlert('Lead marked as Not Interested', 'success', 2500);
            closePostCallModal();
            loadTasks(currentStatus);
        } else {
            showAlert(result?.message || 'Failed to update', 'error', 3000);
        }
    }

    async function handleCNP() {
        const cnpCount = currentLeadData?.cnp_count || 0;
        let confirmMessage = 'Mark as CNP?';
        
        if (cnpCount === 0) {
            confirmMessage = 'Mark as CNP? A new calling task will be created for tomorrow.';
        } else if (cnpCount === 1) {
            confirmMessage = 'Mark as CNP again? This will be the 2nd CNP. Task will be completed automatically.';
        } else {
            confirmMessage = 'Mark as CNP? Task will be completed.';
        }
        
        showConfirmModal(confirmMessage, function() {
            executeCNP(cnpCount);
        });
    }
    
    async function executeCNP(cnpCount) {
        closeConfirmModal();
        
        const result = await apiCall(`/tasks/${currentTaskId}/outcome`, {
            method: 'POST',
            body: { outcome: 'cnp' },
        });

        if (result && result.success) {
            if (cnpCount >= 1) {
                showAlert('CNP recorded. Task completed automatically after 2 CNP attempts.', 'success', 3000);
            } else {
                showAlert('CNP recorded. New task created for tomorrow.', 'success', 3000);
            }
            closePostCallModal();
            loadTasks(currentStatus);
        } else {
            showAlert(result?.message || 'Failed to update', 'error', 3000);
        }
    }

    function handleCallAgain() {
        closePostCallModal();
        document.getElementById('rescheduleModal').classList.add('active');
    }

    function closeRescheduleModal() {
        document.getElementById('rescheduleModal').classList.remove('active');
        document.getElementById('rescheduleDateTime').value = '';
        document.getElementById('rescheduleNotes').value = '';
    }

    async function saveReschedule() {
        const datetime = document.getElementById('rescheduleDateTime').value;
        if (!datetime) {
            showAlert('Please select date and time', 'warning', 2500);
            return;
        }

        const result = await apiCall(`/tasks/${currentTaskId}/outcome`, {
            method: 'POST',
            body: {
                outcome: 'call_again',
                scheduled_at: datetime,
                notes: document.getElementById('rescheduleNotes').value,
            },
        });

        if (result && result.success) {
            showAlert('Call rescheduled successfully', 'success', 2500);
            closeRescheduleModal();
            loadTasks(currentStatus);
        } else {
            showAlert(result?.message || 'Failed to reschedule', 'error', 3000);
        }
    }

    async function handleBlock() {
        showConfirmModalWithInput(
            'Block this lead? This action cannot be undone.',
            'Reason for blocking (optional):',
            function(notes) {
                executeBlock(notes);
            }
        );
    }
    
    async function executeBlock(notes) {
        closeConfirmModal();
        
        const result = await apiCall(`/tasks/${currentTaskId}/outcome`, {
            method: 'POST',
            body: {
                outcome: 'block',
                notes: notes || 'Blocked by telecaller',
            },
        });

        if (result && result.success) {
            showAlert('Lead blocked successfully', 'success', 2500);
            closePostCallModal();
            loadTasks(currentStatus);
        } else {
            showAlert(result?.message || 'Failed to block', 'error', 3000);
        }
    }
    
    // Custom Confirmation Modal Functions
    let confirmCallback = null;
    let confirmInputValue = null;
    
    function showConfirmModal(message, callback) {
        document.getElementById('confirmModalTitle').textContent = 'Confirm Action';
        document.getElementById('confirmModalMessage').textContent = message;
        document.getElementById('confirmModalMessage').innerHTML = message.replace(/\n/g, '<br>');
        confirmCallback = callback;
        confirmInputValue = null;
        
        // Hide input field if exists
        const inputField = document.getElementById('confirmModalInput');
        if (inputField) {
            inputField.style.display = 'none';
            inputField.parentElement.style.display = 'none';
        }
        
        document.getElementById('confirmModal').classList.add('active');
    }
    
    function showConfirmModalWithInput(message, inputLabel, callback) {
        document.getElementById('confirmModalTitle').textContent = 'Confirm Action';
        document.getElementById('confirmModalMessage').textContent = message;
        confirmCallback = callback;
        
        // Show input field
        let inputContainer = document.getElementById('confirmModalInputContainer');
        if (!inputContainer) {
            const messageEl = document.getElementById('confirmModalMessage');
            inputContainer = document.createElement('div');
            inputContainer.id = 'confirmModalInputContainer';
            inputContainer.style.marginTop = '20px';
            inputContainer.innerHTML = `
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #063A1C;">${inputLabel}</label>
                <input type="text" id="confirmModalInput" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px;">
            `;
            messageEl.parentElement.insertBefore(inputContainer, messageEl.nextSibling);
        }
        inputContainer.style.display = 'block';
        document.getElementById('confirmModalInput').style.display = 'block';
        document.getElementById('confirmModalInput').value = '';
        
        document.getElementById('confirmModal').classList.add('active');
        
        // Focus on input
        setTimeout(() => {
            document.getElementById('confirmModalInput').focus();
        }, 100);
    }
    
    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.remove('active');
        confirmCallback = null;
        confirmInputValue = null;
        
        const inputField = document.getElementById('confirmModalInput');
        if (inputField) {
            inputField.value = '';
        }
    }
    
    function executeConfirmAction() {
        const inputField = document.getElementById('confirmModalInput');
        if (inputField && inputField.style.display !== 'none') {
            confirmInputValue = inputField.value;
        }
        
        if (confirmCallback) {
            confirmCallback(confirmInputValue);
        }
        
        closeConfirmModal();
    }
    
    // Close modal on backdrop click
    document.getElementById('confirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeConfirmModal();
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('confirmModal').classList.contains('active')) {
            closeConfirmModal();
        }
    });

    function openWhatsAppFromForm() {
        const phone = document.getElementById('prospectPhone').value;
        if (phone && phone !== '-') {
            const cleanPhone = phone.replace(/[^\d+]/g, '');
            window.open(`https://wa.me/${cleanPhone}`, '_blank');
        } else {
            showAlert('Phone number not available', 'warning', 2500);
        }
    }

    // Star Rating Functionality
    let selectedRating = 0;
    const stars = document.querySelectorAll('.star');
    const ratingText = document.getElementById('ratingText');
    const leadScoreInput = document.getElementById('leadScore');

    // Initialize star rating when modal opens
    function initializeStarRating() {
        selectedRating = 0;
        stars.forEach((star, index) => {
            star.textContent = '☆';
            star.style.color = '#d1d5db';
        });
        if (ratingText) ratingText.textContent = '';
        if (leadScoreInput) leadScoreInput.value = '';
    }

    // Handle star click
    if (stars.length > 0) {
        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                selectedRating = rating;
                
                // Update stars display
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.textContent = '★';
                        s.style.color = '#fbbf24';
                    } else {
                        s.textContent = '☆';
                        s.style.color = '#d1d5db';
                    }
                });
                
                // Update hidden input
                if (leadScoreInput) {
                    leadScoreInput.value = rating;
                }
                
                // Update rating text
                if (ratingText) {
                    const labels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
                    ratingText.textContent = labels[rating] || '';
                }
            });
            
            // Hover effect
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = '#d1d5db';
                    }
                });
            });
        });
        
        // Reset on mouse leave if no rating selected
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            if (selectedRating === 0) {
                stars.forEach(star => {
                    star.textContent = '☆';
                    star.style.color = '#d1d5db';
                });
            } else {
                stars.forEach((star, index) => {
                    if (index < selectedRating) {
                        star.textContent = '★';
                        star.style.color = '#fbbf24';
                    } else {
                        star.textContent = '☆';
                        star.style.color = '#d1d5db';
                    }
                });
            }
        });
    }

    document.getElementById('prospectForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!currentLeadData) {
            showAlert('Lead data not found', 'error', 2500);
            return;
        }

        if (!currentTaskId) {
            showAlert('Task ID not found', 'error', 2500);
            return;
        }

        const formData = {
            lead_id: currentLeadData.lead_id,
            task_id: currentTaskId,
            customer_name: document.getElementById('customerName').value || currentLeadData.lead_name,
            phone: document.getElementById('prospectPhone').value || currentLeadData.lead_phone,
            budget: document.getElementById('budget').value || '',
            preferred_location: document.getElementById('preferredLocation').value || '',
            size: document.getElementById('size').value || '',
            purpose: document.getElementById('purpose').value,
            possession: document.getElementById('possession').value || '',
            remark: document.getElementById('remark').value || '',
            lead_score: document.getElementById('leadScore').value || '',
        };

        // Validate required fields
        if (!formData.customer_name || !formData.phone || !formData.purpose || !formData.remark) {
            showAlert('Please fill all required fields (Name, Phone, Purpose, Remark)', 'warning', 3000);
            return;
        }
        
        // Validate lead score
        if (!formData.lead_score || formData.lead_score < 1 || formData.lead_score > 5) {
            showAlert('Please select a lead score rating (1-5 stars)', 'warning', 3000);
            return;
        }

        try {
            console.log('=== PROSPECT CREATION REQUEST DEBUG ===');
            console.log('Form Data:', formData);
            console.log('Current Task ID:', currentTaskId);
            console.log('Current Lead Data:', currentLeadData);
            console.log('Token Exists:', !!getToken());
            
            const result = await apiCall('/prospects/create', {
                method: 'POST',
                body: formData,
            });

            console.log('=== PROSPECT CREATION RESPONSE ===');
            console.log('Result:', result);

            if (result && result.success) {
                showAlert('Prospect created successfully! Sent for manager verification.', 'success', 3000);
                closeProspectModal();
                loadTasks(currentStatus);
            } else {
                const errorMsg = result?.message || result?.errors || 'Failed to create prospect';
                console.error('Prospect creation failed:', result);
                
                // Show detailed error message
                let alertMsg = typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg);
                showAlert(alertMsg, 'error', 4000);
            }
        } catch (error) {
            console.error('=== PROSPECT CREATION EXCEPTION ===');
            console.error('Error:', error);
            console.error('Error Message:', error.message);
            console.error('Error Stack:', error.stack);
            const errorMsg = 'Error: ' + (error.message || 'Failed to create prospect');
            showAlert(errorMsg, 'error', 4000);
        }
    });

    // View switching functionality
    let currentView = localStorage.getItem('telecaller_task_view') || 'list';
    
    function switchView(view) {
        currentView = view;
        localStorage.setItem('telecaller_task_view', view);
        
        // Update button states
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = 'white';
            btn.style.color = '#063A1C';
        });
        
        const activeBtn = document.getElementById(`view-${view}`);
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.background = '#205A44';
            activeBtn.style.color = 'white';
        }
        
        // Show/hide view containers
        const listView = document.getElementById('list-view-container');
        const kanbanView = document.getElementById('kanban-view-container');
        const calendarView = document.getElementById('calendar-view-container');
        
        if (listView) listView.style.display = view === 'list' ? 'block' : 'none';
        if (kanbanView) kanbanView.style.display = view === 'kanban' ? 'block' : 'none';
        if (calendarView) calendarView.style.display = view === 'calendar' ? 'block' : 'none';
        
        // Load data for the selected view
        if (view === 'list') {
            // Already loaded in loadTasks
        } else if (view === 'kanban') {
            setTimeout(() => loadKanbanView(), 100);
        } else if (view === 'calendar') {
            setTimeout(() => loadCalendarView(), 100);
        }
    }
    
    function loadKanbanView() {
        const kanbanBoard = document.getElementById('kanbanBoard');
        if (!kanbanBoard) return;
        
        kanbanBoard.innerHTML = '<div style="text-align: center; padding: 40px; color: #B3B5B4;"><i class="fas fa-spinner fa-spin" style="font-size: 32px; margin-bottom: 12px;"></i><p>Loading Kanban board...</p></div>';
        
        // Load tasks and organize by status
        apiCall(`/tasks?status=${currentStatus}&per_page=100`).then(result => {
            if (result && result.success) {
                const tasks = result.data || [];
                renderKanbanBoard(tasks);
            } else {
                kanbanBoard.innerHTML = '<div style="text-align: center; padding: 40px; color: #B3B5B4;"><p>Failed to load tasks</p></div>';
            }
        });
    }
    
    function renderKanbanBoard(tasks) {
        const kanbanBoard = document.getElementById('kanbanBoard');
        const statuses = [
            { key: 'pending', label: 'Pending', color: '#fef3c7' },
            { key: 'in_progress', label: 'In Progress', color: '#dbeafe' },
            { key: 'completed', label: 'Completed', color: '#d1fae5' }
        ];
        
        let html = '';
        statuses.forEach(status => {
            const statusTasks = tasks.filter(t => t.status === status.key);
            html += `
                <div class="kanban-column" style="background: ${status.color};">
                    <div class="kanban-column-header">
                        ${status.label} (${statusTasks.length})
                    </div>
                    <div id="kanban-${status.key}">
                        ${statusTasks.map(task => createKanbanCard(task)).join('')}
                    </div>
                </div>
            `;
        });
        
        kanbanBoard.innerHTML = html;
        
        // Add click handlers to cards
        kanbanBoard.querySelectorAll('.kanban-task-card .btn-call-task').forEach(btn => {
            btn.addEventListener('click', function() {
                const taskId = parseInt(this.getAttribute('data-task-id'));
                const phoneNumber = this.getAttribute('data-phone') || '';
                const taskData = tasks.find(t => t.id === taskId);
                if (taskData) {
                    initiateCall(taskId, phoneNumber, taskData);
                }
            });
        });
    }
    
    function createKanbanCard(task) {
        const scheduledDate = task.scheduled_at ? formatDate(task.scheduled_at) : '-';
        const initial = task.lead_name ? task.lead_name.charAt(0).toUpperCase() : 'T';
        const isOverdue = task.scheduled_at && new Date(task.scheduled_at) < new Date() && task.status !== 'completed';
        const overdueClass = isOverdue ? 'overdue' : '';
        
        return `
            <div class="kanban-task-card ${overdueClass}">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #205A44 0%, #063A1C 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px;">
                        ${initial}
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: #063A1C;">${task.lead_name || '-'}</h4>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #666;">${task.lead_phone || '-'}</p>
                    </div>
                </div>
                <div style="font-size: 12px; color: #666; margin-bottom: 12px;">
                    <i class="fas fa-calendar" style="margin-right: 5px;"></i>${scheduledDate}
                    ${isOverdue ? '<span style="display: block; margin-top: 4px; color: #ef4444; font-weight: 600;">OVERDUE</span>' : ''}
                </div>
                ${task.status === 'pending' || task.status === 'rescheduled' ? `
                    <button class="btn-call-task" data-task-id="${task.id}" data-phone="${task.lead_phone || ''}" style="width: 100%; padding: 10px; background: linear-gradient(135deg, #205A44 0%, #063A1C 100%); color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-phone"></i> Call
                    </button>
                ` : ''}
            </div>
        `;
    }
    
    function loadCalendarView() {
        const calendarContainer = document.getElementById('calendarContainer');
        if (!calendarContainer) return;
        
        calendarContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: #B3B5B4;"><i class="fas fa-spinner fa-spin" style="font-size: 32px; margin-bottom: 12px;"></i><p>Loading calendar...</p></div>';
        
        // Load tasks and render FullCalendar
        apiCall(`/tasks?status=${currentStatus}&per_page=100`).then(result => {
            if (result && result.success) {
                const tasks = result.data || [];
                renderFullCalendar(tasks);
            } else {
                calendarContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: #B3B5B4;"><p>Failed to load tasks</p></div>';
            }
        });
    }
    
    function renderFullCalendar(tasks) {
        const calendarContainer = document.getElementById('calendarContainer');
        calendarContainer.innerHTML = ''; // Clear loading message
        
        // Destroy existing calendar if any
        if (calendarInstance) {
            calendarInstance.destroy();
            calendarInstance = null;
        }
        
        // Prepare events for FullCalendar
        const events = tasks
            .filter(task => task.scheduled_at)
            .map(task => {
                const scheduledDate = new Date(task.scheduled_at);
                const isOverdue = scheduledDate < new Date() && task.status !== 'completed';
                
                // Determine color based on status and overdue
                let backgroundColor = '#205A44'; // Default green
                if (isOverdue) {
                    backgroundColor = '#ef4444'; // Red for overdue
                } else if (task.status === 'completed') {
                    backgroundColor = '#10b981'; // Green for completed
                } else if (task.status === 'in_progress') {
                    backgroundColor = '#3b82f6'; // Blue for in progress
                }
                
                return {
                    id: task.id,
                    title: `${task.lead_name || 'Lead'} (${task.lead_phone || '-'})`,
                    start: task.scheduled_at,
                    backgroundColor: backgroundColor,
                    borderColor: backgroundColor,
                    textColor: '#ffffff',
                    extendedProps: {
                        taskId: task.id,
                        leadName: task.lead_name,
                        leadPhone: task.lead_phone,
                        status: task.status,
                        isOverdue: isOverdue,
                        taskData: task
                    }
                };
            });
        
        // Initialize FullCalendar
        calendarInstance = new FullCalendar.Calendar(calendarContainer, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            events: events,
            eventClick: function(info) {
                // Show task details or initiate call
                const taskData = info.event.extendedProps.taskData;
                if (taskData && (taskData.status === 'pending' || taskData.status === 'rescheduled')) {
                    initiateCall(taskData.id, taskData.lead_phone, taskData);
                } else {
                    showAlert(`Task: ${info.event.extendedProps.leadName}\nPhone: ${info.event.extendedProps.leadPhone}\nStatus: ${info.event.extendedProps.status}`, 'info', 3000);
                }
            },
            eventDisplay: 'block',
            height: 'auto',
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            },
            locale: 'en',
            firstDay: 1, // Monday
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day',
                list: 'List'
            },
            dayHeaderFormat: { weekday: 'short' },
            eventContent: function(arg) {
                // Custom event rendering
                const taskData = arg.event.extendedProps;
                const isOverdue = taskData.isOverdue;
                const title = arg.event.title.length > 30 ? arg.event.title.substring(0, 30) + '...' : arg.event.title;
                
                return {
                    html: `
                        <div style="padding: 6px 8px; font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;">
                            ${isOverdue ? '<span style="display: inline-block; width: 6px; height: 6px; background: #fff; border-radius: 50%; margin-right: 4px; vertical-align: middle;"></span>' : ''}
                            <span style="vertical-align: middle;">${title}</span>
                        </div>
                    `
                };
            },
            datesSet: function(dateInfo) {
                // Optionally reload tasks when view changes
                // This allows filtering by visible date range
            },
            validRange: function(nowDate) {
                // Allow viewing past and future dates
                return null;
            }
        });
        
        calendarInstance.render();
        
        // Force calendar to recalculate size after render
        setTimeout(function() {
            if (calendarInstance) {
                calendarInstance.updateSize();
            }
        }, 200);
        
        // Force calendar to recalculate size
        setTimeout(function() {
            if (calendarInstance) {
                calendarInstance.updateSize();
            }
        }, 100);
    }

    // Initialize on page load
    function initializeTasks() {
        console.log('Tasks page loaded, initializing...');
        console.log('API_BASE_URL:', API_BASE_URL);
        console.log('Token:', getToken() ? 'Exists' : 'Missing');
        
        // Set initial view
        switchView(currentView);
        
        const contentDiv = document.getElementById('tasksContent');
        if (contentDiv) {
            loadTasks('pending');
        } else {
            console.error('tasksContent element not found, retrying...');
            setTimeout(initializeTasks, 200);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeTasks);
    } else {
        // DOM already loaded
        initializeTasks();
    }
</script>
@endpush

