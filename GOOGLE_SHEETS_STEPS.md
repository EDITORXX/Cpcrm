# Google Sheets में क्या करना है - Simple Steps

## 📋 Step-by-Step Guide (हिंदी में)

---

## Step 1: Google Sheet तैयार करें

### 1.1 Sheet Structure

आपका Google Sheet कुछ इस तरह होना चाहिए:

```
Row 1 (Headers):
┌─────────────┬──────────────┬─────────────┬──────────┬──────────┬─────────────────┬─────────────┬─────────────┬───────────────┬──────────┬──────────┐
│ Name (A)    │ Phone (B)    │ Email (C)   │ City (D) │ State (E)│ Property Type(F)│ Budget Min(G)│ Budget Max(H)│ Requirements(I)│ Notes (J)│ Status(K)│
├─────────────┼──────────────┼─────────────┼──────────┼──────────┼─────────────────┼─────────────┼─────────────┼───────────────┼──────────┼──────────┤
│ John Doe    │ 9876543210   │ john@...    │ Mumbai   │ MH       │ apartment       │ 5000000     │ 10000000    │ 2BHK needed   │ -        │          │
│ Jane Smith  │ 9876543211   │ jane@...    │ Delhi    │ DL       │ villa           │ 10000000    │ 20000000    │ 3BHK          │ -        │          │
└─────────────┴──────────────┴─────────────┴──────────┴──────────┴─────────────────┴─────────────┴─────────────┴───────────────┴──────────┴──────────┘
```

**Important Points:**
- ✅ Row 1 में headers होने चाहिए
- ✅ Row 2 से data start होगा
- ✅ Column K (Status) खाली रखें - यह automatically fill होगा
- ✅ **Name (Column A)** और **Phone (Column B)** जरूरी हैं
- ✅ बाकी columns optional हैं

### 1.2 Minimum Required Columns

कम से कम ये columns होने चाहिए:

| Column | Header | Required | Description |
|--------|--------|----------|-------------|
| A | Name | ✅ Yes | Customer Name |
| B | Phone | ✅ Yes | Phone Number |
| K | Status | Auto | Status (automatically filled) |

**Optional Columns (अगर चाहिए):**
- C = Email
- D = City
- E = State
- F = Property Type
- G = Budget Min
- H = Budget Max
- I = Requirements
- J = Notes

---

## Step 2: Google Apps Script Add करें

### 2.1 Script Editor Open करें

1. Google Sheet open करें
2. Menu bar में **Extensions** click करें
3. **Apps Script** select करें
4. नया tab open होगा (Script Editor)

### 2.2 Code Paste करें

Script Editor में:
1. Default code (function myFunction...) delete करें
2. `GOOGLE_SHEETS_AUTO_SYNC_SETUP.md` file से complete code copy करें
3. Paste करें

### 2.3 Configuration Update करें

Code में **Line 5** update करें:

```javascript
// यहाँ अपना CRM domain डालें
const CRM_WEBHOOK_URL = 'https://your-domain.com/api/pabbly/webhook';
```

**Example:**
```javascript
// Production:
const CRM_WEBHOOK_URL = 'https://crm.yourcompany.com/api/pabbly/webhook';

// या Testing के लिए (ngrok/Cloudflare):
const CRM_WEBHOOK_URL = 'https://xxxx-xxxx.trycloudflare.com/api/pabbly/webhook';
```

**Column Mapping** (अगर आपके columns different हैं):

अगर आपके sheet में columns different positions में हैं, तो यहाँ adjust करें:

```javascript
const COLUMN_MAPPING = {
  name: 'A',        // Name किस column में है?
  phone: 'B',       // Phone किस column में है?
  email: 'C',       // Email किस column में है?
  // ... adjust करें
};
```

### 2.4 Save करें

1. **File** → **Save** (या Ctrl+S)
2. Project name दें: "CRM Auto Sync"

---

## Step 3: Trigger Setup करें

### 3.1 Setup Function Run करें

1. Script Editor में **Run** dropdown click करें
2. **setupTrigger** select करें
3. **Run** button click करें
4. **Authorization Required** popup आएगा

### 3.2 Authorization Allow करें

1. **Review Permissions** click करें
2. Google account select करें
3. **Advanced** click करें
4. **Go to [Project Name] (unsafe)** click करें
5. **Allow** click करें
6. Script run होगा और trigger create हो जाएगा

### 3.3 Verify Trigger

1. **Triggers** icon (⏰ clock) click करें (left sidebar में)
2. 2 triggers दिखने चाहिए:
   - ✅ `onEdit` - On edit event (when you edit sheet)
   - ✅ `checkForNewLeads` - Every 1 minute (backup check)

---

## Step 4: Test करें

### 4.1 Manual Test (Script Editor में)

1. Script Editor में **Run** → **testSend** select करें
2. **Run** button click करें
3. **View** → **Logs** click करें
4. Result check करें:
   - ✅ Success = "Lead sent successfully"
   - ❌ Error = Error message दिखेगा
5. CRM में lead check करें

### 4.2 Real Test (Google Sheet में)

