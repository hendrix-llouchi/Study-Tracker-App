<template>
  <Card padding="default">
    <div class="overflow-x-auto">
      <table class="w-full border-collapse">
        <thead>
          <tr>
            <th class="w-24 p-3 text-left text-body-small font-semibold text-text-secondary border-b border-border-default">Time</th>
            <th
              v-for="day in weekDays"
              :key="day"
              class="p-3 text-center text-body-small font-semibold text-text-secondary border-b border-border-default"
            >
              {{ day }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="timeSlot in timeSlots"
            :key="timeSlot"
            class="border-b border-border-default"
          >
            <td class="p-3 text-body-small text-text-secondary font-medium">
              {{ timeSlot }}
            </td>
            <td
              v-for="day in weekDays"
              :key="day"
              class="p-2 align-top relative"
              :class="getCellClasses(day, timeSlot)"
            >
              <div
                v-for="classItem in getClassesForCell(day, timeSlot)"
                :key="classItem.id"
                :class="getClassColor(classItem.color)"
                class="p-2 rounded-lg mb-1 text-white text-body-small"
                @click="$emit('edit-class', classItem)"
              >
                <p class="font-semibold mb-0.5">{{ classItem.course }}</p>
                <p class="opacity-90 text-xs">{{ classItem.location }}</p>
                <p class="opacity-75 text-xs mt-0.5">{{ classItem.startTime }} - {{ classItem.endTime }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'

const props = defineProps({
  classes: {
    type: Array,
    default: () => []
  }
})

defineEmits(['edit-class'])

const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']

const timeSlots = computed(() => {
  const slots = []
  for (let hour = 8; hour <= 18; hour++) {
    slots.push(`${String(hour).padStart(2, '0')}:00`)
  }
  return slots
})

const getClassesForCell = (day, timeSlot) => {
  const [hour] = timeSlot.split(':').map(Number)
  return props.classes.filter(classItem => {
    if (classItem.day !== day) return false
    const classHour = parseInt(classItem.startTime.split(':')[0])
    return classHour === hour
  })
}

const getCellClasses = (day, timeSlot) => {
  const classes = getClassesForCell(day, timeSlot)
  if (classes.length > 0) {
    return 'bg-neutral-gray50'
  }
  return 'hover:bg-neutral-gray50'
}

const getClassColor = (color) => {
  const colors = {
    orange: 'bg-accent-orange',
    blue: 'bg-secondary-blue',
    green: 'bg-primary-green',
    purple: 'bg-accent-purple',
    red: 'bg-accent-red'
  }
  return colors[color] || 'bg-neutral-gray500'
}
</script>

