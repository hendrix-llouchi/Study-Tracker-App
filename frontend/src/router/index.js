import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/Stores/auth'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/Pages/Auth/Login.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/Pages/Auth/Register.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/Pages/Auth/ForgotPassword.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/reset-password/:token',
    name: 'ResetPassword',
    component: () => import('@/Pages/Auth/ResetPassword.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/onboarding/profile',
    name: 'OnboardingProfile',
    component: () => import('@/Pages/Onboarding/Profile.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/onboarding/timetable',
    name: 'OnboardingTimetable',
    component: () => import('@/Pages/Onboarding/Timetable.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/onboarding/results',
    name: 'OnboardingResults',
    component: () => import('@/Pages/Onboarding/Results.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/onboarding/preferences',
    name: 'OnboardingPreferences',
    component: () => import('@/Pages/Onboarding/Preferences.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/Pages/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/performance',
    name: 'Performance',
    component: () => import('@/Pages/Performance.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/planning',
    name: 'Planning',
    component: () => import('@/Pages/Planning.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/timetable',
    name: 'Timetable',
    component: () => import('@/Pages/Timetable.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/assignments',
    name: 'Assignments',
    component: () => import('@/Pages/Assignments.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: () => import('@/Pages/Settings.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/progress/weekly',
    name: 'WeeklyReports',
    component: () => import('@/Pages/Progress/WeeklyReports.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/progress/analytics',
    name: 'Analytics',
    component: () => import('@/Pages/Progress/Analytics.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/ai-coach',
    name: 'AICoach',
    component: () => import('@/Pages/AICoach.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Router guard must be set up after Pinia is initialized
export function setupRouterGuard() {
  router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()
    
    // If user is not loaded but token exists, try to fetch user
    if (!authStore.user && authStore.token) {
      try {
        await authStore.fetchUser()
      } catch (error) {
        // Token is invalid, clear it
        authStore.setToken(null)
      }
    }
    
    // Check if route requires authentication
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      next({ name: 'Login', query: { redirect: to.fullPath } })
      return
    }
    
    // Check if route requires guest (not authenticated)
    if (to.meta.requiresGuest && authStore.isAuthenticated) {
      next({ name: 'Dashboard' })
      return
    }
    
    next()
  })
}

export default router
