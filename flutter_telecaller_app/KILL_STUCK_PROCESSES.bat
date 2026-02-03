@echo off
title Kill Stuck Java/Gradle Processes
color 0C

echo ========================================
echo   Killing Stuck Java/Gradle Processes
echo ========================================
echo.

echo This will close all Java processes (including stuck builds)
echo.
echo Press Ctrl+C to cancel, or
pause

taskkill /F /IM java.exe /T 2>nul

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [✓] Java processes killed
) else (
    echo.
    echo [i] No Java processes found or already closed
)

echo.
echo You can now run BUILD_APK_CMD.bat
echo.
pause
