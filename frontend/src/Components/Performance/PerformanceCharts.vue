<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
    <!-- GPA Trend -->
    <Card padding="default">
      <h3 class="text-h3 text-text-primary mb-5">GPA Trend</h3>
      <div class="h-64 sm:h-80 relative">
        <Line
          v-if="gpaTrendData"
          :data="gpaTrendData"
          :options="gpaTrendOptions"
        />
      </div>
    </Card>

    <!-- Subject Comparison -->
    <Card padding="default">
      <h3 class="text-h3 text-text-primary mb-5">Subject Comparison</h3>
      <div class="h-64 sm:h-80 relative">
        <Bar
          v-if="subjectData"
          :data="subjectData"
          :options="subjectOptions"
        />
      </div>
    </Card>

    <!-- Performance Distribution -->
    <Card padding="default" class="lg:col-span-2">
      <h3 class="text-h3 text-text-primary mb-5">Performance Distribution</h3>
      <div class="h-64 sm:h-80 relative">
        <Bar
          v-if="distributionData"
          :data="distributionData"
          :options="distributionOptions"
        />
      </div>
    </Card>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Line, Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import Card from '@/Components/Common/Card.vue'
import { chartColors, generateColorPalette, getChartConfig } from '@/utils/charts'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  results: {
    type: Array,
    default: () => []
  }
})

const gradeToPoints = {
  'A+': 4.0, 'A': 4.0, 'A-': 3.7,
  'B+': 3.3, 'B': 3.0, 'B-': 2.7,
  'C+': 2.3, 'C': 2.0, 'C-': 1.7,
  'D+': 1.3, 'D': 1.0, 'F': 0.0
}

const gpaTrendData = computed(() => {
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
  
  const sortedSemesters = Object.keys(semesters).sort()
  const gpaValues = sortedSemesters.map(sem => {
    const s = semesters[sem]
    return s.credits > 0 ? s.points / s.credits : 0
  })
  
  return {
    labels: sortedSemesters,
    datasets: [
      {
        label: 'GPA',
        data: gpaValues,
        borderColor: chartColors.primary,
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4
      }
    ]
  }
})

const gpaTrendOptions = computed(() => {
  const baseConfig = getChartConfig()
  return {
    ...baseConfig,
    plugins: {
      ...baseConfig.plugins,
      legend: { display: false }
    },
    scales: {
      ...baseConfig.scales,
      y: {
        ...baseConfig.scales.y,
        min: 0,
        max: 4.0
      }
    }
  }
})

const subjectData = computed(() => {
  const subjects = {}
  props.results.forEach(result => {
    if (!subjects[result.course]) {
      subjects[result.course] = { scores: [], credits: 0 }
    }
    subjects[result.course].scores.push((result.score / result.maxScore) * 100)
    subjects[result.course].credits += result.creditHours || 0
  })
  
  const subjectAverages = Object.keys(subjects).map(course => {
    const s = subjects[course]
    const avg = s.scores.reduce((a, b) => a + b, 0) / s.scores.length
    return { course, avg }
  }).sort((a, b) => b.avg - a.avg)
  
  const colors = generateColorPalette(subjectAverages.length)
  
  return {
    labels: subjectAverages.map(s => s.course),
    datasets: [
      {
        label: 'Average Score (%)',
        data: subjectAverages.map(s => s.avg),
        backgroundColor: colors,
        borderRadius: 8
      }
    ]
  }
})

const subjectOptions = computed(() => {
  const baseConfig = getChartConfig()
  return {
    ...baseConfig,
    plugins: {
      ...baseConfig.plugins,
      legend: { display: false }
    },
    scales: {
      ...baseConfig.scales,
      y: {
        ...baseConfig.scales.y,
        beginAtZero: true,
        max: 100
      }
    }
  }
})

const distributionData = computed(() => {
  const ranges = {
    'A (3.7-4.0)': 0,
    'B (3.0-3.6)': 0,
    'C (2.0-2.9)': 0,
    'D/F (0-1.9)': 0
  }
  
  props.results.forEach(result => {
    const points = gradeToPoints[result.grade] || 0
    if (points >= 3.7) ranges['A (3.7-4.0)']++
    else if (points >= 3.0) ranges['B (3.0-3.6)']++
    else if (points >= 2.0) ranges['C (2.0-2.9)']++
    else ranges['D/F (0-1.9)']++
  })
  
  return {
    labels: Object.keys(ranges),
    datasets: [
      {
        label: 'Number of Results',
        data: Object.values(ranges),
        backgroundColor: [
          chartColors.primary,
          chartColors.secondary,
          chartColors.accent.orange,
          chartColors.accent.red
        ],
        borderRadius: 8
      }
    ]
  }
})

const distributionOptions = computed(() => {
  const baseConfig = getChartConfig()
  return {
    ...baseConfig,
    plugins: {
      ...baseConfig.plugins,
      legend: { display: false }
    }
  }
})
</script>

