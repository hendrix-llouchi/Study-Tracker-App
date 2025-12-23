<template>
  <div
    v-if="conflicts.length > 0"
    class="p-4 bg-accent-orange-light border border-accent-orange rounded-lg mb-4"
  >
    <div class="flex items-start gap-3">
      <AlertTriangle :size="20" class="text-accent-orange flex-shrink-0 mt-0.5" />
      <div class="flex-1">
        <p class="text-body font-semibold text-accent-orange mb-2">
          Schedule Conflicts Detected
        </p>
        <div class="space-y-2">
          <div
            v-for="(conflict, index) in conflicts"
            :key="index"
            class="p-2 bg-white rounded border border-accent-orange"
          >
            <p class="text-body-small text-text-primary">
              <strong>{{ conflict.class1.course }}</strong> conflicts with
              <strong>{{ conflict.class2.course }}</strong>
            </p>
            <p class="text-body-small text-text-secondary mt-1">
              Both classes on {{ conflict.class1.day }} at overlapping times
            </p>
          </div>
        </div>
        <Button
          variant="secondary"
          size="sm"
          class="mt-3"
          @click="$emit('resolve')"
        >
          Resolve Conflicts
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import Button from '@/Components/Common/Button.vue'
import { AlertTriangle } from 'lucide-vue-next'

defineProps({
  conflicts: {
    type: Array,
    default: () => []
  }
})

defineEmits(['resolve'])
</script>

