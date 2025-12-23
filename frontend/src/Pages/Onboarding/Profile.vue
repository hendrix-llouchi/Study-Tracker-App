<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="max-w-2xl mx-auto space-y-4 lg:space-y-6">
      <!-- Progress Indicator - Desktop -->
      <div class="hidden sm:flex items-center justify-between mb-6 lg:mb-8 overflow-x-auto pb-2">
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">1</div>
          <span class="text-body-small text-text-secondary">Profile</span>
        </div>
        <div class="flex-1 h-0.5 bg-neutral-gray200 mx-4"></div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="w-8 h-8 bg-neutral-gray200 rounded-full flex items-center justify-center text-neutral-gray500 font-medium text-body-small">2</div>
          <span class="text-body-small text-text-secondary">Timetable</span>
        </div>
        <div class="flex-1 h-0.5 bg-neutral-gray200 mx-4"></div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="w-8 h-8 bg-neutral-gray200 rounded-full flex items-center justify-center text-neutral-gray500 font-medium text-body-small">3</div>
          <span class="text-body-small text-text-secondary">Results</span>
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
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">1</div>
          <span class="text-body font-medium text-text-primary">Step 1 of 4: Profile</span>
        </div>
        <div class="w-full bg-neutral-gray200 rounded-full h-2">
          <div class="bg-primary-green h-2 rounded-full" style="width: 25%"></div>
        </div>
      </div>

      <div>
        <h1 class="text-h1 text-text-primary mb-2">Complete Your Profile</h1>
        <p class="text-body text-text-secondary">Tell us about yourself to get started</p>
      </div>

      <Card padding="lg">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-body-small text-accent-red">{{ error }}</p>
          </div>

          <Input
            v-model="form.name"
            type="text"
            label="Full Name"
            placeholder="John Doe"
            required
            :error="errors.name"
          />

          <Input
            v-model="form.university"
            type="text"
            label="University"
            placeholder="University of Technology"
            required
            :error="errors.university"
          />

          <Select
            v-model="form.semester"
            label="Current Semester"
            placeholder="Select your semester"
            :options="semesterOptions"
            required
            :error="errors.semester"
          />

          <div>
            <label class="block text-body-small text-text-secondary mb-1.5">
              Courses <span class="text-accent-red">*</span>
            </label>
            <div class="space-y-3">
              <div
                v-for="(course, index) in form.courses"
                :key="index"
                class="flex flex-col sm:flex-row gap-3"
              >
                <Input
                  v-model="course.name"
                  type="text"
                  placeholder="Course name"
                  class="flex-1"
                  required
                />
                <Input
                  v-model="course.code"
                  type="text"
                  placeholder="Code"
                  class="w-full sm:w-32"
                  required
                />
                <Button
                  v-if="form.courses.length > 1"
                  type="button"
                  variant="ghost"
                  size="sm"
                  class="self-start sm:self-auto"
                  @click="removeCourse(index)"
                >
                  <Trash2 :size="16" />
                </Button>
              </div>
            </div>
            <Button
              type="button"
              variant="secondary"
              size="md"
              class="mt-3 w-full sm:w-auto"
              @click="addCourse"
            >
              <Plus :size="16" class="mr-2" />
              Add Course
            </Button>
          </div>

          <div class="flex gap-4 pt-4">
            <Button
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full"
            >
              Continue to Timetable
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { computed } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'
import Select from '@/Components/Common/Select.vue'
import { Plus, Trash2 } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const user = computed(() => authStore.user)

const form = ref({
  name: user.value?.name || '',
  university: '',
  semester: '',
  courses: [
    { name: '', code: '' }
  ]
})

const errors = ref({})
const error = ref('')
const loading = ref(false)

const semesterOptions = [
  { value: '1st', label: '1st Semester' },
  { value: '2nd', label: '2nd Semester' },
  { value: '3rd', label: '3rd Semester' },
  { value: '4th', label: '4th Semester' },
  { value: '5th', label: '5th Semester' },
  { value: '6th', label: '6th Semester' },
  { value: '7th', label: '7th Semester' },
  { value: '8th', label: '8th Semester' }
]

const addCourse = () => {
  form.value.courses.push({ name: '', code: '' })
}

const removeCourse = (index) => {
  form.value.courses.splice(index, 1)
}

const handleSubmit = async () => {
  errors.value = {}
  error.value = ''
  loading.value = true

  // Validation
  if (!form.value.name) errors.value.name = 'Name is required'
  if (!form.value.university) errors.value.university = 'University is required'
  if (!form.value.semester) errors.value.semester = 'Semester is required'
  
  const invalidCourses = form.value.courses.filter(c => !c.name || !c.code)
  if (invalidCourses.length > 0) {
    error.value = 'Please fill in all course fields'
    loading.value = false
    return
  }

  try {
    // Bypass API call for now
    console.log('Profile saved:', form.value)
    router.push('/onboarding/timetable')
  } catch (err) {
    error.value = err.message || 'Failed to save profile. Please try again.'
    if (err.errors) {
      errors.value = err.errors
    }
  } finally {
    loading.value = false
  }
}
</script>
