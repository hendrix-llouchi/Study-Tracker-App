<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="max-w-4xl mx-auto space-y-4 lg:space-y-6">
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
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">2</div>
          <span class="text-body-small text-text-primary font-medium">Timetable</span>
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
          <div class="w-8 h-8 bg-primary-green rounded-full flex items-center justify-center text-white font-medium text-body-small">2</div>
          <span class="text-body font-medium text-text-primary">Step 2 of 4: Timetable</span>
        </div>
        <div class="w-full bg-neutral-gray200 rounded-full h-2">
          <div class="bg-primary-green h-2 rounded-full" style="width: 50%"></div>
        </div>
      </div>

      <div>
        <h1 class="text-h1 text-text-primary mb-2">Upload Your Timetable</h1>
        <p class="text-body text-text-secondary">Upload an image or PDF, or manually enter your class schedule</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- Upload Option -->
        <Card padding="lg">
          <h3 class="text-h3 text-text-primary mb-4">Upload File</h3>
          <div
            @drop.prevent="handleDrop"
            @dragover.prevent
            @dragenter.prevent
            :class="dropZoneClasses"
            class="border-2 border-dashed rounded-xl p-6 sm:p-8 text-center cursor-pointer transition-all duration-default"
          >
            <Upload :size="48" class="mx-auto mb-4 text-text-tertiary" />
            <p class="text-body font-medium text-text-primary mb-2">
              Drop your timetable here or click to browse
            </p>
            <p class="text-body-small text-text-secondary mb-4">
              Supports PDF, PNG, JPG (Max 10MB)
            </p>
            <input
              ref="fileInput"
              type="file"
              accept=".pdf,.png,.jpg,.jpeg"
              class="hidden"
              @change="handleFileSelect"
            />
            <Button variant="secondary" size="md" @click="$refs.fileInput.click()">
              Choose File
            </Button>
          </div>
          <div v-if="uploadedFile" class="mt-4 p-3 bg-primary-green-bg rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
              <FileText :size="20" class="text-primary-green flex-shrink-0" />
              <span class="text-body text-text-primary truncate">{{ uploadedFile.name }}</span>
            </div>
            <Button variant="ghost" size="sm" class="flex-shrink-0" @click="removeFile">
              <X :size="16" />
            </Button>
          </div>
          <div v-if="uploading" class="mt-4">
            <div class="flex items-center gap-2 text-body-small text-text-secondary">
              <LoadingSpinner size="sm" />
              <span>Processing timetable with OCR...</span>
            </div>
          </div>
        </Card>

        <!-- Manual Entry Option -->
        <Card padding="lg">
          <h3 class="text-h3 text-text-primary mb-4">Manual Entry</h3>
          <p class="text-body-small text-text-secondary mb-4">
            Or enter your timetable manually
          </p>
          <Button
            variant="secondary"
            size="lg"
            class="w-full"
            @click="showManualEntry = true"
          >
            Enter Manually
          </Button>
        </Card>
      </div>

      <div class="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4">
        <Button
          variant="ghost"
          size="lg"
          @click="router.push('/onboarding/profile')"
          class="w-full sm:w-auto"
        >
          Back
        </Button>
        <Button
          variant="primary"
          size="lg"
          :loading="loading"
          :disabled="loading || (!uploadedFile && !manualEntries.length)"
          class="w-full sm:w-auto sm:ml-auto"
          @click="handleSubmit"
        >
          Continue to Results
        </Button>
      </div>
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
import LoadingSpinner from '@/Components/Common/LoadingSpinner.vue'
import { Upload, FileText, X, Check } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const user = computed(() => authStore.user)

const fileInput = ref(null)
const uploadedFile = ref(null)
const uploading = ref(false)
const showManualEntry = ref(false)
const manualEntries = ref([])
const loading = ref(false)
const isDragging = ref(false)

const dropZoneClasses = computed(() => {
  return isDragging.value
    ? 'border-primary-green bg-primary-green-bg'
    : 'border-border-medium hover:border-border-default'
})

const handleDrop = (event) => {
  isDragging.value = false
  const files = event.dataTransfer.files
  if (files.length > 0) {
    processFile(files[0])
  }
}

const handleFileSelect = (event) => {
  const files = event.target.files
  if (files.length > 0) {
    processFile(files[0])
  }
}

const processFile = (file) => {
  if (file.size > 10 * 1024 * 1024) {
    alert('File size must be less than 10MB')
    return
  }
  uploadedFile.value = file
  uploading.value = true
  
  // Simulate OCR processing
  setTimeout(() => {
    uploading.value = false
  }, 2000)
}

const removeFile = () => {
  uploadedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleSubmit = async () => {
  loading.value = true
  try {
    // Bypass API call for now
    console.log('Timetable saved:', { uploadedFile: uploadedFile.value, manualEntries: manualEntries.value })
    router.push('/onboarding/results')
  } catch (err) {
    alert(err.message || 'Failed to save timetable')
  } finally {
    loading.value = false
  }
}
</script>
