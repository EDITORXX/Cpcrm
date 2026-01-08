---
name: SIM-Based Call Tracking - Website Implementation
overview: Website side par SIM-based call tracking implement karna - Database enhancement, Web Controllers, Dashboard integration (Telecaller, Sales Manager, Admin), Call statistics, Call history views, aur Call notes functionality.
todos:
  - id: db_enhancement
    content: Database Enhancement - Add missing fields to call_logs table (user_id, call_notes, call_outcome, next_followup_date, etc.)
    status: completed
  - id: create_model
    content: Create CallLog Model with relationships and helper methods (formattedDuration, scopes, etc.)
    status: completed
    dependencies:
      - db_enhancement
  - id: update_relationships
    content: Update User and Lead models to add callLogs relationships
    status: completed
    dependencies:
      - create_model
  - id: create_service
    content: Create CallLogService for statistics calculation and helper methods
    status: completed
    dependencies:
      - create_model
  - id: web_controller
    content: Create CallLogController (Web) with index, show, create, store, edit, update, destroy, getStatistics methods
    status: completed
    dependencies:
      - create_service
  - id: api_controller
    content: Create/Enhance Api/CallLogController with API endpoints for mobile app sync and web API
    status: completed
    dependencies:
      - create_service
  - id: add_routes
    content: Add web routes and API routes for call logs with proper middleware and permissions
    status: completed
    dependencies:
      - web_controller
      - api_controller
  - id: telecaller_dashboard
    content: Update Telecaller Dashboard - Add call statistics section with cards, charts, and recent calls list
    status: completed
    dependencies:
      - add_routes
  - id: manager_dashboard
    content: Update Sales Manager Dashboard - Add team call statistics, top performers, and team breakdown
    status: completed
    dependencies:
      - add_routes
  - id: admin_dashboard
    content: Update Admin Dashboard - Add system-wide call statistics, calls by role/user, and outcome distribution
    status: completed
    dependencies:
      - add_routes
  - id: dashboard_controllers
    content: Update Dashboard Controllers (DashboardController, TelecallerDashboardController, AdminDashboardController) to include call statistics
    status: completed
    dependencies:
      - telecaller_dashboard
      - manager_dashboard
      - admin_dashboard
  - id: calls_index_view
    content: Create calls/index.blade.php - List page with filters, search, table, pagination, and export options
    status: completed
    dependencies:
      - web_controller
  - id: calls_detail_view
    content: Create calls/show.blade.php - Detail page with call info, notes, related information, and actions
    status: completed
    dependencies:
      - web_controller
  - id: calls_form_views
    content: Create calls/create.blade.php and edit.blade.php - Forms for manual call entry with validation
    status: completed
    dependencies:
      - web_controller
  - id: calls_statistics_view
    content: Create calls/statistics.blade.php - Statistics page with charts, filters, and detailed breakdowns
    status: completed
    dependencies:
      - web_controller
  - id: navigation_menu
    content: Update navigation menu in layouts/app.blade.php to add Calls menu items for all roles
    status: completed
    dependencies:
      - calls_index_view
  - id: export_functionality
    content: Add export functionality (CSV, PDF) to CallLogController for call logs data
    status: completed
    dependencies:
      - calls_index_view
  - id: realtime_updates
    content: Add Pusher events for real-time call log updates on dashboards
    status: completed
    dependencies:
      - dashboard_controllers
isProject: false
---

# SIM-Based Call Tracking - Website Implementation Plan

## Overview

Website (Laravel Backend + Web Frontend) par SIM-based call tracking implement karna. Mobile app se call logs automatically sync honge, aur web dashboards par sabhi users ko call statistics, history, aur analytics dikhenge.

## Current Status

- ✅ `call_logs` table exists (migration: `2026_01_01_120000_create_call_logs_table.php`)
- ✅ API endpoints exist (`TelecallerController::saveCallLog`, `getCallLogs`, `getCallStatistics`)
- ❌ Web Controllers missing
- ❌ Dashboard integration missing
- ❌ Call notes functionality incomplete
- ❌ Multi-role access missing (currently only Telecaller API)

---

## Phase 1: Database Enhancement

### 1.1 Add Missing Fields to call_logs Table

