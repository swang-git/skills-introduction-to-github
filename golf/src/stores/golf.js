// src/stores/counter.js
import { defineStore } from 'pinia';

export const useGolfStore = defineStore('golf', {
  state: () => ({
    count: 0,
    page: null,
    pageTile: null,
    par: 0,
    rating: 0,
    slope: 0,
    yardage: 0,
    holes: {},
    yards: {},
    hcaps: {},
    tounament: {},
    usertype: null,
  }),
  actions: {
    increment() {
      this.count++;
    },
    decrement() {
      this.count--;
    },
  },
});
