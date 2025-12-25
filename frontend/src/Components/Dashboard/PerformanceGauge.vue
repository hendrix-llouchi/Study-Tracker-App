<template>
  <Card padding="default">
    <div class="flex flex-col items-center">
      <h3 class="text-h3 text-text-primary mb-6 w-full">Overall Performance</h3>
      <div class="relative w-full max-w-[200px] h-[200px] mx-auto mb-6 flex items-center justify-center">
        <svg width="200" height="200" class="overflow-visible">
          <!-- Track -->
          <circle
            cx="100"
            cy="100"
            r="80"
            fill="none"
            stroke="#E5E7EB"
            stroke-width="12"
            stroke-linecap="round"
            transform="rotate(-90 100 100)"
          />
          <!-- Progress -->
          <circle
            cx="100"
            cy="100"
            r="80"
            fill="none"
            stroke="#10B981"
            stroke-width="12"
            stroke-linecap="round"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="offset"
            transform="rotate(-90 100 100)"
            class="transition-all duration-slow"
          />
        </svg>
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="text-4xl font-bold text-text-primary">{{ percentage }}%</span>
        </div>
      </div>
      <p class="text-body text-center text-text-secondary">Your academic performance score</p>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'

const props = defineProps({
  percentage: {
    type: Number,
    required: true,
    validator: (value) => value >= 0 && value <= 100
  }
})

const radius = 80
const circumference = 2 * Math.PI * radius
const offset = computed(() => circumference - (props.percentage / 100) * circumference)
</script>

