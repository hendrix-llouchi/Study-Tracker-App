<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">Study Consistency</h3>
      <Select
        v-model="selectedRange"
        :options="rangeOptions"
        size="sm"
        class="w-32"
      />
    </div>
    <div class="overflow-x-auto">
      <div v-if="heatmapData.length === 0" class="flex items-center justify-center py-12">
        <div class="text-center">
          <p class="text-body text-text-secondary mb-2">No study data available</p>
          <p class="text-body-small text-text-tertiary">Create study plans to see your study consistency heatmap</p>
        </div>
      </div>
      <div v-else class="inline-block min-w-full">
        <div class="grid grid-cols-53 gap-1 mb-2">
          <div
            v-for="day in days"
            :key="day"
            class="text-body-small text-text-tertiary text-center py-1"
          >
            {{ day }}
          </div>
        </div>
        <div class="flex gap-1">
          <div class="flex flex-col gap-1">
            <div
              v-for="week in weeks"
              :key="week"
              class="text-body-small text-text-tertiary flex items-center justify-center h-3 w-4"
            >
              {{ week }}
            </div>
          </div>
          <div class="grid grid-cols-53 gap-1">
            <div
              v-for="(day, index) in heatmapData"
              :key="index"
              :class="getDayColor(day.value)"
              class="w-3 h-3 rounded-sm cursor-pointer hover:ring-2 hover:ring-primary-green transition-all"
              :title="`${day.date}: ${day.value} hours`"
            />
          </div>
        </div>
      </div>
    </div>
    <div class="flex items-center justify-end gap-4 mt-4">
      <span class="text-body-small text-text-tertiary">Less</span>
      <div class="flex gap-1">
        <div class="w-3 h-3 bg-neutral-gray100 rounded-sm"></div>
        <div class="w-3 h-3 bg-primary-green-light rounded-sm"></div>
        <div class="w-3 h-3 bg-primary-green rounded-sm"></div>
        <div class="w-3 h-3 bg-primary-green-hover rounded-sm"></div>
      </div>
      <span class="text-body-small text-text-tertiary">More</span>
    </div>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Select from '@/Components/Common/Select.vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const selectedRange = ref('year')
const days = ['S', 'M', 'T', 'W', 'T', 'F', 'S']
const weeks = Array.from({ length: 53 }, (_, i) => (i % 5 === 0 ? String(Math.floor(i / 5)) : ''))

const rangeOptions = [
  { value: 'month', label: 'Last Month' },
  { value: '3months', label: 'Last 3 Months' },
  { value: 'year', label: 'Last Year' }
]

const generateHeatmapData = () => {
  const data = []
  const today = new Date()
  const startDate = new Date(today)
  
  if (selectedRange.value === 'month') {
    startDate.setMonth(today.getMonth() - 1)
  } else if (selectedRange.value === '3months') {
    startDate.setMonth(today.getMonth() - 3)
  } else {
    startDate.setFullYear(today.getFullYear() - 1)
  }
  
  const currentDate = new Date(startDate)
  while (currentDate <= today) {
    data.push({
      date: currentDate.toISOString().split('T')[0],
      value: Math.floor(Math.random() * 5) // Mock data: 0-4 hours
    })
    currentDate.setDate(currentDate.getDate() + 1)
  }
  
  return data
}

const heatmapData = computed(() => {
  if (props.data && props.data.length > 0) {
    // Ensure data has date and value properties
    return props.data.map(item => ({
      date: item.date,
      value: item.value || 0
    }))
  }
  return []
})

const getDayColor = (value) => {
  if (value === 0) return 'bg-neutral-gray100'
  if (value === 1) return 'bg-primary-green-light'
  if (value === 2) return 'bg-primary-green'
  return 'bg-primary-green-hover'
}
</script>

<style scoped>
.grid-cols-53 {
  grid-template-columns: repeat(53, minmax(0, 1fr));
}

@media (max-width: 768px) {
  .grid-cols-53 {
    grid-template-columns: repeat(26, minmax(0, 1fr));
  }
}
</style>

