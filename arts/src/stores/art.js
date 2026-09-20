// src/stores/counter.js
import { defineStore } from 'pinia'

export const useArtStore = defineStore('art', {
  state: () => ({
    count: 0,
    page: null,
    art: [],
    flw: [],
    // pageTile: null,
    isSearch: false,
    clickedCont: {},
    qids: [],
    // clickedIndex: -1,
    clickedArt: {},
    topTit: '省千里路 🏠 破万卷书'
  }),
  actions: {
    addClicked(key, obj) {
      // ✅ This adds a new key, WILL NOT overwrite other keys
      this.clickedCont[key] = obj
    },
    removeClicked(key) {
      delete this.clickedCont[key]
    },
    resetClicked() {
      this.clickedCont = {} // ⚠️ This clears everything
    },
    increment() {
      this.count++
    },
    decrement() {
      this.count--
    }
  }
})
