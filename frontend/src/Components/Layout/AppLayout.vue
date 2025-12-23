<template>
  <div class="app-layout min-h-screen bg-neutral-gray50">
    <Sidebar :is-mobile-menu-open="isMobileMenuOpen" @close-mobile-menu="isMobileMenuOpen = false" />
    <div class="lg:ml-60">
      <AppHeader
        :user="user"
        :unread-count="unreadCount"
        @logout="handleLogout"
        @toggle-mobile-menu="isMobileMenuOpen = !isMobileMenuOpen"
      />
      <main class="pt-16 lg:pt-20 px-4 sm:px-6 lg:px-8 py-4 lg:py-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from '@/Components/Layout/Sidebar.vue'
import AppHeader from '@/Components/Layout/AppHeader.vue'

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

const emit = defineEmits(['logout'])

const isMobileMenuOpen = ref(false)

const handleLogout = () => {
  emit('logout')
}
</script>
