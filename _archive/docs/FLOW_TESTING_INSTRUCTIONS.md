# Flow Testing & Validation System - Complete Instructions

## Overview

यह Flow Testing System आपको complete CRM flow को Telecaller से Closer तक test करने की सुविधा देता है। आप हर stage पर errors detect कर सकते हैं और उन्हें fix कर सकते हैं।

## Access Flow Testing Page

1. **Login करें** Admin या CRM user के साथ
2. **Admin Dashboard** पर जाएं
3. **"Flow Testing"** button पर click करें
4. या directly URL पर जाएं: `http://localhost:8007/admin/flow-test`

## Complete Testing Flow

### Step 1: User Selection (User Login)

1. **Role Select करें** - Dropdown से role चुनें (Admin, CRM, Sales Manager, Telecaller, etc.)
2. **User Select करें** - Selected role के users list से user चुनें
3. **"Login As User"** automatically हो जाएगा
4. Current user badge में selected user दिखेगा
5. **"Restore Original User"** button से original user पर वापस आ सकते हैं

### Step 2: Select Stage

1. **Flow Progress Bar** में 11 stages दिखेंगे
2. किसी भी **stage पर click** करें
3. Stage details panel open होगा

### Step 3: Test Stage

1. **"Test Stage"** button पर click करें
2. System automatically validate करेगा:
   - Required data exists या नहीं
   - Status transitions correct हैं या नहीं
   - Relationships properly set हैं या नहीं
   - Permissions correct हैं या नहीं

3. **Results दिखेंगे:**
   - ✅ **Success** - Stage valid है
   - ❌ **Errors** - Critical issues जो fix करने होंगे
   - ⚠️ **Warnings** - Issues जो fix करना optional है
   - ℹ️ **Info** - Additional information
   - 📊 **Data** - Stage related data

### Step 4: Validate Stage

1. **"Validate Stage"** button पर click करें
2. Detailed validation results दिखेंगे
3. सभी conditions check होंगी

### Step 5: Get Stage Data

1. **"Get Stage Data"** button पर click करें
2. Current stage का actual data दिखेगा
3. यह देखने के लिए useful है कि data properly set है या नहीं

## Complete Flow Testing (Step by Step)

### Stage 1: Telecaller Lead Creation
**User:** Telecaller/Sales Executive

1. Telecaller user select करें
2. Stage 1 select करें
3. Test करें
4. **Check:**
   - Lead assigned है या नहीं
   - Task created है या नहीं
   - Lead status correct है या नहीं

**If Errors:**
- Lead create करें और assign करें
- Task manually create करें (if needed)

### Stage 2: Prospect Creation
**User:** Telecaller/Sales Executive

1. Same Telecaller user रखें
2. Stage 2 select करें
3. Test करें
4. **Check:**
   - Prospect created है या नहीं
   - Verification status = 'pending_verification' है या नहीं
   - Manager assigned है या नहीं

**If Errors:**
- Lead को "Interested" mark करें
- Prospect form fill करें
- Prospect create करें

### Stage 3: Manager Verification
**User:** Sales Manager

1. **User switch करें** - Sales Manager select करें
2. Stage 3 select करें
3. Test करें
4. **Check:**
   - Verification task exists है या नहीं
   - Task manager को assigned है या नहीं
   - Prospect pending है या नहीं

**If Errors:**
- Verification task manually create करें (if needed)
- Prospect को manager assign करें

### Stage 4: Meeting Creation
**User:** Sales Manager

1. Same Sales Manager user रखें
2. Stage 4 select करें
3. Test करें
4. **Check:**
   - Meeting created है या नहीं
   - Meeting status correct है या नहीं
   - Required fields filled हैं या नहीं

**If Errors:**
- Meeting create करें
- Meeting complete करें
- Verification status = 'pending' set करें

### Stage 5: Meeting Verification
**User:** CRM/Admin/Sales Head

1. **User switch करें** - CRM या Admin select करें
2. Stage 5 select करें
3. Test करें
4. **Check:**
   - Meeting verification panel में है या नहीं
   - Meeting verify हो सकती है या नहीं
   - Achievement counted है या नहीं

**If Errors:**
- Meeting verify करें
- Achievement count check करें

### Stage 6: Site Visit Creation
**User:** Sales Manager

1. **User switch करें** - Sales Manager पर वापस जाएं
2. Stage 6 select करें
3. Test करें
4. **Check:**
   - Site visit created है या नहीं
   - Site visit status correct है या नहीं
   - Required fields filled हैं या नहीं

**If Errors:**
- Site visit create करें
- Site visit complete करें
- Verification status = 'pending' set करें

### Stage 7: Site Visit Verification
**User:** CRM/Admin/Sales Head

