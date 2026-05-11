// import { boot } from 'quasar/wrappers'
// import { createI18n } from 'vue-i18n'
// import messages from 'src/i18n'

// const i18n = createI18n({
//   locale: 'en-US',
//   messages
// })

// export default boot(({ app }) => {
//   // Set i18n instance on app
//   app.use(i18n)
// })

// export { i18n }

import { createI18n } from 'vue-i18n'
import messages from 'src/i18n'

export default ({ app }) => {
  const i18n = createI18n({
    legacy: false, // 👈 THIS DISABLES LEGACY & REMOVES THE WARNING
    locale: 'en',
    messages
  })

  app.use(i18n)
}