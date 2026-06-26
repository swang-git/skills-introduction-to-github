import { defineStore } from 'pinia'

export const useAppsStore = defineStore('apps', {
  state: () => ({
    count: 0,
    page: null,
    pageTile: null,
    clickedCont: {},
    clickedIndex: -1,
    userType: null,
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