1. Google Sheet में नया row add करें (Row 2 या नीचे)
2. Fill करें:
   - **Name**: "Test Lead"
   - **Phone**: "9876543210"
   - **Email**: "test@example.com" (optional)
3. Enter press करें या दूसरे cell में click करें
4. **1-2 seconds wait करें**
5. **Column K (Status)** check करें:
   - ✅ **Green** = "Sent to CRM (ID: 123)" = Success! 🎉
   - ❌ **Red** = "Error: ..." = Failed (error check करें)
6. CRM में lead check करें

---

## Step 5: Normal Use

### 5.1 Daily Use

अब आप बस:
1. Google Sheet में नया lead add करें
2. **Name** और **Phone** fill करें (required)
3. बाकी fields optional हैं
4. Script automatically CRM में send कर देगा
5. Column K में status दिखेगा

### 5.2 Status Column (Column K)

Column K में status automatically update होगा:

| Status | Color | Meaning |
|--------|-------|---------|
| "Sent to CRM (ID: 123)" | 🟢 Green | Success - Lead CRM में create हो गया |
| "Error: HTTP 404" | 🔴 Red | Failed - Error check करें |
| "Missing Name/Phone" | 🔴 Red | Required fields missing |
| (Empty) | - | Not processed yet |

---

## Step 6: CRM में Enable करें

### 6.1 Admin Panel

1. **Admin Login** करें
2. **Integrations** menu click करें
3. **Pabbly** click करें
4. **Enable Integration** toggle ON करें
5. **Webhook Secret** (optional) - अगर security चाहिए
6. **Save** button click करें

### 6.2 Webhook URL Note करें

Webhook URL होगी:
```
https://your-domain.com/api/pabbly/webhook
```

यही URL Google Apps Script में use करें (Line 5 में)।

---

## Troubleshooting

### ❌ Issue: Script not running

**Check करें:**
1. Triggers exist करते हैं या नहीं (Triggers icon)
2. Authorization allow किया है या नहीं
3. Script errors (View → Logs)

**Fix:**
- `setupTrigger()` function फिर से run करें
- Authorization allow करें

---

### ❌ Issue: Data not sending

**Check करें:**
1. Webhook URL correct है या नहीं
2. CRM webhook enable है या नहीं
3. Internet connection
4. Logs में errors (View → Logs)

**Fix:**
- Webhook URL verify करें
- CRM में webhook enable करें
- Test function run करें

---

### ❌ Issue: Wrong column mapping

**Check करें:**
1. Column letters correct हैं या नहीं
2. Sheet structure match कर रहा है या नहीं

**Fix:**
- Column mapping adjust करें
- Sheet structure check करें

---

### ❌ Issue: Status column not updating

**Check करें:**
1. Column K exists करता है या नहीं
2. Script running है या नहीं

**Fix:**
- Column K add करें (अगर missing है)
- Script run करें

---

## Quick Checklist

### Google Sheet Setup:
- [ ] Row 1 में headers add किए
- [ ] Column A = Name
- [ ] Column B = Phone
- [ ] Column K = Status (खाली रखा)

### Google Apps Script:
- [ ] Script Editor open किया
- [ ] Code paste किया
- [ ] Webhook URL update किया
- [ ] Column mapping adjust किया (अगर needed)
- [ ] Save किया
- [ ] `setupTrigger()` run किया
- [ ] Authorization allow किया
- [ ] Triggers verify किए

### CRM Setup:
- [ ] Admin → Integrations → Pabbly
- [ ] Enable Integration ON किया
- [ ] Save किया

### Testing:
- [ ] Test function run किया
- [ ] Real test (Google Sheet में lead add किया)
- [ ] Status column check किया
- [ ] CRM में lead verify किया

---

## Example - Complete Flow

### 1. Google Sheet में Lead Add करें:

```
Row 3 में:
Name: "Rajesh Kumar"
Phone: "9876543210"
Email: "rajesh@example.com"
City: "Mumbai"
```

### 2. Script Automatically Run होगा:

- Script detect करेगा कि नया row add हुआ
- Data read करेगा
- CRM webhook को send करेगा

### 3. Status Update होगा:

```
Column K में:
"Sent to CRM (ID: 456)" - Green background
```

### 4. CRM में Lead Create होगा:

- Lead automatically CRM में create हो जाएगा
- Source = "google_sheets"
- Status = "new"

---

## Important Notes

1. ✅ **Row 1 = Headers** - Data Row 2 से start होगा
2. ✅ **Name & Phone Required** - ये 2 fields जरूरी हैं
3. ✅ **Status Column** - Column K automatically fill होगा
4. ✅ **Real-time** - नया row add होते ही send होगा
5. ✅ **Backup** - Time-based trigger भी चल रहा है (every minute)
6. ✅ **No Duplicates** - अगर already sent है, तो skip करेगा

---

## Support

अगर कोई issue आए:
1. Script Editor में **View** → **Logs** check करें
2. Errors read करें
3. Webhook URL verify करें
4. CRM webhook enable है या नहीं check करें

---

**बस इतना ही! अब automatic sync काम करेगा! 🚀**
