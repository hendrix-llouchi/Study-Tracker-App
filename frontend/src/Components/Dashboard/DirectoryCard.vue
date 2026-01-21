<template>
  <div 
    class="group relative spatial-card liquid-glass p-8 h-full cursor-pointer flex flex-col justify-between"
    @click="$router.push(to)"
  >
    <!-- Background Gradient Glow -->
    <div :class="`absolute -top-24 -right-24 w-48 h-48 rounded-full blur-[80px] opacity-20 ${colorClass}`"></div>

    <div class="space-y-6">
      <!-- Icon Wrapper -->
      <div :class="`w-14 h-14 rounded-2xl flex items-center justify-center liquid-glass border-white/50 dark:border-white/5 shadow-xl group-hover:scale-110 transition-transform duration-500`">
        <component :is="icon" :size="28" :class="iconColor" />
      </div>

      <!-- Text Content -->
      <div class="space-y-2">
        <h3 class="text-2xl font-black tracking-tight text-text-main dark:text-white group-hover:strap-gradient-text transition-colors duration-300">
          {{ title }}
        </h3>
        <p class="text-sm font-medium text-text-muted leading-relaxed line-clamp-2">
          {{ description }}
        </p>
      </div>
    </div>

    <!-- Bottom Action -->
    <div class="mt-8 flex items-center justify-between border-t border-white/20 dark:border-white/5 pt-4">
      <div class="flex items-center gap-2">
        <div class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-pulse"></div>
        <span class="text-[10px] uppercase font-black tracking-widest text-text-muted">Live Insights</span>
      </div>
      <div class="w-8 h-8 rounded-full liquid-glass flex items-center justify-center opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0 transition-all duration-500">
        <ArrowUpRight :size="16" class="text-text-main dark:text-white" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowUpRight } from 'lucide-vue-next'

defineProps({
  title: String,
  description: String,
  icon: Object,
  to: String,
  colorClass: String,
  iconColor: String
})
</script>
