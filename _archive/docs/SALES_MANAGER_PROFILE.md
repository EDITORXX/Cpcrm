# Sales Manager Profile Section

## Overview
Sales Manager ab apna profile manage kar sakte hain, similar to Telecaller profile section. Isme team members ki list bhi dikhai deti hai.

## Features

### 1. Personal Information
- Name, Email, Phone edit kar sakte hain
- Profile Picture upload/change kar sakte hain
- Role, Manager, Joining Date display hota hai

### 2. Team Members Section ⭐
- Apni team ke sabhi members ki list
- Each member ka:
  - Name, Role, Email
  - Availability Status (Available/Absent)
  - Today's prospects count
  - Profile picture

### 3. Password Change
- Current password verify karke new password set kar sakte hain
- Minimum 8 characters required
- Password confirmation required

### 4. Activity History
- Recent login/logout activities
- IP address tracking
- Timestamp of each activity

## Technical Implementation

### Database
- **Table**: `sales_manager_profiles`
- **Fields**: 
  - `user_id` (foreign key to users table)
  - `team_size` (integer)
  - `preferences` (JSON)

### API Endpoints

#### Profile Management
```
GET    /api/sales-manager/profile              - Get profile with team members
PUT    /api/sales-manager/profile              - Update profile info
POST   /api/sales-manager/profile/picture      - Upload profile picture
POST   /api/sales-manager/profile/password     - Change password
```

#### Team Management
```
GET    /api/sales-manager/team/member/{id}     - Get team member details
GET    /api/sales-manager/team/performance     - Get team performance stats
```

### Web Routes
```
GET    /sales-manager/dashboard                - Dashboard
GET    /sales-manager/team                     - Team page
GET    /sales-manager/leads                    - Leads page
GET    /sales-manager/prospects                - Prospects page
GET    /sales-manager/reports                  - Reports page
GET    /sales-manager/profile                  - Profile page
```

## Models & Relationships

### User Model
```php
public function salesManagerProfile(): HasOne
{
    return $this->hasOne(SalesManagerProfile::class);
}

public function teamMembers(): HasMany
{
    return $this->hasMany(User::class, 'manager_id');
}
```

### SalesManagerProfile Model
```php
protected $fillable = [
    'user_id',
    'team_size',
    'preferences',
];

protected $casts = [
    'preferences' => 'array',
    'team_size' => 'integer',
];
```

## Usage

### Login
Sales Manager login karne ke baad automatically `/sales-manager/dashboard` pe redirect ho jayenge.

### Profile Access
Sidebar se "Profile" link pe click karke profile section access kar sakte hain.

### Edit Profile
1. Profile information edit karo
2. "Save Changes" button click karo
3. Success message milega

### Upload Profile Picture
1. Camera icon pe click karo
2. Image select karo (max 2MB, jpg/png)
3. Preview dekhke "Upload Picture" click karo

### Change Password
1. Current password enter karo
2. New password enter karo (min 8 characters)
3. Confirm password enter karo
4. "Save Changes" click karo

### View Team Members
Profile page pe automatically team members ki list dikhai deti hai with:
- Member name and role
- Availability status
- Today's prospect count

## Team Stats
Profile page pe team statistics display hoti hai:
- Total team members
- Active members count
- Available members count
- Today's total prospects by team

## Files Created/Modified

### New Files
1. `app/Models/SalesManagerProfile.php`
2. `app/Http/Controllers/Api/SalesManagerController.php`
3. `app/Http/Controllers/SalesManagerController.php`
4. `database/migrations/2026_01_02_064257_create_sales_manager_profiles_table.php`
5. `resources/views/sales-manager/layout.blade.php`
6. `resources/views/sales-manager/dashboard.blade.php`
7. `resources/views/sales-manager/sections/profile.blade.php`

### Modified Files
1. `app/Models/User.php` - Added salesManagerProfile relationship
2. `routes/api.php` - Added sales-manager API routes
3. `routes/web.php` - Added sales-manager web routes
4. `app/Http/Controllers/Auth/LoginController.php` - Added sales manager redirect logic

## Testing

### Test Credentials
Use existing sales manager credentials from `USER_CREDENTIALS.md`:

**Sales Manager 1:**
- Email: salesmanager1@realtorcrm.com
- Password: sm123

**Sales Manager 2:**
- Email: salesmanager2@realtorcrm.com
- Password: sm123

### Test Steps
1. Login with sales manager credentials
2. Navigate to Profile section
3. Edit personal information
4. Upload profile picture
5. Change password
6. View team members list
7. Check team statistics

## Future Enhancements
- Team member detail modal
- Performance charts for team
- Individual member performance tracking
- Team targets vs achievements
- Quick actions (message team member, assign leads)
- Team availability calendar
- Bulk actions for team management

## Notes
- Profile picture stored in `storage/app/public/profiles/`
- API authentication using Sanctum tokens
- CSRF protection enabled for all forms
- Real-time team stats calculation
- Activity logging for security audit

