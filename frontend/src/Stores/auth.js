import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: {
      id: 'usr_001',
      name: 'John Doe',
      email: 'john.doe@university.edu',
      avatar: null
    },
    token: 'mock-token',
    isAuthenticated: true // Bypass auth for now
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
      // Bypass API call for now - just set mock user
      this.setUser({
        id: 'usr_001',
        name: credentials.email?.split('@')[0] || 'User',
        email: credentials.email,
        avatar: null
      })
      this.setToken('mock-token')
      return { user: this.user, token: this.token }
    },

    async register(userData) {
      // Bypass API call for now - just set mock user
      this.setUser({
        id: 'usr_001',
        name: userData.name || userData.email?.split('@')[0] || 'User',
        email: userData.email,
        avatar: null
      })
      this.setToken('mock-token')
      return { user: this.user, token: this.token }
    },

    async logout() {
      // Bypass API call for now
      this.setUser(null)
      this.setToken(null)
    },

    async updateProfile(data) {
      // Bypass API call for now - just update local state
      this.setUser({ ...this.user, ...data })
      return this.user
    }
  }
})

