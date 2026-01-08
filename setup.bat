@echo off
echo ========================================
echo Realtor CRM Setup Script
echo ========================================
echo.

echo Step 1: Creating .env file...
if not exist .env (
    echo APP_NAME="Realtor CRM" > .env
    echo APP_ENV=local >> .env
    echo APP_KEY= >> .env
    echo APP_DEBUG=true >> .env
    echo APP_URL=http://localhost:8007 >> .env
    echo. >> .env
    echo LOG_CHANNEL=stack >> .env
    echo LOG_DEPRECATIONS_CHANNEL=null >> .env
    echo LOG_LEVEL=debug >> .env
    echo. >> .env
    echo DB_CONNECTION=mysql >> .env
    echo DB_HOST=127.0.0.1 >> .env
    echo DB_PORT=3306 >> .env
    echo DB_DATABASE=realtorcrm >> .env
    echo DB_USERNAME=root >> .env
    echo DB_PASSWORD= >> .env
    echo. >> .env
    echo BROADCAST_DRIVER=pusher >> .env
    echo CACHE_DRIVER=redis >> .env
    echo FILESYSTEM_DISK=local >> .env
    echo QUEUE_CONNECTION=redis >> .env
    echo SESSION_DRIVER=redis >> .env
    echo SESSION_LIFETIME=120 >> .env
    echo. >> .env
    echo REDIS_HOST=127.0.0.1 >> .env
    echo REDIS_PASSWORD=null >> .env
    echo REDIS_PORT=6379 >> .env
    echo. >> .env
    echo PUSHER_APP_ID= >> .env
    echo PUSHER_APP_KEY= >> .env
    echo PUSHER_APP_SECRET= >> .env
    echo PUSHER_HOST= >> .env
    echo PUSHER_PORT=443 >> .env
    echo PUSHER_SCHEME=https >> .env
    echo PUSHER_APP_CLUSTER=mt1 >> .env
    echo .env file created!
) else (
    echo .env file already exists. Skipping...
)
echo.

echo Step 2: Generating application key...
php artisan key:generate
echo.

echo Step 3: Please create the MySQL database 'realtorcrm' manually:
echo    mysql -u root -p -e "CREATE DATABASE realtorcrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo    OR use phpMyAdmin to create database: realtorcrm
echo.
pause

echo Step 4: Running migrations...
php artisan migrate
echo.

echo Step 5: Seeding roles...
php artisan db:seed
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Create admin user: php artisan tinker
echo    Then run: (see SETUP_PORT_8007.md for commands)
echo.
echo 2. Start server on port 8007:
echo    php artisan serve --port=8007
echo.
echo 3. Access website at: http://localhost:8007
echo.
pause

