---
name: Database Import and Role Mapping
overview: Import data from external SQL file, map roles correctly (SALE PROSPECT → TELECALLER, MANGER → MANAGER, ADMIN → ADMIN), create Sales Head user (Arpit), and copy all visit data, prospect data, and other related data
todos:
  - id: analyze-sql-file
    content: Read and analyze SQL file structure to understand tables, users, roles, visits, and prospects
    status: pending
  - id: check-existing-roles
    content: Check existing roles in database and create Sales Head role if needed
    status: pending
  - id: create-import-command
    content: Create Artisan command for importing and mapping data
    status: pending
  - id: implement-role-mapping
    content: Implement role mapping logic (SALE PROSPECT→TELECALLER, MANGER→MANAGER, ADMIN→ADMIN)
    status: pending
  - id: create-arpit-user
    content: Create new user 'Arpit' with Sales Head role
    status: pending
  - id: import-user-data
    content: Import all users with correct role mapping
    status: pending
  - id: import-visit-data
    content: Import all visit/site visit data with proper user ID mapping
    status: pending
  - id: import-prospect-data
    content: Import all prospect data with proper user ID mapping
    status: pending
  - id: import-other-data
    content: Import all other related data (leads, meetings, etc.)
    status: pending
  - id: validate-import
    content: Validate imported data integrity and relationships
    status: pending
---

# Database Import and Role Mapping Plan

## Overview

Import data from external SQL file (`u188221078_team9098.sql`) and properly map roles, create new Sales Head user, and migrate all related data (visits, prospects, etc.)

## Implementation Details

### 1. Analyze SQL File Structure

- Read and analyze the SQL file to understand:
- Table structure
- User data and roles
- Visit data structure
- Prospect data structure
- Relationships between tables
- Foreign key constraints

### 2. Role Mapping Strategy

- **SALE PROSPECT** → **TELECALLER** (telecaller role)
- **MANGER** → **MANAGER** (sales_manager role)
- **ADMIN** → **ADMIN** (admin role)
- **Other roles** → Copy as-is (if they exist in our system)
- **New Role**: Create **SALE HEAD** (sales_head role) if not exists

### 3. Create Migration Script

- Create a Laravel command or migration script to:
- Read the SQL file
- Parse user data
- Map roles according to mapping strategy
- Create/update users with correct roles
- Create new user "Arpit" with Sales Head role
- Import visit data
- Import prospect data
- Import all other related data
- Handle foreign key relationships
- Preserve data integrity

### 4. Data Import Steps

**Step 1: Role Setup**

- Check if all required roles exist in `roles` table
- Create missing roles (especially Sales Head if needed)
- Map old role names to new role IDs

**Step 2: User Import**

- Import all users from SQL file
- Map roles correctly:
- SALE PROSPECT users → telecaller role
- MANGER users → sales_manager role
- ADMIN users → admin role
- Create new user "Arpit" with Sales Head role
- Preserve user passwords (if hashed) or set default password

**Step 3: Visit Data Import**

- Import all visit/site visit data
- Map user IDs to new user IDs
- Preserve all visit details, dates, statuses

**Step 4: Prospect Data Import**

- Import all prospect data
- Map telecaller IDs to new user IDs
- Map manager IDs to new user IDs
- Preserve all prospect details

**Step 5: Other Data Import**

- Import any other related data (leads, meetings, etc.)
- Maintain relationships and foreign keys

### 5. Validation and Safety

- Backup current database before import
- Validate data integrity after import
- Check foreign key relationships
- Verify role assignments
- Test with sample queries

## Files to Create

1. `database/migrations/xxxx_import_external_database.php` - Migration for import

OR

2. `app/Console/Commands/ImportExternalDatabase.php` - Artisan command for import

## Implementation Approach

**Option 1: Laravel Migration**

- Create migration that reads SQL file
- Processes and imports data
- Maps roles correctly

**Option 2: Artisan Command**

- More flexible for complex logic
- Can be run multiple times with safety checks
- Better error handling

**Option 3: Direct SQL Import with Post-Processing**

- Import SQL file directly
- Run post-processing script to fix roles
- Update user roles
- Create Arpit user

## Recommended Approach

Use **Artisan Command** as it provides:

- Better error handling
- Progress tracking
- Ability to run safely multiple times
- Better logging
- Can validate before committing

## Steps in Command

1. **Read SQL File**

- Parse SQL dump file
- Extract table data

2. **Role Mapping**

- Create role mapping array
- Ensure all roles exist

3. **User Import**

- Import users with role mapping
- Create Arpit user

4. **Data Import**

- Import visits
- Import prospects
- Import other related data

5. **Relationship Fixing**

- Update foreign keys
- Fix user references

6. **Validation**

- Verif
- Verifyy data integrity
- Check counts
- Validate relationships