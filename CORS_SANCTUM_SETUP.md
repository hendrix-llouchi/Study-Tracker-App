# CORS and Sanctum Configuration - Complete Setup

This document contains the complete CORS and Sanctum configuration that has been applied to fix the "blocked by CORS policy" error.

## ✅ Changes Applied

### 1. Backend Configuration

#### CORS Configuration (`config/cors.php`)
- ✅ Deleted and recreated with fresh configuration
- ✅ Paths: `['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register']`
- ✅ Allowed origins: `['http://localhost:5173', 'http://127.0.0.1:5173']` (no trailing slashes)
- ✅ `supports_credentials` set to `true` (mandatory for cookie-based auth)

#### Bootstrap Configuration (`bootstrap/app.php`)
- ✅ API prefix set to `api/v1`
- ✅ `statefulApi()` middleware enabled for cookie-based authentication

### 2. Frontend Configuration

#### API Service (`frontend/src/services/api.js`)
- ✅ `axios.defaults.withCredentials = true` set globally
- ✅ `baseURL` set to `http://127.0.0.1:8000/api/v1`

## 📝 Required .env Configuration

Add or update the following in your `backend/study_tracker_app/.env` file:

```env
# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173

# Session Configuration
SESSION_DOMAIN=localhost
```

**Important Notes:**
- No port numbers in `SANCTUM_STATEFUL_DOMAINS` for localhost/127.0.0.1 (just the domain)
- `SESSION_DOMAIN` should be `localhost` (not `127.0.0.1`)
- Ensure `SESSION_DRIVER=cookie` is set (default in Laravel)

## 🧹 Cache Clear Command

Run this single command to clear all Laravel caches and force the new settings to load:

```bash
cd backend/study_tracker_app && php artisan config:clear && php artisan route:clear && php artisan cache:clear
```

Or as a one-liner for Windows PowerShell:
```powershell
cd backend\study_tracker_app; php artisan config:clear; php artisan route:clear; php artisan cache:clear
```

## 🔄 Next Steps

1. Update your `.env` file with the Sanctum and Session domain settings
2. Run the cache clear command
3. Restart your Laravel server: `php artisan serve`
4. Test the connection from your Vue frontend

## 🧪 Testing

After applying these changes:
1. Clear browser cookies for localhost:5173
2. Restart both frontend and backend servers
3. Try logging in from the frontend
4. Check browser DevTools Network tab to verify:
   - `Access-Control-Allow-Credentials: true` header is present
   - No CORS errors in console
   - Cookies are being sent/received properly

