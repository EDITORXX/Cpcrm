@echo off
chcp 65001 >nul
title Flutter Auto Setup
color 0B

echo ========================================
echo   Flutter Auto Setup Script
echo ========================================
echo.
echo This script will help you set up Flutter
echo.

REM Check if Flutter is already installed
where flutter >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [✓] Flutter is already installed!
    flutter --version
    echo.
    echo You can now run: INSTALL_AND_BUILD.bat
    pause
    exit /b 0
)

echo [INFO] Flutter is not installed
echo.
echo ========================================
echo   Option 1: Manual Installation (Recommended)
echo ========================================
echo.
echo 1. Download Flutter SDK:
echo    https://flutter.dev/docs/get-started/install/windows
echo.
echo 2. Extract to C:\flutter
echo.
echo 3. Add to PATH:
echo    - Win+R ^> sysdm.cpl
echo    - Advanced ^> Environment Variables
echo    - Edit PATH ^> Add: C:\flutter\bin
echo.
echo 4. Restart terminal and run: INSTALL_AND_BUILD.bat
echo.
echo ========================================
echo   Option 2: Quick Download Script
echo ========================================
echo.
set /p choice="Do you want to download Flutter automatically? (Y/N): "

if /i "%choice%"=="Y" (
    echo.
    echo Downloading Flutter SDK...
    echo This may take a few minutes...
    echo.
    
    REM Create flutter directory
    if not exist "C:\flutter" mkdir "C:\flutter"
    
    REM Download Flutter (using PowerShell)
    powershell -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://storage.googleapis.com/flutter_infra_release/releases/stable/windows/flutter_windows_3.24.0-stable.zip' -OutFile '%TEMP%\flutter.zip'}"
    
    if exist "%TEMP%\flutter.zip" (
        echo Extracting Flutter...
        powershell -Command "Expand-Archive -Path '%TEMP%\flutter.zip' -DestinationPath 'C:\' -Force"
        
        echo.
        echo [✓] Flutter downloaded and extracted to C:\flutter
        echo.
        echo [IMPORTANT] Add C:\flutter\bin to your PATH:
        echo 1. Press Win+R
        echo 2. Type: sysdm.cpl
        echo 3. Advanced ^> Environment Variables
        echo 4. Edit PATH ^> Add: C:\flutter\bin
        echo 5. Restart terminal
        echo.
        echo Then run: INSTALL_AND_BUILD.bat
    ) else (
        echo [✗] Download failed. Please download manually.
    )
) else (
    echo.
    echo Please install Flutter manually using Option 1
)

echo.
pause

