# Study Tracker App - TODO List

## Overview
This document contains all remaining tasks, incomplete features, and planned enhancements for the Study Tracker App.

---

## 🔴 Critical TODOs (MVP Completion)

### Backend Tasks

#### 1. Google Cloud Vision OCR Integration
- **File**: `backend/study_tracker_app/app/Services/TimetableOcrService.php`
- **Status**: Stub exists, needs Google Cloud Vision API implementation
- **Current State**: Returns empty array, commented code shows structure
- **Action Required**: 
  - Implement Google Cloud Vision API integration
  - Parse extracted text to find courses, times, locations
  - Return structured data with confidence scores
- **Dependencies**: Google Cloud Vision API credentials configured

#### 2. Timetable Upload Processing
- **Files**: 
  - `backend/study_tracker_app/app/Http/Controllers/TimetableController.php`
  - `backend/study_tracker_app/app/Jobs/ProcessTimetableUploadJob.php`
- **Status**: Endpoint exists, needs OCR result processing
- **Current State**: TODO comment at line 107 in TimetableController, line 51 in ProcessTimetableUploadJob
- **Action Required**: 
  - Create timetable and classes from parsed OCR data
  - Handle validation and error cases
  - Store parsed classes in database

#### 3. Google OAuth Authentication (Frontend)
- **Files**: 
  - `frontend/src/Pages/Auth/Login.vue` (line 158)
  - `frontend/src/Pages/Auth/Register.vue` (line 179)
- **Status**: Backend endpoint exists (`/api/v1/auth/google`), frontend not implemented
- **Current State**: Console.log placeholder, TODO comment
- **Action Required**: 
  - Implement Google OAuth flow in frontend
  - Handle OAuth callback
  - Store authentication token
- **Backend**: Already implemented in `GoogleAuthController.php`

#### 4. Password Change Functionality (Frontend)
- **File**: `frontend/src/Components/Settings/AccountSettingsSection.vue`
- **Status**: Backend implemented, frontend bypasses API call
- **Current State**: Uses console.log and alert, bypasses API (line 127-128)
- **Action Required**: 
  - Connect frontend to `/api/v1/settings/change-password` endpoint
  - Handle success/error responses
  - Show proper validation errors

#### 5. Data Export Implementation
- **File**: `backend/study_tracker_app/app/Http/Controllers/SettingsController.php` (line 129)
- **Status**: Stub exists, returns placeholder response
- **Current State**: TODO comment, returns estimated time only
- **Action Required**: 
  - Create data export job
  - Export user data in JSON/CSV/PDF formats
  - Send email with download link
  - Handle large data sets

#### 6. Account Deletion with Grace Period
- **File**: `backend/study_tracker_app/app/Http/Controllers/SettingsController.php` (line 149)
- **Status**: Immediate deletion, needs 30-day grace period
- **Current State**: TODO comment, immediately deletes user
- **Action Required**: 
  - Schedule soft delete with 30-day delay
  - Create scheduled job for permanent deletion
  - Send confirmation email
  - Allow account recovery within grace period

#### 7. Cloud Storage Configuration (S3/Cloudflare R2)
- **Files**: 
  - `backend/study_tracker_app/app/Http/Controllers/SettingsController.php` (line 50)
  - `backend/study_tracker_app/app/Http/Controllers/FileUploadController.php` (line 35)
- **Status**: Using local storage, needs cloud storage
- **Current State**: TODO comments, using local 'public' disk
- **Action Required**: 
  - Configure S3 or Cloudflare R2 storage
  - Update file upload logic
  - Update avatar upload logic
  - Generate proper URLs for cloud storage

### Frontend Tasks

#### 8. Timetable Upload Review Modal
- **File**: `frontend/src/Pages/Timetable.vue` (line 220)
- **Status**: Alert shown, no review modal
- **Current State**: TODO comment, shows alert with parsed classes count
- **Action Required**: 
  - Create review modal component
  - Display parsed classes in editable table
  - Allow user to confirm/edit/delete parsed classes
  - Submit confirmed classes to backend

#### 9. Analytics Page Implementation
- **File**: `frontend/src/Pages/Progress/Analytics.vue`
- **Status**: Placeholder "coming soon" message
- **Current State**: Shows placeholder card only
- **Action Required**: 
  - Implement analytics dashboard
  - Study consistency heatmap
  - Time distribution by subject (pie chart)
  - Productivity trends over time
  - Weak areas identification
  - Custom date range analytics
  - Export analytics data

#### 10. Data Export UI
- **File**: `frontend/src/Components/Settings/AccountSettingsSection.vue` (line 142-145)
- **Status**: Alert placeholder
- **Current State**: Console.log and alert, bypasses API
- **Action Required**: 
  - Implement export request UI
  - Show export status/progress
  - Display download link when ready
  - Handle different export formats (JSON/CSV/PDF)

---

## 🟡 Phase 2 Features (Not Started)

