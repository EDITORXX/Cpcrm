@echo off
echo ============================================
echo Building Simple Flutter APK for Base CRM
echo ============================================
echo.

cd /d "%~dp0"

echo [1/5] Cleaning previous build...
call flutter clean
echo.

echo [2/5] Getting dependencies...
call flutter pub get
echo.

echo [3/5] Building APK (Release Mode)...
call flutter build apk --release
echo.

if exist "build\app\outputs\flutter-apk\app-release.apk" (
    echo [4/5] APK built successfully!
    echo.
    echo [5/5] APK Location:
    echo %cd%\build\app\outputs\flutter-apk\app-release.apk
    echo.
    echo ============================================
    echo BUILD SUCCESSFUL!
    echo ============================================
    echo.
    echo You can install this APK on any Android device.
    echo APK Size: 
    for %%A in ("build\app\outputs\flutter-apk\app-release.apk") do echo %%~zA bytes
    echo.
) else (
    echo [ERROR] APK build failed!
    echo Please check the error messages above.
)

echo.
pause
