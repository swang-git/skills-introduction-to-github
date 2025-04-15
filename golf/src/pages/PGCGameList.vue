<template>
<div style="padding-top:1px">
  <div v-for="(year) in yearList" :key=year>
    <q-expansion-item class="bg-teal-9" dark header-style="font-size:18px" icon="golf_course" :label="year + ' PGC Games'" group="group">
      <div v-for="(g) in dalist.filter(p => p.year == year)" :key=g class="q-pt-sm q-px-xs">
      <q-card class="text-white bg-teal-10" bordered>
        <q-card-actions v-if="g.start_at>today() && PGCsAdmin" class="bg-teal-10" align="between">
          <span class="text-h5 q-px-sm"> {{ year }} {{ g.game }} </span>
          <!-- <q-btn v-if="isDesk" spread outline rounded label="do game grouping" @click="doGameGrouping(year, g.id, g.game_id)" /> -->
          <q-btn v-if="isDesk" spread outline rounded label="do game grouping" @click="doGameGrouping(year, g)" />
          <!-- <q-btn v-else outline rounded @click="gameGrouping(year, g.id, g.game_id)">Details</q-btn> -->
          <q-btn v-else outline rounded @click="doGameGrouping(year, g)">Details</q-btn>
        </q-card-actions>
        <q-card-actions v-else-if="g.start_at<today() && isScoreDone(year, g.game_id)" class="bg-teal-10" align="between">
          <span class="text-h5 q-px-sm"> {{ year }} {{ g.game }} </span>
          <q-btn v-if="isDesk" spread outline rounded label="show game results" @click="showGameResults(year, g.id, g.game_id, g.start_at)" />
          <q-btn v-else outline rounded @click="showGameResults(year, g.id, g.game_id, g.start_at)">Details</q-btn>
        </q-card-actions>
        <q-card-actions v-else-if="g.start_at<today() && PGCsAdmin" class="bg-teal-10" align="between">
          <span class="text-h5 q-px-sm"> {{ year }} {{ g.game }} </span>
          <q-btn v-if="isDesk" spread outline rounded label="enter game score" @click="enterGameScore(year, g.id, g.game_id, g.start_at)" />
          <q-btn v-else outline rounded @click="enterGameScore(year, g.id, g.game_id, g.start_at)">Details</q-btn>
        </q-card-actions>
        <q-card-section class="bg-teal-10">
          <ul style="font-size:18px;line-height:1.6">
            <li>Game on <span class="text-yellow">{{ g.start_at.replace(' ', ' start at ') }}</span> </li>
            <li>At {{ g.courseName }} </li>
            <li>Men play from {{ g.mtee }} </li>
            <li>Ladies Play from {{ g.ltee }} </li>
            <li>Fees: ${{ g.fees }} </li>
            <li v-if="g.teetime_gap>0">Teetime Gap: {{ g.teetime_gap }} </li>
            <li v-else>Shutgun: Start at {{ g.start_at.substring(11, 16) }} </li>
            <li v-if="g.mlongest != null">Men's Longest Drive Hole: {{ g.mlongest }} </li>
            <li v-if="g.llongest != null">Ladies' Longest Drive Hole: {{ g.llongest }} </li>
            <li v-if="g.ctp != null">Closest to PIN Hole: {{ g.ctp }} (men and ladies) </li>
            <li v-if="g.note != null">Notes: <div v-html="g.note" /> </li>
          </ul>
        </q-card-section>
      </q-card>
      </div>
    </q-expansion-item>
  </div>
  <PGCGameResults ref="refGameResults" />
  <PGCGameGrouping ref="refGameGrouping" />
</div>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, onMounted } from 'vue'
import { dayFunctions } from '../composables/dayFunctions'
const { today } = dayFunctions()
import { libFunctions } from '../composables/libFunctions'
const { store, dats, dalist, isDesk, PGCsAdmin } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios } = axiosFunctions()
import PGCGameResults from'./PGCGameResults'
import PGCGameGrouping from './PGCGameGrouping'
const yearList = ref([])
const year = ref(0)
const tmntId = ref(0)
const gameId = ref(0)
const gameStartAt = ref(null)
var gameList = []
var PGCPlayers = []
const refGameGrouping = ref(null)
const refGameResults = ref(null)

onMounted(() => {
  refGameGrouping
  refGameResults
})

console.log('-ST-PGCGameList')

store.pageTitle = 'PGC Game List'
store.page = 'PGCGameList'
getPGCGames()
emitter.on('golf-getPGCGamePlayers', (x) => setPGCGamePlayers(x))
emitter.on('golf-getPGCGames', (x) => setPGCGames(x.lst))

