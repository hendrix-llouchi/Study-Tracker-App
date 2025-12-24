# Troubleshooting Network Errors

## Common Issues and Solutions

### 1. Network Error: Cannot Connect to Server

**Symptoms:** "Network error: Unable to connect to the server"

**Solutions:**
- ✅ Make sure the backend server is running:
  ```bash
  cd backend/study_tracker_app
  php artisan serve
  ```
  You should see: "Laravel development server started: http://127.0.0.1:8000"

- ✅ Check if port 8000 is available (not used by another application)

- ✅ Verify the API URL in frontend `.env`:
  ```
  VITE_API_URL=http://localhost:8000/api/v1
  ```

- ✅ After changing `.env`, restart the frontend dev server:
  ```bash
  # Stop the server (Ctrl+C) and restart
  npm run dev
  ```

### 2. CORS Errors

**Symptoms:** "CORS policy: No 'Access-Control-Allow-Origin' header"

**Solutions:**
- ✅ Check `SANCTUM_STATEFUL_DOMAINS` in backend `.env`:
  ```
  SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:8000
  ```

- ✅ Verify CORS middleware is registered in `bootstrap/app.php`

- ✅ Clear Laravel config cache:
  ```bash
  php artisan config:clear
  php artisan cache:clear
  ```

### 3. 401 Unauthorized Errors

**Symptoms:** "Unauthenticated" or 401 status

**Solutions:**
- ✅ Check if token is being sent in requests (check browser DevTools Network tab)
- ✅ Verify Sanctum is properly configured
- ✅ Make sure `withCredentials: true` is set in axios config

### 4. 422 Validation Errors

**Symptoms:** "Validation failed" with field errors

**Solutions:**
- ✅ Check the error response for specific field validation messages
- ✅ Ensure all required fields are provided:
  - `name`: required, string, max 255
  - `email`: required, valid email, unique
  - `password`: required, min 8 characters, confirmed
  - `password_confirmation`: must match password

### 5. 500 Server Errors

**Symptoms:** "Internal Server Error" or 500 status

**Solutions:**
- ✅ Check Laravel logs: `backend/study_tracker_app/storage/logs/laravel.log`
- ✅ Verify database connection in `.env`
- ✅ Make sure migrations have run: `php artisan migrate`
- ✅ Check PHP error logs

### 6. Frontend Not Loading

**Symptoms:** Frontend shows blank page or errors

**Solutions:**
- ✅ Check browser console for JavaScript errors
- ✅ Verify `.env` file exists in frontend directory
- ✅ Make sure all dependencies are installed: `npm install`
- ✅ Check if Vite dev server is running on port 5173

## Debugging Steps

1. **Check Backend is Running:**
   ```bash
   curl http://localhost:8000/api/v1/auth/register
   # Should return a validation error (expected for GET request)
   ```

2. **Check Frontend API URL:**
   - Open browser console
   - Check Network tab when making a request
   - Verify the request URL is `http://localhost:8000/api/v1/auth/register`

3. **Check Browser Console:**
   - Open DevTools (F12)
   - Check Console tab for errors
   - Check Network tab for failed requests
   - Look at the request/response details

4. **Check Laravel Logs:**
   ```bash
   tail -f backend/study_tracker_app/storage/logs/laravel.log
   ```

5. **Test API Directly:**
   ```bash
   curl -X POST http://localhost:8000/api/v1/auth/register \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
   ```

## Quick Fix Checklist

- [ ] Backend server running on port 8000
- [ ] Frontend server running on port 5173
- [ ] Frontend `.env` file exists with `VITE_API_URL`
- [ ] Backend `.env` has correct database configuration
- [ ] Migrations have been run
- [ ] No firewall blocking ports
- [ ] Browser console shows no CORS errors
- [ ] Network tab shows the API request is being made

