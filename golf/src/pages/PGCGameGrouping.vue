<template>
<q-dialog v-model="opened" maximized seamless>
  <q-layout container>
    <layoutHeader v-if="PGCsAdmin" :tit="'Club ' + tmnt.game" ricon="add_circle" @do-action="openPlayerList" />
    <layoutHeader v-else :tit="'Club ' + tmnt.game" ricon="sort" @do-action="showSorting=!showSorting" />
    <div style="margin:50px 0 0 0" class="bg-teal-10 text-white text-body1 text-center q-pa-xs" v-html="gameInfo()" />
    <div>
      <div class="bg-teal-10 scroll" style="padding:0 0 100px 0;height:800px">
        <q-card-section :id="'grp_'+i" v-for="(g, i) in groups.filter(g => g.players[0] !=undefined)" :key=g :class="getBGcolor(i)" style="padding: 0 0 0 10px">
          <div class="ellipsis justify-center" :class="{ textCenter:isIM }">
            <q-chip :id="'xgrp_'+i" outline no-wrap :class="getTeeColor(getTeeName(g))" style="margin:0;width:310px">
              Group {{i+1}} Play
              <div v-if="teetime_gap>0">
                <b class="bg-cyan-1 text-bold text-body1 q-ma-xs q-px-xs">{{ getTeeName(g) }}</b><span class="q-pl-xs"> Tee Off at </span>
                <b class="bg-amber-2 text-bold q-ma-sm q-px-xs text-body1 text-red">{{ g.tofftm }}</b>
              </div>
              <div v-else>
                <b class="bg-cyan-1 text-bold text-body1 q-ma-xs q-px-xs">{{ getTeeName(g) }}</b><span class="q-pl-xs"> Start from</span>
                <b class="bg-amber-2 text-bold q-ma-sm q-px-xs text-body1 text-red">Hole {{ startHole(i+1) }}</b>
              </div>
            </q-chip>
          </div>
          <table>
            <tr :id="'tr_' + pi" v-for="(p, pi) in g.players.sort((a,b) => { return a.tnum < b.tnum ? -1 : 1})" :key=p class="text-h6 text-white">
              <td width="200px" no-wrap :class="tnumColor(p)"><q-btn round dense :icon="getNature(p)" @click="rmPlayerFromGroup(p, pi, g)" />{{ p.name }}</td>
              <td v-if="pi==0"><q-btn class="text-body1 text-black" round outline :label="p.gamefee" /></td>
              <td v-if="pi==1"><q-btn class="text-body1 text-black" round outline :label="p.gamefee" /></td>
              <td v-if="pi==2"><q-btn class="text-body1 text-black" round outline :label="p.gamefee" /></td>
              <td v-if="pi==3"><q-btn class="text-body1 text-black" round outline :label="p.gamefee" /></td>
              <!-- <td><q-btn v-if="isPGCsAdmin"  round style="margin:0 0 0 -25px" :class="getAvgColor(p.pscore)" @click="setGameScore(p)">{{ p.pscore }}</q-btn></td>
              <td><q-btn v-if="sortBy==='stroke'" round style="margin:0 0 0 -2px"  size="16px" text-color="black" color="orange">{{ p.poyg }}</q-btn></td>
              <td><q-btn v-if="sortBy==='poy'" round style="margin:0 0 0 -2px"  size="16px" text-color="black" color="lime">{{ p.poy }}</q-btn></td> -->
            </tr>
          </table>
        </q-card-section>
      </div>
    </div>
  </q-layout>
  <PGCPlayerListPad ref="playerList" @add-to-group="addToNextGroupOpenSlot"/>
  <PosPad ref="PosPad" />
  <InfoDialog ref="InfoDialog" />
</q-dialog>
</template>
<script setup>
import { scroll } from 'quasar'
const { getScrollTarget, setVerticalScrollPosition } = scroll
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
import { dayFunctions } from '../composables/dayFunctions'
const { isDesk, isIM, PGCsAdmin } = libFunctions()
const { getDay, getDay1 } = dayFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { paxios } = axiosFunctions()
import PGCPlayerListPad from './PGCPlayerListPad'
import PosPad from 'src/components/PosPad'
import InfoDialog from 'src/components/InfoDialog'
import LayoutHeader from 'src/components/LayoutHeader'

var showSorting = false
// const sortBy = 'stroke'
// const fabexp = true
// var listing = false
const opened = ref(false)
const PGCPlayers = []
// const splayers = []
// const pplayers = []
const tmnt = {}
const groups = ref([])
const notGrouped = ref([])
// const numGroups = ref(0)
// const openSlots = ref(0)
// const tmntId = ref(0)
const gameId = ref(0)
const player = ref({})
// const openSlots = 0
// const sortx = 'name'
const teetime_gap = null

