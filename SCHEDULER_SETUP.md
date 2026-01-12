# Laravel Scheduler Setup Guide

## For Auto-Sync to Work

The Google Sheets auto-sync requires the Laravel scheduler to be running. The scheduler runs every minute and checks if it's time to sync based on the `sync_interval_minutes` setting.

## Quick Start (Easiest Method)

**Just run `start_all_servers.bat`** - It will start everything including the scheduler!

The scheduler window will open automatically and must stay open for auto-sync to work.

## Setup Instructions

### Option 1: Use start_all_servers.bat (Easiest - Recommended)

Simply double-click `start_all_servers.bat` - it will start:
- Laravel server
- Queue worker
- **Laravel Scheduler (Auto-Sync)** ← This is what you need!
- Frontend dev server

**IMPORTANT:** Keep the "Laravel Scheduler" window open! Closing it will stop auto-sync.

### Option 2: Run Scheduler Separately

Double-click `run_scheduler.bat` to start just the scheduler.

### Option 3: Windows Task Scheduler (Runs in Background - Advanced)

1. Open Task Scheduler
2. Create a new task
3. Set it to run every minute
4. Action: Start a program
5. Program: `php`
6. Arguments: `artisan schedule:run`
7. Start in: `C:\Users\vivek\Pictures\Laravel crm fully functional`

Or use this command in PowerShell (as Administrator):
```powershell
schtasks /create /tn "Laravel Scheduler" /tr "php artisan schedule:run" /sc minute /mo 1 /f
```

### Option 2: Run Manually (For Testing)

You can manually run the sync command:
```bash
php artisan google-sheets:sync
```

Or force sync (bypasses interval check):
```bash
php artisan google-sheets:sync --force
```

### Option 3: Use a Background Process

Create a batch file `run_scheduler.bat`:
```batch
@echo off
:loop
php artisan schedule:run
timeout /t 60 /nobreak >nul
goto loop
```

Then run it in the background.

## Verify Scheduler is Running

Check scheduled tasks:
```bash
php artisan schedule:list
```

## Sync Interval

The sync interval is configured per Google Sheet config in the database:
- Default: 5 minutes
- Can be changed in the admin panel
- The scheduler checks every minute, but only syncs when the interval has passed

## Troubleshooting

1. **Leads not syncing automatically:**
   - Check if scheduler is running: `php artisan schedule:list`
   - Check logs: `storage/logs/laravel.log`
   - Manually test: `php artisan google-sheets:sync --force`

2. **Sync interval too long:**
   - Update `sync_interval_minutes` in `google_sheets_config` table
   - Or use `--force` flag to bypass interval

3. **Scheduler not running:**
   - Set up Windows Task Scheduler (see above)
   - Or run `php artisan schedule:run` manually every minute
