@echo off
chcp 65001 >nul
title Build Flutter APK
color 0A

echo ========================================
echo   Flutter APK Build
echo ========================================
echo.

REM Set PATH
set PATH=%PATH%;C:\flutter\bin

REM Set ANDROID_HOME
set ANDROID_HOME=%LOCALAPPDATA%\Android\Sdk

echo Flutter PATH set
echo ANDROID_HOME set
echo.

REM Navigate to app directory
cd /d "%~dp0"

echo Current directory: %CD%
echo.

echo Step 1: Cleaning previous builds...
flutter clean
echo.

echo Step 2: Getting dependencies...
flutter pub get
echo.

echo Step 3: Building APK (this may take 3-5 minutes)...
echo Please wait, do not close this window...
echo.

flutter build apk --release

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   APK Built Successfully!
    echo ========================================
    echo.
    echo APK Location:
    echo %CD%\build\app\outputs\flutter-apk\app-release.apk
    echo.
    if exist "build\app\outputs\flutter-apk\app-release.apk" (
        echo Opening folder...
        explorer "build\app\outputs\flutter-apk"
    )
) else (
    echo.
    echo ========================================
    echo   Build Failed
    echo ========================================
    echo.
    echo Please check the error messages above
    echo.
)

echo.
pause