**Migration:** `database/migrations/2026_XX_XX_add_fields_to_call_logs_table.php`

**Fields to Add:**

- `user_id` (nullable) - For Sales Executive/Manager calls (not just telecaller)
- `call_notes` (text, nullable) - Rich text notes
- `recording_url` (string, nullable) - Future: call recording link
- `call_outcome` (enum: 'interested', 'not_interested', 'callback', 'no_answer', 'busy', 'other') - Call result
- `next_followup_date` (datetime, nullable) - Auto-suggest next followup
- `is_verified` (boolean, default false) - User verified this call
- `synced_from_mobile` (boolean, default false) - Track if synced from app

**Indexes to Add:**

- `user_id, start_time` (composite)
- `lead_id, start_time` (composite)
- `call_outcome`
- `synced_from_mobile`

---

## Phase 2: Models & Relationships

### 2.1 Create CallLog Model

**File:** `app/Models/CallLog.php`

**Relationships:**

- `belongsTo(User::class, 'telecaller_id')` or `belongsTo(User::class, 'user_id')`
- `belongsTo(Lead::class)`
- `belongsTo(Task::class, 'task_id')` (nullable)

**Methods:**

- `getFormattedDurationAttribute()` - Format seconds to "Xh Ym Zs"
- `getCallTypeLabelAttribute()` - Human readable call type
- `getStatusLabelAttribute()` - Human readable status
- `scopeForUser($query, $userId)` - Filter by user
- `scopeForTeam($query, $teamMemberIds)` - Filter by team
- `scopeToday($query)` - Today's calls
- `scopeThisWeek($query)` - This week's calls
- `scopeThisMonth($query)` - This month's calls

### 2.2 Update User Model

**File:** `app/Models/User.php`

**Add Relationship:**

```php
public function callLogs()
{
    return $this->hasMany(CallLog::class, 'user_id');
}
```

### 2.3 Update Lead Model

**File:** `app/Models/Lead.php`

**Add Relationship:**

```php
public function callLogs()
{
    return $this->hasMany(CallLog::class);
}
```

---

## Phase 3: Web Controllers

### 3.1 Create CallLogController (Web)

**File:** `app/Http/Controllers/CallLogController.php`

**Methods:**

1. `index(Request $request)` - List all call logs with filters

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Filters: date range, user, lead, call type, status, outcome
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Pagination: 50 per page
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Role-based access:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Telecaller: Own calls only
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Sales Manager: Team calls
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Admin/CRM: All calls

2. `show($id)` - View single call log details

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Show full call information
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Show related lead details
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Show call notes
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Show next followup suggestion

3. `create()` - Manual call entry form (for web)

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Form for manual call logging
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Lead selection
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Phone number input
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Start/End time pickers
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Duration auto-calculate
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Notes field

4. `store(Request $request)` - Save manual call log

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Validation
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Auto-calculate duration if end_time provided
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Link to lead
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Save notes

5. `edit($id)` - Edit call log

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Edit form (only own calls or admin)

6. `update(Request $request, $id)` - Update call log

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Update notes, outcome, followup date
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Validation

7. `destroy($id)` - Delete call log

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Soft delete or hard delete (admin only)

8. `getStatistics(Request $request)` - Get call statistics

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Today, This Week, This Month stats
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Role-based filtering

### 3.2 Create Api/CallLogController (Enhanced)

**File:** `app/Http/Controllers/Api/CallLogController.php`

**Methods:**

1. `index(Request $request)` - API endpoint for call logs list
2. `store(Request $request)` - Save call log from mobile app
3. `show($id)` - Get single call log
4. `update(Request $request, $id)` - Update call log
5. `getStatistics(Request $request)` - Get statistics
6. `getTeamStatistics(Request $request)` - Team statistics (for managers)
7. `getDashboardStats(Request $request)` - Dashboard quick stats

**Enhancements:**

- Support for `user_id` (not just telecaller_id)
- Call notes support
- Call outcome tracking
- Bulk sync from mobile app

---

## Phase 4: Routes

### 4.1 Web Routes

**File:** `routes/web.php`

