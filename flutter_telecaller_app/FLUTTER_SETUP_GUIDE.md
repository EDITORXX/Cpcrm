# Flutter Setup Guide - Step by Step

## Problem: Flutter Doctor Stuck / PATH Issue

Agar `flutter doctor` stuck ho raha hai ya "flutter not recognized" error aa raha hai, yeh guide follow karein.

---

## Step 1: Flutter PATH Permanently Set Karein

### Option A: GUI Method (Recommended)

1. **Windows Search mein jayen:**
   - Press `Win + R`
   - Type: `sysdm.cpl`
   - Press Enter

2. **Environment Variables open karein:**
   - "Advanced" tab par click karein
   - "Environment Variables" button par click karein

3. **PATH edit karein:**
   - "User variables" section mein "Path" select karein
   - "Edit" button par click karein
   - "New" button par click karein
   - Type: `C:\flutter\bin`
   - "OK" par click karein (har dialog mein)

4. **Restart:**
   - Terminal/IDE ko close karein
   - Naya terminal/IDE open karein

### Option B: PowerShell Method (Quick)

1. **PowerShell as Administrator open karein:**
   - Windows Search mein "PowerShell" type karein
   - Right-click → "Run as administrator"

2. **Command run karein:**
   ```powershell
   [Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\flutter\bin", "User")
   ```

3. **Restart terminal/IDE**

---

## Step 2: Verify Flutter Setup

1. **Naya terminal/IDE open karein** (important: restart karein)

2. **Flutter verify karein:**
   ```bash
   flutter --version
   ```
   - Agar version dikhe, toh Flutter working hai ✓
   - Agar error aaye, toh Step 1 dobara check karein

3. **Flutter Doctor run karein:**
   ```bash
   flutter doctor
   ```
   - Ye command 1-2 minutes le sakta hai (first time)
   - Wait karein, output aayega

---

## Step 3: Android SDK Setup

Agar `flutter doctor` mein Android toolchain issue dikhe:

### Android Studio se SDK Install:

1. **Android Studio open karein:**
   - Start Menu se "Android Studio" open karein

2. **SDK Manager open karein:**
   - Welcome screen par: "More Actions" → "SDK Manager"
   - Ya: File → Settings → Appearance & Behavior → System Settings → Android SDK

3. **SDK Components install karein:**
   - "SDK Platforms" tab:
     - Android 13.0 (Tiramisu) - API 33 ✓
     - Android 12.0 (S) - API 31 ✓
   - "SDK Tools" tab:
     - Android SDK Build-Tools ✓
     - Android SDK Command-line Tools ✓
     - Android SDK Platform-Tools ✓
     - Android Emulator ✓
   - "Apply" → "OK" → Wait for installation

4. **ANDROID_HOME Set Karein:**
   - SDK Location note karein (usually: `C:\Users\YourName\AppData\Local\Android\Sdk`)
   - Environment Variables mein:
     - New variable: `ANDROID_HOME`
     - Value: SDK location path (e.g., `C:\Users\vivek\AppData\Local\Android\Sdk`)
   - PATH mein add karein:
     - `%ANDROID_HOME%\platform-tools`
     - `%ANDROID_HOME%\tools`
     - `%ANDROID_HOME%\tools\bin`

---

## Step 4: Android Licenses Accept Karein

1. **Command run karein:**
   ```bash
   flutter doctor --android-licenses
   ```

2. **Har license ke liye `y` press karein** (yes ke liye)

3. **Sab licenses accept ho jayengi**

---

## Step 5: Final Verification

1. **Flutter Doctor dobara run karein:**
   ```bash
   flutter doctor -v
   ```
   - `-v` flag detailed output deta hai

2. **Check karein:**
   - ✓ Flutter - Green tick hona chahiye
   - ✓ Android toolchain - Green tick hona chahiye
   - ⚠️ Agar koi issue ho, toh woh fix karein

---

## Step 6: APK Build Karein

Jab sab setup ho jaye:

### Option A: Build Script Use Karein

1. **File Explorer mein jayen:**
   ```
   flutter_telecaller_app
   ```

2. **`INSTALL_AND_BUILD.bat` par double-click karein**

3. **Script automatically:**
   - Clean build karega
   - Dependencies install karega
   - APK build karega
   - APK folder kholega

### Option B: Manual Build

1. **Terminal mein jayen:**
   ```bash
   cd "c:\Users\vivek\Pictures\Laravel crm fully functional\flutter_telecaller_app"
   ```

2. **Commands run karein:**
   ```bash
   flutter clean
   flutter pub get
   flutter build apk --release
   ```

3. **APK file:**
   ```
   build\app\outputs\flutter-apk\app-release.apk
   ```

---

## Troubleshooting

### Issue 1: "Flutter not recognized"
- **Solution:** PATH permanently set karein (Step 1)
- **Verify:** Naya terminal open karke `flutter --version` run karein

### Issue 2: "Android SDK not found"
- **Solution:** Android Studio se SDK install karein (Step 3)
- **Verify:** `ANDROID_HOME` environment variable set karein

### Issue 3: "Flutter doctor stuck"
- **Solution:** 
  - Terminal close karke naya open karein
  - `flutter doctor -v` run karein (detailed output)
  - Wait karein (first time slow ho sakta hai)

### Issue 4: "Android licenses not accepted"
- **Solution:** `flutter doctor --android-licenses` run karein
- **Action:** Har prompt par `y` press karein

### Issue 5: "Build failed"
- **Solution:** 
  - `flutter clean` run karein
  - `flutter pub get` dobara run karein
  - `flutter doctor` check karein

---

## Quick Checklist

- [ ] Flutter PATH permanently set (Step 1)
- [ ] Terminal/IDE restart kiya
- [ ] `flutter --version` working
- [ ] `flutter doctor` run kiya
- [ ] Android SDK installed
- [ ] ANDROID_HOME set kiya
- [ ] Android licenses accepted
- [ ] `flutter doctor` sab green ticks
- [ ] APK build successful

---

## Need Help?

Agar koi step mein problem ho:
1. Screenshot share karein
2. Error message copy karein
3. `flutter doctor -v` output share karein

---

**Last Updated:** January 28, 2026