function isScoreDone (year, gameId) {
  const tmnt = gameList.filter(p => p.year == year && p.game_id == gameId)[0]
  // console.warn(`year=${year}, gameId=${gameId}`, tmnt)
  return tmnt.score_done > 0
}
function getPGCGames () {
  console.log('-fn-getPGCGames')
  const path = process.env.API + '/golf/getPGCGames/ALL'
  gaxios(path)
}
function setPGCGames (da) {
  console.log('-CK-fn-setPGCGames', da)
  gameList = da
  dats.value = da.sort((a, b) => a.start_at < b.start_at ? 1 : -1)
  yearList.value = [...new Set(dalist.value.map(p => p.start_at.substring(0, 4)))]
  console.log('-CK-dalist', dalist.value)
}
function setPGCGamePlayers (da) {
  console.log(`-CK-fn-setPGCGamePlayers tmntId=${tmntId.value} gameId=${gameId.value} year=${year.value} ${PGCsAdmin} gameStart=${gameStartAt.value} today=${today()}`, da)
  const tmnt = dalist.value.filter(p => { return p.year == year.value && p.id == tmntId.value && p.game_id == gameId.value })[0]
  if (PGCsAdmin && gameStartAt.value > today()) {
    console.log(`doGrouping`,da)
    PGCPlayers = da.PGCPlayers.filter(p => p.game_id === gameId.value)  // doGameGrouping
    refGameGrouping.value.openIt(PGCPlayers, tmnt)
  } else if (PGCsAdmin && gameStartAt.value < today()) {
    if (gameId.value === 6) PGCPlayers = da.PGCPlayers  // enterGameScore
    else PGCPlayers = da.PGCPlayers.filter(p => p.game_id === gameId.value)  // enterGameScore
    console.log(`show game results with PGCsAdmin gameId=${gameId.value}`, PGCPlayers[0])
    refGameResults.value.openIt(PGCPlayers, tmnt, gameStartAt.value < today())
  } else {
    console.log(`show game results`, da)
    PGCPlayers = da.PGCPlayers // showGameResults
    refGameResults.value.openIt(PGCPlayers, tmnt, gameStartAt.value < today())
  }
}
function getPGCGamePlayers (year, tmntId, gameId) {
  console.log(`-fn-getPGCGamePlayers tmntId=${tmntId} gameId=${gameId} year=${year}`)
  const path = process.env.API + '/golf/getPGCGamePlayers/' + tmntId + '/' + gameId + '/' + year
  gaxios(path)
}
function enterGameScore (yr, tId, gId, gStartAt) {
  year.value = yr
  tmntId.value = tId
  gameId.value = gId
  gameStartAt.value = gStartAt
  console.log(`-fn-enterGameScore year=${year.value} tmntId=${tmntId.value} gameId=${gameId.value}`)
  getPGCGamePlayers(year.value, tmntId.value, gameId.value)
}
function doGameGrouping (year, game) {
  year.value = year
  tmntId.value = game.id
  gameId.value = game.game_id
  gameStartAt.value = game.start_at
  console.log(`-fn-doGameGrouping for: year=${year} tmntId=${tmntId.value} gameId=${gameId.value}, gameStartAt=${gameStartAt.value}`)
  // console.log(`-fn-doGameGrouping for: ${year}`, game)
  getPGCGamePlayers(year, tmntId, gameId)
  // getPGCNotGroupedPlayers()
}
function showGameResults (yr, tId, gId) {
  year.value = yr
  tmntId.value = tId
  gameId.value = gId
  // gameStartAt.value = gStartAt
  console.log(`showGameResults for: ${year.value} tmntId=${tmntId.value} gameId=${gameId.value}`)
  getPGCGamePlayers(year.value, tmntId.value, gameId.value)
}
// function getLabel(game) {
//   const date = game.start_at.substring(0, 10)
//   if (isIM) return date.substring(5, 10) + ' (' + getDay(date) + ') ' + game.start_at + ' ' + game.game
//   return  '(' + getDay(date) + ') ' + game.start_at + ' ' + game.game
// }
// function getClass(game) {
//   let colr = game.date < today() ? 'text-grey-5' : 'text-white'
//   if (game.note === 'tentative') colr = 'text-amber'
//   return colr + ' text-h6 ellipsis q-pb-xs q-px-sm cursor-pointer'
// }
// function userSelected (game) {
//   console.log('-fn-userSelected Game', game)
//   $emit('user-selected', game)
// }
</script>
<style>
.active:hover {
  background: navy;
  color: gold;
}
/* div:hover { color: blue }
div:active { color: red }
div:link { color: gold } */
</style>
