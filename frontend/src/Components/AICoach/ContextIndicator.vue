<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-4">AI Context</h3>
    <p class="text-body-small text-text-secondary mb-4">
      The AI coach has access to:
    </p>
    <div class="space-y-2">
      <div class="flex items-center gap-2 p-2 bg-neutral-gray50 rounded">
        <CheckCircle2 v-if="context.hasResults" class="w-4 h-4 text-primary-green" />
        <X v-else class="w-4 h-4 text-text-tertiary" />
        <span class="text-body-small text-text-primary">Academic Results</span>
      </div>
      <div class="flex items-center gap-2 p-2 bg-neutral-gray50 rounded">
        <CheckCircle2 v-if="context.hasPlans" class="w-4 h-4 text-primary-green" />
        <X v-else class="w-4 h-4 text-text-tertiary" />
        <span class="text-body-small text-text-primary">Study Plans</span>
      </div>
      <div class="flex items-center gap-2 p-2 bg-neutral-gray50 rounded">
        <CheckCircle2 v-if="context.hasTimetable" class="w-4 h-4 text-primary-green" />
        <X v-else class="w-4 h-4 text-text-tertiary" />
        <span class="text-body-small text-text-primary">Timetable</span>
      </div>
    </div>
    <p class="text-body-small text-text-tertiary mt-4">
      This allows the AI to provide personalized recommendations based on your data.
    </p>
  </Card>
</template>

<script setup>
import Card from '@/Components/Common/Card.vue'
import { CheckCircle2, X } from 'lucide-vue-next'

defineProps({
  context: {
    type: Object,
    required: true,
    default: () => ({
      hasResults: false,
      hasPlans: false,
      hasTimetable: false
    })
  }
})
</script>

