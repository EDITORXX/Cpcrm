---
name: Meta Leads Bidirectional Google Sheets Sync
overview: "Create a complete bidirectional sync system for Meta/Facebook leads: Map Meta fields to CRM, send leads from Google Sheet to CRM via webhook, and update Google Sheet with CRM status (sent status, assigned user, call date/time)."
todos:
  - id: "1"
    content: Update PabblyWebhookController to map Meta fields to CRM fields and store sheet_row_number
    status: completed
  - id: "2"
    content: Create GoogleSheetUpdateService with methods to update Google Sheet rows
    status: completed
  - id: "3"
    content: Create UpdateGoogleSheetOnLeadAssigned listener to update assigned user column
    status: completed
  - id: "4"
    content: Create UpdateGoogleSheetOnCallMade listener to update call date/time columns
    status: completed
  - id: "5"
    content: Update Google Apps Script code to handle Meta field names and update CRM status columns
    status: completed
  - id: "6"
    content: Register new event listeners in EventServiceProvider
    status: completed
  - id: "7"
    content: Update GoogleSheetsService with methods to write to specific sheet cells
    status: completed
isProject: false
---

# Meta Leads Bidirectional Google Sheets Sync

## Overview

Create a complete bidirectional sync system where:

1. Meta leads come to Google Sheet with specific field names
2. Google Apps Script sends leads to CRM via webhook
3. CRM processes lead (creates, assigns, tracks calls)
4. CRM updates Google Sheet with: sent status, assigned user, call date/time, error reasons

## Meta Field Mapping

### Meta Fields → CRM Fields Mapping


| Meta Field                           | CRM Field                        | Column | Required     |
| ------------------------------------ | -------------------------------- | ------ | ------------ |
| `full_name`                          | `name`                           | -      | Yes          |
| `phone_number`                       | `phone`                          | -      | Yes          |
| `whatsapp_number`                    | -                                | -      | Optional     |
| `email`                              | `email`                          | -      | Optional     |
| `are_u_from_lucknow_??`              | `city`                           | -      | Optional     |
| `great_,_what_are_you_looking_for`   | `requirements`                   | -      | Optional     |
| `purpose_for_purchase`               | `use_end_use`                    | -      | Optional     |
| `what_kind_of_property`              | `property_type`                  | -      | Optional     |
| `budget_approx_?`                    | `budget`                         | -      | Optional     |
| `when_to_buy`                        | `possession_status`              | -      | Optional     |
| `meeting_time_to_discuss_in_details` | `next_followup_at`               | -      | Optional     |
| `job_title`                          | `notes`                          | -      | Optional     |
| `id`                                 | Store in `notes` or custom field | -      | For tracking |
| `created_time`                       | Store in `notes`                 | -      | For tracking |
| `ad_name`, `campaign_name`           | `source`                         | -      | Optional     |


### Google Sheet Columns for CRM Status

Add these columns to track CRM status:


| Column              | Field Name                   | Description                     |
| ------------------- | ---------------------------- | ------------------------------- |
| `crm_sent_status`   | "Sent to CRM" / "Error: ..." | Whether lead was sent to CRM    |
| `crm_lead_id`       | Lead ID from CRM             | CRM lead ID for reference       |
| `crm_assigned_user` | User name                    | Which user lead was assigned to |
| `crm_call_date`     | Date/Time                    | When user called the lead       |
| `crm_call_status`   | "Called" / "Pending"         | Call status                     |


## Implementation Plan

### 1. Update Google Apps Script for Meta Fields

**File**: Update `GOOGLE_SHEETS_AUTO_SYNC_SETUP.md` with Meta field mapping

**Changes**:

- Map Meta field names to CRM fields
- Handle Meta-specific columns
- Send Meta lead ID and created_time for tracking
- Update status columns after sending

**Key Functions**:

- `mapMetaFieldsToCrm()` - Convert Meta fields to CRM format
- `sendMetaLeadToCRM()` - Send with Meta field mapping
- `updateSheetStatus()` - Update CRM status columns

### 2. Update PabblyWebhookController for Meta Leads

**File**: `app/Http/Controllers/Api/PabblyWebhookController.php`

**Changes**:

- Accept Meta field names in webhook payload
- Map Meta fields to CRM fields
- Store Meta lead ID (`id`) and `created_time` in lead notes or custom field
- Return `lead_id` for Google Sheet tracking
- Store `sheet_row_number` if provided in webhook

**New Method**:

- `mapMetaFields($payload)` - Map Meta field names to CRM fields

### 3. Create Google Sheet Update Service

**File**: `app/Services/GoogleSheetUpdateService.php` (new)

**Methods**:

- `updateLeadStatus($sheetConfigId, $rowNumber, $data)` - Update CRM status columns
- `updateAssignedUser($sheetConfigId, $rowNumber, $userId)` - Update assigned user
- `updateCallInfo($sheetConfigId, $rowNumber, $callLog)` - Update call date/time
- `updateErrorStatus($sheetConfigId, $rowNumber, $error)` - Update error status

