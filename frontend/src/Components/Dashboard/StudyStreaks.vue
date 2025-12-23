<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-4">Study Streak</h3>
    <p class="text-body text-text-secondary mb-5">
      Current streak: <span class="font-semibold text-text-primary">{{ streak?.current || 0 }} days</span>
      • Longest: <span class="font-semibold text-text-primary">{{ streak?.longest || 0 }} days</span>
    </p>
    <div class="flex gap-1 sm:gap-2 justify-between overflow-x-auto pb-2">
      <div
        v-for="day in streak?.days || []"
        :key="day.day"
        :class="dayBadgeClasses(day)"
        class="w-10 h-12 flex flex-col items-center justify-center rounded-lg transition-all duration-default"
      >
        <CheckCircle2
          v-if="day.completed"
          :size="20"
          :class="dayIconClasses(day)"
          class="mb-1"
        />
        <span :class="dayLabelClasses(day)" class="text-caption font-medium">
          {{ day.day }}
        </span>
      </div>
    </div>
  </Card>
</template>

<script setup>
import Card from '@/Components/Common/Card.vue'
import { CheckCircle2 } from 'lucide-vue-next'

const props = defineProps({
  streak: {
    type: Object,
    default: null
  }
})

const dayBadgeClasses = (day) => {
  if (day.active) {
    return 'bg-accent-orange-light border-2 border-accent-orange'
  } else if (day.completed) {
    return 'bg-primary-green-light'
  } else {
    return 'bg-neutral-gray100'
  }
}

const dayIconClasses = (day) => {
  if (day.active) {
    return 'text-accent-orange'
  } else if (day.completed) {
    return 'text-primary-green'
  } else {
    return 'text-neutral-gray300'
  }
}

const dayLabelClasses = (day) => {
  if (day.active) {
    return 'text-accent-orange'
  } else if (day.completed) {
    return 'text-primary-green'
  } else {
    return 'text-text-tertiary'
  }
}
</script>

