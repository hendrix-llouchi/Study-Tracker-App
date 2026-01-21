<template>
  <div class="group flex items-center gap-3 cursor-pointer select-none">
    <!-- Spatial Icon Mark -->
    <div class="relative w-10 h-10 flex items-center justify-center">
      <!-- Glow Layer -->
      <div class="absolute inset-0 bg-strap-indigo/30 blur-xl group-hover:bg-strap-violet/50 transition-all duration-500 rounded-full"></div>
      
      <!-- Liquid Glass Container -->
      <div class="relative w-full h-full liquid-glass rounded-xl flex items-center justify-center overflow-hidden border-white/40 dark:border-white/10 group-hover:scale-110 transition-transform duration-500">
        <!-- Minimal Mark (S) -->
        <span class="text-xl font-black text-strap-navy dark:text-white transform group-hover:-rotate-12 transition-transform duration-500">S</span>
        
        <!-- Refraction Effect -->
        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
      </div>
    </div>

    <!-- Minimal Typographic Mark -->
    <div class="flex flex-col">
      <span class="text-lg font-black tracking-tighter text-text-main dark:text-white leading-none">STRAP</span>
      <div class="h-1 w-0 group-hover:w-full bg-strap-gradient-bg transition-all duration-500 rounded-full"></div>
    </div>
  </div>
</template>

<script setup>
// Minimalist typographic mark with spatial depth
</script>
