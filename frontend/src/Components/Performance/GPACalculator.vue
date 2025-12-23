<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">GPA Calculator</h3>
    <div class="text-center mb-6">
      <div class="inline-block relative">
        <div class="text-5xl sm:text-6xl font-bold mb-2" :class="gpaColor">
          {{ formattedGPA }}
        </div>
        <p class="text-body-small text-text-secondary">Current GPA</p>
      </div>
    </div>
    
    <div class="space-y-3">
      <div class="flex items-center justify-between p-3 bg-neutral-gray50 rounded-lg">
        <span class="text-body text-text-secondary">Total Credit Hours</span>
        <span class="text-body font-semibold text-text-primary">{{ totalCredits }}</span>
      </div>
      <div class="flex items-center justify-between p-3 bg-neutral-gray50 rounded-lg">
        <span class="text-body text-text-secondary">Total Grade Points</span>
        <span class="text-body font-semibold text-text-primary">{{ totalGradePoints.toFixed(2) }}</span>
      </div>
      <div class="flex items-center justify-between p-3 bg-neutral-gray50 rounded-lg">
        <span class="text-body text-text-secondary">Semester</span>
        <span class="text-body font-semibold text-text-primary">{{ currentSemester || 'N/A' }}</span>
      </div>
    </div>

    <div v-if="gpaTrend.length > 0" class="mt-6 pt-6 border-t border-border-default">
      <p class="text-body-small font-semibold text-text-secondary mb-3">GPA Trend</p>
      <div class="flex items-end gap-2 h-16">
        <div
          v-for="(gpa, index) in gpaTrend"
          :key="index"
          class="flex-1 bg-primary-green rounded-t-md transition-all hover:opacity-80 cursor-pointer"
          :style="{ height: `${(gpa / 4.0) * 100}%` }"
          :title="`${gpa.toFixed(2)}`"
        />
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'

const props = defineProps({
  results: {
    type: Array,
    default: () => []
  },
  currentSemester: {
    type: String,
    default: ''
  }
})

const gradeToPoints = {
  'A+': 4.0,
  'A': 4.0,
  'A-': 3.7,
  'B+': 3.3,
  'B': 3.0,
  'B-': 2.7,
  'C+': 2.3,
  'C': 2.0,
  'C-': 1.7,
  'D+': 1.3,
  'D': 1.0,
  'F': 0.0
}

const totalCredits = computed(() => {
  return props.results.reduce((sum, result) => sum + (result.creditHours || 0), 0)
})

const totalGradePoints = computed(() => {
  return props.results.reduce((sum, result) => {
    const points = gradeToPoints[result.grade] || 0
    const credits = result.creditHours || 0
    return sum + (points * credits)
  }, 0)
})

const currentGPA = computed(() => {
  if (totalCredits.value === 0) return 0
  return totalGradePoints.value / totalCredits.value
})

const formattedGPA = computed(() => {
  return currentGPA.value.toFixed(2)
})

const gpaColor = computed(() => {
  if (currentGPA.value >= 3.7) return 'text-primary-green'
  if (currentGPA.value >= 3.0) return 'text-secondary-blue'
  if (currentGPA.value >= 2.0) return 'text-accent-orange'
  return 'text-accent-red'
})

const gpaTrend = computed(() => {
  // Calculate GPA for each semester
  const semesters = {}
  props.results.forEach(result => {
    if (!semesters[result.semester]) {
      semesters[result.semester] = { credits: 0, points: 0 }
    }
    const points = gradeToPoints[result.grade] || 0
    const credits = result.creditHours || 0
    semesters[result.semester].credits += credits
    semesters[result.semester].points += points * credits
  })
  
  return Object.values(semesters).map(sem => {
    return sem.credits > 0 ? sem.points / sem.credits : 0
  }).slice(-6) // Last 6 semesters
})
</script>

