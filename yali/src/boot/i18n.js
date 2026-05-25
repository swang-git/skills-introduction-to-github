import { boot } from 'quasar/wrappers'
import { createI18n } from 'vue-i18n'

// 导入语言文件（你自己的语言包路径）
import messages from 'src/i18n'

export default boot(({ app }) => {
  const i18n = createI18n({
    legacy: false, // 👈 关键：关闭传统模式，开启 Composition API 模式
    locale: 'en-US', // 默认语言
    messages
  })

  // 将 i18n 实例挂载到 Vue 应用
  app.use(i18n)
})
// import { defineBoot } from '#q-app/wrappers'
// import { createI18n } from 'vue-i18n'
// import messages from 'src/i18n'

// export default defineBoot(({ app }) => {
//   const i18n = createI18n({
//     locale: 'en-US',
//     globalInjection: true,
//     messages
//   })

//   // Set i18n instance on app
//   app.use(i18n)
// })
