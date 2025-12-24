BACKEND DATABASE SCHEMA

📘 Backend Product Requirements Document (PRD)
Product Name: FocusTrack Backend API
API Version: 1.0
 Architecture: RESTful API + Real-time Services
 Primary Framework: Laravel 10+ / Node.js (Express)

1. Backend Overview & Architecture
System Architecture
┌─────────────────┐
│   Vue.js +      │
│   Inertia.js    │
│   Frontend      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   API Gateway   │
│   (Laravel)     │
└────────┬────────┘
         │
    ┌────┴────┐
    ▼         ▼
┌────────┐ ┌──────────┐
│  REST  │ │  Queue   │
│  API   │ │  System  │
└───┬────┘ └────┬─────┘
    │           │
    ▼           ▼
┌──────────────────────┐
│   PostgreSQL DB      │
└──────────────────────┘
         │
         ▼
┌──────────────────────┐
│   Redis Cache        │
└──────────────────────┘
Technology Stack Recommendation
Option 1: Laravel (Recommended for MVP)
Framework: Laravel 10+
Database: mysql
Cache: Redis 7+
Queue: Laravel Queue with Redis driver
Search: Laravel Scout (optional)
File Storage: AWS S3 / Cloudflare R2
Email: Laravel Mail with SendGrid/AWS SES
Authentication: Laravel Sanctum (SPA) 



2. Core Backend Objectives
Primary Goals
Data Integrity: Ensure accurate tracking of academic performance and study activities
Scalability: Support 100,000+ concurrent users
Reliability: 99.5% uptime SLA with automated backups
Security: Protect sensitive academic data with encryption and access controls
Performance: API response time < 200ms for 95th percentile
Automation: Reliable email delivery and notification systems
Success Metrics
API uptime: 99.5%
Average response time: < 150ms
Email delivery rate: > 98%
Zero data loss incidents
Successful handling of 1000+ requests/second

3. Database Schema Design
Database: PostgreSQL
3.1 Users Table
sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    avatar_url TEXT,
    university VARCHAR(255),
    semester VARCHAR(100),
    onboarding_completed BOOLEAN DEFAULT FALSE,
    onboarding_step VARCHAR(50) DEFAULT 'profile',
    google_id VARCHAR(255) UNIQUE,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_google_id ON users(google_id);
CREATE INDEX idx_users_created_at ON users(created_at);
3.2 User Preferences Table
sql
CREATE TABLE user_preferences (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    morning_email_time TIME DEFAULT '07:00:00',
    reminder_time TIME DEFAULT '21:00:00',
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    weekly_report_enabled BOOLEAN DEFAULT TRUE,
    weekly_report_day VARCHAR(20) DEFAULT 'Sunday',
    timezone VARCHAR(50) DEFAULT 'UTC',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(user_id)
);

CREATE INDEX idx_user_preferences_user_id ON user_preferences(user_id);
3.3 Courses Table
sql
CREATE TABLE courses (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    credits INTEGER NOT NULL DEFAULT 3,
    instructor VARCHAR(255),
    color VARCHAR(50) DEFAULT 'blue',
    semester VARCHAR(100) NOT NULL,
    academic_year VARCHAR(20),
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_courses_user_id ON courses(user_id);
CREATE INDEX idx_courses_semester ON courses(semester);
CREATE INDEX idx_courses_active ON courses(is_active);
3.4 Academic Results Table
sql
CREATE TABLE academic_results (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id UUID NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    assessment_type VARCHAR(100) NOT NULL, -- 'quiz', 'midterm', 'final', 'assignment', 'project'
    assessment_name VARCHAR(255),
    score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    percentage DECIMAL(5,2) GENERATED ALWAYS AS ((score / max_score) * 100) STORED,
    grade VARCHAR(5),
    weight DECIMAL(5,2), -- percentage weight in final grade
    semester VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    CONSTRAINT chk_score_positive CHECK (score >= 0),
    CONSTRAINT chk_max_score_positive CHECK (max_score > 0),
    CONSTRAINT chk_score_not_exceed_max CHECK (score <= max_score)
);

CREATE INDEX idx_results_user_id ON academic_results(user_id);
CREATE INDEX idx_results_course_id ON academic_results(course_id);
CREATE INDEX idx_results_date ON academic_results(date);
CREATE INDEX idx_results_semester ON academic_results(semester);
CREATE INDEX idx_results_user_course ON academic_results(user_id, course_id);
3.5 Study Plans Table
sql
CREATE TABLE study_plans (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id UUID NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    topic VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    planned_duration INTEGER NOT NULL, -- in minutes
    actual_duration INTEGER, -- in minutes
    priority VARCHAR(20) NOT NULL DEFAULT 'medium', -- 'high', 'medium', 'low'
    study_type VARCHAR(50) NOT NULL DEFAULT 'review', -- 'review', 'new-material', 'practice'
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'in-progress', 'completed', 'missed'
    completed_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    CONSTRAINT chk_priority_valid CHECK (priority IN ('high', 'medium', 'low')),
    CONSTRAINT chk_study_type_valid CHECK (study_type IN ('review', 'new-material', 'practice')),
    CONSTRAINT chk_status_valid CHECK (status IN ('pending', 'in-progress', 'completed', 'missed')),
    CONSTRAINT chk_planned_duration_positive CHECK (planned_duration > 0)
);

CREATE INDEX idx_plans_user_id ON study_plans(user_id);
CREATE INDEX idx_plans_course_id ON study_plans(course_id);
CREATE INDEX idx_plans_date ON study_plans(date);
CREATE INDEX idx_plans_status ON study_plans(status);
CREATE INDEX idx_plans_user_date ON study_plans(user_id, date);
CREATE INDEX idx_plans_priority ON study_plans(priority);
3.6 Timetables Table
sql
CREATE TABLE timetables (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    semester VARCHAR(100) NOT NULL,
    academic_year VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    UNIQUE(user_id, semester, academic_year)
);

CREATE INDEX idx_timetables_user_id ON timetables(user_id);
CREATE INDEX idx_timetables_active ON timetables(is_active);
3.7 Timetable Classes Table
sql
CREATE TABLE timetable_classes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    timetable_id UUID NOT NULL REFERENCES timetables(id) ON DELETE CASCADE,
    course_id UUID NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    day_of_week INTEGER NOT NULL, -- 0=Sunday, 1=Monday, ..., 6=Saturday
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(255),
    class_type VARCHAR(50) DEFAULT 'lecture', -- 'lecture', 'lab', 'tutorial', 'seminar'
    instructor VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    CONSTRAINT chk_day_valid CHECK (day_of_week BETWEEN 0 AND 6),
    CONSTRAINT chk_time_valid CHECK (end_time > start_time),
    CONSTRAINT chk_class_type_valid CHECK (class_type IN ('lecture', 'lab', 'tutorial', 'seminar'))
);

