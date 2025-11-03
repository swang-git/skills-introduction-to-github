<template>
<q-dialog v-model="opened" maximized seamless>
  <q-layout container class="bg-teal-9">
    <layoutHeader :tit="'Club ' + tmnt.game" ricon="sort" @do-action="showSorting=!showSorting" />
    <div style="margin:50px 0 0 0" class="bg-teal-10 text-white text-body1 text-center q-pa-xs" v-html="gameInfo()" />
    <div class="q-pt-none bg-teal-10 q-pl-md">
      <div v-if="showSorting" class="float-right q-pr-xs">
        <q-page-sticky position="top-right" :offset="[1, 33]">
          <div><q-btn glossy color="cyan-8"  @click="sortBy='name'"   icon="people" label="人名排序" class="text-body1" /></div>
          <div><q-btn glossy color="teal-10" @click="sortBy='stroke'" icon="sports_golf" label="杆数排序" class="text-body1" /></div>
          <div><q-btn glossy color="lime"    @click="sortBy='poyg'"   icon="sort"  label="POY PTs" class="text-body1 text-black" no-caps /></div>
          <div><q-btn glossy color="amber-9" @click="sortBy='poy'"    icon="sort"  label="POY积分" class="text-body1 text-black" /></div>
          <div><q-btn glossy color="green"   @click="sortBy='group'"  icon="group" label="比赛分组" class="text-body1 text-black" /></div>
          <div v-if="sortBy=='name'" class="float-right q-pr-xs" style="margin:50px 0 0 0px">
            <q-input dark v-model="searchQuery" input-class="text-right text-h6" class="absolute-bottom-right" dense>
              <template v-slot:append>
                <q-icon v-if="searchQuery === ''" name="search" size="md" />
                <q-icon v-else name="clear" size="md" class="cursor-pointer" @click="searchQuery=''" />
              </template>
            </q-input>
          </div>
        </q-page-sticky>
      </div>
      <div v-for="(p, i) in compTplayers" :key=p class="bg-teal-9" style="margin-left:-16px">
        <span v-if="isDesk" class="text-white text-h6 q-pl-xs"><span v-if="i+1<10">&nbsp;&nbsp;</span>{{ i+1 }}.</span>
        <q-chip class="text-h6" :class="{'bg-red':sortBy=='group' && p.tnum=='A','bg-cyan':sortBy=='group' && p.tnum!='A', 'bg-cyan-7':sortBy!='group'}" dark style="width:230px;height:44px">
          <q-avatar v-if="PGCsAdmin" class="cursor-pointer" @click="openPosPad(p)"><img :src="getAvatar(p)" /></q-avatar>
          <q-avatar v-else><img :src="getAvatar(p)" width="30" height="30" /></q-avatar>
          <div @click="rewardDetails(p)">
            <span class="text-amber">{{ p.pos }}</span>
            <span class="q-pl-xs" :class="getCursor(p)">{{ p.name }}<q-tooltip class="bg-cyan-9 text-white text-h6">player_id = {{ p.player_id }}</q-tooltip></span>
          </div>
        </q-chip>
        <q-btn v-if="sortBy==='enterScore'"   round style="margin:0 0 0 -25px" :class="getAvgColor(p.pscore)" @click="setGameScore(p)">{{ p.pscore }}</q-btn>
        <q-btn v-if="/name|group/.test(sortBy)"   round style="margin:0 0 0 -25px" :class="getAvgColor(p.pscore)" @click="setGameScore(p)">{{ p.pscore }}</q-btn>
        <q-btn v-if="sortBy==='stroke'" round style="margin:0 0 0 -25px" :class="getAvgColor(p.pscore)" @click="setGameScore(p)">{{ p.pscore }}</q-btn>
        <q-btn v-if="sortBy==='poyg'"   round style="margin:0 0 0 -25px"  size="16px" text-color="black" color="lime">{{ p.poyg }}</q-btn>
        <q-btn v-if="sortBy==='poy' && gameId < 6"   round style="margin:0 0 0 -25px"  size="16px" text-color="black" color="amber-9">{{ p.poy }}</q-btn>
        <q-btn v-if="sortBy==='poy' && gameId === 6" round style="margin:0 0 0 -25px"  size="16px" text-color="black" color="amber-9">{{ p.poyg + p.poy }}</q-btn>
      </div>
    </div>
  </q-layout>
  <PosPad ref="refPosPad" />
  <GolfScorePad ref="refGolfScorePad" />
  <InfoDialog ref="refInfoDialog" />
