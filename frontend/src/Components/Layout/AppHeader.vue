<template>
  <header class="fixed top-0 right-0 left-0 lg:left-64 z-40 px-6 py-4">
    <div class="max-w-[1600px] mx-auto liquid-glass rounded-2xl px-6 py-3 flex items-center justify-between border-white/20 dark:border-white/5 shadow-lg">
      
      <!-- Left: Mobile Menu + Breadcrumbs -->
      <div class="flex items-center gap-4">
        <button
          @click="$emit('toggle-mobile-menu')"
          class="lg:hidden p-2 rounded-xl hover:bg-white/20 dark:hover:bg-white/5 transition-all"
        >
          <Menu :size="20" class="text-text-main dark:text-white" />
        </button>
        
        <div class="hidden md:block">
          <Breadcrumbs />
        </div>
      </div>

      <!-- Right: Search, Theme, User -->
      <div class="flex items-center gap-4">
        <!-- Premium Search Bar -->
        <div class="relative hidden lg:block group">
          <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted group-focus-within:text-strap-indigo transition-colors" />
          <input
            type="text"
            placeholder="Command Search (⌘K)"
            class="pl-10 pr-4 py-2 w-64 bg-white/50 dark:bg-white/5 border border-transparent focus:border-strap-indigo/30 rounded-xl text-xs font-bold focus:outline-none transition-all"
          />
        </div>

        <!-- Theme Toggle -->
        <button 
          @click="toggleTheme" 
          class="p-2.5 rounded-xl bg-white/50 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 text-text-muted hover:text-strap-indigo transition-all ring-1 ring-white/20"
        >
          <Sun v-if="isDark" :size="18" />
          <Moon v-else :size="18" />
        </button>

        <!-- User Profile Dropdown -->
        <div class="relative flex items-center gap-3 p-1 pl-3 rounded-xl bg-white/50 dark:bg-white/5 border border-white/20 dark:border-white/5">
           <div class="hidden sm:flex flex-col items-end">
             <span class="text-xs font-black text-text-main dark:text-white leading-none">{{ user?.name || 'Scholar' }}</span>
             <span class="text-[9px] font-black uppercase tracking-widest text-strap-indigo">Pro Member</span>
           </div>
           
           <div class="relative group" ref="dropdownRef">
             <button @click="toggleDropdown" class="w-9 h-9 rounded-xl overflow-hidden bg-strap-indigo/10 border-2 border-white dark:border-white/5 shadow-inner">
               <img v-if="user?.avatar" :src="user.avatar" class="w-full h-full object-cover" />
               <span v-else class="text-xs font-black text-strap-indigo">{{ userInitials }}</span>
             </button>

             <!-- Dropdown (Spatial) -->
             <div v-if="showDropdown" class="absolute right-0 mt-4 w-56 liquid-glass rounded-2xl p-2 border-none shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                <router-link v-for="link in dropdownLinks" :key="link.to" :to="link.to" 
                             class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-strap-indigo/10 dark:hover:bg-white/5 text-sm font-bold text-text-main dark:text-white transition-all"
                             @click="closeDropdown"
                >
                  <component :is="link.icon" :size="18" class="text-strap-indigo" />
                  {{ link.label }}
                </router-link>
                <div class="h-px bg-white/20 dark:bg-white/5 my-2 mx-2"></div>
                <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-red-500/10 text-sm font-bold text-red-500 transition-all">
                  <LogOut :size="18" /> Logout
                </button>
             </div>
           </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Menu, Search, Sun, Moon, User, Settings, LogOut, ChevronDown } from 'lucide-vue-next'
import Breadcrumbs from '@/Components/Common/Breadcrumbs.vue'

const props = defineProps({
  user: Object,
  unreadCount: Number
})

const emit = defineEmits(['logout', 'toggle-mobile-menu'])

const showDropdown = ref(false)
const dropdownRef = ref(null)
const isDark = ref(false)

const userInitials = computed(() => {
  if (!props.user?.name) return 'S'
  return props.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const dropdownLinks = [
  { label: 'Profile Settings', to: '/settings', icon: User },
  { label: 'System Prefs', to: '/settings', icon: Settings }
]

const toggleTheme = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.theme = isDark.value ? 'dark' : 'light'
}

const toggleDropdown = () => showDropdown.value = !showDropdown.value
const closeDropdown = () => showDropdown.value = false
const handleLogout = () => { emit('logout'); closeDropdown() }

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) closeDropdown()
}

onMounted(() => {
  isDark.value = localStorage.theme === 'dark' || (!localStorage.theme && window.matchMedia('(prefers-color-scheme: dark)').matches)
  document.documentElement.classList.toggle('dark', isDark.value)
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>
