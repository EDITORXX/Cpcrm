# APK Build Instructions

## Prerequisites

1. **Install Flutter**
   - Download from: https://flutter.dev/docs/get-started/install
   - Add Flutter to your PATH
   - Verify installation: `flutter doctor`

2. **Install Android Studio**
   - Download from: https://developer.android.com/studio
   - Install Android SDK
   - Accept Android licenses: `flutter doctor --android-licenses`

3. **Enable USB Debugging on your Android device** (if testing on device)

## Quick Build (Windows)

1. Open Command Prompt or PowerShell
2. Navigate to the Flutter app directory:
   ```bash
   cd flutter_telecaller_app
   ```
3. Run the build script:
   ```bash
   build_apk.bat
   ```

## Manual Build Steps

1. **Navigate to the app directory:**
   ```bash
   cd flutter_telecaller_app
   ```

2. **Get dependencies:**
   ```bash
   flutter pub get
   ```

3. **Clean previous builds (optional):**
   ```bash
   flutter clean
   ```

4. **Build APK:**
   ```bash
   flutter build apk --release
   ```

5. **Find the APK:**
   - Location: `build/app/outputs/flutter-apk/app-release.apk`
   - This is the installable APK file

## Network Configuration

The app is configured to connect to your Laravel server at:
- **IP Address:** `192.168.1.7`
- **Port:** `8000`

### To change the IP address:

1. Open `lib/config/api_config.dart`
2. Update the `baseUrl`:
   ```dart
   static const String baseUrl = 'http://YOUR_IP_ADDRESS:8000/api';
   ```

### To find your local IP address:

**Windows:**
```bash
ipconfig
```
Look for "IPv4 Address" under your active network adapter.

**Mac/Linux:**
```bash
ifconfig
```
or
```bash
ip addr show
```

## Important Notes

1. **Same Network Required:**
   - Your phone and computer must be on the same Wi-Fi network
   - Use your computer's local IP (not localhost or 127.0.0.1)

2. **Laravel Server:**
   - Make sure your Laravel server is running:
     ```bash
     php artisan serve --host=0.0.0.0 --port=8000
     ```
   - The `--host=0.0.0.0` allows connections from other devices on the network

3. **Firewall:**
   - Make sure Windows Firewall allows connections on port 8000
   - Or temporarily disable firewall for testing

4. **Android Permissions:**
   - The app requires phone permissions for call tracking
   - These will be requested when the app is first run

## Installing the APK

1. Transfer the APK file to your Android device
2. Enable "Install from Unknown Sources" in Android settings
3. Open the APK file and install

## Troubleshooting

### Flutter not found
- Make sure Flutter is installed and added to PATH
- Restart your terminal/command prompt

### Build fails
- Run `flutter doctor` to check for issues
- Make sure Android SDK is properly installed
- Check that you have accepted Android licenses

### App can't connect to server
- Verify both devices are on the same network
- Check that Laravel server is running with `--host=0.0.0.0`
- Verify the IP address in `api_config.dart` matches your computer's IP
- Check firewall settings

### APK file not found
- Check the `build/app/outputs/flutter-apk/` directory
- Make sure the build completed successfully (no errors)

