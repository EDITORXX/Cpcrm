# How to Update IP Address for Network Access

## Current Configuration

The app is currently configured to use: `http://192.168.1.7:8000/api`

## Steps to Update IP Address

1. **Find your computer's local IP address:**

   **Windows:**
   ```bash
   ipconfig
   ```
   Look for "IPv4 Address" (e.g., 192.168.1.7)

   **Mac/Linux:**
   ```bash
   ifconfig
   ```
   or
   ```bash
   ip addr show
   ```

2. **Update the API config file:**
   - Open: `lib/config/api_config.dart`
   - Find the line:
     ```dart
     static const String baseUrl = 'http://192.168.1.7:8000/api';
     ```
   - Replace `192.168.1.7` with your IP address

3. **Rebuild the APK:**
   ```bash
   flutter clean
   flutter pub get
   flutter build apk --release
   ```

## Important Notes

- **Same Network:** Your phone and computer must be on the same Wi-Fi network
- **Server Binding:** Start Laravel server with:
  ```bash
  php artisan serve --host=0.0.0.0 --port=8000
  ```
  The `--host=0.0.0.0` allows connections from other devices

- **Firewall:** Make sure Windows Firewall allows connections on port 8000

## Quick IP Update Script

You can also create a script to automatically update the IP. For Windows, create `update_ip.bat`:

```batch
@echo off
echo Finding your IP address...
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do (
    set IP=%%a
    set IP=!IP:~1!
    echo Your IP: !IP!
    echo.
    echo Updating api_config.dart...
    powershell -Command "(Get-Content lib\config\api_config.dart) -replace 'http://[0-9.]+:8000', 'http://!IP!:8000' | Set-Content lib\config\api_config.dart"
    echo Updated!
    pause
)
```

