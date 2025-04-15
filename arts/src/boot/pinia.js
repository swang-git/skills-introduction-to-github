import { defineBoot } from '#q-app/wrappers'
import { createPinia } from 'pinia'

defineBoot(({ app }) => {
  // Create Pinia instance
  const pinia = createPinia()
  console.log('initializing pinia')
  // Use Pinia in the Vue app
  app.use(pinia)
})

