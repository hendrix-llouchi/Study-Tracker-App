<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="$emit('click', $event)"
  >
    <span v-if="loading" class="mr-2">
      <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'ghost', 'icon'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'button'
  }
})

const emit = defineEmits(['click'])

const buttonClasses = computed(() => {
  const base = 'inline-flex items-center justify-center font-button transition-all duration-default rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2'
  
  const variants = {
    primary: {
      base: 'bg-primary-green text-text-on-primary hover:bg-primary-green-hover focus:ring-primary-green',
      disabled: 'bg-neutral-gray300 text-neutral-gray400 cursor-not-allowed',
      hover: 'hover:transform hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary-green/30'
    },
    secondary: {
      base: 'bg-neutral-white text-primary-green border border-primary-green-light hover:bg-primary-green-bg hover:border-primary-green focus:ring-primary-green',
      disabled: 'bg-neutral-gray100 text-neutral-gray400 border-neutral-gray300 cursor-not-allowed'
    },
    ghost: {
      base: 'bg-transparent text-neutral-gray500 hover:bg-neutral-gray100 hover:text-text-primary focus:ring-neutral-gray300',
      disabled: 'text-neutral-gray400 cursor-not-allowed'
    },
    icon: {
      base: 'w-9 h-9 rounded-md bg-neutral-gray50 text-neutral-gray500 hover:bg-neutral-gray100 hover:text-text-primary focus:ring-neutral-gray300',
      disabled: 'text-neutral-gray400 cursor-not-allowed'
    }
  }
  
  const sizes = {
    sm: 'px-3 py-2 sm:py-1.5 text-sm min-h-[36px] sm:min-h-[32px]',
    md: 'px-5 py-3 sm:py-2.5 text-button min-h-[44px]',
    lg: 'px-6 py-3.5 sm:py-3 text-base min-h-[48px]'
  }
  
  const variant = variants[props.variant]
  const isDisabled = props.disabled || props.loading
  
  let classes = `${base} ${sizes[props.size]}`
  
  if (isDisabled) {
    classes += ` ${variant.disabled}`
  } else {
    classes += ` ${variant.base}`
    if (props.variant === 'primary') {
      classes += ` ${variant.hover}`
    }
  }
  
  return classes
})
</script>

