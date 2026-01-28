# Hostinger Deployment Guide - [akasa.bihtech.com](http://akasa.bihtech.com)

## 📍 **Server Details:**

- **Domain:** akasa.bihtech.com
- **Path:** /home/u188221078/domains/bihtech.com/public_html/akasa
- **Repository:** [https://github.com/EDITORXX/Cpcrm.git](https://github.com/EDITORXX/Cpcrm.git)

---

## 🚀 **Step-by-Step Deployment Commands**

### **Step 1: SSH से Connect करें और Folder में जाएं**

```bash
# SSH से connect (अगर पहले से नहीं है)
ssh u188221078@akasa.bihtech.com

# Project folder में जाएं
c
```

---

### **Step 2: Git Clone करें**

```bash
# अगर folder empty है तो clone करें
git clone https://github.com/EDITORXX/Cpcrm.git .

# या अगर files already हैं तो pull करें
git pull origin main
```

---

### **Step 3: Composer Install करें**

```bash
# PHP dependencies install करें
composer install --no-dev --optimize-autoloader
```

**Note:** अगर `composer` command नहीं मिल रहा, तो:

```bash
# Composer path check करें
which composer

# या full path use करें
php /usr/local/bin/composer install --no-dev --optimize-autoloader
```

---

### **Step 4: Environment File Setup**

```bash
# .env file copy करें
cp .env.example .env

# या manually create करें
nano .env
```

`**.env` file में ये settings add करें:**

```env
APP_NAME="Real Estate CRM"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://akasa.bihtech.com

# Database (Hostinger cPanel से credentials लें)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u188221078_cpcrm
DB_USERNAME=u188221078_cpcrm
DB_PASSWORD=your_database_password

# Queue - sync mode (Redis नहीं है)
QUEUE_CONNECTION=sync

# Cache - file driver
CACHE_DRIVER=file
SESSION_DRIVER=file

# Broadcasting - Pusher Cloud
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=mt1

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your_email@bihtech.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@bihtech.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

### **Step 5: App Key Generate करें**

```bash
php artisan key:generate
```

---

### **Step 6: Permissions Set करें**

```bash
# Storage और cache folders
chmod -R 775 storage bootstrap/cache
chown -R u188221078:u188221078 storage bootstrap/cache

# Storage link create करें
php artisan storage:link
```

---

### **Step 7: Database Setup**

```bash
# Migrations run करें
php artisan migrate

# Seeders run करें (roles के लिए)
php artisan db:seed
```

---

### **Step 8: Admin User Create करें**

```bash
php artisan tinker
```

फिर ये code run करें:

```php
$role = App\Models\Role::where('slug', 'admin')->first();
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@bihtech.com',
    'password' => Hash::make('your_secure_password'),
    'role_id' => $role->id,
    'is_active' => true,
]);
exit
```

---

### **Step 9: Frontend Assets Build करें (अगर local में नहीं किया)**

```bash
# Node modules install
npm install

# Production build
npm run build
```

**Note:** अगर `npm` नहीं मिल रहा, तो local में build करके upload करें।

---

### **Step 10: Optimize करें**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

### **Step 11: .htaccess File Check करें**

`public` folder में `.htaccess` file होनी चाहिए। अगर नहीं है तो:

```bash
# .htaccess file create करें
nano public/.htaccess
```

**Content:**

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

### **Step 12: Document Root Check करें**

**Important:** Hostinger में document root को `public` folder में point करना होगा।

**Option A: cPanel में Document Root Change करें**

1. cPanel → Domains → akasa.bihtech.com
2. Document Root को `/public_html/akasa/public` में change करें

**Option B: .htaccess से Redirect करें**

`public_html/akasa/.htaccess` (root में):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## 🔧 **Troubleshooting**

### **500 Error:**

```bash
# Logs check करें
tail -f storage/logs/laravel.log

# Permissions check करें
ls -la storage bootstrap/cache
```

### **Database Connection Error:**

- cPanel → MySQL Databases से credentials verify करें
- `.env` में correct credentials check करें

### **Composer/NPM नहीं मिल रहे:**

- Hostinger support से contact करें
- या local में `vendor` और `node_modules` build करके upload करें

### **Composer Lock Compatibility Error (Permanent Fix):**

यह error future में न आए, इसके लिए permanent fix:

**Step 1: Server पर Script Run करें**

```bash
cd /home/u188221078/domains/bihtech.com/public_html/crm

# Script download करें (या manually create करें)
wget https://raw.githubusercontent.com/EDITORXX/Cpcrm/main/deploy-fix-composer.sh
# या manually create करें (नीचे content दिया है)

# Script executable बनाएं
chmod +x deploy-fix-composer.sh

# Script run करें
./deploy-fix-composer.sh
```

**Step 2: Updated composer.lock Commit करें**

```bash
git add composer.lock
git commit -m "Fix: Update composer.lock for PHP 8.2 compatibility"
git push origin main
```

**Step 3: Hostinger Deployment Test करें**

अब Hostinger Git Deployment automatically work करेगा!

**Note:** `composer.json` में PHP platform version already set है (`"platform": {"php": "8.2.28"}`), इसलिए future deployments में यह issue नहीं आएगा।

---

## ✅ **Final Checklist**

- Git clone/pull complete
- Composer install done
- `.env` file configured
- `php artisan key:generate` run किया
- Permissions set किए
- Database migrations run किए
- Admin user create किया
- Assets build किए (अगर needed)
- Optimize commands run किए
- `.htaccess` file check की
- Document root properly configured
- Website test किया

---

## 🎯 **Quick Commands Summary**

```bash
# Complete setup (एक साथ)
cd /home/u188221078/domains/bihtech.com/public_html/akasa
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan key:generate
chmod -R 775 storage bootstrap/cache
php artisan storage:link
php artisan migrate
php artisan db:seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

**🚀 Setup complete होने के बाद website: [https://akasa.bihtech.com](https://akasa.bihtech.com) पर available होगा!le**