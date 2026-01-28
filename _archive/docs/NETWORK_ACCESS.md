# Network Access Guide

## Overview

This guide explains how to access your Laravel CRM from other devices on the same network (mobile phones, tablets, other computers).

## Quick Start

1. **Start the server** using `start_server.bat`
2. **Note the Network URL** displayed (e.g., `http://192.168.1.100:8007`)
3. **Open that URL** on any device connected to the same WiFi/network

## How It Works

The `start_server.bat` script automatically:
- Detects your computer's local IP address
- Updates the `APP_URL` in `.env` file with the detected IP
- Starts the server on `0.0.0.0:8007` (accessible from network)
- Displays both local and network access URLs

## Accessing from Different Devices

### Mobile Phone / Tablet

1. **Connect to same WiFi network** as your computer
2. **Open mobile browser** (Chrome, Safari, etc.)
3. **Enter the Network URL** shown in the server console
   - Example: `http://192.168.1.100:8007`
4. **Login** with your credentials

### Another Computer

1. **Connect to same network** (WiFi or LAN)
2. **Open browser**
3. **Enter the Network URL** from server console
4. **Access the application**

### Finding Your IP Manually

If the automatic detection doesn't work, find your IP manually:

**Windows:**
```cmd
ipconfig
```
Look for "IPv4 Address" under your active network adapter.

**Mac/Linux:**
```bash
ifconfig
# or
ip addr show
```

## Troubleshooting

### Cannot Access from Mobile/Other Device

#### 1. Check Network Connection
- ✅ Both devices on same WiFi/network?
- ✅ WiFi password correct?
- ✅ No guest network isolation?

#### 2. Check Firewall Settings

**Windows Firewall:**
1. Open "Windows Defender Firewall"
2. Click "Allow an app through firewall"
3. Find "PHP" or add port 8007
4. Check both "Private" and "Public" networks

**Quick Fix (Temporary):**
```cmd
netsh advfirewall firewall add rule name="Laravel CRM" dir=in action=allow protocol=TCP localport=8007
```

**Remove Rule Later:**
```cmd
netsh advfirewall firewall delete rule name="Laravel CRM"
```

#### 3. Check Server is Running
- ✅ Server console shows "Server started"?
- ✅ No errors in console?
- ✅ Port 8007 is not used by another application?

#### 4. Verify IP Address
- ✅ IP shown in console matches your computer's IP?
- ✅ Try accessing `http://localhost:8007` on the server computer first

#### 5. Router/Network Issues
- Some routers block device-to-device communication
- Try disabling "AP Isolation" or "Client Isolation" in router settings
- Some corporate networks block local device communication

### Server Shows Wrong IP

If the detected IP is incorrect:

1. **Manually edit `.env` file:**
   ```env
   APP_URL=http://YOUR_CORRECT_IP:8007
   ```

2. **Restart the server**

### Port Already in Use

If port 8007 is already in use:

1. **Find what's using the port:**
   ```cmd
   netstat -ano | findstr :8007
   ```

2. **Use a different port:**
   ```cmd
   php artisan serve --host=0.0.0.0 --port=8008
   ```
   Then update `APP_URL` in `.env` accordingly.

### SSL/HTTPS Warnings on Mobile

If you see security warnings:
- This is normal for `http://` URLs
- Click "Advanced" → "Proceed anyway" (or similar)
- For production, use proper SSL certificate

## Security Considerations

### Local Network Only
- This setup is for **local network access only**
- Do NOT expose this to the internet without proper security
- Use firewall rules to restrict access if needed

### Production Deployment
For production (internet access):
- Use proper domain name
- Enable HTTPS/SSL
- Use strong authentication
- Configure proper firewall rules
- Use environment-specific `.env` settings

## Testing Network Access

### Test from Server Computer
```cmd
curl http://localhost:8007
```

### Test from Another Device
```cmd
# Replace with your actual IP
curl http://192.168.1.100:8007
```

### Test API Endpoint
```cmd
curl http://192.168.1.100:8007/api
```

## QR Code for Easy Mobile Access (Optional)

You can generate a QR code with your network URL for easy mobile access:

1. Use online QR code generator: https://www.qr-code-generator.com/
2. Enter your network URL: `http://YOUR_IP:8007`
3. Scan with mobile phone camera
4. Opens directly in browser

## Common Network IP Ranges

Your local IP will typically be in one of these ranges:
- `192.168.x.x` (most common)
- `10.0.x.x`
- `172.16.x.x` to `172.31.x.x`

## API Access from Mobile App

If you're using the Flutter mobile app:

1. **Update API base URL** in mobile app config
2. **Use network IP** instead of localhost:
   ```dart
   baseUrl: 'http://192.168.1.100:8007/api'
   ```

## Restoring Localhost URL

If you want to restore `APP_URL` to localhost:

1. **Edit `.env` file:**
   ```env
   APP_URL=http://localhost:8007
   ```

2. **Or restore from backup:**
   ```cmd
   copy .env.backup .env
   ```

## Need Help?

- Check server console for error messages
- Check `storage/logs/laravel.log` for application errors
- Verify database connection is working
- Ensure all migrations are run

## Quick Reference

| Action | Command/URL |
|--------|-------------|
| Start Server | `start_server.bat` |
| Local Access | `http://localhost:8007` |
| Network Access | `http://YOUR_IP:8007` |
| Check IP | `ipconfig` (Windows) |
| Test Connection | `curl http://YOUR_IP:8007` |