CREATE INDEX idx_timetable_classes_timetable_id ON timetable_classes(timetable_id);
CREATE INDEX idx_timetable_classes_course_id ON timetable_classes(course_id);
CREATE INDEX idx_timetable_classes_day ON timetable_classes(day_of_week);
3.8 Assignments Table
sql
CREATE TABLE assignments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id UUID NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date TIMESTAMP NOT NULL,
    priority VARCHAR(20) DEFAULT 'medium', -- 'high', 'medium', 'low'
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'in-progress', 'completed', 'overdue'
    completed_at TIMESTAMP,
    reminder_sent BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    
    CONSTRAINT chk_assignment_priority CHECK (priority IN ('high', 'medium', 'low')),
    CONSTRAINT chk_assignment_status CHECK (status IN ('pending', 'in-progress', 'completed', 'overdue'))
);

CREATE INDEX idx_assignments_user_id ON assignments(user_id);
CREATE INDEX idx_assignments_course_id ON assignments(course_id);
CREATE INDEX idx_assignments_due_date ON assignments(due_date);
CREATE INDEX idx_assignments_status ON assignments(status);
CREATE INDEX idx_assignments_user_status ON assignments(user_id, status);
3.9 Notifications Table
sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- 'reminder', 'assignment', 'achievement', 'system', 'report'
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP,
    metadata JSONB, -- additional structured data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_notification_type CHECK (type IN ('reminder', 'assignment', 'achievement', 'system', 'report'))
);

CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);
CREATE INDEX idx_notifications_user_unread ON notifications(user_id, is_read) WHERE is_read = FALSE;
3.10 Email Logs Table
sql
CREATE TABLE email_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    email_type VARCHAR(50) NOT NULL, -- 'morning-plan', 'reminder', 'weekly-report', 'verification'
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending', -- 'pending', 'sent', 'failed', 'bounced'
    sent_at TIMESTAMP,
    error_message TEXT,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_email_status CHECK (status IN ('pending', 'sent', 'failed', 'bounced'))
);

CREATE INDEX idx_email_logs_user_id ON email_logs(user_id);
CREATE INDEX idx_email_logs_status ON email_logs(status);
CREATE INDEX idx_email_logs_email_type ON email_logs(email_type);
CREATE INDEX idx_email_logs_created_at ON email_logs(created_at);
3.11 Study Sessions Table
sql
CREATE TABLE study_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    study_plan_id UUID REFERENCES study_plans(id) ON DELETE SET NULL,
    course_id UUID REFERENCES courses(id) ON DELETE SET NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP,
    duration INTEGER GENERATED ALWAYS AS (
        EXTRACT(EPOCH FROM (end_time - start_time)) / 60
    ) STORED, -- in minutes
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_session_time_valid CHECK (end_time IS NULL OR end_time > start_time)
);

CREATE INDEX idx_sessions_user_id ON study_sessions(user_id);
CREATE INDEX idx_sessions_plan_id ON study_sessions(study_plan_id);
CREATE INDEX idx_sessions_start_time ON study_sessions(start_time);
3.12 Weekly Reports Table
sql
CREATE TABLE weekly_reports (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    week_start_date DATE NOT NULL,
    week_end_date DATE NOT NULL,
    total_study_hours DECIMAL(5,2) DEFAULT 0.00,
    planned_hours DECIMAL(5,2) DEFAULT 0.00,
    completion_rate DECIMAL(5,2) DEFAULT 0.00,
    most_studied_course_id UUID REFERENCES courses(id) ON DELETE SET NULL,
    least_studied_course_id UUID REFERENCES courses(id) ON DELETE SET NULL,
    performance_trend VARCHAR(20), -- 'improving', 'declining', 'stable'
    ai_insights TEXT,
    report_data JSONB, -- full report structured data
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    email_sent_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(user_id, week_start_date),
    CONSTRAINT chk_performance_trend CHECK (performance_trend IN ('improving', 'declining', 'stable'))
);

CREATE INDEX idx_reports_user_id ON weekly_reports(user_id);
CREATE INDEX idx_reports_week_start ON weekly_reports(week_start_date);
CREATE INDEX idx_reports_generated_at ON weekly_reports(generated_at);
3.13 AI Chat History Table (Phase 2)
sql
CREATE TABLE ai_chat_history (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_id UUID NOT NULL,
    role VARCHAR(20) NOT NULL, -- 'user', 'assistant'
    message TEXT NOT NULL,
    context_data JSONB, -- user's academic data used for context
    tokens_used INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_role_valid CHECK (role IN ('user', 'assistant'))
);

CREATE INDEX idx_chat_user_id ON ai_chat_history(user_id);
CREATE INDEX idx_chat_session_id ON ai_chat_history(session_id);
CREATE INDEX idx_chat_created_at ON ai_chat_history(created_at);
3.14 File Uploads Table
sql
CREATE TABLE file_uploads (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path TEXT NOT NULL,
    file_type VARCHAR(100) NOT NULL, -- 'timetable', 'result', 'avatar'
    mime_type VARCHAR(100) NOT NULL,
    file_size INTEGER NOT NULL, -- in bytes
    storage_driver VARCHAR(50) DEFAULT 's3', -- 's3', 'local', 'cloudflare'
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_uploads_user_id ON file_uploads(user_id);
CREATE INDEX idx_uploads_file_type ON file_uploads(file_type);
CREATE INDEX idx_uploads_created_at ON file_uploads(created_at);
3.15 Password Reset Tokens Table
sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    PRIMARY KEY (email, token)
);

