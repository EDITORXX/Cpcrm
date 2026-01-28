# Quick Start Guide

## Prerequisites Check

Before starting, ensure you have:
- ✅ PHP 8.1 or higher
- ✅ Composer installed
- ✅ MySQL/MariaDB installed and running
- ✅ Redis installed and running
- ✅ Node.js and NPM installed

## 5-Minute Setup

### Step 1: Install Dependencies (2 minutes)

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 2: Configure Environment (1 minute)

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` and set:
```env
DB_DATABASE=real_estate_crm
DB_USERNAME=root
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Step 3: Setup Database (1 minute)

```bash
# Create database (run in MySQL)
mysql -u root -p
CREATE DATABASE real_estate_crm;
exit;

# Run migrations
php artisan migrate

# Seed roles
php artisan db:seed
```

### Step 4: Create Admin User (1 minute)

```bash
php artisan tinker
```

Then run:
```php
$role = App\Models\Role::where('slug', 'admin')->first();
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role_id' => $role->id,
    'is_active' => true,
]);
exit
```

### Step 5: Start Development Server

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start queue worker
php artisan queue:work

# Terminal 3: Build frontend (optional)
npm run dev
```

## Access the Application

- **Web Interface**: http://localhost:8000
- **API Base URL**: http://localhost:8000/api

## Test API with cURL

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

Save the token from response.

### Get Dashboard
```bash
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Create Lead
```bash
curl -X POST http://localhost:8000/api/leads \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "phone": "+1234567890",
    "email": "john@example.com",
    "source": "website"
  }'
```

## Next Steps

1. **Configure Pusher** for real-time features (see INSTALLATION.md)
2. **Create more users** with different roles
3. **Explore the API** using the API_DOCUMENTATION.md
4. **Customize** the system for your needs

## Common Issues

### "Class not found" errors
```bash
composer dump-autoload
```

### Database connection error
- Check MySQL is running
- Verify credentials in `.env`
- Ensure database exists

### Permission denied errors
```bash
chmod -R 775 storage bootstrap/cache
```

### Queue not working
- Ensure Redis is running
- Check `.env` QUEUE_CONNECTION=redis
- Start queue worker: `php artisan queue:work`

## Production Deployment

See `INSTALLATION.md` for detailed production deployment instructions.

