// src/stores/acomboStore.js
import { defineStore } from 'pinia'
// import emitter from 'tiny-emitter/instance.js'

export const useAdminStore = defineStore('admin', {
  state: () => ({
    isOn: false, // GLOBAL visibility state
    isCleanup: false
  }),
  actions: {
    off() { this.isOn = false },
    on() { this.isOn = true }
  }
})

