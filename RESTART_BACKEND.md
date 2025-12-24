# Restart Backend Server

If you're still seeing CORS errors, you may need to restart the backend server:

1. **Stop the current server:**
   - Find the terminal where `php artisan serve` is running
   - Press `Ctrl+C` to stop it

2. **Start it again:**
   ```bash
   cd backend/study_tracker_app
   php artisan serve
   ```

3. **Clear cache (if needed):**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

The CORS middleware has been updated to:
- Properly handle OPTIONS preflight requests by returning headers immediately
- Accept requests from `http://localhost:5173` and any IP address on port 5173
- Return the exact origin (not wildcard) when credentials are used
- Include all necessary CORS headers (Origin, Methods, Headers, Credentials, Max-Age)
- Remove any conflicting CORS headers from other middleware

