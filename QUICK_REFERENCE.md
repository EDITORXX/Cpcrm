# Quick Reference - Realtor CRM on Port 8007

## 🚀 Quick Start Commands

### 1. Create Database
```sql
CREATE DATABASE realtorcrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run Setup Script (Windows)
```bash
setup.bat
```

### 3. Start Server
```bash
php artisan serve --port=8007
```
OR use the batch file:
```bash
start_server.bat
```

## 🌐 Accessible Website Links

Once server is running on port 8007:

| Link | Description |
|------|-------------|
| **http://localhost:8007** | Main website homepage |
| **http://localhost:8007/dashboard** | Dashboard (requires login) |
| **http://localhost:8007/api** | API base endpoint |
| **http://localhost:8007/api/login** | API login endpoint |
| **http://localhost:8007/api/dashboard** | API dashboard data |
| **http://localhost:8007/api/leads** | API leads endpoint |

## 📋 Database Information

- **Database Name**: `realtorcrm`
- **Host**: `127.0.0.1` (localhost)
- **Port**: `3306`
- **Default Username**: `root`
- **Default Password**: (empty - update in .env if needed)

## 🔑 Default Admin Credentials

After creating admin user:
- **Email**: admin@realtorcrm.com
- **Password**: admin123

(Change these after first login!)

## 📝 Manual .env Configuration

If setup script doesn't work, manually create `.env` file with:

```env
APP_NAME="Realtor CRM"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8007

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=realtorcrm
DB_USERNAME=root
DB_PASSWORD=
```

Then run:
```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
```

## 🧪 Test API

### Login Request
```bash
curl -X POST http://localhost:8007/api/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"admin@realtorcrm.com\",\"password\":\"admin123\"}"
```

### Get Dashboard (after login, use token from login response)
```bash
curl -X GET http://localhost:8007/api/dashboard ^
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## ⚙️ Server Management

### Start Server
```bash
php artisan serve --port=8007
```

### Start Queue Worker (separate terminal)
```bash
php artisan queue:work
```

### Stop Server
Press `Ctrl+C` in the terminal running the server

## 🔧 Troubleshooting

**Port 8007 already in use?**
- Use different port: `php artisan serve --port=8008`
- Update `APP_URL` in `.env` to match

**Database connection error?**
- Check MySQL is running
- Verify database `realtorcrm` exists
- Check `.env` credentials

**Permission errors?**
```bash
chmod -R 775 storage bootstrap/cache
```

## 📚 Full Documentation

- **Setup Guide**: See `SETUP_PORT_8007.md`
- **API Documentation**: See `API_DOCUMENTATION.md`
- **Installation**: See `INSTALLATION.md`
- **Quick Start**: See `QUICK_START.md`