**Data Structure**:

```php
$updateData = [
    'crm_sent_status' => 'Sent to CRM',
    'crm_lead_id' => 123,
    'crm_assigned_user' => 'John Doe',
    'crm_call_date' => '2026-01-27 14:30:00',
    'crm_call_status' => 'Called'
];
```

### 4. Create Event Listeners for Sheet Updates

**New Files**:

- `app/Listeners/UpdateGoogleSheetOnLeadCreated.php`
- `app/Listeners/UpdateGoogleSheetOnLeadAssigned.php`
- `app/Listeners/UpdateGoogleSheetOnCallMade.php`

**Events to Listen**:

- `LeadAssigned` - Update assigned user column
- `CallLogCreated` - Update call date/time column
- Lead creation success - Update sent status and lead ID

### 5. Update GoogleSheetsService

**File**: `app/Services/GoogleSheetsService.php`

**New Methods**:

- `updateSheetRow($sheetConfigId, $rowNumber, $columnUpdates)` - Update specific columns
- `getSheetColumnIndex($sheetId, $columnName)` - Get column index by name
- `writeToSheet($sheetId, $range, $values)` - Write data to sheet

**Column Updates**:

- Support updating multiple columns in one call
- Handle column name to index mapping
- Error handling for missing columns

### 6. Store Sheet Row Mapping

**Enhancement**: When lead is created from webhook:

- Store `sheet_row_number` in Lead or CrmAssignment
- Store `sheet_config_id` or sheet identifier
- Link lead back to Google Sheet row

**Database**:

- Use existing `sheet_row_number` and `sheet_config_id` in CrmAssignment
- Or add to Lead model if needed

### 7. Update Webhook Response

**File**: `app/Http/Controllers/Api/PabblyWebhookController.php`

**Response Enhancement**:

```json
{
  "status": "ok",
  "lead_id": 123,
  "assigned_to": {
    "id": 5,
    "name": "John Doe"
  },
  "sheet_update": {
    "row_number": 2,
    "status": "updated"
  }
}
```

### 8. Google Apps Script Enhancement

**Update**: `GOOGLE_SHEETS_AUTO_SYNC_SETUP.md`

**New Features**:

- Map Meta field names automatically
- Store lead_id in sheet after creation
- Poll CRM API to get assignment and call info (optional)
- Or use webhook callback to update sheet

**Column Detection**:

- Auto-detect Meta field columns
- Map to CRM fields dynamically
- Handle missing columns gracefully

## Data Flow

### Flow 1: Meta Lead → Google Sheet → CRM

```
1. Meta lead arrives in Google Sheet (with Meta fields)
2. Google Apps Script detects new row
3. Script maps Meta fields to CRM fields
4. Script sends to CRM webhook: /api/pabbly/webhook
5. CRM creates lead and returns lead_id + assigned_user
6. Script updates Google Sheet:
   - crm_sent_status = "Sent to CRM"
   - crm_lead_id = 123
   - crm_assigned_user = "John Doe"
```

### Flow 2: CRM → Google Sheet Updates

```
1. Lead assigned to user → Event: LeadAssigned
2. Listener: UpdateGoogleSheetOnLeadAssigned
3. Updates sheet: crm_assigned_user = "User Name"

4. User calls lead → Event: CallLogCreated
5. Listener: UpdateGoogleSheetOnCallMade
6. Updates sheet:
   - crm_call_date = "2026-01-27 14:30:00"
   - crm_call_status = "Called"
```

## Google Sheet Structure

### Required Columns (Meta Fields)

All Meta fields as provided:

- id, created_time, ad_id, ad_name, adset_id, adset_name, campaign_id, campaign_name, form_id, form_name, is_organic, platform, are_u_from_lucknow_??, great_,*what_are_you_looking_for, purpose_for_purchase, what_kind_of_property, budget_approx*?, when_to_buy, meeting_time_to_discuss_in_details, email, full_name, whatsapp_number, phone_number, job_title, inbox_url, lead_status

### CRM Status Columns (New - Add to Sheet)

Add these columns at the end:


| Column              | Field Name      | Example Value                              |
| ------------------- | --------------- | ------------------------------------------ |
| `crm_sent_status`   | CRM Sent Status | "Sent to CRM" / "Error: Validation failed" |
| `crm_lead_id`       | CRM Lead ID     | 123                                        |
| `crm_assigned_user` | Assigned User   | "John Doe"                                 |
| `crm_call_date`     | Call Date/Time  | "2026-01-27 14:30:00"                      |
| `crm_call_status`   | Call Status     | "Called" / "Pending"                       |


## Implementation Details

### 1. Meta Field Mapping Function

**Location**: `app/Http/Controllers/Api/PabblyWebhookController.php`

