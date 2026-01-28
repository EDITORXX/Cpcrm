@echo off
echo ========================================
echo Laravel Scheduler - Windows Task Setup
echo ========================================
echo.
echo This will create a Windows Task that runs every minute.
echo You MUST run this as Administrator!
echo.
echo Right-click this file and select "Run as administrator"
echo OR
echo Press any key to try creating the task now...
pause

set "TASK_NAME=Laravel Scheduler"
set "SCRIPT_PATH=%~dp0"
set "PHP_PATH=php"

REM Try to get full path to PHP
where php >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo WARNING: PHP not found in PATH!
    echo Please make sure PHP is installed and in your system PATH.
    echo.
    set "PHP_PATH=C:\xampp\php\php.exe"
    if exist "%PHP_PATH%" (
        echo Using PHP from: %PHP_PATH%
    ) else (
        echo ERROR: Could not find PHP!
        echo Please update PHP_PATH in this script.
        pause
        exit /b 1
    )
)

echo.
echo Creating scheduled task...
echo Task Name: %TASK_NAME%
echo Script Path: %SCRIPT_PATH%
echo PHP Path: %PHP_PATH%
echo.

REM Create the task
schtasks /create /tn "%TASK_NAME%" /tr "cd /d \"%SCRIPT_PATH%\" && \"%PHP_PATH%\" artisan schedule:run" /sc minute /mo 1 /f

if %errorLevel% equ 0 (
    echo.
    echo ========================================
    echo SUCCESS! Task created successfully!
    echo ========================================
    echo.
    echo The Laravel Scheduler will now run automatically every minute.
    echo No window needed - it runs in the background.
    echo.
    echo To verify the task:
    echo   schtasks /query /tn "%TASK_NAME%"
    echo.
    echo To delete the task later:
    echo   schtasks /delete /tn "%TASK_NAME%" /f
    echo.
    echo Testing the task now...
    timeout /t 2 /nobreak >nul
    schtasks /run /tn "%TASK_NAME%"
    echo.
    echo Task triggered! Check if it's working.
) else (
    echo.
    echo ========================================
    echo ERROR: Failed to create task!
    echo ========================================
    echo.
    echo Possible reasons:
    echo   1. Not running as Administrator
    echo   2. Task already exists (use /f flag to overwrite)
    echo   3. PHP path is incorrect
    echo.
    echo Try running this file as Administrator:
    echo   Right-click -> Run as administrator
    echo.
)

echo.
pause
