# Google Sheets से Automatic Lead Sync - Complete Setup Guide

## Overview

यह guide आपको बताएगा कि Google Sheets में नया lead add होते ही automatically CRM में कैसे send होगा।

---

## Step 1: Google Sheet तैयार करें

### 1.1 Sheet Structure

आपका Google Sheet कुछ इस तरह होना चाहिए:

| A (Name) | B (Phone) | C (Email) | D (City) | E (State) | F (Property Type) | G (Budget Min) | H (Budget Max) | I (Requirements) | J (Notes) | K (Status) |
|----------|-----------|-----------|----------|-----------|-------------------|----------------|----------------|------------------|-----------|------------|
| John Doe | 9876543210 | john@example.com | Mumbai | Maharashtra | apartment | 5000000 | 10000000 | 2BHK needed | - | - |

**Important:**
- Row 1 में headers होने चाहिए
- Row 2 से data start होगा
- Column K (Status) automatically fill होगा (Sent to CRM / Error)

### 1.2 Column Headers Setup

आपके sheet में ये columns होने चाहिए (कम से कम):

**Required:**
- Name (Column A)
- Phone (Column B)

**Optional:**
- Email (Column C)
- City (Column D)
- State (Column E)
- Property Type (Column F)
- Budget Min (Column G)
- Budget Max (Column H)
- Requirements (Column I)
- Notes (Column J)
- Status (Column K) - यह automatically fill होगा

---

## Step 2: Google Apps Script Add करें

### 2.1 Script Editor Open करें

1. Google Sheet open करें
2. Menu में **Extensions** → **Apps Script** click करें
3. नया tab open होगा (Script Editor)

### 2.2 Code Paste करें

Script Editor में default code delete करें और यह complete code paste करें:

