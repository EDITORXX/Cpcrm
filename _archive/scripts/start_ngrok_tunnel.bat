@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Starting ngrok Tunnel for Port 8007
echo ========================================
echo.

REM Check if Laravel is running on port 8007
echo Checking if Laravel is running on port 8007...
netstat -ano | findstr ":8007" | findstr "LISTENING" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] Port 8007 does not appear to be in use
    echo.
    echo Make sure Laravel server is running first!
    echo Run: start_laravel_ngrok.bat
    echo.
    set /p CONTINUE_ANYWAY="Continue anyway? (Y/N): "
    if /i "!CONTINUE_ANYWAY!" NEQ "Y" (
        exit /b 1
    )
    echo.
) else (
    echo [OK] Laravel server detected on port 8007
    echo.
)

REM Check if ngrok is installed
where ngrok >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] ngrok is not found in PATH
    echo.
    
    REM Check common installation locations
    set "NGROK_PATHS=C:\ngrok\ngrok.exe;C:\Program Files\ngrok\ngrok.exe;C:\Program Files (x86)\ngrok\ngrok.exe;%USERPROFILE%\ngrok\ngrok.exe"
    set "NGROK_FOUND=0"
    
    for %%P in (%NGROK_PATHS%) do (
        if exist "%%P" (
            echo [FOUND] ngrok at: %%P
            set "NGROK_CMD=%%P"
            set "NGROK_FOUND=1"
            goto :check_auth
        )
    )
    
    if !NGROK_FOUND! EQU 0 (
        echo Please install ngrok first:
        echo   1. Run: setup_ngrok.bat
        echo   2. Or download from: https://ngrok.com/download
        echo.
        pause
        exit /b 1
    )
) else (
    set "NGROK_CMD=ngrok"
)

:check_auth
REM Check if ngrok is authenticated
echo Checking ngrok configuration...
%NGROK_CMD% config check >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] ngrok may not be configured
    echo.
    echo Please configure ngrok with your authtoken:
    echo   %NGROK_CMD% config add-authtoken YOUR_AUTHTOKEN
    echo.
    echo Get your authtoken from: https://dashboard.ngrok.com/get-started/your-authtoken
    echo.
    set /p HAS_AUTHTOKEN="Do you want to configure ngrok now? (Y/N): "
    if /i "!HAS_AUTHTOKEN!"=="Y" (
        set /p AUTHTOKEN="Enter your ngrok authtoken: "
        if not "!AUTHTOKEN!"=="" (
            %NGROK_CMD% config add-authtoken !AUTHTOKEN!
            if %ERRORLEVEL% EQU 0 (
                echo [SUCCESS] ngrok configured!
                echo.
            ) else (
                echo [ERROR] Failed to configure ngrok
                pause
                exit /b 1
            )
        )
    ) else (
        echo Please configure ngrok before continuing.
        pause
        exit /b 1
    )
) else (
    echo [OK] ngrok is configured
    echo.
)

REM Check if port 4040 is available (ngrok inspector)
netstat -ano | findstr ":4040" | findstr "LISTENING" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [INFO] Port 4040 is in use (ngrok inspector may already be running)
    echo.
    set /p USE_DIFFERENT_PORT="Use different port for inspector? (Y/N): "
    if /i "!USE_DIFFERENT_PORT!"=="Y" (
        set /p INSPECTOR_PORT="Enter port number (default 4041): "
        if "!INSPECTOR_PORT!"=="" set "INSPECTOR_PORT=4041"
        set "NGROK_ARGS=--web-addr=localhost:!INSPECTOR_PORT!"
        echo.
        echo Inspector will be available at: http://localhost:!INSPECTOR_PORT!
    ) else (
        set "NGROK_ARGS="
    )
) else (
    set "NGROK_ARGS="
)

echo ========================================
echo Starting ngrok Tunnel
echo ========================================
echo.
echo Forwarding: localhost:8007 -^> Public URL
echo.
echo [IMPORTANT] Keep this window open while ngrok is running!
echo.
echo Once started, you will see:
echo   - Public HTTPS URL (use this to access your app)
echo   - Public HTTP URL (alternative)
echo.
echo ngrok Request Inspector will be available at:
if defined INSPECTOR_PORT (
    echo   http://localhost:%INSPECTOR_PORT%
) else (
    echo   http://127.0.0.1:4040
)
echo.
echo To stop ngrok, press Ctrl+C
echo.
echo ========================================
echo.

REM Start ngrok
if defined NGROK_ARGS (
    %NGROK_CMD% http 8007 %NGROK_ARGS%
) else (
    %NGROK_CMD% http 8007
)

endlocal
