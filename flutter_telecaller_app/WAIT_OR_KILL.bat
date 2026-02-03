@echo off
chcp 65001 >nul
title Check Build Status
color 0E

echo ========================================
echo   Build Status Check
echo ========================================
echo.

echo Checking if build is still running...
echo.

REM Check for Java/Gradle processes
tasklist /FI "IMAGENAME eq java.exe" 2>nul | find /I "java.exe" >nul
if %ERRORLEVEL% EQU 0 (
    echo [i] Java processes are running - Build is in progress
    echo.
    echo This is NORMAL - Gradle build takes 3-5 minutes
    echo Please wait...
    echo.
    echo If you want to stop and restart:
    echo 1. Press Ctrl+C in the build terminal
    echo 2. Or run: taskkill /F /IM java.exe /T
    echo 3. Then run: FRESH_BUILD.bat
) else (
    echo [i] No Java processes found
    echo Build might have completed or failed
    echo.
    echo Check the build output for success/failure message
)

echo.
pause