CREATE INDEX idx_password_resets_email ON password_reset_tokens(email);
CREATE INDEX idx_password_resets_expires ON password_reset_tokens(expires_at);
3.16 Personal Access Tokens Table (For API Authentication)
sql
CREATE TABLE personal_access_tokens (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT,
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tokens_tokenable ON personal_access_tokens(tokenable_type, tokenable_id);
CREATE INDEX idx_tokens_token ON personal_access_tokens(token);
3.17 Activity Logs Table (Audit Trail)
sql
CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    log_name VARCHAR(255),
    description TEXT NOT NULL,
    subject_type VARCHAR(255),
    subject_id UUID,
    event VARCHAR(255),
    causer_type VARCHAR(255),
    causer_id UUID,
    properties JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_subject ON activity_logs(subject_type, subject_id);
CREATE INDEX idx_activity_logs_event ON activity_logs(event);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);

4. API Endpoints Specification
Base URL: /api/v1
4.1 Authentication Endpoints
POST /auth/register
Purpose: Register new user account
Request Body:
json
{
  "name": "John Doe",
  "email": "john.doe@university.edu",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "university": "University of Technology"
}
Response (201):
json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": "usr_001",
      "name": "John Doe",
      "email": "john.doe@university.edu",
      "avatar_url": null,
      "onboarding_completed": false
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
Validation Rules:
name: required, string, max 255
email: required, email, unique
password: required, min 8, confirmed

POST /auth/login
Purpose: Authenticate user
Request Body:
json
{
  "email": "john.doe@university.edu",
  "password": "SecurePass123!",
  "remember": true
}
Response (200):
json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "usr_001",
      "name": "John Doe",
      "email": "john.doe@university.edu",
      "avatar_url": "https://...",
      "onboarding_completed": true
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}

POST /auth/logout
Purpose: Logout user and invalidate token
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Logged out successfully"
}

POST /auth/google
Purpose: Authenticate with Google OAuth
Request Body:
json
{
  "token": "google_oauth_token_here"
}
Response (200):
json
{
  "success": true,
  "message": "Google authentication successful",
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}

POST /auth/forgot-password
Purpose: Request password reset
Request Body:
json
{
  "email": "john.doe@university.edu"
}
Response (200):
json
{
  "success": true,
  "message": "Password reset link sent to your email"
}

POST /auth/reset-password
Purpose: Reset password with token
Request Body:
json
{
  "token": "reset_token_here",
  "email": "john.doe@university.edu",
  "password": "NewSecurePass123!",
  "password_confirmation": "NewSecurePass123!"
}
Response (200):
json
{
  "success": true,
  "message": "Password reset successful"
}

GET /auth/me
Purpose: Get current authenticated user
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "data": {
    "user": {
      "id": "usr_001",
      "name": "John Doe",
      "email": "john.doe@university.edu",
      "avatar_url": "https://...",
      "university": "University of Technology",
      "semester": "3rd Semester",
      "onboarding_completed": true,
      "preferences": {
        "morning_email_time": "07:00",
        "reminder_time": "21:00",
        "email_notifications": true,
        "push_notifications": true
      }
    }
  }
}

4.2 Dashboard Endpoints
GET /dashboard/stats
Purpose: Get dashboard statistics
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "data": {
    "overall_performance": 85,
    "courses_enrolled": 4,
    "hours_studied": 156,
    "completion_rate": 78,
    "assignments_due": 2,
    "gpa": {
      "current": 3.45,
      "trend": "improving"
    }
  }
}

GET /dashboard/upcoming-classes
Purpose: Get upcoming classes
Headers: Authorization: Bearer {token}
Query Parameters:
limit (optional): default 5
days (optional): default 7
Response (200):
json
{
  "success": true,
  "data": {
    "classes": [
      {
        "id": "cls_001",
        "course_name": "Data Structures & Algorithms",
        "course_code": "CS201",
        "instructor": "Dr. Sarah Johnson",
        "date": "2024-12-23",
        "start_time": "10:00:00",
        "end_time": "11:30:00",
        "location": "Room 301",
        "type": "lecture"
      }
    ]
  }
}

GET /dashboard/study-streak
Purpose: Get study streak information
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "data": {
    "current_streak": 5,
    "longest_streak": 12,
    "days": [
      { "day": "Mon", "completed": true },
      { "day": "Tue", "completed": true },
      { "day": "Wed", "completed": true },
      { "day": "Thu", "completed": true },
      { "day": "Fri", "completed": true },
      { "day": "Sat", "completed": false },
      { "day": "Sun", "completed": false }
    ]
  }
}

GET /dashboard/activities
Purpose: Get recent activities
Headers: Authorization: Bearer {token}
Query Parameters:
limit (optional): default 10
Response (200):
json
{
  "success": true,
  "data": {
    "activities": [
      {
        "id": "act_001",
        "type": "plan_completed",
        "description": "Completed study session for Data Structures",
        "timestamp": "2024-12-23T14:30:00Z",
        "metadata": {
          "course": "CS201",
          "duration": 120
        }
      }
    ]
  }
}

4.3 Course Management Endpoints
GET /courses
Purpose: Get all user courses
Headers: Authorization: Bearer {token}
Query Parameters:
semester (optional): filter by semester
active (optional): true/false
Response (200):
json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": "crs_001",
        "name": "Data Structures & Algorithms",
        "code": "CS201",
        "credits": 4,
        "instructor": "Dr. Sarah Johnson",
        "color": "orange",
        "semester": "Fall 2024",
        "is_active": true
      }
    ]
  }
}

POST /courses
Purpose: Create new course
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "name": "Data Structures & Algorithms",
  "code": "CS201",
  "credits": 4,
  "instructor": "Dr. Sarah Johnson",
  "color": "orange",
  "semester": "Fall 2024",
  "description": "Advanced data structures course"
}
Response (201):
json
{
  "success": true,
  "message": "Course created successfully",
  "data": {
    "course": {
      "id": "crs_001",
      "name": "Data Structures & Algorithms",
      "code": "CS201",
      "credits": 4,
      "instructor": "Dr. Sarah Johnson",
      "color": "orange",
      "semester": "Fall 2024",
      "is_active": true,
      "created_at": "2024-12-23T10:00:00Z"
    }
  }
}
Validation Rules:
name: required, string, max 255
code: required, string, max 50
credits: required, integer, min 1, max 6
semester: required, string

