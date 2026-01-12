@echo off
title Laravel Scheduler - Auto-Sync
color 0A
echo ========================================
echo Laravel Scheduler - Auto-Sync Running
echo ========================================
echo.
echo This window MUST remain open for auto-sync to work!
echo.
echo The scheduler will:
echo   - Check Google Sheets every minute
echo   - Sync leads based on sync interval settings
echo   - Process other scheduled tasks
echo.
echo Last check: %date% %time%
echo.
echo Press Ctrl+C to stop the scheduler.
echo ========================================
echo.

cd /d "%~dp0"

:loop
echo [%date% %time%] Running scheduled tasks...
php artisan schedule:run
if %errorLevel% neq 0 (
    echo ERROR: Scheduler command failed!
    echo Check your PHP installation and Laravel setup.
    timeout /t 5 /nobreak >nul
)
timeout /t 60 /nobreak >nul 2>&1
goto loop
