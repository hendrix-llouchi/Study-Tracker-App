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
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/bd1c4c7e-781a-479d-9320-cf87ca4f25f6',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'run1',hypothesisId:'C,E',location:'auth.js:31',message:'Login attempt started',data:{email:credentials.email,apiBaseURL:import.meta.env.VITE_API_URL,windowOrigin:window.location.origin,timestamp:Date.now()},timestamp:Date.now()})}).catch(()=>{});
        // #endregion
        const response = await api.post('/auth/login', credentials)
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/bd1c4c7e-781a-479d-9320-cf87ca4f25f6',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'run1',hypothesisId:'A',location:'auth.js:34',message:'Login API response received',data:{success:response.success,hasData:!!response.data,timestamp:Date.now()},timestamp:Date.now()})}).catch(()=>{});
        // #endregion
        if (response.success && response.data) {
          this.setUser(response.data.user)
          this.setToken(response.data.token)
          return { user: response.data.user, token: response.data.token }
        }
        throw new Error(response.message || 'Login failed')
      } catch (error) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/bd1c4c7e-781a-479d-9320-cf87ca4f25f6',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'run1',hypothesisId:'C,E',location:'auth.js:42',message:'Login error caught',data:{message:error.message,errorCode:error.error_code,errors:error.errors,timestamp:Date.now()},timestamp:Date.now()})}).catch(()=>{});
        // #endregion
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
    },

    async googleAuth(googleData) {
      try {
        const response = await api.post('/auth/google', googleData)
        if (response.success && response.data) {
          this.setUser(response.data.user)
          this.setToken(response.data.token)
          return { user: response.data.user, token: response.data.token }
        }
        throw new Error(response.message || 'Google authentication failed')
      } catch (error) {
        throw error
      }
    }
  }
})

