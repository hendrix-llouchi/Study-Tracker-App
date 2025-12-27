<template>
  <GuestLayout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div>
          <h2 class="text-h1 text-center text-text-primary">Reset your password</h2>
          <p class="mt-2 text-body text-center text-text-secondary">
            Enter your email address and we'll send you a link to reset your password
          </p>
        </div>
        
        <Card padding="lg">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div v-if="success" class="bg-primary-green-bg border border-primary-green-light rounded-md p-4">
              <p class="text-body-small text-text-success">
                {{ success }}
              </p>
            </div>

            <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
              <p class="text-body-small text-accent-red">{{ error }}</p>
            </div>

            <Input
              v-model="form.email"
              type="email"
              label="Email address"
              placeholder="you@example.com"
              required
              :error="errors.email"
            />

            <Button
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full"
            >
              Send reset link
            </Button>

            <p class="text-center text-body-small text-text-secondary">
              Remember your password?
              <router-link to="/login" class="text-primary-green hover:text-primary-green-hover font-medium">
                Sign in
              </router-link>
            </p>
          </form>
        </Card>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'

const form = ref({
  email: ''
})

const errors = ref({})
const error = ref('')
const success = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  errors.value = {}
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    const response = await api.post('/auth/forgot-password', {
      email: form.value.email
    })

    if (response.success) {
      success.value = response.message || 'Password reset link has been sent to your email address.'
      form.value.email = ''
    } else {
      error.value = response.message || 'Failed to send reset link. Please try again.'
    }
  } catch (err) {
    // Handle validation errors from backend
    if (err.errors) {
      errors.value = {}
      Object.keys(err.errors).forEach(key => {
        if (Array.isArray(err.errors[key])) {
          errors.value[key] = err.errors[key][0]
        } else {
          errors.value[key] = err.errors[key]
        }
      })
      // Set general error message if email field has error
      if (errors.value.email) {
        error.value = errors.value.email
      }
    } else {
      error.value = err.message || 'Failed to send reset link. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

