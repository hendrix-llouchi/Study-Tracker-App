	FRONTEND 


Frontend Application Development Specification
FocusTrack Study Tracker App - Vue.js + Inertia.js

Pages Required
1. Authentication Pages
/login
Primary Functionality: User authentication with email/password and Google OAuth
Components: Login form, OAuth buttons, password reset link
State: Form validation, authentication error handling
Redirect: Upon success → /dashboard
/register
Primary Functionality: New user registration
Components: Registration form with email, password, confirm password
State: Form validation, registration error handling
Redirect: Upon success → /onboarding/profile
/forgot-password
Primary Functionality: Password reset request
Components: Email input form, success message display
State: Email validation, submission status
/reset-password/:token
Primary Functionality: Set new password with reset token
Components: Password reset form
State: Token validation, password strength indicator

2. Onboarding Flow Pages
/onboarding/profile
Primary Functionality: Collect basic student information
Components: Form with fields (name, university, semester, courses)
State: Profile data, form validation
Redirect: Upon completion → /onboarding/timetable
/onboarding/timetable
Primary Functionality: Upload or manually enter semester timetable
Components: File upload, manual entry grid, OCR processing indicator
State: Timetable data, upload status, parsing results
Redirect: Upon completion → /onboarding/results
/onboarding/results
Primary Functionality: Input baseline academic results
Components: Course results input form, grade entry fields
State: Results data, calculation of baseline GPA
Redirect: Upon completion → /onboarding/preferences
/onboarding/preferences
Primary Functionality: Configure notification and reminder settings
Components: Preference toggles, time pickers for email/notifications
State: User preferences
Redirect: Upon completion → /dashboard (with welcome tour trigger)

3. Main Application Pages
/dashboard (Home/Overview)
Primary Functionality: Central hub showing academic and study performance at a glance
Components:
Performance gauge (overall percentage)
Stats cards (courses enrolled, hours studied, completion rate, assignments due)
Quick actions panel
Upcoming classes widget
Recent activity feed
Study streak calendar
Weekly progress mini-chart
State: Dashboard metrics, loading states, date range filters
Data Fetching: User stats, upcoming events, recent activities
/performance
Primary Functionality: Detailed academic performance tracking and visualization
Components:
GPA trend line chart
Subject-wise performance bar chart
Performance comparison table
Grade distribution chart
Filters (semester, course, date range)
Performance trend indicators (improving/declining/stable)
State: Performance data, selected filters, chart configurations
Data Fetching: Academic results history, calculated trends
/results/upload
Primary Functionality: Add and manage academic results
Components:
Manual entry form (course, score, grade, credits, assessment type)
Bulk upload (CSV/PDF)
Results history table with edit/delete actions
GPA calculator display
State: Form data, uploaded files, results list, validation errors
Data Fetching: Existing results, course list
/planning (Study Planning Hub)
Primary Functionality: Create and manage daily/weekly study plans
Components:
Calendar view (daily/weekly toggle)
Study plan creation form
Timetable visualization overlay
Planned vs actual time comparison
Task completion checklist
Carry-over incomplete tasks feature
State: Selected date, planned tasks, timetable data, completion status
Data Fetching: Study plans, timetable, task completion history
/planning/create
Primary Functionality: Create new study plan for specific date
Components:
Date selector
Course/subject dropdown
Topic input
Duration picker
Priority selector (High/Medium/Low)
Study type selector (Review/New Material/Practice)
Conflict checker (shows timetable conflicts)
State: Form data, validation, conflict warnings
Data Fetching: Courses list, timetable for selected date
/timetable
Primary Functionality: View and manage semester class schedule
Components:
Weekly timetable grid
Edit/add class modal
Upload new timetable option
Auto-suggested study slots display
Export timetable option
State: Timetable data, edit mode, selected cell
Data Fetching: Current timetable, semesters list
/progress/weekly
Primary Functionality: View detailed weekly progress reports
Components:
Week selector
Study hours breakdown (planned vs actual)
Task completion rate chart
Most/least studied subjects
Week-over-week comparison
AI-generated insights panel
Downloadable PDF report
State: Selected week, report data, loading states
Data Fetching: Weekly statistics, AI insights
/progress/analytics
Primary Functionality: Advanced analytics and insights dashboard
Components:
Study consistency heatmap
Time distribution by subject (pie chart)
Productivity trends over time
Weak areas identification
Custom date range analytics
Export analytics data
State: Date range, selected metrics, chart data
Data Fetching: Historical study data, performance correlations
/ai-coach (Phase 2)
Primary Functionality: AI-powered study advisor chatbot
Components:
Chat interface with message history
Quick action buttons (analyze weak areas, study tips, etc.)
Context display (shows data AI is analyzing)
Export chat conversation
State: Chat messages, streaming response, loading indicator
Data Fetching: Chat history, user academic context
API Integration: Gemini API for AI responses
/settings
Primary Functionality: Manage account and app preferences
Components:
Profile information section
Notification preferences
Email reminder settings
Password change form
Data export option
Account deletion
State: Settings data, form validation, save status
Data Fetching: Current user settings
/settings/courses
Primary Functionality: Manage enrolled courses
Components:
Courses list with edit/delete
Add new course form
Course details (name, code, credits, instructor)
State: Courses list, edit mode, form data
Data Fetching: User courses
/assignments
Primary Functionality: Track assignments and deadlines
Components:
Assignments list with status filters
Add assignment form
Calendar view of deadlines
Mark as complete action
Priority indicators
State: Assignments list, filters, selected assignment
Data Fetching: Assignments data, upcoming deadlines

