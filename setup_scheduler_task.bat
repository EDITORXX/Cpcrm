@echo off
echo Setting up Windows Task Scheduler for Laravel...
echo.
echo This will create a task that runs every minute.
echo You need to run this as Administrator.
echo.

set "TASK_NAME=Laravel Scheduler"
set "SCRIPT_PATH=%~dp0"
set "PHP_PATH=php"

REM Check if running as admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator!
    echo Right-click and select "Run as administrator"
    pause
    exit /b 1
)

echo Creating scheduled task...
schtasks /create /tn "%TASK_NAME%" /tr "cd /d \"%SCRIPT_PATH%\" && %PHP_PATH% artisan schedule:run" /sc minute /mo 1 /f

if %errorLevel% equ 0 (
    echo.
    echo SUCCESS! Task created successfully.
    echo The scheduler will now run automatically every minute.
    echo.
    echo To verify, run: schtasks /query /tn "%TASK_NAME%"
    echo To delete the task later, run: schtasks /delete /tn "%TASK_NAME%" /f
) else (
    echo.
    echo ERROR: Failed to create task.
    echo Make sure you're running as Administrator.
)

pause
