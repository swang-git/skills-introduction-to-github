<template>
<div class="bg-teal-10">
  <div class="row q-pl-lg">
    <q-chip size="18px" icon="people" color="red-5" text-color="white">{{ signerCount }}<q-tooltip class="bg-red text-white">Number of Peoples Signed up</q-tooltip></q-chip>
    <q-chip size="18px" icon="golf_course" color="green" text-color="white">{{ playerCount }}<q-tooltip class="bg-green-8 text-white">Number of Peoples Golfing</q-tooltip></q-chip>
    <q-chip size="18px" icon="restaurant" color="blue-7" text-color="white">{{ dinnerCount }}<q-tooltip class="bg-blue-7 text-white">Number of Peoples To Dinner</q-tooltip></q-chip>
  </div>
  <CardSelection v-if="activeGames.length > 1 && tmntId==0" :selections="activeGames" todo="Signup" :tplayers="tPlayers" @open-signup-dialog="openSelectionDialog" />
  <ModifyDialog @del-tplayer="delTplayer" @upd-tplayer="updTplayer" />
  <SelectionDialog @new-tplayer="addNewTplayer"/>
</div>
</template>
<script setup>
// /* eslint-disable */
import emitter from 'tiny-emitter/instance'
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
const $router = useRouter()
import { libFunctions } from '../composables/libFunctions'
const { $q, store, isDesk, SysAdmin, ENV_API, PGCsAdmin } = libFunctions()
import { axiosFunctions } from '../composables/axiosFunctions'
const { paxios, gaxios } = axiosFunctions()

import CardSelection from '../components/CardSelection.vue'
import ModifyDialog from '../components/ModifyDialog.vue'
import SelectionDialog from '../components/SelectionDialog.vue'

var year = (new Date()).getFullYear()
var tmntId = 0
const tPlayers = ref([])
const playersForTournament = ref([])
const allPlayers = ref([])
var activeGames = ref([])
const refAddNewPlayer = ref(null)

onMounted(() => refAddNewPlayer )
console.log('-ST-Signup')
if (isDesk) store.pageTitle = 'Signup Tournament of ' + year
else store.pageTitle = 'Signup Tournament'
store.page = 'signup'
emitter.on('tmnt-id', (x) => tmntId = x)
emitter.on('golf-UnexpiredTournaments', (x) => setUnexpiredTournaments(x))
emitter.on('golf-getPlayersForTournament', (x) => setPlyaersForTournament(x))
emitter.on('golf-getTournamentPlayersWithScores', (x) => setTournamentPlayersWithScores(x))
emitter.on('golf-getValidGameTplayers', (x) => setValidGameTplayers(x))
emitter.on('golf-getAllPlayers', (x) => setAllPlayers(x))
getUnexpiredTournaments()
getAllPlayers()

const signerCount = computed(() => { return tPlayers.value.length })
const dinnerCount = computed(() => { return tPlayers.value.filter(p => { return /dinn|both/.test(p.activity) }).length })
const playerCount = computed(() => { return tPlayers.value.filter(p => { return /golf|both/.test(p.activity) }).length })
const tList = computed(() => { return tPlayers.value })

