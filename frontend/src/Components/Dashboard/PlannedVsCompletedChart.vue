<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">Planned vs Completed</h3>
    <div class="h-64 sm:h-80 relative">
      <div v-if="!data || data.length === 0" class="flex items-center justify-center h-full">
        <div class="text-center">
          <p class="text-body text-text-secondary mb-2">No study plan data available</p>
          <p class="text-body-small text-text-tertiary">Create study plans to see your planned vs completed hours</p>
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
import { computed } from 'vue'
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
import { chartColors, getChartConfig } from '@/utils/charts'

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

const chartData = computed(() => {
  const defaultData = [
    { week: 'Week 1', planned: 20, completed: 18 },
    { week: 'Week 2', planned: 22, completed: 20 },
    { week: 'Week 3', planned: 18, completed: 15 },
    { week: 'Week 4', planned: 24, completed: 22 },
    { week: 'Week 5', planned: 20, completed: 19 }
  ]
  
  const data = props.data.length > 0 ? props.data : defaultData
  
  return {
    labels: data.map(d => d.week),
    datasets: [
      {
        label: 'Planned (hours)',
        data: data.map(d => d.planned),
        backgroundColor: chartColors.accent.orange,
        borderRadius: 8,
        borderSkipped: false
      },
      {
        label: 'Completed (hours)',
        data: data.map(d => d.completed),
        backgroundColor: chartColors.primary,
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
        ...baseConfig.plugins.legend,
        position: 'top'
      }
    },
    scales: {
      ...baseConfig.scales,
      y: {
        ...baseConfig.scales.y,
        beginAtZero: true,
        ticks: {
          ...baseConfig.scales.y.ticks,
          callback: function(value) {
            return value + 'h'
          }
        }
      }
    }
  }
})
</script>