User Roles and Permissions
Student (Primary Role - MVP)
Can:
Create and manage their own account
View their personal dashboard and analytics
Upload and track their academic results
Create and manage study plans
Upload and edit their timetable
Receive email notifications and reminders
Access AI study coach (Phase 2)
Configure personal settings and preferences
Export their data
Cannot:
View other students' data
Access admin functions
Modify system-wide settings

Admin (Phase 3 - Future)
Can:
All student permissions
View aggregated analytics (anonymized)
Manage user accounts
Configure system settings
Access usage metrics
Send system-wide announcements

Mentor/Tutor (Phase 3 - Future)
Can:
View assigned students' progress (with permission)
Provide feedback and recommendations
Set goals for students
Access comparative analytics for mentees

Shared Components
1. Navigation System - Sidebar Navigation
Location: Fixed left sidebar (240px width)
Components:
<Sidebar>
  <SidebarLogo />
  <SidebarNav>
    <NavItem icon="LayoutDashboard" to="/dashboard" label="Dashboard" />
    <NavItem icon="BarChart3" to="/performance" label="Performance" />
    <NavItem icon="Calendar" to="/planning" label="Planning" />
    <NavItem icon="Clock" to="/timetable" label="Timetable" />
    <NavItem icon="BookOpen" to="/assignments" label="Assignments" />
    
    <NavSection label="Insights" />
    <NavItem icon="TrendingUp" to="/progress/weekly" label="Weekly Reports" />
    <NavItem icon="PieChart" to="/progress/analytics" label="Analytics" />
    <NavItem icon="MessageSquare" to="/ai-coach" label="AI Coach" badge="New" />
    
    <NavSection label="Settings" />
    <NavItem icon="Settings" to="/settings" label="Settings" />
    <NavItem icon="HelpCircle" to="/help" label="Help" />
  </SidebarNav>
</Sidebar>

