# Google OAuth "OAuth client was not found" Error - Troubleshooting Guide

## Error Message
```
Access blocked: Authorization Error
The OAuth client was not found.
Error 401: invalid_client
```

## Root Causes

This error occurs when Google cannot find or validate your OAuth 2.0 Client ID. Common causes:

1. **Missing or incorrect Client ID** in frontend `.env` file
2. **Client ID doesn't exist** in Google Cloud Console
3. **Client ID is for a different project** or has been deleted
4. **Authorized JavaScript origins** not configured correctly
5. **Backend credentials missing** or incorrect

---

## Step-by-Step Fix

### Step 1: Verify Frontend Environment Variables

1. **Check if `.env` file exists** in `frontend/` directory
2. **Verify `VITE_GOOGLE_CLIENT_ID` is set**:
   ```env
   VITE_GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
   ```
3. **Restart the dev server** after changing `.env`:
   ```bash
   # Stop the server (Ctrl+C)
   npm run dev
   ```

**Important:** Vite requires a server restart to pick up new environment variables!

### Step 2: Verify Backend Environment Variables

1. **Check if `.env` file exists** in `backend/study_tracker_app/` directory
2. **Verify these variables are set**:
   ```env
   GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=your-client-secret
   GOOGLE_REDIRECT_URI=http://localhost:8000/api/v1/auth/google/callback
   ```
3. **Clear config cache** (if needed):
   ```bash
   php artisan config:clear
   ```

### Step 3: Verify Google Cloud Console Setup

#### A. Check if OAuth Client Exists

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project
3. Navigate to **APIs & Services** → **Credentials**
4. Look for **OAuth 2.0 Client IDs**
5. **Verify the Client ID matches** what's in your `.env` file

#### B. If Client ID Doesn't Exist - Create One

1. In **Credentials** page, click **+ CREATE CREDENTIALS** → **OAuth client ID**
2. If prompted, configure **OAuth consent screen** first:
   - User Type: **External** (for testing) or **Internal** (for Google Workspace)
   - App name: Your app name
   - User support email: Your email
   - Developer contact: Your email
   - Click **Save and Continue**
   - Scopes: Add `email` and `profile` (if not already added)
   - Click **Save and Continue**
   - Test users: Add your email (for External apps)
   - Click **Save and Continue**
   - Click **Back to Dashboard**

3. **Create OAuth Client ID**:
   - Application type: **Web application**
   - Name: `Study Tracker App` (or any name)
   - **Authorized JavaScript origins**:
     ```
     http://localhost:5173
     http://localhost:3000
     http://127.0.0.1:5173
     ```
   - **Authorized redirect URIs**:
     ```
     http://localhost:5173
     http://localhost:8000/api/v1/auth/google/callback
     ```
   - Click **Create**
   - **Copy the Client ID and Client Secret**

#### C. Verify Authorized Origins

1. Click on your OAuth 2.0 Client ID
2. **Verify Authorized JavaScript origins** includes:
   - `http://localhost:5173` (or your frontend URL)
   - `http://127.0.0.1:5173` (if using 127.0.0.1)
3. **Verify Authorized redirect URIs** includes:
   - `http://localhost:5173`
   - `http://localhost:8000/api/v1/auth/google/callback`

**Important:** 
- Use `http://` (not `https://`) for localhost
- Match the exact port number (5173 for Vite default)
- No trailing slashes

### Step 4: Update Environment Files

#### Frontend `.env` (in `frontend/` directory):
```env
VITE_API_URL=http://localhost:8000/api/v1
VITE_GOOGLE_CLIENT_ID=your-actual-client-id.apps.googleusercontent.com
```

#### Backend `.env` (in `backend/study_tracker_app/` directory):
```env
GOOGLE_CLIENT_ID=your-actual-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-actual-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/v1/auth/google/callback
```

**Replace `your-actual-client-id` and `your-actual-client-secret` with the values from Google Cloud Console!**

### Step 5: Restart Services

1. **Stop frontend dev server** (Ctrl+C)
2. **Restart frontend**:
   ```bash
   cd frontend
   npm run dev
   ```
3. **Restart backend** (if needed):
   ```bash
   cd backend/study_tracker_app
   php artisan serve
   ```

### Step 6: Test Again

1. Clear browser cache or use incognito mode
2. Try Google Sign-In again
3. Check browser console for errors

---

## Common Issues & Solutions

### Issue 1: "VITE_GOOGLE_CLIENT_ID is undefined"

**Solution:**
- Make sure `.env` file is in `frontend/` directory (not root)
- Variable name must start with `VITE_` for Vite to expose it
- Restart dev server after adding/changing `.env`

### Issue 2: "Client ID doesn't match"

**Solution:**
- Frontend and backend should use the **same Client ID**
- Copy the Client ID exactly from Google Cloud Console
- No extra spaces or quotes in `.env` file

### Issue 3: "Redirect URI mismatch"

**Solution:**
- Add your frontend URL to **Authorized JavaScript origins** in Google Cloud Console
- Add your backend callback URL to **Authorized redirect URIs**
- Use exact URLs (including `http://` and port numbers)

### Issue 4: "OAuth consent screen not configured"

**Solution:**
- Complete the OAuth consent screen setup in Google Cloud Console
- Add test users if using External app type
- Wait a few minutes for changes to propagate

### Issue 5: Environment variables not loading

**Solution:**
- Check file is named exactly `.env` (not `.env.txt` or `.env.local`)
- No spaces around `=` in `.env` file
- Restart dev server after changes
- For Vite: Variables must start with `VITE_`

---

## Verification Checklist

- [ ] Frontend `.env` has `VITE_GOOGLE_CLIENT_ID` set
- [ ] Backend `.env` has `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` set
- [ ] Client ID exists in Google Cloud Console
- [ ] Authorized JavaScript origins includes frontend URL
- [ ] Authorized redirect URIs includes backend callback URL
- [ ] OAuth consent screen is configured
- [ ] Dev server restarted after `.env` changes
- [ ] Client ID in `.env` matches Google Cloud Console exactly

---

## Still Not Working?

1. **Check browser console** for specific error messages
2. **Check backend logs** (`storage/logs/laravel.log`)
3. **Verify the Client ID format**: Should end with `.apps.googleusercontent.com`
4. **Try creating a new OAuth client** in Google Cloud Console
5. **Wait 5-10 minutes** after making changes in Google Cloud Console (propagation delay)

---

## Quick Test

To verify your Client ID is working, you can test it directly:

1. Open browser console on your login page
2. Run:
   ```javascript
   console.log(import.meta.env.VITE_GOOGLE_CLIENT_ID)
   ```
3. Should output your Client ID (not `undefined`)

If it's `undefined`, your `.env` file is not being loaded correctly.
