---
name: Advanced Google Sheets Lead Import System
overview: Complete Google Sheets integration with Service Account JWT authentication, incremental sync, two-way sync, duplicate detection. Auto-assignment settings are managed in Automation section via existing assignment rules.
todos: []
---

# Advanced Google Sheets Lead Import System

## Overview

Complete Google Sheets integration system with Service Account JWT authentication, incremental row tracking, two-way synchronization, duplicate detection, and comprehensive management features. **Auto-assignment settings are managed in Automation section** - Google Sheets config only links to existing assignment rules.

## Database Structure

### Table 1: google_sheets_config

Create migration: `database/migrations/YYYY_MM_DD_create_google_sheets_config_table.php`**Columns:**

- `id` - Primary key
- `sheet_id` - VARCHAR(255) - Google Sheet ID (extracted from URL)
- `sheet_name` - VARCHAR(255) - Tab name (default: 'Sheet1')
- `api_key` - VARCHAR(255) NULL - For public sheets
- `refresh_token` - TEXT NULL - For OAuth (if needed later)
- `service_account_json_path` - VARCHAR(500) NULL - Path to service account JSON file
- `range` - VARCHAR(50) - Data range (default: 'A:Z')
- `name_column` - VARCHAR(10) - Column letter for name (default: 'A')
- `phone_column` - VARCHAR(10) - Column letter for phone (default: 'B')
- `notes_column` - VARCHAR(10) NULL - Column for notes
- `status_column` - VARCHAR(10) - Column for status sync back (default: 'C')
- `notes_column_sync` - VARCHAR(10) - Column for notes sync back (default: 'D')
- `last_sync_at` - DATETIME NULL
- `last_synced_row` - INT - Track last imported row (default: 0)
- `auto_sync_enabled` - BOOLEAN (default: true)
- `sync_interval_minutes` - INT - Minutes between syncs (default: 15)
- `assignment_rule_id` - Foreign key to assignment_rules (NULL for manual assignment)
- `is_active` - BOOLEAN (default: true)
- `completion_notification_sent` - BOOLEAN (default: false)
- `created_by` - Foreign key to users
- `created_at`, `updated_at` - Timestamps

**Indexes:**

- `created_by`, `is_active`, `auto_sync_enabled`
- `assignment_rule_id` - For linking to assignment rules
- `last_sync_at` for scheduler queries

**Note:** Auto-assignment is managed in Automation section via `assignment_rules` table. Google Sheets config only links to an existing assignment rule.

### Table 2: Update lead_assignments

Create migration: `database/migrations/YYYY_MM_DD_add_sheet_fields_to_lead_assignments_table.php`**Add Columns:**

- `sheet_config_id` - Foreign key to google_sheets_config (SET NULL on delete)
- `sheet_row_number` - INT NULL - Row number in sheet for two-way sync

**Index:**

- `sheet_config_id`, `sheet_row_number` for sync lookups

## Auto-Assignment Integration

### Using Existing Assignment Rules

Google Sheets import will use existing `AssignmentRule` system from Automation section:

1. **Manual Assignment (default):**

- If `assignment_rule_id` is NULL → Lead created without assignment
- CRM manually assigns later

2. **Using Assignment Rule:**

- If `assignment_rule_id` is set → Use existing `LeadAssignmentService::assignLead()`
- Supports existing rule types:
    - `specific_user` - Assign to specific user
    - `percentage` - Percentage-based distribution (uses existing `assignment_rule_users` table)

**Implementation in GoogleSheetsService:**

```php
// After creating lead
if ($config->assignment_rule_id) {
    $assignmentService->assignLead($lead, $config->assignment_rule_id, $config->created_by);
}
```

**No separate assignment config needed** - All assignment settings are managed in Automation section.

## API Endpoints

### Controller Methods

#### A. GET getGoogleSheetsConfig

**Route:** `GET /lead-import/google-sheets/config?config_id={id}`**Response:**

