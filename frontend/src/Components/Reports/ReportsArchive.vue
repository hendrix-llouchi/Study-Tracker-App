<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">Weekly Reports Archive</h3>

    <div v-if="reports.length === 0" class="text-center py-12">
      <FileText class="w-12 h-12 text-text-tertiary mx-auto mb-4" />
      <p class="text-body text-text-secondary">No reports available yet</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="report in reports"
        :key="report.id"
        class="p-4 border border-border-default rounded-lg hover:border-primary-green hover:shadow-md transition-all cursor-pointer"
        @click="$emit('view-report', report)"
      >
        <div class="flex items-start justify-between mb-3">
          <div>
            <p class="text-body font-semibold text-text-primary mb-1">
              Week {{ report.weekNumber }} - {{ formatDate(report.week) }}
            </p>
            <p class="text-body-small text-text-secondary">
              {{ formatWeekRange(report.week) }}
            </p>
          </div>
          <Badge :variant="getTrendVariant(report.performanceTrend)" size="sm">
            {{ formatTrend(report.performanceTrend) }}
          </Badge>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div>
            <p class="text-body-small text-text-secondary mb-1">Study Hours</p>
            <p class="text-body font-semibold text-text-primary">
              {{ report.totalStudyHours.actual }}h / {{ report.totalStudyHours.planned }}h
            </p>
          </div>
          <div>
            <p class="text-body-small text-text-secondary mb-1">Completion Rate</p>
            <p class="text-body font-semibold text-text-primary">{{ report.completionRate }}%</p>
          </div>
          <div>
            <p class="text-body-small text-text-secondary mb-1">Most Studied</p>
            <p class="text-body font-semibold text-text-primary truncate">{{ report.mostStudiedSubject }}</p>
          </div>
          <div>
            <p class="text-body-small text-text-secondary mb-1">Least Studied</p>
            <p class="text-body font-semibold text-text-primary truncate">{{ report.leastStudiedSubject }}</p>
          </div>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import Card from '@/Components/Common/Card.vue'
import Badge from '@/Components/Common/Badge.vue'
import { FileText } from 'lucide-vue-next'

defineProps({
  reports: {
    type: Array,
    default: () => []
  }
})

defineEmits(['view-report'])

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatWeekRange = (dateString) => {
  const date = new Date(dateString)
  const start = new Date(date)
  const day = start.getDay()
  const diff = start.getDate() - day
  start.setDate(diff)
  
  const end = new Date(start)
  end.setDate(end.getDate() + 6)
  
  return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`
}

const getTrendVariant = (trend) => {
  const variants = {
    improving: 'success',
    declining: 'error',
    stable: 'info'
  }
  return variants[trend] || 'info'
}

const formatTrend = (trend) => {
  return trend ? trend.charAt(0).toUpperCase() + trend.slice(1) : ''
}
</script>