```php
// Call Logs Routes (Web)
Route::middleware(['auth'])->group(function () {
    Route::prefix('calls')->name('calls.')->group(function () {
        Route::get('/', [CallLogController::class, 'index'])->name('index');
        Route::get('/create', [CallLogController::class, 'create'])->name('create');
        Route::post('/', [CallLogController::class, 'store'])->name('store');
        Route::get('/{id}', [CallLogController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CallLogController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CallLogController::class, 'update'])->name('update');
        Route::delete('/{id}', [CallLogController::class, 'destroy'])->name('destroy');
        Route::get('/statistics/data', [CallLogController::class, 'getStatistics'])->name('statistics');
    });
});
```

### 4.2 API Routes

**File:** `routes/api.php`

```php
// Enhanced Call Logs API (for all roles)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('call-logs')->name('api.call-logs.')->group(function () {
        Route::get('/', [Api\CallLogController::class, 'index']);
        Route::post('/', [Api\CallLogController::class, 'store']);
        Route::get('/statistics', [Api\CallLogController::class, 'getStatistics']);
        Route::get('/team-statistics', [Api\CallLogController::class, 'getTeamStatistics']);
        Route::get('/dashboard-stats', [Api\CallLogController::class, 'getDashboardStats']);
        Route::get('/{id}', [Api\CallLogController::class, 'show']);
        Route::put('/{id}', [Api\CallLogController::class, 'update']);
        Route::post('/bulk-sync', [Api\CallLogController::class, 'bulkSync']); // For mobile app
    });
});
```

---

## Phase 5: Dashboard Integration

### 5.1 Telecaller Dashboard

**File:** `resources/views/telecaller/sections/dashboard.blade.php`

**Add Call Statistics Section:**

- Today's Total Calls (card)
- Total Call Duration (hours:minutes)
- Average Call Duration
- Connection Rate (%)
- Calls Per Hour Chart (Chart.js)
- Recent Calls List (last 5 calls)
- Quick Actions: "View All Calls", "Add Manual Call"

**Metrics to Display:**

```php
- Today's Calls: 12
- Total Duration: 2h 15m
- Avg Duration: 11m 15s
- Connection Rate: 85%
- Incoming: 3 | Outgoing: 9
```

### 5.2 Sales Manager Dashboard

**File:** `resources/views/sales-manager/dashboard.blade.php`

**Add Team Call Statistics Section:**

- Team Total Calls Today
- Team Total Duration
- Team Average Duration
- Top Performers (by calls)
- Calls by Team Member (chart)
- Team Call Breakdown Table
- Filter: Today/This Week/This Month

**Metrics to Display:**

```php
- Team Calls: 45
- Team Duration: 8h 30m
- Avg Duration: 11m 20s
- Top Performer: John (15 calls)
```

### 5.3 Admin Dashboard

**File:** `resources/views/admin/dashboard.blade.php`

**Add System Call Statistics Section:**

- System Total Calls (Today/Week/Month)
- System Total Duration
- Calls by Role (chart)
- Calls by User (top 10)
- Call Outcome Distribution (chart)
- Recent Calls (all users)
- Export Options

**Metrics to Display:**

```php
- Total Calls Today: 120
- Total Duration: 20h 45m
- By Role: Telecaller (80), Sales Exec (30), Manager (10)
- Top User: John (25 calls)
```

### 5.4 Update Dashboard Controllers

**File:** `app/Http/Controllers/Api/DashboardController.php`

**Add Methods:**

- `getCallStatistics($user)` - Get call stats for dashboard
- `getTeamCallStatistics($user)` - Get team call stats (for managers)
- `getSystemCallStatistics()` - Get system-wide stats (for admin)

**Update `index()` method:**

- Add `call_statistics` to response for Telecaller
- Add `team_call_statistics` to response for Sales Manager
- Add `system_call_statistics` to response for Admin/CRM

---

## Phase 6: Views & UI

### 6.1 Call Logs Index Page

**File:** `resources/views/calls/index.blade.php`

**Features:**

- Filters: Date Range, User, Lead, Call Type, Status, Outcome
- Search: Phone number, Lead name
- Table: Phone, Lead, User, Duration, Type, Status, Date, Actions
- Pagination
- Export: CSV, PDF
- Quick Stats Cards (top of page)

**Table Columns:**

