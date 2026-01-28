@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Laravel + ngrok Setup - Complete Guide
echo ========================================
echo.
echo This script will help you start Laravel and ngrok
echo.
echo You need TWO command windows:
echo   1. One for Laravel server (port 8007)
echo   2. One for ngrok tunnel
echo.
echo ========================================
echo.

REM Check prerequisites
echo [1/4] Checking prerequisites...
echo.

REM Check PHP
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] PHP not found in PATH
    echo You may need to use full path to php.exe
    echo.
)

REM Check ngrok
where ngrok >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] ngrok not found in PATH
    echo Please run setup_ngrok.bat first or install ngrok
    echo.
)

REM Check if Laravel project
if not exist "artisan" (
    echo [ERROR] Not a Laravel project (artisan file not found)
    echo Please run this script from the Laravel project root
    pause
    exit /b 1
)

echo [OK] Prerequisites check complete
echo.

echo [2/4] Starting Laravel server in new window...
echo.
start "Laravel Server (Port 8007)" cmd /k "cd /d "%~dp0" && start_laravel_ngrok.bat"

echo Waiting 5 seconds for Laravel to start...
timeout /t 5 /nobreak >nul

echo.
echo [3/4] Starting ngrok tunnel in new window...
echo.
start "ngrok Tunnel" cmd /k "cd /d "%~dp0" && start_ngrok_tunnel.bat"

echo.
echo [4/4] Setup complete!
echo.
echo ========================================
echo What's Running:
echo ========================================
echo.
echo Window 1: Laravel Server
echo   - Local URL: http://localhost:8007
echo   - Keep this window open!
echo.
echo Window 2: ngrok Tunnel
echo   - Public URL: Check the ngrok window for your public URL
echo   - Inspector: http://127.0.0.1:4040
echo   - Keep this window open!
echo.
echo ========================================
echo Next Steps:
echo ========================================
echo.
echo 1. Check the ngrok window for your public URL
echo    (It will look like: https://xxxx-xx-xx-xx-xx.ngrok-free.app)
echo.
echo 2. Open the public URL in your browser
echo    (First visit may show ngrok warning - click "Visit Site")
echo.
echo 3. Open ngrok inspector:
echo    http://127.0.0.1:4040
echo.
echo 4. Test from mobile device using the public URL
echo.
echo ========================================
echo.
echo Both windows are now open. Check them for status.
echo.
echo To stop everything:
echo   - Press Ctrl+C in each window
echo   - Or close the windows
echo.
pause
