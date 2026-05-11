import { route } from 'quasar/wrappers'
import { createRouter, createMemoryHistory, createWebHistory, createWebHashHistory } from 'vue-router'
import routes from './routes'

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default route(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory)

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    // base: '/yali', // NO /apps HERE
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.MODE === 'ssr' ? void 0 : process.env.VUE_ROUTER_BASE)
  })

  // Redirect /apps/arts/* to /arts/*
  Router.beforeEach((to, from, next) => {
    if (to.path.startsWith('/apps/arts')) {
      const newPath = to.path.replace(/^\/apps\/arts/, '/arts')
      return next({ path: newPath, replace: true })
    }
    next()
  })

  // Router.afterEach((to, from) => {
  //   if (from.path.startsWith('/apps/arts') && to.path.startsWith('/arts')) {
  //     window.location.reload()
  //   }
  // })

  // Router.beforeEach((to, from, next) => {
  //   if (to.path.startsWith('/apps/arts')) {
  //     const newPath = to.path.replace(/^\/apps\/arts/, '/arts')
  //     // Hard redirect to force full page reload
  //     window.location.replace(newPath)
  //     return
  //   }
  //   next()
  // })

  return Router
})
