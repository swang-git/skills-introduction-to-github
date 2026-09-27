// src/stores/counter.js
import { defineStore } from 'pinia'
import axios from 'axios'

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
    clickedIdx: {},
    clickedArt: {},
    topTit: '省千里路 🏠 破万卷书'
  }),
  // persist: {
  //   storage: localStorage, // use sessionStorage if you want it cleared when tab closed
  //   paths: ['contDict','qids','clickedIdx'] // ONLY persist selected fields, NOT everything
  // },
  actions: {
    // async fetchArtFromLaravel() {
    //   // call Laravel /api/me endpoint
    //   const res = await axios.get('/api/me')
    //   console.error(`-fn-%c fetchArtFromLaravel`, 'color:pink', rec)
    //   this.$patch(res.data)
    // },
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
