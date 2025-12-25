<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">Upcoming Classes</h3>
    <div class="space-y-3">
      <div
        v-for="classItem in classes"
        :key="`class-${classItem.id}-${classItem.thumbnail || 'no-thumb'}`"
        class="bg-neutral-gray50 rounded-xl p-4 flex flex-col sm:flex-row gap-3 cursor-pointer hover:bg-neutral-gray100 hover:transform hover:translate-x-1 transition-all duration-default"
      >
        <div class="w-full sm:w-20 h-32 sm:h-20 rounded-lg flex-shrink-0 bg-neutral-gray200 flex items-center justify-center overflow-hidden">
          <img
            v-if="hasValidThumbnail(classItem)"
            :src="classItem.thumbnail"
            :alt="classItem.courseName || 'Course'"
            class="w-full h-full object-cover"
            @error="handleImageError(classItem.id)"
          />
          <BookOpen
            v-else
            :size="24"
            class="text-text-tertiary"
          />
        </div>
        <div class="flex-1 min-w-0 flex flex-col justify-between">
          <div>
            <p class="text-body font-semibold text-text-primary mb-1 truncate">
              {{ classItem.courseName }}
            </p>
            <p class="text-body-small text-text-secondary mb-2 truncate">
              {{ classItem.instructor }}
            </p>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-body-small text-accent-orange font-medium">
              <Clock :size="14" class="flex-shrink-0" />
              <span class="whitespace-nowrap">{{ classItem.time }}</span>
            </div>
            <Button
              v-if="classItem.isLive"
              variant="primary"
              size="sm"
              class="flex-shrink-0"
            >
              Live
            </Button>
          </div>
        </div>
      </div>
      <div v-if="classes.length === 0" class="text-center py-8">
        <p class="text-body text-text-secondary">No upcoming classes</p>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { ref } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import { Clock, BookOpen } from 'lucide-vue-next'

defineProps({
  classes: {
    type: Array,
    default: () => []
  }
})

const imageErrors = ref({})

const hasValidThumbnail = (classItem) => {
  // Only show image if thumbnail exists and is a valid URL, and hasn't errored
  if (!classItem.thumbnail) return false
  if (typeof classItem.thumbnail !== 'string') return false
  if (!classItem.thumbnail.startsWith('http') && !classItem.thumbnail.startsWith('/')) return false
  if (imageErrors.value[classItem.id]) return false
  return true
}

const handleImageError = (id) => {
  imageErrors.value[id] = true
}
</script>

