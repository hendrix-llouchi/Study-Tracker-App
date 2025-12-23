<template>
  <div class="flex flex-col h-full">
    <!-- Messages -->
    <div class="flex-1 overflow-y-auto p-4 space-y-4 mb-4">
      <div
        v-for="message in messages"
        :key="message.id"
        :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'"
      >
        <div
          :class="message.role === 'user' ? 'bg-primary-green text-white' : 'bg-neutral-gray100 text-text-primary'"
          class="max-w-[80%] sm:max-w-[70%] p-3 rounded-lg"
        >
          <p class="text-body whitespace-pre-wrap">{{ message.content }}</p>
          <p class="text-body-small opacity-75 mt-1">
            {{ formatTime(message.timestamp) }}
          </p>
        </div>
      </div>

      <div v-if="isLoading" class="flex justify-start">
        <div class="bg-neutral-gray100 text-text-primary max-w-[80%] sm:max-w-[70%] p-3 rounded-lg">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-text-secondary rounded-full animate-bounce"></div>
            <div class="w-2 h-2 bg-text-secondary rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="w-2 h-2 bg-text-secondary rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Input Area -->
    <div class="border-t border-border-default p-4">
      <form @submit.prevent="handleSend" class="flex gap-2">
        <input
          v-model="inputMessage"
          type="text"
          placeholder="Ask me about your performance, study strategies..."
          class="flex-1 px-4 py-2 border border-border-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-primary-green text-body"
          :disabled="isLoading"
        />
        <Button
          type="submit"
          variant="primary"
          size="md"
          :disabled="!inputMessage.trim() || isLoading"
        >
          <Send :size="16" />
        </Button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Button from '@/Components/Common/Button.vue'
import { Send } from 'lucide-vue-next'

const props = defineProps({
  messages: {
    type: Array,
    default: () => []
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['send-message'])

const inputMessage = ref('')

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

const handleSend = () => {
  if (inputMessage.value.trim() && !props.isLoading) {
    emit('send-message', inputMessage.value.trim())
    inputMessage.value = ''
  }
}
</script>