</q-dialog>
</template>
<script setup>
// import { scroll } from 'quasar'
// const { getScrollTarget, setVerticalScrollPosition } = scroll
import { ref, computed, onMounted } from 'vue'
// import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
const { isDesk, PGCsAdmin } = libFunctions()

import { axiosFunctions } from '../composables/axiosFunctions'
const { gaxios } = axiosFunctions()

import { cssFunctions } from '../composables/cssFunctions'
const { getAvgColor, getAvatar } = cssFunctions()

// import { dayFunctions } from '../composables/dayFunctions'

import PosPad from 'src/components/PosPad'
import GolfScorePad from '../components/GolfScorePad'
import InfoDialog from 'src/components/InfoDialog'
import layoutHeader from 'src/components/LayoutHeader'

const searchQuery = ''
const tmntExpired = ref(null)
var showSorting = false
const sortBy = ref('stroke')
// const fabexp = true
const opened = ref(false)
var PGCPlayers = []
const tmnt = ref({})
const tmntId = ref(0)
var gameId = 0
const year = ref(null)
const totalPoyg = ref((1 + 16) * 16 / 2)
var accuPoyg = 0
var poygDone = false
const refPosPad = ref(null)
const refInfoDialog = ref(null)
const refGolfScorePad = ref(null)

onMounted(() => {
  refPosPad
  refInfoDialog
  refGolfScorePad
})

defineExpose({openIt})

console.log('-ST-PGCGameDetailsDialog', tmnt)
showSorting = isDesk ? true : false
// emitter.on('golf-usertype', (x) => golf_usertype = x)

const compTplayers = computed(() => {
  if (sortBy.value === 'stroke') return PGCPlayers.filter(p => p.pscore > 0 && p.game_id === gameId).sort((a, b) => a.pscore < b.pscore ? -1 : 1)
  else if (sortBy.value === 'poyg')   return PGCPlayers.filter(p => p.pscore > 0 && p.poyg > 0).sort((a, b) => a.poyg > b.poyg ? -1 : 1)
  else if (sortBy.value === 'group')  return PGCPlayers.filter(p => p.game_id === gameId)
  else if (sortBy.value === 'poy' && gameId < 6)    return PGCPlayers.filter(p => p.poy > 0).sort((a, b) => a.poy > b.poy ? -1 : 1)
  else if (sortBy.value === 'poy' && gameId === 6)    return PGCPlayers.filter(p => p.poy > 0).sort((a, b) => a.poy + a.poyg > b.poy + b.poyg ? -1 : 1)
  else if (sortBy.value === 'name') {
    var filterKey = searchQuery.length > 0 && searchQuery.toLowerCase()
    let data = PGCPlayers
    if (filterKey.length > 0) {
      var words = filterKey.split(' ')
      words.forEach(word => {
        data = data.filter(row => {
          return Object.keys(row).filter(key => { return !['id', 'game_id', 'tournament_id'].includes(key) }).some(key => {
            return String(row[key]).toLowerCase().indexOf(word) >= 0
          })
        })
      })
      console.log(`Searching ... numFound ${data.length} sortBy ${sortBy.value}`)
      return data
    }
    return PGCPlayers.filter(p => p.game_id === gameId || p.game_id == null).sort((a, b) => a.name < b.name ? -1 : 1)
  }
  return PGCPlayers.filter(p => p.pscore > 0)
})


