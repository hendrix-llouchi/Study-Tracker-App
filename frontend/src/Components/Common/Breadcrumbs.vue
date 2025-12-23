<template>
  <nav class="flex items-center gap-2 text-body-small overflow-x-auto">
    <router-link
      to="/dashboard"
      class="text-text-secondary hover:text-text-primary transition-colors duration-default whitespace-nowrap"
    >
      Home
    </router-link>
    <template v-for="(crumb, index) in breadcrumbs" :key="index">
      <ChevronRight class="w-4 h-4 text-text-tertiary flex-shrink-0" />
      <router-link
        v-if="index < breadcrumbs.length - 1"
        :to="crumb.to"
        class="text-text-secondary hover:text-text-primary transition-colors duration-default whitespace-nowrap"
      >
        {{ crumb.label }}
      </router-link>
      <span v-else class="text-text-primary font-medium whitespace-nowrap">
        {{ crumb.label }}
      </span>
    </template>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { ChevronRight } from 'lucide-vue-next'

const route = useRoute()

const breadcrumbs = computed(() => {
  const path = route.path
  const segments = path.split('/').filter(Boolean)
  
  if (segments.length === 0) {
    return []
  }
  
  const crumbs = []
  let currentPath = ''
  
  segments.forEach((segment, index) => {
    currentPath += `/${segment}`
    const label = segment
      .split('-')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')
    
    crumbs.push({
      to: currentPath,
      label
    })
  })
  
  return crumbs
})
</script>

