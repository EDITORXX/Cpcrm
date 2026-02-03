@echo off
chcp 65001 >nul
title Add Flutter to System PATH
color 0B

echo ========================================
echo   Flutter PATH Setup - System Variables
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [✗] Admin rights required!
    echo.
    echo Please run this script as Administrator:
    echo 1. Right-click on this file
    echo 2. Select "Run as administrator"
    echo.
    pause
    exit /b 1
)

echo [✓] Admin rights confirmed
echo.

REM Check if Flutter exists
if exist "C:\flutter\bin\flutter.bat" (
    echo [✓] Flutter found at C:\flutter\bin
) else (
    echo [✗] Flutter not found at C:\flutter\bin
    echo Please install Flutter first at C:\flutter
    pause
    exit /b 1
)

echo.
echo Adding Flutter to System PATH...
echo.

REM Use PowerShell script to add to System PATH
powershell -ExecutionPolicy Bypass -File "%~dp0add_flutter_path_system.ps1"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   ✓ Setup Complete!
    echo ========================================
    echo.
    echo Flutter has been added to System PATH.
    echo.
    echo IMPORTANT: Please restart your terminal/IDE
    echo for changes to take effect.
    echo.
    echo After restart, run: flutter --version
    echo.
) else (
    echo.
    echo [✗] Failed to add Flutter to PATH
    echo Please try manual method from guide
    echo.
)

pause
