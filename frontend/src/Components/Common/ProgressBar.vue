<template>
  <div class="w-full h-2 bg-neutral-gray200 rounded-full overflow-hidden">
    <div
      :class="progressClasses"
      :style="{ width: `${percentage}%` }"
      class="h-full rounded-full transition-all duration-slow"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  percentage: {
    type: Number,
    required: true,
    validator: (value) => value >= 0 && value <= 100
  },
  color: {
    type: String,
    default: 'green',
    validator: (value) => ['green', 'blue', 'orange', 'purple'].includes(value)
  }
})

const progressClasses = computed(() => {
  const colors = {
    green: 'bg-primary-green',
    blue: 'bg-secondary-blue',
    orange: 'bg-accent-orange',
    purple: 'bg-accent-purple'
  }
  
  return colors[props.color]
})
</script>

