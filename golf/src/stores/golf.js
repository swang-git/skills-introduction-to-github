// src/stores/counter.js
import { defineStore } from 'pinia';

export const useGolfStore = defineStore('golf', {
  state: () => ({
    count: 0,
    page: null,
    pageTitle: null,
    par: 0,
    rating: 0,
    slope: 0,
    yardage: 0,
    holes: {},
    yards: {},
    hcaps: {},
    tournament: {},
    tournamentId: 0,
    usertype: null,
    showSelectedGame: false,
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
