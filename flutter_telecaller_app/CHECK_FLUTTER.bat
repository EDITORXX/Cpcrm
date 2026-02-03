@echo off
chcp 65001 >nul
title Check Flutter Installation
color 0E

echo ========================================
echo   Flutter Installation Check
echo ========================================
echo.

echo Checking Flutter installation...
echo.

REM Check multiple possible locations
if exist "C:\flutter\bin\flutter.bat" (
    echo [✓] Flutter.bat found at C:\flutter\bin\flutter.bat
    goto :found
)

if exist "C:\flutter\bin\flutter.exe" (
    echo [✓] Flutter.exe found at C:\flutter\bin\flutter.exe
    goto :found
)

if exist "C:\flutter\bin\flutter" (
    echo [✓] Flutter found at C:\flutter\bin\flutter
    goto :found
)

echo [✗] Flutter not found at C:\flutter\bin
echo.

REM Check if C:\flutter exists
if exist "C:\flutter" (
    echo [i] C:\flutter folder exists
    echo.
    echo Contents of C:\flutter\bin:
    dir "C:\flutter\bin" 2>nul
    echo.
) else (
    echo [✗] C:\flutter folder does not exist
    echo.
    echo Please install Flutter first.
    echo Download from: https://flutter.dev/docs/get-started/install
    echo.
    pause
    exit /b 1
)

echo.
echo Flutter installation incomplete or in different location.
pause
exit /b 1

:found
echo.
echo ========================================
echo   Flutter Installation Found!
echo ========================================
echo.
echo Flutter is installed correctly.
echo.
echo You can now run: EASY_SETUP.bat
echo.
pause
