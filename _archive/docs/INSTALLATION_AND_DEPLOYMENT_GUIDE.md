# Installation aur Deployment Guide

## Overview

Yeh system 3 main features provide karta hai:

1. **Installation Wizard** - Server pe first time setup ke liye
2. **One-Click Deployment** - Localhost se production server pe update karne ke liye
3. **Settings Manager** - Deploy ke baad settings change karne ke liye

---

## Part 1: Installation Wizard (First Time Setup)

### Step-by-Step Instructions:

#### Step 1: Files Upload Karo
- Server pe files upload karo (FTP, cPanel File Manager, ya koi bhi method)
- **Important:** Vendor folder upload nahi karna - server pe `composer install` run hoga

#### Step 2: Installation Wizard Access Karo
- Browser me `yoursite.com/install` open karo
- Installation wizard automatically dikhega

#### Step 3: Requirements Check (Automatic)
- System automatically check karega:
  - PHP version (8.1+)
  - Required extensions
  - Writable directories
- Agar sab requirements meet ho rahe hain, automatically next step pe jayega

#### Step 4: Database Configuration
- Database details fill karo:
  - **Host:** Usually `127.0.0.1` ya `localhost`
  - **Port:** Usually `3306`
  - **Database Name:** Apna database name
  - **Username:** Database username
  - **Password:** Database password
- "Test Connection" button click karo
- Connection successful hone ke baad "Next" button enable hoga

#### Step 5: Admin User & App Settings
- **Application Name:** Apna app ka name (e.g., "Base CRM")
- **Application URL:** Apna website URL (e.g., `https://yoursite.com`)
- **Admin Name:** Admin user ka name
- **Admin Email:** Admin user ka email
- **Admin Password:** Strong password (minimum 8 characters)

#### Step 6: Installation
- "Next" button click karo
- System automatically:
  - `.env` file create karega
  - App key generate karega
  - Database migrations run karega
  - Roles seed karega
  - Admin user create karega
- Installation complete hone ke baad "Go to Login" button dikhega

### Important Notes:
- Installation complete hone ke baad `/install` route automatically disable ho jayega
- `installed.lock` file create hogi jo re-installation prevent karega
- Agar installation fail ho, to error message dikhega

---

## Part 2: One-Click Deployment (Localhost → Production)

### Prerequisites:
1. Git repository initialize hona chahiye
2. Localhost me Git configured hona chahiye
3. Production server pe Git repository accessible hona chahiye

### Step-by-Step Instructions:

#### Step 1: Localhost me Changes Karo
- Code me changes karo
- Files save karo

#### Step 2: Deployment Dashboard Kholo
- Admin panel me login karo
- Sidebar me "Deployment" link click karo
- Ya directly `/admin/deploy` URL open karo

#### Step 3: Git Status Check Karo
- Dashboard automatically Git status show karega:
  - Current branch
  - Last commit
  - Uncommitted changes (agar hain)

#### Step 4: Deploy Karo
- Agar uncommitted changes hain:
  - "Automatically commit changes" checkbox check karo (optional)
  - Commit message enter karo (optional)
- "Deploy to Server" button click karo
- System automatically:
  - Changes commit karega (agar auto-commit enabled hai)
  - Git push karega
  - Server deployment trigger karega (webhook/SSH se)

#### Step 5: Wait Karo
- Deployment process 30-60 seconds me complete hoga
- Progress bar aur logs dikhenge
- Success message dikhega

### Deployment Methods:

#### Method 1: Webhook (Recommended)
1. Production server pe webhook endpoint setup karo
2. `.env` me add karo:
   ```
   DEPLOYMENT_WEBHOOK_URL=https://yoursite.com/api/deploy/webhook
   ```
3. Webhook automatically trigger hoga jab Git push hoga

#### Method 2: SSH (Advanced)
1. `.env` me SSH details add karo:
   ```
   DEPLOYMENT_SSH_HOST=your-server.com
   DEPLOYMENT_SSH_PORT=22
   DEPLOYMENT_SSH_USERNAME=username
   DEPLOYMENT_SSH_KEY_PATH=/path/to/private/key
   DEPLOYMENT_SSH_DEPLOY_PATH=/path/to/project
   ```
2. SSH se automatically deployment hoga

#### Method 3: Manual
- Agar webhook/SSH configure nahi hai, to:
  - Code Git me push hoga
  - Server pe manually `git pull` karna padega

---

## Part 3: Settings Manager (Post-Deployment)

### Database Settings Update:

