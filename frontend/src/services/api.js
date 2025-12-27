import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1'

console.log('API URL configured as:', API_BASE_URL)

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000, // 30 seconds
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true
})

// Request interceptor
apiClient.interceptors.request.use(
  config => {
    // Ensure method is preserved (axios should set this automatically, but we're being explicit)
    const method = config.method || 'get'
    console.log('Making request:', {
      method: method.toUpperCase(),
      url: config.url,
      baseURL: config.baseURL,
      fullURL: config.baseURL + config.url,
      data: config.data ? (typeof config.data === 'object' ? JSON.stringify(config.data).substring(0, 100) : config.data) : undefined
    })
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => {
    console.error('Request error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor
apiClient.interceptors.response.use(
  response => {
    console.log('Response received:', response.status)
    return response.data
  },
  error => {
    console.error('Response error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status,
      url: error.config?.url,
      method: error.config?.method?.toUpperCase() || 'UNKNOWN',
      baseURL: error.config?.baseURL
    })

    // Network error (no response from server) - includes timeout errors
    if (!error.response) {
      const isTimeout = error.code === 'ECONNABORTED' || error.message?.includes('timeout')
      const networkError = {
        success: false,
        message: isTimeout 
          ? 'Request timed out. Please check if the backend server is running on http://127.0.0.1:8000'
          : 'Network error: Unable to connect to the server. Please make sure the backend is running on http://127.0.0.1:8000',
        error_code: isTimeout ? 'TIMEOUT_ERROR' : 'NETWORK_ERROR',
        errors: {
          network: [isTimeout 
            ? 'The request timed out. Make sure the Laravel backend is running: `php artisan serve`'
            : 'Cannot connect to the API server. Is the backend running?']
        }
      }
      console.error('Network Error:', error.message, {
        code: error.code,
        config: {
          url: error.config?.url,
          baseURL: error.config?.baseURL,
          method: error.config?.method
        }
      })
      return Promise.reject(networkError)
    }

    if (error.response?.status === 401) {
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

export default apiClient


