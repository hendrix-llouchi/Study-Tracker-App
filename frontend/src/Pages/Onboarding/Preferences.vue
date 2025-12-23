<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="max-w-2xl mx-auto space-y-6">
      <!-- Progress Indicator -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">
            <Check :size="16" />
          </div>
          <span class="text-body-small text-text-secondary">Profile</span>
        </div>
        <div class="flex-1 h-0.5 bg-primary-green mx-4"></div>
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">
            <Check :size="16" />
          </div>
          <span class="text-body-small text-text-secondary">Timetable</span>
        </div>
        <div class="flex-1 h-0.5 bg-primary-green mx-4"></div>
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">
            <Check :size="16" />
          </div>
          <span class="text-body-small text-text-secondary">Results</span>
        </div>
        <div class="flex-1 h-0.5 bg-primary-green mx-4"></div>
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">4</div>
          <span class="text-body-small text-text-primary font-medium">Preferences</span>
        </div>
      </div>

      <div>
        <h1 class="text-h1 text-text-primary mb-2">Set Your Preferences</h1>
        <p class="text-body text-text-secondary">Configure notifications and reminders to stay on track</p>
      </div>

      <Card padding="lg">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-body-small text-accent-red">{{ error }}</p>
          </div>

          <!-- Email Notifications -->
          <div class="space-y-4">
            <h3 class="text-h3 text-text-primary">Email Notifications</h3>
            
            <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
              <div>
                <p class="text-body font-medium text-text-primary">Morning Email Summary</p>
                <p class="text-body-small text-text-secondary">Receive your daily study plan via email</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  v-model="form.emailNotifications"
                  type="checkbox"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
              </label>
            </div>

            <div v-if="form.emailNotifications" class="ml-4">
              <Input
                v-model="form.morningEmailTime"
                type="time"
                label="Email Send Time"
                required
              />
            </div>
          </div>

          <!-- Reminders -->
          <div class="space-y-4">
            <h3 class="text-h3 text-text-primary">Reminders</h3>
            
            <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
              <div>
                <p class="text-body font-medium text-text-primary">Evening Reminder</p>
                <p class="text-body-small text-text-secondary">Get reminded to plan your next day</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  v-model="form.eveningReminder"
                  type="checkbox"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
              </label>
            </div>

            <div v-if="form.eveningReminder" class="ml-4">
              <Input
                v-model="form.reminderTime"
                type="time"
                label="Reminder Time"
                required
              />
            </div>
          </div>

          <!-- Weekly Reports -->
          <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
            <div>
              <p class="text-body font-medium text-text-primary">Weekly Progress Reports</p>
              <p class="text-body-small text-text-secondary">Receive weekly summaries every Sunday</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="form.weeklyReports"
                type="checkbox"
                class="sr-only peer"
              />
              <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
            </label>
          </div>

          <div class="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4 pt-4">
            <Button
              variant="ghost"
              size="lg"
              @click="router.push('/onboarding/results')"
              class="w-full sm:w-auto"
            >
              Back
            </Button>
            <Button
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full sm:w-auto sm:ml-auto"
            >
              Complete Setup
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { computed } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'
import { Check } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const user = computed(() => authStore.user)

const form = ref({
  emailNotifications: true,
  morningEmailTime: '07:00',
  eveningReminder: true,
  reminderTime: '21:00',
  weeklyReports: true
})

const error = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  error.value = ''
  loading.value = true

  try {
    // Bypass API call for now
    console.log('Preferences saved:', form.value)
    router.push('/dashboard')
  } catch (err) {
    error.value = err.message || 'Failed to save preferences. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
