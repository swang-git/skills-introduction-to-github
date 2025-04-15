// src/stores/counter.js
import { defineStore } from 'pinia'

export const useArtStore = defineStore('art', {
  state: () => ({
    count: 0,
    page: null,
    art: [],
    flw: [],
    // pageTile: null,
    clickedCont: {},
    clickedIndex: -1,
    topTit: '省千里路 🏠 破万卷书',
  }),
  actions: {
    increment() {
      this.count++
    },
    decrement() {
      this.count--
    },
  },
})
