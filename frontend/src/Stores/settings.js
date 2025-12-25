import { defineStore } from 'pinia'
import api from '@/services/api'
import { getErrorMessage, formatErrorForLog } from '@/utils/errorHandler'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    profile: {
      name: '',
      email: '',
      university: '',
      semester: '',
      avatar: null,
      avatar_url: null
    },
    preferences: {
      morningEmailTime: '07:00',
      reminderTime: '21:00',
      emailNotifications: true,
      pushNotifications: true,
      weeklyReportEnabled: true,
      weeklyReportDay: 'sunday',
      timezone: 'UTC'
    },
    loading: false,
    error: null
  }),

  actions: {
    async fetchSettings() {
      this.loading = true
      this.error = null
      
      try {
        const [profileResponse, preferencesResponse] = await Promise.all([
          api.get('/settings/profile'),
          api.get('/settings/preferences')
        ])
        
        if (profileResponse.success && profileResponse.data?.profile) {
          const user = profileResponse.data.profile
          this.profile = {
            name: user.name || '',
            email: user.email || '',
            university: user.university || '',
            semester: user.semester || '',
            avatar: null,
            avatar_url: user.avatar_url || null
          }
        }
        
        if (preferencesResponse.success && preferencesResponse.data?.preferences) {
          const pref = preferencesResponse.data.preferences
          this.preferences = {
            morningEmailTime: pref.morning_email_time || '07:00',
            reminderTime: pref.reminder_time || '21:00',
            emailNotifications: pref.email_notifications !== undefined ? pref.email_notifications : true,
            pushNotifications: pref.push_notifications !== undefined ? pref.push_notifications : true,
            weeklyReportEnabled: pref.weekly_report_enabled !== undefined ? pref.weekly_report_enabled : true,
            weeklyReportDay: pref.weekly_report_day || 'sunday',
            timezone: pref.timezone || 'UTC'
          }
        }
        
        return {
          profile: this.profile,
          preferences: this.preferences
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to fetch settings', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateProfile(profileData) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = {
          name: profileData.name,
          university: profileData.university || null,
          semester: profileData.semester || null
        }
        
        const response = await api.put('/settings/profile', backendData)
        
        if (response.success && response.data?.profile) {
          const user = response.data.profile
          this.profile = {
            name: user.name || '',
            email: user.email || '',
            university: user.university || '',
            semester: user.semester || '',
            avatar: this.profile.avatar,
            avatar_url: user.avatar_url || this.profile.avatar_url
          }
          return this.profile
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to update profile', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async uploadAvatar(file) {
      this.loading = true
      this.error = null
      
      try {
        const formData = new FormData()
        formData.append('avatar', file)
        
        const response = await api.post('/settings/avatar', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.success && response.data?.avatar_url) {
          this.profile.avatar_url = response.data.avatar_url
          return this.profile.avatar_url
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to upload avatar', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePreferences(preferencesData) {
      this.loading = true
      this.error = null
      
      try {
        const backendData = {
          morning_email_time: preferencesData.morningEmailTime || preferencesData.morning_email_time,
          reminder_time: preferencesData.reminderTime || preferencesData.reminder_time,
          email_notifications: preferencesData.emailNotifications !== undefined 
            ? preferencesData.emailNotifications 
            : preferencesData.email_notifications,
          push_notifications: preferencesData.pushNotifications !== undefined
            ? preferencesData.pushNotifications
            : preferencesData.push_notifications,
          weekly_report_enabled: preferencesData.weeklyReportEnabled !== undefined
            ? preferencesData.weeklyReportEnabled
            : preferencesData.weekly_report_enabled,
          weekly_report_day: preferencesData.weeklyReportDay || preferencesData.weekly_report_day,
          timezone: preferencesData.timezone || 'UTC'
        }
        
        // Remove undefined values
        Object.keys(backendData).forEach(key => {
          if (backendData[key] === undefined) {
            delete backendData[key]
          }
        })
        
        const response = await api.put('/settings/preferences', backendData)
        
        if (response.success && response.data?.preferences) {
          const pref = response.data.preferences
          this.preferences = {
            morningEmailTime: pref.morning_email_time || '07:00',
            reminderTime: pref.reminder_time || '21:00',
            emailNotifications: pref.email_notifications !== undefined ? pref.email_notifications : true,
            pushNotifications: pref.push_notifications !== undefined ? pref.push_notifications : true,
            weeklyReportEnabled: pref.weekly_report_enabled !== undefined ? pref.weekly_report_enabled : true,
            weeklyReportDay: pref.weekly_report_day || 'sunday',
            timezone: pref.timezone || 'UTC'
          }
          return this.preferences
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to update preferences', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async changePassword(currentPassword, newPassword, confirmPassword) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.post('/settings/change-password', {
          current_password: currentPassword,
          new_password: newPassword,
          new_password_confirmation: confirmPassword
        })
        
        if (response.success) {
          return true
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to change password', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async exportData() {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.post('/settings/export-data')
        
        if (response.success) {
          return response.data
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to request data export', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteAccount(password, confirmation) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.delete('/settings/account', {
          data: {
            password,
            confirmation
          }
        })
        
        if (response.success) {
          return true
        }
      } catch (error) {
        const errorMessage = getErrorMessage(error)
        console.error(formatErrorForLog('Failed to delete account', error))
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

