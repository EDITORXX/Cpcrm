@echo off
chcp 65001 >nul
title Verify Flutter Setup
color 0E

echo ========================================
echo   Flutter Setup Verification
echo ========================================
echo.

REM Check if Flutter exists
if exist "C:\flutter\bin\flutter.bat" (
    echo [✓] Flutter installation found at C:\flutter\bin
) else (
    echo [✗] Flutter not found at C:\flutter\bin
    echo Please install Flutter first
    pause
    exit /b 1
)

echo.
echo Checking Flutter PATH...
echo.

REM Check System PATH
powershell -Command "$systemPath = [Environment]::GetEnvironmentVariable('Path', 'Machine'); if ($systemPath -like '*C:\flutter\bin*') { Write-Host '[✓] Flutter found in System PATH' } else { Write-Host '[✗] Flutter NOT in System PATH' }"

REM Check User PATH
powershell -Command "$userPath = [Environment]::GetEnvironmentVariable('Path', 'User'); if ($userPath -like '*C:\flutter\bin*') { Write-Host '[✓] Flutter found in User PATH' } else { Write-Host '[✗] Flutter NOT in User PATH' }"

echo.
echo Testing Flutter command...
echo.

REM Try to run Flutter
where flutter >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [✓] Flutter command is accessible!
    echo.
    echo Flutter version:
    flutter --version
    echo.
    echo ========================================
    echo   ✓ Setup Verified Successfully!
    echo ========================================
) else (
    echo [✗] Flutter command not found
    echo.
    echo PATH might not be updated in current session.
    echo Please restart terminal/IDE and try again.
    echo.
    echo Or run: ADD_FLUTTER_TO_USER_PATH.bat
    echo.
)

echo.
pause
