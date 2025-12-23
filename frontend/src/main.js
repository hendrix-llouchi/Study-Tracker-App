import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router, { setupRouterGuard } from './router'
import App from './App.vue'
import './css/app.css'

// Add error handling
window.addEventListener('error', (event) => {
  console.error('Global error:', event.error)
})

window.addEventListener('unhandledrejection', (event) => {
  console.error('Unhandled promise rejection:', event.reason)
})

const app = createApp(App)
const pinia = createPinia()

// Initialize Pinia first
app.use(pinia)

// Then set up router guard (which needs Pinia)
setupRouterGuard(router)

// Then use router
app.use(router)

app.config.errorHandler = (err, instance, info) => {
  console.error('Vue error:', err)
  console.error('Component:', instance)
  console.error('Info:', info)
}

app.mount('#app')