```javascript
// ============================================
// CRM Webhook Configuration
// ============================================
// अपना domain यहाँ डालें (example: https://crm.yourcompany.com)
const CRM_WEBHOOK_URL = 'https://your-domain.com/api/pabbly/webhook';

// Optional: Security के लिए webhook secret (अगर CRM में set किया है)
const WEBHOOK_SECRET = ''; // Leave empty if not using

// ============================================
// Column Mapping - अपने sheet के columns के अनुसार adjust करें
// ============================================
const COLUMN_MAPPING = {
  name: 'A',           // Column A = Name
  phone: 'B',          // Column B = Phone
  email: 'C',         // Column C = Email
  city: 'D',          // Column D = City
  state: 'E',         // Column E = State
  property_type: 'F', // Column F = Property Type
  budget_min: 'G',    // Column G = Budget Min
  budget_max: 'H',    // Column H = Budget Max
  requirements: 'I',  // Column I = Requirements
  notes: 'J'          // Column J = Notes
};

// Status column (automatically filled)
const STATUS_COLUMN = 'K';

// ============================================
// Main Function - जब sheet edit हो
// ============================================
function onEdit(e) {
  try {
    const sheet = e.source.getActiveSheet();
    const range = e.range;
    const row = range.getRow();
    const lastRow = sheet.getLastRow();
    
    // Only process if it's a new row (last row) and not header
    if (row === lastRow && row > 1) {
      // Wait a moment for data to be fully entered
      Utilities.sleep(1500);
      
      // Get the new row data
      const rowData = getRowData(sheet, row);
      
      // Check if already sent
      const statusCell = sheet.getRange(STATUS_COLUMN + row).getValue();
      if (statusCell && statusCell.includes('Sent to CRM')) {
        return; // Already sent, skip
      }
      
      // Validate required fields
      if (rowData.name && rowData.phone) {
        // Send to CRM
        const result = sendToCRM(rowData);
        
        // Update status
        updateStatus(sheet, row, result);
      } else {
        // Missing required fields
        sheet.getRange(STATUS_COLUMN + row).setValue('Missing Name/Phone');
        sheet.getRange(STATUS_COLUMN + row).setBackground('#fee2e2'); // Red
      }
    }
  } catch (error) {
    Logger.log('Error in onEdit: ' + error.toString());
  }
}

// ============================================
// Alternative: Time-based check (every minute)
// ============================================
function checkForNewLeads() {
  try {
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    const lastRow = sheet.getLastRow();
    
    if (lastRow < 2) return; // No data rows
    
    // Check all rows (skip header)
    for (let row = 2; row <= lastRow; row++) {
      const statusCell = sheet.getRange(STATUS_COLUMN + row).getValue();
      
      // Only process if not already sent
      if (!statusCell || !statusCell.includes('Sent to CRM')) {
        const rowData = getRowData(sheet, row);
        
        if (rowData.name && rowData.phone) {
          const result = sendToCRM(rowData);
          updateStatus(sheet, row, result);
        }
      }
    }
  } catch (error) {
    Logger.log('Error in checkForNewLeads: ' + error.toString());
  }
}

// ============================================
// Get row data based on column mapping
// ============================================
function getRowData(sheet, row) {
  const data = {};
  
  for (const [field, column] of Object.entries(COLUMN_MAPPING)) {
    try {
      const cellValue = sheet.getRange(column + row).getValue();
      if (cellValue && cellValue.toString().trim() !== '') {
        data[field] = cellValue.toString().trim();
      }
    } catch (error) {
      Logger.log('Error reading column ' + column + ': ' + error.toString());
    }
  }
  
  return data;
}

// ============================================
// Send data to CRM via webhook
// ============================================
function sendToCRM(data) {
  try {
    // Prepare payload
    const payload = {
      name: data.name || '',
      phone: data.phone || '',
      email: data.email || '',
      city: data.city || '',
      state: data.state || '',
      property_type: data.property_type || '',
      budget_min: data.budget_min ? parseFloat(data.budget_min.toString().replace(/[^0-9.]/g, '')) : null,
      budget_max: data.budget_max ? parseFloat(data.budget_max.toString().replace(/[^0-9.]/g, '')) : null,
      requirements: data.requirements || '',
      notes: data.notes || '',
      source: 'google_sheets'
    };
    
    // Prepare options for HTTP request
    const options = {
      method: 'post',
      contentType: 'application/json',
      payload: JSON.stringify(payload),
      muteHttpExceptions: true
    };
    
    // Add webhook secret if configured
    if (WEBHOOK_SECRET && WEBHOOK_SECRET !== '') {
      options.headers = {
        'X-Pabbly-Secret': WEBHOOK_SECRET
      };
    }
    
    // Send request
    const response = UrlFetchApp.fetch(CRM_WEBHOOK_URL, options);
    const responseCode = response.getResponseCode();
    const responseText = response.getContentText();
    
    if (responseCode === 200 || responseCode === 201) {
      const result = JSON.parse(responseText);
      return {
        success: true,
        message: 'Lead sent successfully',
        leadId: result.lead_id || null
      };
    } else {
      return {
        success: false,
        message: 'HTTP ' + responseCode + ': ' + responseText.substring(0, 100)
      };
    }
    
  } catch (error) {
    return {
      success: false,
      message: error.toString()
    };
  }
}

// ============================================
// Update status in sheet
// ============================================
function updateStatus(sheet, row, result) {
  const statusCell = sheet.getRange(STATUS_COLUMN + row);
  
  if (result.success) {
    const statusText = 'Sent to CRM' + (result.leadId ? ' (ID: ' + result.leadId + ')' : '');
    statusCell.setValue(statusText);
    statusCell.setBackground('#d1fae5'); // Green
    statusCell.setFontColor('#065f46'); // Dark green text
  } else {
    statusCell.setValue('Error: ' + result.message);
    statusCell.setBackground('#fee2e2'); // Red
    statusCell.setFontColor('#991b1b'); // Dark red text
  }
}

// ============================================
// Helper function to get column index
// ============================================
function getColumnIndex(columnLetter) {
  let result = 0;
  for (let i = 0; i < columnLetter.length; i++) {
    result = result * 26 + (columnLetter.charCodeAt(i) - 'A'.charCodeAt(0) + 1);
  }
  return result;
}

// ============================================
// Setup function - Run once to create triggers
// ============================================
function setupTrigger() {
  try {
    // Delete existing triggers
    const triggers = ScriptApp.getProjectTriggers();
    triggers.forEach(trigger => {
      if (trigger.getHandlerFunction() === 'onEdit' || trigger.getHandlerFunction() === 'checkForNewLeads') {
        ScriptApp.deleteTrigger(trigger);
      }
    });
    
    // Create onEdit trigger (fires when sheet is edited)
    ScriptApp.newTrigger('onEdit')
      .forSpreadsheet(SpreadsheetApp.getActiveSpreadsheet())
      .onEdit()
      .create();
    
    // Create time-based trigger (checks every minute for missed rows)
    ScriptApp.newTrigger('checkForNewLeads')
      .timeBased()
      .everyMinutes(1)
      .create();
    
    Logger.log('✅ Triggers setup successfully!');
    return 'Triggers setup successfully!';
  } catch (error) {
    Logger.log('❌ Error setting up triggers: ' + error.toString());
    return 'Error: ' + error.toString();
  }
}

// ============================================
// Test function - Manual test
// ============================================
function testSend() {
  const testData = {
    name: 'Test Lead ' + new Date().getTime(),
    phone: '9876543210',
    email: 'test@example.com',
    city: 'Mumbai',
    state: 'Maharashtra',
    property_type: 'apartment',
    budget_min: 5000000,
    budget_max: 10000000,
    requirements: 'Looking for 2BHK',
    notes: 'Test from Google Apps Script'
  };
  
  const result = sendToCRM(testData);
  Logger.log('Test Result: ' + JSON.stringify(result));
  return result;
}
```

### 2.3 Configuration Update करें

Code में ये 2 lines update करें:

