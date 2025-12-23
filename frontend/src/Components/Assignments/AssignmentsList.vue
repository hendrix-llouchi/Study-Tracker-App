<template>
  <Card padding="default">
    <div class="space-y-4">
      <div v-if="assignments.length === 0" class="text-center py-12">
        <FileText class="w-12 h-12 text-text-tertiary mx-auto mb-4" />
        <p class="text-body text-text-secondary">No assignments found</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="assignment in assignments"
          :key="assignment.id"
          :class="getAssignmentClasses(assignment)"
          class="p-4 rounded-lg border cursor-pointer hover:shadow-md transition-all"
          @click="$emit('edit-assignment', assignment)"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-2">
                <input
                  :id="`assignment-${assignment.id}`"
                  type="checkbox"
                  :checked="assignment.completed"
                  @click.stop="$emit('toggle-complete', assignment.id)"
                  class="w-5 h-5 text-primary-green border-border-medium rounded focus:ring-primary-green cursor-pointer"
                />
                <label
                  :for="`assignment-${assignment.id}`"
                  :class="assignment.completed ? 'line-through text-text-tertiary' : 'text-text-primary'"
                  class="text-body font-semibold cursor-pointer"
                >
                  {{ assignment.title }}
                </label>
                <Badge :variant="getPriorityVariant(assignment.priority)" size="sm">
                  {{ formatPriority(assignment.priority) }}
                </Badge>
                <Badge v-if="isOverdue(assignment)" variant="error" size="sm">
                  Overdue
                </Badge>
              </div>
              <p class="text-body-small text-text-secondary mb-2">
                {{ assignment.course }}
              </p>
              <p v-if="assignment.description" class="text-body-small text-text-secondary mb-3 line-clamp-2">
                {{ assignment.description }}
              </p>
              <div class="flex items-center gap-4 text-body-small text-text-secondary">
                <div class="flex items-center gap-1">
                  <Calendar :size="14" />
                  <span>{{ formatDate(assignment.dueDate) }}</span>
                </div>
                <div v-if="assignment.dueTime" class="flex items-center gap-1">
                  <Clock :size="14" />
                  <span>{{ assignment.dueTime }}</span>
                </div>
              </div>
            </div>
            <Button variant="ghost" size="sm" @click.stop="$emit('delete-assignment', assignment.id)">
              <Trash2 :size="16" />
            </Button>
          </div>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Badge from '@/Components/Common/Badge.vue'
import { FileText, Calendar, Clock, Trash2 } from 'lucide-vue-next'

defineProps({
  assignments: {
    type: Array,
    default: () => []
  }
})

defineEmits(['edit-assignment', 'toggle-complete', 'delete-assignment'])

const getAssignmentClasses = (assignment) => {
  if (assignment.completed) {
    return 'bg-neutral-gray50 border-border-default'
  }
  if (isOverdue(assignment)) {
    return 'bg-red-50 border-accent-red'
  }
  return 'bg-neutral-white border-border-default'
}

const isOverdue = (assignment) => {
  if (assignment.completed) return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return new Date(assignment.dueDate) < today
}

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
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

