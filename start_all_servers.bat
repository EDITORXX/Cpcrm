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

echo Starting Laravel Server on port 8007...
start "Laravel Server (Port 8007)" cmd /k "php artisan serve --host=0.0.0.0 --port=8007"
timeout /t 2 /nobreak >nul

echo Starting Queue Worker...
start "Queue Worker" cmd /k "php artisan queue:work"
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
echo   - Laravel Server: http://localhost:8007
echo   - Queue Worker: Processing background jobs
echo   - Frontend Dev Server: Hot reload enabled
echo.
echo To stop servers, close the respective command windows.
echo.
pause
