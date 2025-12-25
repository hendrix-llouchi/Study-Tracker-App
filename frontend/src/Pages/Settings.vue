<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="max-w-4xl mx-auto space-y-4 lg:space-y-6">
      <div>
        <h1 class="text-h1 text-text-primary mb-2">Settings</h1>
        <p class="text-body text-text-secondary">Manage your account and preferences</p>
      </div>

      <!-- Profile Section -->
      <div id="profile-section">
        <ProfileSection
          :profile="settings.profile"
          :loading="saving"
          @submit="handleUpdateProfile"
          @cancel="handleCancel"
        />
      </div>

      <!-- Email Preferences -->
      <EmailPreferencesSection
        :preferences="emailPreferences"
        @update="handleUpdateEmailPreferences"
      />

      <!-- Reminder Settings -->
      <ReminderSettingsSection
        :settings="reminderSettings"
        @update="handleUpdateReminderSettings"
      />

      <!-- Notification Preferences -->
      <NotificationPreferencesSection
        :notifications="notifications"
        @update="handleUpdateNotifications"
      />

      <!-- Account Settings -->
      <AccountSettingsSection />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { useSettingsStore } from '@/Stores/settings'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import ProfileSection from '@/Components/Settings/ProfileSection.vue'
import EmailPreferencesSection from '@/Components/Settings/EmailPreferencesSection.vue'
import ReminderSettingsSection from '@/Components/Settings/ReminderSettingsSection.vue'
import NotificationPreferencesSection from '@/Components/Settings/NotificationPreferencesSection.vue'
import AccountSettingsSection from '@/Components/Settings/AccountSettingsSection.vue'

const authStore = useAuthStore()
const settingsStore = useSettingsStore()

const user = computed(() => authStore.user)
const settings = computed(() => settingsStore.$state)
const saving = ref(false)

// Map preferences to component-expected structures
const emailPreferences = computed(() => ({
  morningEmailTime: settings.value.preferences?.morningEmailTime || '07:00',
  enabled: settings.value.preferences?.emailNotifications !== false,
  weeklyReports: settings.value.preferences?.weeklyReportEnabled !== false
}))

const reminderSettings = computed(() => ({
  eveningReminderTime: settings.value.preferences?.reminderTime || '21:00',
  enabled: settings.value.preferences?.emailNotifications !== false,
  channels: settings.value.preferences?.emailNotifications ? ['email'] : [],
  frequency: 'daily',
  doNotDisturb: settings.value.preferences?.emailNotifications === false && settings.value.preferences?.pushNotifications === false
}))

const notifications = computed(() => ({
  pushEnabled: settings.value.preferences?.pushNotifications !== false,
  emailEnabled: settings.value.preferences?.emailNotifications !== false,
  categories: {
    assignments: true,
    deadlines: true,
    reminders: true,
    reports: settings.value.preferences?.weeklyReportEnabled !== false
  }
}))

onMounted(async () => {
  await settingsStore.fetchSettings()
})

const handleUpdateProfile = async (profileData) => {
  saving.value = true
  try {
    await settingsStore.updateProfile(profileData)
    // Update auth store user
    authStore.setUser({ ...authStore.user, ...profileData })
  } finally {
    saving.value = false
  }
}

const handleUpdateEmailPreferences = async (preferences) => {
  await settingsStore.updatePreferences({
    morningEmailTime: preferences.morningEmailTime,
    emailNotifications: preferences.enabled,
    weeklyReportEnabled: preferences.weeklyReports
  })
}

const handleUpdateReminderSettings = async (reminderData) => {
  await settingsStore.updatePreferences({
    reminderTime: reminderData.eveningReminderTime,
    emailNotifications: reminderData.enabled && reminderData.channels?.includes('email'),
    pushNotifications: reminderData.enabled && reminderData.channels?.includes('push')
  })
}

const handleUpdateNotifications = async (notificationData) => {
  await settingsStore.updatePreferences({
    emailNotifications: notificationData.emailEnabled,
    pushNotifications: notificationData.pushEnabled,
    weeklyReportEnabled: notificationData.categories?.reports
  })
}

const handleCancel = () => {
  // Reload settings
  settingsStore.fetchSettings()
}
</script>
