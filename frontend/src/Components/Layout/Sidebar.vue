<template>
  <!-- Mobile Overlay -->
  <div
    v-if="isMobileMenuOpen"
    class="fixed inset-0 bg-black/50 z-40 lg:hidden"
    @click="closeMobileMenu"
  ></div>

  <!-- Sidebar -->
  <aside
    :class="sidebarClasses"
    class="fixed left-0 top-0 h-screen bg-neutral-white border-r border-border-default z-50 transition-transform duration-default lg:translate-x-0"
  >
    <!-- Logo -->
    <div class="px-4 py-4 lg:py-6 mb-6 border-b border-border-default">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-primary-green rounded-md flex items-center justify-center flex-shrink-0">
            <span class="text-white font-bold text-lg">E</span>
          </div>
          <span class="ml-3 text-lg font-semibold text-text-primary">Edupro</span>
        </div>
        <button
          @click="closeMobileMenu"
          class="lg:hidden p-1 rounded-md hover:bg-neutral-gray100"
        >
          <X :size="20" class="text-text-secondary" />
        </button>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="px-2 overflow-y-auto h-[calc(100vh-100px)]">
      <div v-for="section in navigationSections" :key="section.label" class="mb-6">
        <div v-if="section.label" class="px-4 mb-2">
          <span class="text-caption font-semibold text-text-tertiary uppercase tracking-wider">
            {{ section.label }}
          </span>
        </div>
        <div v-for="item in section.items" :key="item.to">
          <router-link
            :to="item.to"
            :class="navItemClasses(item)"
            class="flex items-center gap-3 px-4 py-3 mb-1 rounded-md transition-all duration-default min-h-[44px]"
            @click="closeMobileMenu"
          >
            <component :is="item.icon" :size="20" :class="iconClasses(item)" class="flex-shrink-0" />
            <span class="text-body font-medium">{{ item.label }}</span>
            <Badge v-if="item.badge" variant="info" size="sm" class="ml-auto flex-shrink-0">
              {{ item.badge }}
            </Badge>
          </router-link>
        </div>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
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
import Badge from '@/Components/Common/Badge.vue'

const props = defineProps({
  isMobileMenuOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close-mobile-menu'])

const route = useRoute()

const navigationSections = [
  {
    items: [
      { to: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
      { to: '/performance', label: 'Performance', icon: BarChart3 },
      { to: '/planning', label: 'Planning', icon: Calendar },
      { to: '/timetable', label: 'Timetable', icon: Clock },
      { to: '/assignments', label: 'Assignments', icon: BookOpen }
    ]
  },
  {
    label: 'Insights',
    items: [
      { to: '/progress/weekly', label: 'Weekly Reports', icon: TrendingUp },
      { to: '/progress/analytics', label: 'Analytics', icon: PieChart },
      { to: '/ai-coach', label: 'AI Coach', icon: MessageSquare, badge: 'New' }
    ]
  },
  {
    label: 'Settings',
    items: [
      { to: '/settings', label: 'Settings', icon: Settings },
      { to: '/help', label: 'Help', icon: HelpCircle }
    ]
  }
]

const sidebarClasses = computed(() => {
  let classes = 'w-64 lg:w-60'
  
  if (props.isMobileMenuOpen) {
    classes += ' translate-x-0'
  } else {
    classes += ' -translate-x-full lg:translate-x-0'
  }
  
  return classes
})

const closeMobileMenu = () => {
  emit('close-mobile-menu')
}

const navItemClasses = (item) => {
  const isActive = route.path === item.to
  if (isActive) {
    return 'bg-primary-green-bg text-text-success border-l-3 border-l-primary-green pl-3'
  }
  return 'text-text-secondary hover:bg-neutral-gray100 hover:text-text-primary'
}

const iconClasses = (item) => {
  const isActive = route.path === item.to
  return isActive ? 'text-primary-green' : 'text-neutral-gray400'
}
</script>
