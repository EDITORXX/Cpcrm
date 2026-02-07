# Common Build Errors & Solutions (Android Studio)

## 🔴 Error 1: "Gradle sync failed"

### Error Message:
```
Gradle sync failed: Plugin [id: 'com.android.application'] was not found
```

### Solution:
**File**: `android/settings.gradle`

Ensure this exists at the top:
```groovy
pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}
```

---

## 🔴 Error 2: "Could not resolve all dependencies"

### Error Message:
```
Could not resolve all dependencies for configuration ':app:debugCompileClasspath'
```

### Solution:
**File**: `android/build.gradle`

Add proper repositories:
```groovy
allprojects {
    repositories {
        google()
        mavenCentral()
    }
}
```

Then:
1. `File` → `Sync Project with Gradle Files`
2. Wait for sync to complete

---

## 🔴 Error 3: "Minimum supported Gradle version"

### Error Message:
```
The project is using an incompatible version (AGP X.X) of the Android Gradle plugin.
Minimum supported Gradle version is X.X. Current version is X.X.
```

### Solution:
Click **"Fix Gradle wrapper"** in the error popup

OR manually update:

**File**: `android/gradle/wrapper/gradle-wrapper.properties`
```properties
distributionUrl=https\://services.gradle.org/distributions/gradle-8.3-all.zip
```

---

## 🔴 Error 4: "Java heap space / Out of memory"

### Error Message:
```
java.lang.OutOfMemoryError: Java heap space
```

### Solution:
**File**: `android/gradle.properties`

Add these lines:
```properties
org.gradle.jvmargs=-Xmx4096m -XX:MaxMetaspaceSize=512m -XX:+HeapDumpOnOutOfMemoryError
org.gradle.daemon=true
org.gradle.parallel=true
org.gradle.caching=true
```

---

## 🔴 Error 5: "Failed to transform core-for-system-modules.jar"

### Error Message:
```
Failed to transform core-for-system-modules.jar to match attributes
Error while executing process jlink.exe
```

### Solution (Most Common Issue!):

**Step 1**: Clear Gradle Cache
```
File → Invalidate Caches → Invalidate and Restart
```

**Step 2**: Clean Project
```
Build → Clean Project
```

**Step 3**: If still failing, manual clear:

Open Terminal in Android Studio:
```bash
cd ..
flutter clean
exit
```

Then close and reopen Android Studio.

**Step 4**: Delete system Gradle cache (Nuclear option)

Close Android Studio, then in PowerShell:
```powershell
Remove-Item -Path "$env:USERPROFILE\.gradle\caches" -Recurse -Force
```

Reopen Android Studio and let Gradle sync again.

---

## 🔴 Error 6: "Namespace not specified"

### Error Message:
```
Namespace not specified. Specify a namespace in the module's build.gradle file
```

### Solution:
**File**: `android/app/build.gradle`

Ensure this line exists (around line 8):
```groovy
android {
    namespace = "com.basecrm.simple"
    compileSdk = 34
    // ... rest of config
}
```

---

## 🔴 Error 7: "SDK location not found"

### Error Message:
```
SDK location not found. Define location with sdk.dir in the local.properties file
```

### Solution:
**Create/Edit**: `android/local.properties`

Add this line (adjust path if needed):
```properties
sdk.dir=C:\\Users\\vivek\\AppData\\Local\\Android\\sdk
```

OR in Android Studio:
```
File → Project Structure → SDK Location → Android SDK location
→ Browse to: C:\Users\vivek\AppData\Local\Android\sdk
```

---

## 🔴 Error 8: "Execution failed for task ':app:mergeReleaseResources'"

### Error Message:
```
Execution failed for task ':app:mergeReleaseResources'
Resource compilation failed
```

### Solution:

**Option 1**: Clean and rebuild
```
Build → Clean Project
Build → Rebuild Project
```

**Option 2**: Check for duplicate resources

Open Terminal in Android Studio:
```bash
cd ..
flutter clean
flutter pub get
```

Then rebuild in Android Studio.

---

## 🔴 Error 9: "Invoke-customs are only supported starting with Android O"

### Error Message:
```
Invoke-customs are only supported starting with Android O (--min-api 26)
```

### Solution:
**File**: `android/app/build.gradle`

