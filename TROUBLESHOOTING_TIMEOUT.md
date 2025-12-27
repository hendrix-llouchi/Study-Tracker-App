# Troubleshooting: API Timeout Error

## Error: "timeout of 10000ms exceeded"

This error indicates that the frontend cannot connect to the Laravel backend server.

## Quick Fixes

### 1. Verify Backend Server is Running

**Check if Laravel is running:**
```bash
cd backend/study_tracker_app
php artisan serve
```

The server should start on `http://127.0.0.1:8000` or `http://localhost:8000`

### 2. Verify Port 8000 is Available

**Check if port 8000 is in use:**
```powershell
# Windows PowerShell
Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
```

If nothing is listening on port 8000, start the Laravel server.

### 3. Check API URL Configuration

**Frontend `.env` file should have:**
```env
VITE_API_URL=http://127.0.0.1:8000/api/v1
```

**Or use `http://localhost:8000/api/v1` if 127.0.0.1 doesn't work**

### 4. Test Backend Directly

**Test if the backend responds:**
```powershell
# Test health endpoint
Invoke-WebRequest -Uri "http://127.0.0.1:8000/up" -Method GET

# Test API endpoint
Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/auth/login" -Method OPTIONS
```

### 5. Check CORS Configuration

**Verify `config/cors.php` has:**
- `supports_credentials` set to `true`
- Your frontend origin in `allowed_origins` array
- `paths` includes `'api/*'`

### 6. Clear Laravel Caches

```bash
cd backend/study_tracker_app
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 7. Check Browser Console

Open browser DevTools (F12) and check:
- **Network tab**: Look for the failed request
- **Console tab**: Check for CORS errors or other issues
- **Request details**: Verify the request URL and headers

## Common Issues

### Issue: Backend Server Not Started
**Solution**: Start the Laravel development server
```bash
cd backend/study_tracker_app
php artisan serve
```

### Issue: Wrong Port
**Solution**: Verify the backend is running on port 8000, or update `VITE_API_URL` in frontend `.env`

### Issue: Firewall Blocking
**Solution**: Check Windows Firewall settings or temporarily disable to test

### Issue: CORS Preflight Failing
**Solution**: Ensure CORS middleware is properly configured and caches are cleared

## Browser Extension Errors (Can be Ignored)

The following errors are from browser extensions and can be safely ignored:
- `Unchecked runtime.lastError: A listener indicated an asynchronous response...`
- `Uncaught (in promise) Error: A listener indicated an asynchronous response...`

These are typically from:
- React DevTools
- Redux DevTools
- Vue DevTools
- Other browser extensions

They don't affect your application functionality.

## Still Having Issues?

1. **Check Laravel logs:**
   ```bash
   cd backend/study_tracker_app
   tail -f storage/logs/laravel.log
   ```

2. **Verify database connection:**
   ```bash
   php artisan migrate:status
   ```

3. **Test with curl/Postman:**
   ```bash
   curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -H "Origin: http://localhost:5173" \
     -d '{"email":"test@example.com","password":"password"}'
   ```

