# Deployment Zip File Instructions

## Quick Start

### Step 1: Zip File Create Karo

**Option A: Windows (Easiest)**
1. `create_deployment_zip.bat` file ko double-click karo
2. Zip file automatically create ho jayega

**Option B: Command Line**
```bash
php create_deployment_zip.php
```

### Step 2: Zip File Upload Karo

1. Zip file ko server pe upload karo (FTP, cPanel File Manager, ya koi bhi method)
2. Server pe extract karo (unzip)
3. Web root directory me extract karo (jahan public folder accessible ho)

### Step 3: Server Pe Setup Karo

**Important Commands (SSH se ya cPanel Terminal se):**

```bash
# 1. Storage permissions set karo
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 2. Storage link create karo
php artisan storage:link

# 3. NPM install (agar frontend assets build karne hain - optional)
npm install
npm run build
```

**Note:** Vendor folder already included hai - composer install ki zarurat nahi hai!

### Step 4: Installation Wizard

1. Browser me `yoursite.com/install` open karo
2. Installation wizard follow karo
3. Database details fill karo
4. Admin user create karo
5. Done!

---

## Zip File Me Kya Hai

### Included (Sab Kuch):
- ✅ `app/` - Application code
- ✅ `bootstrap/` - Bootstrap files (complete)
- ✅ `vendor/` - PHP dependencies (pre-installed)
- ✅ `config/` - Configuration files
- ✅ `database/` - Migrations aur seeders
- ✅ `public/` - Public assets
- ✅ `resources/` - Views aur assets
- ✅ `routes/` - Route files
- ✅ `storage/` - Storage structure (empty, permissions set karni padegi)
- ✅ `.env.example` - Environment template
- ✅ `composer.json` & `composer.lock` - PHP dependencies
- ✅ `package.json` & `package-lock.json` - Node dependencies
- ✅ `artisan` - Laravel CLI
- ✅ Documentation files

### Excluded (Server Pe Install Hoga):
- ❌ `node_modules/` - NPM se install hoga (agar frontend build karna hai)
- ❌ `.env` - Installation wizard se create hoga
- ❌ `.git/` - Git history (optional, include kar sakte ho)
- ❌ Cache files
- ❌ Log files

---

## Server Requirements

### Minimum Requirements:
- PHP >= 8.1
- MySQL/MariaDB
- Web server (Apache/Nginx)
- Node.js & NPM (optional - agar frontend build karna hai)
- **Composer NOT required** - vendor folder already included!

### PHP Extensions Required:
- PDO
- PDO_MySQL
- MBString
- OpenSSL
- Tokenizer
- JSON
- cURL
- FileInfo
- GD

---

## Installation Process Flow

```
1. Zip Upload
   ↓
2. Extract Files
   ↓
3. Set Permissions
   ↓
4. NPM Install (if frontend build needed - optional)
   ↓
5. Open /install
   ↓
6. Fill Database Details
   ↓
7. Create Admin User
   ↓
8. Installation Complete!
   ↓
9. Login & Use System
```

**Note:** Vendor folder already included - no composer install needed!

---

## Important Notes

1. **Vendor Folder:** Already included in zip - no need to run `composer install` on server!
2. **Bootstrap Folder:** Complete bootstrap folder included - ready to use
3. **Permissions:** Storage aur bootstrap/cache writable hona chahiye
4. **Database:** Pehle database create karo, phir installation wizard me details fill karo
5. **.env File:** Installation wizard automatically create karega
6. **Cache:** Installation ke baad cache clear karna zaroori nahi - wizard automatically karega

---

## Troubleshooting

### Problem: Zip file create nahi ho rahi
- **Solution:** PHP ZipArchive extension check karo: `php -m | grep zip`
- **Solution:** File permissions check karo

### Problem: Server pe extract ke baad files missing
- **Solution:** Extract path verify karo
- **Solution:** File permissions check karo

### Problem: Application run nahi ho raha hai
- **Solution:** PHP version check karo (8.1+)
- **Solution:** Vendor folder properly extracted hai ya nahi verify karo
- **Solution:** File permissions check karo (storage aur bootstrap/cache writable hona chahiye)

### Problem: Installation wizard open nahi ho raha
- **Solution:** Web server properly configured hai ya nahi check karo
- **Solution:** `.htaccess` file exists hai ya nahi verify karo
- **Solution:** PHP errors check karo (error_log)

---

## File Size Estimate

- **With vendor folder (included):** ~50-100 MB
- **With vendor + node_modules (if included):** ~200-300 MB
- **Note:** Vendor folder included hai, isliye zip file size thoda bada hoga, lekin server pe kuch download karne ki zarurat nahi hai

---

## Security Checklist

Before uploading to server:
- ✅ `.env` file excluded (safe)
- ✅ Sensitive files excluded
- ✅ Git credentials not included
- ✅ Service account JSON files excluded

After installation:
- ✅ Change default admin password
- ✅ Review file permissions
- ✅ Enable HTTPS
- ✅ Configure firewall

---

**Note:** Zip file me sab kuch ready hai - vendor folder aur bootstrap folder dono included hain! Bas server pe extract karo, permissions set karo, aur installation wizard follow karo. Kuch bhi download karne ki zarurat nahi hai - sab kuch pre-packaged hai!
