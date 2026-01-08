# Quick Start - APK Build Guide

## ✅ What's Already Done

1. ✅ API config updated to use your local IP: `192.168.1.7:8000`
2. ✅ All Flutter code is ready
3. ✅ Build script created (`build_apk.bat`)

## 📱 Step 1: Install Flutter

1. **Download Flutter:**
   - Go to: https://flutter.dev/docs/get-started/install/windows
   - Download the Flutter SDK zip file
   - Extract it to `C:\flutter` (or any location you prefer)

2. **Add Flutter to PATH:**
   - Open "Environment Variables" in Windows
   - Add `C:\flutter\bin` to your PATH
   - Restart your terminal/command prompt

3. **Verify Installation:**
   ```bash
   flutter doctor
   ```

4. **Install Android Studio:**
   - Download from: https://developer.android.com/studio
   - Install Android SDK
   - Accept licenses: `flutter doctor --android-licenses`

## 🔧 Step 2: Start Laravel Server (Same Network)

**Important:** Start your Laravel server with network access:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

The `--host=0.0.0.0` allows connections from other devices on your network.

## 📦 Step 3: Build APK

1. **Open Command Prompt** in the project directory

2. **Navigate to Flutter app:**
   ```bash
   cd flutter_telecaller_app
   ```

3. **Run the build script:**
   ```bash
   build_apk.bat
   ```

   OR manually:
   ```bash
   flutter pub get
   flutter build apk --release
   ```

4. **Find your APK:**
   - Location: `build\app\outputs\flutter-apk\app-release.apk`
   - This is the file you need to install on your phone

## 📲 Step 4: Install on Android Phone

1. **Transfer APK to phone:**
   - Copy `app-release.apk` to your phone
   - Or use USB/email/cloud storage

2. **Enable Unknown Sources:**
   - Go to Settings > Security
   - Enable "Install from Unknown Sources" or "Allow from this source"

3. **Install:**
   - Open the APK file on your phone
   - Tap "Install"

## 🌐 Step 5: Connect to Server

1. **Make sure both devices are on same Wi-Fi network**

2. **Verify your computer's IP:**
   ```bash
   ipconfig
   ```
   Look for IPv4 Address (currently set to: 192.168.1.7)

3. **If IP changed, update it:**
   - Open `lib/config/api_config.dart`
   - Change `192.168.1.7` to your current IP
   - Rebuild APK

## ⚠️ Troubleshooting

### Flutter not found
- Make sure Flutter is in PATH
- Restart terminal after adding to PATH

### Build fails
- Run `flutter doctor` to check issues
- Make sure Android SDK is installed
- Accept Android licenses: `flutter doctor --android-licenses`

### Can't connect to server
- Check both devices are on same Wi-Fi
- Verify Laravel server is running with `--host=0.0.0.0`
- Check Windows Firewall allows port 8000
- Verify IP address in `api_config.dart`

### APK not found
- Check `build/app/outputs/flutter-apk/` folder
- Make sure build completed without errors

## 📝 Current Configuration

- **Server IP:** 192.168.1.7
- **Server Port:** 8000
- **API Base URL:** http://192.168.1.7:8000/api

## 🚀 Alternative: Use Android Studio

If you have Android Studio installed:

1. Open Android Studio
2. File > Open > Select `flutter_telecaller_app` folder
3. Wait for Flutter to sync
4. Click "Build" > "Build Bundle(s) / APK(s)" > "Build APK(s)"
5. APK will be in `build/app/outputs/flutter-apk/`

---

**Need Help?** Check `BUILD_INSTRUCTIONS.md` for detailed steps.

