<template>
  <Card padding="default" class="hover:transform hover:-translate-y-0.5 hover:shadow-lg transition-all duration-default cursor-pointer">
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <p class="text-body-small text-text-secondary mb-1">{{ label }}</p>
        <p class="text-2xl font-bold text-text-primary mb-2">{{ value }}</p>
        <ProgressRing
          v-if="progress !== null"
          :percentage="progress"
          :size="40"
          :color="color"
        />
      </div>
      <div
        :class="iconContainerClasses"
        class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
      >
        <component :is="icon" :size="24" :class="iconClasses" />
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import ProgressRing from '@/Components/Common/ProgressRing.vue'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: [Object, Function],
    required: true
  },
  color: {
    type: String,
    default: 'green',
    validator: (value) => ['green', 'blue', 'orange', 'purple'].includes(value)
  },
  progress: {
    type: Number,
    default: null
  }
})

const iconContainerClasses = computed(() => {
  const colors = {
    green: 'bg-primary-green-light',
    blue: 'bg-secondary-blue-light',
    orange: 'bg-accent-orange-light',
    purple: 'bg-accent-purple-light'
  }
  return colors[props.color]
})

const iconClasses = computed(() => {
  const colors = {
    green: 'text-primary-green',
    blue: 'text-secondary-blue',
    orange: 'text-accent-orange',
    purple: 'text-accent-purple'
  }
  return colors[props.color]
})
</script>