//== function sections
function updTplayer (ntp) {
  console.log(`-fn-updTplayer ntp_name=${ntp.name}`, ntp)
  tPlayers.value = tPlayers.value.map(elm => elm.player_id === ntp.player_id ? { ...elm, ...ntp} : elm)
  const path = ENV_API + '/golf/addTournamentPlayer'
  paxios(path, [ntp])
}
function delTplayer (tpid) {
  console.log(`-fn-delTplayer tpid=${tpid}`)
  tPlayers.value = tPlayers.value.filter(p => p.id != tpid)
  const path = ENV_API + '/golf/delPGCTplayer/' + tpid
  gaxios(path)
}
function addNewTplayer (tp) {
  console.log(`-fn-addNewTplayer`, tp)
  tPlayers.value.push(tp)
}
function openSelectionDialog (tmt, grp) {
  const tplayerIds = tPlayers.value.map(p => p.player_id)
  console.log(`-fn-openSelectionDialog grp=${grp}`, tplayerIds)
  const players = allPlayers.value.map(p => ({ value: p.player_id, label: p.name })).filter(x => !tplayerIds.includes(x.value))
  emitter.emit('open-SelectionDialog', 'add_circle', 'Signup Players', players, tmt, grp)
}
function setValidGameTplayers (da) {
  tPlayers.value = da.tplayers
  console.log(`-fn-setValidGameTplayers`, tPlayers.value)
  // setCounts()
}
function getAllPlayers () {
  console.log(`-fn-getAllPlayers`)
  const path = ENV_API + '/golf/getAllPlayers'
  gaxios(path)
}
function setAllPlayers (da) {
  allPlayers.value = da.players
  console.log(`-fn-setAllPlayers`, allPlayers.value)
}
function setTournamentPlayersWithScores (da) {
  console.log(`-CK-fn-setTournamentPlayersWithScores`, da)
  const tmnt = da.tmnt
    tournament.value = tmnt
    store.tournament = tmnt
    store.holes = tmnt.holes
    tPlayers.value = da.tplayers
    console.log('enter tmnt holes for tmnt', tmnt.holes)
    tPlayers.value.forEach(p => {
      if (p.f9scores !== null) p.f9total = p.f9scores.reduce((x, y) => x + y)
      if (p.b9scores !== null) p.b9total = p.b9scores.reduce((x, y) => x + y)
      if (p.f9scores !== null && p.b9scores !== null) p.gstotal = p.f9total + p.b9total
      if (p.f9total === 0) p.f9total = null
      if (p.b9total === 0) p.b9total = null
      if (p.gstotal === 0) p.gstotal = null
    })
    console.log('tplayer', tPlayers.value, ' of tmnt ', tmnt)
    store.pageTitle = 'Signup ' + tmnt.game
}
function setPlyaersForTournament (da) {
  console.log(`-CK-fn-setPlyaersForTournament`, da)
  playersForTournament.value = da.lst
  if (PGCsAdmin) playersForTournament.value.push({ value:-1, label:'Add New Player' })
}
function setUnexpiredTournaments (da) {
  console.log(`-CK-fn-setUnexpiredTournaments`, da.games)
  activeGames.value = da.games
  if (activeGames.value.length === 0) {
    $q.dialog({ title: 'there are no active games. Please ask the Admin to create the games/tournaments/outings etc..' })
    $router.push({ path: '/' })
  } else if (activeGames.value.length === 1) {
    // showSelectedGame.value = true
    const tournament = activeGames.value[0]
    // showSignupPage(tournament.id)
    getPlayers4SignupPage(tournament)
  } else {
    console.log('-INFO-there are more than one active games, show them and let user to choose which one to signup')
    // console.log('-CK-activeGames')
    // showSelectedGame.value = false
    tmntId = 0
  }
  const tmntIds = da.games.map(p => p.id)
  if (tmntIds.length > 0) {
    const path = ENV_API + '/golf/getValidGameTplayers'
    paxios(path, tmntIds)
  }
}
function getUnexpiredTournaments (gameName = 'ALL') {
  // console.log(`-CK-fn-getUnexpiredTournaments gameName=${gameName}`)
  const path = ENV_API + '/golf/UnexpiredTournaments/' + gameName
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
    const path = ENV_API + '/golf/updTplayerActivity'
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
//     tPlayers.value = da.tplayers
//     console.log('enter tmnt holes for tmnt', tournamentId, tmnt.holes)
//     tPlayers.value.forEach(p => {
//       if (p.f9scores !== null) p.f9total = p.f9scores.reduce((x, y) => x + y)
//       if (p.b9scores !== null) p.b9total = p.b9scores.reduce((x, y) => x + y)
//       if (p.f9scores !== null && p.b9scores !== null) p.gstotal = p.f9total + p.b9total
//       if (p.f9total === 0) p.f9total = null
//       if (p.b9total === 0) p.b9total = null
//       if (p.gstotal === 0) p.gstotal = null
//     })
//     console.log('tplayer', tPlayers.value, ' of tmnt ', tmnt)
//     store.pageTitle = 'Signup ' + tmnt.game
//   } else if (target === 'golf.getTournamentPlayers') {
//     console.log('-ab-getTournamentPlayers da', da)
//     tPlayers.value = da.tplayers
//   } else if (target === 'golf.addTournamentPlayer') {
//     const addedPlayer = da.addedPlayer
//     console.log('-ab-addTournamentPlayer', addedPlayer)
//     if (addedPlayer.id > 0) {
//       tPlayers.value.unshift(addedPlayer)
//       playersForTournament.value = playersForTournament.value.filter(p => p.value !== addedPlayer.pid)
//     }
//   } else if (target === 'golf.updTplayerActivity') {
//     console.log('-ab-updTplayerActivity', da)
//     tPlayers.value = da
//   }
// }
// const refSelOptions = ref(null)
function showPlayerList () {
  // refSelOptions.value.openIt('signup', 'Signup Tournament', playersTobeAdded)
  emitter.emit('open-SelOptionsWithSearch', 'signup', 'Signup Tournament', playersTobeAdded.value)
}
function userSelectedTmnt (tmnt) {
  console.log('-CK-fn-userSeletedTmnt', tmnt)
  tournament.value = tmnt
  tmntId = tmnt.id
  year = tmnt.start_at.substring(0, 4)
  getPlayers4SignupPage(tmnt)
}
// function doSorting () {
//   if (sortby === 'PNM') return tPlayers.value
//   else if (sortby === 'GSC') return sortByGPN('GSC')
//   else if (sortby === 'POY') return sortByGPN('POY')
//   else if (sortby === 'FPOY') return sortFinalPoints('POY')
//   else if (sortby === 'NPY') return sortByGPN('NPY')
//   else if (sortby === 'FNPY') return sortFinalPoints('NPY')
// }
// function sortByGPN (tag) {
//   const data = tPlayers.value
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
    let path = ENV_API + '/golf/delTournamentPlayer/' + p.id
    gaxios(path)
    tPlayers.value.splice(idx, 1)
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
    const player = opt.label
    const playerId = opt.value
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
    console.log(`-CK-fn-player playerId=${playerId} player=${player}`)
    dx.onOk(() => {
      const inData = {}
      inData.tmntId = store.tournament.id
      inData.gameId = store.tournament.game_id
      inData.playerId = playerId
      inData.fullname = player
      inData.year = year
      inData.activity = 'both'
      inData.status = 'A'
      // inData = [inData] // to be able to re-use upserTplayers at the backend
      console.log(`-fn-addTournamentPlayer`, inData)
      const path = ENV_API + '/golf/addTournamentPlayer'
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
//   const cnt = tPlayers.value.length
//   for (let i = 0; i < cnt; i++) {
//     const gsc = Math.floor((Math.random() * 30) + 72)
//     tPlayers.value[i].GSC = gsc
//   }
// }
function getPlayers4SignupPage (tmt) {
  console.log('-CK-fn-showSignUpPage stored tmnt', tmt)
  year = tmt.year
  const tid = tmt.id
  let path = ENV_API + '/golf/getPlayersForTournament/' + tid
  gaxios(path)
  path = ENV_API + '/golf/getTournamentPlayersWithScores/' + tid
  gaxios(path)
}
</script>
