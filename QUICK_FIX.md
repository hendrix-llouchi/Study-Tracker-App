# Quick Fix for Network Error

## Immediate Steps

### 1. Verify Backend is Running

Open a new terminal and run:
```bash
cd backend/study_tracker_app
php artisan serve
```

You should see:
```
Laravel development server started: http://127.0.0.1:8000
```

### 2. Test Backend Directly

Open your browser and go to:
```
http://localhost:8000/health
```

You should see: `{"status":"ok",...}`

### 3. Check Frontend Environment

Make sure `frontend/.env` exists with:
```
VITE_API_URL=http://localhost:8000/api/v1
```

**Important:** After creating/updating `.env`, restart the frontend dev server!

### 4. Check Browser Console

1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for the message: `API URL configured as: http://localhost:8000/api/v1`
4. Go to Network tab
5. Try registering again
6. Look for the request to `/api/v1/auth/register`
7. Click on it to see the error details

### 5. Common Issues

**If you see "Network Error" in console:**
- Backend is not running → Start it with `php artisan serve`
- Wrong port → Check if backend is on port 8000
- Firewall blocking → Check Windows Firewall settings

**If you see CORS error:**
- Clear Laravel cache: `php artisan config:clear`
- Restart backend server
- Check `SANCTUM_STATEFUL_DOMAINS` in backend `.env`

**If you see 404 Not Found:**
- Check API route exists: `php artisan route:list | grep register`
- Verify route is: `POST /api/v1/auth/register`

**If you see 500 Internal Server Error:**
- Check Laravel logs: `backend/study_tracker_app/storage/logs/laravel.log`
- Verify database is connected
- Check migrations ran: `php artisan migrate:status`

## Test API Directly

You can test the registration endpoint directly using curl:

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}"
```

If this works, the backend is fine and the issue is in the frontend connection.

