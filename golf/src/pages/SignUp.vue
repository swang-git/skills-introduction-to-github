<template>
<div class="bg-teal-10">
  <CardSelection v-if="activeGames.length > 1 && tmntId==0" :selections="activeGames" todo="Signup" @user-selected="userSelectedTmnt" />
  <div v-if="tmntId>0" class="q-pt-none q-pl-xs">
    <div class="row">
      <q-chip size="18px" icon="people" color="red-5" text-color="white">{{ signerCount }}<q-tooltip class="bg-red text-white">Number of Peoples Signed up</q-tooltip></q-chip>
      <q-chip size="18px" icon="golf_course" color="green" text-color="white">{{ playerCount }}<q-tooltip class="bg-green-8 text-white">Number of Peoples Golfing</q-tooltip></q-chip>
      <q-chip size="18px" icon="restaurant" color="blue-7" text-color="white">{{ dinnerCount }}<q-tooltip class="bg-blue-7 text-white">Number of Peoples To Dinner</q-tooltip></q-chip>
      <div class="q-pl-xs q-pt-xs">
        <q-btn glossy rounded icon="add_circle" @click="showPlayerList" class="text-white bg-cyan-9" style="width:74px" />
        <q-tooltip class="bg-blue-10 text-h6 text-white">Show Player List to Choose to Signup</q-tooltip>
      </div>
    </div>
    <div>
      <SelOptions ref="selOptions" @selected-option="addTournamentPlayer" />
    </div>
    <div style="margin:-28px 0 0 -8px">
      <q-scroll-area style="height:1000px;padding:8px">
        <div v-for="(p,i) in tList" :key=p.x>
          <div class="row">
            <q-btn @click="updTplayerActivity(p)" :color="getColor(p)" :icon="getIcon(p)" round glossy><q-tooltip class="bg-green-8 text-h6">Click to Update</q-tooltip></q-btn>
              <q-btn v-if="sortby==='POY' || sortby==='NPY' || sortby==='GSC' || sortby ==='FPOY' || sortby ==='FNPY'" round outline size="sm" color="white"> {{p.label}} </q-btn>
              <q-chip v-if="p.activity==='dinn'" class="name-chip bg-blue-9">
                <q-tooltip class="bg-blue-9 text-h6">Dinner only for <div class="text-black">{{ p.fullname }}</div></q-tooltip>
                <q-avatar><img :src="getAvatar(p)"></q-avatar><div class="text-body1">{{ p.fullname }}</div>
              </q-chip>
              <q-chip v-else-if="p.activity==='golf'" class="name-chip" :avatar="getAvatar(p)" color="green">
                <q-tooltip class="bg-green-7 text-h6">Golf only for <div class="text-black">{{ p.fullname }}</div></q-tooltip>
                <q-avatar> <img :src="getAvatar(p)" /></q-avatar><div class="text-body1">{{ p.fullname }}</div>
              </q-chip>
              <q-chip v-else class="name-chip" :avatar="getAvatar(p)" color="red">
                <q-tooltip class="bg-red text-h6">Golf & Dinner for <div class="text-black">{{ p.fullname }}</div></q-tooltip>
                <q-avatar> <img :src="getAvatar(p)" /></q-avatar><div class="text-body1">{{ p.fullname }}</div>
              </q-chip>
            <div v-if="SysAdmin">
              <q-btn color="yellow" icon="cancel" round outline @click="delTournamentPlayer(p, i)" />
            </div>
            <div v-else>
              <q-btn style="margin-left:-10px" v-if="sortby==='NPY' || sortby==='FNPY' || sortby==='PNM' || sortby==='GSC'" round outline color="white">
                <q-tooltip class="bg-cyan-9 text-h6">your net playoff points</q-tooltip>{{p.npy}}</q-btn>
              <q-btn style="margin-left:-1px" v-if="sortby==='POY' || sortby==='PNM' || sortby==='GSC' || sortby==='FPOY'" round outline color="yellow">
                <q-tooltip class="bg-yellow text-h6 text-brown">your playoff points</q-tooltip>{{p.poy}}</q-btn>
              <q-btn style="margin-left:-1px" v-if="sortby==='CDX' || sortby==='PNM' || sortby==='CDX' || sortby==='CDX'"  round outline color="white">
                <q-tooltip class="bg-brown text-h6">your club index</q-tooltip>{{p.cdx}}</q-btn>
              <q-btn v-if="sortby==='FNPY'" round outline size="md" color="yellow"> {{p.finalNPY}} </q-btn>
              <q-btn v-if="sortby==='FPOY'" round outline size="md" color="yellow"> {{p.finalPOY}} </q-btn>
              <q-btn v-if="sortby==='POY'"  round outline size="md" color="yellow"> {{p.FPOYpoint}} </q-btn>
              <q-btn v-if="sortby==='NPY'"  round outline size="md" color="yellow"> {{p.FNPYpoint}} </q-btn>
            </div>
          </div>
        </div>
      </q-scroll-area>
    </div>
  </div>
  <SignupDialog ref="refAddNewPlayer" @add-new-player="addTournamentPlayer" />
