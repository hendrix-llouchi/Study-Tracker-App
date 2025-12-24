# Backend-Frontend Connection Setup Guide

## Quick Start

### 1. Backend Setup

1. Navigate to backend directory:
```bash
cd backend/study_tracker_app
```

2. Make sure your `.env` file has these settings:
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:8000

SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
```

3. Start the backend server:
```bash
php artisan serve
```

The backend will run on `http://localhost:8000`

### 2. Frontend Setup

1. Navigate to frontend directory:
```bash
cd frontend
```

2. Create `.env` file:
```bash
# Create .env file with:
VITE_API_URL=http://localhost:8000/api/v1
```

3. Install dependencies (if not already done):
```bash
npm install
```

4. Start the frontend development server:
```bash
npm run dev
```

The frontend will run on `http://localhost:5173`

## How It Works

### Authentication Flow

1. **Login/Register**: Frontend sends credentials to `/api/v1/auth/login` or `/api/v1/auth/register`
2. **Token Storage**: Backend returns a Sanctum token, stored in `localStorage`
3. **API Requests**: All subsequent requests include `Authorization: Bearer {token}` header
4. **Auto-fetch User**: On app load, if token exists, frontend fetches user data from `/api/v1/auth/me`

### API Service

All API calls go through `frontend/src/services/api.js`:
- Automatically adds auth token to requests
- Handles 401 errors by redirecting to login
- Base URL configured via `VITE_API_URL` environment variable

### CORS Configuration

The backend is configured to:
- Accept requests from `http://localhost:5173`
- Allow credentials (cookies) for Sanctum SPA authentication
- Handle preflight OPTIONS requests

## Testing the Connection

1. Start both servers (backend on :8000, frontend on :5173)
2. Open browser to `http://localhost:5173`
3. Try to register a new user
4. Check browser console and network tab for API calls
5. Verify token is stored in localStorage

## Troubleshooting

### CORS Errors
- Make sure `SANCTUM_STATEFUL_DOMAINS` includes your frontend URL
- Check that `APP_URL` in backend `.env` matches your backend URL
- Verify frontend `.env` has correct `VITE_API_URL`

### 401 Unauthorized
- Check that token is being sent in Authorization header
- Verify Sanctum is properly configured
- Check that user exists and token is valid

### Connection Refused
- Ensure backend server is running on port 8000
- Check firewall settings
- Verify API URL in frontend `.env` is correct

