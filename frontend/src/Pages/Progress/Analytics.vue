<template>
  <AppLayout :user="user" :unread-count="0">
    <div class="space-y-4 lg:space-y-6">
      <div>
        <h1 class="text-h1 text-text-primary mb-2">Analytics</h1>
        <p class="text-body text-text-secondary">Detailed insights into your study patterns and performance</p>
      </div>

      <Card padding="default">
        <p class="text-body text-text-secondary">Advanced analytics features coming soon in Phase 2</p>
        <p class="text-body-small text-text-tertiary mt-2">
          This will include performance predictions, detailed study pattern analysis, and more advanced visualizations.
        </p>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/Stores/auth'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Card from '@/Components/Common/Card.vue'

const authStore = useAuthStore()
const user = computed(() => authStore.user)
</script>

