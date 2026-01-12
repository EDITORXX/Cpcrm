@echo off
echo ========================================
echo Laravel CRM - Local Server Setup
echo ========================================
echo.

cd /d "c:\Users\vivek\Pictures\Laravel crm fully functional"

echo [1/6] Checking PHP...
php --version
if %errorlevel% neq 0 (
    echo ERROR: PHP not found! Please install PHP.
    pause
    exit /b 1
)
echo.

echo [2/6] Checking .env file...
if not exist .env (
    echo .env file not found! Creating from .env.example...
    if exist .env.example (
        copy .env.example .env
        echo .env file created!
    ) else (
        echo ERROR: .env.example not found!
        pause
        exit /b 1
    )
) else (
    echo .env file exists!
)
echo.

echo [3/6] Checking APP_KEY...
findstr /C:"APP_KEY=" .env | findstr /V "APP_KEY=base64:" >nul
if %errorlevel% equ 0 (
    echo APP_KEY is empty! Generating...
    php artisan key:generate
) else (
    echo APP_KEY is set!
)
echo.

echo [4/6] Installing Composer dependencies...
if not exist vendor (
    echo Installing dependencies...
    composer install
) else (
    echo Vendor folder exists! Skipping install...
)
echo.

echo [5/6] Clearing cache...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo.

echo [6/6] Starting Laravel server on port 8007...
echo.
echo ========================================
echo Server starting at: http://localhost:8007
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php artisan serve --port=8007 --host=127.0.0.1

pause
