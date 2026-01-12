@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Starting Laravel CRM on Port 8007
echo ========================================
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo [ERROR] artisan file not found!
    echo Please run this script from the Laravel project root directory.
    echo.
    pause
    exit /b 1
)

REM Check PHP installation
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP is not recognized as a command
    echo.
    echo Common fixes:
    echo   1. Use full path to php.exe:
    echo      "C:\xampp\php\php.exe" artisan serve --port=8007
    echo   2. Add PHP to PATH:
    echo      setx PATH "%%PATH%%;C:\xampp\php"
    echo      (Then close and reopen CMD)
    echo   3. Check if PHP is installed:
    echo      where php
    echo.
    
    REM Try common PHP paths
    set "PHP_PATHS=C:\xampp\php\php.exe;C:\php\php.exe;C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe"
    set "PHP_FOUND=0"
    
    for %%P in (%PHP_PATHS%) do (
        if exist "%%P" (
            echo [FOUND] PHP at: %%P
            echo Using this PHP installation...
            echo.
            set "PHP_CMD=%%P"
            set "PHP_FOUND=1"
            goto :check_port
        )
    )
    
    if !PHP_FOUND! EQU 0 (
        echo [ERROR] Could not find PHP installation
        echo Please install PHP or add it to PATH
        echo.
        pause
        exit /b 1
    )
) else (
    set "PHP_CMD=php"
)

:check_port
REM Check if port 8007 is already in use
netstat -ano | findstr ":8007" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [WARNING] Port 8007 appears to be in use
    echo.
    echo Checking which process is using port 8007...
    for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":8007" ^| findstr "LISTENING"') do (
        echo Process ID: %%a
        tasklist /FI "PID eq %%a" /FO TABLE
        echo.
        set /p KILL_PROCESS="Kill this process and continue? (Y/N): "
        if /i "!KILL_PROCESS!"=="Y" (
            taskkill /PID %%a /F >nul 2>&1
            echo Process killed. Continuing...
            echo.
            goto :check_env
        ) else (
            echo.
            set /p USE_DIFFERENT_PORT="Use a different port? (Y/N): "
            if /i "!USE_DIFFERENT_PORT!"=="Y" (
                set /p ALT_PORT="Enter port number (default 8008): "
                if "!ALT_PORT!"=="" set "ALT_PORT=8008"
                set "LARAVEL_PORT=!ALT_PORT!"
                goto :check_env
            ) else (
                echo Exiting. Please free port 8007 or choose another port.
                pause
                exit /b 1
            )
        )
    )
)

if not defined LARAVEL_PORT set "LARAVEL_PORT=8007"

:check_env
REM Check if .env exists
if not exist ".env" (
    echo [WARNING] .env file not found!
    echo.
    echo Creating .env from .env.example if it exists...
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo [OK] .env file created
        echo.
        echo [IMPORTANT] Please configure your .env file before continuing!
        echo At minimum, set:
        echo   - APP_URL=http://localhost:%LARAVEL_PORT%
        echo   - Database credentials
        echo.
        pause
    ) else (
        echo [ERROR] .env.example not found. Cannot create .env automatically.
        pause
        exit /b 1
    )
)

REM Check for application key
findstr /C:"APP_KEY=" .env | findstr /V "APP_KEY=$" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [INFO] Generating application key...
    %PHP_CMD% artisan key:generate
    echo.
)

REM Check Windows Firewall
echo ========================================
echo Checking Windows Firewall
echo ========================================
echo.
netsh advfirewall firewall show rule name="Laravel Port %LARAVEL_PORT%" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [INFO] Adding Windows Firewall rule for port %LARAVEL_PORT%...
    netsh advfirewall firewall add rule name="Laravel Port %LARAVEL_PORT%" dir=in action=allow protocol=TCP localport=%LARAVEL_PORT% >nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        echo [OK] Firewall rule added
    ) else (
        echo [WARNING] Could not add firewall rule automatically
        echo You may need to allow port %LARAVEL_PORT% manually in Windows Firewall
    )
) else (
    echo [OK] Firewall rule already exists
)
echo.

echo ========================================
echo Starting Laravel Server
echo ========================================
echo.
echo Server will start on: http://localhost:%LARAVEL_PORT%
echo.
echo [IMPORTANT] Keep this window open while the server is running!
echo.
echo To stop the server, press Ctrl+C
echo.
echo ========================================
echo.

REM Start Laravel server
%PHP_CMD% artisan serve --port=%LARAVEL_PORT%

endlocal