GET /courses/{id}
Purpose: Get single course details
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "data": {
    "course": {
      "id": "crs_001",
      "name": "Data Structures & Algorithms",
      "code": "CS201",
      "credits": 4,
      "instructor": "Dr. Sarah Johnson",
      "color": "orange",
      "semester": "Fall 2024",
      "statistics": {
        "total_study_hours": 45,
        "average_grade": 85,
        "results_count": 3
      }
    }
  }
}

PUT /courses/{id}
Purpose: Update course
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "name": "Advanced Data Structures",
  "credits": 4,
  "instructor": "Dr. Sarah Johnson"
}
Response (200):
json
{
  "success": true,
  "message": "Course updated successfully",
  "data": {
    "course": {...}
  }
}

DELETE /courses/{id}
Purpose: Delete course (soft delete)
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Course deleted successfully"
}

4.4 Academic Results Endpoints
GET /performance/results
Purpose: Get all academic results
**Headers
Continue
12:07 PM
:** Authorization: Bearer {token}
Query Parameters:
course_id (optional): filter by course
semester (optional): filter by semester
from_date (optional): YYYY-MM-DD
to_date (optional): YYYY-MM-DD
assessment_type (optional): quiz, midterm, final, assignment
Response (200):
json
{
  "success": true,
  "data": {
    "results": [
      {
        "id": "res_001",
        "course": {
          "id": "crs_001",
          "name": "Data Structures & Algorithms",
          "code": "CS201"
        },
        "assessment_type": "midterm",
        "assessment_name": "Midterm Exam",
        "score": 85,
        "max_score": 100,
        "percentage": 85.00,
        "grade": "B+",
        "weight": 30.00,
        "semester": "Fall 2024",
        "date": "2024-10-15",
        "created_at": "2024-10-15T14:00:00Z"
      }
    ],
    "pagination": {
      "total": 15,
      "per_page": 10,
      "current_page": 1,
      "last_page": 2
    }
  }
}

POST /performance/results
Purpose: Add new academic result
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "course_id": "crs_001",
  "assessment_type": "midterm",
  "assessment_name": "Midterm Exam",
  "score": 85,
  "max_score": 100,
  "grade": "B+",
  "weight": 30.00,
  "semester": "Fall 2024",
  "date": "2024-10-15",
  "notes": "Good performance overall"
}
Response (201):
json
{
  "success": true,
  "message": "Result added successfully",
  "data": {
    "result": {...}
  }
}
Validation Rules:
course_id: required, exists in courses
assessment_type: required, in: quiz, midterm, final, assignment, project
score: required, numeric, min 0
max_score: required, numeric, min 0, gte score
date: required, date

POST /performance/results/bulk
Purpose: Bulk upload results (CSV)
Headers: Authorization: Bearer {token}, Content-Type: multipart/form-data
Request Body (Form Data):
file: CSV file
Response (201):
json
{
  "success": true,
  "message": "Results uploaded successfully",
  "data": {
    "total_uploaded": 25,
    "successful": 24,
    "failed": 1,
    "errors": [
      {
        "row": 15,
        "error": "Invalid course code"
      }
    ]
  }
}

GET /performance/gpa-trend
Purpose: Get GPA trend over time
Headers: Authorization: Bearer {token}
Query Parameters:
period (optional): semester, year, all (default: all)
Response (200):
json
{
  "success": true,
  "data": {
    "trend": [
      {
        "period": "Fall 2023",
        "gpa": 3.2,
        "credits": 15
      },
      {
        "period": "Spring 2024",
        "gpa": 3.5,
        "credits": 16
      },
      {
        "period": "Fall 2024",
        "gpa": 3.65,
        "credits": 15
      }
    ],
    "current_gpa": 3.65,
    "cumulative_gpa": 3.45,
    "trend_direction": "improving"
  }
}

GET /performance/subjects
Purpose: Get subject-wise performance
Headers: Authorization: Bearer {token}
Query Parameters:
semester (optional)
Response (200):
json
{
  "success": true,
  "data": {
    "subjects": [
      {
        "course_id": "crs_001",
        "course_name": "Data Structures & Algorithms",
        "average_score": 85.5,
        "total_assessments": 4,
        "trend": "stable"
      }
    ]
  }
}

PUT /performance/results/{id}
Purpose: Update result
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "score": 90,
  "grade": "A-",
  "notes": "Updated after regrade"
}
Response (200):
json
{
  "success": true,
  "message": "Result updated successfully",
  "data": {
    "result": {...}
  }
}

DELETE /performance/results/{id}
Purpose: Delete result
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Result deleted successfully"
}

4.5 Study Planning Endpoints
GET /planning/plans
Purpose: Get study plans
Headers: Authorization: Bearer {token}
Query Parameters:
date (optional): YYYY-MM-DD (default: today)
from_date (optional): YYYY-MM-DD
to_date (optional): YYYY-MM-DD
status (optional): pending, completed, missed
course_id (optional)
Response (200):
json
{
  "success": true,
  "data": {
    "plans": [
      {
        "id": "pln_001",
        "course": {
          "id": "crs_001",
          "name": "Data Structures & Algorithms",
          "color": "orange"
        },
        "topic": "Binary Search Trees",
        "date": "2024-12-23",
        "start_time": "09:00:00",
        "planned_duration": 120,
        "actual_duration": null,
        "priority": "high",
        "study_type": "new-material",
        "status": "pending",
        "completed_at": null,
        "notes": ""
      }
    ],
    "summary": {
      "total_plans": 3,
      "pending": 2,
      "completed": 1,
      "total_planned_minutes": 270
    }
  }
}

POST /planning/plans
Purpose: Create study plan
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "course_id": "crs_001",
  "topic": "Binary Search Trees",
  "description": "Study BST operations and implementations",
  "date": "2024-12-23",
  "start_time": "09:00",
  "planned_duration": 120,
  "priority": "high",
  "study_type": "new-material"
}
Response (201):
json
{
  "success": true,
  "message": "Study plan created successfully",
  "data": {
    "plan": {...},
    "conflicts": []
  }
}
Validation Rules:
course_id: required, exists in courses
topic: required, string, max 255
date: required, date, after_or_equal today
start_time: required, time
planned_duration: required, integer, min 15, max 480
priority: required, in: high, medium, low
study_type: required, in: review, new-material, practice

PUT /planning/plans/{id}
Purpose: Update study plan
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "topic": "Binary Search Trees Advanced",
  "planned_duration": 150,
  "priority": "high"
}
Response (200):
json
{
  "success": true,
  "message": "Plan updated successfully",
  "data": {
    "plan": {...}
  }
}

