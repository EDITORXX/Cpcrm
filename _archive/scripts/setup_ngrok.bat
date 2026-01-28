@echo off
setlocal enabledelayedexpansion

echo ========================================
echo ngrok Setup and Configuration Guide
echo ========================================
echo.

REM Check if ngrok is already installed
where ngrok >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] ngrok is already installed and in PATH
    ngrok version
    echo.
    goto :check_auth
)

echo [INFO] ngrok is not found in PATH
echo.
echo ========================================
echo Installation Options:
echo ========================================
echo.
echo Option 1: Manual Download (Recommended)
echo   1. Visit: https://ngrok.com/download
echo   2. Download Windows 64-bit version
echo   3. Extract ngrok.exe to a folder (e.g., C:\ngrok\)
echo   4. Add to PATH or use full path
echo.
echo Option 2: Install via Chocolatey (if installed)
echo   Run: choco install ngrok
echo.
echo ========================================
echo.

REM Check common installation locations
set "NGROK_PATHS=C:\ngrok\ngrok.exe;C:\Program Files\ngrok\ngrok.exe;C:\Program Files (x86)\ngrok\ngrok.exe;%USERPROFILE%\ngrok\ngrok.exe"

for %%P in (%NGROK_PATHS%) do (
    if exist "%%P" (
        echo [FOUND] ngrok at: %%P
        echo.
        echo To use this installation, either:
        echo   1. Add the folder to PATH:
        echo      setx PATH "%%~dpPATH;%%~dpP"
        echo   2. Or use full path when running ngrok
        echo.
        set "NGROK_FOUND=1"
        goto :check_auth
    )
)

if not defined NGROK_FOUND (
    echo [NOT FOUND] ngrok not found in common locations
    echo.
    echo Please download and install ngrok first.
    echo After installation, run this script again.
    echo.
    pause
    exit /b 1
)

:check_auth
echo ========================================
echo Checking ngrok Authentication
echo ========================================
echo.

REM Try to get ngrok config to check if authtoken is set
ngrok config check >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] ngrok appears to be configured
    echo.
    goto :signup_guide
) else (
    echo [WARNING] ngrok authtoken may not be configured
    echo.
)

:signup_guide
echo ========================================
echo ngrok Account Setup (Required)
echo ========================================
echo.
echo To use ngrok, you need a free account:
echo.
echo 1. Sign up at: https://dashboard.ngrok.com/signup
echo 2. Get your authtoken from: https://dashboard.ngrok.com/get-started/your-authtoken
echo 3. Configure ngrok with your authtoken:
echo.
echo    ngrok config add-authtoken YOUR_AUTHTOKEN_HERE
echo.
echo ========================================
echo.

set /p HAS_AUTHTOKEN="Do you have your ngrok authtoken ready? (Y/N): "
if /i "%HAS_AUTHTOKEN%"=="Y" (
    set /p AUTHTOKEN="Enter your ngrok authtoken: "
    if not "!AUTHTOKEN!"=="" (
        echo.
        echo Configuring ngrok...
        ngrok config add-authtoken !AUTHTOKEN!
        if %ERRORLEVEL% EQU 0 (
            echo [SUCCESS] ngrok configured successfully!
        ) else (
            echo [ERROR] Failed to configure ngrok
        )
    )
) else (
    echo.
    echo Please sign up and get your authtoken, then run this script again.
    echo Or configure manually using: ngrok config add-authtoken YOUR_TOKEN
)

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Next steps:
echo   1. Run: start_laravel_ngrok.bat (to start Laravel)
echo   2. Run: start_ngrok_tunnel.bat (in a separate window to start ngrok)
echo.
pause
