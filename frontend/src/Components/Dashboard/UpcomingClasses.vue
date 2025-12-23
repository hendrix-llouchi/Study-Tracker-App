<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-5">Upcoming Classes</h3>
    <div class="space-y-3">
      <div
        v-for="classItem in classes"
        :key="classItem.id"
        class="bg-neutral-gray50 rounded-xl p-4 flex flex-col sm:flex-row gap-3 cursor-pointer hover:bg-neutral-gray100 hover:transform hover:translate-x-1 transition-all duration-default"
      >
        <img
          :src="classItem.thumbnail || 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=100&h=75&fit=crop'"
          :alt="classItem.courseName"
          class="w-full sm:w-20 h-32 sm:h-15 rounded-lg object-cover flex-shrink-0"
        />
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
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import { Clock } from 'lucide-vue-next'

defineProps({
  classes: {
    type: Array,
    default: () => []
  }
})
</script>

