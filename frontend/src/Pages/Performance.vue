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

        <div class="lg:col-span-1">
          <BulkUploadCard @upload="handleBulkUpload" />
        </div>
      </div>

      <!-- Historical Comparison -->
      <HistoricalComparison :results="results" />

      <!-- Result Modal -->
      <ResultModal
        v-model="showResultModal"
        :result="editingResult"
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
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import GPACalculator from '@/Components/Performance/GPACalculator.vue'
import WeakAreasCard from '@/Components/Performance/WeakAreasCard.vue'
import PerformanceCharts from '@/Components/Performance/PerformanceCharts.vue'
import ResultsList from '@/Components/Performance/ResultsList.vue'
import BulkUploadCard from '@/Components/Performance/BulkUploadCard.vue'
import HistoricalComparison from '@/Components/Performance/HistoricalComparison.vue'
import ResultModal from '@/Components/Modals/ResultModal.vue'
import { Plus } from 'lucide-vue-next'

const authStore = useAuthStore()
const performanceStore = usePerformanceStore()

const user = computed(() => authStore.user)
const results = computed(() => performanceStore.results)
const currentSemester = ref('Fall 2024')

const showResultModal = ref(false)
const editingResult = ref(null)

onMounted(async () => {
  await performanceStore.fetchResults()
})

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
  console.log('Bulk upload:', file)
  // In real implementation, this would parse the file and add results
  alert('Bulk upload feature will be implemented in Phase 2')
}
</script>
