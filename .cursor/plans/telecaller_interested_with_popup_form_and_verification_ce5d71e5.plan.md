---
name: Telecaller Interested with Popup Form and Verification
overview: When telecaller marks interested, centralized lead requirement form opens in popup modal. Form has "Send for Verification" button. On submit, saves form data to lead, creates prospect, and sends for manager verification (same old flow). Also implement target system (200 calls/month, 15 verified prospects/month) with pie charts on dashboard.
todos:
  - id: create_api_get_form
    content: Create API endpoint to get lead form data for modal (GET /api/telecaller/tasks/{task}/lead-form)
    status: in_progress
  - id: create_api_submit_verification
    content: Create API endpoint to submit form for verification (POST /api/telecaller/tasks/{taskId}/submit-for-verification)
    status: pending
  - id: create_modal_component
    content: Create modal component that loads centralized form (lead-requirement-form.blade.php)
    status: pending
  - id: update_handleInterested_modal
    content: Update handleInterested() to open modal instead of redirect
    status: pending
  - id: map_form_to_prospect
    content: Map centralized form field values to Prospect model fields when creating prospect
    status: pending
  - id: update_target_seeder
    content: "Update TargetSeeder: 200 calls/month and 15 prospects/month for telecallers"
    status: pending
  - id: fix_dashboard_metrics
    content: Fix getPerformanceMetrics() to use correct task_type and include prospects_verified
    status: pending
  - id: fix_target_model_calls
    content: Fix Target::getCallsCompletedCount() to use TelecallerTask instead of Task
    status: pending
  - id: add_pie_charts_dashboard
    content: Add pie chart section to dashboard showing target vs achievement for calls and verified prospects
    status: pending
isProject: false
---

# Telecaller Interested Flow: Popup Form with Verification

## Requirements

1. **Popup Modal**: Lead detail/edit form (centralized form) opens in popup when telecaller marks interested
2. **Send for Verification Button**: Replace "Save Requirements" with "Send for Verification" button in modal
3. **Verification Flow**: Same old flow - create prospect, send to manager, manager gets calling task automatically
4. **Targets**: Set 200 calls/month and 15 verified prospects/month for telecallers
5. **Dashboard Charts**: Show pie charts for target vs achievement

## Implementation Plan

### 1. Create API Endpoint to Load Lead Form in Modal

**File**: `app/Http/Controllers/Api/TelecallerController.php`

**New Method**: `getLeadFormForModal(Request $request, $taskId)`

- Route: `GET /api/telecaller/tasks/{task}/lead-form`
- Gets TelecallerTask, extracts lead_id
- Returns lead data and form HTML (rendered view as JSON)
- Or returns lead ID and form fields configuration

**Alternative Approach**: Return lead ID, then load form via AJAX to `/leads/{leadId}/edit` endpoint and extract form HTML

### 2. Update handleInterested() to Open Modal

**File**: `resources/views/telecaller/sections/tasks.blade.php`

**Change**: Modify `handleInterested()` function (line 1164)

- Remove redirect logic
- Get lead_id from task
- Load form content via AJAX
- Open modal with form content
- Pre-fill name and phone from lead

**Code Pattern**:

```javascript
async function handleInterested() {
    closePostCallModal();
    
    if (!currentTaskId) {
        showAlert('Error: Task ID not found', 'error', 3000);
        return;
    }
    
    // Get lead data for form
    const response = await apiCall(`/tasks/${currentTaskId}/lead-form`);
    
    if (response && response.lead_id) {
        // Load form content and open modal
        loadLeadFormInModal(response.lead_id, response.lead_data);
    }
}

async function loadLeadFormInModal(leadId, leadData) {
    // Fetch form HTML or render form in modal
    // Open modal with centralized form
    openLeadRequirementModal(leadId, leadData);
}
```

### 3. Create Modal Component

**File**: `resources/views/telecaller/modals/lead-requirement-form.blade.php` (new)

**Structure**:

- Large modal (fullscreen or 90% width/height)
- Scrollable content area
- Include centralized form partial: `@include('leads.partials.centralized-form')`
- Modify form action and button text via JavaScript
- Add "Send for Verification" button at bottom

**Key Features**:

- Modal title: "Fill Lead Requirements - {Lead Name}"
- Form loads with existing lead data
- Only telecaller-level fields visible
- Submit button text: "Send for Verification"
- Cancel button closes modal

### 4. Create Submit Endpoint for Verification

**File**: `app/Http/Controllers/Api/TelecallerController.php`

