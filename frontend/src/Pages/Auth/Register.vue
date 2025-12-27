<template>
  <GuestLayout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div>
          <h2 class="text-h1 text-center text-text-primary">Create your account</h2>
          <p class="mt-2 text-body text-center text-text-secondary">
            Start tracking your studies and improving your results
          </p>
        </div>
        
        <Card padding="lg">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
              <p class="text-body-small text-accent-red">{{ error }}</p>
            </div>

            <Input
              v-model="form.name"
              type="text"
              label="Full name"
              placeholder="John Doe"
              required
              :error="errors.name"
            />

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
              placeholder="Create a strong password"
              required
              :error="errors.password"
            />

            <Input
              v-model="form.password_confirmation"
              type="password"
              label="Confirm password"
              placeholder="Confirm your password"
              required
              :error="errors.password_confirmation"
            />

            <div class="flex items-start">
              <input
                id="terms"
                v-model="form.accept_terms"
                type="checkbox"
                required
                class="h-4 w-4 mt-0.5 text-primary-green focus:ring-primary-green border-border-medium rounded"
              />
              <label for="terms" class="ml-2 block text-body-small text-text-secondary">
                I agree to the
                <a href="#" class="text-primary-green hover:text-primary-green-hover">Terms of Service</a>
                and
                <a href="#" class="text-primary-green hover:text-primary-green-hover">Privacy Policy</a>
              </label>
            </div>

            <Button
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full"
            >
              Create account
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
              @click="handleGoogleRegister"
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
              Sign up with Google
            </Button>

            <p class="text-center text-body-small text-text-secondary">
              Already have an account?
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/Composables/useAuth'
import { useAuthStore } from '@/Stores/auth'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'
import Card from '@/Components/Common/Card.vue'
import Button from '@/Components/Common/Button.vue'
import Input from '@/Components/Common/Input.vue'

const router = useRouter()
const { register } = useAuth()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  accept_terms: false
})

const errors = ref({})
const error = ref('')
const loading = ref(false)
const googleLoading = ref(false)

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
    await register(form.value)
  } catch (err) {
    error.value = err.message || 'Registration failed. Please try again.'
    if (err.errors) {
      errors.value = err.errors
    }
  } finally {
    loading.value = false
  }
}

const handleGoogleRegister = async () => {
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

    // Redirect to onboarding or dashboard
    router.push('/onboarding/profile')
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
  // Preload Google script
  if (import.meta.env.VITE_GOOGLE_CLIENT_ID) {
    loadGoogleScript().catch(() => {
      console.warn('Google OAuth not configured. Set VITE_GOOGLE_CLIENT_ID in your .env file.')
    })
  }
})
</script>