1. **User switch करें** - CRM या Admin select करें
2. Stage 7 select करें
3. Test करें
4. **Check:**
   - Site visit verification panel में है या नहीं
   - Site visit verify हो सकती है या नहीं
   - Achievement counted है या नहीं

**If Errors:**
- Site visit verify करें
- Achievement count check करें

### Stage 8: Closer Conversion
**User:** Sales Manager

1. **User switch करें** - Sales Manager पर वापस जाएं
2. Stage 8 select करें
3. Test करें
4. **Check:**
   - Verified site visit available है या नहीं
   - Closer conversion possible है या नहीं
   - Closer status = 'pending' set है या नहीं

**If Errors:**
- Site visit को closer में convert करें
- Closer status = 'pending' set करें

### Stage 9: Closer Verification
**User:** CRM/Admin

1. **User switch करें** - CRM या Admin select करें
2. Stage 9 select करें
3. Test करें
4. **Check:**
   - Closer verification panel में है या नहीं
   - Closer verify हो सकता है या नहीं
   - Lead status = 'closed' update हुआ है या नहीं
   - Achievement counted है या नहीं

**If Errors:**
- Closer verify करें
- Lead status check करें
- Achievement count check करें

### Stage 10: Incentive Request
**User:** HR Manager/Sales Executive/Sales Manager

1. **User switch करें** - HR Manager या Sales Manager select करें
2. Stage 10 select करें
3. Test करें
4. **Check:**
   - Verified closer available है या नहीं
   - Incentive request possible है या नहीं
   - Incentive status = 'pending' set है या नहीं

**If Errors:**
- Incentive request create करें
- Incentive status = 'pending' set करें

### Stage 11: Incentive Approval
**User:** Finance Manager

1. **User switch करें** - Finance Manager select करें
2. Stage 11 select करें
3. Test करें
4. **Check:**
   - Incentive request verification panel में है या नहीं
   - Incentive approve/reject हो सकता है या नहीं
   - Payment processing possible है या नहीं

**If Errors:**
- Incentive verify करें
- Approve/Reject करें
- Payment processing check करें

## Error Types & Fixes

### Missing Data Errors
**Example:** "No leads assigned to telecaller"

**Fix:**
1. Lead create करें
2. Lead को telecaller को assign करें
3. Task create करें

### Invalid Status Errors
**Example:** "Invalid lead status"

**Fix:**
1. Lead status check करें
2. Correct status set करें
3. Status transition validate करें

### Missing Relationship Errors
**Example:** "Missing verification task"

**Fix:**
1. Required relationship create करें
2. Foreign keys check करें
3. Relationships properly link करें

### Permission Errors
**Example:** "User must be Sales Manager to test this stage"

**Fix:**
1. Correct user select करें
2. User role check करें
3. Permissions verify करें

## Tips & Best Practices

1. **Sequential Testing:** Stages को order में test करें (1 से 11 तक)
2. **User Switching:** हर stage के लिए correct user select करें
3. **Data Validation:** हर stage के बाद data check करें
4. **Error Fixing:** Errors fix करने के बाद फिर से test करें
5. **Progress Tracking:** Progress bar में stage status देखते रहें

## Common Issues & Solutions

### Issue: "Stage not found"
**Solution:** Page refresh करें और फिर से try करें

### Issue: "User login failed"
**Solution:** 
- Check करें कि user active है
- Check करें कि user role correct है
- Session clear करें और फिर से login करें

### Issue: "Validation failed"
**Solution:**
- Errors read करें
- Required data create करें
- Status transitions check करें
- Relationships verify करें

### Issue: "No data available"
**Solution:**
- Previous stages complete करें
- Required data create करें
- Dependencies check करें

## Testing Checklist

- [ ] Stage 1: Telecaller Lead Creation - ✅
- [ ] Stage 2: Prospect Creation - ✅
- [ ] Stage 3: Manager Verification - ✅
- [ ] Stage 4: Meeting Creation - ✅
- [ ] Stage 5: Meeting Verification - ✅
- [ ] Stage 6: Site Visit Creation - ✅
- [ ] Stage 7: Site Visit Verification - ✅
- [ ] Stage 8: Closer Conversion - ✅
- [ ] Stage 9: Closer Verification - ✅
- [ ] Stage 10: Incentive Request - ✅
- [ ] Stage 11: Incentive Approval - ✅

## Support

अगर कोई issue आए:
1. Browser console check करें (F12)
2. Network tab में API calls check करें
3. Errors read करें और fix करें
4. Page refresh करें और फिर से try करें

## Notes

- Flow testing page केवल Admin और CRM users access कर सकते हैं
- User switching temporary है - page refresh के बाद original user पर वापस आ जाएंगे
- Test data production data को affect नहीं करता
- सभी testing activities log होती हैं

---

**Happy Testing! 🚀**
