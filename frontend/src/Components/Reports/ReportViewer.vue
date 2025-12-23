<template>
  <Card padding="lg" v-if="report">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-h1 text-text-primary mb-1">
          Week {{ report.weekNumber }} Report
        </h2>
        <p class="text-body text-text-secondary">
          {{ formatWeekRange(report.week) }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="secondary" size="md" @click="$emit('share')">
          <Share2 :size="16" class="mr-2" />
          Share
        </Button>
        <Button variant="secondary" size="md" @click="$emit('download')">
          <Download :size="16" class="mr-2" />
          Download PDF
        </Button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="p-4 bg-primary-green-bg border border-primary-green-light rounded-lg">
        <p class="text-body-small text-text-secondary mb-1">Total Study Hours</p>
        <p class="text-h2 text-text-primary">
          {{ report.totalStudyHours.actual }}h
        </p>
        <p class="text-body-small text-text-secondary mt-1">
          of {{ report.totalStudyHours.planned }}h planned
        </p>
      </div>
      <div class="p-4 bg-secondary-blue-light border border-secondary-blue rounded-lg">
        <p class="text-body-small text-text-secondary mb-1">Completion Rate</p>
        <p class="text-h2 text-text-primary">{{ report.completionRate }}%</p>
      </div>
      <div class="p-4 bg-accent-orange-light border border-accent-orange rounded-lg">
        <p class="text-body-small text-text-secondary mb-1">Performance Trend</p>
        <p class="text-h2 text-text-primary capitalize">{{ report.performanceTrend }}</p>
      </div>
    </div>

    <!-- Study Subject Breakdown -->
    <div class="mb-6">
      <h3 class="text-h3 text-text-primary mb-4">Subject Analysis</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-4 bg-neutral-gray50 rounded-lg">
          <p class="text-body-small text-text-secondary mb-2">Most Studied</p>
          <p class="text-body font-semibold text-text-primary">{{ report.mostStudiedSubject }}</p>
        </div>
        <div class="p-4 bg-neutral-gray50 rounded-lg">
          <p class="text-body-small text-text-secondary mb-2">Least Studied</p>
          <p class="text-body font-semibold text-text-primary">{{ report.leastStudiedSubject }}</p>
        </div>
      </div>
    </div>

    <!-- Week-over-Week Comparison -->
    <div v-if="report.weekOverWeekComparison" class="mb-6">
      <h3 class="text-h3 text-text-primary mb-4">Week-over-Week Comparison</h3>
      <div class="space-y-3">
        <div class="flex items-center justify-between p-3 bg-neutral-gray50 rounded-lg">
          <span class="text-body text-text-primary">Study Hours</span>
          <span :class="getComparisonColor(report.weekOverWeekComparison.studyHours)" class="text-body font-semibold">
            {{ formatComparison(report.weekOverWeekComparison.studyHours) }}h
          </span>
        </div>
        <div class="flex items-center justify-between p-3 bg-neutral-gray50 rounded-lg">
          <span class="text-body text-text-primary">Completion Rate</span>
          <span :class="getComparisonColor(report.weekOverWeekComparison.completionRate)" class="text-body font-semibold">
            {{ formatComparison(report.weekOverWeekComparison.completionRate) }}%
          </span>
        </div>
      </div>
    </div>

    <!-- AI Suggestions -->
    <div class="mb-6">
      <h3 class="text-h3 text-text-primary mb-4">AI Improvement Suggestions</h3>
      <div class="space-y-3">
        <div
          v-for="(suggestion, index) in report.aiSuggestions"
          :key="index"
          class="p-4 bg-primary-green-bg border border-primary-green-light rounded-lg"
        >
          <div class="flex items-start gap-3">
            <Lightbulb class="w-5 h-5 text-primary-green flex-shrink-0 mt-0.5" />
            <p class="text-body text-text-primary">{{ suggestion }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <ReportCharts :report="report" />
  </Card>
</template>

<script setup>
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import ReportCharts from './ReportCharts.vue'
import { Share2, Download, Lightbulb } from 'lucide-vue-next'

defineProps({
  report: {
    type: Object,
    default: null
  }
})

defineEmits(['share', 'download'])

const formatWeekRange = (dateString) => {
  const date = new Date(dateString)
  const start = new Date(date)
  const day = start.getDay()
  const diff = start.getDate() - day
  start.setDate(diff)
  
  const end = new Date(start)
  end.setDate(end.getDate() + 6)
  
  return `${start.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`
}

const formatComparison = (value) => {
  if (value > 0) {
    return `+${value.toFixed(1)}`
  }
  return value.toFixed(1)
}

const getComparisonColor = (value) => {
  if (value > 0) return 'text-primary-green'
  if (value < 0) return 'text-accent-red'
  return 'text-text-secondary'
}
</script>

