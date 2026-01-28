# Setup Instructions for Port 8007

## Quick Setup Guide

### Step 1: Create MySQL Database

Open MySQL command line or phpMyAdmin and run:

```sql
CREATE DATABASE realtorcrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or using MySQL command line:
```bash
mysql -u root -p -e "CREATE DATABASE realtorcrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 2: Configure Database Credentials

If your MySQL username/password is different from root, edit `.env` file:
```env
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 3: Generate Application Key

```bash
php artisan key:generate
```

### Step 4: Run Migrations

```bash
php artisan migrate
```

### Step 5: Seed Roles

```bash
php artisan db:seed
```

### Step 6: Create Admin User

```bash
php artisan tinker
```

Then run:
```php
$role = App\Models\Role::where('slug', 'admin')->first();
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@realtorcrm.com',
    'password' => Hash::make('admin123'),
    'role_id' => $role->id,
    'is_active' => true,
]);
exit
```

### Step 7: Start the Server on Port 8007

```bash
php artisan serve --port=8007
```

## Accessible Links

Once the server is running, you can access:

- **Main Website**: http://localhost:8007
- **API Base URL**: http://localhost:8007/api
- **Dashboard**: http://localhost:8007/dashboard (requires login)
- **API Login**: http://localhost:8007/api/login

## Test API Login

Use the admin credentials created above:

```bash
curl -X POST http://localhost:8007/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@realtorcrm.com","password":"admin123"}'
```

## Additional Setup (Optional)

### Start Queue Worker (for background jobs)

Open a new terminal:
```bash
php artisan queue:work
```

### Build Frontend Assets (if needed)

```bash
npm install
npm run build
```

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running
- Verify database `realtorcrm` exists
- Check username/password in `.env`

### Port Already in Use
If port 8007 is already in use, you can use a different port:
```bash
php artisan serve --port=8008
```
Then update `APP_URL` in `.env` accordingly.

### Permission Errors
```bash
chmod -R 775 storage bootstrap/cache
```

