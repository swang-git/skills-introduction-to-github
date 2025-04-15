// import Vue from 'vue'
// import Vuex from 'vuex'
import { createStore } from 'vuex'

import example from './module-example'
import arts from './arts'

// Vue.use(Vuex)

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation
 */

export default function (/* { ssrContext } */) {
  const Store = createStore({
    modules: {
      example,
      arts,
    },
    // enable strict mode (adds overhead!)
    // for dev mode and --debug builds only
    strict: process.env.DEBUGGING
  })

  return Store
}
