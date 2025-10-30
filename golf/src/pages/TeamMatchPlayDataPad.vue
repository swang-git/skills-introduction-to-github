<template>
<!-- <q-dialog v-model="opened" :maximized="iPhoneX" :full-width="iPhoneX"> -->
<q-dialog v-model="opened" maximized>
  <q-card square class="text-h6 text-white bg-cyan-10">
    <q-card-section class="inset-shadow-down text-center bg-cyan-9" style="z-index:1">
      <!-- <div :class="{'text-h5':isDesk, 'text-body1':iPhoneX}" class="cursor-pointer" v-close-popup> {{ tit }} </div> -->
      <div class="cursor-pointer;text-h6" v-close-popup> {{ tit }} </div>
      <div class="q-pt-sm text-h6"> {{ year }} Handicap is <b class="text-black">{{ handicap }}</b> ({{ numBestIdxDiff }} / {{ totalGames }}) </div>
      <div class="row justify-between">
        <div><q-btn round glossy color="amber-9" icon="chevron_left" v-close-popup /></div>
        <div class="q-mt-">
          <q-fab glossy color="purple" padding="xs sm sm" icon="年" direction="down" >
            <q-fab-action v-for="yr in years" :key="yr" color="primary" @click="setYear(yr)" icon="history" :label="yr" class="text-h6" />
          </q-fab>
        </div>
        <div><q-btn round glossy color="green" icon="sort" @click="setSortby()" /></div>
        <div><q-btn round glossy color="pink" icon="trending_down" @click="showPlayDataChart()" /></div>
      </div>
    </q-card-section>
    <q-card-section style="margin:-20px 0 0 0">
      <tr v-for="(p, i) in compGScores" :key="p" :class="getBGcolor(p, i)">
        <td v-if="isDesk" class="text-right q-pl-none">{{ i+1 }}</td>
        <td v-if="isDesk" class="ellipsis text-no-wrap cursor-pointer q-pl-sm" v-close-popup style="width:400px">{{ p.name }}</td>
        <td v-else class="ellipsis text-no-wrap cursor-pointer q-pl-xs" v-close-popup style="width:210px">{{ p.name.substring(0, 18) }}</td>
        <!-- <td class="text-center text-no-wrap q-pl-xs cursor-pointer" @click="showHandicapChart()">{{ p.start_at.substring(0, 10) }}</td> -->
        <td class="text-center text-no-wrap q-pl-xs cursor-pointer" @click="showPlayDataChart()">{{ p.start_at.substring(0, 10) }}</td>
        <td :class="getGSColor(p.gross_score)" class="text-right text-h6 text-bold q-px-xs">{{ getScoreFmt(p.gross_score) }}</td>
      </tr>
    </q-card-section>
  </q-card>
</q-dialog>
<ChartsProxy :scores="gscores" :name="name" />
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref, computed, createApp } from 'vue'
import { libFunctions } from 'src/composables/libFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import { cssFunctions } from 'src/composables/cssFunctions'
import ChartsProxy from 'src/components/ChartsProxy'
const app = createApp({})
app.component('ChartsProxy', ChartsProxy)
var gameId = 0

console.log(`-ST-TeamMatchPlayDataPad`)
// const cproxy = ref(null)
const compGScores = computed(() => {
  var yscores
  if (sortby.value === 'time') yscores = gscores.value.filter(p => p.start_at.substring(0, 4) == year.value)
  else yscores = gscores.value.filter(p => p.start_at.substring(0, 4) == year.value).sort((a, b) => a.gross_score > b.gross_score ? 1 : -1)
  return yscores
})
const { isDesk } = libFunctions()
const { today } = dayFunctions()
const { getGSColor } = cssFunctions()
function getHandicap () { // get current year handicap if p == null
  console.log(`-fn-getHandicap for player ${name.value}, year=${year.value}`)
  const games20 = gscores.value.filter(p => p.start_at.substring(0, 4) == year.value).sort((a, b) => a.start_at - b.start_at).slice(0, 20)
  const idxDiffs = games20.map(p => [parseFloat(p.idxDiff), p.start_at]).sort((a, b) => a[0] > b[0] ? 1: -1)
  if (idxDiffs.length === 0) handicap.value = 0
  totalGames.value = idxDiffs.length
  years.value = [...new Set(gscores.value.map(p => parseInt(p.start_at.substring(0, 4))).sort((a, b) => a > b ? 1: -1))]
  numBestIdxDiff.value = 10
  if (idxDiffs.length <= 10) numBestIdxDiff.value = Math.min(idxDiffs.length, 3)
  else if (idxDiffs.length <= 15) numBestIdxDiff.value = Math.min(idxDiffs.length, 6)
  const idxDiffsCut = idxDiffs.slice(0, numBestIdxDiff.value)
  const handicapx = idxDiffsCut.map(p => p[0]).reduce((a, b) => a + b, 0) / idxDiffsCut.length * 0.96
  idxChosen.value = idxDiffsCut.map(p => p[1])
  // console.log(`-fn-getHandicap for player ${name.value}, year=${year.value}`, idxChosen.value)
  handicap.value = parseInt(handicapx * 10) / 10
  if (Number.isInteger(handicap.value)) handicap.value += '.0'
}
function openIt (gs, gmId, player, back_n_days) {
  console.log(`%c-fn-openIt TeamMatchPlayerDataPad`,"color:red;font-size:12px", gs, gmId, player, back_n_days)
  gameId = gmId
  gscores.value = gs
  name.value = player.name
  tit.value = name.value + '\'s Scores Played at ' + (gameId == 13 ? 'JZs' : gameId == 14 ? 'KJs' : gameId == 16 ? 'ALs': 'Game' ) + ' Match'
  // if (gameId === 14) tit.value = name.value + '\'s Scores Played at KJs Match'
  year.value = parseInt(today().substring(0, 4))
  // console.log(`-CK-TeamMatchPlayDataPad-openIt-allscores of ${player.name} year=${year.value}`, gscores.value)
  getHandicap()
    opened.value = true
  // if (iPhoneX) opened.value = true
  // else if (iPhone11ProMax) opened_iPhone11ProMax.value = true
  // else if (iPhone13) opened_iPhone13.value = true
}
function setSortby () {
  sortby.value = sortby.value === 'time' ? 'score' : 'time'
}
function setYear (yr) {
  year.value = yr
  getHandicap()
}
function getBGcolor (game, i) {
  if (idxChosen.value.includes(game.start_at)) return 'bg-grey-9'
  return i%2 === 0 ? 'bg-teal-8' : 'bg-teal-9'
}
function getScoreFmt (score) {
  if (score < 100) return score
  else return score.toFixed(0).substring(1, 3)
}
function showPlayDataChart () {
  // console.log(`-CK-fn-showPlayDataChart year=${year.value}`)
  emitter.emit('open-ChartsProxy')
}
const opened = ref(false)
// const opened_iPhone11ProMax = ref(false)
// const opened_iPhone13 = ref(false)
// const dialogName = ref(null)
const name = ref(null)
const tit = ref(null)
const gscores = ref([])
const year = ref(null)
const years = ref([])
const idxChosen = ref([])
const handicap = ref(null)
const numBestIdxDiff = ref(null)
const totalGames = ref(0)
const sortby = ref('time')
emitter.on('open-TeamMatchPlayDataPad', (x,y,z) => openIt(x,y,z))
</script>
