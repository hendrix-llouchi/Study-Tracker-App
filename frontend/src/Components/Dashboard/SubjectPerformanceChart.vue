<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">Subject Performance</h3>
      <Select
        v-model="selectedMetric"
        :options="metricOptions"
        size="sm"
        class="w-32"
      />
    </div>
    <div class="h-64 sm:h-80 relative">
      <div v-if="!data || data.length === 0" class="flex items-center justify-center h-full">
        <div class="text-center">
          <p class="text-body text-text-secondary mb-2">No subject performance data available</p>
          <p class="text-body-small text-text-tertiary">Add academic results to see subject performance</p>
        </div>
      </div>
      <Bar
        v-else
        :data="chartData"
        :options="chartOptions"
      />
    </div>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import Card from '@/Components/Common/Card.vue'
import Select from '@/Components/Common/Select.vue'
import { chartColors, generateColorPalette, getChartConfig } from '@/utils/charts'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
)

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const selectedMetric = ref('gpa')

const metricOptions = [
  { value: 'gpa', label: 'GPA' },
  { value: 'score', label: 'Average Score' },
  { value: 'hours', label: 'Study Hours' }
]

const chartData = computed(() => {
  const defaultData = [
    { subject: 'Data Structures', gpa: 3.8, score: 92, hours: 45 },
    { subject: 'Database Systems', gpa: 3.6, score: 88, hours: 38 },
    { subject: 'Computer Networks', gpa: 3.5, score: 85, hours: 42 },
    { subject: 'Software Engineering', gpa: 3.9, score: 94, hours: 40 }
  ]
  
  const data = props.data.length > 0 ? props.data : defaultData
  const colors = generateColorPalette(data.length)
  
  return {
    labels: data.map(d => d.subject),
    datasets: [
      {
        label: selectedMetric.value === 'gpa' ? 'GPA' : selectedMetric.value === 'score' ? 'Score (%)' : 'Hours',
        data: data.map(d => d[selectedMetric.value]),
        backgroundColor: colors,
        borderRadius: 8,
        borderSkipped: false
      }
    ]
  }
})

const chartOptions = computed(() => {
  const baseConfig = getChartConfig()
  return {
    ...baseConfig,
    plugins: {
      ...baseConfig.plugins,
      legend: {
        display: false
      }
    },
    scales: {
      ...baseConfig.scales,
      y: {
        ...baseConfig.scales.y,
        beginAtZero: true
      }
    }
  }
})
</script>

