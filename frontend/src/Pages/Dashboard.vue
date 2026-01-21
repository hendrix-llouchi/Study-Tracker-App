<template>
  <AppLayout :user="user">
    <div class="max-w-[1600px] mx-auto space-y-12 py-8 relative">
      
      <!-- HERO SECTION: Cinematic Experience -->
      <section class="relative px-6 sm:px-0">
        <!-- Floating Decorative Elements -->
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-strap-indigo/10 blur-[120px] rounded-full"></div>
        <div class="absolute top-40 -right-20 w-80 h-80 bg-strap-violet/10 blur-[100px] rounded-full animate-float"></div>

        <div class="relative grid grid-cols-1 lg:grid-cols-5 gap-12 items-center">
          <!-- Text Content -->
          <div class="lg:col-span-3 space-y-6 animate-in fade-in slide-in-from-left-8 duration-1000">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full liquid-glass border-strap-indigo/30 bg-strap-indigo/5 text-strap-indigo dark:text-strap-violet font-black uppercase tracking-[0.2em] text-[10px]">
              <Sparkles :size="14" />
              Academic Year 2024
            </div>
            
            <h1 class="text-5xl lg:text-7xl font-black text-text-main dark:text-white tracking-tighter leading-[0.9]">
              Welcome back, <br/>
              <span class="strap-gradient-text">{{ firstName }}</span>.
            </h1>
            
            <p class="text-lg font-medium text-text-muted max-w-xl leading-relaxed">
              Your academic command center is synchronized. You have <span class="text-text-main dark:text-white font-bold">4 pending tasks</span> today. Let's maintain your <span class="text-strap-amber font-bold">8-day study streak</span>.
            </p>

            <div class="flex items-center gap-4 pt-4">
              <Button variant="primary" size="lg" class="strap-gradient-bg border-none shadow-2xl shadow-strap-indigo/40 px-8 py-6 rounded-2xl font-black tracking-tight" @click="$router.push('/planning')">
                View Today's Plan
              </Button>
              <Button variant="secondary" size="lg" class="liquid-glass border-none px-8 py-6 rounded-2xl font-black tracking-tight dark:text-white" @click="$router.push('/ai-coach')">
                Ask AI Coach
              </Button>
            </div>
          </div>

          <!-- Central Performance Hub -->
          <div class="lg:col-span-2 flex justify-center animate-in fade-in zoom-in-95 duration-1000 delay-300">
            <PerformanceMeter :value="88" />
          </div>
        </div>
      </section>

      <!-- BENTO DIRECTORY: The Grid of Discovery -->
      <section class="px-6 sm:px-0">
        <h2 class="text-xs font-black uppercase tracking-[0.3em] text-text-muted mb-8 ml-2 flex items-center gap-3">
          <Layout :size="16" class="text-brand-primary" />
          Dashboard Directory
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <DirectoryCard
            v-for="card in directoryCards"
            :key="card.to"
            v-bind="card"
            class="animate-in fade-in slide-in-from-bottom-8 duration-700"
          />
        </div>
      </section>

      <!-- QUICK STATS BENTO: Real-time Performance -->
      <section class="px-6 sm:px-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="(stat, index) in quickStats" :key="index" 
             class="liquid-glass p-6 rounded-[2rem] border-white/40 dark:border-white/5 group hover:scale-[1.02] transition-all duration-300"
        >
          <div class="flex flex-col">
            <span class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-1">{{ stat.label }}</span>
            <div class="flex items-end justify-between">
               <span class="text-4xl font-black text-text-main dark:text-white tracking-tighter">{{ stat.value }}</span>
               <div class="p-2 rounded-xl bg-strap-indigo/10 dark:bg-white/5 text-strap-indigo">
                 <component :is="stat.icon" :size="20" />
               </div>
            </div>
          </div>
        </div>
      </section>

      <!-- RECENT INTELLIGENCE: Minimalist Updates -->
      <section class="max-w-4xl mx-auto px-6 sm:px-0 pt-12">
        <div class="liquid-glass rounded-[3rem] p-10 space-y-8 relative overflow-hidden">
          <div class="absolute top-0 right-0 w-64 h-64 bg-strap-indigo/5 blur-3xl pointer-events-none"></div>
          
          <div class="flex items-center justify-between relative z-10">
            <h2 class="text-xl font-black dark:text-white flex items-center gap-3">
              <Activity :size="24" class="text-strap-indigo" />
              Intelligence Feed
            </h2>
            <Button variant="ghost" size="sm" class="text-xs font-bold text-strap-indigo hover:bg-strap-indigo/10 rounded-xl px-4">See Full History</Button>
          </div>
          
          <div class="space-y-6 relative z-10">
            <div v-for="activity in recentActivities" :key="activity.id" 
                 class="flex gap-6 group cursor-pointer p-4 rounded-3xl hover:bg-white/30 dark:hover:bg-white/5 transition-all duration-300"
            >
              <div class="w-1.5 bg-neutral-gray100 dark:bg-white/10 group-hover:bg-strap-indigo rounded-full transition-all duration-500"></div>
              <div class="flex-1">
                <p class="text-sm font-bold text-text-main dark:text-gray-200 mb-1">{{ activity.description }}</p>
                <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">{{ activity.time }}</span>
              </div>
              <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                <ChevronRight :size="16" class="text-text-muted" />
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import { useDashboard } from '@/Composables/useDashboard'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import PerformanceMeter from '@/Components/Dashboard/PerformanceMeter.vue'
import DirectoryCard from '@/Components/Dashboard/DirectoryCard.vue'
import { 
  Layout, 
  BarChart3, 
  Sparkles, 
  Activity, 
  ArrowUpRight, 
  ChevronRight, 
  Calendar, 
  MessageSquare,
  Clock,
  Target,
  Zap,
  CheckCircle2
} from 'lucide-vue-next'

const authStore = useAuthStore()
const { stats, recentActivities, refresh } = useDashboard()

const user = computed(() => authStore.user)
const firstName = computed(() => user.value?.name?.split(' ')[0] || 'Scholar')

const directoryCards = [
  {
    title: 'Semester Overview',
    description: 'Track credits, GPA goals, and overall academic trajectory across all terms.',
    icon: Calendar,
    to: '/planning',
    colorClass: 'bg-strap-indigo',
    iconColor: 'text-strap-indigo'
  },
  {
    title: 'Performance Hub',
    description: 'Deep dive into assessment data, PDF transcript processing, and trend analysis.',
    icon: BarChart3,
    to: '/performance',
    colorClass: 'bg-strap-violet',
    iconColor: 'text-strap-violet'
  },
  {
    title: 'AI Study Coach',
    description: 'Get disciplined guidance on your study habits and predicted performance dips.',
    icon: MessageSquare,
    to: '/ai-coach',
    colorClass: 'bg-strap-amber',
    iconColor: 'text-strap-amber'
  }
]

const quickStats = computed(() => [
  { label: 'Current Streak', value: `${stats.value?.studyStreak?.current || 0}d`, icon: Zap },
  { label: 'Study Hours', value: `${stats.value?.hoursStudied || 0}h`, icon: Clock },
  { label: 'Completion Rate', value: `${stats.value?.completionRate || 0}%`, icon: Target },
  { label: 'Tasks Due', value: stats.value?.assignmentsDue || 0, icon: CheckCircle2 }
])

onMounted(async () => {
  await refresh()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
