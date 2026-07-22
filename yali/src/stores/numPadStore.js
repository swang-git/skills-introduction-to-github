// src/stores/acomboStore.js
import { defineStore } from 'pinia'
import emitter from 'tiny-emitter/instance.js'

export const useNumPadStore = defineStore('numpad', {
  state: () => ({
    isOpen: false // GLOBAL visibility state
  }),
  actions: {
    close() { this.isOpen = false },
<<<<<<< HEAD
    open(flag, tit, msg) { 
      this.isOpen = true
=======
    open(flag, tit, msg) {
      this.isOpen = true
      console.log(`-fn-numPadStore.open isOpen=${this.isOpen} flag=${flag} tit=${tit} msg=${msg}`)
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
      emitter.emit('open-NumPad', flag, tit, msg)
    }
  }
})
