# Telecaller CRM - Flutter Mobile App

A complete Flutter mobile application for telecallers to manage leads, tasks, prospects, and track call statistics.

## Features

### ✅ Completed Features

1. **Authentication**
   - Login with email/password
   - Token-based authentication
   - Auto-login support
   - Remember me functionality

2. **Dashboard**
   - Statistics cards (Pending Tasks, Completed Tasks)
   - Call statistics (Today's Calls, Talking Time, Incoming/Outgoing)
   - Quick actions
   - Pull to refresh

3. **Tasks Management**
   - View tasks with filters (Pending, Completed, Rescheduled, All)
   - Task cards with lead information
   - Call button integration
   - Post-call outcome popup (Interested, Not Interested, CNP, Call Again, Block)

4. **Leads Management**
   - Card view (2 per row on mobile, 4 per row on desktop)
   - Search functionality
   - Status filters
   - Call and WhatsApp buttons

5. **Prospects/Verification Pending**
   - Filter tabs (Pending, Approved, Rejected, All)
   - Prospect cards with details
   - WhatsApp integration
   - Rejection reason display

6. **Profile**
   - View profile information
   - Profile picture display
   - User information
   - Logout functionality

7. **Call Tracking**
   - Call duration tracking
   - Call statistics (today's calls, talking time, incoming/outgoing)
   - Call history
   - Integration with phone state listener

8. **Phone Integration**
   - Make phone calls
   - Open WhatsApp chat
   - Phone number sanitization

## Project Structure

```
lib/
├── main.dart
├── config/
│   ├── api_config.dart
│   └── theme_config.dart
├── models/
│   ├── user_model.dart
│   ├── lead_model.dart
│   ├── task_model.dart
│   ├── prospect_model.dart
│   ├── call_log_model.dart
│   └── api_response_model.dart
├── services/
│   ├── api_service.dart
│   ├── auth_service.dart
│   ├── storage_service.dart
│   └── call_tracking_service.dart
├── providers/
│   ├── auth_provider.dart
│   ├── task_provider.dart
│   ├── lead_provider.dart
│   ├── prospect_provider.dart
│   └── call_tracking_provider.dart
├── screens/
│   ├── auth/
│   │   ├── login_screen.dart
│   │   └── splash_screen.dart
│   ├── dashboard/
│   │   └── dashboard_screen.dart
│   ├── tasks/
│   │   └── task_list_screen.dart
│   ├── leads/
│   │   └── lead_list_screen.dart
│   ├── prospects/
│   │   └── prospect_list_screen.dart
│   ├── profile/
│   │   └── profile_screen.dart
│   └── calls/
│       └── call_statistics_screen.dart
└── utils/
    └── helpers.dart
```

## Setup Instructions

1. **Update API Base URL**
   - Open `lib/config/api_config.dart`
   - Update `baseUrl` with your server URL:
     ```dart
     static const String baseUrl = 'http://your-server.com/api';
     ```

2. **Install Dependencies**
   ```bash
   flutter pub get
   ```

3. **Run the App**
   ```bash
   flutter run
   ```

## Backend Requirements

The app requires the following backend endpoints:

- `POST /api/telecaller/login` - Login
- `GET /api/telecaller/whoami` - Get current user
- `POST /api/telecaller/logout` - Logout
- `GET /api/telecaller/tasks` - Get tasks
- `POST /api/telecaller/tasks/{id}/outcome` - Record outcome
- `GET /api/telecaller/leads` - Get leads
- `GET /api/telecaller/prospects` - Get prospects
- `POST /api/telecaller/prospects/create` - Create prospect
- `GET /api/telecaller/profile` - Get profile
- `PUT /api/telecaller/profile` - Update profile
- `POST /api/telecaller/call-logs` - Save call log
- `GET /api/telecaller/call-logs` - Get call logs
- `GET /api/telecaller/call-statistics` - Get call statistics

## Permissions

### Android (AndroidManifest.xml)
```xml
<uses-permission android:name="android.permission.READ_PHONE_STATE" />
<uses-permission android:name="android.permission.READ_CALL_LOG" />
<uses-permission android:name="android.permission.CALL_PHONE" />
```

### iOS (Info.plist)
```xml
<key>NSPhoneNumberUsageDescription</key>
<string>We need access to track your call duration</string>
```

## Notes

- The app uses Provider for state management
- All API calls are handled through Dio with interceptors
- Token is stored securely using SharedPreferences
- Call tracking requires phone state permissions
- The app supports both Android and iOS

## Next Steps

1. Run database migration for `call_logs` table:
   ```bash
   php artisan migrate
   ```

2. Test the app with a telecaller account

3. Configure push notifications (optional)

4. Add offline support (optional)

