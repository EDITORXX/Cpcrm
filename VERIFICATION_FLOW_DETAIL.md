# Prospect Verification Flow - Detailed Documentation

## Overview

The verification flow is a critical process in the Laravel CRM system where prospects created by telecallers are verified by Sales Managers before they are converted to verified leads. This document provides a comprehensive guide to the verification process.

---

## Step-by-Step Verification Flow

### Step 1: Telecaller Marks Lead as Interested

**Actor:** Telecaller

**Action:**
- Telecaller calls a lead assigned to them
- Customer shows interest in the property
- Telecaller marks the lead as "Interested" via the centralized form

**Data Collected:**
- Customer Name (required)
- Phone Number (required)
- Budget
- Preferred Location
- Size (property size)
- Purpose (End User/Investment)
- Possession Status
- Employee Remark/Notes
- Lead Score

**Result:**
- A Prospect record is created with `verification_status = 'pending_verification'`
- Prospect is linked to the original lead via `lead_id`
- Prospect is assigned to a manager via `assigned_manager` or `manager_id`

---

### Step 2: Prospect Creation

**System Action:**
- Prospect model creates a new record in the `prospects` table
- Initial status: `verification_status = 'pending_verification'`
- Prospect is linked to:
  - Lead (via `lead_id`)
  - Telecaller (via `telecaller_id`)
  - Assignment (via `assignment_id`)
  - Manager (via `assigned_manager` or `manager_id`)

**Key Fields:**
```php
- customer_name
- phone
- budget
- preferred_location
- size
- purpose
- possession
- lead_score
- employee_remark
- verification_status = 'pending_verification'
- assigned_manager
- telecaller_id
```

---

### Step 3: Event Fired

**Event:** `ProspectSentForVerification`

**Trigger:**
- Fired immediately after prospect creation
- Only if `verification_status === 'pending_verification'`

**Event Details:**
- Event class: `App\Events\ProspectSentForVerification`
- Contains: Prospect model instance (with relationships loaded)

**Code Location:**
- `app/Events/ProspectSentForVerification.php`
- Fired in: `app/Services/TelecallerService.php` (line 535)

---

### Step 4: Listener Creates Verification Task

**Listener:** `CreateManagerVerificationCallTask`

**Actions:**
1. Listens to `ProspectSentForVerification` event
2. Identifies the manager assigned for verification:
   - Priority: `assigned_manager` > `manager_id`
3. Creates a verification task:
   - Type: `phone_call`
   - Assigned to: Manager (identified above)
   - Scheduled at: Now + 10 minutes
   - Title: "Verify prospect: {customer_name}"
   - Description: "Manager verification call task"
   - Status: `pending`
   - Linked to lead via `lead_id`

**Code Location:**
- `app/Listeners/CreateManagerVerificationCallTask.php`
- Registered in: `app/Providers/EventServiceProvider.php`

**Task Details:**
```php
Task::create([
    'lead_id' => $prospect->lead_id,
    'assigned_to' => $managerId,
    'type' => 'phone_call',
    'title' => "Verify prospect: {$prospect->customer_name}",
    'description' => 'Manager verification call task',
    'status' => 'pending',
    'scheduled_at' => now()->addMinutes(10),
    'created_by' => $prospect->created_by,
]);
```

---

### Step 5: Sales Manager Receives Task

**Actor:** Sales Manager

**Interface:**
- Sales Manager logs into the system
- Navigates to Tasks section
- Views pending verification tasks

**Task Display:**
- Customer Name
- Phone Number
- Prospect Details (budget, location, size, etc.)
- Scheduled Time
- Status: Pending
- Action buttons: "Verify" / "Reject"

**API Endpoint:**
- `GET /api/sales-manager/tasks`
- Filter: `type = 'phone_call'`

---

### Step 6: Manager Makes Verification Call

**Actor:** Sales Manager

**Action:**
- Manager calls the customer using the phone number from the task
- Verifies the information provided by telecaller
- Confirms customer interest and requirements
- Collects additional information if needed

**Decision Points:**
After the call, manager decides one of three outcomes:
1. **VERIFIED** - Customer is genuine and interested (Hot/Warm/Cold/Junk)
2. **FOLLOW-UP** - Customer needs to be called later (requires follow-up date)
3. **REJECTED** - Customer is not genuine or not interested (requires rejection reason)

---

### Step 7: Verification Outcome - Three Paths

#### Path A: VERIFIED (Hot/Warm/Cold/Junk)

**API Endpoint:** `POST /api/sales-manager/tasks/{task}/verify`

**Process:**
1. Manager fills verification form with:
   - Customer name (updated)
   - Phone number (updated)
   - Lead Status: Hot / Warm / Cold / Junk
   - Manager Remark (optional)
   - Interested Projects (required)
   - Dynamic form fields (if configured)

