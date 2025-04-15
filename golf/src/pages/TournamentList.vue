<template>
  <div style="background:rgb(28, 68, 78);overflow:auto;">
    <q-toolbar-title v-if="PGCsAdmin">
      <q-btn glossy color="teal-10" label="create new tournament" icon="add_circle" @click="addTournament" style="float:right;margin: 3px 0 0 0" />
    </q-toolbar-title>
    <div v-for="(t, i) in tmntList" :key=t.id>
      <q-expansion-item dark header-style="font-size:18px" :icon="getIcon(t)" :label="t.year + ' ' + t.game + ' ' + t.disptm + ' ' + t.courseName" group="group">
        <q-card class="bg-teal-9 text-white">
          <q-card-section>
            <ul style="font-size:18px;line-height:1.3">
              <!-- <li v-show="isActive(t)">Game on <span style="color:black">{{ t.disptm.replace(' ', ' start at ') }}</span> </li> -->
              <li>Game on <span class="text-amber">{{ t.disptm.replace(' ', ' start at ') }}</span> </li>
              <li>Men's tee: {{ t.mtee }} </li>
              <li>Ladies tee: {{ t.ltee }} </li>
              <li>Fees: ${{ t.fees }} </li>
              <li v-if="t.teetime_gap==0">Shutgun Start at: {{ t.disptm.substring(6, 11)}}</li>
              <li v-else>Teetime Gap: {{ t.teetime_gap }} </li>
              <!-- <li v-if="t.note != null">Notes: {{ t.note }} </li> -->
              <li v-if="t.note  != null"><q-btn rounded outline no-caps label="Tournament Notes" @click="showTournamentNotes(t)" /></li>
              <li v-if="t.links != null"><q-btn rounded outline no-caps label="Tournament Links" @click="showTournamentLinks(t)" /></li>
            </ul>
          </q-card-section>
          <q-separator />
          <q-toolbar-title v-if="!isActive(t)">
            <q-btn v-if="PGCsAdmin" glossy icon="edit" label="edit game" @click="updTournament(t)" color="blue" style="margin:5px 0 0 0;float:left" />
            <q-btn glossy icon="score" label="score / index" @click="showScores(t)" color="secondary" style="margin:5px 0 0 18px" />
            <q-btn v-if="isTodayGame(t)" glossy icon="score" label="enter scores" @click="showEnterScores(t)" color="primary" style="margin:5px 0 0 0;float:right" />
          </q-toolbar-title>
          <q-card-actions v-if="isActive(t) || PGCsAdmin" align="between">
            <q-btn glossy icon="delete" label="delete" @click="delTournament(t, i)" color="red" v-if="PGCsAdmin" />
            <q-btn glossy icon="assignment" @click="showSignup(t)" color="primary" label="Signup" />
            <q-btn glossy icon="update" label="Update" @click="updTournament(t)" color="purple" v-if="PGCsAdmin" />
          </q-card-actions>
        </q-card>
      </q-expansion-item>
    </div>
    <TournamentCreator @upd-game-list="updGameList" />
  </div>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'

import { libFunctions } from '../composables/libFunctions'
import { dayFunctions } from '../composables/dayFunctions'
import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios, paxios } = axiosFunctions()
const { PGCsAdmin, store, $q, $router } = libFunctions()
const { gameExpired, todayGame, getNNextSunday } = dayFunctions()

import TournamentCreator from 'pages/TournamentCreator'

const act = ref(null)
// const opened = ref(true)
// const openedRow = ref(-1)
const tmntList = ref([])
// const fees = ref(0)
const selectedIdx = ref(-1)
const selectedTmnt = ref({})
const golf_usertype = ref(null)

store.pageTitle = 'Tournament List'
store.page = 'tournamentList'
emitter.on('golf-usertype', (x) => { golf_usertype.value = x })
emitter.on('golf-getTournamentList', (x) => { tmntList.value = x.lst })
// emitter.on('usertype', (x) => { this.usertype = x })
// emitter.emit('sys-admin', 'SysAdmin')
console.log(`-ST-TournamentList pageTitle=${store.pageTitle} page=${store.page}`)
loadData()

function loadData () {
  console.log(`-CK-fn-loadData`)
  const path = process.env.API + '/golf/getTournamentList'
  gaxios(path)
}

