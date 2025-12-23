<template>
  <div class="w-full">
    <label v-if="label" :for="inputId" class="block text-body-small text-text-secondary mb-1.5">
      {{ label }}
      <span v-if="required" class="text-accent-red">*</span>
    </label>
    <div class="relative">
      <input
        :id="inputId"
        :type="type"
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
import { computed, useSlots } from 'vue'

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
  
  return classes
})
</script>

