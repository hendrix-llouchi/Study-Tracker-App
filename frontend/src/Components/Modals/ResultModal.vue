<template>
  <BaseModal
    :model-value="modelValue"
    :title="isEdit ? 'Edit Result' : 'Add Result'"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template #default>
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
          <p class="text-body-small text-accent-red">{{ error }}</p>
        </div>

        <Select
          v-model="form.courseId"
          label="Course"
          placeholder="Select course"
          :options="courseOptions"
          required
          :error="errors.courseId"
        />

        <Select
          v-model="form.assessmentType"
          label="Assessment Type"
          placeholder="Select type"
          :options="assessmentOptions"
          required
          :error="errors.assessmentType"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input
            v-model.number="form.score"
            type="number"
            label="Score"
            placeholder="85"
            required
            :error="errors.score"
          />
          <Input
            v-model.number="form.maxScore"
            type="number"
            label="Max Score"
            placeholder="100"
            required
            :error="errors.maxScore"
          />
        </div>

        <Input
          v-model="form.grade"
          type="text"
          label="Grade"
          placeholder="B+"
          required
          :error="errors.grade"
        />

        <Input
          v-model.number="form.credits"
          type="number"
          label="Credit Hours"
          placeholder="4"
          required
          :error="errors.credits"
        />

        <Input
          v-model="form.semester"
          type="text"
          label="Semester"
          placeholder="Fall 2024"
          required
          :error="errors.semester"
        />

        <Input
          v-model="form.date"
          type="date"
          label="Date"
          required
          :error="errors.date"
        />
      </form>
    </template>

    <template #footer>
      <div class="flex flex-col-reverse sm:flex-row gap-3 justify-end">
        <Button variant="ghost" @click="handleCancel">Cancel</Button>
        <Button type="button" variant="primary" :loading="loading" class="w-full sm:w-auto" @click="handleSubmit">
          {{ isEdit ? 'Update' : 'Add' }} Result
        </Button>
      </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import BaseModal from './BaseModal.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'
import Select from '@/Components/Common/Select.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  courses: {
    type: Array,
    default: () => []
  },
  result: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'save'])

const isEdit = computed(() => !!props.result)

const form = ref({
  courseId: '',
  assessmentType: '',
  score: '',
  maxScore: '',
  grade: '',
  credits: '',
  semester: '',
  date: ''
})

const errors = ref({})
const error = ref('')
const loading = ref(false)

const courseOptions = computed(() => {
  return props.courses.map(course => ({
    value: course.id,
    label: course.name
  }))
})

const assessmentOptions = [
  { value: 'quiz', label: 'Quiz' },
  { value: 'assignment', label: 'Assignment' },
  { value: 'midterm', label: 'Midterm' },
  { value: 'final', label: 'Final Exam' },
  { value: 'project', label: 'Project' }
]

watch(() => props.result, (newResult) => {
  if (newResult) {
    form.value = {
      courseId: newResult.courseId || '',
      assessmentType: newResult.assessmentType || '',
      score: newResult.score || '',
      maxScore: newResult.maxScore || '',
      grade: newResult.grade || '',
      credits: newResult.credits || '',
      semester: newResult.semester || '',
      date: newResult.date || ''
    }
  }
}, { immediate: true })

const handleSubmit = async (e) => {
  if (e) {
    e.preventDefault()
  }
  errors.value = {}
  error.value = ''
  loading.value = true

  try {
    // Bypass API call for now
    console.log('Result saved:', form.value)
    emit('save', form.value)
    emit('update:modelValue', false)
  } catch (err) {
    error.value = err.message || 'Failed to save result'
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

