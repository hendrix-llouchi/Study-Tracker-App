<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">Calendar View</h3>
      <div class="flex items-center gap-2">
        <Button variant="ghost" size="sm" @click="previousMonth">
          <ChevronLeft :size="16" />
        </Button>
        <Button variant="ghost" size="sm" @click="nextMonth">
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
          <div class="flex-1 flex flex-col gap-0.5 overflow-hidden">
            <div
              v-for="assignment in day.assignments.slice(0, 2)"
              :key="assignment.id"
              :class="getAssignmentColor(assignment)"
              class="text-xs px-1 py-0.5 rounded truncate"
              :title="assignment.title"
            >
              {{ assignment.title }}
            </div>
            <span v-if="day.assignments.length > 2" class="text-xs text-text-tertiary">
              +{{ day.assignments.length - 2 }} more
            </span>
          </div>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  assignments: {
    type: Array,
    default: () => []
  },
  selectedDate: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['date-selected'])

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const currentDate = ref(new Date())

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
  const dayAssignments = props.assignments.filter(a => a.dueDate === dateStr)
  
  return {
    date: dateStr,
    day: date.getDate(),
    isCurrentMonth,
    isToday: dateStr === new Date().toISOString().split('T')[0],
    isSelected: dateStr === props.selectedDate,
    assignments: dayAssignments
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

const getAssignmentColor = (assignment) => {
  if (assignment.completed) {
    return 'bg-neutral-gray300 text-neutral-gray600'
  }
  if (assignment.priority === 'high') {
    return 'bg-accent-red text-white'
  }
  if (assignment.priority === 'medium') {
    return 'bg-accent-orange text-white'
  }
  return 'bg-secondary-blue text-white'
}

const selectDate = (date) => {
  emit('date-selected', date)
}

const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

const goToToday = () => {
  currentDate.value = new Date()
  const today = new Date().toISOString().split('T')[0]
  emit('date-selected', today)
}

watch(() => props.selectedDate, (newDate) => {
  if (newDate) {
    const date = new Date(newDate)
    currentDate.value = date
  }
}, { immediate: true })
</script>