Features:
Active route highlighting (green background #ECFDF5, green text #059669)
Hover states on menu items
Collapsible on mobile (hamburger toggle)
Smooth transitions (0.2s ease)
Icons from lucide-vue-next
State Management:
sidebarCollapsed (boolean for mobile)
activeRoute (current route path)

2. Header/Top Bar
Location: Fixed top, spans full width minus sidebar
Components:
<AppHeader>
  <HeaderLeft>
    <Breadcrumbs />
  </HeaderLeft>
  
  <HeaderRight>
    <SearchBar placeholder="Search..." />
    <NotificationBell :unreadCount="notifications.unread" />
    <ThemeToggle /> <!-- Phase 2 -->
    <UserDropdown>
      <UserAvatar :src="user.avatar" :name="user.name" />
      <DropdownMenu>
        <DropdownItem icon="User" to="/settings">Profile</DropdownItem>
        <DropdownItem icon="Settings" to="/settings">Settings</DropdownItem>
        <DropdownDivider />
        <DropdownItem icon="LogOut" @click="logout">Logout</DropdownItem>
      </DropdownMenu>
    </UserDropdown>
  </HeaderRight>
</AppHeader>

Features:
Greeting message based on time of day
Real-time notification count
User avatar with dropdown menu
Responsive search bar (expands on focus)
State Management:
user (current user object)
notifications (notification data)
searchQuery (search input)

3. Breadcrumbs
Location: Top left of header, below main navigation
Implementation:
<Breadcrumbs>
  <BreadcrumbItem to="/dashboard">Home</BreadcrumbItem>
  <BreadcrumbSeparator />
  <BreadcrumbItem to="/performance">Performance</BreadcrumbItem>
  <BreadcrumbSeparator />
  <BreadcrumbItem active>Details</BreadcrumbItem>
</Breadcrumbs>

Features:
Auto-generated from route meta
Clickable navigation (except last item)
Chevron separators
Responsive (collapses on mobile to show only current page)

4. Layout Components
AppLayout.vue (Authenticated Layout)
<template>
  <div class="app-layout">
    <Sidebar />
    <div class="main-content">
      <AppHeader />
      <main class="content-area">
        <slot />
      </main>
    </div>
  </div>
</template>

GuestLayout.vue (Unauthenticated Layout)
<template>
  <div class="guest-layout">
    <GuestHeader />
    <main class="guest-content">
      <slot />
    </main>
    <Footer />
  </div>
</template>


5. Reusable UI Components
Core Components:
Button.vue (variants: primary, secondary, ghost, icon)
Card.vue (with header, body, footer slots)
Input.vue (text, email, password, number)
Select.vue (single and multi-select)
Checkbox.vue & Radio.vue
DatePicker.vue & TimePicker.vue
Badge.vue (variants: success, warning, error, info, neutral)
ProgressBar.vue & ProgressRing.vue
Tooltip.vue
Alert.vue (banner style, dismissible)
Table.vue (with sorting, filtering)
Pagination.vue
EmptyState.vue
LoadingSpinner.vue & LoadingSkeleton.vue
Chart Components:
LineChart.vue
BarChart.vue
PieChart.vue
HeatmapCalendar.vue
GaugeChart.vue

Modals/Popups
1. Study Plan Modal (PlanModal.vue)
Trigger: "Create Plan" button on planning page
Purpose: Quick plan creation without leaving current page
Components: Full plan creation form, conflict warnings
Actions: Save, Save & Create Another, Cancel
2. Result Entry Modal (ResultModal.vue)
Trigger: "Add Result" button on performance page
Purpose: Quick result entry
Components: Course selector, grade input, semester selector
Actions: Save, Cancel
3. Confirmation Modal (ConfirmModal.vue)
Trigger: Delete actions, account deletion
Purpose: Confirm destructive actions
Components: Warning message, confirmation input (for critical actions)
Actions: Confirm, Cancel
4. Assignment Details Modal (AssignmentModal.vue)
Trigger: Click on assignment card
Purpose: View/edit assignment details
Components: Assignment info, deadline, status, notes
Actions: Mark Complete, Edit, Delete, Close
5. Bulk Upload Preview Modal (UploadPreviewModal.vue)
Trigger: After CSV/PDF upload on results page
Purpose: Preview and validate uploaded data before saving
Components: Data table, validation errors, edit capability
Actions: Confirm Upload, Edit Data, Cancel
6. Welcome Tour Modal (OnboardingTourModal.vue)
Trigger: First login after onboarding completion
Purpose: Interactive feature walkthrough
Components: Step-by-step guide, feature highlights, skip option
Actions: Next, Previous, Skip, Finish
7. Notification Settings Modal (NotificationModal.vue)
Trigger: Bell icon in header
Purpose: View notifications and configure settings
Components: Notification list, mark as read, preferences link
Actions: View All, Mark All Read, Settings, Close
8. Export Data Modal (ExportModal.vue)
Trigger: Export buttons on various pages
Purpose: Configure and download data exports
Components: Format selector (PDF/CSV/JSON), date range, data type selection
Actions: Export, Cancel
9. Quick Action Modal (QuickActionModal.vue)
Trigger: FAB (Floating Action Button) on dashboard
Purpose: Quick access to common actions
Components: Action buttons grid (Add Plan, Add Result, Add Assignment)
Actions: Select action (opens respective modal), Close
10. Weekly Report Preview Modal (ReportPreviewModal.vue)
Trigger: View report link in weekly report email
Purpose: Full-screen report view with download option
Components: Complete weekly report, charts, insights
Actions: Download PDF, Share, Close

Technical Requirements
1. CSS Framework
Primary: Tailwind CSS 3.4+
Configuration:
Custom color palette from design system
Extended spacing scale
Custom components for buttons, cards, badges
Dark mode support (Phase 2)
2. Component Architecture
Component Structure:
src/
├── Components/
│   ├── Common/
│   │   ├── Button.vue
│   │   ├── Card.vue
│   │   ├── Input.vue
│   │   └── ...
│   ├── Charts/
│   │   ├── LineChart.vue
│   │   ├── BarChart.vue
│   │   └── ...
│   ├── Layout/
│   │   ├── AppLayout.vue
│   │   ├── GuestLayout.vue
│   │   ├── Sidebar.vue
│   │   └── AppHeader.vue
│   ├── Dashboard/
│   │   ├── StatsCard.vue
│   │   ├── PerformanceGauge.vue
│   │   ├── UpcomingClasses.vue
│   │   └── ...
│   ├── Performance/
│   │   ├── GPATrendChart.vue
│   │   ├── SubjectComparison.vue
│   │   └── ...
│   ├── Planning/
│   │   ├── PlanCalendar.vue
│   │   ├── PlanForm.vue
│   │   └── ...
│   └── Modals/
│       ├── PlanModal.vue
│       ├── ResultModal.vue
│       └── ...
├── Pages/
│   ├── Auth/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   └── ...
│   ├── Onboarding/
│   │   ├── Profile.vue
│   │   ├── Timetable.vue
│   │   └── ...
│   ├── Dashboard.vue
│   ├── Performance.vue
│   └── ...
├── Composables/
│   ├── useAuth.js
│   ├── useDashboard.js
│   ├── usePerformance.js
│   ├── usePlanning.js
│   └── ...
├── Services/
│   ├── api.js
│   ├── auth.service.js
│   ├── dashboard.service.js
│   ├── performance.service.js
│   └── ...
├── Stores/
│   ├── auth.js
│   ├── user.js
│   ├── notifications.js
│   └── ...
└── Utils/
    ├── dateHelpers.js
    ├── chartHelpers.js
    ├── validators.js
    └── ...

3. State Management
Pinia Stores:
stores/auth.js
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false
  }),
  actions: {
    login(credentials),
    logout(),
    register(userData),
    updateProfile(data)
  },
  getters: {
    currentUser: (state) => state.user,
    isLoggedIn: (state) => state.isAuthenticated
  }
})

