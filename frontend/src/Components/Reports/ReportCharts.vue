<template>
  <div class="space-y-6">
    <!-- Study Hours Comparison -->
    <div>
      <h4 class="text-body font-semibold text-text-primary mb-4">Study Hours: Planned vs Actual</h4>
      <div class="h-48 sm:h-64 relative">
        <Bar
          v-if="studyHoursData"
          :data="studyHoursData"
          :options="chartOptions"
        />
      </div>
    </div>
  </div>
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
  report: {
    type: Object,
    required: true
  }
})

const studyHoursData = computed(() => {
  return {
    labels: ['Study Hours'],
    datasets: [
      {
        label: 'Planned',
        data: [props.report.totalStudyHours.planned],
        backgroundColor: chartColors.accent.orange
      },
      {
        label: 'Actual',
        data: [props.report.totalStudyHours.actual],
        backgroundColor: chartColors.primary
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
        beginAtZero: true
      }
    }
  }
})
</script>

