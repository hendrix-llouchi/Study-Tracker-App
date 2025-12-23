<template>
  <div class="w-full">
    <label v-if="label" :for="selectId" class="block text-body-small text-text-secondary mb-1.5">
      {{ label }}
      <span v-if="required" class="text-accent-red">*</span>
    </label>
    <select
      :id="selectId"
      :value="modelValue"
      :disabled="disabled"
      :required="required"
      :class="selectClasses"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="mt-1.5 text-body-small text-accent-red">
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1.5 text-body-small text-text-tertiary">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  options: {
    type: Array,
    required: true
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
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue'])

const selectId = computed(() => props.id || `select-${Math.random().toString(36).substr(2, 9)}`)

const selectClasses = computed(() => {
  const sizes = {
    sm: 'px-2.5 py-1.5 text-sm',
    md: 'px-3.5 py-2.5 text-body',
    lg: 'px-4 py-3 text-base'
  }
  
  let classes = `w-full ${sizes[props.size]} text-text-primary bg-neutral-white border rounded-md transition-all duration-default focus:outline-none cursor-pointer`
  
  if (props.error) {
    classes += ' border-accent-red focus:ring-3 focus:ring-accent-red/10 focus:border-accent-red'
  } else {
    classes += ' border-border-medium focus:border-border-focus focus:ring-3 focus:ring-border-focus/10'
  }
  
  if (props.disabled) {
    classes += ' bg-neutral-gray50 text-text-tertiary cursor-not-allowed'
  }
  
  return classes
})
</script>

