<template>
  <BaseModal
    :model-value="modelValue"
    :title="isEdit ? 'Edit Assignment' : 'Add Assignment'"
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

        <Input
          v-model="form.title"
          type="text"
          label="Assignment Title"
          placeholder="e.g., Advanced problem solving (sorting)"
          required
          :error="errors.title"
        />

        <div>
          <label class="block text-body-small text-text-secondary mb-1.5">
            Description
          </label>
          <textarea
            v-model="form.description"
            rows="4"
            class="w-full px-3.5 py-2.5 text-body text-text-primary bg-neutral-white border border-border-medium rounded-md transition-all duration-default placeholder:text-text-tertiary focus:outline-none focus:border-border-focus focus:ring-3 focus:ring-border-focus/10"
            placeholder="Assignment description..."
          ></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input
            v-model="form.dueDate"
            type="date"
            label="Due Date"
            required
            :error="errors.dueDate"
          />
          <Select
            v-model="form.priority"
            label="Priority"
            :options="priorityOptions"
            required
            :error="errors.priority"
          />
        </div>

        <div v-if="isEdit" class="flex items-center justify-between p-4 border border-border-default rounded-lg">
          <div>
            <p class="text-body font-medium text-text-primary">Status</p>
            <p class="text-body-small text-text-secondary">Mark assignment as completed</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input
              v-model="form.completed"
              type="checkbox"
              class="sr-only peer"
            />
            <div class="w-11 h-6 bg-neutral-gray300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-green rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-green"></div>
          </label>
        </div>
      </form>
    </template>

    <template #footer>
      <div class="flex flex-col-reverse sm:flex-row gap-3 justify-end">
        <Button v-if="isEdit" variant="ghost" class="text-accent-red hover:text-accent-red hover:bg-red-50 order-3 sm:order-1" @click="handleDelete">Delete</Button>
        <Button variant="ghost" @click="handleCancel" class="order-2 sm:order-2">Cancel</Button>
        <Button type="button" variant="primary" :loading="loading" class="w-full sm:w-auto order-1 sm:order-3" @click="handleSubmit">
          {{ isEdit ? 'Update' : 'Add' }} Assignment
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
  assignment: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'save', 'delete'])

const isEdit = computed(() => !!props.assignment)

const form = ref({
  courseId: '',
  title: '',
  description: '',
  dueDate: '',
  priority: '',
  completed: false
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

const priorityOptions = [
  { value: 'high', label: 'High' },
  { value: 'medium', label: 'Medium' },
  { value: 'low', label: 'Low' }
]

watch(() => props.assignment, (newAssignment) => {
  if (newAssignment) {
    form.value = {
      courseId: newAssignment.courseId || '',
      title: newAssignment.title || '',
      description: newAssignment.description || '',
      dueDate: newAssignment.dueDate || '',
      priority: newAssignment.priority || '',
      completed: newAssignment.status === 'completed'
    }
  }
}, { immediate: true })

const handleSubmit = async () => {
  errors.value = {}
  error.value = ''
  loading.value = true

  try {
    // Bypass API call for now
    console.log('Assignment saved:', form.value)
    emit('save', form.value)
    emit('update:modelValue', false)
  } catch (err) {
    error.value = err.message || 'Failed to save assignment'
    if (err.errors) {
      errors.value = err.errors
    }
  } finally {
    loading.value = false
  }
}

const handleDelete = () => {
  emit('delete', props.assignment.id)
}

const handleCancel = () => {
  emit('update:modelValue', false)
}
</script>

