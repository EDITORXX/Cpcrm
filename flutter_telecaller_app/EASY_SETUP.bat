@echo off
chcp 65001 >nul
title Flutter Easy Setup
color 0A

echo ========================================
echo   Flutter PATH Setup - Easy Method
echo ========================================
echo.

REM Check if Flutter exists
if exist "C:\flutter\bin\flutter.bat" (
    echo [✓] Flutter found at C:\flutter\bin
    goto :continue
)

REM Alternative check
if exist "C:\flutter\bin\flutter.exe" (
    echo [✓] Flutter found at C:\flutter\bin
    goto :continue
)

echo [✗] Flutter not found at C:\flutter\bin
echo.
echo Checking alternative locations...
if exist "C:\flutter" (
    echo [i] C:\flutter folder exists
    dir "C:\flutter\bin" 2>nul
) else (
    echo [✗] C:\flutter folder does not exist
)
echo.
echo Please verify Flutter installation at C:\flutter
pause
exit /b 1

:continue

echo.
echo This will add Flutter to User PATH (no admin needed)
echo.
echo Press any key to continue...
pause >nul

echo.
echo Running PowerShell script...
echo.

REM Run PowerShell script
powershell -ExecutionPolicy Bypass -File "%~dp0add_flutter_path.ps1"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   Setup Complete!
    echo ========================================
    echo.
    echo Please restart your terminal/IDE now.
    echo.
) else (
    echo.
    echo [✗] Setup failed. Please try manual method.
    echo.
)

pause
