@echo off
chcp 65001 >nul
title Complete Setup - APK Builder
color 0E
cls

echo ╔══════════════════════════════════════════════════════════╗
echo ║     Telecaller CRM - Complete APK Setup & Build         ║
echo ╚══════════════════════════════════════════════════════════╝
echo.

REM Get current directory
cd /d "%~dp0"

echo [STEP 1/5] Checking Flutter Installation...
echo.

where flutter >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [✓] Flutter is installed
    flutter --version | findstr /C:"Flutter"
    echo.
    goto :check_android
)

echo [✗] Flutter is NOT installed
echo.
echo ========================================
echo   Flutter Installation Required
echo ========================================
echo.
echo Please choose an option:
echo.
echo 1. Auto-download Flutter (Recommended)
echo 2. Manual installation guide
echo 3. Exit
echo.
set /p install_choice="Enter choice (1/2/3): "

if "%install_choice%"=="1" (
    call :install_flutter
) else if "%install_choice%"=="2" (
    call :manual_install_guide
    pause
    exit /b 1
) else (
    exit /b 0
)

:check_android
echo.
echo [STEP 2/5] Checking Android Setup...
echo.

flutter doctor --android-licenses >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [✓] Android licenses accepted
) else (
    echo [⚠] Android licenses need to be accepted
    echo.
    set /p accept_licenses="Accept Android licenses now? (Y/N): "
    if /i "!accept_licenses!"=="Y" (
        flutter doctor --android-licenses
    )
)

echo.
echo [STEP 3/5] Checking Dependencies...
echo.

if not exist "pubspec.yaml" (
    echo [✗] Not in Flutter app directory
    echo Current directory: %CD%
    pause
    exit /b 1
)

echo [✓] Flutter project found
echo.

echo [STEP 4/5] Installing Dependencies...
echo.

flutter pub get
if %ERRORLEVEL% NEQ 0 (
    echo [✗] Failed to get dependencies
    pause
    exit /b 1
)

echo [✓] Dependencies installed
echo.

echo [STEP 5/5] Building APK...
echo.
echo This may take 5-10 minutes. Please wait...
echo.

flutter clean >nul 2>&1
flutter build apk --release

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ╔══════════════════════════════════════════════════════════╗
    echo ║              ✓ APK BUILT SUCCESSFULLY!                  ║
    echo ╚══════════════════════════════════════════════════════════╝
    echo.
    
    set APK_PATH=%CD%\build\app\outputs\flutter-apk\app-release.apk
    
    if exist "%APK_PATH%" (
        echo APK Location:
        echo %APK_PATH%
        echo.
        echo File Size:
        for %%A in ("%APK_PATH%") do echo %%~zA bytes
        echo.
        echo Opening folder...
        explorer "build\app\outputs\flutter-apk"
        echo.
        echo ╔══════════════════════════════════════════════════════════╗
        echo ║  Next Steps:                                             ║
        echo ║  1. Transfer APK to your Android phone                    ║
        echo ║  2. Enable "Install from Unknown Sources"                ║
        echo ║  3. Install the APK                                      ║
        echo ║  4. Start Laravel server: START_SERVER.bat               ║
        echo ╚══════════════════════════════════════════════════════════╝
    )
) else (
    echo.
    echo ╔══════════════════════════════════════════════════════════╗
    echo ║              ✗ BUILD FAILED                             ║
    echo ╚══════════════════════════════════════════════════════════╝
    echo.
    echo Please check the error messages above
    echo.
    echo Common solutions:
    echo - Run: flutter doctor
    echo - Install Android Studio
    echo - Accept Android licenses: flutter doctor --android-licenses
    echo.
)

echo.
pause
exit /b 0

:install_flutter
echo.
echo Downloading Flutter SDK...
echo This may take 5-10 minutes depending on your internet speed...
echo.

if not exist "C:\flutter" mkdir "C:\flutter"

echo Downloading from: https://storage.googleapis.com/flutter_infra_release/releases/stable/windows/flutter_windows_3.24.0-stable.zip
echo.

powershell -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $ProgressPreference = 'SilentlyContinue'; Invoke-WebRequest -Uri 'https://storage.googleapis.com/flutter_infra_release/releases/stable/windows/flutter_windows_3.24.0-stable.zip' -OutFile '%TEMP%\flutter.zip'}"

if not exist "%TEMP%\flutter.zip" (
    echo [✗] Download failed
    echo Please download manually from: https://flutter.dev/docs/get-started/install/windows
    pause
    exit /b 1
)

echo Extracting Flutter...
powershell -Command "Expand-Archive -Path '%TEMP%\flutter.zip' -DestinationPath 'C:\' -Force"

if exist "C:\flutter\bin\flutter.bat" (
    echo [✓] Flutter extracted successfully
    echo.
    echo [IMPORTANT] Adding Flutter to PATH...
    echo.
    
    REM Add to PATH for current session
    set PATH=%PATH%;C:\flutter\bin
    
    echo Flutter added to PATH for this session
    echo.
    echo [IMPORTANT] To make it permanent:
    echo 1. Press Win+R
    echo 2. Type: sysdm.cpl
    echo 3. Advanced ^> Environment Variables
    echo 4. Edit PATH ^> Add: C:\flutter\bin
    echo 5. Restart terminal
    echo.
    
    REM Verify installation
    C:\flutter\bin\flutter.bat --version
    echo.
    
    echo Continuing with build...
    echo.
    goto :check_android
) else (
    echo [✗] Extraction failed
    echo Please extract manually to C:\flutter
    pause
    exit /b 1
)

:manual_install_guide
echo.
echo ========================================
echo   Manual Installation Guide
echo ========================================
echo.
echo 1. Download Flutter SDK:
echo    https://flutter.dev/docs/get-started/install/windows
echo.
echo 2. Extract to: C:\flutter
echo.
echo 3. Add to PATH:
echo    - Press Win+R
echo    - Type: sysdm.cpl
echo    - Go to: Advanced ^> Environment Variables
echo    - Edit PATH variable
echo    - Add: C:\flutter\bin
echo    - Click OK on all dialogs
echo.
echo 4. Restart terminal/command prompt
echo.
echo 5. Run this script again
echo.
goto :eof

