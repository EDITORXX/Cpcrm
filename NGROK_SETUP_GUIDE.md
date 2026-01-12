# Laravel + ngrok Setup Guide for Windows

Complete guide to run Laravel CRM on port 8007 and expose it publicly using ngrok.

## Quick Start

1. **Setup ngrok** (one-time):
   ```cmd
   setup_ngrok.bat
   ```

2. **Start everything** (opens 2 windows automatically):
   ```cmd
   start_laravel_with_ngrok.bat
   ```

3. **Or start manually** (2 separate windows):
   - Window 1: `start_laravel_ngrok.bat`
   - Window 2: `start_ngrok_tunnel.bat`

4. **Verify setup**:
   ```cmd
   verify_ngrok_setup.bat
   ```

## Detailed Steps

### Step 1: Install and Configure ngrok

#### Option A: Using Setup Script (Recommended)
```cmd
setup_ngrok.bat
```
This script will:
- Check if ngrok is installed
- Guide you through installation if needed
- Help configure your authtoken

#### Option B: Manual Installation

1. **Download ngrok:**
   - Visit: https://ngrok.com/download
   - Download Windows 64-bit version
   - Extract `ngrok.exe` to a folder (e.g., `C:\ngrok\`)

2. **Add to PATH (Optional):**
   ```cmd
   setx PATH "%PATH%;C:\ngrok"
   ```
   Close and reopen CMD after this.

3. **Sign up for free account:**
   - Go to: https://dashboard.ngrok.com/signup
   - Get your authtoken from: https://dashboard.ngrok.com/get-started/your-authtoken

4. **Configure ngrok:**
   ```cmd
   ngrok config add-authtoken YOUR_AUTHTOKEN_HERE
   ```

### Step 2: Start Laravel Server

**Option A: Using Batch Script**
```cmd
start_laravel_ngrok.bat
```

**Option B: Manual Command**
```cmd
cd "c:\Users\vivek\Pictures\Laravel crm fully functional"
php artisan serve --port=8007
```

**Expected Output:**
```
INFO  Server running on [http://127.0.0.1:8007]
```

**Verify locally:**
- Open browser: http://localhost:8007
- You should see your Laravel CRM homepage

**Keep this window open!** Laravel server must stay running.

### Step 3: Start ngrok Tunnel

**Open a SECOND CMD window** (keep Laravel running in first window).

**Option A: Using Batch Script**
```cmd
start_ngrok_tunnel.bat
```

**Option B: Manual Command**
```cmd
ngrok http 8007
```

**Expected Output:**
```
Session Status                online
Account                       Your Name (Plan: Free)
Version                       3.x.x
Region                        United States (us)
Forwarding                    https://xxxx-xx-xx-xx-xx.ngrok-free.app -> http://localhost:8007
Forwarding                    http://xxxx-xx-xx-xx-xx.ngrok-free.app -> http://localhost:8007
```

**Copy the public URL:**
- Note the `https://xxxx-xx-xx-xx-xx.ngrok-free.app` URL
- This is your public URL accessible from anywhere

**Keep this window open!** ngrok tunnel must stay running.

### Step 4: Verify Public URL

1. **Test in browser:**
   - Open the ngrok HTTPS URL (e.g., `https://xxxx-xx-xx-xx-xx.ngrok-free.app`)
   - You should see your Laravel CRM homepage
   - First visit may show ngrok warning page - click "Visit Site" button

2. **Test from mobile device:**
   - Open the ngrok HTTPS URL on your phone's browser
   - Should load your Laravel CRM

3. **Test API endpoint:**
   ```cmd
   curl https://xxxx-xx-xx-xx-xx.ngrok-free.app/api
   ```

### Step 5: Open ngrok Request Inspector

1. **Access ngrok web interface:**
   - Open browser: http://127.0.0.1:4040
   - OR: http://localhost:4040

2. **What you'll see:**
   - Real-time request/response logs
   - Request headers, body, response data
   - Replay requests
   - Request timeline

3. **Keep inspector open:**
   - It updates automatically as requests come through ngrok tunnel

## Common Windows Errors & Quick Fixes

### Error 1: "php is not recognized"

**Problem:** PHP not in system PATH

**Fix Option A - Use full path:**
```cmd
"C:\xampp\php\php.exe" artisan serve --port=8007
```
(Replace with your actual PHP path)

**Fix Option B - Add to PATH:**
1. Find PHP installation (e.g., `C:\xampp\php\` or `C:\php\`)
2. Add to PATH:
   ```cmd
   setx PATH "%PATH%;C:\xampp\php"
   ```
3. Close and reopen CMD

**Fix Option C - Check if PHP installed:**
```cmd
where php
```

### Error 2: "Port 8007 is already in use"

**Problem:** Another process using port 8007

**Fix:**
```cmd
netstat -ano | findstr :8007
```
- Note the PID (last column)
- Kill the process:
  ```cmd
  taskkill /PID <PID_NUMBER> /F
  ```
- Or use different port:
  ```cmd
  php artisan serve --port=8008
  ```
  (Then update ngrok: `ngrok http 8008`)

### Error 3: "ngrok is not recognized"

**Problem:** ngrok not in PATH or not installed

**Fix:**
- Use full path: `C:\ngrok\ngrok.exe http 8007`
- Or add ngrok folder to PATH (see Step 1)
- Or run: `setup_ngrok.bat`

### Error 4: Windows Firewall blocking

**Problem:** Firewall blocking port 8007

**Fix:**
```cmd
netsh advfirewall firewall add rule name="Laravel Port 8007" dir=in action=allow protocol=TCP localport=8007
```

### Error 5: "ngrok: command failed: authtoken required"

**Problem:** ngrok not authenticated

**Fix:**
```cmd
ngrok config add-authtoken YOUR_AUTHTOKEN
```
Get authtoken from: https://dashboard.ngrok.com/get-started/your-authtoken

### Error 6: "Address already in use" (ngrok inspector)

**Problem:** Port 4040 already in use

**Fix:**
- Use different port for inspector:
  ```cmd
  ngrok http 8007 --web-addr=localhost:4041
  ```
- Then access: http://localhost:4041

### Error 7: Laravel shows "No application encryption key"

**Fix:**
```cmd
php artisan key:generate
```

### Error 8: Database connection error

**Fix:**
- Check `.env` file has correct database credentials
- Ensure MySQL is running
- Test connection:
  ```cmd
  php artisan tinker
  ```
  Then: `DB::connection()->getPdo();`

## Quick Reference Commands

**Terminal 1 (Laravel):**
```cmd
cd "c:\Users\vivek\Pictures\Laravel crm fully functional"
php artisan serve --port=8007
```

**Terminal 2 (ngrok):**
```cmd
ngrok http 8007
```

**Verify:**
- Local: http://localhost:8007
- Public: https://xxxx-xx-xx-xx-xx.ngrok-free.app
- Inspector: http://127.0.0.1:4040

## Available Batch Scripts

| Script | Purpose |
|--------|---------|
| `setup_ngrok.bat` | Install and configure ngrok |
| `start_laravel_ngrok.bat` | Start Laravel server on port 8007 |
| `start_ngrok_tunnel.bat` | Start ngrok tunnel |
| `start_laravel_with_ngrok.bat` | Start both Laravel and ngrok automatically |
| `verify_ngrok_setup.bat` | Verify everything is working |

## Notes

- Keep both CMD windows open (Laravel server + ngrok)
- Free ngrok URLs change each time you restart ngrok
- For permanent URL, upgrade to ngrok paid plan
- ngrok free plan has connection limits
- Laravel must be running before starting ngrok
- First ngrok visit may show warning page - click "Visit Site"

## Troubleshooting

If something doesn't work:

1. **Run verification script:**
   ```cmd
   verify_ngrok_setup.bat
   ```

2. **Check both windows are running:**
   - Laravel window should show: "Server running on [http://127.0.0.1:8007]"
   - ngrok window should show: "Session Status: online"

3. **Check ngrok inspector:**
   - Open: http://127.0.0.1:4040
   - Look for any errors in the request logs

4. **Test local first:**
   - Make sure http://localhost:8007 works before using ngrok

5. **Check firewall:**
   - Windows Firewall may block connections
   - Run the firewall command from Error 4 above
