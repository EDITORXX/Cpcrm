# Server par update – New User Welcome Email & Admin Notification

Jab bhi naya user banega, usko welcome email (name, password, position, details) jayegi aur admin ko in-app notification milega. Dono options Admin > System Settings se on/off kiye ja sakte hain.

---

## Server par kya karna hai

### 1. Code pull karo

```bash
git pull origin main
```

### 2. Migration chalao (agar abhi tak nahi chala)

```bash
php artisan migrate --force
```

Ye `system_settings` mein do naye keys add karega (default ON):
- `send_welcome_email_to_new_user`
- `notify_admin_on_new_user`

### 3. Mail config (.env)

Default mail **support@crm.bihtech.com** se bhejna hai. Server ke `.env` mein ye set karo:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=support@crm.bihtech.com
MAIL_PASSWORD=Base@9369
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="support@crm.bihtech.com"
MAIL_FROM_NAME="${APP_NAME}"
```

- **MAIL_HOST / PORT / ENCRYPTION**: Apne hosting (e.g. Hostinger) ke SMTP hisaab se change kar sakte ho.
- **MAIL_PASSWORD**: Apna real password yahi daalna (Git par kabhi commit mat karna).

### 4. Queue worker (optional, recommended)

Welcome email queue se bhejti hai. Background mein bhejne ke liye server par queue worker chalao:

```bash
php artisan queue:work
```

Ya supervisor/cron se run karo. Agar queue nahi chalega to email sync bhejegi (thodi delay ho sakti hai request ke time).

### 5. Cache clear

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Admin panel se control

- **Admin** > **System Settings** par jao.
- **"User & Email Notifications"** section mein:
  - **Send welcome email to new user** – ON rahe to naye user ko credentials wala email jayega.
  - **Notify admin when a new user is created** – ON rahe to admin ko in-app notification (bell) milega.
- Dono by default **ON** hain. Toggles change karke **Save User Notification Settings** click karo.

---

## Test kaise karein

1. Naya user banao (web Users page ya CRM dashboard se).
2. Us user ke email par welcome mail aani chahiye (name, temp password, position, login link).
3. Admin login karke notification bell check karo – "New user created: ..." dikhna chahiye.

Agar email nahi aa rahi to `.env` mail values aur queue worker check karo; `storage/logs/laravel.log` mein mail/queue errors dekh sakte ho.

---

## Localhost par mail test (same config)

Local machine par bhi same mail flow test kar sakte ho.

### Option A – Mailpit (recommended, emails UI mein dikhengi)

1. **Mailpit** chalao (Docker: `docker run -p 1025:1025 -p 8025:8025 axllent/mailpit` ya [mailpit.app](https://mailpit.app) se download).
2. Apne **`.env`** mein (same variables as production, different host/port):

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=support@crm.bihtech.com
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="support@crm.bihtech.com"
MAIL_FROM_NAME="${APP_NAME}"
```

3. Browser mein `http://localhost:8025` kholo – yahan saari bheji hui emails dikhengi.
4. Admin → System Settings → **Test email** ya **Mail debug** se test bhejo.

### Option B – Log driver (bina Mailpit, sirf test)

`.env` mein:

```env
MAIL_MAILER=log
```

Baaki MAIL_* optional. Emails `storage/logs/laravel.log` mein likhi jayengi – send nahi, sirf verify karne ke liye.

### Option C – Production jaisa (real email from local)

Production wali hi values daalo (smtp.hostinger.com, 587, tls, real password). Tab localhost se bhi real email jayegi.