2. System Updates:
   - Updates prospect:
     - `verification_status = 'verified'`
     - `verified_at = now()`
     - `verified_by = manager_id`
     - `lead_status = selected_status` (Hot/Warm/Cold/Junk)
     - `manager_remark = provided_remark`
     - Updates all prospect fields with form data
   
   - Updates/creates Lead:
     - Updates lead basic fields (name, phone, etc.)
     - Creates lead if doesn't exist
     - Sets lead status to `verified_prospect`
     - Saves dynamic form field values
     - Marks `form_filled_by_manager = true`
   
   - Creates Lead Assignment:
     - Deactivates existing assignments
     - Creates new active assignment to manager
     - Fires `LeadAssigned` event
   
   - Completes Task:
     - Marks verification task as `completed`
     - Sets `completed_at = now()`

3. Notification:
   - Sends notification to telecaller
   - Notification type: `verified`
   - Contains: lead details, verification status, manager remark

**Result:**
- Prospect is marked as verified
- Lead is created/updated and assigned to manager
- Telecaller receives notification
- Prospect moves out of pending verification list

---

#### Path B: FOLLOW-UP

**API Endpoint:** `POST /api/sales-manager/tasks/{task}/verify`

**Process:**
1. Manager selects Lead Status: **Follow-up**
2. Manager provides:
   - Follow-up Date & Time (required)
   - Manager Remark (optional)
   - All other form fields

2. System Updates:
   - Updates prospect:
     - **Keeps** `verification_status = 'pending_verification'` (remains pending)
     - **Does NOT** set `verified_at` or `verified_by`
     - `lead_status = 'warm'`
     - `manager_remark = follow_up_remark`
     - Updates all prospect fields
   
   - Creates Follow-up Task:
     - Type: `phone_call`
     - Assigned to: Manager
     - Scheduled at: Follow-up date provided
     - Title: "Follow-up call: {customer_name}"
     - Status: `pending`
   
   - Completes Current Task:
     - Marks verification task as `completed`

3. **No Notification** sent to telecaller (prospect still pending)

**Result:**
- Prospect remains in `pending_verification` status
- New follow-up task created for the specified date
- Current verification task marked as completed
- Prospect will be verified/rejected during follow-up call

---

#### Path C: REJECTED

**API Endpoint:** `POST /api/sales-manager/tasks/{task}/reject`

**Process:**
1. Manager provides:
   - Rejection Reason (required, max 1000 chars)

2. System Updates:
   - Updates prospect:
     - `verification_status = 'rejected'`
     - `rejection_reason = provided_reason`
     - `verified_at = now()` (timestamp recorded)
     - `verified_by = null` (cleared)
     - Updates notes with rejection reason
   
   - Completes Task:
     - Marks verification task as `completed`

3. Notification:
   - Sends notification to telecaller
   - Notification type: `rejected`
   - Contains: lead details, rejection reason, manager name

**Result:**
- Prospect is marked as rejected
- Telecaller receives notification with rejection reason
- Prospect moves out of pending verification list
- Prospect cannot be verified again (permanent rejection)

---

## Verification Status States

### 1. pending_verification
- **Initial state** when prospect is created
- Prospect is waiting for manager verification
- Appears in manager's verification task list
- Can transition to: `verified` or `rejected`
- Can remain as `pending_verification` (for follow-up)

### 2. verified
- **Final state** - Prospect verified by manager
- Lead is created/updated and assigned
- Telecaller receives notification
- Cannot transition back to `pending_verification`
- Appears in verified prospects list

### 3. rejected
- **Final state** - Prospect rejected by manager
- Rejection reason is recorded
- Telecaller receives notification
- Cannot transition back to `pending_verification`
- Appears in rejected prospects list

---

## API Endpoints

### Telecaller Endpoints

1. **Mark Lead as Interested**
   - `POST /api/telecaller/interested`
   - Creates prospect with `pending_verification` status
   - Fires `ProspectSentForVerification` event

2. **View Pending Verifications**
   - `GET /api/telecaller/verification-pending`
   - Returns prospects awaiting verification
   - Shows verification status updates

### Sales Manager Endpoints

1. **Get Verification Tasks**
   - `GET /api/sales-manager/tasks?type=phone_call`
   - Returns all verification tasks assigned to manager
   - Includes prospect and lead details

2. **Verify Prospect**
   - `POST /api/sales-manager/tasks/{task}/verify`
   - Verifies prospect with form data
   - Updates prospect and lead
   - Creates assignment
   - Sends notification

3. **Reject Prospect**
   - `POST /api/sales-manager/tasks/{task}/reject`
   - Rejects prospect with reason
   - Updates prospect status
   - Sends notification

### CRM/Admin Endpoints

1. **View All Prospects**
   - `GET /api/crm/verifications/pending-prospects`
   - Returns all prospects (pending, verified, rejected)
   - Read-only view for CRM/Admin

---

## Database Schema

### Prospects Table

Key fields for verification:
```php
- verification_status: enum('pending_verification', 'verified', 'rejected')
- verified_at: timestamp (nullable)
- verified_by: foreign key to users (nullable)
- rejection_reason: text (nullable)
- lead_status: string (Hot/Warm/Cold/Junk)
- manager_remark: text (nullable)
- employee_remark: text (nullable)
- assigned_manager: foreign key to users
- manager_id: foreign key to users
- telecaller_id: foreign key to users
- lead_id: foreign key to leads
```

