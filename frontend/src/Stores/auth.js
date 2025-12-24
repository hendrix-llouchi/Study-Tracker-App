import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    isAuthenticated: !!localStorage.getItem('token')
  }),

  getters: {
    currentUser: (state) => state.user,
    isLoggedIn: (state) => state.isAuthenticated
  },

  actions: {
    setUser(user) {
      this.user = user
      this.isAuthenticated = !!user
    },

    setToken(token) {
      this.token = token
      if (token) {
        localStorage.setItem('token', token)
      } else {
        localStorage.removeItem('token')
      }
    },

    async login(credentials) {
      try {
        const response = await api.post('/auth/login', credentials)
        if (response.success && response.data) {
          this.setUser(response.data.user)
          this.setToken(response.data.token)
          return { user: response.data.user, token: response.data.token }
        }
        throw new Error(response.message || 'Login failed')
      } catch (error) {
        throw error
      }
    },

    async register(userData) {
      try {
        console.log('Registering user:', userData)
        const response = await api.post('/auth/register', userData)
        console.log('Registration response:', response)
        if (response.success && response.data) {
          this.setUser(response.data.user)
          this.setToken(response.data.token)
          return { user: response.data.user, token: response.data.token }
        }
        throw new Error(response.message || 'Registration failed')
      } catch (error) {
        console.error('Registration error:', error)
        // If error has a message, throw it, otherwise create a user-friendly message
        if (error.message) {
          throw error
        }
        throw new Error('Registration failed. Please check your connection and try again.')
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.setUser(null)
        this.setToken(null)
      }
    },

    async fetchUser() {
      try {
        const response = await api.get('/auth/me')
        if (response.success && response.data) {
          this.setUser(response.data.user)
          return response.data.user
        }
      } catch (error) {
        // If token is invalid, clear it
        this.setUser(null)
        this.setToken(null)
        throw error
      }
    },

    async updateProfile(data) {
      try {
        const response = await api.put('/settings/profile', data)
        if (response.success && response.data) {
          this.setUser(response.data.profile)
          return response.data.profile
        }
        throw new Error(response.message || 'Update failed')
      } catch (error) {
        throw error
      }
    }
  }
})

