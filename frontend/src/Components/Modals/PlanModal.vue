<template>
  <BaseModal
    :model-value="modelValue"
    :title="plan ? 'Edit Study Plan' : 'Create Study Plan'"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template #default>
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
          <p class="text-body-small text-accent-red">{{ error }}</p>
        </div>

        <Input
          v-model="form.date"
          type="date"
          label="Date"
          required
          :error="errors.date"
        />

        <Select
          v-model="form.courseId"
          label="Course"
          placeholder="Select course"
          :options="courseOptions"
          required
          :error="errors.courseId"
        />

        <Input
          v-model="form.topic"
          type="text"
          label="Topic/Chapter"
          placeholder="e.g., Binary Search Trees"
          required
          :error="errors.topic"
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
            v-model.number="form.duration"
            type="number"
            label="Duration (minutes)"
            placeholder="120"
            required
            :error="errors.duration"
          />
        </div>

        <Select
          v-model="form.priority"
          label="Priority"
          :options="priorityOptions"
          required
          :error="errors.priority"
        />

        <Select
          v-model="form.studyType"
          label="Study Type"
          :options="studyTypeOptions"
          required
          :error="errors.studyType"
        />

        <div v-if="checkingConflict" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
          <p class="text-body-small text-text-secondary">Checking for conflicts...</p>
        </div>
        
        <div v-else-if="hasConflict && conflicts.length > 0" class="p-4 bg-accent-orange-light border border-accent-orange rounded-lg">
          <div class="flex items-start gap-2">
            <AlertTriangle :size="20" class="text-accent-orange flex-shrink-0 mt-0.5" />
            <div class="flex-1">
              <p class="text-body-small font-medium text-accent-orange mb-2">Schedule Conflict Detected</p>
              <div class="space-y-1">
                <p v-for="(conflict, index) in conflicts" :key="index" class="text-body-small text-text-secondary">
                  Conflicts with <strong>{{ conflict.course_name }}</strong> 
                  ({{ conflict.start_time }} - {{ conflict.end_time }}{{ conflict.location ? `, ${conflict.location}` : '' }})
                </p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </template>

    <template #footer>
      <div class="flex flex-col sm:flex-row gap-3 justify-end">
        <Button variant="ghost" @click="handleCancel" class="order-3 sm:order-1">Cancel</Button>
        <Button variant="secondary" @click="handleSaveAndCreate" class="order-2 w-full sm:w-auto">Save & Create Another</Button>
        <Button type="button" variant="primary" :loading="loading" class="order-1 sm:order-3 w-full sm:w-auto" @click="handleSubmit">Save Plan</Button>
      </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { usePlanningStore } from '@/Stores/planning'
import { getErrorMessage, getValidationErrors } from '@/utils/errorHandler'
import BaseModal from './BaseModal.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'
import Select from '@/Components/Common/Select.vue'
import { AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  courses: {
    type: Array,
    default: () => []
  },
  plan: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'save', 'save-and-create'])

const planningStore = usePlanningStore()

const form = ref({
  date: '',
  courseId: '',
  topic: '',
  startTime: '',
  duration: '',
  priority: '',
  studyType: ''
})

const errors = ref({})
const error = ref('')
const loading = ref(false)
const hasConflict = ref(false)
const conflicts = ref([])
const checkingConflict = ref(false)

const courseOptions = computed(() => {
  return props.courses.map(course => ({
    value: course.id,
    label: course.name
  }))
})

const priorityOptions = [
  { value: 'high', label: 'High' },
  { value: 'medium', label: 'Medium' },
  { value: 'low', label: 'Low' }
]

const studyTypeOptions = [
  { value: 'review', label: 'Review' },
  { value: 'new-material', label: 'New Material' },
  { value: 'practice', label: 'Practice' }
]

// Watch for plan prop changes (editing mode)
watch(() => props.plan, (newPlan) => {
  if (newPlan) {
    form.value = {
      date: newPlan.date || '',
      courseId: newPlan.courseId || '',
      topic: newPlan.topic || '',
      startTime: newPlan.startTime || '',
      duration: newPlan.duration || '',
      priority: newPlan.priority || 'medium',
      studyType: newPlan.studyType || 'new-material'
    }
  } else {
    // Reset form when closing
    form.value = {
      date: '',
      courseId: '',
      topic: '',
      startTime: '',
      duration: '',
      priority: '',
      studyType: ''
    }
    hasConflict.value = false
    conflicts.value = []
  }
}, { immediate: true })

// Watch for modal open/close
watch(() => props.modelValue, (isOpen) => {
  if (isOpen && props.plan) {
    // Form is already set by plan watcher
    checkConflictsDebounced()
  } else if (!isOpen) {
    // Reset on close
    form.value = {
      date: '',
      courseId: '',
      topic: '',
      startTime: '',
      duration: '',
      priority: '',
      studyType: ''
    }
    hasConflict.value = false
    conflicts.value = []
    errors.value = {}
    error.value = ''
  }
})

// Check conflicts when date, startTime, or duration changes
let conflictCheckTimeout = null
const checkConflictsDebounced = () => {
  if (conflictCheckTimeout) {
    clearTimeout(conflictCheckTimeout)
  }
  
  conflictCheckTimeout = setTimeout(async () => {
    if (!form.value.date || !form.value.startTime || !form.value.duration) {
      hasConflict.value = false
      conflicts.value = []
      return
    }
    
    checkingConflict.value = true
    try {
      const result = await planningStore.checkConflicts(
        form.value.date,
        form.value.startTime,
        parseInt(form.value.duration) || 0
      )
      
      hasConflict.value = result.has_conflicts || false
      conflicts.value = result.conflicts || []
    } catch (err) {
      console.error('Failed to check conflicts:', err)
      hasConflict.value = false
      conflicts.value = []
    } finally {
      checkingConflict.value = false
    }
  }, 500) // Debounce by 500ms
}

watch([() => form.value.date, () => form.value.startTime, () => form.value.duration], () => {
  if (props.modelValue) {
    checkConflictsDebounced()
  }
})

const handleSubmit = async () => {
  await savePlan(false)
}

const handleSaveAndCreate = async () => {
  await savePlan(true)
}

const savePlan = async (createAnother) => {
  errors.value = {}
  error.value = ''
  
  // Validation
  if (!form.value.date) errors.value.date = 'Date is required'
  if (!form.value.courseId) errors.value.courseId = 'Course is required'
  if (!form.value.topic) errors.value.topic = 'Topic is required'
  if (!form.value.startTime) errors.value.startTime = 'Start time is required'
  if (!form.value.duration) errors.value.duration = 'Duration is required'
  if (!form.value.priority) errors.value.priority = 'Priority is required'
  if (!form.value.studyType) errors.value.studyType = 'Study type is required'
  
  if (Object.keys(errors.value).length > 0) {
    return
  }
  
  loading.value = true

  try {
    if (createAnother) {
      emit('save-and-create', form.value)
      // Reset form but keep date
      form.value = {
        ...form.value,
        courseId: '',
        topic: '',
        startTime: '',
        duration: '',
        priority: '',
        studyType: ''
      }
      hasConflict.value = false
      conflicts.value = []
    } else {
      emit('save', form.value)
      emit('update:modelValue', false)
    }
  } catch (err) {
    error.value = getErrorMessage(err)
    const validationErrors = getValidationErrors(err)
    if (Object.keys(validationErrors).length > 0) {
      errors.value = validationErrors
    }
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  emit('update:modelValue', false)
}
</script>

