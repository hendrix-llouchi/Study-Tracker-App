<template>
  <GuestLayout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div>
          <h2 class="text-h1 text-center text-text-primary">Set new password</h2>
          <p class="mt-2 text-body text-center text-text-secondary">
            Enter your new password below
          </p>
        </div>
        
        <Card padding="lg">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
              <p class="text-body-small text-accent-red">{{ error }}</p>
            </div>

            <Input
              v-model="form.password"
              type="password"
              label="New password"
              placeholder="Enter your new password"
              required
              :error="errors.password"
            />

            <Input
              v-model="form.password_confirmation"
              type="password"
              label="Confirm new password"
              placeholder="Confirm your new password"
              required
              :error="errors.password_confirmation"
            />

            <Button
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full"
            >
              Reset password
            </Button>

            <p class="text-center text-body-small text-text-secondary">
              <router-link to="/login" class="text-primary-green hover:text-primary-green-hover font-medium">
                Back to sign in
              </router-link>
            </p>
          </form>
        </Card>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'

const route = useRoute()
const router = useRouter()

const form = ref({
  password: '',
  password_confirmation: '',
  token: ''
})

const errors = ref({})
const error = ref('')
const loading = ref(false)

onMounted(() => {
  form.value.token = route.params.token
})

const handleSubmit = async () => {
  errors.value = {}
  error.value = ''
  loading.value = true

  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match'
    loading.value = false
    return
  }

  try {
    // Bypass API call for now
    console.log('Password reset:', form.value)
    router.push('/login')
  } catch (err) {
    error.value = err.message || 'Failed to reset password. Please try again.'
    if (err.errors) {
      errors.value = err.errors
    }
  } finally {
    loading.value = false
  }
}
</script>

