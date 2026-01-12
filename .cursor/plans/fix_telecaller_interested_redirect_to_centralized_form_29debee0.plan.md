---
name: Fix Telecaller Interested Redirect to Centralized Form
overview: When telecaller marks lead as "interested", the old "Prospect Details" modal is appearing instead of redirecting to the new centralized form. Need to update handleInterested() function in tasks.blade.php to call API and redirect instead of opening old modal.
todos:
  - id: update_handleInterested
    content: Update handleInterested() function in tasks.blade.php to call API and redirect instead of opening old modal
    status: completed
  - id: verify_api_response
    content: Verify callOutcome() API endpoint returns redirect URL correctly
    status: completed
  - id: test_redirect_flow
    content: Test that telecaller marking interested redirects to centralized form page
    status: completed
isProject: false
---

# Fix Telecaller Interested Redirect to Centralized Form

## Problem

When telecaller marks a lead as "interested" from the tasks page, the old "Prospect Details" modal (with fields like Budget, Size, Purpose, Assign To) is still opening instead of redirecting to the new centralized lead requirement form.

## Root Cause

The `handleInterested()` function in `resources/views/telecaller/sections/tasks.blade.php` (line 1164) directly opens the old `prospectModal` without calling the API endpoint that handles the interested status and provides redirect URL.

## Solution

### 1. Update handleInterested() function in tasks.blade.php

   - **File**: `resources/views/telecaller/sections/tasks.blade.php`
   - **Location**: Lines 1164-1176
   - **Change**: Instead of opening `prospectModal`, call the API endpoint `/tasks/${currentTaskId}/call-outcome` with `outcome: 'interested'`
   - **Behavior**: 
     - Close post call modal
     - Call API endpoint
     - Show success message
     - Redirect to centralized form using `response.redirect` or construct URL from `response.lead_id`
     - Refresh tasks list after redirect

### 2. Ensure API endpoint returns correct response

   - **File**: `app/Http/Controllers/Api/TelecallerController.php`
   - **Location**: `callOutcome()` method (lines 652-677)
   - **Status**: Already updated in previous implementation
   - **Verification**: Ensure it returns `redirect` URL and `lead_id` in response

### 3. Keep old prospectModal for reference (optional)

   - **File**: `resources/views/telecaller/sections/tasks.blade.php`
   - **Location**: Lines 729-783 (prospectModal HTML)
   - **Action**: Leave as-is for now, but it should not be triggered by handleInterested() anymore
   - **Future**: Can be removed if confirmed not needed elsewhere

## Implementation Details

### Modified Code Pattern

```javascript
// OLD (current - opens modal):
function handleInterested() {
    closePostCallModal();
    if (currentLeadData) {
        document.getElementById('customerName').value = currentLeadData.lead_name || '';
        document.getElementById('prospectPhone').value = currentLeadData.lead_phone || '';
        document.getElementById('assignTo').value = currentLeadData.manager_name || 'Not Assigned';
        if (typeof initializeStarRating === 'function') {
            initializeStarRating();
        }
        document.getElementById('prospectModal').classList.add('active'); // ❌ OLD MODAL
    }
}

// NEW (should redirect to centralized form):
async function handleInterested() {
    closePostCallModal();
    
    if (!currentTaskId) {
        showAlert('Error: Task ID not found', 'error', 3000);
        return;
    }
    
    try {
        const response = await apiCall(`/tasks/${currentTaskId}/call-outcome`, {
            method: 'POST',
            body: JSON.stringify({ outcome: 'interested' })
        });
        
        if (response && response.success) {
            // Show success message
            if (typeof showAlert === 'function') {
                showAlert(response.message || 'Lead marked as interested. Redirecting to form...', 'success', 2000);
            } else {
                alert(response.message || 'Lead marked as interested. Redirecting to form...');
            }
            
            // Redirect to centralized form after a short delay
            if (response.redirect) {
                setTimeout(() => {
                    window.location.href = response.redirect;
                }, 1500);
            } else if (response.lead_id) {
                // Fallback: construct URL manually
                setTimeout(() => {
                    window.location.href = `/leads/${response.lead_id}/edit`;
                }, 1500);
            }
            
            // Refresh tasks list if available
            if (typeof loadTasks === 'function') {
                setTimeout(() => loadTasks(), 1000);
            }
        } else {
            showAlert(response?.error || 'Failed to mark as interested', 'error', 3000);
        }
    } catch (error) {
        console.error('Error marking as interested:', error);
        showAlert('Error: Failed to mark as interested. Please try again.', 'error', 3000);
    }
}
```

## Files to Modify

1. **resources/views/telecaller/sections/tasks.blade.php**

   - Update `handleInterested()` function (lines 1164-1176)
   - Change from synchronous modal opening to async API call + redirect
   - Add error handling

## Testing Checklist

- [ ] Telecaller marks lead as "interested" from tasks page
- [ ] Old "Prospect Details" modal does NOT appear
- [ ] API call is made to `/tasks/{taskId}/call-outcome` with outcome='interested'
- [ ] Success message is shown
- [ ] Redirect happens to `/leads/{leadId}/edit` (centralized form)
- [ ] Centralized form loads with telecaller-level fields visible
- [ ] Tasks list refreshes after redirect (if user stays on page)

## Related Files (Already Updated)

- ✅ `app/Http/Controllers/Api/TelecallerController.php` - `callOutcome()` method already returns redirect URL
- ✅ `resources/views/telecaller/modals/call-outcome.blade.php` - Already updated with redirect logic (but this is a different modal flow)

## Notes

- The `prospectModal` in tasks.blade.php (lines 729-783) can remain in the code but will not be triggered by `handleInterested()` anymore
- The old prospect form submission endpoint might still exist, but telecallers should now use the centralized form instead
- If there are other places calling `handleInterested()` or opening `prospectModal`, they may need similar updates