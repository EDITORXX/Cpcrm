# Build Stuck? - What to Do

## Normal Build Time

Gradle build process **normally takes 3-5 minutes**, especially:
- First build after clean
- After dependency updates
- When downloading Gradle dependencies

## Check if Build is Actually Running

### Option 1: Check Processes

PowerShell mein:
```powershell
Get-Process | Where-Object {$_.ProcessName -like "*java*"}
```

Agar Java processes dikhen, toh build **still running** hai - wait karein!

### Option 2: Check CPU Usage

Agar CPU usage high hai, toh build **actively running** hai.

---

## If Really Stuck (No Progress for 10+ minutes)

### Step 1: Kill Stuck Processes

PowerShell mein:
```powershell
taskkill /F /IM java.exe /T
```

Ya CMD mein:
```cmd
taskkill /F /IM java.exe /T
```

### Step 2: Clear Gradle Cache

PowerShell mein:
```powershell
Remove-Item -Path "$env:USERPROFILE\.gradle" -Recurse -Force -ErrorAction SilentlyContinue
```

### Step 3: Fresh Build

```powershell
cd "c:\Users\vivek\Pictures\Laravel crm fully functional\flutter_telecaller_app"
$env:PATH += ";C:\flutter\bin"
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"
flutter clean
flutter pub get
flutter build apk --release
```

---

## Current Status

Agar terminal mein "Running Gradle task 'assembleRelease'..." dikh raha hai:
- **First 3-5 minutes:** Normal - wait karein
- **After 10 minutes:** Stuck - kill processes aur restart karein

---

## Quick Commands

**Check if running:**
```powershell
Get-Process java -ErrorAction SilentlyContinue
```

**Kill if stuck:**
```powershell
taskkill /F /IM java.exe /T
```

**Restart build:**
```powershell
cd "c:\Users\vivek\Pictures\Laravel crm fully functional\flutter_telecaller_app"; $env:PATH += ";C:\flutter\bin"; $env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"; flutter build apk --release
```
