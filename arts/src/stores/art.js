// src/stores/counter.js
import { defineStore } from 'pinia'

export const useArtStore = defineStore('art', {
  state: () => ({
    count: 0,
    page: null,
    art: [],
    flw: [],
    isSearch: false,
    tag: null,
    ymd: null,
    qid: null,
    searchCat: null,
    searchTxt: null,
    contDict: {},
    qids: {},
    // clickedCont: null,
    clickedIdx: {},
    clickedArt: {},
    topTit: '省千里路 🏠 破万卷书'
  }),
  actions: {
    addContDict(key, obj) {
      // ✅ This adds a new key, WILL NOT overwrite other keys
      this.contDict[key] = obj
    },
    removeContDict(key) {
      delete this.contDict[key]
    },
    resetContDict() {
      this.contDict = {} // ⚠️ This clears everything
    },
    increment() {
      this.count++
    },
    decrement() {
      this.count--
    }
  }
})
