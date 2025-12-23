<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">Areas for Improvement</h3>
      <Badge variant="warning" size="sm">{{ weakAreas.length }} courses</Badge>
    </div>

    <div v-if="weakAreas.length === 0" class="text-center py-8">
      <CheckCircle2 class="w-12 h-12 text-primary-green mx-auto mb-3" />
      <p class="text-body text-text-primary font-medium">Great work!</p>
      <p class="text-body-small text-text-secondary">All your courses are performing well</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="area in weakAreas"
        :key="area.course"
        class="p-4 bg-accent-orange-light border border-accent-orange rounded-lg"
      >
        <div class="flex items-start justify-between mb-2">
          <div>
            <p class="text-body font-semibold text-text-primary mb-1">{{ area.course }}</p>
            <p class="text-body-small text-text-secondary">Average: {{ area.average.toFixed(1) }}%</p>
          </div>
          <AlertTriangle class="w-5 h-5 text-accent-orange flex-shrink-0" />
        </div>
        <div class="mt-3">
          <p class="text-body-small font-medium text-accent-orange mb-2">Recommended Actions:</p>
          <ul class="list-disc list-inside space-y-1 text-body-small text-text-secondary">
            <li>Increase study hours for this subject</li>
            <li>Focus on key concepts and practice problems</li>
            <li>Seek additional help or tutoring if needed</li>
          </ul>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Badge from '@/Components/Common/Badge.vue'
import { CheckCircle2, AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  results: {
    type: Array,
    default: () => []
  },
  threshold: {
    type: Number,
    default: 75
  }
})

const weakAreas = computed(() => {
  const subjects = {}
  
  props.results.forEach(result => {
    if (!subjects[result.course]) {
      subjects[result.course] = { scores: [], total: 0, count: 0 }
    }
    const percentage = (result.score / result.maxScore) * 100
    subjects[result.course].scores.push(percentage)
    subjects[result.course].total += percentage
    subjects[result.course].count++
  })
  
  const weak = Object.keys(subjects)
    .map(course => ({
      course,
      average: subjects[course].total / subjects[course].count
    }))
    .filter(subject => subject.average < props.threshold)
    .sort((a, b) => a.average - b.average)
  
  return weak
})
</script>

