import { ref, computed, onMounted } from 'vue'
import { useDashboardStore } from '@/Stores/dashboard'

export function useDashboard() {
  const dashboardStore = useDashboardStore()
  const loading = ref(false)
  
  const loadDashboard = async () => {
    loading.value = true
    try {
      await dashboardStore.fetchDashboardData()
    } catch (error) {
      console.error('Failed to load dashboard:', error)
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
    recentActivities: computed(() => dashboardStore.recentActivities),
    studyStreak: computed(() => dashboardStore.studyStreak),
    weeklyProgress: computed(() => dashboardStore.weeklyProgress),
    loading,
    refresh: loadDashboard
  }
}

