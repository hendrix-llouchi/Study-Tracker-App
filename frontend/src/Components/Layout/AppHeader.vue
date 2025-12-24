<template>
  <header class="fixed top-0 right-0 left-0 lg:left-60 bg-neutral-white border-b border-border-default z-30">
    <div class="px-4 lg:px-8 py-3 lg:py-4 flex items-center justify-between">
      <!-- Left: Mobile Menu Button + Breadcrumbs -->
      <div class="flex items-center gap-3 lg:gap-2 flex-1 min-w-0">
        <button
          @click="$emit('toggle-mobile-menu')"
          class="lg:hidden p-2 rounded-md hover:bg-neutral-gray100 transition-colors duration-default min-w-[44px] min-h-[44px] flex items-center justify-center"
          aria-label="Toggle menu"
        >
          <Menu :size="20" class="text-text-secondary" />
        </button>
        
        <div class="hidden md:flex items-center gap-2 min-w-0">
          <Breadcrumbs />
        </div>
        <div class="md:hidden">
          <span class="text-body-small font-medium text-text-primary truncate">
            {{ currentPageTitle }}
          </span>
        </div>
      </div>

      <!-- Right: Search, Notifications, User -->
      <div class="flex items-center gap-2 lg:gap-4 flex-shrink-0">
        <!-- Search Bar -->
        <div class="relative hidden md:block">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-text-tertiary" />
          <input
            type="text"
            placeholder="Search..."
            class="pl-10 pr-4 py-2 w-48 lg:w-64 bg-neutral-gray50 border border-border-default rounded-md text-body text-text-primary placeholder:text-text-tertiary focus:outline-none focus:ring-2 focus:ring-border-focus/10 focus:border-border-focus transition-all duration-default"
          />
        </div>

        <!-- Mobile Search Button -->
        <button
          class="md:hidden p-2 rounded-md hover:bg-neutral-gray100 transition-colors duration-default min-w-[44px] min-h-[44px] flex items-center justify-center"
          @click="$emit('toggle-search')"
        >
          <Search class="w-5 h-5 text-text-secondary" />
        </button>

        <!-- Notifications -->
        <button class="relative p-2 rounded-md hover:bg-neutral-gray100 transition-colors duration-default min-w-[44px] min-h-[44px] flex items-center justify-center">
          <Bell class="w-5 h-5 text-text-secondary" />
          <span
            v-if="unreadCount > 0"
            class="absolute top-1 right-1 w-2 h-2 bg-accent-red rounded-full"
          ></span>
        </button>

        <!-- User Dropdown -->
        <div class="relative" ref="dropdownRef">
          <button
            @click="toggleDropdown"
            class="flex items-center gap-2 lg:gap-3 p-1.5 lg:p-2 rounded-md hover:bg-neutral-gray100 transition-colors duration-default min-w-[44px] min-h-[44px]"
          >
            <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full border-2 border-border-default overflow-hidden bg-neutral-gray200 flex-shrink-0">
              <img
                v-if="user?.avatar"
                :src="user.avatar"
                :alt="user.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-text-secondary font-medium text-sm">
                {{ userInitials }}
              </div>
            </div>
            <ChevronDown class="w-4 h-4 text-text-secondary hidden lg:block" />
          </button>

          <!-- Dropdown Menu -->
          <div
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-48 bg-neutral-white border border-border-default rounded-lg shadow-lg py-1 z-50"
          >
            <router-link
              to="/settings#profile"
              class="flex items-center gap-2 px-4 py-2.5 text-body text-text-primary hover:bg-neutral-gray100 transition-colors duration-default min-h-[44px]"
              @click="handleProfileClick"
            >
              <User class="w-4 h-4" />
              Profile
            </router-link>
            <router-link
              to="/settings"
              class="flex items-center gap-2 px-4 py-2.5 text-body text-text-primary hover:bg-neutral-gray100 transition-colors duration-default min-h-[44px]"
              @click="closeDropdown"
            >
              <Settings class="w-4 h-4" />
              Settings
            </router-link>
            <div class="border-t border-border-default my-1"></div>
            <button
              @click="handleLogout"
              class="w-full flex items-center gap-2 px-4 py-2.5 text-body text-text-primary hover:bg-neutral-gray100 transition-colors duration-default min-h-[44px]"
            >
              <LogOut class="w-4 h-4" />
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Search, Bell, ChevronDown, User, Settings, LogOut, Menu } from 'lucide-vue-next'
import Breadcrumbs from '@/Components/Common/Breadcrumbs.vue'

const props = defineProps({
  user: {
    type: Object,
    default: () => ({})
  },
  unreadCount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['logout', 'toggle-mobile-menu', 'toggle-search'])

const router = useRouter()
const route = useRoute()
const showDropdown = ref(false)
const dropdownRef = ref(null)

const userInitials = computed(() => {
  if (!props.user?.name) return 'U'
  const names = props.user.name.split(' ')
  return names.map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const currentPageTitle = computed(() => {
  const path = route.path
  const segments = path.split('/').filter(Boolean)
  if (segments.length === 0) return 'Dashboard'
  const lastSegment = segments[segments.length - 1]
  return lastSegment
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
})

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const closeDropdown = () => {
  showDropdown.value = false
}

const handleProfileClick = () => {
  closeDropdown()
  // If already on settings page, scroll to profile section
  if (route.path === '/settings') {
    setTimeout(() => {
      const profileSection = document.getElementById('profile-section')
      if (profileSection) {
        profileSection.scrollIntoView({ behavior: 'smooth', block: 'start' })
      }
    }, 100)
  }
}

const handleLogout = () => {
  closeDropdown()
  emit('logout')
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