</div>
</template>
<script setup>
// /* eslint-disable */
import emitter from 'tiny-emitter/instance'
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
const $router = useRouter()
import { libFunctions } from '../composables/libFunctions'
const { $q, store, isDesk, SysAdmin, PGCAdmin } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { paxios, gaxios } = axiosFunctions()

import SignupDialog from './SignupDialog'
import CardSelection from 'src/components/CardSelection'
import SelOptions from 'src/components/SelOptionsWithSearch'

var year = (new Date()).getFullYear()
var tmntId = 0
var tPlayers = []
const playersForTournament = ref([])
const player = null
const playerId = null
const sortby = 'PNM'
// const sortOptions = []
// const poyPlayers = []
// const npyPlayers = []
var activeGames = ref([])
// const selectedTid = -1
// const isDone = false
const refAddNewPlayer = ref(null)
// const showSelectedGame = ref(false)
// var tmnt = {}

onMounted(() => refAddNewPlayer )
console.log('-ST-Signup')
if (isDesk) store.pageTitle = 'Signup Tournament of ' + year
else store.pageTitle = 'Signup Tournament'
store.page = 'signup'
emitter.on('tmnt-id', (x) => tmntId = x)
emitter.on('golf-UnexpiredTournaments', (x) => setUnexpiredTournaments(x))
emitter.on('golf-getPlayersForTournament', (x) => setPlyaersForTournament(x))
emitter.on('golf-getTournamentPlayersWithScores', (x) => setTournamentPlayersWithScores(x))
getUnexpiredTournaments()

const signerCount = computed(() => { return tPlayers.length })
const dinnerCount = computed(() => { return tPlayers.filter(p => { return /dinn|both/.test(p.activity) }).length })
const playerCount = computed(() => { return tPlayers.filter(p => { return /golf|both/.test(p.activity) }).length })
const playersTobeAdded = computed(() => { return playersForTournament })

const tournament = computed({
  get: () => store.tournament,
  set: t => store.tournament = t,
})
// const game = computed(() => { return tournament.value === null ? null : tournament.value.game })
// const disptm = computed(() => { return tournament.value === null ? null : tournament.value.start_at })
// const course = computed(() => { return tournament.value === null ? null : tournament.value.courseName })
// const exportData = computed(() => {
//   const dat = []
//   tPlayers.forEach((p, i) => {
//     const px = { id: i, player: p.player, activity: p.activity, notes: p.activity === 'dinn' ? 'Dinner Only' : '' }
//     dat.push(px)
//   })
//   return dat
// })
const tList = computed(() => { return tPlayers })
// const poyList = computed({
//   get: () => doSortingPoyPlayers(),
//   set: val => poyPlayers = val
// })

