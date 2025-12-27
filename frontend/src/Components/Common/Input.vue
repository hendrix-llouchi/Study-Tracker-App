<template>
  <div class="w-full">
    <label v-if="label" :for="inputId" class="block text-body-small text-text-secondary mb-1.5">
      {{ label }}
      <span v-if="required" class="text-accent-red">*</span>
    </label>
    <div class="relative">
      <input
        :id="inputId"
        :type="type === 'password' && showPassword ? 'text' : type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="inputClasses"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      <div v-if="$slots.icon" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-neutral-gray400">
        <slot name="icon" />
      </div>
      <button
        v-if="type === 'password'"
        type="button"
        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-neutral-gray400 hover:text-text-secondary focus:outline-none"
        @click="showPassword = !showPassword"
        tabindex="-1"
      >
        <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
      </button>
    </div>
    <p v-if="error" class="mt-1.5 text-body-small text-accent-red">
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1.5 text-body-small text-text-tertiary">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed, ref, useSlots } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  id: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])
const slots = useSlots()

const showPassword = ref(false)

const inputId = computed(() => props.id || `input-${Math.random().toString(36).substr(2, 9)}`)

const inputClasses = computed(() => {
  let classes = 'w-full px-3.5 py-2.5 text-body text-text-primary bg-neutral-white border rounded-md transition-all duration-default placeholder:text-text-tertiary focus:outline-none'
  
  if (props.error) {
    classes += ' border-accent-red focus:ring-3 focus:ring-accent-red/10 focus:border-accent-red'
  } else {
    classes += ' border-border-medium focus:border-border-focus focus:ring-3 focus:ring-border-focus/10'
  }
  
  if (props.disabled) {
    classes += ' bg-neutral-gray50 text-text-tertiary cursor-not-allowed'
  }
  
  if (slots.icon) {
    classes += ' pl-10'
  }
  
  if (props.type === 'password') {
    classes += ' pr-10'
  }
  
  return classes
})
</script>

