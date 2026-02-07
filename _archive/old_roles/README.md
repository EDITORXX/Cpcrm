# Old Role Files Archive

## Purpose
This folder contains archived files from the old role naming convention before the role restructure on 2026-02-03.

## What Changed

### Role Name Changes:
1. **Telecaller** → **Sales Executive** (promoted)
2. **Sales Executive** → **Assistant Sales Manager** (promoted)

### New Roles Added:
- HR Manager
- Finance Manager  
- Senior Manager
- Telecaller (new role for verification only)

## Archived Files

### Controllers
- `TelecallerController.php` - Original telecaller controller (now SalesExecutiveController.php)

### Views
- `telecaller/` - All telecaller views (now sales-executive/)

### Models
- `TelecallerProfile.php` - Still in use but renamed conceptually
- `TelecallerTask.php` - Still in use
- `TelecallerDailyLimit.php` - Still in use

## Updated Role Structure

```
ADMIN (Owner)
    │
    ├── CRM (Operations Manager)
    │
    ├── HR Manager
    │
    ├── Finance Manager
    │
    └── Sales Manager
        │
        └── Senior Manager
            │
            └── Assistant Sales Manager
                │
                └── Sales Executive (Caller/Field Executive)
                    │
                    └── Telecaller (Verification)
```

## Backward Compatibility

The system maintains backward compatibility:
- Old route `/telecaller/*` redirects to `/sales-executive/*`
- `isTelecaller()` method checks both 'telecaller' and 'sales_executive' roles
- Role constants: `Role::TELECALLER` points to 'sales_executive' for compatibility

## Migration Date
**Date:** February 3, 2026  
**By:** System Update

## Notes
- Do not delete these files - they serve as reference and backup
- All active code now uses the new naming convention
- Database migrations were run to update role names and add new roles