stores/user.js
export const useUserStore = defineStore('user', {
  state: () => ({
    profile: null,
    preferences: null,
    courses: [],
    timetable: null
  }),
  actions: {
    fetchProfile(),
    updatePreferences(prefs),
    fetchCourses(),
    updateTimetable(data)
  }
})

stores/dashboard.js
export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: null,
    upcomingClasses: [],
    recentActivities: [],
    studyStreak: null
  }),
  actions: {
    fetchDashboardData(),
    refreshStats()
  }
})

stores/performance.js
export const usePerformanceStore = defineStore('performance', {
  state: () => ({
    results: [],
    gpaTrend: [],
    subjectPerformance: [],
    filters: {
      semester: 'all',
      dateRange: null
    }
  }),
  actions: {
    fetchResults(),
    addResult(data),
    updateResult(id, data),
    deleteResult(id),
    calculateGPA()
  }
})

stores/planning.js
export const usePlanningStore = defineStore('planning', {
  state: () => ({
    plans: [],
    selectedDate: new Date(),
    timetable: null
  }),
  actions: {
    fetchPlans(date),
    createPlan(data),
    updatePlan(id, data),
    deletePlan(id),
    markComplete(id)
  }
})

stores/notifications.js
export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    unreadCount: 0
  }),
  actions: {
    fetchNotifications(),
    markAsRead(id),
    markAllAsRead()
  }
})

4. Composables (Vue Composition API)
composables/useAuth.js
import { useAuthStore } from '@/Stores/auth'
import { router } from '@inertiajs/vue3'

export function useAuth() {
  const authStore = useAuthStore()
  
  const login = async (credentials) => {
    try {
      await authStore.login(credentials)
      router.visit('/dashboard')
    } catch (error) {
      // Handle error
    }
  }
  
  const logout = async () => {
    await authStore.logout()
    router.visit('/login')
  }
  
  return {
    user: computed(() => authStore.user),
    isAuthenticated: computed(() => authStore.isAuthenticated),
    login,
    logout
  }
}

composables/useDashboard.js
import { useDashboardStore } from '@/Stores/dashboard'

export function useDashboard() {
  const dashboardStore = useDashboardStore()
  const loading = ref(false)
  
  const loadDashboard = async () => {
    loading.value = true
    try {
      await dashboardStore.fetchDashboardData()
    } finally {
      loading.value = false
    }
  }
  
  onMounted(() => {
    loadDashboard()
  })
  
  return {
    stats: computed(() => dashboardStore.stats),
    upcomingClasses: computed(() => dashboardStore.upcomingClasses),
    loading,
    refresh: loadDashboard
  }
}

composables/usePerformance.js
export function usePerformance() {
  const performanceStore = usePerformanceStore()
  
  const calculateTrend = (results) => {
    // Trend calculation logic
  }
  
  const getWeakAreas = () => {
    // Identify subjects below average
  }
  
  return {
    results: computed(() => performanceStore.results),
    gpaTrend: computed(() => performanceStore.gpaTrend),
    weakAreas: computed(() => getWeakAreas()),
    addResult: performanceStore.addResult
  }
}

