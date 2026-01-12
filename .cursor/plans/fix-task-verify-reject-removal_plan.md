# Fix Tasks Not Removing After Verify/Reject

## Issue
When tasks are verified or rejected, they are marked as completed but don't disappear from the view immediately, especially in "overdue" and "pending" filter views.

## Root Cause
1. After verify/reject, `loadTasks()` is called but there's a delay before the API response updates
2. The task card is not immediately removed from DOM
3. Completed tasks might still show up if currentStatus filter allows them

## Implementation

### 1. Add Task ID to Card HTML

**Update `resources/views/sales-manager/tasks.blade.php`:**
- In `renderTasks()` function (around line 952), add `id="task-card-${task.id}"` to the task card div

### 2. Immediate DOM Removal for Reject

**Update `submitRejectProspect()` function (around line 1401):**
- After successful API call, immediately remove the task card from DOM using the ID
- Add fade-out animation for better UX
- Then call `loadTasks()` after a short delay

### 3. Immediate DOM Removal for Verify

**Update verify submit function (around line 1889):**
- After successful API call, immediately remove the task card from DOM
- Add fade-out animation
- Then call `loadTasks()` after a short delay

### 4. Backend Query Safety Check

**Update `app/Http/Controllers/Api/SalesManagerController.php`:**
- In `getTasks()` method (around line 666), ensure that when status filter is "pending" or "overdue", completed tasks are explicitly excluded
- Add `->where('status', '!=', 'completed')` for these filters as a safety check

## Technical Details

### Task Card ID
```javascript
return `
    <div id="task-card-${task.id}" class="task-card ${overdueClass}">
        <!-- card content -->
    </div>
`;
```

### Immediate Removal (Reject)
```javascript
if (result && result.success) {
    // Remove task card immediately
    const taskCard = document.getElementById(`task-card-${currentTaskId}`);
    if (taskCard) {
        taskCard.style.transition = 'opacity 0.3s, transform 0.3s';
        taskCard.style.opacity = '0';
        taskCard.style.transform = 'scale(0.95)';
        setTimeout(() => {
            taskCard.remove();
            loadTasks(); // Reload after removal
        }, 300);
    } else {
        loadTasks(); // Fallback if card not found
    }
    
    showAlert('Prospect rejected successfully', 'success');
    closeRejectReasonModal();
    currentTaskId = null;
}
```

### Immediate Removal (Verify)
```javascript
if (response && response.success) {
    // Remove task card immediately
    const taskCard = document.getElementById(`task-card-${currentTaskId}`);
    if (taskCard) {
        taskCard.style.transition = 'opacity 0.3s, transform 0.3s';
        taskCard.style.opacity = '0';
        taskCard.style.transform = 'scale(0.95)';
        setTimeout(() => {
            taskCard.remove();
            loadTasks(); // Reload after removal
        }, 300);
    } else {
        setTimeout(() => loadTasks(), 500); // Fallback
    }
    
    // ... rest of success handling
}
```

### Backend Safety Check
```php
// After line 663, add explicit exclusion for pending/overdue
if (in_array($statusFilter, ['pending', 'overdue'])) {
    $query->where('status', '!=', 'completed');
}
```

## Files to Update
- resources/views/sales-manager/tasks.blade.php
  - Add task card ID in renderTasks() (line ~952)
  - Update submitRejectProspect() to remove card immediately (line ~1401)
  - Update verify submit function to remove card immediately (line ~1889)

- app/Http/Controllers/Api/SalesManagerController.php
  - Add explicit exclusion of completed tasks for pending/overdue filters (line ~666)

## Testing Checklist
- [ ] Verify task - card disappears immediately with animation
- [ ] Reject task - card disappears immediately with animation
- [ ] Task doesn't show in "overdue" filter after verify/reject
- [ ] Task doesn't show in "pending" filter after verify/reject
- [ ] Task list refreshes correctly after removal
