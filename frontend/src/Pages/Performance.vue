<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="space-y-4 lg:space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-h1 text-text-primary mb-2">Academic Performance</h1>
          <p class="text-body text-text-secondary">Track your grades and academic progress over time</p>
        </div>
        <Button
          variant="primary"
          size="lg"
          @click="showResultModal = true"
        >
          <Plus :size="20" class="mr-2" />
          Add Result
        </Button>
      </div>

      <!-- GPA Calculator -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
        <div class="lg:col-span-1">
          <GPACalculator :results="results" :current-semester="currentSemester" />
        </div>

        <!-- Weak Areas -->
        <div class="lg:col-span-2">
          <WeakAreasCard :results="results" :threshold="75" />
        </div>
      </div>

      <!-- Performance Charts -->
      <PerformanceCharts :results="results" />

      <!-- Results List and Bulk Upload -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
        <div class="lg:col-span-2">
          <ResultsList
            :results="results"
            @add-result="showResultModal = true"
            @edit-result="handleEditResult"
            @delete-result="handleDeleteResult"
          />
        </div>

        <div class="lg:col-span-1 space-y-4">
          <BulkUploadCard @upload="handleBulkUpload" />
          <BulkPdfUploadCard @upload="handleBulkPdfUpload" />
        </div>
      </div>

      <!-- Historical Comparison -->
      <HistoricalComparison :results="results" />

      <!-- Result Modal -->
      <ResultModal
        v-model="showResultModal"
        :result="editingResult"
        :courses="courses"
        @save="handleSaveResult"
        @close="handleCloseModal"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { usePerformanceStore } from '@/Stores/performance'
import { getErrorMessage } from '@/utils/errorHandler'
import api from '@/services/api'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import GPACalculator from '@/Components/Performance/GPACalculator.vue'
import WeakAreasCard from '@/Components/Performance/WeakAreasCard.vue'
import PerformanceCharts from '@/Components/Performance/PerformanceCharts.vue'
import ResultsList from '@/Components/Performance/ResultsList.vue'
import BulkUploadCard from '@/Components/Performance/BulkUploadCard.vue'
import BulkPdfUploadCard from '@/Components/Performance/BulkPdfUploadCard.vue'
import HistoricalComparison from '@/Components/Performance/HistoricalComparison.vue'
import ResultModal from '@/Components/Modals/ResultModal.vue'
import { Plus } from 'lucide-vue-next'

const authStore = useAuthStore()
const performanceStore = usePerformanceStore()

const user = computed(() => authStore.user)
const results = computed(() => performanceStore.results)
const currentSemester = ref('Fall 2024')
const courses = ref([])

const showResultModal = ref(false)
const editingResult = ref(null)

onMounted(async () => {
  await Promise.all([
    performanceStore.fetchResults(),
    fetchCourses()
  ])
})

const fetchCourses = async () => {
  try {
    const response = await api.get('/courses')
    if (response.success && response.data) {
      // Handle both array and paginated response
      courses.value = Array.isArray(response.data) 
        ? response.data 
        : (response.data.courses || response.data.data || [])
    }
  } catch (error) {
    console.error('Failed to fetch courses:', error)
  }
}

const handleEditResult = (result) => {
  editingResult.value = result
  showResultModal.value = true
}

const handleDeleteResult = async (id) => {
  if (confirm('Are you sure you want to delete this result?')) {
    await performanceStore.deleteResult(id)
  }
}

const handleSaveResult = async (resultData) => {
  if (editingResult.value) {
    await performanceStore.updateResult(editingResult.value.id, resultData)
  } else {
    await performanceStore.addResult(resultData)
  }
  handleCloseModal()
}

const handleCloseModal = () => {
  showResultModal.value = false
  editingResult.value = null
}

const handleBulkUpload = async (file) => {
  try {
    await performanceStore.bulkUpload(file)
    // Results will be automatically refreshed in the store
    alert('Results uploaded successfully!')
  } catch (error) {
    const errorMessage = getErrorMessage(error)
    console.error('Bulk upload failed:', error)
    
    // Show detailed errors if available
    if (error.errors?.row_errors && Array.isArray(error.errors.row_errors)) {
      const errorDetails = error.errors.row_errors.slice(0, 10).join('\n')
      const moreErrors = error.errors.row_errors.length > 10 
        ? `\n\n... and ${error.errors.row_errors.length - 10} more error(s)` 
        : ''
      alert(`Upload failed with the following errors:\n\n${errorDetails}${moreErrors}`)
    } else if (error.errors) {
      // Show all error details for debugging
      const errorDetails = JSON.stringify(error.errors, null, 2)
      console.error('Full error details:', errorDetails)
      
      // Check for missing columns error
      if (error.errors.missing_columns) {
        alert(`Upload failed: Missing required columns: ${error.errors.missing_columns.join(', ')}\n\n${error.message || ''}`)
      } else if (error.errors.detected_columns) {
        alert(`Upload failed: Could not detect required columns.\n\nDetected columns: ${error.errors.detected_columns.join(', ') || 'none'}\n\n${error.message || ''}`)
      } else {
        alert(`${errorMessage}\n\nFull error details logged to console.`)
      }
    } else {
      alert(errorMessage || 'Failed to upload results. Please check the file format.')
    }
  }
}

const handleBulkPdfUpload = async (files, callbacks) => {
  try {
    // Use the new bulkStorePdfs method that extracts data from PDFs
    await performanceStore.bulkStorePdfs(files, callbacks)
  } catch (error) {
    const errorMessage = getErrorMessage(error)
    console.error('Bulk PDF upload failed:', error)
    
    // Errors are already handled by the component through callbacks
    if (callbacks?.onError) {
      callbacks.onError(errorMessage)
    }
  }
}
</script>
