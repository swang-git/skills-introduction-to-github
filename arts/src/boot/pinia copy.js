import { createPinia } from 'pinia'

export default ({ app }) => {
  // Create Pinia instance
  const pinia = createPinia()
  console.log('initializing pinia')
  // Use Pinia in the Vue app
  app.use(pinia)
}
