<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="handleOverlayClick"
      >
        <!-- Overlay -->
        <div
          class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity"
          @click="handleOverlayClick"
        ></div>

        <!-- Modal Container -->
        <div
          class="relative bg-neutral-white rounded-2xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-hidden transform transition-all"
        >
          <!-- Close Button -->
          <button
            v-if="showClose"
            @click="handleClose"
            class="absolute top-4 right-4 z-10 w-8 h-8 rounded-lg bg-transparent text-neutral-gray500 hover:bg-neutral-gray100 flex items-center justify-center transition-colors duration-default"
          >
            <X :size="20" />
          </button>

          <!-- Header -->
          <div v-if="$slots.header || title" class="px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 pb-4 border-b border-border-default">
            <slot name="header">
              <h2 v-if="title" class="text-h2 text-text-primary">{{ title }}</h2>
            </slot>
          </div>

          <!-- Content -->
          <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <slot />
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="px-4 sm:px-6 lg:px-8 py-4 border-t border-border-default bg-neutral-gray50">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { X } from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  showClose: {
    type: Boolean,
    default: true
  },
  closeOnOverlay: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

const handleClose = () => {
  emit('update:modelValue', false)
  emit('close')
}

const handleOverlayClick = () => {
  if (props.closeOnOverlay) {
    handleClose()
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-active .bg-neutral-white,
.modal-leave-active .bg-neutral-white {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .bg-neutral-white,
.modal-leave-to .bg-neutral-white {
  transform: scale(0.95);
  opacity: 0;
}
</style>