console.log('-ST-PGCGameDetailsDialog', tmnt)
showSorting = isDesk ? true : false
// listing = !PGCsAdmin
// emitter.on('golf-usertype', (x) => golf_usertype = x)
emitter.on('golf-addPGCTplayer', (x) => setTid(x))

function getTeeName (g) {
  // if (g.players[1].gender === 'F') return g.lteename
  if (g.players[0].gender === 'F') return g.lteename
  else return g.mteename
}
function startHole (gi) {
  // const doubleGroupHole = 8
  let holex = -1
  if (gi < 8) holex = gi
  else if (gi === 8) holex = gi + '(A)'
  else if (gi === 9) holex = (gi - 1) + '(B)'
  else holex = gi - 1
  // console.log(`-ck-BstartHole- ${gi} ${holex}`)
  return holex
}
function setTid (da) {
  player.value.id = da.tid
  console.log(`-setTid ${da.tid}, ${player.value.id}`)
}
// function updTeamMatchTplayer(player) {
//   const path = process.env.API + '/golf/updTeamMatchTplayer'
//   paxios(path, player)
// }
function rmPlayerFromGroup(p, pi, g) {
  console.log(`-fn-rmPlayerFromGroup`, p, pi, g)
  if (!PGCsAdmin) return
  p.grp = 0
  p.tnum = null
  g.players.splice(pi, 1)
  emitter.emit('add-to-not-grouped', p)
  addPGCTplayer(p)
}
function getNextGroup () {
  for (let i=0; i<groups.value.length; ++i) {
    if (groups[i].players.length < 4) return i
  }
  console.log(`-ck-grouping done`)
  return -1
}
function addToNextGroupOpenSlot (p) {
  // console.table(notGrouped)
  console.log(`-addToNextGTeamSlot-${p.name}`)
  const grp = getNextGroup()
  if (grp === -1) {
    const newgrp = groups.value.length + 1
    // const teeofftime = getTeeOffTime(newgrp, tmnt.start_at.substring(11, 16), tmnt.teetime_gap)
    p.grp = newgrp
    p.tnum = 'A'
    p.gamefee = getGamefee(p)
    // console.log(`-ck-add ${p.name} to new group ${newgrp} ${teeofftime}`, p)
    // const g = { tofftm:teeofftime, players:[], grp: newgrp, mteename:tmnt.mtee, lteename:tmnt.ltee }
    g.players.push(p)
    groups.value.push(g)
    addPGCTplayer(p)
    return
  }
  p.grp = grp + 1
  let g = groups[grp]
  // console.table(groups[0].players)
  console.table(g)
  // p.tmntId = g.tmntId
  const tnum = g.players.map(x => x.tnum)
  console.table(tnum)
  if (!tnum.includes('A')) p.tnum = 'A'
  else if (!tnum.includes('B')) p.tnum = 'B'
  else if (!tnum.includes('C')) p.tnum = 'C'
  else if (!tnum.includes('D')) p.tnum = 'D'
  p.gamefee = getGamefee(p)
  g.players.push(p)
  notGrouped.value = notGrouped.value.filter(x => x.player_id !== p.player_id)
  addPGCTplayer(p) // upd as well
  // if (p.id > 0)updTeamMatchTplayer(p)
  // else addPGCTplayer(p)
  moveToBottom()
}
function addPGCTplayer (p) {
  console.log(`-fn-addPGCTplayer`, p)
  if (p.id == null || p.id <= 0) player.value = p
  if (!PGCsAdmin) return
  emitter.emit('add-to-list', p)
  emitter.emit('open-list')
  const path = process.env.API + '/golf/addPGCTplayer'
  paxios(path, p);
}
function moveToBottom () {
  const nxtg = getNextGroup() - 1
  if (nxtg < 0) return
  const idname = 'grp_' + nxtg
  const elm = document.getElementById(idname)
  console.log(`-ck-elm ${idname} nxtg=${nxtg}`, elm)
  const target = getScrollTarget(elm)
  console.log(`-fn-move-to-bottom elm`, elm)
  const offset = elm.offsetTop
  const duration = 400
  setVerticalScrollPosition(target, offset, duration)
}
const refplayerList = ref(null)
function openPlayerList () {
  console.log(`-CK-fn-openPlayerList`)
  if (!PGCsAdmin) return
  moveToBottom()
  refplayerList.value.openIt(tmnt.id, tmnt.game_id, tmnt.year, tmnt.fees)
}
// function openIt (PGCPlayers, tmt) {
//   console.log(`-CK-fn-openIt-game`, PGCPlayers, tmt)
//   tmnt = tmt
//   tmntId = tmt.id
//   gameId = tmt.game_id
//   teetime_gap = tmt.teetime_gap
//   PGCPlayers = PGCPlayers
//   setGroups()
//   opened.value = true
// }
function gameInfo () {
  const dd = tmnt.start_at.substring(0, 10)
  const titDesk = dd + ' (' + getDay(dd) + ') ' + ' at ' + tmnt.courseName + ' (' + PGCPlayers.length + ' Players)'
  const titFone = dd + ' (' + getDay1(dd) + ') ' + ' at ' + tmnt.courseName + '<div class="text-center">(' + PGCPlayers.length + ' Players)</div>'
  return isDesk ? titDesk : titFone
}
function tnumColor(p, pscore=null) {
  if (p.type == 'G') return 'bg-amber'
  if (pscore === null) return p.tnum === 'A' ? 'bg-red' : 'bg-blue-9'
  else return p.tnum === 'A' ? 'red' : 'blue-9'
}
function getBGcolor (i) {
  const numPlayersInGroup = groups[i].players.length
  if (numPlayersInGroup > 4) {
    groups[i].note = 'There are more than 4 players in the group'
    return 'bg-red'
  } else if (i % 2 === 0 && numPlayersInGroup === 4) {
    return 'bg-green-4'
  } else if (numPlayersInGroup < 4) {
    return 'bg-yellow-5'
  } else {
    return 'bg-green-6'
  }
}
function getNature (p) {
  return p.gender === 'M' ? 'nature_people' : 'nature'
}
function getTeeColor(tee) {
  if (tee === 'Player') tee = 'purple-2'
  else if (tee === 'Blue') tee = 'blue-4'
  else if (tee === 'Green') tee = 'green-8'
  else if (tee === 'Gold') tee = 'yellow-10'
  else if (tee === 'White') tee = 'white'
  else if (tee === 'Black') tee = 'grey-4'
  return 'bg-' + tee
}
function getGamefee (p) {
  return p.type === 'H' ? 'H' : (p.course_member == 'FG' && gameId.value === 2) ? 25 : p.type === 'G' ? parseInt(tmnt.fees) + 50 : parseInt(tmnt.fees)
}
// function setGroups () {
//   console.log('-CK-fn-setGroups for tmnt', tmnt)
//   numGroups.value = Math.ceil(PGCPlayers.length / 4)
//   groups.value = Array(numGroups)
//   openSlots.value = numGroups.value * 4 - PGCPlayers.length
//   // console.log('-ab-setGroups PGCPlayers:', PGCPlayers, 'groups:', groups, 'gameDate:', gameDate, 'numGroups:', numGroups, 'openSlots:', openSlots)

