<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h3 class="text-h3 text-text-primary">{{ formattedDate }}</h3>
        <p class="text-body-small text-text-secondary mt-1">
          {{ plans.length }} {{ plans.length === 1 ? 'task' : 'tasks' }} planned
        </p>
      </div>
      <Button variant="primary" size="md" @click="$emit('add-plan')">
        <Plus :size="16" class="mr-2" />
        Add Plan
      </Button>
    </div>

    <!-- Progress Indicator -->
    <div v-if="plans.length > 0" class="mb-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-body-small text-text-secondary">Daily Progress</span>
        <span class="text-body-small font-semibold text-text-primary">{{ completionRate }}%</span>
      </div>
      <div class="w-full bg-neutral-gray200 rounded-full h-2">
        <div
          class="bg-primary-green h-2 rounded-full transition-all"
          :style="{ width: `${completionRate}%` }"
        />
      </div>
      <div class="flex items-center justify-between mt-2 text-body-small text-text-secondary">
        <span>{{ completedPlans.length }} completed</span>
        <span>{{ plannedHours }}h planned • {{ actualHours }}h actual</span>
      </div>
    </div>

    <!-- Plans List -->
    <div v-if="plans.length === 0" class="text-center py-12">
      <Calendar class="w-12 h-12 text-text-tertiary mx-auto mb-4" />
      <p class="text-body text-text-secondary mb-2">No study plans for this day</p>
      <p class="text-body-small text-text-tertiary">Create your first study plan to get started</p>
    </div>

    <div v-else class="space-y-3">
      <PlanTaskItem
        v-for="plan in sortedPlans"
        :key="plan.id"
        :plan="plan"
        @complete="handleComplete"
        @update="handleUpdate"
        @delete="handleDelete"
      />
    </div>

    <!-- Carry Over Option -->
    <div
      v-if="incompletePlans.length > 0 && isPastDate"
      class="mt-6 pt-6 border-t border-border-default"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-body font-medium text-text-primary mb-1">
            {{ incompletePlans.length }} incomplete task{{ incompletePlans.length === 1 ? '' : 's' }}
          </p>
          <p class="text-body-small text-text-secondary">
            Carry over to today?
          </p>
        </div>
        <Button variant="secondary" size="md" @click="$emit('carry-over')">
          Carry Over
        </Button>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import PlanTaskItem from './PlanTaskItem.vue'
import { Plus, Calendar } from 'lucide-vue-next'

const props = defineProps({
  plans: {
    type: Array,
    default: () => []
  },
  date: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['add-plan', 'complete', 'update', 'delete', 'carry-over'])

const formattedDate = computed(() => {
  const date = new Date(props.date)
  return date.toLocaleDateString('en-US', { 
    weekday: 'long', 
    month: 'long', 
    day: 'numeric',
    year: 'numeric'
  })
})

const isPastDate = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return props.date < today
})

const completedPlans = computed(() => {
  return props.plans.filter(p => p.completed)
})

const incompletePlans = computed(() => {
  return props.plans.filter(p => !p.completed)
})

const completionRate = computed(() => {
  if (props.plans.length === 0) return 0
  return Math.round((completedPlans.value.length / props.plans.length) * 100)
})

const plannedHours = computed(() => {
  const total = props.plans.reduce((sum, p) => sum + (p.duration || 0), 0)
  return (total / 60).toFixed(1)
})

const actualHours = computed(() => {
  const total = completedPlans.value
    .filter(p => p.actualTime)
    .reduce((sum, p) => sum + (p.actualTime || 0), 0)
  return (total / 60).toFixed(1)
})

const sortedPlans = computed(() => {
  return [...props.plans].sort((a, b) => {
    // Sort by start time
    if (a.startTime && b.startTime) {
      return a.startTime.localeCompare(b.startTime)
    }
    return 0
  })
})

const handleComplete = (planId, actualTime) => {
  emit('complete', planId, actualTime)
}

const handleUpdate = (plan) => {
  emit('update', plan)
}

const handleDelete = (planId) => {
  emit('delete', planId)
}
</script>

