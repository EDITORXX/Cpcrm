@echo off
cd /d "%~dp0"
if not exist .env (
    echo .env not found. Run setup.bat first.
    pause
    exit /b 1
)
echo Starting Laravel on http://localhost:8007
echo Press Ctrl+C to stop.
php artisan serve --port=8007 --host=127.0.0.1
pause
