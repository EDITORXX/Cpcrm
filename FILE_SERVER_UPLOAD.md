# File Server Upload & Git Push Guide

## 1. Git push fix (GitHub)

Remote ab **SSH** par set hai: `git@github.com:EDITORXX/Cpcrm.git`

### Agar push ab bhi fail ho:

**Option A – SSH key use karein (recommended)**  
- GitHub pe SSH key add karein: https://github.com/settings/keys  
- Phir local se: `ssh -T git@github.com` se test karein  
- Push: `git push origin main`

**Option B – HTTPS + Personal Access Token**  
- Remote wapas HTTPS par set karein:
  ```powershell
  git remote set-url origin https://github.com/EDITORXX/Cpcrm.git
  ```
- GitHub → Settings → Developer settings → Personal access tokens se naya token banao (repo access)
- Jab push karein to Username = GitHub username, Password = **token** (password nahi)

---

## 2. File server par upload (bina Git push)

Aap direct **zip** bana kar file server par upload kar sakte ho.

### Zip banane ke liye (project root se):

```powershell
php _archive\deployment\create_deployment_zip.php
```

Ye script `_archive\deployment\` folder ke andar `crm_deployment_YYYY-MM-DD_HHMMSS.zip` bana degi.  
Us zip ko server par upload karke extract karein.

### Ya "upload folder" copy (manual):

- Pure project folder ko copy karein (`.git` aur `node_modules` hata kar chhota kar sakte ho)
- Us copy ko zip karein aur server par upload karein
- Server par: extract → `composer install` (agar vendor nahi diya) → `.env` set → `php artisan key:generate` → permissions

---

## 3. Quick commands

| Task              | Command |
|-------------------|--------|
| Git push (SSH)    | `git push origin main` |
| Deployment zip    | `php _archive\deployment\create_deployment_zip.php` |
| Remote check      | `git remote -v` |