composables/usePlanning.js
export function usePlanning() {
  const planningStore = usePlanningStore()
  
  const getPlansForDate = (date) => {
    return planningStore.plans.filter(p => 
      isSameDay(new Date(p.date), date)
    )
  }
  
  const checkConflicts = (newPlan) => {
    // Check against timetable
  }
  
  return {
    plans: computed(() => planningStore.plans),
    selectedDate: computed(() => planningStore.selectedDate),
    createPlan: planningStore.createPlan,
    checkConflicts
  }
}

5. API Services Layer
services/api.js (Base API Client)
import axios from 'axios'

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
apiClient.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => Promise.reject(error)
)

// Response interceptor
apiClient.interceptors.response.use(
  response => response.data,
  error => {
    if (error.response?.status === 401) {
      // Handle unauthorized
      router.visit('/login')
    }
    return Promise.reject(error)
  }
)

export default apiClient

services/auth.service.js
import api from './api'

export const authService = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'),
  forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
  resetPassword: (token, password) => api.post('/auth/reset-password', { token, password }),
  getCurrentUser: () => api.get('/auth/me')
}

services/dashboard.service.js
export const dashboardService = {
  getStats: () => api.get('/dashboard/stats'),
  getUpcomingClasses: () => api.get('/dashboard/upcoming-classes'),
  getRecentActivities: () => api.get('/dashboard/activities'),
  getStudyStreak: () => api.get('/dashboard/streak')
}

services/performance.service.js
export const performanceService = {
  getResults: (filters) => api.get('/performance/results', { params: filters }),
  addResult: (data) => api.post('/performance/results', data),
  updateResult: (id, data) => api.put(`/performance/results/${id}`, data),
  deleteResult: (id) => api.delete(`/performance/results/${id}`),
  getGPATrend: () => api.get('/performance/gpa-trend'),
  getSubjectPerformance: () => api.get('/performance/subjects')
}

services/planning.service.js
export const planningService = {
  getPlans: (date) => api.get('/planning/plans', { params: { date } }),
  createPlan: (data) => api.post('/planning/plans', data),
  updatePlan: (id, data) => api.put(`/planning/plans/${id}`, data),
  deletePlan: (id) => api.delete(`/planning/plans/${id}`),
  markComplete: (id) => api.patch(`/planning/plans/${id}/complete`),
  getTimetable: () => api.get('/planning/timetable'),
  uploadTimetable: (formData) => api.post('/planning/timetable', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

6. URL-Based Routing (Inertia.js)
Route Structure:
// web.php (Laravel Backend Routes)

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    
    // Onboarding
    Route::prefix('onboarding')->group(function () {
        Route::get('/profile', [OnboardingController::class, 'profile']);
        Route::post('/profile', [OnboardingController::class, 'saveProfile']);
        Route::get('/timetable', [OnboardingController::class, 'timetable']);
        Route::post('/timetable', [OnboardingController::class, 'saveTimetable']);
        Route::get('/results', [OnboardingController::class, 'results']);
        Route::post('/results', [OnboardingController::class, 'saveResults']);
        Route::get('/preferences', [OnboardingController::class, 'preferences']);
        Route::post('/preferences', [OnboardingController::class, 'savePreferences']);
    });
    
    // Main App
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('performance')->group(function () {
        Route::get('/', [PerformanceController::class, 'index'])->name('performance');
        Route::get('/results/upload', [PerformanceController::class, 'upload']);
        Route::post('/results', [PerformanceController::class, 'store']);
    });
    
    Route::prefix('planning')->group(function () {
        Route::get('/', [PlanningController::class, 'index'])->name('planning');
        Route::get('/create', [PlanningController::class, 'create']);
        Route::post('/plans', [PlanningController::class, 'store']);
    });
    
    Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable');
    
    Route::prefix('progress')->group(function () {
        Route::get('/weekly', [ProgressController::class, 'weekly'])->name('progress.weekly');
        Route::get('/analytics', [ProgressController::class, 'analytics']);
    });
    
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments');
    
    Route::get('/ai-coach', [AICoachController::class, 'index'])->name('ai-coach');
    
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings');
        Route::get('/courses', [SettingsController::class, 'courses']);
        Route::patch('/profile', [SettingsController::class, 'updateProfile']);
        Route::patch('/preferences', [SettingsController::class, 'updatePreferences']);
    });
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

7. Mock API Store Structure
mockData/users.js
export const mockUsers = [
  {
    id: 'usr_001',
    name: 'John Doe',
    email: 'john.doe@university.edu',
    avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=John',
    university: 'University of Technology',
    semester: '3rd Semester',
    createdAt: '2024-09-01T00:00:00Z',
    onboardingCompleted: true,
    preferences: {
      morningEmailTime: '07:00',
      reminderTime: '21:00',
      emailNotifications: true,
      pushNotifications: true
    }
  }
]

