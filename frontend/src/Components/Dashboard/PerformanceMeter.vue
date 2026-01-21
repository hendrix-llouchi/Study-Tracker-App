<template>
  <div class="relative flex flex-center justify-center p-8 active:scale-95 transition-transform duration-300">
    <!-- Spatial Base Layer -->
    <div class="absolute inset-0 bg-strap-indigo/5 dark:bg-strap-indigo/10 blur-3xl rounded-full"></div>
    
    <div class="relative w-64 h-64 flex items-center justify-center rounded-full liquid-glass border-white/50 dark:border-white/5 shadow-2xl overflow-hidden animate-float">
      <!-- Refracted Background (Subtle) -->
      <div class="absolute inset-0 opacity-10 dark:opacity-20 bg-[url('/images/premium_bg.png')] bg-cover bg-center"></div>

      <!-- SVG Circular Progress -->
      <svg class="w-full h-full transform -rotate-90 p-4">
        <!-- Background Track -->
        <circle
          cx="128"
          cy="128"
          r="100"
          stroke="currentColor"
          stroke-width="12"
          fill="transparent"
          class="text-neutral-gray100 dark:text-white/5"
        />
        <!-- Progress Bar (Gradient) -->
        <circle
          cx="128"
          cy="128"
          r="100"
          stroke="url(#liquidGradient)"
          stroke-width="12"
          stroke-linecap="round"
          fill="transparent"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="strokeDashoffset"
          class="transition-all duration-1000 ease-out"
        />
        
        <!-- Definitions -->
        <defs>
          <linearGradient id="liquidGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="var(--strap-indigo)" />
            <stop offset="100%" stop-color="var(--strap-violet)" />
          </linearGradient>
          <filter id="glow">
            <feGaussianBlur stdDeviation="4" result="blur" />
            <feComposite in="SourceGraphic" in2="blur" operator="over" />
          </filter>
        </defs>
      </svg>

      <!-- Center Content -->
      <div class="absolute flex flex-col items-center">
        <span class="text-xs font-black uppercase tracking-[0.2em] text-text-muted mb-1">Overall Rank</span>
        <div class="flex items-baseline gap-1">
          <span class="text-6xl font-black text-text-main dark:text-white tracking-tighter">{{ displayValue }}</span>
          <span class="text-xl font-bold text-strap-indigo dark:text-strap-violet">%</span>
        </div>
        <div class="mt-2 px-3 py-1 rounded-full bg-strap-indigo/10 dark:bg-white/5 border border-strap-indigo/20 translate-y-2 animate-pulse">
           <span class="text-[10px] font-bold text-strap-indigo dark:text-strap-violet tracking-widest uppercase">Excellent</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'

const props = defineProps({
  value: { type: Number, default: 85 }
})

const displayValue = ref(0)
const radius = 100
const circumference = 2 * Math.PI * radius

const strokeDashoffset = computed(() => {
  return circumference - (props.value / 100) * circumference
})

onMounted(() => {
  // Animate the number count
  const duration = 1500
  const start = 0
  const end = props.value
  const startTime = performance.now()

  const animate = (currentTime) => {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)
    displayValue.value = Math.floor(progress * end)

    if (progress < 1) {
      requestAnimationFrame(animate)
    }
  }

  requestAnimationFrame(animate)
})
</script>