### 11. AI Coach - Google Gemini Integration
- **File**: `backend/study_tracker_app/app/Http/Controllers/AiCoachController.php` (line 32)
- **Status**: Returns mock response
- **Current State**: TODO comment, returns hardcoded response
- **Action Required**: 
  - Integrate Google Gemini API
  - Pass user context (course performance, study patterns)
  - Generate contextual AI responses
  - Handle streaming responses (optional)
  - Implement rate limiting and cost controls

### 12. Weekly Report Enhancements
- **File**: `backend/study_tracker_app/app/Services/WeeklyReportService.php` (lines 154-155)
- **Status**: Missing productivity trends and weak areas
- **Current State**: Returns empty arrays for:
  - `productivity_trends` (TODO: Implement)
  - `weak_areas` (TODO: Implement)
- **Action Required**: 
  - Calculate productivity trends over time
  - Identify weak areas based on performance data
  - Generate actionable insights
  - Add visualizations for trends

### 13. Dashboard GPA Trend Calculation
- **File**: `backend/study_tracker_app/app/Services/DashboardService.php` (line 212)
- **Status**: Hardcoded 'improving' trend
- **Current State**: TODO comment, returns static 'improving' value
- **Action Required**: 
  - Calculate actual GPA trend from historical data
  - Compare current GPA with previous periods
  - Return: 'improving', 'declining', or 'stable'
  - Calculate percentage change

### 14. Bulk CSV Upload for Results
- **File**: `backend/study_tracker_app/app/Http/Controllers/PerformanceController.php` (line 82)
- **Status**: Stub exists
- **Current State**: TODO comment, returns 0 uploaded
- **Action Required**: 
  - Implement CSV parsing
  - Validate CSV format and data
  - Bulk insert academic results
  - Handle errors and partial uploads
  - Return detailed upload results

---

## 🟢 Phase 3 Features (Future)

### Mobile & Platform Features
- **15. Mobile Native Apps** (iOS + Android)
  - React Native or Flutter implementation
  - Push notifications
  - Offline mode support

- **16. Offline Mode**
  - Local data storage
  - Sync when online
  - Conflict resolution

- **17. Social Features**
  - Study groups
  - Peer comparison (opt-in)
  - Collaborative study plans

### Advanced Features
- **18. Mentor/Advisor Accounts**
  - Multi-role authentication
  - Student-mentor relationships
  - Progress sharing with permissions

- **19. Gamification**
  - Study streaks tracking
  - Badges and achievements
  - Leaderboards (optional)

- **20. Calendar Integration**
  - Google Calendar sync
  - Outlook integration
  - Two-way sync for study plans

- **21. Browser Extension**
  - Quick study session logging
  - Assignment reminders
  - Grade calculator

- **22. Pomodoro Timer Integration**
  - Built-in timer
  - Automatic study session tracking
  - Break reminders

- **23. Flashcard System**
  - Create and manage flashcards
  - Spaced repetition algorithm
  - Study mode with progress tracking

---

## 📋 Priority Recommendations

### Immediate Priority (MVP Blockers)
These should be completed first to finish core functionality:

1. ✅ **Google OAuth Frontend Implementation** - Required for user authentication
2. ✅ **Password Change Frontend Connection** - Basic security feature
3. ✅ **Timetable OCR Review Modal** - Core timetable feature
4. ✅ **Timetable Upload Processing** - Complete OCR workflow

### High Priority (MVP Polish)
These improve user experience and complete MVP:

5. ✅ **Data Export Implementation** - User data portability
6. ✅ **Account Deletion Grace Period** - Proper account management
7. ✅ **Analytics Page Implementation** - Core feature from PRD
8. ✅ **Cloud Storage Configuration** - Production-ready file storage

### Phase 2 Priority
These enhance the app with advanced features:

9. ✅ **AI Coach Gemini Integration** - Major Phase 2 feature
10. ✅ **Weekly Report Enhancements** - Complete analytics
11. ✅ **GPA Trend Calculation** - Accurate dashboard data
12. ✅ **CSV Bulk Upload** - User convenience feature

---

## 📝 Notes

### Technical Debt
- Rate limiting temporarily disabled (mentioned in `bootstrap/app.php`)
- Some frontend components bypass API calls with console.log/alert
- Local storage used instead of cloud storage

### Dependencies Required
- Google Cloud Vision API credentials for OCR
- Google Gemini API credentials for AI Coach
- AWS S3 or Cloudflare R2 for file storage
- Redis for queues (currently using database queue)

### Testing Needs
- Integration tests for OCR processing
- E2E tests for OAuth flow
- Unit tests for GPA trend calculation
- Performance tests for bulk uploads

---

## 🎯 Completion Status

- **MVP (Phase 1)**: ~85% Complete
- **Phase 2**: ~10% Complete
- **Phase 3**: 0% Complete

---

## 📅 Last Updated
Generated from codebase analysis on current date.

