<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 h-full">
    <!-- Main Velocity: Taking prominence -->
    <div :class="mode === 'velocity' ? 'lg:col-span-2' : ''" class="liquid-glass p-8 rounded-[2rem] border-white/40 dark:border-white/5 space-y-6 flex flex-col h-full">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-strap-indigo/10 flex items-center justify-center">
            <TrendingUp :size="20" class="text-strap-indigo" />
          </div>
          <h3 class="text-xl font-black text-text-main dark:text-white tracking-tight">Academic Momentum</h3>
        </div>
        <div class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 text-[10px] font-black uppercase tracking-widest">
          High Growth Potential
        </div>
      </div>
      <div class="flex-1 min-h-[350px] relative">
        <Line v-if="gpaTrendData" :data="gpaTrendData" :options="gpaTrendOptions" />
      </div>
    </div>

    <!-- Mastery Matrix (Only if not in velocity mode) -->
    <div v-if="mode !== 'velocity'" class="liquid-glass p-8 rounded-[2rem] border-white/40 dark:border-white/5 space-y-6 flex flex-col h-full">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-strap-violet/10 flex items-center justify-center">
          <BarChart3 :size="20" class="text-strap-violet" />
        </div>
        <h3 class="text-xl font-black text-text-main dark:text-white tracking-tight">Concept Mastery</h3>
      </div>
      <div class="flex-1 min-h-[300px] relative">
        <Bar v-if="subjectData" :data="subjectData" :options="subjectOptions" />
      </div>
    </div>

    <!-- Grade Spectrum (Only if not in velocity mode) -->
    <div v-if="mode !== 'velocity'" class="lg:col-span-2 liquid-glass p-8 rounded-[3rem] border-white/40 dark:border-white/5 space-y-6 flex flex-col">
       <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-strap-amber/10 flex items-center justify-center">
          <PieChart :size="20" class="text-strap-amber" />
        </div>
        <h3 class="text-xl font-black text-text-main dark:text-white tracking-tight">Grade Spectrum Analysis</h3>
      </div>
      <div class="flex-1 min-h-[250px] relative">
        <Bar v-if="distributionData" :data="distributionData" :options="distributionOptions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Line, Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { TrendingUp, BarChart3, PieChart } from 'lucide-vue-next'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler)

const props = defineProps({
  results: { type: Array, default: () => [] },
  mode: { type: String, default: 'full' } // 'full' or 'velocity'
})

const gradeToPoints = { 'A+': 4.0, 'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7, 'C+': 2.3, 'C': 2.0, 'C-': 1.7, 'D+': 1.3, 'D': 1.0, 'F': 0.0 }

const gpaTrendData = computed(() => {
  const semesters = {}
  props.results.forEach(result => {
    if (!semesters[result.semester]) semesters[result.semester] = { credits: 0, points: 0 }
    const points = gradeToPoints[result.grade] || 0
    const credits = result.creditHours || 0
    semesters[result.semester].credits += credits
    semesters[result.semester].points += points * credits
  })
  const labels = Object.keys(semesters).sort()
  const data = labels.map(l => semesters[l].credits > 0 ? semesters[l].points / semesters[l].credits : 0)
  
  return {
    labels,
    datasets: [{
      label: 'Semester GPA',
      data,
      borderColor: '#6366f1',
      backgroundColor: (context) => {
        const chart = context.chart;
        const {ctx, chartArea} = chart;
        if (!chartArea) return null;
        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.2)');
        return gradient;
      },
      fill: true,
      tension: 0.45,
      pointRadius: 6,
      pointBackgroundColor: '#fff',
      pointBorderWidth: 3,
      pointBorderColor: '#6366f1',
      pointHoverRadius: 9,
      pointHoverBackgroundColor: '#6366f1',
      pointHoverBorderColor: '#fff',
      pointHoverBorderWidth: 4
    }]
  }
})

const commonOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
      titleColor: '#fff',
      bodyColor: '#94a3b8',
      titleFont: { family: 'Outfit', size: 14, weight: '900' },
      bodyFont: { family: 'Inter', size: 12, weight: 'bold' },
      padding: 16,
      cornerRadius: 16,
      displayColors: false,
      borderWidth: 1,
      borderColor: 'rgba(255, 255, 255, 0.1)'
    }
  },
  scales: {
    x: { 
      grid: { display: false }, 
      ticks: { font: { weight: '900', size: 10 }, color: '#64748b', padding: 10 } 
    },
    y: { 
      grid: { color: 'rgba(148, 163, 184, 0.05)', drawBorder: false }, 
      ticks: { font: { weight: '900', size: 10 }, color: '#64748b', padding: 10 } 
    }
  }
}))

const gpaTrendOptions = computed(() => {
  const opts = { ...commonOptions.value }
  opts.scales.y = { ...opts.scales.y, min: 0, max: 4.0, ticks: { ...opts.scales.y.ticks, stepSize: 1 } }
  return opts
})

const subjectData = computed(() => {
  const subjects = {}
  props.results.forEach(r => {
    if (!subjects[r.course]) subjects[r.course] = []
    subjects[r.course].push((r.score / r.maxScore) * 100)
  })
  const sorted = Object.keys(subjects).map(c => ({ c, avg: subjects[c].reduce((a, b) => a + b, 0) / subjects[c].length })).sort((a, b) => b.avg - a.avg)
  return {
    labels: sorted.map(s => s.c),
    datasets: [{
      label: 'Avg Mastery %',
      data: sorted.map(s => s.avg),
      backgroundColor: sorted.map((_, i) => i % 2 === 0 ? '#6366f1' : '#8b5cf6'),
      borderRadius: 14,
      barThickness: 24
    }]
  }
})

const subjectOptions = computed(() => {
  const opts = { ...commonOptions.value }
  opts.scales.y = { ...opts.scales.y, min: 0, max: 100 }
  return opts
})

const distributionData = computed(() => {
  const counts = { 'A Range': 0, 'B Range': 0, 'C Range': 0, 'D/F Range': 0 }
  props.results.forEach(r => {
    const pts = gradeToPoints[r.grade] || 0
    if (pts >= 3.7) counts['A Range']++
    else if (pts >= 3.0) counts['B Range']++
    else if (pts >= 2.0) counts['C Range']++
    else counts['D/F Range']++
  })
  return {
    labels: Object.keys(counts),
    datasets: [{
      data: Object.values(counts),
      backgroundColor: ['#6366f1', '#8b5cf6', '#f59e0b', '#ef4444'],
      borderRadius: 14,
      barThickness: 40
    }]
  }
})

const distributionOptions = commonOptions
</script>
