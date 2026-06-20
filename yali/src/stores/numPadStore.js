// src/stores/acomboStore.js
import { defineStore } from 'pinia'
import emitter from 'tiny-emitter/instance.js'

export const useNumPadStore = defineStore('numpad', {
  state: () => ({
    isOpen: false // GLOBAL visibility state
  }),
  actions: {
    close() { this.isOpen = false },
    open(flag, tit, msg) {
      this.isOpen = true
      console.log(`-fn-numPadStore.open isOpen=${this.isOpen} flag=${flag} tit=${tit} msg=${msg}`)
      emitter.emit('open-NumPad', flag, tit, msg)
    }
  }
})