```javascript
// Line 5: अपना CRM domain डालें
const CRM_WEBHOOK_URL = 'https://your-domain.com/api/pabbly/webhook';

// Example:
// const CRM_WEBHOOK_URL = 'https://crm.yourcompany.com/api/pabbly/webhook';
// या local testing के लिए:
// const CRM_WEBHOOK_URL = 'https://xxxx-xxxx.trycloudflare.com/api/pabbly/webhook';
```

**Column Mapping** (अगर आपके columns different हैं):

```javascript
// अगर आपके sheet में columns different हैं, तो यहाँ adjust करें
const COLUMN_MAPPING = {
  name: 'A',        // Name किस column में है?
  phone: 'B',       // Phone किस column में है?
  email: 'C',       // Email किस column में है?
  // ... बाकी columns
};
```

### 2.4 Save करें

1. **File** → **Save** (या Ctrl+S)
2. Project name दें: "CRM Auto Sync" (या कोई भी name)

---

## Step 3: Authorization & Trigger Setup

### 3.1 First Time Authorization

1. Script Editor में **Run** → **setupTrigger** select करें
2. **Authorization Required** popup आएगा
3. **Review Permissions** click करें
4. Google account select करें
5. **Advanced** → **Go to [Project Name] (unsafe)** click करें
6. **Allow** click करें
7. Script run होगा और trigger create हो जाएगा

### 3.2 Verify Trigger

1. **Triggers** icon (clock) click करें
2. 2 triggers दिखने चाहिए:
   - `onEdit` - On edit event
   - `checkForNewLeads` - Every 1 minute

---

## Step 4: CRM में Webhook Enable करें

### 4.1 Admin Panel में

1. **Admin Login** करें
2. **Integrations** → **Pabbly** पर जाएं
3. **Enable Integration** toggle ON करें
4. **Webhook Secret** (optional) - अगर security चाहिए
5. **Save** करें

### 4.2 Webhook URL Note करें

Webhook URL होगी:
```
https://your-domain.com/api/pabbly/webhook
```

यही URL Google Apps Script में use करें।

---

## Step 5: Test करें

### 5.1 Manual Test

1. Script Editor में **Run** → **testSend** select करें
2. **Authorization** allow करें (first time)
3. **View** → **Logs** में result check करें
4. CRM में lead check करें

### 5.2 Real Test

1. Google Sheet में नया row add करें:
   - Name: "Test Lead"
   - Phone: "9876543210"
   - Email: "test@example.com"
2. Row add करते ही script automatically run होगा
3. Column K (Status) में check करें:
   - ✅ "Sent to CRM (ID: 123)" = Success
   - ❌ "Error: ..." = Failed
4. CRM में lead check करें

---

## Step 6: Use करना

### 6.1 Normal Use

1. Google Sheet में नया lead add करें
2. Name और Phone fill करें (required)
3. बाकी fields optional हैं
4. Script automatically CRM में send कर देगा
5. Column K में status दिखेगा

### 6.2 Status Column

Column K में status दिखेगा:
- ✅ **Green** = "Sent to CRM (ID: 123)" = Success
- ❌ **Red** = "Error: ..." = Failed
- **Empty** = Not processed yet

---

## Troubleshooting

### Issue: Script not running
**Solution:**
1. Triggers check करें (Triggers icon)
2. Authorization check करें
3. Script errors check करें (View → Logs)

### Issue: Data not sending
**Solution:**
1. Webhook URL check करें (correct domain?)
2. CRM webhook enable है या नहीं check करें
3. Logs check करें (View → Logs)
4. Test function run करें

### Issue: Wrong column mapping
**Solution:**
1. Column mapping adjust करें
2. Sheet structure check करें
3. Column letters correct हैं या नहीं check करें

### Issue: Authorization error
**Solution:**
1. Triggers delete करें
2. `setupTrigger()` function फिर से run करें
3. Authorization allow करें

---

## Important Notes

1. **Row 1 = Headers** - Data Row 2 से start होगा
2. **Name & Phone Required** - ये 2 fields जरूरी हैं
3. **Status Column** - Column K automatically fill होगा
4. **Real-time** - नया row add होते ही send होगा
5. **Backup** - Time-based trigger भी चल रहा है (every minute)

---

## Summary - आपको क्या करना है:

### Google Sheet में:
1. ✅ Sheet structure तैयार करें (Columns A-J)
2. ✅ Row 1 में headers add करें
3. ✅ Column K (Status) खाली रखें (automatically fill होगा)

### Google Apps Script में:
1. ✅ Extensions → Apps Script
2. ✅ Code paste करें
3. ✅ Webhook URL update करें
4. ✅ Column mapping adjust करें (अगर needed)
5. ✅ Save करें
6. ✅ `setupTrigger()` run करें
7. ✅ Authorization allow करें

### CRM में:
1. ✅ Admin → Integrations → Pabbly
2. ✅ Enable Integration
3. ✅ Save करें

### Test:
1. ✅ Google Sheet में test lead add करें
2. ✅ Status column check करें
3. ✅ CRM में lead check करें

---

**बस इतना ही! अब automatic sync काम करेगा! 🚀**