PATCH /planning/plans/{id}/complete
Purpose: Mark plan as completed
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "actual_duration": 115,
  "notes": "Completed all BST operations"
}
Response (200):
json
{
  "success": true,
  "message": "Plan marked as completed",
  "data": {
    "plan": {
      "id": "pln_001",
      "status": "completed",
      "completed_at": "2024-12-23T11:00:00Z",
      "actual_duration": 115
    }
  }
}

DELETE /planning/plans/{id}
Purpose: Delete study plan
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Plan deleted successfully"
}

POST /planning/plans/check-conflicts
Purpose: Check for timetable conflicts
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "date": "2024-12-23",
  "start_time": "10:00",
  "duration": 120
}
Response (200):
json
{
  "success": true,
  "data": {
    "has_conflicts": true,
    "conflicts": [
      {
        "type": "class",
        "course_name": "Data Structures & Algorithms",
        "start_time": "10:00:00",
        "end_time": "11:30:00",
        "location": "Room 301"
      }
    ]
  }
}

4.6 Timetable Endpoints
GET /timetable
Purpose: Get user timetable
Headers: Authorization: Bearer {token}
Query Parameters:
semester (optional): get specific semester timetable
Response (200):
json
{
  "success": true,
  "data": {
    "timetable": {
      "id": "ttb_001",
      "semester": "Fall 2024",
      "is_active": true,
      "classes": [
        {
          "id": "cls_001",
          "course": {
            "id": "crs_001",
            "name": "Data Structures & Algorithms",
            "code": "CS201",
            "color": "orange"
          },
          "day_of_week": 1,
          "day_name": "Monday",
          "start_time": "10:00:00",
          "end_time": "11:30:00",
          "location": "Room 301",
          "class_type": "lecture",
          "instructor": "Dr. Sarah Johnson"
        }
      ]
    }
  }
}

POST /timetable
Purpose: Create or update timetable
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "semester": "Fall 2024",
  "academic_year": "2024-2025",
  "classes": [
    {
      "course_id": "crs_001",
      "day_of_week": 1,
      "start_time": "10:00",
      "end_time": "11:30",
      "location": "Room 301",
      "class_type": "lecture",
      "instructor": "Dr. Sarah Johnson"
    }
  ]
}
Response (201):
json
{
  "success": true,
  "message": "Timetable created successfully",
  "data": {
    "timetable": {...}
  }
}

POST /timetable/upload
Purpose: Upload timetable (image/PDF with OCR)
Headers: Authorization: Bearer {token}, Content-Type: multipart/form-data
Request Body (Form Data):
file: Image or PDF file
semester: "Fall 2024"
Response (201):
json
{
  "success": true,
  "message": "Timetable uploaded and processed",
  "data": {
    "timetable": {...},
    "parsed_classes": [
      {
        "course_name": "Data Structures",
        "day": "Monday",
        "time": "10:00 - 11:30",
        "location": "Room 301",
        "confidence": 0.95
      }
    ],
    "requires_manual_review": false
  }
}

PUT /timetable/classes/{id}
Purpose: Update timetable class
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "location": "Room 305",
  "instructor": "Prof. New Instructor"
}
Response (200):
json
{
  "success": true,
  "message": "Class updated successfully",
  "data": {
    "class": {...}
  }
}

DELETE /timetable/classes/{id}
Purpose: Delete timetable class
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Class deleted successfully"
}

4.7 Assignment Endpoints
GET /assignments
Purpose: Get all assignments
Headers: Authorization: Bearer {token}
Query Parameters:
status (optional): pending, completed, overdue
course_id (optional)
upcoming (optional): true (only upcoming assignments)
Response (200):
json
{
  "success": true,
  "data": {
    "assignments": [
      {
        "id": "asn_001",
        "course": {
          "id": "crs_001",
          "name": "Data Structures & Algorithms",
          "color": "orange"
        },
        "title": "Advanced problem solving (sorting)",
        "description": "Implement merge sort and quick sort",
        "due_date": "2024-12-28T23:59:00Z",
        "priority": "high",
        "status": "pending",
        "days_until_due": 5,
        "completed_at": null
      }
    ]
  }
}

POST /assignments
Purpose: Create assignment
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "course_id": "crs_001",
  "title": "Advanced problem solving (sorting)",
  "description": "Implement merge sort and quick sort algorithms",
  "due_date": "2024-12-28 23:59:00",
  "priority": "high"
}
Response (201):
json
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "assignment": {...}
  }
}
Validation Rules:
course_id: required, exists in courses
title: required, string, max 255
due_date: required, datetime, after now
priority: required, in: high, medium, low

PATCH /assignments/{id}/complete
Purpose: Mark assignment as completed
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Assignment marked as completed",
  "data": {
    "assignment": {
      "id": "asn_001",
      "status": "completed",
      "completed_at": "2024-12-23T15:30:00Z"
    }
  }
}

PUT /assignments/{id}
Purpose: Update assignment
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "title": "Updated Assignment Title",
  "due_date": "2024-12-30 23:59:00",
  "priority": "medium"
}
Response (200):
json
{
  "success": true,
  "message": "Assignment updated successfully",
  "data": {
    "assignment": {...}
  }
}

DELETE /assignments/{id}
Purpose: Delete assignment
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Assignment deleted successfully"
}

4.8 Progress & Reports Endpoints
GET /progress/weekly
Purpose: Get weekly progress report
Headers: Authorization: Bearer {token}
Query Parameters:
week_start (optional): YYYY-MM-DD (default: current week)
Response (200):
json
{
  "success": true,
  "data": {
    "report": {
      "id": "rpt_001",
      "week_start_date": "2024-12-16",
      "week_end_date": "2024-12-22",
      "total_study_hours": 24.5,
      "planned_hours": 28.0,
      "completion_rate": 87.5,
      "most_studied_course": {
        "id": "crs_001",
        "name": "Data Structures & Algorithms",
        "hours": 10.5
      },
      "least_studied_course": {
        "id": "crs_003",
        "name": "Computer Networks",
        "hours": 3.0
      },
      "performance_trend": "improving",
      "daily_breakdown": [
        {
          "date": "2024-12-16",
          "planned": 4,
          "actual": 3.5,
          "completion_rate": 87.5
        }
      ],
      "ai_insights": "Great progress this week! Focus more on Computer Networks.",
      "generated_at": "2024-12-22T20:00:00Z"
    }
  }
}

