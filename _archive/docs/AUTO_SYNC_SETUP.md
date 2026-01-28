# Google Sheets Auto-Sync Setup

## Problem: Manual Sync Works, Auto-Sync Doesn't

If manual sync works (`php artisan google-sheets:sync`) but auto-sync doesn't, it means the **Laravel Scheduler is not running**.

## Solution: Start the Scheduler

### Method 1: Use start_all_servers.bat (Recommended)

1. Double-click `start_all_servers.bat`
2. A window named "Laravel Scheduler" will open
3. **Keep this window open** - closing it stops auto-sync
4. The scheduler will check every minute and sync when interval is reached

### Method 2: Run Scheduler Only

1. Double-click `run_scheduler.bat`
2. Keep the window open
3. Auto-sync will work every minute

### Method 3: Windows Task Scheduler (Background - No Window Needed)

1. Right-click `setup_scheduler_task.bat`
2. Select "Run as administrator"
3. This creates a Windows task that runs every minute
4. No window needed - runs in background

## Verify Auto-Sync is Working

1. Check the scheduler window - you should see messages every minute
2. Add a new lead to your Google Sheet
3. Wait for the sync interval (check in admin panel)
4. The lead should appear in CRM automatically

## Troubleshooting

### Auto-sync still not working?

1. **Check if scheduler is running:**
   - Look for "Laravel Scheduler" window
   - Or check Task Manager for `php artisan schedule:run`

2. **Check sync interval:**
   - Go to Lead Import → Google Sheets Config
   - Check `sync_interval_minutes` setting
   - Default is 5 minutes

3. **Test manually:**
   ```bash
   php artisan google-sheets:sync --force
   ```

4. **Check logs:**
   - Open `storage/logs/laravel.log`
   - Look for "Google Sheets sync" messages

### Scheduler window closes automatically?

- Make sure PHP is in your PATH
- Check for errors in the window before it closes
- Try running `run_scheduler.bat` directly

## Important Notes

- **The scheduler window MUST stay open** for auto-sync to work
- Auto-sync checks every minute, but only syncs when interval has passed
- Use `--force` flag to bypass interval check for testing
- Scheduler runs all scheduled tasks, not just Google Sheets sync
