import { computed, onMounted } from 'vue'
import { useDashboardStore } from '@/Stores/dashboard'

export function useDashboard() {
  const dashboardStore = useDashboardStore()
  
  const loadDashboard = async () => {
    try {
      await dashboardStore.fetchDashboardData()
    } catch (error) {
      console.error('Failed to load dashboard:', error)
      throw error
    }
  }
  
  onMounted(() => {
    loadDashboard()
  })
  
  return {
    stats: computed(() => dashboardStore.stats),
    upcomingClasses: computed(() => dashboardStore.upcomingClasses),
    recentActivities: computed(() => dashboardStore.recentActivities),
    studyStreak: computed(() => dashboardStore.studyStreak),
    weeklyProgress: computed(() => dashboardStore.weeklyProgress),
    loading: computed(() => dashboardStore.loading),
    error: computed(() => dashboardStore.error),
    refresh: loadDashboard
  }
}

