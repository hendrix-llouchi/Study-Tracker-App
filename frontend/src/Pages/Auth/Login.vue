<template>
  <GuestLayout>
    <div class="min-h-[calc(100vh-120px)] flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-neutral-gray50 via-white to-primary-green-bg/30">
      <div class="max-w-md w-full">
        <!-- Header Section -->
        <div class="text-center mb-8 sm:mb-10">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-green to-primary-green-hover rounded-2xl shadow-lg shadow-primary-green/20 mb-4 transform transition-transform hover:scale-105">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <h1 class="text-3xl sm:text-4xl font-bold text-text-primary mb-2 tracking-tight">
            Welcome back
          </h1>
          <p class="text-base text-text-secondary">
            Sign in to continue to your account
          </p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-border-light p-8 sm:p-10 backdrop-blur-sm">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Success Message -->
            <Transition
              enter-active-class="transition ease-out duration-300"
              enter-from-class="opacity-0 transform translate-y-2"
              enter-to-class="opacity-100 transform translate-y-0"
              leave-active-class="transition ease-in duration-200"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <div v-if="success" class="bg-primary-green-bg border-l-4 border-primary-green rounded-md p-4 flex items-start">
                <svg class="w-5 h-5 text-primary-green mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-text-success font-medium">{{ success }}</p>
              </div>
            </Transition>

            <!-- Error Message -->
            <Transition
              enter-active-class="transition ease-out duration-300"
              enter-from-class="opacity-0 transform translate-y-2"
              enter-to-class="opacity-100 transform translate-y-0"
              leave-active-class="transition ease-in duration-200"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <div v-if="error" class="bg-red-50 border-l-4 border-accent-red rounded-md p-4 flex items-start">
                <svg class="w-5 h-5 text-accent-red mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-accent-red font-medium">{{ error }}</p>
              </div>
            </Transition>

            <!-- Email Input -->
            <div class="space-y-2">
              <Input
                v-model="form.email"
                type="email"
                label="Email address"
                placeholder="Enter your email"
                required
                :error="errors.email"
                class="transition-all duration-200"
              />
            </div>

            <!-- Password Input -->
            <div class="space-y-2">
              <Input
                v-model="form.password"
                type="password"
                label="Password"
                placeholder="Enter your password"
                required
                :error="errors.password"
                class="transition-all duration-200"
              />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between pt-1">
              <label class="flex items-center group cursor-pointer">
                <input
                  id="remember-me"
                  v-model="form.remember"
                  type="checkbox"
                  class="h-4 w-4 text-primary-green focus:ring-2 focus:ring-primary-green focus:ring-offset-0 border-border-medium rounded transition-all duration-200 cursor-pointer"
                />
                <span class="ml-2.5 text-sm text-text-secondary group-hover:text-text-primary transition-colors duration-200">
                  Remember me
                </span>
              </label>

              <router-link
                to="/forgot-password"
                class="text-sm font-medium text-primary-green hover:text-primary-green-hover transition-colors duration-200 hover:underline"
              >
                Forgot password?
              </router-link>
            </div>

            <!-- Sign In Button -->
            <Button
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full mt-6 shadow-md hover:shadow-lg transition-shadow duration-200"
            >
              <span v-if="!loading">Sign in</span>
              <span v-else>Signing in...</span>
            </Button>

            <!-- Divider -->
            <div class="relative my-6">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-border-default"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white text-text-secondary">Or continue with</span>
              </div>
            </div>

            <!-- Google Sign In Button -->
            <Button
              type="button"
              variant="secondary"
              size="lg"
              class="w-full border-2 hover:border-primary-green transition-all duration-200"
              :loading="googleLoading"
              :disabled="googleLoading || loading"
              @click="handleGoogleLogin"
            >
              <svg class="w-5 h-5 mr-2.5" viewBox="0 0 24 24">
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
              <span v-if="!googleLoading">Sign in with Google</span>
              <span v-else>Connecting...</span>
            </Button>

            <!-- Sign Up Link -->
            <div class="pt-4 text-center">
              <p class="text-sm text-text-secondary">
                Don't have an account?
                <router-link 
                  to="/register" 
                  class="ml-1 font-semibold text-primary-green hover:text-primary-green-hover transition-colors duration-200 hover:underline"
                >
                  Sign up
                </router-link>
              </p>
            </div>
          </form>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 text-center">
          <p class="text-xs text-text-tertiary flex items-center justify-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Your data is encrypted and secure
          </p>
        </div>
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

<style scoped>
/* Smooth transitions for form elements */
input[type="checkbox"]:checked {
  background-color: rgb(52, 211, 153);
  border-color: rgb(52, 211, 153);
}

input[type="checkbox"]:focus {
  outline: none;
  ring: 2px;
  ring-color: rgb(52, 211, 153);
}
</style>
