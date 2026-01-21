<template>
  <AppLayout :user="user">
    <div class="max-w-[1600px] mx-auto space-y-10 py-8 relative">
      
      <!-- HERO: Title & Primary Actions -->
      <section class="flex flex-col md:flex-row md:items-end justify-between gap-8 px-6 sm:px-0">
        <div class="space-y-4 animate-in fade-in slide-in-from-left-8 duration-700">
          <div class="flex items-center gap-2 text-strap-indigo font-black uppercase tracking-[0.3em] text-[10px]">
            <BarChart3 :size="16" />
            Performance Insight v4.0
          </div>
          <h1 class="text-4xl lg:text-6xl font-black text-text-main dark:text-white tracking-tighter leading-none">
            Scale Your <br/> 
            <span class="strap-gradient-text">Academic Velocity.</span>
          </h1>
        </div>

        <div class="flex items-center gap-3 animate-in fade-in slide-in-from-right-8 duration-700">
          <Button variant="secondary" size="lg" class="liquid-glass border-none px-6 py-4 rounded-2xl font-black tracking-tight dark:text-white" @click="showResultModal = true">
            <Plus :size="18" class="mr-2" />
            Add Record
          </Button>
        </div>
      </section>

      <!-- PULSE: Top-level Intelligence Strip -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-6 sm:px-0">
        <div v-for="stat in pulseStats" :key="stat.label" class="liquid-glass p-6 rounded-3xl border-white/40 dark:border-white/5 group hover:scale-[1.02] transition-all duration-300">
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">{{ stat.label }}</span>
              <p class="text-3xl font-black text-text-main dark:text-white tracking-tighter">{{ stat.value }}</p>
            </div>
            <div :class="`p-3 rounded-2xl ${stat.bg} ${stat.color} shadow-lg shadow-black/5`">
              <component :is="stat.icon" :size="20" />
            </div>
          </div>
          <div class="mt-4 flex items-center gap-2">
            <TrendingUp :size="12" class="text-emerald-500" />
            <span class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest">{{ stat.trend }} vs. Last Term</span>
          </div>
        </div>
      </section>

      <!-- INTELLIGENCE BENTO GRID -->
      <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 px-6 sm:px-0">
        <!-- Main Velocity Map (Chart) -->
        <div class="lg:col-span-8">
           <PerformanceCharts :results="results" mode="velocity" />
        </div>

        <!-- GPA Engine (Projection) -->
        <div class="lg:col-span-4">
           <GPACalculator :results="results" :current-semester="currentSemester" />
        </div>
      </section>

      <!-- DATA HUB -->
      <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 px-6 sm:px-0">
        <!-- Records Center (Left/Wide) -->
        <div class="lg:col-span-8 space-y-6">
          <div class="flex items-center justify-between px-4">
             <h2 class="text-2xl font-black text-text-main dark:text-white tracking-tight flex items-center gap-3 font-outfit">
               <List :size="24" class="text-strap-indigo" />
               Historical Records
             </h2>
          </div>
          <div class="liquid-glass rounded-[2rem] overflow-hidden border-white/40 dark:border-white/5 shadow-2xl transition-all duration-500">
             <ResultsList
               :results="results"
               @add-result="showResultModal = true"
               @edit-result="handleEditResult"
               @delete-result="handleDeleteResult"
             />
          </div>
        </div>

        <!-- Operations Console (Right) -->
        <div class="lg:col-span-4 space-y-8">
          <div class="px-4">
             <h2 class="text-2xl font-black text-text-main dark:text-white tracking-tight flex items-center gap-3 font-outfit">
               <Zap :size="24" class="text-strap-amber" />
               Operations
             </h2>
          </div>
          
          <div class="space-y-6">
            <BulkPdfUploadCard ref="bulkPdfCard" @upload="handleBulkPdfUpload" />
            
            <div class="spatial-card liquid-glass p-8 space-y-6 bg-strap-indigo/5 dark:bg-strap-indigo/10 border-strap-indigo/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-strap-indigo flex items-center justify-center shadow-lg shadow-strap-indigo/30">
                  <BrainCircuit class="text-white" :size="20" />
                </div>
                <span class="text-sm font-black text-text-main dark:text-white uppercase tracking-widest">Growth Engine Hint</span>
              </div>
              <p class="text-[11px] font-medium text-text-muted leading-relaxed italic">
                "Target a <span class="text-strap-indigo font-bold">3.5 GPA</span> this semester to stay in the top 5% of your faculty. I suggest focusing on your upcoming Midterms."
              </p>
            </div>
          </div>
        </div>
      </section>

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
import api from '@/services/api'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import GPACalculator from '@/Components/Performance/GPACalculator.vue'
import PerformanceCharts from '@/Components/Performance/PerformanceCharts.vue'
import ResultsList from '@/Components/Performance/ResultsList.vue'
import BulkPdfUploadCard from '@/Components/Performance/BulkPdfUploadCard.vue'
import ResultModal from '@/Components/Modals/ResultModal.vue'
import { 
  Plus, 
  BarChart3, 
  Upload, 
  Activity, 
  Zap, 
  List, 
  BrainCircuit,
  TrendingUp,
  Target,
  GraduationCap,
  Clock
} from 'lucide-vue-next'

const authStore = useAuthStore()
const performanceStore = usePerformanceStore()

const user = computed(() => authStore.user)
const results = computed(() => performanceStore.results)
const currentSemester = ref('Semester 1 2024')
const courses = ref([])

const showResultModal = ref(false)
const editingResult = ref(null)
const bulkPdfCard = ref(null)

const gradeToPoints = { 'A+': 4.0, 'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7, 'C+': 2.3, 'C': 2.0, 'C-': 1.7, 'D+': 1.3, 'D': 1.0, 'F': 0.0 }
const totalCredits = computed(() => results.value.reduce((sum, r) => sum + (r.creditHours || 0), 0))
const currentGPA = computed(() => {
  const totalPoints = results.value.reduce((sum, r) => sum + ((gradeToPoints[r.grade] || 0) * (r.creditHours || 0)), 0)
  return totalCredits.value === 0 ? 0 : (totalPoints / totalCredits.value).toFixed(2)
})

const pulseStats = computed(() => [
  { label: 'Current GPA', value: currentGPA.value, icon: Target, bg: 'bg-strap-indigo/10', color: 'text-strap-indigo', trend: '+4.2%' },
  { label: 'Total Credits', value: totalCredits.value, icon: GraduationCap, bg: 'bg-strap-violet/10', color: 'text-strap-violet', trend: 'On Track' },
  { label: 'Completed', value: `${results.value.length}`, icon: Activity, bg: 'bg-strap-amber/10', color: 'text-strap-amber', trend: '+2 this week' },
  { label: 'Time Tracked', value: '45h', icon: Clock, bg: 'bg-strap-indigo/5', color: 'text-strap-indigo', trend: '85% active' }
])

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
      courses.value = Array.isArray(response.data) ? response.data : (response.data.courses || [])
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
  if (confirm('Permanently delete this record?')) {
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

const handleBulkPdfUpload = async (files, callbacks) => {
  try {
    await performanceStore.bulkStorePdfs(files, callbacks)
  } catch (error) {
    console.error('Bulk PDF upload failed:', error)
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@900&display=swap');
.font-outfit { font-family: 'Outfit', sans-serif; }
</style>