- Phone Number
- Lead Name
- User (Telecaller/Executive)
- Duration (formatted)
- Call Type (Incoming/Outgoing)
- Status (Completed/Missed/Busy)
- Outcome (Interested/Not Interested/etc)
- Date & Time
- Actions (View, Edit, Delete)

### 6.2 Call Log Detail Page

**File:** `resources/views/calls/show.blade.php`

**Sections:**

1. Call Information Card

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Phone Number
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Lead Details (with link)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - User (who made call)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Date & Time
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Duration
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Call Type & Status
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Outcome

2. Call Notes Section

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Display notes (rich text)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Edit notes button
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Notes history (if multiple edits)

3. Related Information

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Lead details card
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Previous calls to same lead
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Next followup suggestion
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Related tasks

4. Actions

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Edit Call
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Add Followup
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - View Lead
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Delete Call

### 6.3 Create/Edit Call Log Form

**File:** `resources/views/calls/create.blade.php` & `edit.blade.php`

**Form Fields:**

- Lead Selection (searchable dropdown)
- Phone Number (auto-fill from lead)
- Call Type (Incoming/Outgoing)
- Start Time (datetime picker)
- End Time (datetime picker)
- Duration (auto-calculate, editable)
- Status (Completed/Missed/Busy/Rejected)
- Outcome (dropdown)
- Call Notes (rich text editor - TinyMCE or similar)
- Next Followup Date (optional)

**Validation:**

- Lead required
- Phone number required
- Start time required
- If end time provided, must be after start time
- Duration must match start/end time difference

### 6.4 Call Statistics Page

**File:** `resources/views/calls/statistics.blade.php`

**Sections:**

1. Summary Cards

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Total Calls
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Total Duration
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Average Duration
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Connection Rate

2. Charts

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Calls Per Day (line chart)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Calls Per Hour (bar chart)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Calls by Type (pie chart)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Calls by Outcome (pie chart)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Calls by User (bar chart - for managers/admin)

3. Filters

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Date Range (Today/This Week/This Month/Custom)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - User (for admin/managers)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Call Type
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Outcome

4. Detailed Table

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Daily breakdown
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - User-wise breakdown
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                - Export options

---

## Phase 7: Services

### 7.1 Create CallLogService

**File:** `app/Services/CallLogService.php`

**Methods:**

1. `getCallStatistics($userId, $dateRange)` - Calculate statistics
2. `getTeamCallStatistics($managerId, $dateRange)` - Team stats
3. `getSystemCallStatistics($dateRange)` - System-wide stats
4. `formatDuration($seconds)` - Format seconds to "Xh Ym Zs"
5. `calculateConnectionRate($totalCalls, $completedCalls)` - Calculate %
6. `getCallsPerHour($userId, $date)` - Hourly breakdown
7. `getTopPerformers($dateRange, $limit = 10)` - Top users by calls
8. `suggestNextFollowup($callLog)` - AI/rule-based followup suggestion

---

## Phase 8: Dashboard API Endpoints

### 8.1 Update TelecallerDashboardController

**File:** `app/Http/Controllers/Api/TelecallerDashboardController.php`

**Add to `index()` method:**

```php
'call_statistics' => [
    'today' => [
        'total_calls' => ...,
        'total_duration' => ...,
        'average_duration' => ...,
        'connection_rate' => ...,
    ],
    'this_week' => [...],
    'recent_calls' => [...], // Last 5 calls
]
```

### 8.2 Create SalesManagerDashboardController (if not exists)

**File:** `app/Http/Controllers/Api/SalesManagerDashboardController.php`

**Add:**

- Team call statistics
- Team performance metrics
- Top performers

### 8.3 Update AdminDashboardController

**File:** `app/Http/Controllers/Admin/AdminDashboardController.php`

**Add:**

- System-wide call statistics
- Calls by role
- Calls by user
- Call outcome distribution

---

## Phase 9: Navigation & Menu

### 9.1 Update Navigation

**File:** `resources/views/layouts/app.blade.php`

**Add Menu Items:**

- For Telecaller: "My Calls" link
- For Sales Manager: "Team Calls" link
- For Admin/CRM: "All Calls" link
- For All: "Call Statistics" link

**Menu Structure:**