```json
{
  "success": true,
  "config": {
    "id": 1,
    "sheet_id": "...",
    "sheet_name": "Sheet1",
    "range": "A:Z",
    "name_column": "A",
    "phone_column": "B",
    "notes_column": "C",
    "status_column": "D",
    "notes_column_sync": "E",
    "auto_sync_enabled": true,
    "sync_interval_minutes": 15,
    "assignment_rule_id": 1,
    "assignment_rule": {
      "id": 1,
      "name": "Sales Team Distribution",
      "type": "percentage"
    }
  }
}
```



#### B. POST saveGoogleSheetsConfig

**Route:** `POST /lead-import/google-sheets/config`**Request Body:**

```json
{
  "config_id": null,
  "sheet_id": "1abc...",
  "sheet_name": "Sheet1",
  "api_key": "...",
  "service_account_json_path": "google-service-account.json",
  "range": "A:Z",
  "name_column": "A",
  "phone_column": "B",
  "notes_column": "C",
  "status_column": "D",
  "notes_column_sync": "E",
  "auto_sync_enabled": true,
  "sync_interval_minutes": 15,
  "assignment_rule_id": 1  // NULL for manual assignment
}
```

**Validation:**

- `sheet_id` required
- `assignment_rule_id` must exist in `assignment_rules` table if provided
- Extract `sheet_id` from URL if full URL provided
- Validate column letters (A-Z, single character)

## UI Components

### A. Google Sheets Configuration Page

**View:** `resources/views/lead-import/google-sheets-config.blade.php`**Form Fields:**

- Google Sheet ID/URL (with validation and URL extraction)
- Sheet Name (tab name, default: Sheet1)
- API Key (optional, for public sheets)
- Service Account JSON Path (optional, for private sheets)
- Range (default: A:Z)
- Name Column (default: A, dropdown A-Z)
- Phone Column (default: B, dropdown A-Z)
- Notes Column (optional, dropdown A-Z)
- Status Column (default: C, for sync back, dropdown A-Z)
- Notes Sync Column (default: D, for sync back, dropdown A-Z)
- Auto-sync checkbox
- Sync Interval (minutes, default: 15, min: 1)
- **Assignment Rule dropdown** - Select from existing assignment rules (or "Manual" for no auto-assignment)
- Shows all active assignment rules from Automation section
- Link to "Create New Rule" in Automation section

**Note:** Assignment rule configuration is done in Automation section, not here.

### B. Automation Section Updates

**View:** `resources/views/crm/automation/rules.blade.php`**Add:**

- Show which Google Sheets configs are using each assignment rule
- Display count: "Used by X Google Sheets imports"
- Link to Google Sheets configs using this rule

## Implementation Notes

1. **Assignment Rules Management:**

- All assignment rule creation/editing stays in Automation section
- Google Sheets config only references existing rules
- No duplicate assignment logic needed

2. **Service Integration:**

- Use existing `LeadAssignmentService::assignLead()` method
- No need for separate assignment service for Google Sheets
- Leverage existing percentage-based and specific-user assignment

3. **UI Flow:**

- User creates assignment rule in Automation section first
- Then selects that rule when configuring Google Sheets import
- Clear separation: Automation = assignment logic, Lead Import = data import

## Removed Components

- ❌ `sheet_assignment_config` table (use existing `assignment_rule_users`)
- ❌ Assignment method fields from `google_sheets_config` (use `assignment_rule_id`)
- ❌ Assignment configuration UI in Google Sheets form (moved to Automation)
- ❌ Separate assignment service methods (use existing `LeadAssignmentService`)

## Updated Implementation Steps

1. **Database Migrations**

- Create `google_sheets_config` table (without assignment fields)
- Update `lead_assignments` table (add sheet tracking fields)
- ❌ No `sheet_assignment_config` table needed

2. **Models**

- Create `GoogleSheetsConfig` model with `belongsTo(AssignmentRule)`
- Update `LeadAssignment` model
- ❌ No `SheetAssignmentConfig` model needed

3. **Services**

- Create `GoogleSheetsService` with JWT auth
- Use existing `LeadAssignmentService` (no changes needed)
- Create `DuplicateDetectionService`

4. **Controllers**

- Create `LeadImportController` with Google Sheets methods
- Use existing assign
- Use existing assignmement service when `assignment_rule_id` is set

5. **Views**

- Create Google Sheets config form (with assignment rule dropdown)
- Update Automation rules page to show Google Sheets usage