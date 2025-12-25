<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="space-y-4 lg:space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-h1 text-text-primary mb-2">Study Planning</h1>
          <p class="text-body text-text-secondary">Create and manage your daily study plans</p>
        </div>
        <div class="flex items-center gap-2">
          <Select
            v-model="viewMode"
            :options="viewModeOptions"
            class="w-32"
          />
          <Button
            variant="primary"
            size="lg"
            @click="showPlanModal = true"
          >
            <Plus :size="20" class="mr-2" />
            New Plan
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-6">
        <!-- Calendar -->
        <div class="lg:col-span-1">
          <PlanningCalendar
            :selected-date="selectedDate"
            :plans="plans"
            @date-selected="handleDateSelected"
          />
        </div>

        <!-- Daily Plan View -->
        <div class="lg:col-span-2">
          <DailyPlanView
            :plans="dailyPlans"
            :date="selectedDate"
            @add-plan="showPlanModal = true"
            @complete="handleCompletePlan"
            @update="handleUpdatePlan"
            @delete="handleDeletePlan"
            @carry-over="handleCarryOver"
          />
        </div>

        <!-- Statistics -->
        <div class="lg:col-span-1">
          <PlanStatistics :statistics="statistics" />
        </div>
      </div>

      <!-- Plan Modal -->
      <PlanModal
        v-model="showPlanModal"
        :courses="courses"
        :plan="editingPlan"
        @save="handleSavePlan"
        @close="handleCloseModal"
      />

      <!-- Carry Over Modal -->
      <CarryOverModal
        v-model="showCarryOverModal"
        :incomplete-tasks="incompletePlansForDate"
        :from-date="carryOverFromDate"
        @carry-over="handleConfirmCarryOver"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { usePlanningStore } from '@/Stores/planning'
import { getErrorMessage } from '@/utils/errorHandler'
import api from '@/services/api'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import Select from '@/Components/Common/Select.vue'
import PlanningCalendar from '@/Components/Planning/PlanningCalendar.vue'
import DailyPlanView from '@/Components/Planning/DailyPlanView.vue'
import PlanStatistics from '@/Components/Planning/PlanStatistics.vue'
import PlanModal from '@/Components/Modals/PlanModal.vue'
import CarryOverModal from '@/Components/Planning/CarryOverModal.vue'
import { Plus } from 'lucide-vue-next'

const authStore = useAuthStore()
const planningStore = usePlanningStore()

const user = computed(() => authStore.user)

const viewMode = ref('day')
const selectedDate = ref(new Date().toISOString().split('T')[0])
const showPlanModal = ref(false)
const showCarryOverModal = ref(false)
const editingPlan = ref(null)
const carryOverFromDate = ref('')
const courses = ref([])

const viewModeOptions = [
  { value: 'day', label: 'Day View' },
  { value: 'week', label: 'Week View' }
]

const plans = computed(() => planningStore.plans)
const dailyPlans = computed(() => planningStore.dailyPlans)
const statistics = computed(() => planningStore.statistics)
const incompletePlansForDate = computed(() => {
  return plans.value.filter(p => p.date === carryOverFromDate.value && !p.completed)
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

onMounted(async () => {
  await Promise.all([
    planningStore.fetchPlans(selectedDate.value),
    fetchCourses()
  ])
})

const handleDateSelected = (date) => {
  selectedDate.value = date
  planningStore.selectedDate = date
  planningStore.fetchPlans(date)
}

const handleSavePlan = async (planData) => {
  try {
    if (editingPlan.value) {
      await planningStore.updatePlan(editingPlan.value.id, planData)
    } else {
      await planningStore.addPlan({
        ...planData,
        date: selectedDate.value
      })
    }
    handleCloseModal()
    await planningStore.fetchPlans(selectedDate.value)
  } catch (error) {
    const errorMessage = getErrorMessage(error)
    console.error('Failed to save plan:', error)
    alert(errorMessage || 'Failed to save plan')
  }
}

const handleCompletePlan = async (planId, actualTime) => {
  await planningStore.markComplete(planId, actualTime)
}

const handleUpdatePlan = (plan) => {
  editingPlan.value = plan
  showPlanModal.value = true
}

const handleDeletePlan = async (planId) => {
  await planningStore.deletePlan(planId)
}

const handleCarryOver = () => {
  carryOverFromDate.value = selectedDate.value
  showCarryOverModal.value = true
}

const handleConfirmCarryOver = async (taskIds) => {
  const today = new Date().toISOString().split('T')[0]
  await planningStore.carryOverPlans(carryOverFromDate.value, today)
  showCarryOverModal.value = false
  await planningStore.fetchPlans(today)
}

const handleCloseModal = () => {
  showPlanModal.value = false
  editingPlan.value = null
}
</script>
