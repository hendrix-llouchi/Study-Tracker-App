<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">Plan Statistics</h3>
    
    <div class="space-y-4">
      <div class="flex items-center justify-between p-4 bg-neutral-gray50 rounded-lg">
        <div>
          <p class="text-body-small text-text-secondary mb-1">Completion Rate</p>
          <p class="text-h2 text-text-primary">{{ statistics.completionRate }}%</p>
        </div>
        <div class="w-16 h-16">
          <ProgressRing :percentage="statistics.completionRate" :size="64" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="p-4 bg-neutral-gray50 rounded-lg">
          <p class="text-body-small text-text-secondary mb-1">Planned Hours</p>
          <p class="text-h3 text-text-primary">{{ statistics.plannedHours }}h</p>
        </div>
        <div class="p-4 bg-neutral-gray50 rounded-lg">
          <p class="text-body-small text-text-secondary mb-1">Actual Hours</p>
          <p class="text-h3 text-text-primary">{{ statistics.actualHours }}h</p>
        </div>
      </div>

      <div v-if="statistics.plannedHours > 0" class="pt-4 border-t border-border-default">
        <div class="flex items-center justify-between mb-2">
          <span class="text-body-small text-text-secondary">Time Efficiency</span>
          <span class="text-body-small font-semibold" :class="efficiencyColor">
            {{ efficiency }}%
          </span>
        </div>
        <div class="w-full bg-neutral-gray200 rounded-full h-2">
          <div
            class="h-2 rounded-full transition-all"
            :class="efficiencyBarColor"
            :style="{ width: `${Math.min(efficiency, 100)}%` }"
          />
        </div>
        <p class="text-body-small text-text-tertiary mt-2">
          {{ efficiencyText }}
        </p>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import ProgressRing from '@/Components/Common/ProgressRing.vue'

const props = defineProps({
  statistics: {
    type: Object,
    required: true,
    default: () => ({
      completionRate: 0,
      plannedHours: 0,
      actualHours: 0
    })
  }
})

const efficiency = computed(() => {
  if (props.statistics.plannedHours === 0) return 0
  return Math.round((props.statistics.actualHours / props.statistics.plannedHours) * 100)
})

const efficiencyColor = computed(() => {
  if (efficiency.value >= 90) return 'text-primary-green'
  if (efficiency.value >= 70) return 'text-secondary-blue'
  if (efficiency.value >= 50) return 'text-accent-orange'
  return 'text-accent-red'
})

const efficiencyBarColor = computed(() => {
  if (efficiency.value >= 90) return 'bg-primary-green'
  if (efficiency.value >= 70) return 'bg-secondary-blue'
  if (efficiency.value >= 50) return 'bg-accent-orange'
  return 'bg-accent-red'
})

const efficiencyText = computed(() => {
  if (efficiency.value >= 90) return 'Excellent time management!'
  if (efficiency.value >= 70) return 'Good progress, keep it up!'
  if (efficiency.value >= 50) return 'Room for improvement in time planning'
  return 'Consider adjusting your planned study times'
})
</script>

