<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">Upcoming Deadlines</h3>

    <div v-if="upcoming.length === 0" class="text-center py-8">
      <CheckCircle2 class="w-12 h-12 text-primary-green mx-auto mb-3" />
      <p class="text-body text-text-secondary">No upcoming deadlines</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="assignment in upcoming.slice(0, 5)"
        :key="assignment.id"
        class="p-3 bg-neutral-gray50 rounded-lg cursor-pointer hover:bg-neutral-gray100 transition-colors"
        @click="$emit('select-assignment', assignment)"
      >
        <div class="flex items-start justify-between mb-2">
          <p class="text-body font-medium text-text-primary flex-1">
            {{ assignment.title }}
          </p>
          <Badge :variant="getPriorityVariant(assignment.priority)" size="sm">
            {{ formatPriority(assignment.priority) }}
          </Badge>
        </div>
        <p class="text-body-small text-text-secondary mb-2">
          {{ assignment.course }}
        </p>
        <div class="flex items-center gap-2 text-body-small" :class="getDateColor(assignment.dueDate)">
          <Calendar :size="14" />
          <span>{{ formatDate(assignment.dueDate) }}</span>
          <span v-if="assignment.dueTime">• {{ assignment.dueTime }}</span>
        </div>
      </div>
    </div>

    <div v-if="overdue.length > 0" class="mt-6 pt-6 border-t border-border-default">
      <h4 class="text-body font-semibold text-accent-red mb-3">Overdue</h4>
      <div class="space-y-2">
        <div
          v-for="assignment in overdue.slice(0, 3)"
          :key="assignment.id"
          class="p-2 bg-red-50 border border-accent-red rounded cursor-pointer hover:bg-red-100 transition-colors"
          @click="$emit('select-assignment', assignment)"
        >
          <p class="text-body-small font-medium text-accent-red mb-1">
            {{ assignment.title }}
          </p>
          <p class="text-body-small text-text-secondary">
            Due: {{ formatDate(assignment.dueDate) }}
          </p>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Badge from '@/Components/Common/Badge.vue'
import { CheckCircle2, Calendar } from 'lucide-vue-next'

const props = defineProps({
  assignments: {
    type: Array,
    default: () => []
  }
})

defineEmits(['select-assignment'])

const upcoming = computed(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return props.assignments
    .filter(a => !a.completed && new Date(a.dueDate) >= today)
    .sort((a, b) => new Date(a.dueDate) - new Date(b.dueDate))
})

const overdue = computed(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return props.assignments
    .filter(a => !a.completed && new Date(a.dueDate) < today)
    .sort((a, b) => new Date(a.dueDate) - new Date(b.dueDate))
})

const getPriorityVariant = (priority) => {
  const variants = {
    high: 'error',
    medium: 'warning',
    low: 'info'
  }
  return variants[priority] || 'info'
}

const formatPriority = (priority) => {
  return priority ? priority.charAt(0).toUpperCase() + priority.slice(1) : ''
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dateOnly = new Date(date)
  dateOnly.setHours(0, 0, 0, 0)
  
  if (dateOnly.getTime() === today.getTime()) {
    return 'Today'
  }
  
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  if (dateOnly.getTime() === tomorrow.getTime()) {
    return 'Tomorrow'
  }
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const getDateColor = (dateString) => {
  const date = new Date(dateString)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dateOnly = new Date(date)
  dateOnly.setHours(0, 0, 0, 0)
  
  const diffDays = Math.floor((dateOnly - today) / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) return 'text-accent-red'
  if (diffDays <= 3) return 'text-accent-orange'
  return 'text-text-secondary'
}
</script>

