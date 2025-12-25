<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">Weekly Progress</h3>
    <div class="h-48 sm:h-60 relative mb-4">
      <div v-if="!data || data.length === 0" class="flex items-center justify-center h-full">
        <div class="text-center">
          <p class="text-body text-text-secondary mb-2">No weekly progress data available</p>
          <p class="text-body-small text-text-tertiary">Create study plans to track your weekly progress</p>
        </div>
      </div>
      <canvas v-else ref="chartCanvas"></canvas>
    </div>
    <div class="flex gap-4 justify-center mt-4">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-primary-green rounded"></div>
        <span class="text-body-small text-text-secondary">Completed</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-accent-orange rounded"></div>
        <span class="text-body-small text-text-secondary">Planned</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-accent-red rounded"></div>
        <span class="text-body-small text-text-secondary">Missed</span>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import Card from '@/Components/Common/Card.vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const chartCanvas = ref(null)

onMounted(() => {
  drawChart()
})

watch(() => props.data, () => {
  drawChart()
}, { deep: true })

const drawChart = () => {
  if (!chartCanvas.value) return

  const canvas = chartCanvas.value
  const ctx = canvas.getContext('2d')
  const width = canvas.offsetWidth || 400
  const height = 240

  canvas.width = width
  canvas.height = height

  const data = props.data || []
  
  if (data.length === 0) {
    return
  }

  const maxValue = Math.max(...data.flatMap(d => [d.completed, d.planned, d.missed]))
  const padding = 40
  const chartWidth = width - padding * 2
  const chartHeight = height - padding * 2
  const barWidth = chartWidth / data.length / 3
  const spacing = barWidth / 2

  // Clear canvas
  ctx.clearRect(0, 0, width, height)

  // Draw bars
  data.forEach((item, index) => {
    const x = padding + (index * (chartWidth / data.length)) + spacing
    const baseY = height - padding

    // Completed (green)
    const completedHeight = (item.completed / maxValue) * chartHeight
    ctx.fillStyle = '#10B981'
    ctx.fillRect(x, baseY - completedHeight, barWidth, completedHeight)

    // Planned (orange)
    const plannedHeight = (item.planned / maxValue) * chartHeight
    ctx.fillStyle = '#F59E0B'
    ctx.fillRect(x + barWidth + spacing / 2, baseY - plannedHeight, barWidth, plannedHeight)

    // Missed (red)
    const missedHeight = (item.missed / maxValue) * chartHeight
    ctx.fillStyle = '#EF4444'
    ctx.fillRect(x + (barWidth + spacing / 2) * 2, baseY - missedHeight, barWidth, missedHeight)

    // Week label
    ctx.fillStyle = '#9CA3AF'
    ctx.font = '12px Inter'
    ctx.textAlign = 'center'
    ctx.fillText(item.week, x + barWidth * 1.5, height - 10)
  })
}
</script>

