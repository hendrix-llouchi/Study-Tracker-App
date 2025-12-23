<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">Historical Comparison</h3>
    
    <div class="mb-4">
      <Select
        v-model="selectedComparison"
        :options="comparisonOptions"
        placeholder="Select Comparison Type"
      />
    </div>

    <div v-if="selectedComparison === 'semester'" class="space-y-4">
      <div
        v-for="semester in semesterComparisons"
        :key="semester.name"
        class="p-4 border border-border-default rounded-lg"
      >
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-body font-semibold text-text-primary">{{ semester.name }}</h4>
          <span class="text-body font-bold" :class="getGpaColor(semester.gpa)">
            {{ semester.gpa.toFixed(2) }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex-1 bg-neutral-gray200 rounded-full h-2">
            <div
              class="bg-primary-green h-2 rounded-full transition-all"
              :style="{ width: `${(semester.gpa / 4.0) * 100}%` }"
            />
          </div>
          <span class="text-body-small text-text-secondary">{{ semester.results }} results</span>
        </div>
      </div>
    </div>

    <div v-else-if="selectedComparison === 'year'" class="space-y-4">
      <div
        v-for="year in yearComparisons"
        :key="year.name"
        class="p-4 border border-border-default rounded-lg"
      >
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-body font-semibold text-text-primary">{{ year.name }}</h4>
          <span class="text-body font-bold" :class="getGpaColor(year.gpa)">
            {{ year.gpa.toFixed(2) }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex-1 bg-neutral-gray200 rounded-full h-2">
            <div
              class="bg-primary-green h-2 rounded-full transition-all"
              :style="{ width: `${(year.gpa / 4.0) * 100}%` }"
            />
          </div>
          <span class="text-body-small text-text-secondary">{{ year.results }} results</span>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Select from '@/Components/Common/Select.vue'

const props = defineProps({
  results: {
    type: Array,
    default: () => []
  }
})

const selectedComparison = ref('semester')

const comparisonOptions = [
  { value: 'semester', label: 'By Semester' },
  { value: 'year', label: 'By Year' }
]

const gradeToPoints = {
  'A+': 4.0, 'A': 4.0, 'A-': 3.7,
  'B+': 3.3, 'B': 3.0, 'B-': 2.7,
  'C+': 2.3, 'C': 2.0, 'C-': 1.7,
  'D+': 1.3, 'D': 1.0, 'F': 0.0
}

const semesterComparisons = computed(() => {
  const semesters = {}
  
  props.results.forEach(result => {
    if (!semesters[result.semester]) {
      semesters[result.semester] = { credits: 0, points: 0, count: 0 }
    }
    const points = gradeToPoints[result.grade] || 0
    const credits = result.creditHours || 0
    semesters[result.semester].credits += credits
    semesters[result.semester].points += points * credits
    semesters[result.semester].count++
  })
  
  return Object.keys(semesters)
    .map(name => {
      const s = semesters[name]
      const gpa = s.credits > 0 ? s.points / s.credits : 0
      return {
        name,
        gpa,
        results: s.count
      }
    })
    .sort((a, b) => a.name.localeCompare(b.name))
})

const yearComparisons = computed(() => {
  const years = {}
  
  props.results.forEach(result => {
    const year = result.semester.split(' ')[1] || 'Unknown'
    if (!years[year]) {
      years[year] = { credits: 0, points: 0, count: 0 }
    }
    const points = gradeToPoints[result.grade] || 0
    const credits = result.creditHours || 0
    years[year].credits += credits
    years[year].points += points * credits
    years[year].count++
  })
  
  return Object.keys(years)
    .map(name => {
      const y = years[name]
      const gpa = y.credits > 0 ? y.points / y.credits : 0
      return {
        name: name,
        gpa,
        results: y.count
      }
    })
    .sort((a, b) => b.name.localeCompare(a.name))
})

const getGpaColor = (gpa) => {
  if (gpa >= 3.7) return 'text-primary-green'
  if (gpa >= 3.0) return 'text-secondary-blue'
  if (gpa >= 2.0) return 'text-accent-orange'
  return 'text-accent-red'
}
</script>