function showTournamentNotes (tmnt) {
  emitter.emit('open-InfoDisplay', 'Tournament Notes', tmnt.note)
}
function showTournamentLinks (tmnt) {
  console.log(`-CK-fn-showtournamentLinks`, tmnt.links)
  let x = tmnt.links.split('@')
  let links = ''
  x.forEach(url => { links += '<a href="' + url.split('<p>')[1] + '" target=_blank>' + url.split('<p>')[0] + '</a><br />' })
  emitter.emit('open-InfoDisplay', 'Tournament Links', links)
}
// function showIt (i) {
//   return i !== openedRow.value
// }
function showEnterScores (tmnt) {
  store.tournament = tmnt
  $router.push({ path: 'EnterScores' })
}
function showSignup (tmnt) {
  store.tournamentId = tmnt.id
  store.tournament = tmnt
  store.showSelectedGame = true
  console.log('stored tournament', store.tournament)
  $router.push({ path: '/SignupVue/' + tmnt.id })
}
function showScores () {
  // store.dispatch('getTournamentId', tournamentId)
  // store.tournamentId = tmnt.id
  // var params = { 'tid': store.tournamentId }
  console.log('tournamentId', store.tournament.id)
  // $router.push({ path: '/TournamentScores' })
  $router.push({ path: '/TournamentScoreVue' })
  // this.$$router.push({ name: 'TournamentScores', params: params })
  // this.$$router.push({ name: 'PlayersRanking', params: params })
}
// updTournament (tmnt, i) {
function updTournament (tmnt) {
  // console.log('updTournament for', tmnt.courseName)
  // this.opened = false
  // this.opened[i] = false
  // this.hide()
  // this.openedRow = i
  // this.showIt(i)
  console.log('updTournament', tmnt)
  // this.$refs.TournamentCreator.startAt = tmnt.start_at
  // this.$refs.TournamentCreator.gameId = tmnt.game_id
  emitter.emit('open-TournamentCreator', tmnt, true, 'Update')
}
function addTournament () {
  // this.opened = false
  act.value = 'add'
  console.log('addTournament called')
  const newTmnt = {}
  const nxd = getNNextSunday()
  const ymd = nxd.yyyymmdd()
  newTmnt.start_at = ymd + ' 12:30'
  newTmnt.disptm = ymd + ' 12:30'
  newTmnt.year = nxd.getFullYear()
  newTmnt.courseName = 'Course to be seleted'
  newTmnt.game = 'Game name to be selected'
  newTmnt.mtee = 'mtee name to be selected'
  newTmnt.ltee = 'ltee name to be selected'
  newTmnt.fees = 100
  newTmnt.teetime_gap = 10
  // newTmnt.game_id = 0
  // newTmnt.course_id = 0
  // this.tmntList.unshift(newTmnt)
  // console.log('-dg-show TournamentCreator with add tmnt', newTmnt)
  emitter.emit('open-TournamentCreator', newTmnt, true, 'Create')
}
function delTournament (tmnt, idx) {
  selectedTmnt.value = tmnt
  selectedIdx.value = idx
  const tit = 'Confirm: Delete ' + tmnt.game + ' on ' + tmnt.courseName + ' at ' + tmnt.start_at.replace('T', ' ')
  // var msg = 'Delete ' + tmnt.game + ' on ' + tmnt.courseName + ' at ' + tmnt.start_at.replace('T', ' ')
  actionDialog(tit, 'delFromDB')
}
function delFromDB () {
  console.log('delFromDB', selectedTmnt.value)
  const inData = {}
  inData.id = selectedTmnt.value.id
  console.log(' == delTmnt', inData)
  const path = process.env.API + '/golf/delTournament'
  paxios(path, inData)
  console.log('delete tmnt', selectedIdx.value, selectedTmnt.value)
  tmntList.value.splice(selectedIdx.value, 1)
}
function actionDialog (tit) {
  $q.dialog({
    title: tit,
    color: 'primary',
    ok: true, // takes i18n value, or String for "OK" button label
    cancel: true, // takes i18n value, or String for "Cancel" button label
    preventClose: true,
    noBackdropDismiss: false, // gets set to "true" automatically if preventClose is "true"
    noEscDismiss: false, // gets set to "true" automatically if preventClose is "true"
    stackButtons: false,
    position: 'top',
    noRefocus: true
  }).onOk(() => {
    console.log('user agreed')
    delFromDB()
  }).onCancel(() => {
    console.log('user disagreed - do nothing')
  })
}
function isActive (t) { return !gameExpired(t.start_at) }
function isTodayGame (t) { return todayGame(t.start_at) }
function getIcon (t) {
  if (gameExpired(t.start_at) && todayGame(t.start_at)) return 'score'
  if (gameExpired(t.start_at)) return 'check_circle'
  else return 'golf_course'
}
function updGameList (act, tmnt = null) {
  if (act === 'add') {
    // this.tmntList.shift()
    console.log(`TournamentList - updGameList act=${act}`, 'tmnt', tmnt)
    tmntList.value.unshift(tmnt)
  } else if (act === 'upd') {
    // this.tournamentList.splice(this.gameList, 1, tmnt)
    tmntList.value.splice(tmntList.value, 1, tmnt)
  }
}
</script>
