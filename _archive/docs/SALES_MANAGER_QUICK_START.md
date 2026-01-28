# Sales Manager Profile - Quick Start Guide

## 🚀 Quick Start

### Step 1: Login
1. Open browser and go to: `http://localhost:8007/login`
2. Use Sales Manager credentials:
   - **Email**: `salesmanager1@realtorcrm.com`
   - **Password**: `sm123`
3. Click "Login"

### Step 2: Access Profile
After login, you'll be redirected to Sales Manager Dashboard.
- Click **"Profile"** in the left sidebar
- OR directly navigate to: `http://localhost:8007/sales-manager/profile`

## 📋 What You'll See

### Profile Page Sections:

#### 1. Profile Header
- Your avatar (with initial letter)
- Name and email
- **"Save Changes"** button at top right

#### 2. Personal Information
- **Editable Fields:**
  - Name
  - Phone
  - Email
- **Read-only Fields:**
  - Role: Sales Manager
  - Manager: (Your manager's name)
  - Joining Date

#### 3. My Team Section ⭐
Shows all your team members with:
- Member name and role
- Email address
- Availability status (Available/Absent)
- Today's prospects count
- Team statistics badge showing total members

**For Sales Manager 1:**
- Telecaller 1
- Telecaller 2

**For Sales Manager 2:**
- Telecaller 3
- Telecaller 4

#### 4. Change Password
- Current password field
- New password field (min 8 characters)
- Confirm password field
- Password show/hide toggle buttons

#### 5. Recent Activity
- Table showing your recent login/logout activities
- IP addresses
- Timestamps

## ✏️ How to Use

### Edit Your Profile
1. Change name, email, or phone in the form
2. Click **"Save Changes"** button at top
3. You'll see success message
4. Profile updates immediately

### Upload Profile Picture
1. Click the **camera icon** on your avatar
2. Select an image file (JPG or PNG, max 2MB)
3. Preview will show
4. Click **"Upload Picture"**
5. Avatar updates immediately

### Change Password
1. Enter your **current password**
2. Enter **new password** (minimum 8 characters)
3. **Confirm** the new password
4. Click **"Save Changes"** at top
5. Password fields will clear on success

### View Team Members
- Scroll to **"My Team"** section
- See all your team members
- Check who is available/absent
- View today's prospect counts
- See team statistics in the badge

## 🎯 Features

### What You Can Do:
✅ Edit personal information
✅ Upload/change profile picture
✅ Change password securely
✅ View all team members
✅ See team availability
✅ Monitor team performance
✅ View activity history

### Team Information:
- Total team members
- Active members count
- Available members count
- Individual member status
- Today's prospects per member

## 🔐 Security

- All changes require authentication
- Password change requires current password
- Activity logging for audit trail
- CSRF protection on all forms
- Secure API token authentication

## 📱 Navigation

### Sidebar Menu:
- **Dashboard** - Overview and stats
- **My Team** - Team management (coming soon)
- **Leads** - Lead management (coming soon)
- **Prospects** - Prospects list (coming soon)
- **Reports** - Performance reports (coming soon)
- **Profile** - Your profile page ✅

### Top Bar:
- Your name displayed
- **Logout** button

## 💡 Tips

1. **Save Changes Button**: One button saves all changes (profile info, password, picture)
2. **Password Visibility**: Click eye icon to show/hide passwords
3. **Image Preview**: Preview your profile picture before uploading
4. **Team Stats**: Badge shows real-time team statistics
5. **Activity History**: Track your login activities for security

## 🐛 Troubleshooting

### Profile Not Loading?
- Check if you're logged in
- Verify you're using sales manager credentials
- Clear browser cache and reload

### Can't Upload Picture?
- Check file size (must be < 2MB)
- Use JPG or PNG format only
- Try a different image

### Password Change Failed?
- Verify current password is correct
- New password must be at least 8 characters
- Make sure passwords match

### Team Members Not Showing?
- Verify you have team members assigned
- Check if you're logged in as sales manager
- Refresh the page

## 📞 Support

If you encounter any issues:
1. Check browser console for errors (F12)
2. Verify database migrations ran successfully
3. Check Laravel logs: `storage/logs/laravel.log`
4. Ensure API routes are working: `php artisan route:list`

## 🎉 Success Checklist

After implementation, verify:
- ✅ Can login as sales manager
- ✅ Redirected to sales manager dashboard
- ✅ Can access profile page
- ✅ Can edit personal information
- ✅ Can upload profile picture
- ✅ Can change password
- ✅ Can see team members list
- ✅ Team statistics display correctly
- ✅ Activity history shows recent logins

## 🔗 Useful URLs

- **Login**: `http://localhost:8007/login`
- **Dashboard**: `http://localhost:8007/sales-manager/dashboard`
- **Profile**: `http://localhost:8007/sales-manager/profile`
- **API Profile**: `http://localhost:8007/api/sales-manager/profile`

## 📚 Documentation

For detailed technical documentation, see:
- `SALES_MANAGER_PROFILE.md` - Complete feature documentation
- `IMPLEMENTATION_SUMMARY.md` - Implementation details
- `USER_CREDENTIALS.md` - All user credentials

---

**Enjoy your new Sales Manager Profile Section! 🎊**