### Tasks Table

Verification task fields:
```php
- type: 'phone_call' (for verification tasks)
- assigned_to: manager_id
- lead_id: links to lead
- status: 'pending' → 'completed'
- scheduled_at: timestamp
- title: "Verify prospect: {name}"
- description: "Manager verification call task"
```

---

## Events & Listeners

### Event: ProspectSentForVerification

**Fired:** When prospect is created with `verification_status = 'pending_verification'`

**Listeners:**
1. `CreateManagerVerificationCallTask`
   - Creates verification task for manager
   - Scheduled 10 minutes from now

2. (Other listeners can be added as needed)

### Event: LeadAssigned

**Fired:** When lead is assigned after verification

**Listeners:**
- `SendLeadAssignedNotification` (if configured)
- Other assignment-related listeners

---

## Notifications

### Verified Notification

**Recipient:** Telecaller (who created the prospect)

**Content:**
- Title: "Prospect Verified: {customer_name}"
- Message: "Manager {manager_name} verified prospect {customer_name}. Status: {lead_status}"
- Type: `verified`
- Data: lead_id, prospect_id, verification_status, lead_status, manager_remark, verified_at

### Rejected Notification

**Recipient:** Telecaller (who created the prospect)

**Content:**
- Title: "Prospect Rejected: {customer_name}"
- Message: "Manager {manager_name} rejected prospect {customer_name}. Reason: {rejection_reason}"
- Type: `rejected`
- Data: lead_id, prospect_id, verification_status, rejection_reason, rejected_at

---

## Important Notes

1. **Follow-up Status:**
   - When Follow-up is selected, prospect **remains** in `pending_verification` status
   - A new follow-up task is created
   - Prospect will be verified/rejected during the follow-up call
   - No notification is sent to telecaller for follow-up

2. **Task Completion:**
   - Verification task is always marked as completed after verify/reject action
   - Even for follow-up, the current verification task is completed
   - New follow-up task is created separately

3. **Lead Creation:**
   - Lead is auto-created when prospect is verified
   - Lead is NOT created for rejected prospects
   - Lead is NOT created for follow-up (until verified)

4. **Status Immutability:**
   - Once verified, prospect cannot go back to `pending_verification`
   - Once rejected, prospect cannot be verified again
   - Follow-up keeps status as `pending_verification` until next verification

5. **Manager Assignment:**
   - Priority: `assigned_manager` > `manager_id` > `telecaller->manager_id`
   - Task is assigned to the identified manager
   - Manager can only verify tasks assigned to them

---

## Flow Diagram Files

1. **Verification_Flow_Diagram.pdf** - Visual diagram showing the complete flow
2. **VERIFICATION_FLOW_DETAIL.md** - This detailed documentation

Both files provide comprehensive coverage of the verification flow from different perspectives.

---

## Code Locations

### Models
- `app/Models/Prospect.php` - Prospect model with verify/reject methods
- `app/Models/Lead.php` - Lead model
- `app/Models/Task.php` - Task model

### Controllers
- `app/Http/Controllers/Api/TelecallerController.php` - Telecaller interested endpoint
- `app/Http/Controllers/Api/SalesManagerController.php` - Verify/Reject endpoints
- `app/Http/Controllers/Api/Crm/VerificationController.php` - CRM view endpoints

### Services
- `app/Services/TelecallerService.php` - Prospect creation logic
- `app/Services/TaskService.php` - Task creation logic
- `app/Services/NotificationService.php` - Notification sending

### Events & Listeners
- `app/Events/ProspectSentForVerification.php` - Verification event
- `app/Listeners/CreateManagerVerificationCallTask.php` - Task creation listener
- `app/Providers/EventServiceProvider.php` - Event registration

---

## Testing the Flow

1. **As Telecaller:**
   - Call a lead
   - Mark as "Interested"
   - Fill the centralized form
   - Submit
   - Prospect should be created with `pending_verification` status

2. **As Sales Manager:**
   - Login and check tasks
   - Find verification task (should appear within 10 minutes)
   - Click "Verify" or "Reject"
   - Fill form and submit
   - Check that prospect status updates
   - Check that telecaller receives notification

3. **Verification Checks:**
   - Prospect status changes correctly
   - Lead is created/updated (for verified)
   - Task is marked as completed
   - Notification is sent to telecaller
   - Assignment is created (for verified)

---

## Summary

The verification flow ensures that all prospects created by telecallers are properly verified by Sales Managers before becoming verified leads. The process includes:

- **Automated task creation** via events and listeners
- **Three possible outcomes**: Verified, Follow-up, or Rejected
- **Proper status tracking** throughout the process
- **Notifications** to keep telecallers informed
- **Lead creation and assignment** for verified prospects
- **Flexible follow-up** system for prospects needing later verification

This flow maintains data integrity and ensures that only genuine, verified prospects are converted to leads in the CRM system.
