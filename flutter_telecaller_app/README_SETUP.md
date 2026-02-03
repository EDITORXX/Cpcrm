# Flutter PATH Setup - Quick Guide

## 🚀 Quick Setup (Recommended)

### Option 1: User PATH (No Admin Needed) - **EASIEST**

1. **File Explorer mein jayen:**
   ```
   flutter_telecaller_app
   ```

2. **`ADD_FLUTTER_TO_USER_PATH.bat` par double-click karein**

3. **Terminal/IDE restart karein**

4. **Verify karein:**
   ```bash
   flutter --version
   ```

✅ **Yeh method sabse easy hai - admin rights ki zarurat nahi!**

---

### Option 2: System PATH (Admin Required)

1. **`ADD_FLUTTER_TO_SYSTEM_PATH.bat` par right-click karein**

2. **"Run as administrator" select karein**

3. **Terminal/IDE restart karein**

4. **Verify karein:**
   ```bash
   flutter --version
   ```

---

## 📋 Available Scripts

### 1. `QUICK_SETUP.bat` ⭐ **START HERE**
   - Interactive menu
   - Sab options ek jagah
   - Double-click karein aur follow karein

### 2. `ADD_FLUTTER_TO_USER_PATH.bat`
   - User PATH mein add karega
   - Admin rights nahi chahiye
   - **Recommended for most users**

### 3. `ADD_FLUTTER_TO_SYSTEM_PATH.bat`
   - System PATH mein add karega
   - Admin rights required
   - Sab users ke liye access

### 4. `VERIFY_FLUTTER_SETUP.bat`
   - Current setup check karega
   - Flutter accessible hai ya nahi verify karega

---

## ✅ After Setup

1. **Terminal/IDE restart karein** (important!)

2. **Verify karein:**
   ```bash
   flutter --version
   ```

3. **Flutter Doctor run karein:**
   ```bash
   flutter doctor
   ```

4. **Android licenses accept karein:**
   ```bash
   flutter doctor --android-licenses
   ```

5. **APK build karein:**
   ```bash
   INSTALL_AND_BUILD.bat
   ```

---

## 🆘 Troubleshooting

### Issue: "Flutter not recognized"
- **Solution:** Terminal/IDE restart karein
- **Verify:** `VERIFY_FLUTTER_SETUP.bat` run karein

### Issue: Script says "already exists"
- **Solution:** Yeh normal hai - PATH already set hai
- **Action:** Terminal restart karein aur verify karein

### Issue: Admin rights error
- **Solution:** `ADD_FLUTTER_TO_USER_PATH.bat` use karein (no admin needed)

---

## 📝 Step-by-Step

1. **`QUICK_SETUP.bat` run karein**
2. **Option 1 select karein** (User PATH)
3. **Terminal/IDE restart karein**
4. **`flutter --version` run karein**
5. **Done!** ✓

---

**Last Updated:** January 28, 2026
