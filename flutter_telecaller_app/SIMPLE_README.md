# Base CRM - Simple Flutter APK

## 🎯 Yeh Kya Hai?

Yeh ek **bahut hi simple** Flutter mobile app hai jo telecallers ke liye banaya gaya hai. Isme sirf **3 basic dependencies** hain:
- `http` - API calls ke liye
- `shared_preferences` - Login token save karne ke liye  
- `url_launcher` - Phone call aur WhatsApp open karne ke liye

## ✨ Features

### 1. **Login Screen**
- Email aur password se login
- Token save hota hai automatic
- Simple aur fast

### 2. **Dashboard (Home Tab)**
- Welcome message
- Stats cards:
  - Pending Tasks
  - Completed Tasks
  - Total Leads
  - Calls Today

### 3. **Tasks Tab**
- Dummy tasks list
- Phone call button
- Simple UI

### 4. **Leads Tab**
- Dummy leads list
- Phone call button
- WhatsApp button

### 5. **Profile Tab**
- User name display
- Settings option
- Logout button

## 🚀 APK Kaise Banaye?

### Method 1: Batch File (Sabse Easy)
```bash
BUILD_SIMPLE_APK.bat
```
Bas yeh file ko double-click karo. Automatically sab kuch ho jayega!

### Method 2: Manual Commands
```bash
# 1. Clean
flutter clean

# 2. Get dependencies
flutter pub get

# 3. Build APK
flutter build apk --release
```

## 📱 APK Kahaan Milega?

Build hone ke baad APK yahan milega:
```
flutter_telecaller_app/build/app/outputs/flutter-apk/app-release.apk
```

## ⚙️ API URL Kaise Update Karein?

APK banane se **pehle** API URL change karna zaroori hai:

1. Open file: `lib/main.dart`
2. Line 51 par jao
3. Yeh line dhundo:
```dart
Uri.parse('http://192.168.1.100:8007/api/telecaller/login'),
```
4. Apna server URL dalo:
```dart
Uri.parse('http://YOUR-IP:8007/api/telecaller/login'),
```

## 📦 Dependencies (Sirf 3!)

```yaml
dependencies:
  http: ^1.1.0              # API calls
  shared_preferences: ^2.2.2 # Local storage
  url_launcher: ^6.2.2      # Phone & WhatsApp
```

## 🔧 Requirements

- Flutter SDK installed hona chahiye
- Android SDK setup hona chahiye
- Internet connection (dependencies download ke liye)

## 📊 APK Size

Approximately **15-20 MB** (bahut chota!)

Pehle wala complex app: **40-50 MB**
Yeh simple app: **15-20 MB** ✅

## 🎨 Screens

1. **Login** → Email/Password
2. **Home** → Dashboard with stats
3. **Tasks** → Task list with call button
4. **Leads** → Lead list with call/WhatsApp
5. **Profile** → User info and logout

## 🔑 Test Login Credentials

Backend mein jo bhi telecaller account hai, woh use kar sakte ho.

Example:
- Email: `telecaller1@example.com`
- Password: `password123`

## ⚠️ Important Notes

1. **API URL** change karna mat bhoolna!
2. Phone call karne ke liye device mein **CALL_PHONE** permission chahiye
3. Yeh offline work nahi karega (API calls ke liye internet chahiye)
4. All data dummy hai (static) - Real API se connect karo to real data dikhega

## 🐛 Agar Error Aaye?

### Error: "Gradle build failed"
```bash
cd android
./gradlew clean
cd ..
flutter clean
flutter pub get
```

### Error: "SDK not found"
- Android SDK install karo
- Flutter doctor chala kar check karo:
```bash
flutter doctor -v
```

### Error: "Dependencies resolve nahi ho rahe"
```bash
flutter pub cache repair
flutter pub get
```

## 📞 Support

Koi problem ho to:
1. Check karo `flutter doctor`
2. Internet connection check karo
3. API URL sahi hai ya nahi check karo

## 🎉 Build Successful Hone Ke Baad

APK ko phone mein transfer karo aur install karo:
1. APK file ko phone mein copy karo
2. File manager se open karo
3. "Install" par click karo
4. "Unknown sources" allow karna pad sakta hai

Done! App ready hai! 🚀
