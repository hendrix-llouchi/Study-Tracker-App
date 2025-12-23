<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">My Courses</h3>
      <Button variant="ghost" size="sm">View All</Button>
    </div>

    <div class="space-y-1">
        <div
          v-for="course in courses"
          :key="course.id"
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 rounded-lg hover:bg-neutral-gray50 transition-colors duration-default cursor-pointer gap-3"
        >
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <div
              :class="courseIconClasses(course.color)"
              class="w-10 h-10 rounded-md flex items-center justify-center flex-shrink-0"
            >
              <BookOpen :size="20" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-body font-medium text-text-primary truncate">{{ course.name }}</p>
              <p class="text-body-small text-text-secondary truncate">{{ course.code }} • {{ course.instructor }}</p>
            </div>
          </div>
          <div class="flex items-center gap-4 justify-between sm:justify-end">
            <ProgressBar :percentage="course.progress" :color="course.color" class="w-20 flex-shrink-0" />
            <Button variant="icon" size="sm" class="flex-shrink-0">
              <ChevronRight :size="16" />
            </Button>
          </div>
        </div>
    </div>
  </Card>
</template>

<script setup>
import { ref } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import ProgressBar from '@/Components/Common/ProgressBar.vue'
import { BookOpen, ChevronRight } from 'lucide-vue-next'

const courses = ref([
  {
    id: 1,
    name: 'Data Structures & Algorithms',
    code: 'CS201',
    instructor: 'Dr. Sarah Johnson',
    color: 'orange',
    progress: 75
  },
  {
    id: 2,
    name: 'Database Systems',
    code: 'CS301',
    instructor: 'Prof. Michael Chen',
    color: 'blue',
    progress: 85
  },
  {
    id: 3,
    name: 'Computer Networks',
    code: 'CS302',
    instructor: 'Dr. Emily Rodriguez',
    color: 'green',
    progress: 60
  },
  {
    id: 4,
    name: 'Software Engineering',
    code: 'CS303',
    instructor: 'Prof. David Kim',
    color: 'purple',
    progress: 90
  }
])

const courseIconClasses = (color) => {
  const colors = {
    orange: 'bg-accent-orange-light text-accent-orange',
    blue: 'bg-secondary-blue-light text-secondary-blue',
    green: 'bg-primary-green-light text-primary-green',
    purple: 'bg-accent-purple-light text-accent-purple'
  }
  return colors[color] || colors.orange
}
</script>

