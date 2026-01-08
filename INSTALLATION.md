# Installation Guide

## Prerequisites

- PHP >= 8.1
- Composer
- MySQL >= 5.7 or MariaDB >= 10.3
- Redis (for queues and caching)
- Node.js and NPM (for frontend assets)

## Step 1: Install Dependencies

```bash
composer install
npm install
```

## Step 2: Environment Configuration

1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Generate application key:
```bash
php artisan key:generate
```

3. Update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=real_estate_crm
DB_USERNAME=root
DB_PASSWORD=your_password
```

4. Configure Redis:
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

5. Configure Pusher for real-time features:
```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

Or use Laravel WebSockets (self-hosted):
```env
BROADCAST_DRIVER=pusher
# Configure Laravel WebSockets package
```

## Step 3: Database Setup

1. Create the database:
```sql
CREATE DATABASE real_estate_crm;
```

2. Run migrations:
```bash
php artisan migrate
```

3. Seed the database with roles:
```bash
php artisan db:seed
```

## Step 4: Create Admin User

Create an admin user manually or use a seeder:

```bash
php artisan tinker
```

Then:
```php
$role = App\Models\Role::where('slug', 'admin')->first();
$user = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role_id' => $role->id,
    'is_active' => true,
]);
```

## Step 5: Build Frontend Assets

```bash
npm run build
```

For development:
```bash
npm run dev
```

## Step 6: Start Queue Worker

For background jobs and notifications:

```bash
php artisan queue:work
```

Or use supervisor for production.

## Step 7: Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Step 8: Configure WebSocket Server

### Option 1: Using Pusher (Cloud)

1. Sign up at https://pusher.com
2. Create a new app
3. Copy credentials to `.env`

### Option 2: Using Laravel WebSockets (Self-hosted)

1. Install Laravel WebSockets:
```bash
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
php artisan migrate
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"
```

2. Start WebSocket server:
```bash
php artisan websockets:serve
```

3. Update `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

## Production Deployment

### 1. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 2. Set Up Queue Workers

Use supervisor to manage queue workers:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

### 3. Set Up WebSocket Server (if using Laravel WebSockets)

```ini
[program:laravel-websockets]
command=php /path/to/artisan websockets:serve --host=0.0.0.0 --port=6001
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/websockets.log
```

### 4. Configure Nginx

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5. SSL Certificate

Use Let's Encrypt for free SSL:

```bash
sudo certbot --nginx -d your-domain.com
```

## Troubleshooting

### Permission Issues

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Queue Not Processing

- Check Redis connection
- Ensure queue worker is running
- Check queue configuration in `.env`

### WebSocket Not Working

- Verify Pusher credentials
- Check firewall rules for WebSocket port
- Ensure CORS is configured correctly
- Check browser console for errors

### Database Connection Issues

- Verify database credentials in `.env`
- Ensure MySQL is running
- Check database exists
- Verify user has proper permissions

