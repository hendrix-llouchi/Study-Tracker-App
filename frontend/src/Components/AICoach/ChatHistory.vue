<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-h3 text-text-primary">Chat History</h3>
      <Button variant="ghost" size="sm" @click="$emit('new-chat')">
        <Plus :size="16" />
      </Button>
    </div>

    <div v-if="history.length === 0" class="text-center py-8">
      <MessageSquare class="w-12 h-12 text-text-tertiary mx-auto mb-3" />
      <p class="text-body-small text-text-secondary">No chat history</p>
    </div>

    <div v-else class="space-y-2">
      <div
        v-for="chat in history"
        :key="chat.id"
        :class="chat.id === selectedChatId ? 'bg-primary-green-bg border-primary-green' : 'border-border-default'"
        class="p-3 border rounded-lg cursor-pointer hover:bg-neutral-gray50 transition-colors"
        @click="$emit('select-chat', chat.id)"
      >
        <p class="text-body font-medium text-text-primary mb-1 truncate">
          {{ chat.title }}
        </p>
        <p class="text-body-small text-text-secondary truncate mb-2">
          {{ chat.lastMessage }}
        </p>
        <p class="text-body-small text-text-tertiary">
          {{ formatDate(chat.timestamp) }}
        </p>
      </div>
    </div>
  </Card>
</template>

<script setup>
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import { Plus, MessageSquare } from 'lucide-vue-next'

defineProps({
  history: {
    type: Array,
    default: () => []
  },
  selectedChatId: {
    type: String,
    default: null
  }
})

defineEmits(['new-chat', 'select-chat'])

const formatDate = (timestamp) => {
  const date = new Date(timestamp)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dateOnly = new Date(date)
  dateOnly.setHours(0, 0, 0, 0)
  
  if (dateOnly.getTime() === today.getTime()) {
    return 'Today'
  }
  
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  if (dateOnly.getTime() === yesterday.getTime()) {
    return 'Yesterday'
  }
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}
</script>

