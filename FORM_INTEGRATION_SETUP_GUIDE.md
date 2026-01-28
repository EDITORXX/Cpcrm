# Form Integration Setup Guide

## Overview

This guide will help you set up form integration for Meta/Facebook, Google Forms, or any custom form via Google Sheets. The system automatically syncs leads from Google Sheets to CRM and updates the sheet with CRM status.

## Quick Start (5 Minutes)

1. Go to **Admin → Integrations → Form Integration**
2. Click **"Add New Integration"**
3. Follow the 6-step wizard
4. Copy and paste the Google Apps Script
5. Test the integration

## Step-by-Step Guide

### Step 1: Select Sheet Type

Choose the type of form you're integrating:

- **Meta/Facebook Sheet**: Pre-configured mappings for Meta lead forms
- **Google Forms Sheet**: Pre-configured mappings for Google Forms
- **Custom Sheet**: Manual mapping for any custom form

**Recommendation**: If you're using Meta/Facebook, select "Meta/Facebook Sheet" for automatic field mapping.

### Step 2: Google Sheet Configuration

1. **Google Sheet URL or ID**: 
   - Paste your full Google Sheet URL, or
   - Just the Sheet ID (extracted automatically)

2. **Sheet Name (Tab Name)**: 
   - Usually "Sheet1" or the name of your tab

3. **Authentication**:
   - **API Key**: For public sheets (anyone with link can view)
   - **Service Account JSON**: For private sheets (recommended)

4. Click **"Auto-Detect Columns"** to fetch column headers from your sheet

### Step 3: Field Mapping

Map your sheet columns to CRM fields:

**Required Fields**:
- `name` - Customer name
- `phone` - Phone number

**Optional Fields**:
- `email` - Email address
- `city` - City
- `state` - State
- `property_type` - Property type
- `budget` - Budget
- `requirements` - Requirements/Notes
- `notes` - Additional notes

**For Meta/Facebook Sheets**: Click **"Use Meta Template"** to auto-fill all mappings.

**For Custom Sheets**: Manually map each column to the appropriate CRM field.

### Step 4: CRM Status Columns

Map columns where CRM will update status:

- **CRM Sent Status**: Whether lead was sent to CRM (e.g., Column AA)
- **CRM Lead ID**: Lead ID from CRM (e.g., Column AB)
- **CRM Assigned User**: Assigned user name (e.g., Column AC)
- **CRM Call Date**: Call date/time (e.g., Column AD)
- **CRM Call Status**: Call status (e.g., Column AE)

**Note**: If these columns don't exist in your sheet, add them at the end. The system will update them automatically.

### Step 5: Google Apps Script Setup

1. Click **"Generate Script"** to create the Google Apps Script code
2. Copy the generated script
3. Go to your Google Sheet → **Extensions → Apps Script**
4. Paste the script
5. Click **"Save"** (Ctrl+S or Cmd+S)
6. Run the **`setupTrigger()`** function:
   - Click on the function dropdown
   - Select `setupTrigger`
   - Click **"Run"**
7. Authorize permissions when prompted
8. Click **"Complete Setup"**

### Step 6: Test Integration

1. Click **"1-Click Test"** button
2. System will send a test lead to CRM
3. Check the result:
   - **Success**: Test lead created in CRM
   - **Error**: Check error message and fix issues
4. Verify in Google Sheet: CRM status columns should update
5. Click **"Go to Integrations"** to finish

## Meta/Facebook Leads Setup

### Pre-Configured Field Mappings

When you select "Meta/Facebook Sheet", these fields are automatically mapped:

- `full_name` → `name`
- `phone_number` → `phone`
- `whatsapp_number` → stored in notes
- `email` → `email`
- `are_u_from_lucknow_??` → `city`
- `great_,_what_are_you_looking_for` → `requirements`
- `purpose_for_purchase` → `use_end_use`
- `what_kind_of_property` → `property_type`
- `budget_approx_?` → `budget`
- `when_to_buy` → `possession_status`
- `meeting_time_to_discuss_in_details` → stored in notes
- `job_title` → stored in notes
- Meta metadata (id, created_time, ad_name, campaign_name) → stored in notes

### Setup Process

