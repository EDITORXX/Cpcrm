@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Verifying Laravel + ngrok Setup
echo ========================================
echo.

set "ALL_CHECKS_PASSED=1"

REM Check 1: PHP
echo [Check 1/6] PHP Installation...
where php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    php -v | findstr /C:"PHP"
    echo [OK] PHP is installed
) else (
    echo [FAIL] PHP not found in PATH
    set "ALL_CHECKS_PASSED=0"
)
echo.

REM Check 2: Laravel Project
echo [Check 2/6] Laravel Project...
if exist "artisan" (
    echo [OK] Laravel project detected
) else (
    echo [FAIL] Not a Laravel project (artisan file not found)
    set "ALL_CHECKS_PASSED=0"
)
echo.

REM Check 3: .env file
echo [Check 3/6] Environment Configuration...
if exist ".env" (
    echo [OK] .env file exists
    findstr /C:"APP_KEY=" .env | findstr /V "APP_KEY=$" >nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        echo [OK] APP_KEY is set
    ) else (
        echo [WARNING] APP_KEY not set (run: php artisan key:generate)
    )
) else (
    echo [WARNING] .env file not found
)
echo.

REM Check 4: Laravel Server Running
echo [Check 4/6] Laravel Server Status...
netstat -ano | findstr ":8007" | findstr "LISTENING" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] Laravel server is running on port 8007
    echo.
    echo Testing local connection...
    curl -s -o nul -w "HTTP Status: %%{http_code}\n" http://localhost:8007 2>nul
    if %ERRORLEVEL% EQU 0 (
        echo [OK] Local server is responding
    ) else (
        echo [WARNING] Could not connect to local server (curl may not be available)
    )
) else (
    echo [FAIL] Laravel server is NOT running on port 8007
    echo   Run: start_laravel_ngrok.bat
    set "ALL_CHECKS_PASSED=0"
)
echo.

REM Check 5: ngrok Installation
echo [Check 5/6] ngrok Installation...
where ngrok >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    ngrok version 2>nul
    echo [OK] ngrok is installed
) else (
    echo [FAIL] ngrok not found in PATH
    echo   Run: setup_ngrok.bat
    set "ALL_CHECKS_PASSED=0"
)
echo.

REM Check 6: ngrok Tunnel Running
echo [Check 6/6] ngrok Tunnel Status...
netstat -ano | findstr ":4040" | findstr "LISTENING" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] ngrok inspector is running (port 4040)
    echo.
    echo Testing ngrok inspector...
    curl -s -o nul -w "HTTP Status: %%{http_code}\n" http://127.0.0.1:4040 2>nul
    if %ERRORLEVEL% EQU 0 (
        echo [OK] ngrok inspector is accessible
        echo   Open: http://127.0.0.1:4040
    ) else (
        echo [WARNING] Could not connect to inspector (curl may not be available)
    )
    echo.
    echo [INFO] To get your public URL:
    echo   1. Open: http://127.0.0.1:4040
    echo   2. Look for the "Forwarding" section
    echo   3. Copy the HTTPS URL (e.g., https://xxxx-xx-xx-xx-xx.ngrok-free.app)
) else (
    echo [FAIL] ngrok tunnel is NOT running
    echo   Run: start_ngrok_tunnel.bat
    set "ALL_CHECKS_PASSED=0"
)
echo.

REM Check 7: Windows Firewall
echo [Check 7/6] Windows Firewall...
netsh advfirewall firewall show rule name="Laravel Port 8007" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] Firewall rule exists for port 8007
) else (
    echo [WARNING] Firewall rule not found (may need to allow port 8007)
)
echo.

echo ========================================
echo Verification Summary
echo ========================================
echo.

if %ALL_CHECKS_PASSED% EQU 1 (
    echo [SUCCESS] All critical checks passed!
    echo.
    echo Your setup is ready:
    echo   - Local URL: http://localhost:8007
    echo   - ngrok Inspector: http://127.0.0.1:4040
    echo   - Public URL: Check ngrok window or inspector
    echo.
) else (
    echo [WARNING] Some checks failed. Please fix the issues above.
    echo.
    echo Quick fixes:
    echo   - Start Laravel: start_laravel_ngrok.bat
    echo   - Start ngrok: start_ngrok_tunnel.bat
    echo   - Setup ngrok: setup_ngrok.bat
    echo.
)

echo ========================================
echo.
pause
