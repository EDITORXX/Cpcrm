# Pabbly Webhook Integration Guide

## Overview

This guide explains how to connect Pabbly to your Laravel CRM to automatically create leads when events occur in Pabbly.

## Webhook Endpoint

**URL:** `http://your-domain:8007/api/pabbly/webhook`  
**Method:** POST  
**Content-Type:** application/json

**Note:** Replace `your-domain` with:
- `localhost` for local testing
- Your ngrok/Cloudflare tunnel URL for public access (e.g., `https://xxxx-xxxx-xxxx.trycloudflare.com`)

## Expected Payload Format

The webhook accepts flexible field names. Here are the supported fields:

### Required Fields
- `name` (or `customer_name`, `full_name`, `contact_name`)
- `phone` (or `mobile`, `phone_number`, `contact_number`)

### Optional Fields
- `email` (or `email_address`)
- `address` (or `street_address`)
- `city`
- `state`
- `pincode` (or `pin_code`, `postal_code`, `zip`)
- `property_type` (values: `apartment`, `villa`, `plot`, `commercial`, `other`)
- `budget_min` (or `budget_minimum`, `min_budget`)
- `budget_max` (or `budget_maximum`, `max_budget`)
- `requirements` (or `requirement`, `message`, `notes`)
- `notes` (or `note`, `additional_notes`)

## Example Payload

```json
{
  "name": "John Doe",
  "phone": "9876543210",
  "email": "john@example.com",
  "city": "Mumbai",
  "state": "Maharashtra",
  "property_type": "apartment",
  "budget_min": 5000000,
  "budget_max": 10000000,
  "requirements": "Looking for 2BHK apartment in prime location"
}
```

## Testing the Webhook

### Option 1: Using cURL (Command Line)

**Local Testing:**
```bash
curl -X POST http://localhost:8007/api/pabbly/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Lead",
    "phone": "9876543210",
    "email": "test@example.com"
  }'
```

**Public Testing (with ngrok/Cloudflare):**
```bash
curl -X POST https://xxxx-xxxx-xxxx.trycloudflare.com/api/pabbly/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Lead",
    "phone": "9876543210",
    "email": "test@example.com"
  }'
```

### Option 2: Using Postman

1. **Create a new POST request**
2. **URL:** `http://localhost:8007/api/pabbly/webhook` (or your public URL)
3. **Headers:**
   - `Content-Type: application/json`
4. **Body (raw JSON):**
   ```json
   {
     "name": "Test Lead",
     "phone": "9876543210",
     "email": "test@example.com",
     "city": "Mumbai"
   }
   ```
5. **Click Send**

### Option 3: Using PowerShell (Windows)

```powershell
$body = @{
    name = "Test Lead"
    phone = "9876543210"
    email = "test@example.com"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8007/api/pabbly/webhook" `
    -Method Post `
    -ContentType "application/json" `
    -Body $body
```

## Expected Response

### Success Response (201 Created)
```json
{
  "status": "ok",
  "message": "Lead created successfully",
  "lead_id": 123,
  "lead": {
    "id": 123,
    "name": "Test Lead",
    "phone": "9876543210",
    "email": "test@example.com",
    "source": "pabbly"
  }
}
```

### Error Response (422 Validation Error)
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "phone": ["The phone field is required."]
  }
}
```

### Error Response (500 Server Error)
```json
{
  "status": "error",
  "message": "Failed to create lead: [error details]"
}
```

## Setting Up in Pabbly

1. **In your Pabbly workflow:**
   - Add a "Webhook" action step
   - Choose "POST" method
   - Enter your webhook URL: `http://your-domain:8007/api/pabbly/webhook`
   - Set Content-Type: `application/json`

2. **Map your fields:**
   - Map your form/trigger fields to the JSON payload
   - At minimum, include `name` and `phone`
   - Add other fields as needed

3. **Test the connection:**
   - Run a test in Pabbly
   - Check Laravel logs: `storage/logs/laravel.log`
   - Verify lead was created in your CRM

## Field Mapping Examples

### Pabbly Form Fields → Webhook Payload

If your Pabbly form has:
- "Full Name" → map to `name`
- "Mobile Number" → map to `phone`
- "Email ID" → map to `email`
- "City" → map to `city`
- "Property Type" → map to `property_type`
- "Budget Range" → split into `budget_min` and `budget_max`

### Example Pabbly Webhook Configuration

In Pabbly, your webhook body should look like:
```json
{
  "name": "{{form.full_name}}",
  "phone": "{{form.mobile_number}}",
  "email": "{{form.email_id}}",
  "city": "{{form.city}}",
  "property_type": "{{form.property_type}}",
  "requirements": "{{form.message}}"
}
```

## Logging

All webhook requests are logged to `storage/logs/laravel.log` with:
- Full incoming payload
- Request headers
- IP address
- Success/error status
- Lead ID (on success)

To view logs:
```bash
tail -f storage/logs/laravel.log
```

Or on Windows:
```cmd
type storage\logs\laravel.log
```

## Troubleshooting

### Issue: "Validation failed"
**Solution:** Ensure `name` and `phone` fields are included in the payload.

### Issue: "Failed to create lead"
**Solution:** 
- Check Laravel logs for detailed error
- Verify database connection
- Ensure migration was run: `php artisan migrate`

### Issue: Webhook not receiving requests
**Solution:**
- Verify Laravel server is running on port 8007
- Check firewall settings
- For public access, ensure ngrok/Cloudflare tunnel is active
- Test locally first: `http://localhost:8007/api/pabbly/webhook`

### Issue: Lead created but source is wrong
**Solution:**
- Ensure migration was run: `php artisan migrate`
- Verify `pabbly` is in the source enum

## Security Considerations

**Current Setup:** Webhook is public (no authentication)

**For Production:** Consider adding:
- API key authentication
- IP whitelist
- Request signature verification

## Next Steps

1. Test the webhook locally using cURL or Postman
2. Set up your Pabbly workflow with the webhook action
3. Test end-to-end: Trigger Pabbly → Check Laravel logs → Verify lead in CRM
4. Monitor logs for any issues

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for detailed error messages
2. Verify the webhook URL is accessible
3. Test with a simple payload first (just name and phone)
4. Ensure all required fields are mapped correctly in Pabbly