1. Connect Meta Lead Ads to Google Sheet (via Zapier, Pabbly, or Meta's native integration)
2. Ensure your Google Sheet has all Meta fields
3. Add CRM status columns at the end (AA, AB, AC, AD, AE)
4. Follow the 6-step wizard
5. Select "Meta/Facebook Sheet" type
6. Use the template to auto-fill mappings
7. Complete setup

## Google Forms Setup

### Pre-Configured Field Mappings

When you select "Google Forms Sheet", these fields are automatically mapped:

- `Name` → `name`
- `Email` → `email`
- `Phone` → `phone`
- `Mobile` → `phone`

### Setup Process

1. Create Google Form and link to Google Sheet
2. Add CRM status columns at the end
3. Follow the 6-step wizard
4. Select "Google Forms Sheet" type
5. Use the template to auto-fill mappings
6. Complete setup

## Custom Form Setup

### Manual Mapping

1. Follow the 6-step wizard
2. Select "Custom Sheet" type
3. In Step 3, manually map each column:
   - Select sheet column from dropdown
   - Select CRM field from dropdown
   - Mark as required if needed
4. Complete setup

## How It Works

### Data Flow

```
Form → Google Sheet → Google Apps Script → CRM API → CRM
                                                      ↓
Google Sheet ← Google Sheet Update Service ← CRM Events
```

1. **Form to Sheet**: Your form (Meta, Google Forms, etc.) sends leads to Google Sheet
2. **Sheet to CRM**: Google Apps Script detects new rows and sends to CRM API
3. **CRM Processing**: CRM creates lead, assigns user, processes lead
4. **CRM to Sheet**: CRM updates Google Sheet with status, assigned user, call info

### Automatic Updates

The system automatically updates Google Sheet when:

- **Lead Created**: Updates "CRM Sent Status" and "CRM Lead ID"
- **Lead Assigned**: Updates "CRM Assigned User"
- **Call Made**: Updates "CRM Call Date" and "CRM Call Status"

## Troubleshooting

### Test Fails

1. Check Google Sheet URL/ID is correct
2. Verify sheet name (tab name) matches
3. Check authentication (API Key or Service Account)
4. Ensure required fields (name, phone) are mapped
5. Check CRM logs for errors

### Leads Not Syncing

1. Verify Google Apps Script is installed
2. Check trigger is set up (run `setupTrigger()`)
3. Verify API endpoint URL in script
4. Check sheet permissions
5. Review Apps Script execution logs

### Status Not Updating in Sheet

1. Verify CRM status columns are mapped correctly
2. Check column letters match your sheet
3. Verify sheet permissions for service account
4. Check CRM logs for update errors

### Duplicate Leads

1. System checks for duplicates by phone number
2. If duplicate found, sheet is updated but no new lead created
3. Check "CRM Sent Status" column for "Lead already exists" message

## Future Forms

To add a new form type:

1. Go to **Integrations → Form Integration**
2. Click **"Add New Integration"**
3. Select **"Custom Sheet"**
4. Follow the wizard and map fields manually
5. Save configuration

**Tip**: After mapping, you can save it as a template for future use (feature coming soon).

## Support

For issues or questions:
1. Check this guide
2. Review CRM logs: `storage/logs/laravel.log`
3. Check Google Apps Script execution logs
4. Contact support

## API Endpoint

The Google Apps Script sends data to:
```
POST /api/google-sheets/leads
```

**Request Format**:
```json
{
  "sheet_id": "abc123",
  "sheet_row_number": 2,
  "sheet_type": "meta_facebook",
  "name": "John Doe",
  "phone": "9876543210",
  ...
}
```

**Response Format**:
```json
{
  "status": "ok",
  "message": "Lead created successfully",
  "lead_id": 123,
  "assigned_to": {
    "id": 5,
    "name": "John Doe"
  }
}
```

## Best Practices

1. **Use Service Account**: For private sheets, use Service Account JSON (more secure)
2. **Add Status Columns**: Always add CRM status columns at the end of your sheet
3. **Test First**: Always test integration before going live
4. **Monitor Logs**: Regularly check CRM and Apps Script logs
5. **Backup Data**: Keep backup of your Google Sheet
6. **Update Mappings**: If form fields change, update mappings in Step 3

---

**Last Updated**: January 2026
