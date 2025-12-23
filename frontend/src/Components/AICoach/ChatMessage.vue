<template>
  <div :class="messageClasses">
    <div :class="bubbleClasses" class="max-w-[80%] sm:max-w-[70%] p-3 rounded-lg">
      <p class="text-body whitespace-pre-wrap">{{ message.content }}</p>
      <p class="text-body-small opacity-75 mt-1">
        {{ formatTime(message.timestamp) }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  message: {
    type: Object,
    required: true
  }
})

const messageClasses = computed(() => {
  return props.message.role === 'user'
    ? 'flex justify-end'
    : 'flex justify-start'
})

const bubbleClasses = computed(() => {
  return props.message.role === 'user'
    ? 'bg-primary-green text-white'
    : 'bg-neutral-gray100 text-text-primary'
})

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}
</script>