`mockData/
courses.js`
export const mockCourses = [
  {
    id: 'crs_001',
    userId: 'usr_001',
    name: 'Data Structures & Algorithms',
    code: 'CS201',
    credits: 4,
    instructor: 'Dr. Sarah Johnson',
    color: 'orange',
    semester: 'Fall 2024'
  },
  {
    id: 'crs_002',
    userId: 'usr_001',
    name: 'Database Systems',
    code: 'CS301',
    credits: 3,
    instructor: 'Prof. Michael Chen',
    color: 'blue',
    semester: 'Fall 2024'
  },
  {
    id: 'crs_003',
    userId: 'usr_001',
    name: 'Computer Networks',
    code: 'CS302',
    credits: 3,
    instructor: 'Dr. Emily Rodriguez',
    color: 'green',
    semester: 'Fall 2024'
  },
  {
    id: 'crs_004',
    userId: 'usr_001',
    name: 'Software Engineering',
    code: 'CS303',
    credits: 4,
    instructor: 'Prof. David Kim',
    color: 'purple',
    semester: 'Fall 2024'
  }
]

mockData/results.js
export const mockResults = [
  {
    id: 'res_001',
    userId: 'usr_001',
    courseId: 'crs_001',
    courseName: 'Data Structures & Algorithms',
    assessmentType: 'Midterm',
    score: 85,
    maxScore: 100,
    grade: 'B+',
    credits: 4,
    semester: 'Fall 2024',
    date: '2024-10-15T00:00:00Z'
  },
  {
    id: 'res_002',
    userId: 'usr_001',
    courseId: 'crs_002',
    courseName: 'Database Systems',
    assessmentType: 'Quiz 1',
    score: 92,
    maxScore: 100,
    grade: 'A-',
    credits: 3,
    semester: 'Fall 2024',
    date: '2024-10-10T00:00:00Z'
  },
  {
    id: 'res_003',
    userId: 'usr_001',
    courseId: 'crs_003',
    courseName: 'Computer Networks',
    assessmentType: 'Assignment 1',
    score: 78,
    maxScore: 100,
    grade: 'C+',
    credits: 3,
    semester: 'Fall 2024',
    date: '2024-10-05T00:00:00Z'
  },
  {
    id: 'res_004',
    userId: 'usr_001',
    courseId: 'crs_001',
    courseName: 'Data Structures & Algorithms',
    assessmentType: 'Quiz 1',
    score: 88,
    maxScore: 100,
    grade: 'B+',
    credits: 4,
    semester: 'Fall 2024',
    date: '2024-09-28T00:00:00Z'
  }
]

mockData/plans.js
export const mockPlans = [
  {
    id: 'pln_001',
    userId: 'usr_001',
    courseId: 'crs_001',
    courseName: 'Data Structures & Algorithms',
    topic: 'Binary Search Trees',
    date: '2024-12-23',
    startTime: '09:00',
    duration: 120, // minutes
    priority: 'high',
    studyType: 'new-material',
    completed: false,
    actualDuration: null,
    notes: ''
  },
  {
    id: 'pln_002',
    userId: 'usr_001',
    courseId: 'crs_002',
    courseName: 'Database Systems',
    topic: 'SQL Joins Review',
    date: '2024-12-23',
    startTime: '14:00',
    duration: 90,
    priority: 'medium',
    studyType: 'review',
    completed: true,
    actualDuration: 85,
    notes: 'Completed practice problems'
  },
  {
    id: 'pln_003',
    userId: 'usr_001',
    courseId: 'crs_003',
    courseName: 'Computer Networks',
    topic: 'TCP/IP Protocol Practice',
    date: '2024-12-24',
    startTime: '10:00',
    duration: 60,
    priority: 'high',
    studyType: 'practice',
    completed: false,
    actualDuration: null,
    notes: ''
  }
]

