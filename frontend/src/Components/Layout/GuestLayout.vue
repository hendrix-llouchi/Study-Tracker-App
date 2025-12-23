<template>
  <div class="guest-layout min-h-screen bg-neutral-gray50">
    <header class="bg-neutral-white border-b border-border-default">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-primary-green rounded-md flex items-center justify-center">
              <span class="text-white font-bold text-lg">E</span>
            </div>
            <span class="ml-3 text-lg font-semibold text-text-primary">Edupro</span>
          </div>
        </div>
      </div>
    </header>
    <main class="guest-content">
      <slot />
    </main>
    <footer class="bg-neutral-white border-t border-border-default py-6 mt-auto">
      <div class="max-w-7xl mx-auto px-6 text-center text-body-small text-text-secondary">
        <p>&copy; 2024 Edupro. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
</script>

