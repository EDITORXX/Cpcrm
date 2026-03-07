# Facebook Lead Ads – Required Migrations

If you see errors like **Table 'fb_pages' doesn't exist** or **Table 'fb_lead_ads_settings' doesn't exist**, run migrations on the server (or with production `.env`):

```bash
php artisan migrate
```

This will create all missing tables:

- `fb_lead_ads_settings`
- `fb_pages`
- `fb_forms`
- `fb_form_mappings`
- `fb_webhook_events`
- `fb_leads`