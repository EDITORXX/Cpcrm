# APK File Build Guide - हिंदी में

## 🚀 Quick Start (सबसे आसान तरीका)

### Step 1: Flutter Install करें

**Option A: Automatic (स्वचालित)**
1. `AUTO_SETUP.bat` file को double-click करें
2. Instructions follow करें

**Option B: Manual (मैनुअल)**
1. Flutter download करें: https://flutter.dev/docs/get-started/install/windows
2. Extract करें `C:\flutter` में
3. PATH में add करें:
   - Win+R दबाएं
   - `sysdm.cpl` type करें
   - Advanced > Environment Variables
   - PATH edit करें, `C:\flutter\bin` add करें
4. Terminal restart करें

### Step 2: APK Build करें

1. `INSTALL_AND_BUILD.bat` file को double-click करें
2. Script automatically सब कुछ कर देगी
3. APK file यहाँ मिलेगी: `build\app\outputs\flutter-apk\app-release.apk`

### Step 3: Laravel Server Start करें

**Important:** Same network के लिए server को इस तरह start करें:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

या `START_SERVER.bat` file use करें

### Step 4: Phone पर Install करें

1. APK file को phone में transfer करें
2. Settings > Security में "Unknown Sources" enable करें
3. APK file open करके install करें

## 📱 Current Configuration

- **Server IP:** 192.168.1.7
- **Port:** 8000
- **API URL:** http://192.168.1.7:8000/api

## ⚠️ Important Notes

1. **Same Network:** Phone और computer same Wi-Fi पर होने चाहिए
2. **Server Binding:** Laravel server `--host=0.0.0.0` के साथ start करें
3. **IP Address:** अगर IP change हो, तो `lib/config/api_config.dart` में update करें

## 🔧 Troubleshooting

### Flutter नहीं मिल रहा
- PATH check करें
- Terminal restart करें
- `flutter doctor` run करें

### Build fail हो रहा है
- `flutter doctor` run करें
- Android SDK install करें
- Licenses accept करें: `flutter doctor --android-licenses`

### Server connect नहीं हो रहा
- Same Wi-Fi network check करें
- Server `--host=0.0.0.0` के साथ start है या नहीं
- Firewall check करें
- IP address verify करें

## 📂 Files Created

1. `INSTALL_AND_BUILD.bat` - APK build करने के लिए
2. `AUTO_SETUP.bat` - Flutter install करने के लिए
3. `START_SERVER.bat` - Laravel server start करने के लिए
4. `QUICK_START.md` - Detailed guide
5. `BUILD_INSTRUCTIONS.md` - Complete instructions

## 🎯 Simple Steps Summary

```
1. AUTO_SETUP.bat run करें (Flutter install)
2. INSTALL_AND_BUILD.bat run करें (APK build)
3. START_SERVER.bat run करें (Server start)
4. APK file phone में install करें
```

---

**सब कुछ ready है! बस scripts run करें और APK मिल जाएगी! 🎉**

