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
      <div v-if="!data || data.length === 0" class="flex items-center justify-center h-full">
        <div class="text-center">
          <p class="text-body text-text-secondary mb-2">No GPA data available</p>
          <p class="text-body-small text-text-tertiary">Add academic results to see your GPA trend</p>
        </div>
      </div>
      <Line
        v-else
        :data="chartData"
        :options="chartOptions"
      />
    </div>
  </Card>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
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
import { useDashboardStore } from '@/Stores/dashboard'
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

const dashboardStore = useDashboardStore()
const selectedPeriod = ref('all')

const periodOptions = [
  { value: 'all', label: 'All Time' },
  { value: 'year', label: 'This Year' },
  { value: 'semester', label: 'This Semester' },
  { value: 'month', label: 'This Month' }
]

// Watch for period changes and refresh data
watch(selectedPeriod, async (newPeriod) => {
  await dashboardStore.refreshGPATrend(newPeriod)
})

const chartData = computed(() => {
  // Use props.data if available, otherwise show empty state
  const data = props.data || []
  
  if (data.length === 0) {
    return {
      labels: [],
      datasets: [
        {
          label: 'GPA',
          data: [],
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
  }
  
  return {
    labels: data.map(d => d.date || d.period || ''),
    datasets: [
      {
        label: 'GPA',
        data: data.map(d => d.gpa || 0),
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

