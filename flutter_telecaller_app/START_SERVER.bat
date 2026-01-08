@echo off
echo Starting Laravel server for network access...
echo.
echo Server will be accessible at: http://192.168.1.7:8000
echo.
echo Make sure your phone is on the same Wi-Fi network!
echo.
echo Press Ctrl+C to stop the server
echo.

cd ..
php artisan serve --host=0.0.0.0 --port=8000

pause

