<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">GPA Trend</h3>
      <Select
        v-model="selectedPeriod"
        :options="periodOptions"
        size="sm"
        class="w-32"
      />
    </div>
    <div class="h-64 sm:h-80 relative">
      <Line
        v-if="chartData"
        :data="chartData"
        :options="chartOptions"
      />
    </div>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import Card from '@/Components/Common/Card.vue'
import Select from '@/Components/Common/Select.vue'
import { chartColors, getChartConfig } from '@/utils/charts'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const selectedPeriod = ref('semester')

const periodOptions = [
  { value: 'month', label: 'This Month' },
  { value: 'semester', label: 'This Semester' },
  { value: 'year', label: 'This Year' },
  { value: 'all', label: 'All Time' }
]

const chartData = computed(() => {
  const defaultData = [
    { date: 'Jan', gpa: 3.2 },
    { date: 'Feb', gpa: 3.4 },
    { date: 'Mar', gpa: 3.5 },
    { date: 'Apr', gpa: 3.6 },
    { date: 'May', gpa: 3.7 },
    { date: 'Jun', gpa: 3.8 }
  ]
  
  const data = props.data.length > 0 ? props.data : defaultData
  
  return {
    labels: data.map(d => d.date),
    datasets: [
      {
        label: 'GPA',
        data: data.map(d => d.gpa),
        borderColor: chartColors.primary,
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: chartColors.primary,
        pointBorderColor: '#fff',
        pointBorderWidth: 2
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
        min: 0,
        max: 4.0,
        ticks: {
          ...baseConfig.scales.y.ticks,
          stepSize: 0.5,
          callback: function(value) {
            return value.toFixed(1)
          }
        }
      }
    }
  }
})
</script>

