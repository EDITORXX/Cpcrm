@echo off
echo ================================================
echo Flutter APK Build - Complete Fix Script
echo ================================================
echo.

REM Kill any running Java/Gradle processes
echo [1/7] Stopping all Java processes...
taskkill /F /IM java.exe /T >nul 2>&1
timeout /t 2 >nul

REM Clear all Gradle caches
echo [2/7] Clearing Gradle cache...
if exist "%USERPROFILE%\.gradle" rmdir /s /q "%USERPROFILE%\.gradle"
if exist "%LOCALAPPDATA%\Temp\gradle*" rmdir /s /q "%LOCALAPPDATA%\Temp\gradle*"

REM Clear Flutter build directories
echo [3/7] Clearing Flutter build cache...
cd /d "%~dp0"
if exist "build" rmdir /s /q "build"
if exist "android\build" rmdir /s /q "android\build"
if exist "android\.gradle" rmdir /s /q "android\.gradle"
flutter clean

REM Set JAVA_HOME
echo [4/7] Setting Java environment...
set "JAVA_HOME=C:\Program Files\Android\Android Studio\jbr"
set "PATH=%JAVA_HOME%\bin;%PATH%"

REM Verify Flutter setup
echo [5/7] Verifying Flutter...
flutter doctor

REM Get dependencies
echo [6/7] Getting Flutter dependencies...
flutter pub get

REM Build APK
echo [7/7] Building APK...
echo This may take 5-10 minutes on first build...
flutter build apk --release

echo.
echo ================================================
if exist "build\app\outputs\flutter-apk\app-release.apk" (
    echo BUILD SUCCESSFUL!
    echo APK Location: %cd%\build\app\outputs\flutter-apk\app-release.apk
    for %%A in (build\app\outputs\flutter-apk\app-release.apk) do echo APK Size: %%~zA bytes
) else (
    echo BUILD FAILED!
    echo Check the errors above.
)
echo ================================================
pause