```
Dashboard
Leads
Calls (new)
 - My Calls / Team Calls / All Calls
 - Call Statistics
 - Add Call
Site Visits
...
```

---

## Phase 10: Permissions & Middleware

### 10.1 Update Permissions

**File:** `app/Http/Middleware/CheckPermission.php` (if exists)

**Add Permissions:**

- `view_calls` - View own calls
- `view_team_calls` - View team calls (Sales Manager)
- `view_all_calls` - View all calls (Admin/CRM)
- `create_calls` - Create call logs
- `edit_calls` - Edit call logs
- `delete_calls` - Delete call logs

### 10.2 Role-Based Access

- **Telecaller**: View/Edit own calls only
- **Sales Executive**: View/Edit own calls
- **Sales Manager**: View team calls + own calls
- **Admin/CRM**: View/Edit/Delete all calls

---

## Phase 11: Export Functionality

### 11.1 Add Export Methods

**File:** `app/Http/Controllers/CallLogController.php`

**Methods:**

1. `exportCsv(Request $request)` - Export to CSV
2. `exportPdf(Request $request)` - Export to PDF

**Export Fields:**

- Phone Number
- Lead Name
- User Name
- Date & Time
- Duration
- Call Type
- Status
- Outcome
- Notes

---

## Phase 12: Real-time Updates

### 12.1 Add Pusher Events

**File:** `app/Events/CallLogCreated.php`

**Broadcast:**

- When new call log created
- Update dashboard in real-time
- Notify managers of team calls

---

## Implementation Order

### Week 1: Foundation

1. Database migration (add fields)
2. Create CallLog model
3. Update relationships
4. Create CallLogService

### Week 2: Controllers & API

5. Create CallLogController (Web)
6. Create/Enhance Api/CallLogController
7. Add routes
8. Add permissions

### Week 3: Dashboard Integration

9. Update Telecaller Dashboard
10. Update Sales Manager Dashboard
11. Update Admin Dashboard
12. Update Dashboard Controllers

### Week 4: Views & UI

13. Create call logs index page
14. Create call log detail page
15. Create create/edit forms
16. Create statistics page
17. Add navigation links

### Week 5: Polish & Testing

18. Add export functionality
19. Add real-time updates
20. Testing & bug fixes
21. Performance optimization

---

## Files to Create/Modify

### New Files:

1. `database/migrations/2026_XX_XX_add_fields_to_call_logs_table.php`
2. `app/Models/CallLog.php`
3. `app/Http/Controllers/CallLogController.php`
4. `app/Http/Controllers/Api/CallLogController.php`
5. `app/Services/CallLogService.php`
6. `resources/views/calls/index.blade.php`
7. `resources/views/calls/show.blade.php`
8. `resources/views/calls/create.blade.php`
9. `resources/views/calls/edit.blade.php`
10. `resources/views/calls/statistics.blade.php`

### Files to Modify:

1. `app/Models/User.php` - Add callLogs relationship
2. `app/Models/Lead.php` - Add callLogs relationship
3. `routes/web.php` - Add call log routes
4. `routes/api.php` - Add/enhance call log API routes
5. `resources/views/telecaller/sections/dashboard.blade.php` - Add call stats
6. `resources/views/sales-manager/dashboard.blade.php` - Add team call stats
7. `resources/views/admin/dashboard.blade.php` - Add system call stats
8. `app/Http/Controllers/Api/DashboardController.php` - Add call stats
9. `app/Http/Controllers/Api/TelecallerDashboardController.php` - Add call stats
10. `app/Http/Controllers/Admin/AdminDashboardController.php` - Add call stats
11. `resources/views/layouts/app.blade.php` - Add navigation links

---

## Success Metrics

- ✅ All dashboards show call statistics
- ✅ Call logs visible to appropriate roles
- ✅ Manual call entry working
- ✅ Statistics accurate
- ✅ Export functionality working
- ✅ Real-time updates working
- ✅ Mobile app can sync call logs

---

## Notes

- Mobile app se data sync ke liye API endpoints already exist, bas enhance karna hai
- Call notes ke liye rich text editor use karna (TinyMCE ya similar)
- Charts ke liye Chart.js use karna (already in project)
- Performance ke liye database indexes important
- Pagination zaroori hai for large datasets