#### Steps:
1. Admin panel me "System Settings" kholo
2. "Database Settings" section me jao
3. New database credentials enter karo
4. "Test Connection" button click karo
5. Connection successful hone ke baad "Save Database Settings" click karo
6. System automatically:
   - `.env` file update karega
   - Backup create karega
   - Config cache clear karega

### Environment Variables Update:

#### Steps:
1. "System Settings" me "Environment Variables" section me jao
2. "Load Environment Variables" button click karo
3. All environment variables list me dikhenge
4. Values edit karo
5. "Save Environment Variables" click karo
6. System automatically `.env` file update karega

### Important Notes:
- Sensitive keys (like `APP_KEY`) web interface se change nahi kar sakte (security ke liye)
- Har update se pehle automatic backup create hota hai
- Backup files: `.env.backup.YYYY-MM-DD_HH-ii-ss` format me

---

## Security Features

### Installation Security:
- Installation complete hone ke baad `/install` route disable
- `installed.lock` file se re-installation prevent
- Only first-time setup allowed

### Deployment Security:
- Only admin users can deploy
- Git commit messages sanitized
- Deployment logs maintained
- All operations logged

### Settings Security:
- Only admin users can update settings
- Sensitive keys protected
- Automatic backups before changes
- Database connection tested before saving

---

## Troubleshooting

### Installation Issues:

**Problem:** Requirements check fail ho raha hai
- **Solution:** Server pe required PHP extensions install karo
- **Solution:** Directory permissions check karo (storage aur bootstrap/cache writable hona chahiye)

**Problem:** Database connection fail ho raha hai
- **Solution:** Database credentials verify karo
- **Solution:** Database server running hai ya nahi check karo
- **Solution:** Database user ko proper permissions diye hain ya nahi check karo

**Problem:** Installation stuck ho raha hai
- **Solution:** Browser console me errors check karo
- **Solution:** Server logs check karo (`storage/logs/laravel.log`)
- **Solution:** Page refresh karo aur phir se try karo

### Deployment Issues:

**Problem:** Git status show nahi ho raha
- **Solution:** Git repository initialize karo: `git init`
- **Solution:** Git remote add karo: `git remote add origin <repo-url>`

**Problem:** Git push fail ho raha hai
- **Solution:** Git credentials check karo
- **Solution:** Remote repository access verify karo
- **Solution:** Network connection check karo

**Problem:** Server deployment trigger nahi ho raha
- **Solution:** Webhook URL verify karo
- **Solution:** SSH configuration check karo
- **Solution:** Manual deployment option use karo

### Settings Issues:

**Problem:** Database settings save nahi ho rahe
- **Solution:** `.env` file writable hai ya nahi check karo
- **Solution:** File permissions check karo
- **Solution:** Server logs check karo

**Problem:** Environment variables load nahi ho rahe
- **Solution:** `.env` file exists hai ya nahi check karo
- **Solution:** File permissions verify karo

---

## File Structure

### New Files Created:
```
app/Http/Controllers/InstallController.php
app/Http/Controllers/Admin/DeploymentController.php
app/Services/DeploymentService.php
app/Http/Middleware/CheckInstallation.php
resources/views/install/index.blade.php
resources/views/admin/deployment/index.blade.php
config/deployment.php
database/migrations/2026_01_27_200000_create_deployment_logs_table.php
```

### Modified Files:
```
routes/web.php (installation aur deployment routes)
app/Http/Kernel.php (CheckInstallation middleware)
app/Http/Controllers/Admin/SystemSettingsController.php (settings methods)
resources/views/admin/system-settings/index.blade.php (settings UI)
resources/views/layouts/app.blade.php (deployment menu link)
```

---

## Quick Reference

### Installation:
- URL: `/install`
- Steps: 4 (Requirements → Database → Admin → Install)
- Time: 2-5 minutes

### Deployment:
- URL: `/admin/deploy`
- Access: Admin only
- Time: 30-60 seconds

### Settings:
- URL: `/admin/system-settings`
- Access: Admin only
- Features: Database settings, Environment variables

---

## Support

Agar koi issue aaye to:
1. Browser console check karo (F12)
2. Server logs check karo (`storage/logs/laravel.log`)
3. Error messages carefully read karo
4. Troubleshooting section dekh lo

---

## Next Steps After Installation:

1. Admin panel me login karo
2. Company settings configure karo
3. Users create karo
4. Projects add karo
5. System ready hai!

---

**Note:** Yeh system production-ready hai aur security best practices follow karta hai. Har operation properly logged aur secured hai.