POST /progress/weekly/generate
Purpose: Generate weekly report manually
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "week_start": "2024-12-16"
}
Response (201):
json
{
  "success": true,
  "message": "Weekly report generated successfully",
  "data": {
    "report": {...}
  }
}

GET /progress/analytics
Purpose: Get advanced analytics
Headers: Authorization: Bearer {token}
Query Parameters:
from_date (optional): YYYY-MM-DD
to_date (optional): YYYY-MM-DD
group_by (optional): day, week, month
Response (200):
json
{
  "success": true,
  "data": {
    "analytics": {
      "study_consistency": {
        "days_studied": 45,
        "total_days": 60,
        "consistency_rate": 75.0
      },
      "time_distribution": [
        {
          "course_id": "crs_001",
          "course_name": "Data Structures & Algorithms",
          "hours": 45.5,
          "percentage": 35.0
        }
      ],
      "productivity_trends": [
        {
          "week": "2024-12-16",
          "average_session_duration": 95,
          "completion_rate": 87.5
        }
      ],
      "weak_areas": [
        {
          "course_id": "crs_003",
          "course_name": "Computer Networks",
          "average_score": 72.5,
          "study_hours": 15.0,
          "recommendation": "Increase study time"
        }
      ]
    }
  }
}

4.9 Notification Endpoints
GET /notifications
Purpose: Get user notifications
Headers: Authorization: Bearer {token}
Query Parameters:
unread (optional): true/false
type (optional): reminder, assignment, achievement, system, report
limit (optional): default 20
Response (200):
json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": "ntf_001",
        "type": "reminder",
        "title": "Study Plan Reminder",
        "message": "Don't forget to set your study plan for tomorrow!",
        "action_url": "/planning",
        "is_read": false,
        "created_at": "2024-12-23T21:00:00Z"
      }
    ],
    "unread_count": 3
  }
}

PATCH /notifications/{id}/read
Purpose: Mark notification as read
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Notification marked as read",
  "data": {
    "notification": {
      "id": "ntf_001",
      "is_read": true,
      "read_at": "2024-12-23T22:00:00Z"
    }
  }
}

POST /notifications/mark-all-read
Purpose: Mark all notifications as read
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "All notifications marked as read",
  "data": {
    "updated_count": 5
  }
}

4.10 Settings Endpoints
GET /settings/profile
Purpose: Get user profile
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "data": {
    "profile": {
      "id": "usr_001",
      "name": "John Doe",
      "email": "john.doe@university.edu",
      "avatar_url": "https://...",
      "university": "University of Technology",
      "semester": "3rd Semester",
      "created_at": "2024-09-01T00:00:00Z"
    }
  }
}

PUT /settings/profile
Purpose: Update user profile
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "name": "John Michael Doe",
  "university": "University of Technology",
  "semester": "4th Semester"
}
Response (200):
json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "profile": {...}
  }
}

POST /settings/avatar
Purpose: Upload profile avatar
Headers: Authorization: Bearer {token}, Content-Type: multipart/form-data
Request Body (Form Data):
avatar: Image file
Response (200):
json
{
  "success": true,
  "message": "Avatar uploaded successfully",
  "data": {
    "avatar_url": "https://..."
  }
}

GET /settings/preferences
Purpose: Get user preferences
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "data": {
    "preferences": {
      "morning_email_time": "07:00:00",
      "reminder_time": "21:00:00",
      "email_notifications": true,
      "push_notifications": true,
      "weekly_report_enabled": true,
      "weekly_report_day": "Sunday",
      "timezone": "America/New_York"
    }
  }
}

PUT /settings/preferences
Purpose: Update user preferences
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "morning_email_time": "08:00",
  "reminder_time": "20:00",
  "email_notifications": true,
  "weekly_report_day": "Monday"
}
Response (200):
json
{
  "success": true,
  "message": "Preferences updated successfully",
  "data": {
    "preferences": {...}
  }
}

POST /settings/change-password
Purpose: Change password
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "current_password": "OldPassword123!",
  "new_password": "NewPassword123!",
  "new_password_confirmation": "NewPassword123!"
}
Response (200):
json
{
  "success": true,
  "message": "Password changed successfully"
}

POST /settings/export-data
Purpose: Request data export
Headers: Authorization: Bearer {token}
Response (200):
json
{
  "success": true,
  "message": "Data export request received. You'll receive an email with download link.",
  "data": {
    "estimated_time": "5-10 minutes"
  }
}

DELETE /settings/account
Purpose: Delete user account
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "password": "CurrentPassword123!",
  "confirmation": "DELETE"
}
Response (200):
json
{
  "success": true,
  "message": "Account deletion scheduled. Your data will be permanently deleted in 30 days."
}

4.11 AI Chat Endpoints (Phase 2)
POST /ai-coach/chat
Purpose: Send message to AI coach
Headers: Authorization: Bearer {token}
Request Body:
json
{
  "session_id": "session_001",
  "message": "How can I improve my grades in Computer Networks?"
}
Response (200):
json
{
  "success": true,
  "data": {
    "response": "Based on your current performance in Computer Networks (average: 72.5%), I recommend...",
    "session_id": "session_001",
    "context_used": {
      "course_performance": true,
      "study_patterns": true
    }
  }
}

