/**
 * Error handling utility functions
 */

/**
 * Extract user-friendly error message from API error response
 * @param {Object} error - Error object from API
 * @returns {string} User-friendly error message
 */
export function getErrorMessage(error) {
  if (!error) {
    return 'An unexpected error occurred'
  }

  // If error has a message property, use it
  if (error.message) {
    return error.message
  }

  // If error has errors object (validation errors), format them
  if (error.errors && typeof error.errors === 'object') {
    const errorMessages = []
    
    // Handle Laravel validation errors (array format)
    Object.keys(error.errors).forEach(key => {
      const fieldErrors = Array.isArray(error.errors[key]) 
        ? error.errors[key] 
        : [error.errors[key]]
      
      fieldErrors.forEach(msg => {
        if (msg && typeof msg === 'string') {
          errorMessages.push(msg)
        }
      })
    })
    
    if (errorMessages.length > 0) {
      return errorMessages.join('. ')
    }
  }

  // Handle specific error codes
  if (error.error_code) {
    switch (error.error_code) {
      case 'NETWORK_ERROR':
        return 'Unable to connect to the server. Please check your internet connection.'
      case 'UNAUTHORIZED':
        return 'You are not authorized to perform this action. Please log in again.'
      case 'FORBIDDEN':
        return 'You do not have permission to perform this action.'
      case 'NOT_FOUND':
        return 'The requested resource was not found.'
      case 'VALIDATION_ERROR':
        return 'Please check your input and try again.'
      case 'SERVER_ERROR':
        return 'A server error occurred. Please try again later.'
      default:
        return error.message || 'An error occurred'
    }
  }

  // Fallback
  return error.message || 'An unexpected error occurred. Please try again.'
}

/**
 * Extract validation errors from API error response
 * @param {Object} error - Error object from API
 * @returns {Object} Object with field names as keys and error messages as values
 */
export function getValidationErrors(error) {
  if (!error || !error.errors) {
    return {}
  }

  const validationErrors = {}
  
  // Handle Laravel validation errors format
  if (typeof error.errors === 'object') {
    Object.keys(error.errors).forEach(key => {
      const fieldErrors = Array.isArray(error.errors[key])
        ? error.errors[key]
        : [error.errors[key]]
      
      // Take first error message for each field
      if (fieldErrors.length > 0 && fieldErrors[0]) {
        validationErrors[key] = typeof fieldErrors[0] === 'string' 
          ? fieldErrors[0] 
          : String(fieldErrors[0])
      }
    })
  }

  return validationErrors
}

/**
 * Check if error is a network error
 * @param {Object} error - Error object
 * @returns {boolean}
 */
export function isNetworkError(error) {
  return error?.error_code === 'NETWORK_ERROR' || 
         (error?.message && error.message.includes('Network error'))
}

/**
 * Check if error is an authentication error
 * @param {Object} error - Error object
 * @returns {boolean}
 */
export function isAuthError(error) {
  return error?.error_code === 'UNAUTHORIZED' || 
         error?.status === 401
}

/**
 * Format error for console logging
 * @param {string} context - Context where error occurred (e.g., 'Failed to fetch plans')
 * @param {Object} error - Error object
 * @returns {string} Formatted error message
 */
export function formatErrorForLog(context, error) {
  const message = getErrorMessage(error)
  return `${context}: ${message}`
}

