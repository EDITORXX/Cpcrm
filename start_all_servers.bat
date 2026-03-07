@echo off
echo ========================================
echo Starting All CRM Servers...
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo ERROR: .env file not found!
    echo Please run setup.bat first to create .env file.
    echo.
    pause
    exit /b 1
)

echo Starting Laravel Server on port 8008...
start "Laravel Server (Port 8008)" cmd /k "php artisan serve --host=0.0.0.0 --port=8008"
timeout /t 2 /nobreak >nul

echo Starting Queue Worker...
start "Queue Worker" cmd /k "php artisan queue:work"
timeout /t 2 /nobreak >nul

echo Starting Laravel Scheduler (Auto-Sync)...
start "Laravel Scheduler" cmd /k "run_scheduler.bat"
timeout /t 2 /nobreak >nul

echo Starting Frontend Dev Server...
start "Frontend Dev Server (Vite)" cmd /k "npm run dev"
timeout /t 2 /nobreak >nul

echo.
echo ========================================
echo All Servers Started!
echo ========================================
echo.
echo Server Windows:
echo   - Laravel Server: http://localhost:8008
echo   - Queue Worker: Processing background jobs
echo   - Laravel Scheduler: Auto-syncing Google Sheets every minute
echo   - Frontend Dev Server: Hot reload enabled
echo.
echo IMPORTANT: Keep all windows open for services to work!
echo   - Auto-sync requires the Scheduler window to stay open
echo   - Queue processing requires the Queue Worker window to stay open
echo.
echo To stop servers, close the respective command windows.
echo.
pause
