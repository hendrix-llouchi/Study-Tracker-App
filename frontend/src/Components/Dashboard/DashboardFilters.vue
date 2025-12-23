<template>
  <Card padding="default">
    <div class="flex flex-col sm:flex-row gap-4">
      <Select
        v-model="filters.course"
        :options="courseOptions"
        placeholder="All Courses"
        class="flex-1"
        @update:model-value="$emit('filter-change', filters)"
      />
      <Select
        v-model="filters.semester"
        :options="semesterOptions"
        placeholder="All Semesters"
        class="flex-1"
        @update:model-value="$emit('filter-change', filters)"
      />
      <Select
        v-model="filters.period"
        :options="periodOptions"
        placeholder="Select Period"
        class="flex-1"
        @update:model-value="$emit('filter-change', filters)"
      />
      <Button
        v-if="hasActiveFilters"
        variant="ghost"
        size="md"
        @click="clearFilters"
      >
        Clear
      </Button>
    </div>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Select from '@/Components/Common/Select.vue'
import Button from '@/Components/Common/Button.vue'

const emit = defineEmits(['filter-change'])

const props = defineProps({
  courses: {
    type: Array,
    default: () => []
  },
  semesters: {
    type: Array,
    default: () => []
  }
})

const filters = ref({
  course: null,
  semester: null,
  period: null
})

const courseOptions = computed(() => {
  const defaultCourses = [
    { value: 'cs201', label: 'Data Structures & Algorithms' },
    { value: 'cs301', label: 'Database Systems' },
    { value: 'cs302', label: 'Computer Networks' },
    { value: 'cs303', label: 'Software Engineering' }
  ]
  
  const courses = props.courses.length > 0 ? props.courses : defaultCourses
  return [
    { value: null, label: 'All Courses' },
    ...courses.map(c => ({ value: c.id || c.value, label: c.name || c.label }))
  ]
})

const semesterOptions = computed(() => {
  const defaultSemesters = [
    { value: 'fall2024', label: 'Fall 2024' },
    { value: 'spring2024', label: 'Spring 2024' },
    { value: 'fall2023', label: 'Fall 2023' }
  ]
  
  const semesters = props.semesters.length > 0 ? props.semesters : defaultSemesters
  return [
    { value: null, label: 'All Semesters' },
    ...semesters.map(s => ({ value: s.id || s.value, label: s.name || s.label }))
  ]
})

const periodOptions = [
  { value: null, label: 'All Time' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
  { value: 'semester', label: 'This Semester' },
  { value: 'year', label: 'This Year' },
  { value: 'custom', label: 'Custom Range' }
]

const hasActiveFilters = computed(() => {
  return filters.value.course !== null || filters.value.semester !== null || filters.value.period !== null
})

const clearFilters = () => {
  filters.value = {
    course: null,
    semester: null,
    period: null
  }
  emit('filter-change', filters.value)
}
</script>

