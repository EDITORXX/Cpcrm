@echo off
chcp 65001 >nul
title Flutter PATH Setup
color 0B

echo ========================================
echo   Flutter PATH Setup
echo ========================================
echo.

REM Check if Flutter exists
if exist "C:\flutter\bin\flutter.bat" (
    echo [✓] Flutter found at C:\flutter\bin
) else (
    echo [✗] Flutter not found at C:\flutter\bin
    echo Please install Flutter first
    pause
    exit /b 1
)

echo.
echo Adding Flutter to PATH for this session...
set PATH=%PATH%;C:\flutter\bin

echo.
echo Testing Flutter command...
flutter --version >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [✓] Flutter command working!
    echo.
    echo ========================================
    echo   Running Flutter Doctor...
    echo ========================================
    echo.
    flutter doctor
) else (
    echo [✗] Flutter command not working
    echo Please check Flutter installation
)

echo.
echo ========================================
echo   Note: PATH is set for this session only
echo ========================================
echo.
echo To make PATH permanent:
echo 1. Press Win+R, type: sysdm.cpl
echo 2. Go to Advanced tab ^> Environment Variables
echo 3. Edit PATH, add: C:\flutter\bin
echo 4. Restart terminal/IDE
echo.
pause
