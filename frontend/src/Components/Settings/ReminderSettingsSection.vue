<template>
  <Card padding="lg">
    <h3 class="text-h3 text-text-primary mb-5">Reminder Settings</h3>

    <div class="space-y-6">
      <!-- Evening Reminder -->
      <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
        <div class="flex-1">
          <p class="text-body font-medium text-text-primary mb-1">Evening Planning Reminder</p>
          <p class="text-body-small text-text-secondary">
            Get reminded if you haven't planned your day by evening
          </p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            v-model="form.enabled"
            type="checkbox"
            class="sr-only peer"
            @change="handleUpdate"
          />
          <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
        </label>
      </div>

      <div v-if="form.enabled" class="ml-4 space-y-4">
        <Input
          v-model="form.eveningReminderTime"
          type="time"
          label="Reminder Time"
          @update:model-value="handleUpdate"
        />

        <Select
          v-model="channelsString"
          label="Notification Channels"
          :options="channelOptions"
        />

        <Select
          v-model="form.frequency"
          label="Reminder Frequency"
          :options="frequencyOptions"
          @update:model-value="handleUpdate"
        />
      </div>

      <!-- Do Not Disturb -->
      <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
        <div>
          <p class="text-body font-medium text-text-primary mb-1">Do Not Disturb</p>
          <p class="text-body-small text-text-secondary">
            Disable all reminders when enabled
          </p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            v-model="form.doNotDisturb"
            type="checkbox"
            class="sr-only peer"
            @change="handleUpdate"
          />
          <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
        </label>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Input from '@/Components/Common/Input.vue'
import Select from '@/Components/Common/Select.vue'

const props = defineProps({
  settings: {
    type: Object,
    required: false,
    default: () => ({
      eveningReminderTime: '21:00',
      enabled: true,
      channels: ['email'],
      frequency: 'daily',
      doNotDisturb: false
    })
  }
})

const emit = defineEmits(['update'])

const form = ref({
  eveningReminderTime: props.settings?.eveningReminderTime || '21:00',
  enabled: props.settings?.enabled !== false,
  channels: props.settings?.channels || ['email'],
  frequency: props.settings?.frequency || 'daily',
  doNotDisturb: props.settings?.doNotDisturb || false
})

// Map channel arrays to string identifiers
const channelMap = {
  'email-only': ['email'],
  'push-only': ['push'],
  'both': ['email', 'push']
}

// Convert channels array to string identifier for Select component
const channelsString = computed({
  get: () => {
    const channels = form.value.channels || ['email']
    const sortedChannels = [...channels].sort().join(',')
    if (sortedChannels === 'email') return 'email-only'
    if (sortedChannels === 'push') return 'push-only'
    if (sortedChannels === 'email,push') return 'both'
    return 'email-only' // default fallback
  },
  set: (value) => {
    form.value.channels = channelMap[value] || ['email']
    handleUpdate()
  }
})

const channelOptions = [
  { value: 'email-only', label: 'Email Only' },
  { value: 'push-only', label: 'Push Only' },
  { value: 'both', label: 'Both Email & Push' }
]

const frequencyOptions = [
  { value: 'daily', label: 'Daily' },
  { value: 'weekly', label: 'Weekly' },
  { value: 'custom', label: 'Custom' }
]

watch(() => props.settings, (newVal) => {
  if (newVal) {
    form.value = {
      eveningReminderTime: newVal.eveningReminderTime || '21:00',
      enabled: newVal.enabled !== false,
      channels: newVal.channels || ['email'],
      frequency: newVal.frequency || 'daily',
      doNotDisturb: newVal.doNotDisturb || false
    }
  }
}, { deep: true, immediate: true })

const handleUpdate = () => {
  emit('update', form.value)
}
</script>

