<template>
  <Card padding="default">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-h3 text-text-primary">Academic Results</h3>
      <Button variant="primary" size="md" @click="$emit('add-result')">
        <Plus :size="16" class="mr-2" />
        Add Result
      </Button>
    </div>

    <div v-if="results.length === 0" class="text-center py-12">
      <FileText class="w-12 h-12 text-text-tertiary mx-auto mb-4" />
      <p class="text-body text-text-secondary mb-2">No results added yet</p>
      <p class="text-body-small text-text-tertiary">Start tracking your academic performance by adding your first result</p>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-border-default">
            <th class="text-left py-3 px-4 text-body-small font-semibold text-text-secondary">Course</th>
            <th class="text-left py-3 px-4 text-body-small font-semibold text-text-secondary">Type</th>
            <th class="text-left py-3 px-4 text-body-small font-semibold text-text-secondary">Score</th>
            <th class="text-left py-3 px-4 text-body-small font-semibold text-text-secondary">Grade</th>
            <th class="text-left py-3 px-4 text-body-small font-semibold text-text-secondary">Semester</th>
            <th class="text-left py-3 px-4 text-body-small font-semibold text-text-secondary">Date</th>
            <th class="text-right py-3 px-4 text-body-small font-semibold text-text-secondary">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="result in results"
            :key="result.id"
            class="border-b border-border-default hover:bg-neutral-gray50 transition-colors"
          >
            <td class="py-3 px-4">
              <p class="text-body font-medium text-text-primary">{{ result.course }}</p>
            </td>
            <td class="py-3 px-4">
              <Badge :variant="getAssessmentBadgeVariant(result.assessmentType)" size="sm">
                {{ formatAssessmentType(result.assessmentType) }}
              </Badge>
            </td>
            <td class="py-3 px-4">
              <p class="text-body text-text-primary">{{ result.score }} / {{ result.maxScore }}</p>
            </td>
            <td class="py-3 px-4">
              <span :class="getGradeColor(result.grade)" class="text-body font-semibold">
                {{ result.grade }}
              </span>
            </td>
            <td class="py-3 px-4">
              <p class="text-body-small text-text-secondary">{{ result.semester }}</p>
            </td>
            <td class="py-3 px-4">
              <p class="text-body-small text-text-secondary">{{ formatDate(result.date) }}</p>
            </td>
            <td class="py-3 px-4">
              <div class="flex items-center justify-end gap-2">
                <Button variant="ghost" size="sm" @click="$emit('edit-result', result)">
                  <Edit :size="16" />
                </Button>
                <Button variant="ghost" size="sm" @click="$emit('delete-result', result.id)">
                  <Trash2 :size="16" />
                </Button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </Card>
</template>

<script setup>
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Badge from '@/Components/Common/Badge.vue'
import { Plus, FileText, Edit, Trash2 } from 'lucide-vue-next'

defineProps({
  results: {
    type: Array,
    default: () => []
  }
})

defineEmits(['add-result', 'edit-result', 'delete-result'])

const formatAssessmentType = (type) => {
  const types = {
    quiz: 'Quiz',
    midterm: 'Midterm',
    final: 'Final',
    assignment: 'Assignment',
    project: 'Project'
  }
  return types[type] || type
}

const getAssessmentBadgeVariant = (type) => {
  const variants = {
    quiz: 'info',
    midterm: 'warning',
    final: 'error',
    assignment: 'success',
    project: 'info'
  }
  return variants[type] || 'info'
}

const getGradeColor = (grade) => {
  const gradeNum = parseFloat(grade)
  if (gradeNum >= 3.7) return 'text-primary-green'
  if (gradeNum >= 3.0) return 'text-secondary-blue'
  if (gradeNum >= 2.0) return 'text-accent-orange'
  return 'text-accent-red'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

