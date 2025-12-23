<template>
  <span :class="badgeClasses">
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'neutral',
    validator: (value) => ['success', 'warning', 'error', 'info', 'neutral'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const badgeClasses = computed(() => {
  const variants = {
    success: 'bg-primary-green-light text-text-success',
    warning: 'bg-accent-orange-light text-accent-orange',
    error: 'bg-red-100 text-red-800',
    info: 'bg-secondary-blue-light text-blue-800',
    neutral: 'bg-neutral-gray100 text-neutral-gray600'
  }
  
  const sizes = {
    sm: 'px-2 py-0.5 text-caption',
    md: 'px-3 py-1 text-body-small font-medium',
    lg: 'px-4 py-1.5 text-body font-medium'
  }
  
  return `${variants[props.variant]} ${sizes[props.size]} rounded-full inline-flex items-center`
})
</script>

