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
        :preferences="settings.emailPreferences"
        @update="handleUpdateEmailPreferences"
      />

      <!-- Reminder Settings -->
      <ReminderSettingsSection
        :settings="settings.reminderSettings"
        @update="handleUpdateReminderSettings"
      />

      <!-- Notification Preferences -->
      <NotificationPreferencesSection
        :notifications="settings.notifications"
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
  await settingsStore.updateEmailPreferences(preferences)
}

const handleUpdateReminderSettings = async (settings) => {
  await settingsStore.updateReminderSettings(settings)
}

const handleUpdateNotifications = async (notifications) => {
  await settingsStore.updateNotifications(notifications)
}

const handleCancel = () => {
  // Reload settings
  settingsStore.fetchSettings()
}
</script>
