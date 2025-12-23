# FocusTrack Study Tracker - Frontend Application

A modern Vue.js 3 + Inertia.js frontend application for tracking academic performance and study planning.

## 🚀 Getting Started

### Prerequisites

- Node.js 18+ and npm
- A backend API server (Laravel recommended)

### Installation

1. Install dependencies:
```bash
npm install
```

2. Create environment file:
```bash
cp .env.example .env
```

3. Update `.env` with your configuration:
```
VITE_APP_NAME=FocusTrack
VITE_API_URL=http://localhost:8000
```

### Development

Start the development server:
```bash
npm run dev
```

The application will be available at `http://localhost:5173`

### Build for Production

```bash
npm run build
```

The built files will be in the `dist` directory.

## 📁 Project Structure

```
frontend/
├── src/
│   ├── Components/
│   │   ├── Common/          # Reusable UI components
│   │   ├── Dashboard/       # Dashboard-specific components
│   │   └── Layout/          # Layout components
│   ├── Pages/               # Page components
│   │   ├── Auth/           # Authentication pages
│   │   ├── Onboarding/     # Onboarding flow
│   │   └── ...             # Other pages
│   ├── Stores/             # Pinia stores
│   ├── Composables/        # Vue composables
│   ├── router/             # Vue Router configuration
│   ├── css/                # Global styles
│   └── main.js             # Application entry point
├── design.json             # Design system specifications
├── tailwind.config.js      # Tailwind CSS configuration
└── package.json
```

## 🎨 Design System

The application strictly follows the design system defined in `design.json`. All colors, typography, spacing, and component styles are configured to match the specifications.

### Key Design Tokens

- **Primary Color**: Green (#34D399)
- **Font Family**: Inter
- **Spacing Scale**: 4px base unit
- **Border Radius**: 8px default, 16px for cards

## 🔧 Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run preview` - Preview production build
- `npm run lint` - Run ESLint
- `npm run format` - Format code with Prettier

## 📦 Key Dependencies

- **Vue 3** - Progressive JavaScript framework
- **Vue Router** - Official router for Vue.js
- **Pinia** - State management
- **Tailwind CSS** - Utility-first CSS framework
- **Lucide Vue Next** - Icon library
- **Chart.js** - Chart library (for analytics)

## 🏗️ Architecture

### State Management

The application uses Pinia for state management with the following stores:

- `auth.js` - Authentication state
- `dashboard.js` - Dashboard data
- `performance.js` - Academic performance data
- `planning.js` - Study planning data

### Routing

Vue Router handles client-side routing with route guards for authentication.

### Components

Components are organized by feature:
- **Common**: Reusable UI components (Button, Card, Input, etc.)
- **Layout**: Layout components (Sidebar, Header, etc.)
- **Dashboard**: Dashboard-specific components
- **Modals**: Modal dialogs

## 🔐 Authentication

The app includes:
- Login page
- Registration page
- Password reset flow
- Google OAuth integration (to be implemented)

## 📱 Pages

### Authentication
- `/login` - User login
- `/register` - User registration
- `/forgot-password` - Password reset request
- `/reset-password/:token` - Password reset

### Onboarding
- `/onboarding/profile` - Profile setup
- `/onboarding/timetable` - Timetable upload
- `/onboarding/results` - Baseline results
- `/onboarding/preferences` - User preferences

### Main Application
- `/dashboard` - Main dashboard
- `/performance` - Performance tracking
- `/planning` - Study planning
- `/timetable` - Timetable management
- `/assignments` - Assignment tracking
- `/settings` - User settings

## 🎯 Next Steps

1. Connect to backend API
2. Implement remaining pages
3. Add modal components
4. Implement form validation
5. Add error handling
6. Write tests

## 📝 Notes

- The application is configured to work with a Laravel backend using Inertia.js
- API endpoints should be prefixed with `/api`
- Authentication tokens are stored in localStorage
- Mock data is used for development when API is unavailable

## 🤝 Contributing

1. Follow the design system in `design.json`
2. Use TypeScript-style prop definitions
3. Follow Vue 3 Composition API patterns
4. Maintain component reusability
5. Write descriptive commit messages

