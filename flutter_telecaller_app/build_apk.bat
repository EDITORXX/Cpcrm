@echo off
echo Building Flutter APK...
echo.

REM Check if Flutter is installed
where flutter >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Flutter is not installed or not in PATH
    echo Please install Flutter from https://flutter.dev/docs/get-started/install
    pause
    exit /b 1
)

echo Flutter found!
echo.

REM Get Flutter version
flutter --version
echo.

REM Clean previous builds
echo Cleaning previous builds...
flutter clean
echo.

REM Get dependencies
echo Getting dependencies...
flutter pub get
echo.

REM Build APK
echo Building APK...
flutter build apk --release
echo.

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo APK built successfully!
    echo ========================================
    echo.
    echo APK location: build\app\outputs\flutter-apk\app-release.apk
    echo.
    echo You can find the APK file in the build\app\outputs\flutter-apk\ folder
    echo.
) else (
    echo.
    echo ========================================
    echo APK build failed!
    echo ========================================
    echo.
    echo Please check the error messages above
    echo.
)

pause

