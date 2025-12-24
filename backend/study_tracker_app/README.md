# FocusTrack Backend API

Backend API for FocusTrack study tracker application built with Laravel 12.

## Features

- User authentication with Laravel Sanctum
- Course management
- Academic results tracking
- Study planning and scheduling
- Timetable management
- Assignment tracking
- Weekly progress reports
- Email notifications
- AI Coach (Phase 2)

## Requirements

- PHP 8.2+
- Composer
- PostgreSQL/MySQL
- Redis (for caching and queues)
- Node.js & NPM

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Copy environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure your `.env` file with database and Redis credentials

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

8. Start queue worker (in separate terminal):
   ```bash
   php artisan queue:work
   ```

9. Start scheduler (in separate terminal):
   ```bash
   php artisan schedule:work
   ```

## API Documentation

Base URL: `/api/v1`

### Authentication Endpoints

- `POST /api/v1/auth/register` - Register new user
- `POST /api/v1/auth/login` - Login user
- `POST /api/v1/auth/logout` - Logout user
- `POST /api/v1/auth/google` - Google OAuth
- `POST /api/v1/auth/forgot-password` - Request password reset
- `POST /api/v1/auth/reset-password` - Reset password
- `GET /api/v1/auth/me` - Get current user

### Dashboard Endpoints

- `GET /api/v1/dashboard/stats` - Get dashboard statistics
- `GET /api/v1/dashboard/upcoming-classes` - Get upcoming classes
- `GET /api/v1/dashboard/study-streak` - Get study streak
- `GET /api/v1/dashboard/activities` - Get recent activities

### Course Management

- `GET /api/v1/courses` - List courses
- `POST /api/v1/courses` - Create course
- `GET /api/v1/courses/{id}` - Get course details
- `PUT /api/v1/courses/{id}` - Update course
- `DELETE /api/v1/courses/{id}` - Delete course

### Performance Tracking

- `GET /api/v1/performance/results` - List academic results
- `POST /api/v1/performance/results` - Add result
- `GET /api/v1/performance/gpa-trend` - Get GPA trend
- `GET /api/v1/performance/subjects` - Get subject performance

### Study Planning

- `GET /api/v1/planning/plans` - List study plans
- `POST /api/v1/planning/plans` - Create study plan
- `PATCH /api/v1/planning/plans/{id}/complete` - Mark plan as completed

### Timetable

- `GET /api/v1/timetable` - Get timetable
- `POST /api/v1/timetable` - Create/update timetable
- `POST /api/v1/timetable/upload` - Upload timetable (OCR)

### Assignments

- `GET /api/v1/assignments` - List assignments
- `POST /api/v1/assignments` - Create assignment
- `PATCH /api/v1/assignments/{id}/complete` - Mark as completed

### Progress & Reports

- `GET /api/v1/progress/weekly` - Get weekly report
- `POST /api/v1/progress/weekly/generate` - Generate weekly report
- `GET /api/v1/progress/analytics` - Get analytics

### Notifications

- `GET /api/v1/notifications` - List notifications
- `PATCH /api/v1/notifications/{id}/read` - Mark as read
- `POST /api/v1/notifications/mark-all-read` - Mark all as read

### Settings

- `GET /api/v1/settings/profile` - Get profile
- `PUT /api/v1/settings/profile` - Update profile
- `GET /api/v1/settings/preferences` - Get preferences
- `PUT /api/v1/settings/preferences` - Update preferences

## Environment Variables

Key environment variables to configure:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=focustrack
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key

GOOGLE_CLOUD_PROJECT_ID=your_project_id
GOOGLE_CLOUD_KEY_FILE=path/to/service-account.json
```

## Scheduled Tasks

The following tasks run automatically:

- **Hourly**: Send morning emails, send reminders, update assignment statuses
- **Daily (2 AM)**: Cleanup old data
- **Weekly**: Generate weekly reports (based on user preferences)

## Testing

Run tests with:
```bash
php artisan test
```

## License

MIT
