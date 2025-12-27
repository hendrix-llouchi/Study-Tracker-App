<template>
  <GuestLayout>
    <div class="min-h-[calc(100vh-120px)] flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-6 sm:space-y-8">
        <div>
          <h2 class="text-h1 text-center text-text-primary">Welcome back</h2>
          <p class="mt-2 text-body text-center text-text-secondary">
            Sign in to your account to continue
          </p>
        </div>
        
        <Card padding="lg">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div v-if="success" class="bg-primary-green-bg border border-primary-green-light rounded-md p-4">
              <p class="text-body-small text-text-success">{{ success }}</p>
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

            <Input
              v-model="form.password"
              type="password"
              label="Password"
              placeholder="Enter your password"
              required
              :error="errors.password"
            />

            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="remember-me"
                  v-model="form.remember"
                  type="checkbox"
                  class="h-4 w-4 text-primary-green focus:ring-primary-green border-border-medium rounded"
                />
                <label for="remember-me" class="ml-2 block text-body-small text-text-secondary">
                  Remember me
                </label>
              </div>

              <router-link
                to="/forgot-password"
                class="text-body-small text-primary-green hover:text-primary-green-hover"
              >
                Forgot password?
              </router-link>
            </div>

            <Button
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full"
            >
              Sign in
            </Button>

            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-border-default"></div>
              </div>
              <div class="relative flex justify-center text-body-small">
                <span class="px-2 bg-neutral-white text-text-secondary">Or continue with</span>
              </div>
            </div>

            <Button
              type="button"
              variant="secondary"
              size="lg"
              class="w-full"
              :loading="googleLoading"
              :disabled="googleLoading || loading"
              @click="handleGoogleLogin"
            >
              <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                <path
                  fill="currentColor"
                  d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                />
                <path
                  fill="currentColor"
                  d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                />
                <path
                  fill="currentColor"
                  d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                />
                <path
                  fill="currentColor"
                  d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                />
              </svg>
              Sign in with Google
            </Button>

            <p class="text-center text-body-small text-text-secondary">
              Don't have an account?
              <router-link to="/register" class="text-primary-green hover:text-primary-green-hover font-medium">
                Sign up
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
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/Composables/useAuth'
import { useAuthStore } from '@/Stores/auth'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'

const router = useRouter()
const route = useRoute()
const { login } = useAuth()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
  remember: false
})

const errors = ref({})
const error = ref('')
const success = ref('')
const loading = ref(false)
const googleLoading = ref(false)

const handleSubmit = async () => {
  errors.value = {}
  error.value = ''
  loading.value = true

  try {
    await login(form.value)
  } catch (err) {
    // Handle validation errors from backend
    if (err.errors) {
      errors.value = {}
      // Laravel validation errors come as { field: ['message1', 'message2'] }
      Object.keys(err.errors).forEach(key => {
        // Convert array of messages to single string
        if (Array.isArray(err.errors[key])) {
          errors.value[key] = err.errors[key][0]
        } else {
          errors.value[key] = err.errors[key]
        }
      })
      // Set general error message if email or password field has error
      if (errors.value.email) {
        error.value = errors.value.email
      } else if (errors.value.password) {
        error.value = errors.value.password
      }
    } else {
      // Handle other error messages
      error.value = err.message || 'Invalid credentials. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const handleGoogleLogin = async () => {
  googleLoading.value = true
  error.value = ''

  try {
    // Check if Google Client ID is configured
    if (!import.meta.env.VITE_GOOGLE_CLIENT_ID) {
      error.value = 'Google OAuth is not configured. Please contact support.'
      googleLoading.value = false
      return
    }

    // Load Google Identity Services script if not already loaded
    if (!window.google) {
      await loadGoogleScript()
    }

    // Set up a timeout to reset loading state if callback doesn't fire
    const timeoutId = setTimeout(() => {
      if (googleLoading.value) {
        console.warn('Google OAuth timeout - resetting loading state')
        googleLoading.value = false
        error.value = 'Google authentication timed out. Please try again.'
      }
    }, 60000) // 60 second timeout

    // Use Google Identity Services with popup flow
    const tokenClient = window.google.accounts.oauth2.initTokenClient({
      client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID,
      scope: 'email profile',
      callback: async (response) => {
        // Clear timeout since callback fired
        clearTimeout(timeoutId)
        
        if (response.access_token) {
          await handleGoogleTokenResponse(response.access_token)
        } else if (response.error) {
          console.error('Google OAuth error:', response.error)
          error.value = 'Google authentication was cancelled or failed. Please try again.'
          googleLoading.value = false
        } else {
          // No access token and no error - user likely closed popup
          console.warn('Google OAuth: No access token and no error - user may have closed popup')
          error.value = 'Google authentication was cancelled. Please try again.'
          googleLoading.value = false
        }
      }
    })
    
    tokenClient.requestAccessToken()
  } catch (err) {
    console.error('Google OAuth error:', err)
    error.value = 'Failed to initialize Google sign-in. Please try again.'
    googleLoading.value = false
  }
}

const handleGoogleTokenResponse = async (accessToken) => {
  try {
    // Send only token to backend - let Socialite handle verification
    await authStore.googleAuth({
      token: accessToken
    })

    // Redirect to dashboard
    router.push('/dashboard')
  } catch (err) {
    console.error('Google token response error:', err)
    // Handle API errors with more detail
    if (err.errors) {
      // Backend validation errors
      const errorMessages = Object.values(err.errors).flat()
      error.value = errorMessages[0] || 'Google authentication failed. Please try again.'
    } else if (err.message) {
      error.value = err.message
    } else {
      error.value = 'Google authentication failed. Please try again.'
    }
    googleLoading.value = false
  }
}

const loadGoogleScript = () => {
  return new Promise((resolve, reject) => {
    if (window.google && window.google.accounts) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = 'https://accounts.google.com/gsi/client'
    script.async = true
    script.defer = true
    script.onload = () => resolve()
    script.onerror = () => reject(new Error('Failed to load Google script'))
    document.head.appendChild(script)
  })
}

onMounted(() => {
  // Check for password reset success message
  if (route.query.reset === 'success') {
    success.value = 'Password reset successful! You can now sign in with your new password.'
    // Clear the query parameter
    router.replace({ query: {} })
  }

  // Preload Google script
  if (import.meta.env.VITE_GOOGLE_CLIENT_ID) {
    loadGoogleScript().catch(() => {
      console.warn('Google OAuth not configured. Set VITE_GOOGLE_CLIENT_ID in your .env file.')
    })
  }
})
</script>

