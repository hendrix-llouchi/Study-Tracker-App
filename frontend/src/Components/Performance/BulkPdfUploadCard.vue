<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-4">Bulk PDF Upload</h3>
    <p class="text-body-small text-text-secondary mb-4">
      Upload multiple PDF files at once. Each PDF will be stored and a database entry will be created.
    </p>
    
    <div
      @drop.prevent="handleDrop"
      @dragover.prevent
      @dragenter.prevent
      :class="dropZoneClasses"
      class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-default mb-4"
      @click="$refs.fileInput.click()"
    >
      <Upload :size="48" class="mx-auto mb-4 text-text-tertiary" />
      <p class="text-body font-medium text-text-primary mb-2">
        Drop PDF files here or click to browse
      </p>
      <p class="text-body-small text-text-secondary mb-4">
        Supports PDF files only (Max 5MB per file)
      </p>
      <input
        ref="fileInput"
        type="file"
        accept=".pdf"
        multiple
        class="hidden"
        @change="handleFileSelect"
      />
      <Button variant="secondary" size="md" @click.stop="$refs.fileInput.click()">
        Choose PDF Files
      </Button>
    </div>

    <!-- Selected Files List -->
    <div v-if="selectedFiles.length > 0" class="mb-4 space-y-2">
      <div
        v-for="(file, index) in selectedFiles"
        :key="index"
        class="p-3 bg-primary-green-bg rounded-lg flex items-center justify-between"
      >
        <div class="flex items-center gap-2 flex-1 min-w-0">
          <FileText :size="20" class="text-primary-green flex-shrink-0" />
          <span class="text-body text-text-primary truncate">{{ file.name }}</span>
          <span class="text-body-small text-text-secondary flex-shrink-0">
            ({{ formatFileSize(file.size) }})
          </span>
        </div>
        <Button variant="ghost" size="sm" @click="removeFile(index)" :disabled="uploading">
          <X :size="16" />
        </Button>
      </div>
    </div>

    <!-- Progress Bar -->
    <div v-if="uploading" class="mt-4 space-y-2">
      <div class="flex items-center justify-between text-body-small text-text-secondary mb-2">
        <div class="flex items-center gap-2">
          <LoadingSpinner size="sm" />
          <span>{{ uploadStatus || `Uploading ${uploadedCount} of ${totalFiles} files...` }}</span>
        </div>
        <span class="font-medium">{{ uploadProgress }}%</span>
      </div>
      <div class="w-full bg-neutral-gray100 rounded-full h-2 overflow-hidden">
        <div
          class="bg-primary-green h-2 rounded-full transition-all duration-300"
          :style="{ width: uploadProgress + '%' }"
        ></div>
      </div>
      <p v-if="uploadStatus && uploadStatus.includes('Analyzing')" class="text-body-small text-text-secondary mt-2">
        {{ uploadStatus }}
      </p>
    </div>

    <!-- Upload Button -->
    <div v-if="selectedFiles.length > 0 && !uploading" class="mt-4">
      <Button
        variant="primary"
        size="md"
        class="w-full"
        @click="handleUpload"
        :disabled="selectedFiles.length === 0"
      >
        Upload {{ selectedFiles.length }} PDF{{ selectedFiles.length > 1 ? 's' : '' }}
      </Button>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-body-small text-red-600">{{ error }}</p>
    </div>

    <!-- Success Message -->
    <div v-if="uploadComplete && !uploading" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
      <p class="text-body-small text-green-600">
        Successfully uploaded {{ uploadedCount }} PDF file(s)!
      </p>
      <p v-if="uploadErrors.length > 0" class="text-body-small text-orange-600 mt-2">
        {{ uploadErrors.length }} file(s) failed to upload.
      </p>
    </div>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import LoadingSpinner from '@/Components/Common/LoadingSpinner.vue'
import { Upload, FileText, X } from 'lucide-vue-next'

const emit = defineEmits(['upload'])

const fileInput = ref(null)
const selectedFiles = ref([])
const uploading = ref(false)
const isDragging = ref(false)
const uploadProgress = ref(0)
const uploadedCount = ref(0)
const totalFiles = ref(0)
const uploadStatus = ref('')
const error = ref('')
const uploadComplete = ref(false)
const uploadErrors = ref([])

const dropZoneClasses = computed(() => {
  return isDragging.value
    ? 'border-primary-green bg-primary-green-bg'
    : 'border-border-medium hover:border-border-default'
})

const handleDrop = (event) => {
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files)
  addFiles(files)
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  addFiles(files)
}

const addFiles = (files) => {
  error.value = ''
  uploadComplete.value = false
  uploadErrors.value = []

  const pdfFiles = files.filter(file => {
    const fileName = file.name.toLowerCase()
    const isValidPdf = fileName.endsWith('.pdf')
    
    if (!isValidPdf) {
      error.value = `Invalid file type. Only PDF files are allowed. Skipped: ${file.name}`
      return false
    }

    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
      error.value = `File size exceeds 5MB limit. Skipped: ${file.name}`
      return false
    }

    return true
  })

  // Add new files to the list (avoid duplicates by name)
  pdfFiles.forEach(file => {
    const exists = selectedFiles.value.some(f => f.name === file.name && f.size === file.size)
    if (!exists) {
      selectedFiles.value.push(file)
    }
  })
}

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
  error.value = ''
  uploadComplete.value = false
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const handleUpload = async () => {
  if (selectedFiles.value.length === 0) return

  uploading.value = true
  uploadProgress.value = 0
  uploadedCount.value = 0
  totalFiles.value = selectedFiles.value.length
  uploadStatus.value = 'Preparing upload...'
  error.value = ''
  uploadComplete.value = false
  uploadErrors.value = []

  try {
    emit('upload', selectedFiles.value, {
      onProgress: (progress, current, total, status) => {
        uploadProgress.value = progress
        uploadedCount.value = current
        totalFiles.value = total
        uploadStatus.value = status || `Uploading ${current} of ${total} files...`
      },
      onComplete: (successCount, errors) => {
        uploadComplete.value = true
        uploadedCount.value = successCount
        uploadErrors.value = errors || []
        uploadStatus.value = `Upload complete: ${successCount} successful, ${errors?.length || 0} failed`
        
        // Clear selected files after successful upload
        if (errors?.length === 0) {
          setTimeout(() => {
            selectedFiles.value = []
            uploadComplete.value = false
          }, 3000)
        }
      },
      onError: (errorMessage) => {
        error.value = errorMessage
      }
    })
  } catch (err) {
    error.value = err.message || 'Upload failed'
    uploading.value = false
  }
}
</script>