mockData/timetable.js
export const mockTimetable = {
  id: 'ttb_001',
  userId: 'usr_001',
  semester: 'Fall 2024',
  classes: [
    {
      id: 'cls_001',
      courseId: 'crs_001',
      courseName: 'Data Structures & Algorithms',
      day: 'Monday',
      startTime: '10:00',
      endTime: '11:30',
      location: 'Room 301',
      type: 'Lecture'
    },
    {
      id: 'cls_002',
      courseId: 'crs_001',
      courseName: 'Data Structures & Algorithms',
      day: 'Wednesday',
      startTime: '10:00',
      endTime: '11:30',
      location: 'Room 301',
      type: 'Lecture'
    },
    {
      id: 'cls_003',
      courseId: 'crs_001',
      courseName: 'Data Structures & Algorithms Lab',
      day: 'Friday',
      startTime: '14:00',
      endTime: '16:00',
      location: 'Lab 102',
      type: 'Lab'
    },
    {
      id: 'cls_004',
      courseId: 'crs_002',
      courseName: 'Database Systems',
      day: 'Tuesday',
      startTime: '09:00',
      endTime: '10:30',
      location: 'Room 205',
      type: 'Lecture'
    },
    {
      id: 'cls_005',
      courseId: 'crs_002',
      courseName: 'Database Systems',
      day: 'Thursday',
      startTime: '09:00',
      endTime: '10:30',
      location: 'Room 205',
      type: 'Lecture'
    },
    {
      id: 'cls_006',
      courseId: 'crs_003',
      courseName: 'Computer Networks',
      day: 'Monday',
      startTime: '14:00',
      endTime: '15:30',
      location: 'Room 401',
      type: 'Lecture'
    },
    {
      id: 'cls_007',
      courseId: 'crs_003',
      courseName: 'Computer Networks',
      day: 'Wednesday',
      startTime: '14:00',
      endTime: '15:30',
      location: 'Room 401',
      type: 'Lecture'
    },
    {
      id: 'cls_008',
      courseId: 'crs_004',
      courseName: 'Software Engineering',
      day: 'Tuesday',
      startTime: '13:00',
      endTime: '14:30',
      location: 'Room 310',
      type: 'Lecture'
    },
    {
      id: 'cls_009',
      courseId: 'crs_004',
      courseName: 'Software Engineering',
      day: 'Thursday',
      startTime: '13:00',
      endTime: '14:30',
      location: 'Room 310',
      type: 'Lecture'
    }
  ]
}

mockData/assignments.js
export const mockAssignments = [
  {
    id: 'asn_001',
    userId: 'usr_001',
    courseId: 'crs_001',
    courseName: 'Data Structures & Algorithms',
    title: 'Advanced problem solving (sorting)',
    description: 'Implement merge sort and quick sort algorithms',
    dueDate: '2024-12-28T23:59:00Z',
    priority: 'high',
    status: 'pending',
    completedAt: null
  },
  {
    id: 'asn_002',
    userId: 'usr_001',
    courseId: 'crs_004',
    courseName: 'Software Engineering',
    title: 'Vocal chicken',
    description: 'Design document for class project',
    dueDate: '2024-12-30T23:59:00Z',
    priority: 'medium',
    status: 'pending',
    completedAt: null
  },
  {
    id: 'asn_003',
    userId: 'usr_001',
    courseId: 'crs_002',
    courseName: 'Database Systems',
    title: 'SQL Query Assignment',
    description: 'Complex joins and subqueries',
    dueDate: '2024-12-25T23:59:00Z',
    priority: 'high',
    status: 'completed',
    completedAt: '2024-12-22T15:30:00Z'
  }
]

mockData/dashboardStats.js
export const mockDashboardStats = {
  userId: 'usr_001',
  overallPerformance: 85,
  coursesEnrolled: 4,
  hoursStudied: 156,
  completionRate: 78,
  assignmentsDue: 2,
  upcomingClasses: [
    {
      id: 'cls_001',
      courseName: 'Data Structures & Algorithms',
      instructor: 'Dr. Sarah Johnson',
      time: '10:00 - 11:30',
      date: '2024-12-23',
      location: 'Room 301',
      thumbnail: 'https://images.unsplash.com/photo-1516116216624-53e697fedbea',
      isLive: false
    },
    {
      id: 'cls_004',
      courseName: 'Database Systems',
      instructor: 'Prof. Michael Chen',
      time: '09:00 - 10:30',
      date: '2024-12-24',
      location: 'Room 205',
      thumbnail: 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8',
      isLive: false
    }
  ],
  studyStreak: {
    current: 5,
    longest: 12,
    days: [
      { day: 'Mon', active: true, completed: true },
      { day: 'Tue', active: true, completed: true },
      { day: 'Wed', active: true, completed: true },
      { day: 'Thu', active: true, completed: true },
      { day: 'Fri', active: true, completed: true },
      { day: 'Sat', active: false, completed: false },
      { day: 'Sun', active: false, completed: false }
    ]
  },
  weeklyProgress: [
    { week: 'Week 1', completed: 12, planned: 15, missed: 3 },
    { week: 'Week 2', completed: 14, planned: 16, missed: 2 },
    { week: 'Week 3', completed: 10, planned: 14, missed: 4 },
    { week: 'Week 4', completed: 16, planned: 18, missed: 2 }
  ]
}

