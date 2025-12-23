<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">Suggested Study Slots</h3>
      <Button variant="ghost" size="sm" @click="refreshSlots">
        <RefreshCw :size="16" />
      </Button>
    </div>

    <div v-if="slots.length === 0" class="text-center py-8">
      <Clock class="w-12 h-12 text-text-tertiary mx-auto mb-3" />
      <p class="text-body text-text-secondary">No available study slots found</p>
    </div>

    <div v-else class="space-y-3 max-h-96 overflow-y-auto">
      <div
        v-for="(slot, index) in slots"
        :key="index"
        class="p-4 bg-primary-green-bg border border-primary-green-light rounded-lg cursor-pointer hover:bg-primary-green-light transition-colors"
        @click="$emit('select-slot', slot)"
      >
        <div class="flex items-start justify-between mb-2">
          <div>
            <p class="text-body font-semibold text-text-primary">{{ slot.day }}</p>
            <p class="text-body-small text-text-secondary mt-1">
              {{ slot.startTime }} - {{ slot.endTime }}
            </p>
          </div>
          <Badge variant="success" size="sm">
            {{ formatDuration(slot.duration) }}
          </Badge>
        </div>
        <div class="flex items-center gap-2 text-body-small text-primary-green">
          <Clock :size="14" />
          <span>{{ slot.duration }} minutes available</span>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Badge from '@/Components/Common/Badge.vue'
import { Clock, RefreshCw } from 'lucide-vue-next'

const props = defineProps({
  slots: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['select-slot', 'refresh'])

const formatDuration = (minutes) => {
  if (!minutes) return '0m'
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  if (hours > 0 && mins > 0) {
    return `${hours}h ${mins}m`
  } else if (hours > 0) {
    return `${hours}h`
  }
  return `${mins}m`
}

const refreshSlots = () => {
  emit('refresh')
}
</script>

