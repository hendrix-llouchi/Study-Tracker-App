<template>
  <Card padding="lg">
    <h3 class="text-h3 text-text-primary mb-5">
      {{ isEdit ? 'Edit Class' : 'Add New Class' }}
    </h3>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <Select
        v-model="form.courseId"
        label="Course"
        placeholder="Select course"
        :options="courseOptions"
        required
        :error="errors.courseId"
      />

      <Select
        v-model="form.day"
        label="Day"
        :options="dayOptions"
        required
        :error="errors.day"
      />

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <Input
          v-model="form.startTime"
          type="time"
          label="Start Time"
          required
          :error="errors.startTime"
        />
        <Input
          v-model="form.endTime"
          type="time"
          label="End Time"
          required
          :error="errors.endTime"
        />
      </div>

      <Input
        v-model="form.location"
        type="text"
        label="Location"
        placeholder="e.g., Room 301"
        required
        :error="errors.location"
      />

      <Input
        v-model="form.instructor"
        type="text"
        label="Instructor"
        placeholder="e.g., Dr. Sarah Johnson"
        :error="errors.instructor"
      />

      <Select
        v-model="form.color"
        label="Color"
        :options="colorOptions"
        :error="errors.color"
      />

      <div v-if="hasConflict" class="p-4 bg-accent-orange-light border border-accent-orange rounded-lg">
        <div class="flex items-start gap-2">
          <AlertTriangle :size="20" class="text-accent-orange flex-shrink-0 mt-0.5" />
          <div>
            <p class="text-body-small font-medium text-accent-orange mb-1">Schedule Conflict Detected</p>
            <p class="text-body-small text-text-secondary">This time conflicts with an existing class.</p>
          </div>
        </div>
      </div>

      <div class="flex flex-col-reverse sm:flex-row gap-3 justify-end pt-4">
        <Button variant="ghost" type="button" @click="$emit('cancel')">
          Cancel
        </Button>
        <Button variant="primary" type="submit" :loading="loading">
          {{ isEdit ? 'Update' : 'Add' }} Class
        </Button>
      </div>
    </form>
  </Card>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Input from '@/Components/Common/Input.vue'
import Select from '@/Components/Common/Select.vue'
import Button from '@/Components/Common/Button.vue'
import { AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  classItem: {
    type: Object,
    default: null
  },
  courses: {
    type: Array,
    default: () => []
  },
  existingClasses: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'cancel'])

const isEdit = computed(() => !!props.classItem)

const form = ref({
  courseId: '',
  day: '',
  startTime: '',
  endTime: '',
  location: '',
  instructor: '',
  classType: 'lecture',
  color: 'blue'
})

const errors = ref({})

const courseOptions = computed(() => {
  return props.courses.map(course => ({
    value: course.id,
    label: `${course.code || ''} - ${course.name || 'Unknown'}`
  }))
})

const dayOptions = [
  { value: 'Monday', label: 'Monday' },
  { value: 'Tuesday', label: 'Tuesday' },
  { value: 'Wednesday', label: 'Wednesday' },
  { value: 'Thursday', label: 'Thursday' },
  { value: 'Friday', label: 'Friday' },
  { value: 'Saturday', label: 'Saturday' },
  { value: 'Sunday', label: 'Sunday' }
]

const colorOptions = [
  { value: 'orange', label: 'Orange' },
  { value: 'blue', label: 'Blue' },
  { value: 'green', label: 'Green' },
  { value: 'purple', label: 'Purple' },
  { value: 'red', label: 'Red' }
]

const hasConflict = computed(() => {
  if (!form.value.day || !form.value.startTime || !form.value.endTime) {
    return false
  }
  
  const otherClasses = props.existingClasses.filter(
    c => c.id !== props.classItem?.id
  )
  
  return otherClasses.some(c => {
    return (
      c.day === form.value.day &&
      hasTimeConflict(
        { startTime: form.value.startTime, endTime: form.value.endTime },
        { startTime: c.startTime, endTime: c.endTime }
      )
    )
  })
})

function timeToMinutes(time) {
  const [hours, minutes] = time.split(':').map(Number)
  return hours * 60 + minutes
}

function hasTimeConflict(time1, time2) {
  const start1 = timeToMinutes(time1.startTime)
  const end1 = timeToMinutes(time1.endTime)
  const start2 = timeToMinutes(time2.startTime)
  const end2 = timeToMinutes(time2.endTime)
  
  return (start1 < end2 && end1 > start2)
}

watch(() => props.classItem, (newVal) => {
  if (newVal) {
    form.value = {
      courseId: newVal.courseId || '',
      day: newVal.day || '',
      startTime: newVal.startTime || '',
      endTime: newVal.endTime || '',
      location: newVal.location || '',
      instructor: newVal.instructor || '',
      classType: newVal.classType || 'lecture',
      color: newVal.color || 'blue'
    }
  } else {
    form.value = {
      courseId: '',
      day: '',
      startTime: '',
      endTime: '',
      location: '',
      instructor: '',
      classType: 'lecture',
      color: 'blue'
    }
  }
}, { immediate: true })

const handleSubmit = () => {
  errors.value = {}
  
  if (!form.value.courseId) errors.value.courseId = 'Course is required'
  if (!form.value.day) errors.value.day = 'Day is required'
  if (!form.value.startTime) errors.value.startTime = 'Start time is required'
  if (!form.value.endTime) errors.value.endTime = 'End time is required'
  if (!form.value.location) errors.value.location = 'Location is required'
  
  if (Object.keys(errors.value).length > 0) {
    return
  }
  
  if (hasConflict.value && !confirm('This schedule has a conflict. Do you want to proceed anyway?')) {
    return
  }
  
  emit('submit', form.value)
}
</script>