mockData/notifications.js
export const mockNotifications = [
  {
    id: 'ntf_001',
    userId: 'usr_001',
    type: 'reminder',
    title: 'Study Plan Reminder',
    message: "Don't forget to set your study plan for tomorrow!",
    read: false,
    createdAt: '2024-12-23T21:00:00Z'
  },
  {
    id: 'ntf_002',
    userId: 'usr_001',
    type: 'assignment',
    title: 'Assignment Due Soon',
    message: 'Advanced problem solving (sorting) is due in 5 days',
    read: false,
    createdAt: '2024-12-23T09:00:00Z'
  },
  {
    id: 'ntf_003',
    userId: 'usr_001',
    type: 'achievement',
    title: '5-Day Streak!',
    message: "Great job! You've maintained your study streak for 5 days.",
    read: true,
    createdAt: '2024-12-22T20:00:00Z'
  }
]

8. Component Reusability Standards
Prop Design Patterns:
<!-- Good: Flexible, typed props -->
<Button
  variant="primary"
  size="md"
  :loading="isSubmitting"
  :disabled="!isValid"
  @click="handleSubmit"
>
  Submit
</Button>

<!-- Bad: Too specific -->
<SubmitButton :is-submitting="isSubmitting" />

Slot Usage:
<Card>
  <template #header>
    <h3>Card Title</h3>
  </template>
  
  <template #default>
    <p>Card content goes here</p>
  </template>
  
  <template #footer>
    <Button>Action</Button>
  </template>
</Card>

Composable Patterns:
// Reusable data fetching
export function useFetch(url) {
  const data = ref(null)
  const loading = ref(false)
  const error = ref(null)
  
  const fetch = async () => {
    loading.value = true
    try {
      data.value = await api.get(url)
    } catch (e) {
      error.value = e
    } finally {
      loading.value = false
    }
  }
  
  return { data, loading, error, fetch }
}


Additional Considerations
1. Performance Optimization
Lazy load routes using dynamic imports
Implement virtual scrolling for long lists
Debounce search inputs
Optimize chart rendering with canvas
Cache API responses where appropriate
Use computed properties for derived state
2. Accessibility (a11y)
ARIA labels on all interactive elements
Keyboard navigation support (Tab, Enter, Escape)
Focus management in modals
Screen reader announcements for dynamic content
Color contrast compliance (WCAG AA)
Skip navigation links
3. Error Handling
Global error boundary component
Toast notifications for user feedback
Form validation with clear error messages
API error handling with retry logic
Offline detection and user notification
4. Loading States
Skeleton screens for initial loads
Spinner for button actions
Progress bars for uploads
Shimmer effects for cards
Graceful degradation
5. Responsive Design
Mobile-first approach
Breakpoints: 320px, 768px, 1024px, 1440px
Collapsible sidebar on mobile
Touch-friendly tap targets (min 44x44px)
Responsive tables (card view on mobile)
6. Data Validation
Client-side validation with Vuelidate or VeeValidate
Real-time feedback as user types
Server-side validation integration
Custom validation rules for academic data
7. Security
CSRF protection via Inertia
XSS prevention (Vue auto-escaping)
Sanitize user inputs
Secure authentication token storage
Role-based access control
8. Testing Strategy
Unit tests for utilities and composables (Vitest)
Component tests for UI components (Vue Test Utils)
E2E tests for critical flows (Playwright/Cypress)
API mock for testing
9. SEO & Meta Tags
Dynamic page titles via Inertia Head
Meta descriptions for public pages
Open Graph tags for sharing
10. Analytics Integration (Phase 2)
Track page views
Monitor user engagement metrics
Track feature usage
A/B testing capability

Development Workflow
1. Project Setup
# Install dependencies
npm install

# Environment variables
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Install frontend dependencies
npm install

# Start dev server
npm run dev

2. Development Commands
# Frontend dev server
npm run dev

# Build for production
npm run build

# Run tests
npm run test

# Lint code
npm run lint

# Format code
npm run format

3. Git Workflow
Feature branches: feature/dashboard-stats-cards
Bug fixes: fix/login-validation-error
Commit convention: Conventional Commits
Pull request reviews required

Phase 1 (MVP) Priority Tasks
Week 1-2: Foundation
Project setup and configuration
Authentication system (login, register, logout)
Layout components (Sidebar, Header)
Base UI components (Button, Card, Input)
Week 3-4: Core Features
Dashboard page with stats
Results upload and tracking
Study planning interface
Timetable management
Week 5-6: Automation & Polish
Email notification system
Reminder system
Weekly reports
Testing and bug fixes
Week 7-8: Launch Preparation
Performance optimization
Documentation
User acceptance testing
Deployment

This comprehensive specification provides everything needed to build STRUAPP with Vue.js + Inertia.js while maintaining clean architecture, code reusability, and excellent user experience.

