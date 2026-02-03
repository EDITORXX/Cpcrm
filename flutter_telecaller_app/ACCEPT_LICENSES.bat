@echo off
chcp 65001 >nul
title Accept Android Licenses
color 0A

echo ========================================
echo   Android Licenses Acceptance
echo ========================================
echo.
echo This will accept all Android SDK licenses.
echo You need to press 'y' for each license prompt.
echo.
echo Press any key to start...
pause >nul

echo.
echo Running: flutter doctor --android-licenses
echo.
echo IMPORTANT: When prompted, type 'y' and press Enter
echo for each license (usually 6-7 licenses).
echo.

REM Set PATH to include Flutter
set PATH=%PATH%;C:\flutter\bin

REM Set ANDROID_HOME
set ANDROID_HOME=%LOCALAPPDATA%\Android\Sdk

REM Run license acceptance
flutter doctor --android-licenses

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   Licenses Accepted Successfully!
    echo ========================================
    echo.
) else (
    echo.
    echo [⚠] Some licenses might not be accepted
    echo Please check the output above
    echo.
)

echo.
pause
