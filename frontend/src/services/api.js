import axios from 'axios'

// Get API URL from environment or use default
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'

console.log('API URL configured as:', API_URL)

// Create axios instance
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Important for Sanctum SPA authentication
  timeout: 10000, // 10 second timeout
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    return response.data
  },
  (error) => {
    // Network error (no response from server)
    if (!error.response) {
      const networkError = {
        success: false,
        message: 'Network error: Unable to connect to the server. Please make sure the backend is running on http://localhost:8000',
        error_code: 'NETWORK_ERROR',
        errors: {
          network: ['Cannot connect to the API server. Is the backend running?']
        }
      }
      console.error('Network Error:', error.message)
      return Promise.reject(networkError)
    }
    
    if (error.response?.status === 401) {
      // Unauthorized - clear token and redirect to login
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    
    // Return error response data if available
    if (error.response?.data) {
      return Promise.reject(error.response.data)
    }
    
    // Fallback error
    return Promise.reject({
      success: false,
      message: error.message || 'An error occurred',
      error_code: 'UNKNOWN_ERROR'
    })
  }
)

export default api

