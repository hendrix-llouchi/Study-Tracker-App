<template>
  <Card padding="lg">
    <h3 class="text-h3 text-text-primary mb-5">Email Preferences</h3>

    <div class="space-y-6">
      <!-- Morning Email -->
      <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
        <div class="flex-1">
          <p class="text-body font-medium text-text-primary mb-1">Daily Morning Email</p>
          <p class="text-body-small text-text-secondary">
            Receive your daily study plan via email every morning
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

      <div v-if="form.enabled" class="ml-4">
        <Input
          v-model="form.morningEmailTime"
          type="time"
          label="Send Time"
          @update:model-value="handleUpdate"
        />
      </div>

      <!-- Weekly Reports -->
      <div class="flex items-center justify-between p-4 border border-border-default rounded-lg">
        <div>
          <p class="text-body font-medium text-text-primary mb-1">Weekly Progress Reports</p>
          <p class="text-body-small text-text-secondary">
            Receive weekly summaries every Sunday evening
          </p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            v-model="form.weeklyReports"
            type="checkbox"
            class="sr-only peer"
            @change="handleUpdate"
          />
          <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
        </label>
      </div>

      <!-- Email Preview -->
      <div class="pt-4 border-t border-border-default">
        <Button variant="secondary" size="md" @click="showPreview = true">
          Preview Email Template
        </Button>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { ref, watch } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Input from '@/Components/Common/Input.vue'
import Button from '@/Components/Common/Button.vue'

const props = defineProps({
  preferences: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update'])

const form = ref({
  morningEmailTime: props.preferences.morningEmailTime || '07:00',
  enabled: props.preferences.enabled !== false,
  weeklyReports: props.preferences.weeklyReports !== false
})

const showPreview = ref(false)

watch(() => props.preferences, (newVal) => {
  form.value = {
    morningEmailTime: newVal.morningEmailTime || '07:00',
    enabled: newVal.enabled !== false,
    weeklyReports: newVal.weeklyReports !== false
  }
}, { deep: true })

const handleUpdate = () => {
  emit('update', form.value)
}
</script>

