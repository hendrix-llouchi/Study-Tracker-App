<template>
  <BaseModal
    :model-value="modelValue"
    :title="title"
    :close-on-overlay="false"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div class="space-y-4">
      <div v-if="icon" class="flex justify-center">
        <div :class="iconContainerClasses" class="w-16 h-16 rounded-full flex items-center justify-center">
          <component :is="icon" :size="32" :class="iconClasses" />
        </div>
      </div>

      <p class="text-body text-text-primary text-center">{{ message }}</p>

      <div v-if="requireConfirmation" class="mt-4">
        <Input
          v-model="confirmationText"
          type="text"
          :label="confirmationLabel"
          :placeholder="confirmationPlaceholder"
          required
        />
        <p class="text-body-small text-text-secondary mt-2">
          Type <strong>{{ confirmationKeyword }}</strong> to confirm
        </p>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3 justify-end">
        <Button variant="ghost" @click="handleCancel">Cancel</Button>
        <Button
          variant="primary"
          :disabled="requireConfirmation && confirmationText !== confirmationKeyword"
          :loading="loading"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </Button>
      </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import BaseModal from './BaseModal.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'
import { AlertTriangle, Trash2, Info } from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  variant: {
    type: String,
    default: 'danger',
    validator: (value) => ['danger', 'warning', 'info'].includes(value)
  },
  requireConfirmation: {
    type: Boolean,
    default: false
  },
  confirmationKeyword: {
    type: String,
    default: 'DELETE'
  },
  confirmationLabel: {
    type: String,
    default: 'Confirmation'
  },
  confirmationPlaceholder: {
    type: String,
    default: 'Type to confirm'
  },
  icon: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const loading = ref(false)
const confirmationText = ref('')

const iconComponent = computed(() => {
  if (props.icon) return props.icon
  if (props.variant === 'danger') return Trash2
  if (props.variant === 'warning') return AlertTriangle
  return Info
})

const iconContainerClasses = computed(() => {
  if (props.variant === 'danger') return 'bg-red-100'
  if (props.variant === 'warning') return 'bg-accent-orange-light'
  return 'bg-secondary-blue-light'
})

const iconClasses = computed(() => {
  if (props.variant === 'danger') return 'text-accent-red'
  if (props.variant === 'warning') return 'text-accent-orange'
  return 'text-secondary-blue'
})

const handleConfirm = () => {
  loading.value = true
  emit('confirm')
  // Note: Parent should handle closing the modal after async operations
}

const handleCancel = () => {
  confirmationText.value = ''
  emit('cancel')
  emit('update:modelValue', false)
}
</script>

