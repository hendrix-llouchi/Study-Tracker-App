<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="max-w-2xl mx-auto space-y-6">
      <!-- Progress Indicator - Desktop -->
      <div class="hidden sm:flex items-center justify-between mb-6 lg:mb-8 overflow-x-auto pb-2">
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">
            <Check :size="16" />
          </div>
          <span class="text-body-small text-text-secondary">Profile</span>
        </div>
        <div class="flex-1 h-0.5 bg-primary-green mx-4"></div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">
            <Check :size="16" />
          </div>
          <span class="text-body-small text-text-secondary">Timetable</span>
        </div>
        <div class="flex-1 h-0.5 bg-primary-green mx-4"></div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">3</div>
          <span class="text-body-small text-text-primary font-medium">Results</span>
        </div>
        <div class="flex-1 h-0.5 bg-neutral-gray200 mx-4"></div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="w-8 h-8 bg-neutral-gray200 rounded-full flex items-center justify-center text-neutral-gray500 font-medium text-body-small">4</div>
          <span class="text-body-small text-text-secondary">Preferences</span>
        </div>
      </div>

      <!-- Progress Indicator - Mobile -->
      <div class="sm:hidden mb-4">
        <div class="flex items-center justify-center gap-2 mb-2">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">3</div>
          <span class="text-body font-medium text-text-primary">Step 3 of 4: Results</span>
        </div>
        <div class="w-full bg-neutral-gray200 rounded-full h-2">
          <div class="bg-primary-green h-2 rounded-full" style="width: 75%"></div>
        </div>
      </div>

      <div>
        <h1 class="text-h1 text-text-primary mb-2">Input Baseline Results</h1>
        <p class="text-body text-text-secondary">Enter your current academic results to establish a baseline</p>
      </div>

      <Card padding="lg">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-body-small text-accent-red">{{ error }}</p>
          </div>

          <div class="space-y-4">
            <div
              v-for="(result, index) in form.results"
              :key="index"
              class="p-4 border border-border-default rounded-lg space-y-4"
            >
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-body font-medium text-text-primary">Result {{ index + 1 }}</h4>
                <Button
                  v-if="form.results.length > 1"
                  type="button"
                  variant="ghost"
                  size="sm"
                  @click="removeResult(index)"
                >
                  <Trash2 :size="16" />
                </Button>
              </div>

              <Select
                v-model="result.course"
                label="Course"
                placeholder="Select course"
                :options="courseOptions"
                required
              />

              <Select
                v-model="result.assessmentType"
                label="Assessment Type"
                placeholder="Select type"
                :options="assessmentOptions"
                required
              />

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Input
                  v-model.number="result.score"
                  type="number"
                  label="Score"
                  placeholder="85"
                  required
                />
                <Input
                  v-model.number="result.maxScore"
                  type="number"
                  label="Max Score"
                  placeholder="100"
                  required
                />
              </div>

              <Input
                v-model="result.grade"
                type="text"
                label="Grade"
                placeholder="B+"
                required
              />

              <Input
                v-model.number="result.credits"
                type="number"
                label="Credit Hours"
                placeholder="4"
                required
              />
            </div>
          </div>

          <Button
            type="button"
            variant="secondary"
            size="md"
            @click="addResult"
          >
            <Plus :size="16" class="mr-2" />
            Add Another Result
          </Button>

          <div v-if="calculatedGPA !== null" class="p-4 bg-primary-green-bg rounded-lg">
            <div class="flex items-center justify-between">
              <span class="text-body font-medium text-text-primary">Calculated GPA:</span>
              <span class="text-2xl font-bold text-primary-green">{{ calculatedGPA.toFixed(2) }}</span>
            </div>
          </div>

          <div class="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4 pt-4">
            <Button
              variant="ghost"
              size="lg"
              @click="router.push('/onboarding/timetable')"
              class="w-full sm:w-auto"
            >
              Back
            </Button>
            <Button
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full sm:w-auto sm:ml-auto"
            >
              Continue to Preferences
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/Stores/auth'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'
import Select from '@/Components/Common/Select.vue'
import { Plus, Trash2, Check } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const user = computed(() => authStore.user)

const form = ref({
  results: [
    {
      course: '',
      assessmentType: '',
      score: '',
      maxScore: '',
      grade: '',
      credits: ''
    }
  ]
})

const error = ref('')
const loading = ref(false)

const courseOptions = [
  { value: 'cs201', label: 'Data Structures & Algorithms' },
  { value: 'cs301', label: 'Database Systems' },
  { value: 'cs302', label: 'Computer Networks' },
  { value: 'cs303', label: 'Software Engineering' }
]

const assessmentOptions = [
  { value: 'quiz', label: 'Quiz' },
  { value: 'assignment', label: 'Assignment' },
  { value: 'midterm', label: 'Midterm' },
  { value: 'final', label: 'Final Exam' },
  { value: 'project', label: 'Project' }
]

const calculatedGPA = computed(() => {
  const validResults = form.value.results.filter(r => r.score && r.maxScore && r.credits)
  if (validResults.length === 0) return null

  // Simple GPA calculation (can be improved)
  let totalPoints = 0
  let totalCredits = 0

  validResults.forEach(result => {
    const percentage = (result.score / result.maxScore) * 100
    let points = 0
    
    if (percentage >= 90) points = 4.0
    else if (percentage >= 80) points = 3.5
    else if (percentage >= 70) points = 3.0
    else if (percentage >= 60) points = 2.5
    else points = 2.0

    totalPoints += points * result.credits
    totalCredits += result.credits
  })

  return totalCredits > 0 ? totalPoints / totalCredits : null
})

const addResult = () => {
  form.value.results.push({
    course: '',
    assessmentType: '',
    score: '',
    maxScore: '',
    grade: '',
    credits: ''
  })
}

const removeResult = (index) => {
  form.value.results.splice(index, 1)
}

const handleSubmit = async () => {
  error.value = ''
  loading.value = true

  try {
    // Bypass API call for now
    console.log('Results saved:', form.value)
    router.push('/onboarding/preferences')
  } catch (err) {
    error.value = err.message || 'Failed to save results. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
