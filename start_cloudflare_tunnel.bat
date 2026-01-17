@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Starting Cloudflare Tunnel for CRM...
echo ========================================
echo.

REM Change to cloudflared directory
cd /d C:\cloudflared

REM Check if cloudflared.exe exists
if not exist "cloudflared.exe" (
    echo ERROR: cloudflared.exe not found in C:\cloudflared\
    echo Please check if the file exists.
    pause
    exit /b 1
)

echo Found cloudflared.exe
echo.
echo Starting tunnel "crm"...
echo Press Ctrl+C to stop.
echo.
echo ========================================
echo.

REM Run tunnel with config if exists, otherwise just run tunnel
if exist "config.yml" (
    echo Using config.yml file...
    cloudflared.exe tunnel --config config.yml run crm
) else (
    echo Running tunnel without config...
    cloudflared.exe tunnel run crm
)

echo.
echo Tunnel stopped.
pause

endlocal
