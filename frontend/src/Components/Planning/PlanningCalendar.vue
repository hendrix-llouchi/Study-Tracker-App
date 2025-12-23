<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">Study Calendar</h3>
      <div class="flex items-center gap-2">
        <Button variant="ghost" size="sm" @click="previousPeriod">
          <ChevronLeft :size="16" />
        </Button>
        <Button variant="ghost" size="sm" @click="nextPeriod">
          <ChevronRight :size="16" />
        </Button>
        <Button variant="secondary" size="sm" @click="goToToday">
          Today
        </Button>
      </div>
    </div>

    <div class="mb-4">
      <p class="text-body font-semibold text-text-primary text-center">
        {{ currentMonthYear }}
      </p>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7 gap-1 mb-2">
      <div
        v-for="day in weekDays"
        :key="day"
        class="text-center text-body-small font-semibold text-text-secondary py-2"
      >
        {{ day }}
      </div>
    </div>

    <div class="grid grid-cols-7 gap-1">
      <div
        v-for="(day, index) in calendarDays"
        :key="index"
        :class="getDayClasses(day)"
        class="aspect-square p-1 cursor-pointer hover:bg-neutral-gray50 rounded-md transition-colors"
        @click="selectDate(day.date)"
      >
        <div class="flex flex-col h-full">
          <span :class="getDayNumberClasses(day)" class="text-body-small font-medium mb-1">
            {{ day.day }}
          </span>
          <div v-if="day.plans > 0" class="flex-1 flex items-center justify-center">
            <div class="flex gap-0.5">
              <div
                v-for="plan in day.planIndicators"
                :key="plan"
                :class="plan.completed ? 'bg-primary-green' : 'bg-accent-orange'"
                class="w-1.5 h-1.5 rounded-full"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  selectedDate: {
    type: String,
    required: true
  },
  plans: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['date-selected'])

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const currentDate = ref(new Date(props.selectedDate))

const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const daysInMonth = lastDay.getDate()
  const startingDayOfWeek = firstDay.getDay()
  
  const days = []
  
  // Previous month days
  const prevMonth = new Date(year, month, 0)
  const prevMonthDays = prevMonth.getDate()
  for (let i = startingDayOfWeek - 1; i >= 0; i--) {
    const date = new Date(year, month - 1, prevMonthDays - i)
    days.push(createDay(date, false))
  }
  
  // Current month days
  for (let i = 1; i <= daysInMonth; i++) {
    const date = new Date(year, month, i)
    days.push(createDay(date, true))
  }
  
  // Next month days
  const remainingDays = 42 - days.length
  for (let i = 1; i <= remainingDays; i++) {
    const date = new Date(year, month + 1, i)
    days.push(createDay(date, false))
  }
  
  return days
})

const createDay = (date, isCurrentMonth) => {
  const dateStr = date.toISOString().split('T')[0]
  const dayPlans = props.plans.filter(p => p.date === dateStr)
  const completedPlans = dayPlans.filter(p => p.completed).length
  
  return {
    date: dateStr,
    day: date.getDate(),
    isCurrentMonth,
    isToday: dateStr === new Date().toISOString().split('T')[0],
    isSelected: dateStr === props.selectedDate,
    plans: dayPlans.length,
    completed: completedPlans,
    planIndicators: dayPlans.slice(0, 3).map(p => ({ completed: p.completed }))
  }
}

const getDayClasses = (day) => {
  let classes = ''
  if (!day.isCurrentMonth) {
    classes += 'opacity-40 '
  }
  if (day.isSelected) {
    classes += 'bg-primary-green-bg border-2 border-primary-green '
  }
  if (day.isToday && !day.isSelected) {
    classes += 'ring-2 ring-primary-green-light '
  }
  return classes
}

const getDayNumberClasses = (day) => {
  if (day.isSelected) {
    return 'text-primary-green font-semibold'
  }
  if (day.isToday) {
    return 'text-primary-green'
  }
  return day.isCurrentMonth ? 'text-text-primary' : 'text-text-tertiary'
}

const selectDate = (date) => {
  emit('date-selected', date)
}

const previousPeriod = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextPeriod = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

const goToToday = () => {
  const today = new Date().toISOString().split('T')[0]
  currentDate.value = new Date()
  emit('date-selected', today)
}
</script>