```php
protected function mapMetaFields(array $payload): array
{
    return [
        'name' => $payload['full_name'] ?? $payload['name'] ?? null,
        'phone' => $payload['phone_number'] ?? $payload['phone'] ?? null,
        'email' => $payload['email'] ?? null,
        'city' => $this->extractCity($payload['are_u_from_lucknow_??'] ?? null),
        'requirements' => $payload['great_,_what_are_you_looking_for'] ?? null,
        'use_end_use' => $payload['purpose_for_purchase'] ?? null,
        'property_type' => $this->mapPropertyType($payload['what_kind_of_property'] ?? null),
        'budget' => $payload['budget_approx_?'] ?? null,
        'possession_status' => $payload['when_to_buy'] ?? null,
        'next_followup_at' => $this->parseMeetingTime($payload['meeting_time_to_discuss_in_details'] ?? null),
        'source' => $this->buildSource($payload),
        'notes' => $this->buildNotes($payload),
    ];
}
```

### 2. Google Sheet Update Service

**File**: `app/Services/GoogleSheetUpdateService.php`

**Methods**:

- `updateStatusColumns($sheetConfigId, $rowNumber, $statusData)`
- `getSheetConfig($sheetConfigId)`
- `authenticateGoogleSheets()`
- `updateCell($sheetId, $range, $value)`

### 3. Event Listeners

**File**: `app/Listeners/UpdateGoogleSheetOnLeadAssigned.php`

```php
public function handle(LeadAssigned $event)
{
    $lead = $event->lead;
    $assignedTo = $event->assignedTo;
    
    // Find sheet assignment
    $assignment = CrmAssignment::where('lead_id', $lead->id)
        ->whereNotNull('sheet_config_id')
        ->whereNotNull('sheet_row_number')
        ->first();
    
    if ($assignment) {
        $this->sheetUpdateService->updateAssignedUser(
            $assignment->sheet_config_id,
            $assignment->sheet_row_number,
            $assignedTo->name
        );
    }
}
```

**File**: `app/Listeners/UpdateGoogleSheetOnCallMade.php`

```php
public function handle(CallLogCreated $event)
{
    $callLog = $event->callLog;
    $lead = $callLog->lead;
    
    // Find sheet assignment
    $assignment = CrmAssignment::where('lead_id', $lead->id)
        ->whereNotNull('sheet_config_id')
        ->whereNotNull('sheet_row_number')
        ->first();
    
    if ($assignment) {
        $this->sheetUpdateService->updateCallInfo(
            $assignment->sheet_config_id,
            $assignment->sheet_row_number,
            $callLog
        );
    }
}
```

### 4. Store Sheet Reference in Webhook

**File**: `app/Http/Controllers/Api/PabblyWebhookController.php`

When creating lead from webhook:

- Accept `sheet_row_number` and `sheet_id` in payload (optional)
- Store in CrmAssignment or Lead
- Use for future sheet updates

### 5. Google Apps Script Updates

**Update**: Google Apps Script code

**New Features**:

- Auto-detect Meta field columns
- Map Meta fields to CRM fields
- Send with sheet_row_number in payload
- Update CRM status columns after response
- Handle errors and update error column

## Files to Create/Modify

### New Files:

1. `app/Services/GoogleSheetUpdateService.php` - Service for updating Google Sheets
2. `app/Listeners/UpdateGoogleSheetOnLeadAssigned.php` - Update assigned user
3. `app/Listeners/UpdateGoogleSheetOnCallMade.php` - Update call info
4. `app/Events/CallLogCreated.php` - Event when call is made (if not exists)

### Modified Files:

1. `app/Http/Controllers/Api/PabblyWebhookController.php` - Add Meta field mapping, store sheet reference
2. `app/Services/GoogleSheetsService.php` - Add methods to update sheet rows
3. `app/Providers/EventServiceProvider.php` - Register new listeners
4. `GOOGLE_SHEETS_AUTO_SYNC_SETUP.md` - Update with Meta field mapping

## Google Sheet Column Structure

### Meta Fields (Existing):

- All Meta fields as provided

### CRM Status Columns (Add):

- `crm_sent_status` - "Sent to CRM" / "Error: ..."
- `crm_lead_id` - Lead ID from CRM
- `crm_assigned_user` - User name who is assigned
- `crm_call_date` - Date/time when called
- `crm_call_status` - "Called" / "Pending"

## Testing

1. Add Meta lead to Google Sheet
2. Verify lead sent to CRM
3. Check `crm_sent_status` and `crm_lead_id` updated
4. Assign lead to user
5. Check `crm_assigned_user` updated
6. User calls lead
7. Check `crm_call_date` and `crm_call_status` updated

## Error Handling

- If webhook fails: Update `crm_sent_status` with error message
- If sheet update fails: Log error, don't break CRM flow
- If column missing: Skip that column, update others
- If authentication fails: Log error, retry later

