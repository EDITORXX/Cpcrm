@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Starting Realtor CRM on port 8007...
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

echo Step 1: Detecting your local network IP address...
php get_local_ip.php > temp_ip.txt 2>nul
set /p DETECTED_IP=<temp_ip.txt
del temp_ip.txt

if "%DETECTED_IP%"=="" (
    echo Warning: Could not detect IP address, using localhost
    set DETECTED_IP=127.0.0.1
)

echo Detected IP: %DETECTED_IP%
echo.

REM Backup .env file
if exist .env (
    echo Step 2: Backing up .env file...
    copy .env .env.backup >nul 2>&1
    echo Backup created: .env.backup
    echo.
)

REM Update APP_URL in .env
echo Step 3: Updating APP_URL for network access...
set "NEW_APP_URL=http://%DETECTED_IP%:8007"

REM Update APP_URL in .env using PowerShell for reliable parsing
powershell -Command "$content = Get-Content '.env' -Raw; $newUrl = 'APP_URL=%NEW_APP_URL%'; $newContent = $content -replace '(?m)^\s*APP_URL\s*=.*$', $newUrl; [System.IO.File]::WriteAllText((Resolve-Path '.env'), $newContent, [System.Text.Encoding]::UTF8)"
echo APP_URL updated to: %NEW_APP_URL%
echo.

echo ========================================
echo Server Access URLs:
echo ========================================
echo Local:  http://localhost:8007
echo Network: http://%DETECTED_IP%:8007
echo.
echo To access from mobile/tablet on same network:
echo   1. Make sure your device is on the same WiFi/network
echo   2. Open browser and go to: http://%DETECTED_IP%:8007
echo.
echo Note: Windows Firewall may ask for permission on first run
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

REM Start the server
php artisan serve --host=0.0.0.0 --port=8007

REM On exit, optionally restore original .env (commented out - keep network URL)
REM if exist .env.backup (
REM     echo.
REM     echo Restoring original .env...
REM     copy /y .env.backup .env >nul 2>&1
REM     del .env.backup >nul 2>&1
REM )

endlocal
