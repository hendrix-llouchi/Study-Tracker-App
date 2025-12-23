<template>
  <div :class="cardClasses">
    <div v-if="$slots.header" class="px-6 py-4 border-b border-border-default">
      <slot name="header" />
    </div>
    <div :class="bodyClasses">
      <slot />
    </div>
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-border-default">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  padding: {
    type: String,
    default: 'default',
    validator: (value) => ['none', 'sm', 'default', 'lg'].includes(value)
  },
  hover: {
    type: Boolean,
    default: false
  }
})

const cardClasses = computed(() => {
  let classes = 'bg-neutral-white border border-border-default rounded-2xl transition-all duration-default'
  
  if (props.hover) {
    classes += ' cursor-pointer hover:transform hover:-translate-y-0.5 hover:shadow-lg hover:border-neutral-gray300'
  }
  
  return classes
})

const bodyClasses = computed(() => {
  const paddingMap = {
    none: 'p-0',
    sm: 'p-4',
    default: 'p-6',
    lg: 'p-8'
  }
  
  return paddingMap[props.padding]
})
</script>

