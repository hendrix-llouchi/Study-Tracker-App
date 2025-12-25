<template>
  <Card padding="lg">
    <h3 class="text-h3 text-text-primary mb-5">Notification Preferences</h3>

    <div class="space-y-6">
      <!-- Push Notifications -->
      <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
        <div>
          <p class="text-body font-medium text-text-primary mb-1">Push Notifications</p>
          <p class="text-body-small text-text-secondary">
            Receive push notifications on your device
          </p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            v-model="form.pushEnabled"
            type="checkbox"
            class="sr-only peer"
            @change="handleUpdate"
          />
          <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
        </label>
      </div>

      <!-- Email Notifications -->
      <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
        <div>
          <p class="text-body font-medium text-text-primary mb-1">Email Notifications</p>
          <p class="text-body-small text-text-secondary">
            Receive email notifications
          </p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            v-model="form.emailEnabled"
            type="checkbox"
            class="sr-only peer"
            @change="handleUpdate"
          />
          <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
        </label>
      </div>

      <!-- Notification Categories -->
      <div class="pt-4 border-t border-border-default">
        <p class="text-body font-semibold text-text-primary mb-4">Notification Categories</p>
        <div class="space-y-3">
          <div
            v-for="(enabled, category) in form.categories"
            :key="category"
            class="flex items-center justify-between p-3 bg-neutral-gray50 rounded-lg"
          >
            <div>
              <p class="text-body font-medium text-text-primary">{{ formatCategory(category) }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="form.categories[category]"
                type="checkbox"
                class="sr-only peer"
                @change="handleUpdate"
              />
              <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
            </label>
          </div>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { ref, watch } from 'vue'
import Card from '@/Components/Common/Card.vue'

const props = defineProps({
  notifications: {
    type: Object,
    required: false,
    default: () => ({
      pushEnabled: true,
      emailEnabled: true,
      categories: {
        assignments: true,
        deadlines: true,
        reminders: true,
        reports: true
      }
    })
  }
})

const emit = defineEmits(['update'])

const form = ref({
  pushEnabled: props.notifications?.pushEnabled !== false,
  emailEnabled: props.notifications?.emailEnabled !== false,
  categories: {
    assignments: props.notifications?.categories?.assignments !== false,
    deadlines: props.notifications?.categories?.deadlines !== false,
    reminders: props.notifications?.categories?.reminders !== false,
    reports: props.notifications?.categories?.reports !== false
  }
})

watch(() => props.notifications, (newVal) => {
  if (newVal) {
    form.value = {
      pushEnabled: newVal.pushEnabled !== false,
      emailEnabled: newVal.emailEnabled !== false,
      categories: {
        assignments: newVal.categories?.assignments !== false,
        deadlines: newVal.categories?.deadlines !== false,
        reminders: newVal.categories?.reminders !== false,
        reports: newVal.categories?.reports !== false
      }
    }
  }
}, { deep: true, immediate: true })

const formatCategory = (category) => {
  const categories = {
    assignments: 'Assignment Notifications',
    deadlines: 'Deadline Reminders',
    reminders: 'Planning Reminders',
    reports: 'Weekly Reports'
  }
  return categories[category] || category
}

const handleUpdate = () => {
  emit('update', form.value)
}
</script>

