// import Vue from 'vue'
// import Vuex from 'vuex'
import { createStore } from 'vuex'

import apps from './apps'
import example from './module-example'
import expense from './expense'
import exp from './exp'
import arts from './arts'
import golf from './golf'
import watcher from './watcher'
import glucosecheck from './glucosecheck'

// Vue.use(Vuex)

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation
 */

export default function (/* { ssrContext } */) {
  const Store = createStore({
    modules: {
      apps,
      example,
      expense,
      exp,
      arts,
      golf,
      watcher,
      glucosecheck
    },
    // enable strict mode (adds overhead!)
    // for dev mode and --debug builds only
    strict: process.env.DEBUGGING
  })

  return Store
}
