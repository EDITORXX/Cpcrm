---
name: Show Meeting Popup for Manager Meeting Tasks
overview: When a sales manager calls from a meeting task, show an enhanced meeting post-call popup with options: Meeting on Time (new), Reschedule (opens meeting section modal), Cancel Meeting (calls meeting section function), Complete (opens meeting section modal), Mark as Dead (opens meeting section modal). After any action, complete the calling task.
todos:
  - id: include_meeting_popup_sales_manager
    content: Include meeting-post-call-popup component in sales-manager layout
    status: pending
  - id: update_meeting_popup_buttons
    content: Update meeting-post-call-popup to include Meeting on Time, Reschedule, Cancel, Complete, and Mark as Dead buttons matching meeting section
    status: pending
  - id: update_popup_handler_functions
    content: Update handlePostCallAction to handle meeting_on_time, reschedule (opens modal), cancel (calls function), complete (opens modal), mark_dead (opens modal)
    status: pending
  - id: add_task_completion_logic
    content: Add logic to complete the calling task after any meeting action (for both Task and TelecallerTask models)
    status: pending
  - id: update_manager_call_handler
    content: Update handleManagerCallClick to detect meeting tasks and show meeting popup with task_id tracking
    status: pending
isProject: false
---

## Show Meeting Popup for Manager Meeting Tasks

### Problem
When a sales manager calls from a meeting task (pre-meeting reminder), the system should show an enhanced meeting post-call popup with all options from the meeting section. After any action, the calling task should be completed.

### Requirements
1. Add "Meeting on Time" option - completes calling task immediately
2. Reschedule button should open the same reschedule modal from meeting section
3. Cancel Meeting button should call the same cancelMeeting function from meeting section
4. Complete button should open the same completeMeetingModal from meeting section
5. Mark as Dead button should open the same markDeadModal from meeting section
6. After any action, the calling task (Task model for managers) should be completed

### Files to Modify

1. **`resources/views/sales-manager/layout.blade.php`**
   - Include meeting-post-call-popup component
   - Include meeting section modals (rescheduleMeetingModal, completeMeetingModal, markDeadModal) if not already included

2. **`resources/views/components/meeting-post-call-popup.blade.php`**
   - Update popup buttons to match meeting section options
   - Add "Meeting on Time" button
   - Update button handlers to call meeting section functions
   - Track task_id to complete it after actions

3. **`resources/views/sales-manager/tasks.blade.php`**
   - Update `handleManagerCallClick()` to detect meeting tasks
   - Extract meeting_id from task notes
   - Show meeting popup with task_id for meeting tasks
   - Ensure meeting section modals are accessible

4. **`app/Http/Controllers/Api/MeetingController.php`** (Optional)
   - Update `completePreCall()` to also complete the Task model task if it's a manager task

### Implementation Details

**1. Include Meeting Popup and Modals in Sales Manager Layout:**

Add to `resources/views/sales-manager/layout.blade.php` before closing body tag:

```php
<!-- Include Meeting Post-Call Popup Component -->
@include('components.meeting-post-call-popup')

<!-- Include Meeting Section Modals (if not already in tasks.blade.php) -->
<!-- These modals are needed for reschedule, complete, and mark dead actions -->
```

**2. Update Meeting Post-Call Popup Component:**

Update `resources/views/components/meeting-post-call-popup.blade.php`:

- Add task_id tracking: `window.currentTaskId = null;`
- Update buttons to match meeting section:
  - Meeting on Time (new - green/orange)
  - Reschedule (orange - opens rescheduleMeetingModal)
  - Cancel Meeting (red - calls cancelMeeting function)
  - Complete (green - opens completeMeetingModal)
  - Mark as Dead (red - opens markDeadModal)

**3. Update handlePostCallAction Function:**

- Handle "meeting_on_time" action: Complete calling task and close popup
- Handle "reschedule": Close popup, open rescheduleMeetingModal, set meeting ID
- Handle "cancel": Call cancelMeeting function, then complete task
- Handle "complete": Close popup, open completeMeetingModal, set meeting ID
- Handle "mark_dead": Close popup, open markDeadModal, set meeting ID
- After each action, complete the calling task via API

**4. Update Manager Call Handler:**

Modify `handleManagerCallClick()` in `resources/views/sales-manager/tasks.blade.php`:
- Detect meeting task by checking notes for "Pre-meeting reminder"
- Extract meeting_id from notes (format: "Meeting ID: {id}")
- Show meeting popup with both meeting_id and task_id
- Pass task_id to popup so it can be completed after actions

**5. Task Completion API:**

Create or use existing endpoint to complete Task model tasks:
- Endpoint: `POST /api/sales-manager/tasks/{task}/complete`
- Or update meeting controller to handle task completion

### Notes

- The meeting popup should work for both telecaller and manager, but manager needs access to meeting section modals
- Task completion should work for both Task model (managers) and TelecallerTask model (telecallers)
- Meeting section modals (rescheduleMeetingModal, completeMeetingModal, markDeadModal) should be accessible from tasks page
- After any meeting action, the calling task must be marked as completed
- "Meeting on Time" is a new action that simply completes the task without any meeting status change
