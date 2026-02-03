@echo off
chcp 65001 >nul
title Fresh APK Build
color 0B

echo ========================================
echo   Fresh APK Build (Clean Start)
echo ========================================
echo.

REM Kill stuck processes first
echo Step 0: Killing stuck Java processes...
taskkill /F /IM java.exe /T 2>nul
timeout /t 2 /nobreak >nul
echo.

REM Set PATH
set PATH=%PATH%;C:\flutter\bin
set ANDROID_HOME=%LOCALAPPDATA%\Android\Sdk

REM Navigate to app directory
cd /d "%~dp0"

echo Step 1: Cleaning Gradle cache...
if exist "%USERPROFILE%\.gradle\caches" (
    rmdir /s /q "%USERPROFILE%\.gradle\caches" 2>nul
    echo [✓] Gradle cache cleared
) else (
    echo [i] Gradle cache already clean
)
echo.

echo Step 2: Flutter clean...
flutter clean
echo.

echo Step 3: Getting dependencies...
flutter pub get
echo.

echo Step 4: Building APK...
echo.
echo IMPORTANT: This will take 3-5 minutes
echo Please wait, do NOT close this window
echo.

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
    if exist "build\app\outputs\flutter-apk\app-release.apk" (
        explorer "build\app\outputs\flutter-apk"
    )
) else (
    echo.
    echo ========================================
    echo   ✗ Build Failed
    echo ========================================
    echo.
    echo Please check error messages above
    echo.
)

echo.
pause
