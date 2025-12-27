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
              v-model="form.email"
              type="email"
              label="Email address"
              placeholder="you@example.com"
              required
              :error="errors.email"
              :disabled="!!route.query.email"
            />

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
import api from '@/services/api'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'

const route = useRoute()
const router = useRouter()

const form = ref({
  password: '',
  password_confirmation: '',
  token: '',
  email: ''
})

const errors = ref({})
const error = ref('')
const loading = ref(false)

onMounted(() => {
  // Get token from route params
  form.value.token = route.params.token || ''
  
  // Get email from query params (Laravel typically includes email in the reset URL)
  form.value.email = route.query.email || ''
  
  // If no email in query, show error
  if (!form.value.email) {
    error.value = 'Invalid reset link. Please request a new password reset link.'
  }
  
  // If no token, show error
  if (!form.value.token) {
    error.value = 'Invalid reset link. Please request a new password reset link.'
  }
})

const handleSubmit = async () => {
  errors.value = {}
  error.value = ''
  loading.value = true

  // Client-side validation
  if (!form.value.email) {
    error.value = 'Email is required. Please use the link from your email.'
    loading.value = false
    return
  }

  if (!form.value.token) {
    error.value = 'Reset token is required. Please use the link from your email.'
    loading.value = false
    return
  }

  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match'
    loading.value = false
    return
  }

  if (form.value.password.length < 8) {
    errors.value.password = 'Password must be at least 8 characters long'
    loading.value = false
    return
  }

  try {
    const response = await api.post('/auth/reset-password', {
      token: form.value.token,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })

    if (response.success) {
      // Show success message and redirect to login
      router.push({
        path: '/login',
        query: { reset: 'success' }
      })
    } else {
      error.value = response.message || 'Failed to reset password. Please try again.'
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
      // Set general error message if email or token field has error
      if (errors.value.email) {
        error.value = errors.value.email
      } else if (errors.value.token) {
        error.value = errors.value.token
      } else if (errors.value.password) {
        error.value = errors.value.password
      }
    } else {
      error.value = err.message || 'Failed to reset password. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