Update minSdk:
```groovy
android {
    defaultConfig {
        minSdk = 21  // Change if less than 21
        targetSdk = 34
    }
}
```

---

## 🔴 Error 10: "Gradle build daemon disappeared unexpectedly"

### Error Message:
```
Gradle build daemon disappeared unexpectedly (it may have been killed or may have crashed)
```

### Solution:

**Step 1**: Kill all Gradle processes

Windows PowerShell:
```powershell
taskkill /F /IM java.exe /T
```

**Step 2**: Delete Gradle daemon locks
```powershell
Remove-Item -Path "$env:USERPROFILE\.gradle\daemon" -Recurse -Force
```

**Step 3**: Restart Android Studio

---

## 🔴 Error 11: "Could not download [dependency].jar"

### Error Message:
```
Could not download kotlin-stdlib.jar
Could not GET 'https://...'
```

### Solution:

**Check Internet Connection** - Most common cause!

If internet is fine:

**File**: `android/build.gradle`

Add repositories in correct order:
```groovy
allprojects {
    repositories {
        google()
        mavenCentral()
        maven { url 'https://jitpack.io' }
    }
}
```

Then:
```
File → Sync Project with Gradle Files
```

---

## 🔴 Error 12: "Flutter SDK not found"

### Error Message:
```
Flutter SDK not found at specified path
```

### Solution:

**In Android Studio**:
```
File → Settings → Languages & Frameworks → Flutter
→ Flutter SDK path: C:\flutter
```

Verify in Terminal:
```bash
flutter doctor -v
```

---

## 🟡 Warning: "Gradle sync is taking longer than usual"

### When this happens:

✓ **Normal** - First sync can take 10-15 minutes
✓ Check bottom status bar for progress
✓ Check internet connection - downloads happening
✗ **If stuck > 30 minutes**:
   - Cancel sync
   - Restart Android Studio
   - Try again

---

## 🟢 Success Indicators

When build is working correctly, you'll see:

```
> Task :app:compileReleaseKotlin
> Task :app:compileReleaseJavaWithJavac
> Task :app:mergeReleaseResources
> Task :app:processReleaseResources
> Task :app:packageRelease

BUILD SUCCESSFUL in 8m 32s
```

**APK Generated Notification**:
```
APK(s) generated successfully for 1 module:
Module 'app': locate or analyze the APK
```

---

## 📋 Pre-Build Checklist

Before building, ensure:

- [ ] Correct folder opened (android folder, not parent)
- [ ] Gradle sync completed without errors
- [ ] Internet connection active
- [ ] No antivirus blocking Gradle downloads
- [ ] Sufficient disk space (10+ GB free)
- [ ] No red underlines in build.gradle files
- [ ] Android SDK properly installed

---

## 🔧 General Troubleshooting Steps

**When anything goes wrong**:

1. **Read the error message** - Last few lines usually have the actual error
2. **Check Build output** - Bottom "Build" tab has full logs
3. **Clean project** - `Build → Clean Project`
4. **Invalidate caches** - `File → Invalidate Caches → Invalidate and Restart`
5. **Check gradle.properties** - Ensure proper JVM args
6. **Update Gradle** - Use latest stable version
7. **Check internet** - Many errors are download failures
8. **Restart Android Studio** - Solves many weird issues

---

## 📞 Still Stuck?

If none of these work:

1. **Export error logs**:
   ```
   Help → Show Log in Explorer → Copy entire log
   ```

2. **Check Flutter doctor**:
   ```bash
   flutter doctor -v
   ```

3. **Try debug build first**:
   ```bash
   flutter build apk --debug
   ```
   Debug builds are simpler and reveal issues faster.

---

## 💡 Pro Tips

### Tip 1: Enable Offline Mode (After First Successful Build)
```
File → Settings → Build, Execution, Deployment → Gradle
→ ☑ Offline work
```
Speeds up subsequent builds.

### Tip 2: Increase Build Performance
**File**: `android/gradle.properties`
```properties
org.gradle.daemon=true
org.gradle.parallel=true
org.gradle.caching=true
org.gradle.configureondemand=true
```

### Tip 3: Watch Gradle Progress
```
View → Tool Windows → Build
```
See real-time task execution.

### Tip 4: Use Gradle Commands Directly
```
Right sidebar → Gradle → android → Tasks → build → assembleRelease
```
Double-click to build.

---

Happy Building! 🚀
