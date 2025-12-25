<template>
  <AppLayout :user="user" :unread-count="0">
    <div v-if="loading" class="flex items-center justify-center min-h-[400px]">
      <LoadingSpinner size="lg" />
    </div>

    <div v-else class="space-y-4 lg:space-y-6">
      <!-- Alert Banner -->
      <div class="bg-primary-green-bg border border-primary-green-light rounded-xl p-4 lg:p-5 flex items-start gap-3">
        <CheckCircle class="w-5 h-5 text-text-success flex-shrink-0 mt-0.5" />
        <p class="text-body text-text-success flex-1">
          Great job! You've maintained your study streak for {{ studyStreak?.current || 0 }} days.
        </p>
        <button class="text-text-success hover:text-primary-green-hover flex-shrink-0 min-w-[44px] min-h-[44px] flex items-center justify-center">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Filters -->
      <DashboardFilters
        :courses="courses"
        :semesters="semesters"
        @filter-change="handleFilterChange"
      />

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatsCard
          v-for="stat in statsCards"
          :key="stat.label"
          :label="stat.label"
          :value="stat.value"
          :icon="stat.icon"
          :color="stat.color"
          :progress="stat.progress"
        />
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- GPA Trend Chart -->
        <GPATrendChart :data="gpaTrend" />

        <!-- Subject Performance Chart -->
        <SubjectPerformanceChart :data="subjectPerformance" />
      </div>

      <!-- Main Content Grid - Performance and Courses -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- Left Column - Performance Gauge -->
        <PerformanceGauge :percentage="stats?.overallPerformance || 0" />

        <!-- Right Column - Course List -->
        <CourseList />
      </div>

      <!-- Upcoming Classes -->
      <div class="grid grid-cols-1 gap-4 lg:gap-6">
        <UpcomingClasses :classes="upcomingClasses" />
      </div>

      <!-- Bottom Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- Study Streaks -->
        <StudyStreaks :streak="studyStreak" />

        <!-- Planned vs Completed Chart -->
        <PlannedVsCompletedChart :data="plannedVsCompleted" />
      </div>

      <!-- Study Consistency Heatmap -->
      <div class="grid grid-cols-1 gap-4 lg:gap-6">
        <StudyHeatmap :data="heatmapData" />
      </div>

      <!-- Weekly Progress Chart -->
      <div class="grid grid-cols-1 gap-4 lg:gap-6">
        <WeeklyProgressChart :data="weeklyProgress" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { useDashboard } from '@/Composables/useDashboard'
import { useDashboardStore } from '@/Stores/dashboard'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import LoadingSpinner from '@/Components/Common/LoadingSpinner.vue'
import StatsCard from '@/Components/Dashboard/StatsCard.vue'
import PerformanceGauge from '@/Components/Dashboard/PerformanceGauge.vue'
import CourseList from '@/Components/Dashboard/CourseList.vue'
import UpcomingClasses from '@/Components/Dashboard/UpcomingClasses.vue'
import StudyStreaks from '@/Components/Dashboard/StudyStreaks.vue'
import WeeklyProgressChart from '@/Components/Dashboard/WeeklyProgressChart.vue'
import GPATrendChart from '@/Components/Dashboard/GPATrendChart.vue'
import SubjectPerformanceChart from '@/Components/Dashboard/SubjectPerformanceChart.vue'
import StudyHeatmap from '@/Components/Dashboard/StudyHeatmap.vue'
import PlannedVsCompletedChart from '@/Components/Dashboard/PlannedVsCompletedChart.vue'
import DashboardFilters from '@/Components/Dashboard/DashboardFilters.vue'
import { CheckCircle, X, BookOpen, Clock, CheckCircle2, FileText } from 'lucide-vue-next'

const authStore = useAuthStore()
const dashboardStore = useDashboardStore()
const { stats, upcomingClasses, studyStreak, weeklyProgress, loading } = useDashboard()

const user = computed(() => authStore.user)

const gpaTrend = computed(() => dashboardStore.gpaTrend)
const subjectPerformance = computed(() => dashboardStore.subjectPerformance)
const plannedVsCompleted = computed(() => dashboardStore.plannedVsCompleted)
const heatmapData = computed(() => dashboardStore.heatmapData)

const courses = ref([])
const semesters = ref([])

const statsCards = computed(() => [
  {
    label: 'Courses Enrolled',
    value: stats.value?.coursesEnrolled || 0,
    icon: BookOpen,
    color: 'blue',
    progress: null
  },
  {
    label: 'Hours Studied',
    value: `${stats.value?.hoursStudied || 0}h`,
    icon: Clock,
    color: 'green',
    progress: null
  },
  {
    label: 'Completion Rate',
    value: `${stats.value?.completionRate || 0}%`,
    icon: CheckCircle2,
    color: 'orange',
    progress: stats.value?.completionRate || 0
  },
  {
    label: 'Assignments Due',
    value: stats.value?.assignmentsDue || 0,
    icon: FileText,
    color: 'purple',
    progress: null
  }
])

const handleFilterChange = (newFilters) => {
  dashboardStore.updateFilters(newFilters)
}
</script>
