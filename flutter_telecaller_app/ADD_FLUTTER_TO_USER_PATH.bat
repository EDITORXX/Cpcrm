@echo off
chcp 65001 >nul
title Add Flutter to User PATH
color 0A

echo ========================================
echo   Flutter PATH Setup - User Variables
echo ========================================
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
echo Adding Flutter to User PATH...
echo.

REM Use PowerShell script to add to User PATH (no admin needed)
powershell -ExecutionPolicy Bypass -File "%~dp0add_flutter_path.ps1"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   ✓ Setup Complete!
    echo ========================================
    echo.
    echo Flutter has been added to User PATH.
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