//== function sections
function setTournamentPlayersWithScores (da) {
  console.log(`-CK-fn-setTournamentPlayersWithScores`, da)
  const tmnt = da.tmnt
    tournament.value = tmnt
    store.tournament = tmnt
    store.holes = tmnt.holes
    tPlayers = da.tplayers
    console.log('enter tmnt holes for tmnt', tmnt.holes)
    tPlayers.forEach(p => {
      if (p.f9scores !== null) p.f9total = p.f9scores.reduce((x, y) => x + y)
      if (p.b9scores !== null) p.b9total = p.b9scores.reduce((x, y) => x + y)
      if (p.f9scores !== null && p.b9scores !== null) p.gstotal = p.f9total + p.b9total
      if (p.f9total === 0) p.f9total = null
      if (p.b9total === 0) p.b9total = null
      if (p.gstotal === 0) p.gstotal = null
    })
    console.log('tplayer', tPlayers, ' of tmnt ', tmnt)
    store.pageTitle = 'Signup ' + tmnt.game
}
function setPlyaersForTournament (da) {
  console.log(`-CK-fn-setPlyaersForTournament`, da)
  playersForTournament.value = da.lst
  if (PGCAdmin) playersForTournament.value.push({ value:-1, label:'Add New Player' })
}
function setUnexpiredTournaments (da) {
  // console.log(`-CK-fn-setUnexpiredTournaments`, da)
  activeGames.value = da.games
  if (activeGames.value.length === 0) {
    $q.dialog({ title: 'there are no active games. Please ask the Admin to create the games/tournaments/outings etc..' })
    $router.push({ path: '/' })
  } else if (activeGames.value.length === 1) {
    // showSelectedGame.value = true
    const tournament = activeGames.value[0]
    showSignupPage(tournament.id)
  } else {
    console.log('-INFO-there are more than one active games, show them and let user to choose which one to signup')
    // console.log('-CK-activeGames')
    // showSelectedGame.value = false
    tmntId = 0
  }
}
function getUnexpiredTournaments (gameName = 'ALL') {
  // console.log(`-CK-fn-getUnexpiredTournaments gameName=${gameName}`)
  const path = process.env.API + '/golf/UnexpiredTournaments/' + gameName
  gaxios(path)
}
function getIcon (p) {
  return p.activity === 'both' ? 'people' : p.activity === 'golf' ? 'golf_course' : 'restaurant'
}
function getColor (p) {
  return p.activity === 'both' ? 'red' : p.activity === 'golf' ? 'green' : 'blue-9'
}
function updTplayerActivity (p) {
  console.log('-CK-fn-updTplayerActivity', p.dinn, p)
  const dx = $q.dialog({
    title: 'Options',
    message: 'Hi, ' + p.player + ' How do you want to register?',
    ok: { label: 'OK', push: true },
    cancel: 'cancel',
    color: 'primary',
    options: {
      type: 'radio',
      model: 'play_dinner',
      items: [
        { label: 'Golf & Dinner (打球 吃饭)', value: 'both', color: 'primary' },
        { label: 'Golf Only (只打球)', value: 'golf', color: 'red' },
        { label: 'Dinner Only (只吃饭)', value: 'dinn', color: 'blue' }
      ]
    }
  })
  // console.log('-CK-fn-player:', playerId, player, dx)
  dx.onOk(() => {
    // if (option === undefined) {
    //   $q.dialog({title: 'Please select play golf only or dinner only or both'})
    //   return
    // }
    const inData = {}
    inData.tmntId = store.tournament.id
    inData.id = p.id
    // inData.activity = option
    // console.log(-CK-p.player + ' selected', inData)
    const path = process.env.API + '/golf/updTplayerActivity'
    paxios(path, inData)
  }).onCancel(() => { $q.notify('canceled')})
}
// function axiosBack (target, da) {
//   if (target === 'XXXgolf.getUnexpiredTournaments') {
//     activeGames.value = da.games
//     if (da.games.length === 0) {
//       $q.dialog({ title: 'there are no active games. Please ask the Admin to create the games/tournaments/outings etc..' })
//       $router.push({ path: '/' })
//     } else if (activeGames.value.length === 1) {
//       // showSelectedGame.value = true
//       const tournament = activeGames.value[0]
//       showSignupPage(tournament.id)
//     } else {
//       // $q.localStorage.set('tournament', {id:0})
//       console.log('-INFO-there are more than one active games -- to show them and let user to choose which one to act on')
//       console.log('-CK-activeGames', activeGames)
//       // showSelectedGame.value = false
//       tmntId = 0
//     }
//   } else if (target === 'XXXgolf.getPlayersForTournament') {
//     playersForTournament.value = da.lst
//     console.log('-ab-playersForTournament', da)
//     if (PGCAdmin) playersForTournament.value.push({ value:-1, label:'Add New Player' })
//   } else if (target === 'XXXgolf.getTournamentPlayersWithScores') {
//     console.log('-CK-ab-getTournamentPlayersWithScores da', da)
//     const tmnt = da.tmnt
//     tournament.value = tmnt
//     store.tournament = tmnt
//     store.holes = tmnt.holes
//     tPlayers = da.tplayers
//     console.log('enter tmnt holes for tmnt', tournamentId, tmnt.holes)
//     tPlayers.forEach(p => {
//       if (p.f9scores !== null) p.f9total = p.f9scores.reduce((x, y) => x + y)
//       if (p.b9scores !== null) p.b9total = p.b9scores.reduce((x, y) => x + y)
//       if (p.f9scores !== null && p.b9scores !== null) p.gstotal = p.f9total + p.b9total
//       if (p.f9total === 0) p.f9total = null
//       if (p.b9total === 0) p.b9total = null
//       if (p.gstotal === 0) p.gstotal = null
//     })
//     console.log('tplayer', tPlayers, ' of tmnt ', tmnt)
//     store.pageTitle = 'Signup ' + tmnt.game
//   } else if (target === 'golf.getTournamentPlayers') {
//     console.log('-ab-getTournamentPlayers da', da)
//     tPlayers = da.tplayers
//   } else if (target === 'golf.addTournamentPlayer') {
//     const addedPlayer = da.addedPlayer
//     console.log('-ab-addTournamentPlayer', addedPlayer)
//     if (addedPlayer.id > 0) {
//       tPlayers.unshift(addedPlayer)
//       playersForTournament.value = playersForTournament.value.filter(p => p.value !== addedPlayer.pid)
//     }
//   } else if (target === 'golf.updTplayerActivity') {
//     console.log('-ab-updTplayerActivity', da)
//     tPlayers = da
//   }
// }
const refSelOptions = ref(null)
function showPlayerList () {
  refSelOptions.value.openIt('signup', 'Signup Tournament', playersTobeAdded)
}
function userSelectedTmnt (tmnt) {
  // console.log('-CK-fn-userSeleted tmnt', tmnt)
  tournament.value = tmnt
  tmntId = tmnt.id
  year = tmnt.start_at.substring(0, 4)
  showSignupPage(tmnt)
}
// function doSorting () {
//   if (sortby === 'PNM') return tPlayers
//   else if (sortby === 'GSC') return sortByGPN('GSC')
//   else if (sortby === 'POY') return sortByGPN('POY')
//   else if (sortby === 'FPOY') return sortFinalPoints('POY')
//   else if (sortby === 'NPY') return sortByGPN('NPY')
//   else if (sortby === 'FNPY') return sortFinalPoints('NPY')
// }
// function sortByGPN (tag) {
//   const data = tPlayers
//   let showData = []
//   data.forEach(p => {
//     if (tag === 'GSC' && p.GSC > 0) showData.push(p)
//     else if (tag === 'POY' && p.poy > 0) showData.push(p)
//     else if (tag === 'NPY' && p.npy > 0) showData.push(p)
//   })
//   showData = showData.slice().sort((a, b) => {
//     if (tag === 'GSC') {
//       a = parseFloat(a.GSC)
//       b = parseFloat(b.GSC)
//     } else if (tag === 'POY') {
//       a = parseFloat(a.poy)
//       b = parseFloat(b.poy)
//     } else if (tag === 'NPY') {
//       a = parseFloat(a.npy)
//       b = parseFloat(b.npy)
//     }
//     return (a === b ? 0 : a < b ? 1 : -1)
//   })
//   if (tag !== 'GSC') showData = showData.splice(0, 16)