GET /ai-coach/history
Purpose: Get chat history
Headers: Authorization: Bearer {token}
Query Parameters:
session_id (optional): get specific session
Response (200):
json
{
  "success": true,
  "data": {
    "history": [
      {
        "id": "msg_001",
        "session_id": "session_001",
        "role": "user",
        "message": "How can I improve?",
        "created_at": "2024-12-23T10:00:00Z"
      },
      {
        "id": "msg_002",
        "session_id": "session_001",
        "role": "assistant",
        "message": "Based on your data...",
        "created_at": "2024-12-23T10:00:05Z"
      }
    ]
  }
}
```

---

## 5. Background Jobs & Scheduled Tasks

### 5.1 Queue Jobs

#### **SendMorningEmailJob**
**Purpose:** Send daily study plan emails

**Schedule:** Daily at user's configured time (default: 7:00 AM user timezone)

**Process:**
1. Query users with `email_notifications = true`
2. Get study plans for current day
3. Generate personalized email with:
   - Greeting
   - Daily study plan list
   - Total planned hours
   - Motivational quote
4. Send via email service
5. Log to `email_logs` table

**Retry Logic:** 3 attempts with exponential backoff

---

#### **SendReminderJob**
**Purpose:** Send reminder if no plan set

**Schedule:** Daily at user's configured reminder time (default: 9:00 PM user timezone)

**Process:**
1. Query users with `push_notifications = true` or `email_notifications = true`
2. Check if user has created plan for next day
3. If no plan exists, send reminder via:
   - Push notification (if enabled)
   - Email (if enabled)
4. Create notification record
5. Log reminder sent

---

#### **GenerateWeeklyReportJob**
**Purpose:** Generate and send weekly progress reports

**Schedule:** Weekly on user's configured day (default: Sunday at 8:00 PM)

**Process:**
1. Calculate week date range (Monday-Sunday)
2. Aggregate study data:
   - Total study hours
   - Planned vs actual hours
   - Completion rate
   - Course breakdown
3. Calculate performance metrics
4. Generate AI insights (Phase 2)
5. Store report in `weekly_reports` table
6. Send email with PDF attachment
7. Create notification

---

#### **UpdateAssignmentStatusJob**
**Purpose:** Update assignment statuses to "overdue"

**Schedule:** Every hour

**Process:**
1. Query assignments with `status = 'pending'` and `due_date < now()`
2. Update status to 'overdue'
3. Send overdue notification to user

---

#### **ProcessTimetableUploadJob**
**Purpose:** Process uploaded timetable files with OCR

**Trigger:** Async after file upload

**Process:**
1. Retrieve uploaded file from storage
2. Send to OCR service (Google Cloud Vision API)
3. Parse extracted text for:
   - Course names
   - Days and times
   - Locations
4. Match course names to user's courses
5. Create timetable classes
6. Flag low-confidence results for manual review
7. Notify user of completion

---

#### **CleanupOldDataJob**
**Purpose:** Clean up old notifications and logs

**Schedule:** Daily at 2:00 AM

**Process:**
1. Delete notifications older than 90 days
2. Delete email logs older than 180 days
3. Delete activity logs older than 365 days
4. Archive old weekly reports

---

### 5.2 Cron Schedule Summary
```
# Daily morning emails (user-specific times)
0 * * * * php artisan schedule:run --command="app:send-morning-emails"

# Daily reminders (user-specific times)
0 * * * * php artisan schedule:run --command="app:send-reminders"

# Weekly reports (user-specific days/times)
0 * * * * php artisan schedule:run --command="app:generate-weekly-reports"

# Update assignment statuses
0 * * * * php artisan schedule:run --command="app:update-assignment-statuses"

# Cleanup old data
0 2 * * * php artisan schedule:run --command="app:cleanup-old-data"

# Database backup
0 3 * * * php artisan backup:run

6. External Service Integrations
6.1 Email Service (SendGrid / AWS SES)
Configuration:
env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@focustrack.app
MAIL_FROM_NAME="FocusTrack"
Email Templates:
Morning Plan Email
Study Reminder
Weekly Report
Welcome Email
Password Reset
Email Verification

6.2 File Storage (AWS S3 / Cloudflare R2)
Configuration:
env
FILESYSTEM_DISK=s3
Continue
12:09 PM
AWS_ACCESS_KEY_ID=your_access_key AWS_SECRET_ACCESS_KEY=your_secret_key AWS_DEFAULT_REGION=us-east-1 AWS_BUCKET=focustrack-uploads AWS_URL=https://your-bucket.s3.amazonaws.com

**Storage Buckets:**
- `avatars/` - User profile pictures
- `timetables/` - Uploaded timetable files
- `results/` - Bulk upload CSVs
- `reports/` - Generated PDF reports

---

### 6.3 OCR Service (Google Cloud Vision API)

**Configuration:**
```env
GOOGLE_CLOUD_PROJECT_ID=your_project_id
GOOGLE_CLOUD_KEY_FILE=path/to/service-account.json
```

**Usage:**
- Timetable image/PDF parsing
- Extract text from uploaded documents

---

### 6.4 AI Service (Google Gemini API) - Phase 2

**Configuration:**
```env
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-pro
```

**Usage:**
- AI Study Coach conversations
- Weekly report insights generation
- Weak area analysis

---

### 6.5 Cache (Redis)

**Configuration:**
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

**Caching Strategy:**
- Dashboard stats: 5 minutes TTL
- User preferences: 1 hour TTL
- Course list: 30 minutes TTL
- Timetable: 1 hour TTL

---

## 7. Security Requirements

### 7.1 Authentication & Authorization

**Token-Based Authentication:**
- Use Laravel Sanctum for SPA authentication
- JWT tokens for API authentication
- Token expiration: 24 hours (access), 30 days (refresh)
- Rotate refresh tokens on each use

**Password Security:**
- Minimum 8 characters
- Must contain: uppercase, lowercase, number
- Bcrypt hashing with cost factor 12
- Password history: prevent reuse of last 5 passwords

**Rate Limiting:**
- Login attempts: 5 per minute per IP
- API requests: 60 per minute per user
- Password reset: 3 per hour per email

---

### 7.2 Data Encryption

**At Rest:**
- Database encryption using PostgreSQL transparent encryption
- Sensitive fields (passwords) hashed with Bcrypt
- File encryption for uploaded documents

**In Transit:**
- HTTPS/TLS 1.3 for all API communications
- Secure WebSocket connections (WSS) for real-time features

---

### 7.3 Input Validation & Sanitization

- Validate all inputs server-side
- Sanitize HTML input to prevent XSS
- Use parameterized queries to prevent SQL injection
- CSRF protection for state-changing requests

---

### 7.4 API Security Headers
X-Frame-Options: DENY X-Content-Type-Options: nosniff X-XSS-Protection: 1; mode=block Strict-Transport-Security: max-age=31536000; includeSubDomains Content-Security-Policy: default-src 'self'

---

### 7.5 Audit Logging

**Log all:**
- Authentication attempts (success/failure)
- Password changes
- Data exports
- Account deletions
- Failed authorization attempts
- Bulk data operations

---

## 8. Performance Requirements

### 8.1 Response Time SLA