//   for (let i=0; i<numGroups.value; ++i) {
//     const teeofftime = getTeeOffTime(i, tmnt.start_at.substring(11, 16), tmnt.teetime_gap)
//     // console.log(`-fn-teeofftime=${teeofftime}`)
//     const g = { tofftm:teeofftime, players:[] }
//     groups[i] = g
//   }
//   // console.table(PGCPlayers.map(p => p.tmntId + ' ~ ' + p.grp + ' ~ ' + p.tnum + ' ~ ' + p.type + ' ~ ' + p.name))
//   groups.value.forEach((g, i) => {
//     const ttm = tmnt.start_at.substring(11, 16)
//     g.ttm = ttm
//     // console.table(`games(${i}) ${ttm}`)
//     const x = {
//       idx: i+1, // this will be used for team grouping setup
//       tmntId: tmntId,
//       players: [],
//       mteename: tmnt.mtee,
//       lteename: tmnt.ltee
//     }
//     PGCPlayers.forEach(p => {
//       p.gamefee = getGamefee(p)
//       if (p.grp > 0) { // use player's grp to identify -- no tmnId in case to add a new tournament
//         if (p.grp === x.idx) {
//           x.players.push(p)
//           p.grp = x.idx
//           x.grp = p.grp
//         }
//       }
//     })
//   })
// }
// function showGroups () {
//   tmntId = tmnt.id
//   // console.log('-fn-userSelected(tid) for tmnt Id', tmnt.id, activeGames)
//   console.log('-fn-userSelected(tid) for tmnt Id', tmnt)
//   store.pageTitle = 'Grouping ' + tmnt.game
//   groups = []
//   getPGCGamePlayers()
// }
</script>