//   showData.sort((a, b) => {
//     a = parseFloat(a.GSC)
//     b = parseFloat(b.GSC)
//     return (a === b ? 0 : a > b ? 1 : -1)
//   })
//   const ar = []
//   showData.forEach((p, i) => {
//     p.label = i + 1
//     ar.push(p.GSC)
//   })
//   // const avgar = avgPartialArray(ar)
//   // showData.forEach((p, i) => {
//   //   p.FPOYpoint = avgar[i]
//   //   p.FNPYpoint = avgar[i]
//   // })
//   return showData
// }
// function sortFinalPoints (tag) {
//   const da = sortByGPN(tag)
//   da.forEach(p => {
//     if (tag === 'POY') {
//       p.finalPOY = p.FPOYpoint * 5 + parseFloat(p.poy)
//     } else if (tag === 'NPY') {
//       p.finalNPY = p.FNPYpoint * 5 + parseFloat(p.npy)
//     }
//   })
//   da.sort((a, b) => {
//     if (tag === 'POY') {
//       a = parseFloat(a.finalPOY)
//       b = parseFloat(b.finalPOY)
//     } else if (tag === 'NPY') {
//       a = parseFloat(a.finalNPY)
//       b = parseFloat(b.finalNPY)
//     }
//     return (a === b ? 0 : a > b ? -1 : 1)
//   })
//   da.forEach((p, i) => { p.label = i + 1 })
//   return da
// }
// function getColors (i) {
//   return i % 2 === 0 ? 'teal-8' : 'teal-10'
// }
function delTournamentPlayer (p, idx) {
  if (!SysAdmin) {
    console.log('you do not have privilege to remove tournament player')
    $q.dialog({
      title: 'Information to remove tournament players',
      message: 'Please ask Jim Huang or Chen Li or Shengli or Jiajin to remove'
    })
    return
  }
  const player = p.fullname
  $q.dialog({
    title: 'Confirm',
    // message: 'Remove ' + player + '(' + p.id + ') from this tournament?',
    message: 'Remove " ' + player + ' " from this tournament?',
    ok: 'Conform',
    cancel: 'Cancel'
  }).onOk(() => {
    let path = process.env.API + '/golf/delTournamentPlayer/' + p.id
    gaxios(path)
    tPlayers.splice(idx, 1)
    signerCount.value--
    const toBeAddedPlayer = {}
    toBeAddedPlayer.label = player
    toBeAddedPlayer.value = p.playerId
    playersTobeAdded.value.unshift(toBeAddedPlayer)
  })
  $q.notify({
    color: 'info',
    position: 'top',
    message: player + ' has been removed from the tournament',
    icon: 'info'
  })
}
function addTournamentPlayer (model, opt) {
  if (opt.label === 'Add New Player') {
    refAddNewPlayer.value.openIt('create', null)
  } else {
    // player = opt.label
    // playerId = opt.value
    // model = model
    console.log('-CK-fn-addTournamentPlayer:', model, opt)
    const dx = $q.dialog({
      title: 'Options',
      message: 'Hi, How do you want to register?',
      ok: { label: 'signup', push: true },
      cancel: 'cancel',
      color: 'primary',
      options: {
        type: 'radio',
        model: 'golf',
        items: [
          { label: 'Golf & Dinner (打球 吃饭)', value: 'both', color: 'primary' },
          { label: 'Golf Only (只打球)', value: 'golf', color: 'red' },
          { label: 'Dinner Only (只吃饭)', value: 'dinn', color: 'blue' }
        ]
      }
    })
    console.log('-CK-fn-player:', playerId, player)
    dx.onOk(() => {
      const inData = {}
      inData.tmntId = store.tournament.id
      inData.gameId = store.tournament.game_id
      inData.playerId = playerId
      inData.fullname = player
      inData.year = year
      // inData.activity = option
      inData.status = 'A'
      // inData = [inData] // to be able to re-use upserTplayers at the backend
      const path = process.env.API + '/golf/addTournamentPlayer'
      paxios(path, [inData])
      playersForTournament.value = playersForTournament.value.filter(p => p.value !== playerId)
    }).onCancel(() => { $q.notify('canceled')})
  }
}
// function calcFinalPOY () {
//   const da = poyList
//   const ar = []
//   da.value.forEach(a => { ar.push(a.GSC) })
//   const avgarr = avgPartialArray(ar)
//   for (let i = 0; i < 16; i++) {
//     const p = da[i]
//     p.finalPOY = avgarr[i] * 5 + parseFloat(p.poy)
//   }
//   const order = -1
//   const data = poyList.value.slice().sort((a, b) => {
//     a = parseFloat(a.finalPOY)
//     b = parseFloat(b.finalPOY)
//     return (a === b ? 0 : a > b ? 1 : -1) * order
//   })
//   data.forEach((p, i) => { p.label = i + 1 })
//   return data
// }
// function getFakeGSC () {
//   const cnt = tPlayers.length
//   for (let i = 0; i < cnt; i++) {
//     const gsc = Math.floor((Math.random() * 30) + 72)
//     tPlayers[i].GSC = gsc
//   }
// }
function showSignupPage (tmt) {
  // console.log('-CK-fn-showSignUpPage stored tmnt', tmt)
  year = tmt.year
  const tid = tmt.id
  let path = process.env.API + '/golf/getPlayersForTournament/' + tid
  gaxios(path)
  path = process.env.API + '/golf/getTournamentPlayersWithScores/' + tid
  gaxios(path)
}
</script>
