# Test Accounts Available

This document lists all test accounts available in the Study Tracker App for development and testing purposes.

## 📋 Available Test Accounts

### 1. **Primary Test Account (AcademicSeeder)**
**Location:** Created by `AcademicSeeder.php`

- **Email:** `test@studytracker.com`
- **Password:** `password`
- **Name:** `Test User`
- **University:** `Test University`
- **Semester:** `Fall 2024`
- **Status:** Email verified ✅

**Data Included:**
- ✅ 5 Courses (randomly generated)
- ✅ 10 Study Plans (linked to courses)
- ✅ 5 Academic Results (linked to courses)

**To Create This Account:**
```bash
cd backend/study_tracker_app
php artisan db:seed --class=AcademicSeeder
```

---

### 2. **Basic Test Account (DatabaseSeeder)**
**Location:** Created by `DatabaseSeeder.php`

- **Email:** `test@example.com`
- **Password:** `password` (default from UserFactory)
- **Name:** `Test User`
- **Status:** Email verified ✅

**Data Included:**
- ❌ No courses, study plans, or academic results

**To Create This Account:**
```bash
cd backend/study_tracker_app
php artisan db:seed
```

---

## 🔑 Default Password

**All test accounts use the default password:** `password`

This is set in:
- `UserFactory.php` (line 31): `Hash::make('password')`
- `AcademicSeeder.php` (line 26): `Hash::make('password')`

---

## 🧪 Test Accounts Used in Unit Tests

### LoginTest.php
- **Email:** `test@example.com`
- **Password:** `Password123!` (created dynamically in tests)

### RegisterTest.php
- **Email:** `test@example.com`
- **Password:** `Password123!` (used for registration tests)

**Note:** These are created and destroyed during test execution, not persistent accounts.

---

## 🚀 Quick Start - Create Test Accounts

### Option 1: Create Account with Full Data (Recommended)
```bash
cd backend/study_tracker_app
php artisan db:seed --class=AcademicSeeder
```

This creates:
- ✅ Test user: `test@studytracker.com` / `password`
- ✅ 5 courses
- ✅ 10 study plans
- ✅ 5 academic results

### Option 2: Create Basic Account
```bash
cd backend/study_tracker_app
php artisan db:seed
```

This creates:
- ✅ Test user: `test@example.com` / `password`
- ❌ No additional data

### Option 3: Create Multiple Test Users
```bash
cd backend/study_tracker_app
php artisan tinker
```

Then in tinker:
```php
User::factory()->count(5)->create();
```

This creates 5 random test users with:
- Random names and emails
- Password: `password`
- Random university and semester data

---

## 📝 Test Account Summary

| Email | Password | Data Included | Seeder |
|-------|----------|--------------|--------|
| `test@studytracker.com` | `password` | ✅ Courses, Plans, Results | AcademicSeeder |
| `test@example.com` | `password` | ❌ None | DatabaseSeeder |

---

## ⚠️ Security Notes

1. **Development Only:** These test accounts are for development and testing only
2. **Never Use in Production:** Do not use these accounts in production environments
3. **Change Passwords:** If deploying to staging, change all test account passwords
4. **Remove Test Accounts:** Consider removing test accounts before production deployment

---

## 🔄 Resetting Test Data

To reset and recreate test accounts:

```bash
cd backend/study_tracker_app

# Option 1: Fresh migration with seeding
php artisan migrate:fresh --seed

# Option 2: Seed specific seeder
php artisan db:seed --class=AcademicSeeder
```

---

## 📧 Google OAuth Test Accounts

For Google OAuth testing, you can use:
- Any Google account (personal or test account)
- Test users added in Google Cloud Console OAuth consent screen

**Note:** Google OAuth accounts don't require a password - they authenticate via Google.

---

## 🎯 Recommended Test Account

**For full testing experience, use:**
- **Email:** `test@studytracker.com`
- **Password:** `password`

This account has the most complete dataset for testing all features.
