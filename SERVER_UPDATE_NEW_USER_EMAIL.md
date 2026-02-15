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
