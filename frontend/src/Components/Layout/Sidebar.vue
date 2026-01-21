<template>
  <!-- Mobile Overlay -->
  <Transition name="fade">
    <div v-if="isMobileMenuOpen" class="fixed inset-0 bg-strap-navy/60 backdrop-blur-sm z-[50] lg:hidden" @click="closeMobileMenu"></div>
  </Transition>

  <!-- Sidebar -->
  <aside 
    :class="sidebarClasses"
    class="fixed left-0 top-0 h-screen w-64 bg-bg-secondary border-r border-white/10 dark:border-white/5 z-[60] transition-all duration-500 lg:translate-x-0 group"
  >
    <!-- Background Texture -->
    <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.07] pointer-events-none overflow-hidden">
      <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-strap-indigo blur-[100px] rounded-full"></div>
    </div>

    <div class="relative h-full flex flex-col p-6">
      <!-- Logo Section -->
      <div class="mb-12">
        <Logo />
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-8 overflow-y-auto scrollbar-hide">
        <div v-for="section in navGroups" :key="section.label">
          <h3 class="px-4 mb-4 text-[10px] font-black uppercase tracking-[0.3em] text-text-muted opacity-50">
            {{ section.label }}
          </h3>
          <div class="space-y-1">
            <router-link
              v-for="item in section.items"
              :key="item.to"
              :to="item.to"
              v-slot="{ isActive }"
              @click="closeMobileMenu"
            >
              <div 
                :class="[
                  isActive 
                    ? 'bg-strap-indigo shadow-lg shadow-strap-indigo/25 text-white' 
                    : 'text-text-muted hover:bg-strap-indigo/5 hover:text-text-main dark:hover:text-white'
                ]"
                class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 group/nav"
              >
                <component 
                  :is="item.icon" 
                  :size="20" 
                  :class="[isActive ? 'text-white' : 'text-text-muted group-hover/nav:text-strap-indigo']"
                  class="transition-colors duration-300"
                />
                <span class="text-sm font-bold tracking-tight">{{ item.label }}</span>
                
                <div v-if="isActive" class="ml-auto w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
              </div>
            </router-link>
          </div>
        </div>
      </nav>

      <!-- Bottom Profile Preview -->
      <div class="mt-auto pt-6 border-t border-white/10 dark:border-white/5">
        <div class="liquid-glass p-4 rounded-2xl flex items-center gap-3 active:scale-95 transition-transform">
          <div class="w-10 h-10 rounded-xl strap-gradient-bg flex items-center justify-center font-black text-white text-xs shadow-lg">
            ST
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-xs font-black text-text-main dark:text-white truncate">Standard Plan</span>
            <span class="text-[9px] font-bold text-text-muted uppercase tracking-widest">v2.4.0-liquid</span>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { 
  LayoutDashboard, 
  BarChart3, 
  Calendar, 
  Clock, 
  BookOpen, 
  TrendingUp, 
  PieChart, 
  MessageSquare, 
  Settings, 
  HelpCircle,
  X 
} from 'lucide-vue-next'
import Logo from '@/Components/Common/Logo.vue'

const props = defineProps({
  isMobileMenuOpen: Boolean
})

const emit = defineEmits(['close-mobile-menu'])

const navGroups = [
  {
    label: 'Main Ops',
    items: [
      { to: '/dashboard', label: 'Command Center', icon: LayoutDashboard },
      { to: '/performance', label: 'Performance Hub', icon: BarChart3 },
      { to: '/planning', label: 'Course Planning', icon: Calendar },
      { to: '/timetable', label: 'Live Timetable', icon: Clock }
    ]
  },
  {
    label: 'Analytics',
    items: [
      { to: '/progress/analytics', label: 'Growth Reports', icon: PieChart },
      { to: '/ai-coach', label: 'AI Study Coach', icon: MessageSquare }
    ]
  },
  {
    label: 'System',
    items: [
      { to: '/settings', label: 'Config', icon: Settings },
      { to: '/help', label: 'Support', icon: HelpCircle }
    ]
  }
]

const sidebarClasses = computed(() => {
  return props.isMobileMenuOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'
})

const closeMobileMenu = () => emit('close-mobile-menu')
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