// function searchName () {
//   console.log(`-CK-fn-search-name searchQuery=${searchQuery}`)
// }
function openIt (pgcPlayers, tmt, expired) {
  tmntExpired.value = expired
  tmnt.value = tmt
  tmntId.value = tmt.id
  year.value = tmt.year
  gameId = tmt.game_id
  PGCPlayers = pgcPlayers
  accuPoyg = 0
  PGCPlayers.filter(p => p.poyg > 0).forEach(p => accuPoyg += p.poyg)
  // accuPoyg = PGCPlayers.filter(p => p.poyg > 0).reduce((a, b) => a.poyg + b.poyg, 0)
  if (gameId.value === 6) {
    totalPoyg.value *= 3
  }
  poygDone = accuPoyg === totalPoyg.value
  console.log(`-CK-fn-openIt-gameId=${gameId} accuPoyg=${accuPoyg} totalPoyg=${totalPoyg.value} poygDone=${poygDone} sortBy=${sortBy.value}`, tmnt)
  if (!poygDone) {
    PGCPlayers.forEach(p => p.poyg = 0)
    accuPoyg = 0
    calcPoyGamePoints()
  }
  opened.value = true
}
function setGameScore (p) {
  console.log('-CK-fn-setGameScore', p)
  if (!PGCsAdmin) return
  // if (isNotPGCsAdmin()) return
  refGolfScorePad.value.openIt(p)
}
function openPosPad (p) {
  if (!PGCsAdmin) return
  refPosPad.value.openIt(p, gameId)
}
function gameInfo () {
  console.log('-CK-fn-gameInfo', tmnt)
  const dd = tmnt.value.start_at.substring(0, 10)
  const titDesk = dd + ' (' + dd.chwk3() + ') ' + ' at ' + tmnt.value.courseName + ' (' + PGCPlayers.filter(p => p.game_id === gameId && p.pscore>0).length + ' Players)'
  const titFone = dd + ' (' + dd.chwk1() + ') ' + ' at ' + tmnt.value.courseName + '<div class="text-center">(' + PGCPlayers.filter(p => p.game_id === gameId && p.pscore>0).length + ' Players)</div>'
  return isDesk ? titDesk : titFone
}
function calcPoyGamePoints () {
  if (gameId < 6) { // only for regular tournaments not playoff
    const g5players = PGCPlayers.filter(p => p.pos != null && p.pos.indexOf('G') === 0)
    console.log('g5players', g5players)
    g5players.forEach(p => {
      if (p.pos === 'G1') p.poyg = 16
      else if (p.pos === 'G2') p.poyg = 15
      else if (p.pos === 'G3') p.poyg = 14
      else if (p.pos === 'G4') p.poyg = 13
      else if (p.pos === 'G5') p.poyg = 12
      savePoyg(p)
      accuPoyg = (16 + 12) * 5 / 2
    })
  }
  const gplayers = PGCPlayers.filter(p => p.pscore > 0 && (p.pos == null || p.pos.indexOf('G') != 0)).sort((a, b) => a.pscore > b.pscore ? 1 : -1)
  var points = 0
  for (let i=0; i<gplayers.length; ++i) {
    console.log(`-CK-calcPoy-gameId=${gameId} accuPoyg=${accuPoyg} totalPoyg=${totalPoyg.value} poygDone=${poygDone} sortBy=${sortBy.value}`)
    if (accuPoyg === totalPoyg.value) break
    const p = gplayers[i]
    points = (gameId === 6 ? 16 : 11) - i

    if (p.poyg > 0) continue

    const pscore = p.pscore
    const sameScorePlayers = gplayers.filter(x => x.pscore === pscore)

    let ar = new Array(sameScorePlayers.length)
    for (let k=0; k<ar.length; ++k) ar[k] = Math.max(points - k, 0)
    let avpoint = ar.reduce((a, b) => a + b, 0) / ar.length
    // if (pscore === 84) console.warn(`i=${i} sameScorePlayers.length=${sameScorePlayers.length} ${p.name} ${p.pscore} ${i} ${ar.length}`, sameScorePlayers)
    for (let j=0; j<sameScorePlayers.length; j++) {
      const pp = sameScorePlayers[j]
      pp.poyg = avpoint
      if (gameId === 6) pp.poyg *= 3
      accuPoyg += pp.poyg
      savePoyg(pp)
    }
  }
}
function savePoyg (p) {
  console.log(`poyg=${p.poyg} name=${p.name}`)
  const path = process.env.API + '/golf/savePoyg/' + p.poyg + '/' + p.id
  gaxios(path)
}
function getCursor (p) {
  let ret = p.pos == null ? null : 'cursor-pointer'
  return ret
}
function rewardDetails (p) {
  if (p.note == null || p.note == '') return
  console.log(`-fn-rewardDetails for ${p.name} to be constructed show details like CP how close LD how long`)
  const tit = "Reward Notes"
  const msg = p.note
  refInfoDialog.value.openIt(tit, msg)
}
</script>
