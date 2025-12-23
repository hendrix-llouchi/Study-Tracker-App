import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/Stores/auth'

export function useAuth() {
  const authStore = useAuthStore()
  const router = useRouter()
  
  const login = async (credentials) => {
    try {
      await authStore.login(credentials)
      router.push('/dashboard')
    } catch (error) {
      throw error
    }
  }
  
  const logout = async () => {
    await authStore.logout()
    router.push('/login')
  }
  
  const register = async (userData) => {
    try {
      await authStore.register(userData)
      router.push('/onboarding/profile')
    } catch (error) {
      throw error
    }
  }
  
  return {
    user: computed(() => authStore.user),
    isAuthenticated: computed(() => authStore.isAuthenticated),
    login,
    logout,
    register,
    updateProfile: authStore.updateProfile
  }
}

