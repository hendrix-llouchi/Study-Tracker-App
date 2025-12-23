<template>
  <div
    :class="taskClasses"
    class="p-4 rounded-lg border transition-all duration-default"
  >
    <div class="flex items-start gap-3">
      <!-- Checkbox -->
      <div class="flex-shrink-0 pt-1">
        <input
          :id="`plan-${plan.id}`"
          type="checkbox"
          :checked="plan.completed"
          @change="handleToggleComplete"
          class="w-5 h-5 text-primary-green border-border-medium rounded focus:ring-primary-green cursor-pointer"
        />
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2 mb-2">
          <div class="flex-1 min-w-0">
            <label
              :for="`plan-${plan.id}`"
              :class="plan.completed ? 'line-through text-text-tertiary' : 'text-text-primary'"
              class="text-body font-semibold cursor-pointer"
            >
              {{ plan.topic }}
            </label>
            <p class="text-body-small text-text-secondary mt-0.5">
              {{ plan.course }}
            </p>
          </div>
          <Badge :variant="getPriorityVariant(plan.priority)" size="sm">
            {{ formatPriority(plan.priority) }}
          </Badge>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-body-small text-text-secondary mb-3">
          <div class="flex items-center gap-1">
            <Clock :size="14" />
            <span>{{ plan.startTime }}</span>
          </div>
          <div class="flex items-center gap-1">
            <Timer :size="14" />
            <span>{{ formatDuration(plan.duration) }}</span>
          </div>
          <Badge variant="info" size="sm">
            {{ formatStudyType(plan.studyType) }}
          </Badge>
        </div>

        <!-- Actual Time Input (shown when completed) -->
        <div v-if="plan.completed" class="mb-3">
          <label class="block text-body-small text-text-secondary mb-1">
            Actual time spent:
          </label>
          <div class="flex items-center gap-2">
            <input
              v-model.number="actualTime"
              type="number"
              placeholder="Minutes"
              class="w-24 px-2 py-1.5 text-body border border-border-medium rounded-md focus:outline-none focus:ring-2 focus:ring-primary-green focus:border-primary-green"
              @blur="handleTimeUpdate"
            />
            <span class="text-body-small text-text-secondary">minutes</span>
            <span v-if="plan.actualTime" class="text-body-small text-primary-green">
              ({{ formatDuration(plan.actualTime) }})
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
          <Button variant="ghost" size="sm" @click="handleEdit">
            <Edit :size="14" />
          </Button>
          <Button variant="ghost" size="sm" @click="handleDelete">
            <Trash2 :size="14" />
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Badge from '@/Components/Common/Badge.vue'
import Button from '@/Components/Common/Button.vue'
import { Clock, Timer, Edit, Trash2 } from 'lucide-vue-next'

const props = defineProps({
  plan: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['complete', 'update', 'delete'])

const actualTime = ref(props.plan.actualTime || null)

const taskClasses = computed(() => {
  if (props.plan.completed) {
    return 'bg-primary-green-bg border-primary-green-light'
  }
  return 'bg-neutral-white border-border-default hover:border-primary-green-light'
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

const formatStudyType = (type) => {
  const types = {
    'new-material': 'New Material',
    'review': 'Review',
    'practice': 'Practice'
  }
  return types[type] || type
}

const handleToggleComplete = () => {
  emit('complete', props.plan.id, actualTime.value)
}

const handleTimeUpdate = () => {
  if (actualTime.value && props.plan.completed) {
    emit('complete', props.plan.id, actualTime.value)
  }
}

const handleEdit = () => {
  emit('update', props.plan)
}

const handleDelete = () => {
  if (confirm('Are you sure you want to delete this plan?')) {
    emit('delete', props.plan.id)
  }
}
</script>

