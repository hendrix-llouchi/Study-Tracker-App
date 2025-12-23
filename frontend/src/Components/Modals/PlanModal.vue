<template>
  <BaseModal
    :model-value="modelValue"
    title="Create Study Plan"
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

        <div v-if="hasConflict" class="p-4 bg-accent-orange-light border border-accent-orange rounded-lg">
          <div class="flex items-start gap-2">
            <AlertTriangle :size="20" class="text-accent-orange flex-shrink-0 mt-0.5" />
            <div>
              <p class="text-body-small font-medium text-accent-orange mb-1">Schedule Conflict</p>
              <p class="text-body-small text-text-secondary">This time conflicts with a scheduled class.</p>
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
import { ref, computed } from 'vue'
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
  }
})

const emit = defineEmits(['update:modelValue', 'save', 'save-and-create'])

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

const handleSubmit = async () => {
  await savePlan(false)
}

const handleSaveAndCreate = async () => {
  await savePlan(true)
}

const savePlan = async (createAnother) => {
  errors.value = {}
  error.value = ''
  loading.value = true

  try {
    // Bypass API call for now
    console.log('Plan created:', form.value)
    
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
    } else {
      emit('save', form.value)
      emit('update:modelValue', false)
    }
  } catch (err) {
    error.value = err.message || 'Failed to create plan'
    if (err.errors) {
      errors.value = err.errors
    }
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  emit('update:modelValue', false)
}
</script>

