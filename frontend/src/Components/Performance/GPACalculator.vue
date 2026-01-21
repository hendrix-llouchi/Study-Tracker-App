<template>
  <Card class="liquid-glass border-none overflow-hidden h-full p-8 space-y-8 flex flex-col justify-between">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-black text-text-main dark:text-white tracking-tight">GPA Analytics</h3>
        <div class="p-2 rounded-xl bg-strap-indigo/10 text-strap-indigo">
          <TrendingUp :size="20" />
        </div>
      </div>
      
      <div class="text-center relative py-6">
        <!-- Floating Ambient Background -->
        <div class="absolute inset-0 bg-strap-indigo/5 blur-3xl rounded-full"></div>
        
        <div class="relative inline-block">
          <div class="text-7xl font-black tracking-tighter transition-all duration-500 hover:scale-110" :class="gpaColor">
            {{ formattedGPA }}
          </div>
          <p class="text-[10px] font-black uppercase tracking-[0.3em] text-text-muted mt-2">Current Standing</p>
        </div>
      </div>
      
      <div class="space-y-3">
        <div v-for="stat in gpaStats" :key="stat.label" class="flex items-center justify-between p-4 bg-white/40 dark:bg-white/5 rounded-2xl border border-transparent hover:border-strap-indigo/10 transition-all">
          <span class="text-xs font-bold text-text-muted uppercase tracking-widest">{{ stat.label }}</span>
          <span class="text-base font-black text-text-main dark:text-white">{{ stat.value }}</span>
        </div>
      </div>
    </div>

    <!-- Mini Trend Sparkline -->
    <div v-if="gpaTrend.length > 0" class="space-y-4">
      <div class="flex items-center justify-between">
         <span class="text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Historical Momentum</span>
         <span class="text-[10px] font-bold text-strap-indigo">{{ currentSemester }}</span>
      </div>
      <div class="flex items-end gap-2 h-16 px-1">
        <div
          v-for="(gpa, index) in gpaTrend"
          :key="index"
          class="flex-1 strap-gradient-bg rounded-t-xl transition-all duration-500 hover:brightness-125 cursor-pointer"
          :style="{ height: `${(gpa / 4.0) * 100}%`, opacity: 0.3 + (index / gpaTrend.length) * 0.7 }"
          :title="`${gpa.toFixed(2)}`"
        />
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue'
import Card from '@/Components/Common/Card.vue'
import { TrendingUp } from 'lucide-vue-next'

const props = defineProps({
  results: { type: Array, default: () => [] },
  currentSemester: { type: String, default: '' }
})

const gradeToPoints = {
  'A+': 4.0, 'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7,
  'C+': 2.3, 'C': 2.0, 'C-': 1.7, 'D+': 1.3, 'D': 1.0, 'F': 0.0
}

const totalCredits = computed(() => props.results.reduce((sum, r) => sum + (r.creditHours || 0), 0))
const totalGradePoints = computed(() => props.results.reduce((sum, r) => sum + ((gradeToPoints[r.grade] || 0) * (r.creditHours || 0)), 0))
const currentGPA = computed(() => totalCredits.value === 0 ? 0 : totalGradePoints.value / totalCredits.value)
const formattedGPA = computed(() => currentGPA.value.toFixed(2))

const gpaColor = computed(() => {
  if (currentGPA.value >= 3.7) return 'strap-gradient-text'
  if (currentGPA.value >= 3.0) return 'text-strap-indigo'
  if (currentGPA.value >= 2.0) return 'text-strap-amber'
  return 'text-red-500'
})

const gpaStats = computed(() => [
  { label: 'Total Credits', value: totalCredits.value },
  { label: 'Weighted Points', value: totalGradePoints.value.toFixed(1) },
  { label: 'Current Term', value: props.currentSemester || 'N/A' }
])

const gpaTrend = computed(() => {
  const semesters = {}
  props.results.forEach(result => {
    if (!semesters[result.semester]) semesters[result.semester] = { credits: 0, points: 0 }
    semesters[result.semester].credits += result.creditHours || 0
    semesters[result.semester].points += (gradeToPoints[result.grade] || 0) * (result.creditHours || 0)
  })
  return Object.values(semesters).map(sem => sem.credits > 0 ? sem.points / sem.credits : 0).slice(-6)
})
</script>
