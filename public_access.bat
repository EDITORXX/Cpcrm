@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Starting Public Access via Cloudflare Tunnel...
echo ========================================
echo.

REM Try multiple possible paths
set CLOUDFLARED_PATH=

REM Check path 1: C:\cloudflared\cloudflared.exe
if exist "C:\cloudflared\cloudflared.exe" (
    set CLOUDFLARED_PATH=C:\cloudflared\cloudflared.exe
    goto :found
)

REM Check path 2: C:\cloudflared\cloudflared.exe.exe (if user mentioned this)
if exist "C:\cloudflared\cloudflared.exe.exe" (
    set CLOUDFLARED_PATH=C:\cloudflared\cloudflared.exe.exe
    goto :found
)

REM Check path 3: Try to find in current directory
if exist "cloudflared.exe" (
    set CLOUDFLARED_PATH=cloudflared.exe
    goto :found
)

REM If not found, show error
echo ERROR: cloudflared.exe not found!
echo.
echo Searched locations:
echo   - C:\cloudflared\cloudflared.exe
echo   - C:\cloudflared\cloudflared.exe.exe
echo   - Current directory
echo.
echo Please ensure cloudflared.exe is installed and accessible.
pause
exit /b 1

:found
echo Found cloudflared.exe at: %CLOUDFLARED_PATH%
echo.
echo Starting tunnel to expose localhost:8007...
echo.
echo ========================================
echo IMPORTANT: Your public URL will appear below:
echo ========================================
echo.

REM Run tunnel with localhost:8007
"%CLOUDFLARED_PATH%" tunnel --url http://localhost:8007

echo.
echo Tunnel stopped.
pause

endlocal
