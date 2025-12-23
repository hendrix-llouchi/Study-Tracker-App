<template>
  <Card padding="default">
    <h3 class="text-h3 text-text-primary mb-4">Bulk Upload</h3>
    <p class="text-body-small text-text-secondary mb-4">
      Upload multiple results at once using CSV or PDF files
    </p>
    
    <div
      @drop.prevent="handleDrop"
      @dragover.prevent
      @dragenter.prevent
      :class="dropZoneClasses"
      class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-default mb-4"
    >
      <Upload :size="48" class="mx-auto mb-4 text-text-tertiary" />
      <p class="text-body font-medium text-text-primary mb-2">
        Drop your file here or click to browse
      </p>
      <p class="text-body-small text-text-secondary mb-4">
        Supports CSV, PDF (Max 10MB)
      </p>
      <input
        ref="fileInput"
        type="file"
        accept=".csv,.pdf"
        class="hidden"
        @change="handleFileSelect"
      />
      <Button variant="secondary" size="md" @click="$refs.fileInput.click()">
        Choose File
      </Button>
    </div>

    <div v-if="uploadedFile" class="p-3 bg-primary-green-bg rounded-lg flex items-center justify-between">
      <div class="flex items-center gap-2">
        <FileText :size="20" class="text-primary-green" />
        <span class="text-body text-text-primary">{{ uploadedFile.name }}</span>
      </div>
      <Button variant="ghost" size="sm" @click="removeFile">
        <X :size="16" />
      </Button>
    </div>

    <div v-if="uploading" class="mt-4">
      <div class="flex items-center gap-2 text-body-small text-text-secondary">
        <LoadingSpinner size="sm" />
        <span>Processing file...</span>
      </div>
    </div>

    <div class="mt-4 pt-4 border-t border-border-default">
      <p class="text-body-small font-semibold text-text-secondary mb-2">CSV Format Example:</p>
      <div class="bg-neutral-gray50 rounded-lg p-3 text-body-small font-mono text-text-secondary overflow-x-auto">
        Course,Score,MaxScore,Grade,CreditHours,Semester,AssessmentType<br/>
        Data Structures,85,100,B+,3,Fall 2024,Final<br/>
        Database Systems,92,100,A,3,Fall 2024,Midterm
      </div>
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
const uploadedFile = ref(null)
const uploading = ref(false)
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
  
  // Simulate file processing
  setTimeout(() => {
    uploading.value = false
    emit('upload', file)
  }, 2000)
}

const removeFile = () => {
  uploadedFile.value = null
  uploading.value = false
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}
</script>

