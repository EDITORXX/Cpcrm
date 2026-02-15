# Google Sheets Auto Sync - Quick Start (5 Minutes)

## 🚀 Fast Setup

### Step 1: Google Sheet (2 minutes)

1. Google Sheet open करें
2. Row 1 में headers add करें:
   ```
   A = Name | B = Phone | C = Email | ... | K = Status
   ```
3. Column K (Status) खाली रखें

### Step 2: Google Apps Script (2 minutes)

1. **Extensions** → **Apps Script**
2. Code paste करें (नीचे दिया है)
3. **Line 5** में webhook URL update करें:
   ```javascript
   const CRM_WEBHOOK_URL = 'https://your-domain.com/api/pabbly/webhook';
   ```
4. **Save** करें

### Step 3: Trigger Setup (1 minute)

1. **Run** → **setupTrigger**
2. **Authorization** allow करें
3. Done! ✅

---

## 📝 Complete Code (Copy-Paste)

```javascript
// Webhook URL (अपना domain डालें)
const CRM_WEBHOOK_URL = 'https://your-domain.com/api/pabbly/webhook';

// Column Mapping
const COLUMN_MAPPING = {
  name: 'A', phone: 'B', email: 'C', city: 'D', state: 'E',
  property_type: 'F', budget_min: 'G', budget_max: 'H',
  requirements: 'I', notes: 'J'
};
const STATUS_COLUMN = 'K';

// Main function
function onEdit(e) {
  const sheet = e.source.getActiveSheet();
  const row = e.range.getRow();
  const lastRow = sheet.getLastRow();
  
  if (row === lastRow && row > 1) {
    Utilities.sleep(1500);
    const rowData = getRowData(sheet, row);
    const statusCell = sheet.getRange(STATUS_COLUMN + row).getValue();
    
    if (!statusCell || !statusCell.includes('Sent to CRM')) {
      if (rowData.name && rowData.phone) {
        const result = sendToCRM(rowData);
        updateStatus(sheet, row, result);
      }
    }
  }
}

// Get row data
function getRowData(sheet, row) {
  const data = {};
  for (const [field, column] of Object.entries(COLUMN_MAPPING)) {
    const value = sheet.getRange(column + row).getValue();
    if (value) data[field] = value.toString().trim();
  }
  return data;
}

// Send to CRM
function sendToCRM(data) {
  try {
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
    
    const response = UrlFetchApp.fetch(CRM_WEBHOOK_URL, {
      method: 'post',
      contentType: 'application/json',
      payload: JSON.stringify(payload),
      muteHttpExceptions: true
    });
    
    const responseCode = response.getResponseCode();
    if (responseCode === 200 || responseCode === 201) {
      const result = JSON.parse(response.getContentText());
      return { success: true, leadId: result.lead_id || null };
    } else {
      return { success: false, message: 'HTTP ' + responseCode };
    }
  } catch (error) {
    return { success: false, message: error.toString() };
  }
}

// Update status
function updateStatus(sheet, row, result) {
  const statusCell = sheet.getRange(STATUS_COLUMN + row);
  if (result.success) {
    statusCell.setValue('Sent to CRM' + (result.leadId ? ' (ID: ' + result.leadId + ')' : ''));
    statusCell.setBackground('#d1fae5');
  } else {
    statusCell.setValue('Error: ' + result.message);
    statusCell.setBackground('#fee2e2');
  }
}

// Setup trigger
function setupTrigger() {
  ScriptApp.getProjectTriggers().forEach(t => {
    if (t.getHandlerFunction() === 'onEdit') ScriptApp.deleteTrigger(t);
  });
  ScriptApp.newTrigger('onEdit').forSpreadsheet(SpreadsheetApp.getActiveSpreadsheet()).onEdit().create();
  ScriptApp.newTrigger('checkForNewLeads').timeBased().everyMinutes(1).create();
}

// Backup check
function checkForNewLeads() {
  const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  for (let row = 2; row <= sheet.getLastRow(); row++) {
    const status = sheet.getRange(STATUS_COLUMN + row).getValue();
    if (!status || !status.includes('Sent to CRM')) {
      const rowData = getRowData(sheet, row);
      if (rowData.name && rowData.phone) {
        const result = sendToCRM(rowData);
        updateStatus(sheet, row, result);
      }
    }
  }
}
```

---

## ✅ Test करें

1. Google Sheet में नया row add करें
2. Name और Phone fill करें
3. Column K में status check करें:
   - ✅ Green = Success
   - ❌ Red = Error

---

## 📞 CRM में Enable करें

1. Admin → Integrations → Pabbly
2. Enable Integration ON
3. Save

---

**Done! Automatic sync काम करेगा! 🎉**
