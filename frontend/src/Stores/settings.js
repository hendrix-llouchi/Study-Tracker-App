import { defineStore } from 'pinia'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    profile: {
      name: '',
      email: '',
      university: '',
      avatar: null
    },
    emailPreferences: {
      morningEmailTime: '07:00',
      enabled: true,
      weeklyReports: true
    },
    reminderSettings: {
      eveningReminderTime: '21:00',
      enabled: true,
      channels: ['email'], // 'email', 'push', 'both'
      frequency: 'daily',
      doNotDisturb: false
    },
    notifications: {
      pushEnabled: true,
      emailEnabled: true,
      categories: {
        assignments: true,
        deadlines: true,
        reminders: true,
        reports: true
      }
    }
  }),

  actions: {
    async fetchSettings() {
      // Bypass API call - use mock data
      this.loadMockData()
      return this.$state
    },

    loadMockData() {
      this.profile = {
        name: 'John Doe',
        email: 'john.doe@university.edu',
        university: 'University of Technology',
        avatar: null
      }
    },

    async updateProfile(profileData) {
      this.profile = { ...this.profile, ...profileData }
      return this.profile
    },

    async updateEmailPreferences(preferences) {
      this.emailPreferences = { ...this.emailPreferences, ...preferences }
      return this.emailPreferences
    },

    async updateReminderSettings(settings) {
      this.reminderSettings = { ...this.reminderSettings, ...settings }
      return this.reminderSettings
    },

    async updateNotifications(notifications) {
      this.notifications = { ...this.notifications, ...notifications }
      return this.notifications
    }
  }
})