- API endpoints (95th percentile): < 200ms
- Database queries: < 50ms
- Dashboard load: < 2 seconds
- Charts generation: < 1 second

### 8.2 Optimization Strategies

**Database:**
- Proper indexing on frequently queried fields
- Query optimization with EXPLAIN ANALYZE
- Connection pooling
- Read replicas for heavy read operations

**Caching:**
- Redis for session management
- Cache dashboard statistics
- Cache user preferences
- Cache query results

**API:**
- Response compression (gzip)
- Pagination for list endpoints (default: 20 items)
- Eager loading to prevent N+1 queries
- API response caching with ETags

---

## 9. Error Handling & Logging

### 9.1 Error Response Format
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "error_code": "VALIDATION_ERROR",
  "timestamp": "2024-12-23T10:00:00Z"
}
```

### 9.2 HTTP Status Codes

- `200 OK` - Successful GET, PUT, PATCH
- `201 Created` - Successful POST
- `204 No Content` - Successful DELETE
- `400 Bad Request` - Invalid input
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

### 9.3 Logging Strategy

**Log Levels:**
- **DEBUG**: Development debugging information
- **INFO**: General application events
- **WARNING**: Deprecated features, non-critical issues
- **ERROR**: Runtime errors
- **CRITICAL**: System failures

**Log to:**
- Application logs: `storage/logs/laravel.log`
- Query logs: `storage/logs/query.log`
- Email logs: `email_logs` table
- Activity logs: `activity_logs` table

**External Monitoring:**
- Sentry for error tracking
- New Relic / DataDog for APM
- CloudWatch for AWS infrastructure

---

## 10. Testing Requirements

### 10.1 Unit Tests

**Coverage Target:** 80%+

**Test:**
- Service classes
- Helper functions
- Validation rules
- Calculation logic (GPA, statistics)

### 10.2 Integration Tests

**Test:**
- API endpoints
- Database transactions
- Queue jobs
- Email sending

### 10.3 End-to-End Tests

**Critical User Flows:**
1. User registration → onboarding → dashboard
2. Create study plan → receive morning email
3. Upload results → view performance charts
4. Generate weekly report

---

## 11. Deployment & DevOps

### 11.1 Environment Configuration

**Development:**
```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
```

**Staging:**
```env
APP_ENV=staging
APP_DEBUG=false
LOG_LEVEL=info
```

**Production:**
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
```

### 11.2 CI/CD Pipeline

**GitHub Actions Workflow:**
1. Run tests
2. Code quality checks (PHPStan, Laravel Pint)
3. Build Docker image
4. Deploy to staging
5. Run smoke tests
6. Deploy to production (manual approval)

### 11.3 Database Migrations

- Version controlled migrations
- Rollback capability
- Seed data for development
- Production data backup before migration

### 11.4 Zero-Downtime Deployment

- Blue-green deployment strategy
- Database migrations run before deployment
- Health checks before traffic routing
- Automated rollback on failure

---

## 12. Monitoring & Alerting

### 12.1 Health Checks

**Endpoints:**
- `GET /health` - Basic health check
- `GET /health/database` - Database connectivity
- `GET /health/cache` - Redis connectivity
- `GET /health/storage` - File storage accessibility

### 12.2 Metrics to Monitor

- API response times
- Error rates
- Queue processing times
- Email delivery rates
- Database connection pool
- Memory usage
- CPU usage

### 12.3 Alerts

**Critical Alerts (Immediate):**
- API downtime > 1 minute
- Error rate > 5%
- Database connection failures
- Queue backlog > 1000 jobs

**Warning Alerts (15 minutes):**
- API response time > 500ms
- Email delivery rate < 95%
- Disk space > 80%

---

## 13. Documentation Requirements

### 13.1 API Documentation

- OpenAPI (Swagger) specification
- Postman collection
- Code examples in multiple languages
- Authentication guide

### 13.2 Developer Documentation

- Setup instructions
- Architecture overview
- Database schema documentation
- Deployment guide
- Contributing guidelines

### 13.3 Operations Documentation

- Runbook for common issues
- Backup and restore procedures
- Scaling guide
- Monitoring setup

---

## 14. Compliance & Privacy

### 14.1 Data Privacy

- GDPR compliance
- FERPA awareness (educational records)
- User data export capability
- Right to be forgotten (account deletion)
- Privacy policy enforcement

### 14.2 Data Retention

- Active user data: Indefinite
- Deleted accounts: 30-day grace period
- Email logs: 180 days
- Activity logs: 365 days
- Backups: 90 days

---

## 15. Success Criteria for MVP

### 15.1 Functional Completeness

- ✅ All authentication endpoints working
- ✅ CRUD operations for all entities
- ✅ Morning email system operational
- ✅ Reminder system functional
- ✅ Weekly report generation working
- ✅ Dashboard statistics accurate
- ✅ File upload and storage working

### 15.2 Performance Metrics

- ✅ 99% API uptime
- ✅ < 200ms average response time
- ✅ > 98% email delivery rate
- ✅ Zero critical security vulnerabilities

### 15.3 Quality Metrics

- ✅ 80%+ test coverage
- ✅ All critical user flows tested
- ✅ Documentation complete
- ✅ Security audit passed

---

## Appendix A: Database ERD
users ──┐ │ ├──< user_preferences ├──< courses ──┐ ├──< academic_results ──┤ ├──< study_plans ───────┤ ├──< timetables ──┐ │ ├──< assignments ─┤ │ ├──< notifications│ │ ├──< weekly_reports │ └──< file_uploads │ │ timetable_classes ──────┤ │ study_sessions ─────────┘

---

## Appendix B: API Request Flow
Client Request │ ▼ API Gateway (Laravel) │ ├──> Authentication Middleware │ │ │ ▼ ├──> Rate Limiting Middleware │ │ │ ▼ ├──> Validation │ │ │ ▼ ├──> Controller │ │ │ ▼ ├──> Service Layer │ │ │ ▼ ├──> Repository/Model │ │ │ ▼ └──> Database │ ▼ Response Format │ ▼ JSON Response

---

**Document Version:** 1.0  
**Last Updated:** December 23, 2024  
**Document Owner:** Backend Team Lead  
**Approved By:** Technical Architect, Product Manager

---

**This comprehensive backend PRD provides everything needed to build a robust, scalable, and secure API for the FocusTrack study tracker application.**





