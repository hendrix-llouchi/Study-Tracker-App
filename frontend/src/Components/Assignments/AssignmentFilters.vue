<template>
  <Card padding="default">
    <div class="flex flex-col sm:flex-row gap-4">
      <Select
        v-model="filters.course"
        :options="courseOptions"
        placeholder="All Courses"
        class="flex-1"
        @update:model-value="handleFilterChange"
      />
      <Select
        v-model="filters.status"
        :options="statusOptions"
        placeholder="All Status"
        class="flex-1"
        @update:model-value="handleFilterChange"
      />
      <Input
        v-model="filters.dueDate"
        type="date"
        placeholder="Filter by date"
        class="flex-1"
        @update:model-value="handleFilterChange"
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
import Input from '@/Components/Common/Input.vue'
import Button from '@/Components/Common/Button.vue'

const emit = defineEmits(['filter-change'])

const props = defineProps({
  courses: {
    type: Array,
    default: () => []
  }
})

const filters = ref({
  course: null,
  status: null,
  dueDate: null
})

const courseOptions = computed(() => {
  const defaultCourses = [
    { id: 'cs201', name: 'Data Structures & Algorithms' },
    { id: 'cs301', name: 'Database Systems' },
    { id: 'cs302', name: 'Computer Networks' },
    { id: 'cs303', name: 'Software Engineering' }
  ]
  
  const courses = props.courses.length > 0 ? props.courses : defaultCourses
  return [
    { value: null, label: 'All Courses' },
    ...courses.map(c => ({ value: c.id || c.value, label: c.name || c.label }))
  ]
})

const statusOptions = [
  { value: null, label: 'All Status' },
  { value: 'upcoming', label: 'Upcoming' },
  { value: 'overdue', label: 'Overdue' },
  { value: 'completed', label: 'Completed' }
]

const hasActiveFilters = computed(() => {
  return filters.value.course !== null || filters.value.status !== null || filters.value.dueDate !== null
})

const handleFilterChange = () => {
  emit('filter-change', filters.value)
}

const clearFilters = () => {
  filters.value = {
    course: null,
    status: null,
    dueDate: null
  }
  emit('filter-change', filters.value)
}
</script>

