<template>
  <BaseModal
    :model-value="modelValue"
    title="Carry Over Incomplete Tasks"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template #default>
      <div class="space-y-4">
        <p class="text-body text-text-secondary">
          You have <strong>{{ incompleteTasks.length }}</strong> incomplete task{{ incompleteTasks.length === 1 ? '' : 's' }} 
          from <strong>{{ fromDate }}</strong>. Would you like to carry them over to today?
        </p>

        <div class="max-h-64 overflow-y-auto space-y-2">
          <div
            v-for="task in incompleteTasks"
            :key="task.id"
            class="p-3 bg-neutral-gray50 rounded-lg flex items-start gap-3"
          >
            <input
              :id="`task-${task.id}`"
              v-model="selectedTasks"
              type="checkbox"
              :value="task.id"
              class="mt-1 w-4 h-4 text-primary-green border-border-medium rounded focus:ring-primary-green cursor-pointer"
            />
            <label :for="`task-${task.id}`" class="flex-1 cursor-pointer">
              <p class="text-body font-medium text-text-primary">{{ task.topic }}</p>
              <p class="text-body-small text-text-secondary mt-0.5">{{ task.course }}</p>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-body-small text-text-secondary">{{ task.startTime }}</span>
                <span class="text-body-small text-text-secondary">•</span>
                <span class="text-body-small text-text-secondary">{{ formatDuration(task.duration) }}</span>
              </div>
            </label>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex flex-col-reverse sm:flex-row gap-3 justify-end">
        <Button variant="ghost" @click="$emit('update:modelValue', false)">
          Cancel
        </Button>
        <Button
          variant="primary"
          :disabled="selectedTasks.length === 0"
          @click="handleCarryOver"
        >
          Carry Over Selected ({{ selectedTasks.length }})
        </Button>
      </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import BaseModal from '@/Components/Modals/BaseModal.vue'
import Button from '@/Components/Common/Button.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  incompleteTasks: {
    type: Array,
    default: () => []
  },
  fromDate: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'carry-over'])

const selectedTasks = ref([])

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    selectedTasks.value = props.incompleteTasks.map(t => t.id)
  }
})

const formatDuration = (minutes) => {
  if (!minutes) return '0m'
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  if (hours > 0 && mins > 0) {
    return `${hours}h ${mins}m`
  } else if (hours > 0) {
    return `${hours}h`
  }
  return `${mins}m`
}

const handleCarryOver = () => {
  emit('carry-over', selectedTasks.value)
  emit('update:modelValue', false)
}
</script>

