# FocusTrack Frontend

Vue.js 3 frontend application for FocusTrack study tracker.

## Setup

1. Install dependencies:
```bash
npm install
```

2. Create `.env` file:
```bash
cp .env.example .env
```

3. Configure API URL in `.env`:
```
VITE_API_URL=http://localhost:8000/api/v1
```

4. Start development server:
```bash
npm run dev
```

The frontend will run on `http://localhost:5173`

## Backend Connection

The frontend is configured to connect to the Laravel backend API. Make sure:

1. Backend is running on `http://localhost:8000`
2. CORS is properly configured in the backend
3. Sanctum is configured for SPA authentication
4. API URL matches in `.env` file

## API Service

All API calls are made through `src/services/api.js` which uses axios with:
- Automatic token injection from localStorage
- Error handling and 401 redirects
- Base URL configuration from environment variables
