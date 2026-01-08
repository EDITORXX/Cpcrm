@echo off
chcp 65001 >nul
title Flutter App - Install and Build APK
color 0A

echo ========================================
echo   Telecaller CRM - APK Builder
echo ========================================
echo.

REM Check if Flutter is installed
where flutter >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [✓] Flutter is already installed
    goto :build
)

echo [✗] Flutter is not installed
echo.
echo ========================================
echo   Installing Flutter...
echo ========================================
echo.

REM Check if Flutter directory exists
if exist "C:\flutter\bin\flutter.bat" (
    echo [✓] Flutter found at C:\flutter
    set PATH=%PATH%;C:\flutter\bin
    goto :build
)

echo [INFO] Flutter needs to be installed manually
echo.
echo Please follow these steps:
echo.
echo 1. Download Flutter SDK from:
echo    https://storage.googleapis.com/flutter_infra_release/releases/stable/windows/flutter_windows_3.24.0-stable.zip
echo.
echo 2. Extract to C:\flutter
echo.
echo 3. Add C:\flutter\bin to your PATH:
echo    - Press Win+R, type: sysdm.cpl
echo    - Go to Advanced tab ^> Environment Variables
echo    - Edit PATH, add: C:\flutter\bin
echo.
echo 4. Restart this script
echo.
pause
exit /b 1

:build
echo.
echo ========================================
echo   Building APK...
echo ========================================
echo.

REM Navigate to app directory
cd /d "%~dp0"

REM Clean previous builds
echo [1/4] Cleaning previous builds...
flutter clean >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [✓] Cleaned
) else (
    echo [✗] Clean failed (may be first build)
)

REM Get dependencies
echo [2/4] Getting dependencies...
flutter pub get
if %ERRORLEVEL% NEQ 0 (
    echo [✗] Failed to get dependencies
    pause
    exit /b 1
)
echo [✓] Dependencies installed

REM Check Android setup
echo [3/4] Checking Android setup...
flutter doctor --android-licenses >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [⚠] Android licenses not accepted
    echo     Run: flutter doctor --android-licenses
)

REM Build APK
echo [4/4] Building APK (this may take a few minutes)...
flutter build apk --release

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   ✓ APK Built Successfully!
    echo ========================================
    echo.
    echo APK Location:
    echo %CD%\build\app\outputs\flutter-apk\app-release.apk
    echo.
    
    REM Try to open the folder
    if exist "build\app\outputs\flutter-apk\app-release.apk" (
        echo Opening folder...
        explorer "build\app\outputs\flutter-apk"
        echo.
        echo APK file is ready! You can install it on your Android device.
    )
) else (
    echo.
    echo ========================================
    echo   ✗ Build Failed
    echo ========================================
    echo.
    echo Please check the error messages above
    echo Common issues:
    echo - Flutter not properly installed
    echo - Android SDK not configured
    echo - Missing dependencies
    echo.
    echo Run: flutter doctor
    echo.
)

echo.
pause

