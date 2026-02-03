# Manual License Acceptance Guide

## Current Situation

Terminal mein prompt aa raha hai:
```
Review licenses that have not been accepted (y/N)?
```

## Solution: Manual Command

### Step 1: CMD ya PowerShell Open Karein

1. **Windows + R** press karein
2. Type: `cmd` → **Enter**
   Ya
   Type: `powershell` → **Enter**

### Step 2: Flutter PATH Set Karein

CMD mein:
```cmd
set PATH=%PATH%;C:\flutter\bin
```

PowerShell mein:
```powershell
$env:PATH += ";C:\flutter\bin"
```

### Step 3: ANDROID_HOME Set Karein

CMD mein:
```cmd
set ANDROID_HOME=%LOCALAPPDATA%\Android\Sdk
```

PowerShell mein:
```powershell
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"
```

### Step 4: Licenses Accept Karein

```cmd
flutter doctor --android-licenses
```

### Step 5: Har Prompt Par 'y' Type Karein

Jab bhi prompt aaye:
- Type: `y`
- Press: **Enter**

Yeh 6-7 baar repeat hoga (har license ke liye).

### Step 6: Success Check

Jab sab licenses accept ho jayen, aapko dikhega:
```
All SDK package licenses accepted.
```

---

## Quick One-Liner (PowerShell)

Agar aap PowerShell use kar rahe hain:

```powershell
$env:PATH += ";C:\flutter\bin"; $env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"; flutter doctor --android-licenses
```

Phir har prompt par `y` type karein.

---

## Alternative: Batch File Use Karein

1. File Explorer mein jayen: `flutter_telecaller_app`
2. `ACCEPT_LICENSES.bat` par double-click karein
3. Har prompt par `y` type karein

---

## After Licenses Accepted

Phir run karein:
```cmd
flutter doctor
```

Ab Android toolchain green tick dikhna chahiye!