**New Method**: `submitLeadFormForVerification(Request $request, $taskId)`

- Route: `POST /api/telecaller/tasks/{taskId}/submit-for-verification`
- Validates centralized form fields using `LeadFormValidationService`
- Updates lead with form field values (saves to LeadFormFieldValue)
- Maps form field values to prospect fields:
  - `category` → Not directly mapped (prospect doesn't have category)
  - `preferred_location` → `preferred_location`
  - `type` → Not directly mapped
  - `purpose` → `purpose`
  - `possession` → `possession`
  - `budget` → `budget`
  - `final_status` → Used for lead status
- Creates Prospect with `verification_status = 'pending_verification'`
- Fires `ProspectSentForVerification` event (triggers manager task creation)
- Completes the TelecallerTask
- Returns success response

**Field Mapping Logic**:

```php
// Get form field values from request
$category = $request->input('category');
$preferredLocation = $request->input('preferred_location');
$type = $request->input('type');
$purpose = $request->input('purpose');
$possession = $request->input('possession');
$budget = $request->input('budget');
$finalStatus = $request->input('final_status');
$remark = $request->input('remark') ?? '';

// Create prospect with mapped values
$prospect = Prospect::create([
    'lead_id' => $lead->id,
    'telecaller_id' => $telecallerId,
    'manager_id' => $manager->id,
    'customer_name' => $lead->name,
    'phone' => $lead->phone,
    'budget' => $budget,
    'preferred_location' => $preferredLocation,
    'purpose' => $purpose,
    'possession' => $possession,
    'remark' => $remark,
    'verification_status' => 'pending_verification',
    'created_by' => $telecallerId,
]);
```

### 5. Update Form Submission Handler

**File**: `public/js/telecaller-lead-form-modal.js` (new) or modify existing

**Function**: Handle form submission in modal

- Intercept form submit
- Collect all form field values (including dynamic fields)
- Send to verification endpoint
- Show loading state
- On success: Close modal, refresh tasks, show success message
- On error: Show error message, keep modal open

### 6. Update Target Seeder

**File**: `database/seeders/TargetSeeder.php`

**Change**: Lines 58-72

- Set `target_calls`: 200 (monthly, not 6000)
- Set `target_prospects_verified`: 15 (monthly)
- Keep current month period logic

**Updated Code**:

```php
foreach ($telecallers as $telecaller) {
    Target::updateOrCreate(
        [
            'user_id' => $telecaller->id,
            'target_month' => $currentMonth,
        ],
        [
            'target_meetings' => 0,
            'target_visits' => 0,
            'target_closers' => 0,
            'target_prospects_extract' => 0, // Not needed
            'target_prospects_verified' => 15, // Monthly target
            'target_calls' => 200, // Monthly target (not daily)
        ]
    );
}
```

### 7. Fix Dashboard Metrics Calculation

**File**: `app/Services/TelecallerDashboardService.php`

**Method**: `getPerformanceMetrics()` (lines 340-406)

**Issues to Fix**:

- Line 362: Uses `task_type = 'call'` but should be `'calling'`
- Line 388: Maps `target_meetings` but should map `target_calls`
- Missing: `prospects_verified` count and target

**Fixes**:

```php
// Calculate achievements
$callsMade = TelecallerTask::where('assigned_to', $userId)
    ->where('task_type', 'calling') // Fixed: was 'call'
    ->where('status', 'completed')
    ->whereBetween('completed_at', [$currentMonth, $currentMonthEnd])
    ->count();

$prospectsVerified = Prospect::where('telecaller_id', $userId)
    ->where('verification_status', 'verified')
    ->whereYear('verified_at', $currentMonth->year)
    ->whereMonth('verified_at', $currentMonth->month)
    ->count();

return [
    'has_target' => true,
    'targets' => [
        'calls' => $target->target_calls ?? 0, // Fixed: was target_meetings
        'prospects_verified' => $target->target_prospects_verified ?? 0, // Added
    ],
    'achievements' => [
        'calls' => $callsMade,
        'prospects_verified' => $prospectsVerified, // Added
    ],
    'percentages' => [
        'calls' => $target->target_calls > 0 ? round(($callsMade / $target->target_calls) * 100, 1) : 0,
        'prospects_verified' => $target->target_prospects_verified > 0 ? round(($prospectsVerified / $target->target_prospects_verified) * 100, 1) : 0,
    ],
];
```

### 8. Fix Target Model Method

**File**: `app/Models/Target.php`

**Method**: `getCallsCompletedCount()` (lines 65-73)

**Change**: Use TelecallerTask instead of Task model

```php
public function getCallsCompletedCount(): int
{
    return TelecallerTask::where('assigned_to', $this->user_id)
        ->where('task_type', 'calling')
        ->where('status', 'completed')
        ->whereYear('completed_at', $this->target_month->year)
        ->whereMonth('completed_at', $this->target_month->month)
        ->count();
}
```

### 9. Add Pie Charts to Dashboard

**File**: `resources/views/telecaller/sections/dashboard.blade.php`

**Location**: After "Performance Tracker" section (after line 284)

**Implementation**:

- Add new section: "Target vs Achievement (Calls & Prospects)"
- Two pie charts side by side
- Chart 1: Calls (Achieved vs Remaining)
- Chart 2: Verified Prospects (Achieved vs Remaining)
- Show target, achieved, percentage below each chart

**Chart Data Structure**:

```javascript
// Calls Chart
{
    labels: ['Achieved', 'Remaining'],
    data: [achieved_calls, Math.max(0, target_calls - achieved_calls)],
    backgroundColor: ['#10b981', '#e5e7eb']
}

// Prospects Chart
{
    labels: ['Achieved', 'Remaining'],
    data: [achieved_prospects, Math.max(0, target_prospects - achieved_prospects)],
    backgroundColor: ['#10b981', '#e5e7eb']
}
```

### 10. Update Modal JavaScript

**File**: Create or update JavaScript for modal handling

**Functions Needed**:

- `openLeadRequirementModal(leadId, leadData)` - Opens modal and loads form
- `closeLeadRequirementModal()` - Closes modal
- `handleFormSubmitForVerification(event)` - Handles form submission
- Modify form action and button on modal open

## Data Flow

```
Telecaller marks "Interested"
    ↓
handleInterested() called
    ↓
API: GET /tasks/{taskId}/lead-form
    ↓
Returns lead_id and lead data
    ↓
Open Modal → Load centralized form via AJAX
    ↓
User fills form (telecaller-level fields only)
    ↓
Clicks "Send for Verification"
    ↓
API: POST /tasks/{taskId}/submit-for-verification
    ↓
1. Validate form fields
2. Save form values to LeadFormFieldValue
3. Map field values to Prospect fields
4. Create Prospect (verification_status='pending_verification')
5. Fire ProspectSentForVerification Event
    ↓
CreateManagerVerificationCallTask Listener
    ↓
Auto-create calling task for Manager
    ↓
Complete TelecallerTask
    ↓
Return Success → Close Modal → Refresh Tasks
```

## Files to Create

1. `resources/views/telecaller/modals/lead-requirement-form.blade.php` - Modal container with centralized form
2. `public/js/telecaller-lead-form-modal.js` - Modal JavaScript handlers

## Files to Modify

1. `resources/views/telecaller/sections/tasks.blade.php` - Update handleInterested() function
2. `app/Http/Controllers/Api/TelecallerController.php` - Add endpoints:

   - `getLeadFormForModal()` - GET endpoint for form data
   - `submitLeadFormForVerification()` - POST endpoint for verification

3. `app/Services/TelecallerDashboardService.php` - Fix getPerformanceMetrics()
4. `app/Models/Target.php` - Fix getCallsCompletedCount() method
5. `resources/views/telecaller/sections/dashboard.blade.php` - Add pie chart section
6. `database/seeders/TargetSeeder.php` - Update target values (200 calls, 15 prospects)

## Key Implementation Details

### Form Field to Prospect Field Mapping

When creating prospect from centralized form:

- `name` (from lead) → `customer_name`
- `phone` (from lead) → `phone`
- `budget` (form field) → `budget`
- `preferred_location` (form field) → `preferred_location`
- `purpose` (form field) → `purpose`
- `possession` (form field) → `possession`
- `remark` or `notes` → `remark`
- `final_status` → Used for lead status update, not stored in prospect

### Verification Flow (Unchanged)

- Prospect created with `verification_status = 'pending_verification'`
- `ProspectSentForVerification` event fires
- `CreateManagerVerificationCallTask` listener creates task for manager
- Manager verifies/rejects via existing flow
- If verified, `verified_at` is set, counts in telecaller achievement

### Achievement Counting

- **Calls**: `TelecallerTask` where `task_type='calling'`, `status='completed'`, in current month
- **Verified Prospects**: `Prospect` where `telecaller_id` matches, `verification_status='verified'`, `verified_at` in current month
- Only verified prospects count (rejected/pending don't count)