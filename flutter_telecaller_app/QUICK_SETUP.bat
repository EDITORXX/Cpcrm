@echo off
chcp 65001 >nul
title Flutter Quick Setup
color 0C

echo ========================================
echo   Flutter Quick Setup Guide
echo ========================================
echo.

echo This will help you set up Flutter PATH.
echo.
echo Choose an option:
echo.
echo [1] Add to User PATH (Recommended - No Admin needed)
echo [2] Add to System PATH (Requires Admin rights)
echo [3] Verify current setup
echo [4] Exit
echo.

set /p choice="Enter your choice (1-4): "

if "%choice%"=="1" (
    echo.
    echo Running User PATH setup...
    call "%~dp0ADD_FLUTTER_TO_USER_PATH.bat"
    goto :end
)

if "%choice%"=="2" (
    echo.
    echo Running System PATH setup (Admin required)...
    call "%~dp0ADD_FLUTTER_TO_SYSTEM_PATH.bat"
    goto :end
)

if "%choice%"=="3" (
    echo.
    echo Verifying setup...
    call "%~dp0VERIFY_FLUTTER_SETUP.bat"
    goto :end
)

if "%choice%"=="4" (
    exit /b 0
)

echo Invalid choice!
pause
goto :end

:end
echo.
echo Setup complete!
pause
