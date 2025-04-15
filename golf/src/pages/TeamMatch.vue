<template>
<div class="bg-teal-10 q-pa-xs">
  <div v-if="(gameId==14 || gameId==13 || gameId==16) && matchDate">
    <TeamMatchGrouping :gameId="gameId" :games="games" :matchDate="matchDate" :courseInfo="courseInfo" :aliases="aliases"
      :handicaps="handicaps" :handicapFlag="handicapFlag"
      @course-info="showCourseInfo" @change-tmnt="changeTmnt" @switch-aliases="switchAliases"
      @switch-handicap= "switchHandicap" />
    <TeamMatchGrouped  :gameId="gameId" :games="games" :matchDate="matchDate" :courseInfo="courseInfo" @course-info="showCourseInfo" @change-tmnt="changeTmnt" :groups="groups" />
  </div>

  <div v-if="matchDate==null" style="margin-left:-2px" class="text-white text-h6">
    <q-btn flat class="q-pl-sm text-h6 text-yellow-3" label="Create New Match" no-cap @click="createNewMatch()" icon="img:icons/hole_in_one.png" />
    <!-- <q-btn v-if="kjNewPlayer.length>0 && gameId==14" rounded outline icon-right="person_add" :label="isDesk ? 'add new player' : '...'" @click="addKjNewPlayer" /> -->
    <q-btn v-if="gameId==14" rounded outline :label="isDesk ? 'Check KJ Game Data' : '...'" @click="showKjGamePlayers"><q-icon name="check" color="green" size="30px" /></q-btn>
    <!-- <q-btn v-else-if="SysAdmin && gameId!=14" rounded outline icon-right="person_add" :label="isDesk ? 'add new player' : '...'" @click="addNewPlayer" /> -->
    <q-btn v-else-if="SysAdmin && gameId!=14" rounded outline icon-right="person_add" :label="isDesk ? 'add new player' : '...'" @click="addNewPlayer" />
  </div>

  <div v-if="matchBanner" class="bg-cyan">
    "Show Match Banner"
  </div>
  <component :is="TeamMatchCreator" />
  <TeamMatchList :matchList="dalist" @user-selected="userSelectedMatchDate" />
  <CourseInfo />
  <KJNewPlayerDialog />
  <TeamGroupPointDetails />
  <!-- <SelOptionsWithSearch ref="selOptions" @selected-option="selectedPlayer" /> -->
  <SelOptionsWithSearch ref="refSelOptionsWithSearch" @selected-option="selectedPlayer" />
  <SimpPlayerDialog ref="refSimpPlayerDialog" />
  <KjGameDataDisplay />
</div>
</template>
<script setup>
import { scroll } from 'quasar'
import emitter from 'tiny-emitter/instance'
import { ref, getCurrentInstance, onMounted } from "vue";
// import { utilFunctions } from '../composables/utilFunctions'
import { axiosFunctions } from '../composables/axiosFunctions'
import { dayFunctions } from '../composables/dayFunctions'
import { libFunctions } from '../composables/libFunctions'
// import { cssFunctions } from '../composables/cssFunctions'
// import { storeFunctions } from '../composables/storeFunctions'

import TeamGroupPointDetails from 'pages/TeamGroupPointDetails'
import TeamMatchCreator  from './TeamMatchCreator'
import TeamMatchList from './TeamMatchList'
import TeamMatchGrouping from './TeamMatchGrouping'
import TeamMatchGrouped from './TeamMatchGrouped'
import CourseInfo from '../components/CourseInfo'
import KJNewPlayerDialog from '../components/KJNewPlayerDialog'
// import Tooltip from 'src/components/ToolTip'
import SelOptionsWithSearch from 'src/components/SelOptionsWithSearch'
// import sel from 'src/components/Selection'
import KjGameDataDisplay from './KjGameDataDisplay'
import SimpPlayerDialog from '../components/SimpPlayerDialog'

//== data section
const { getScrollTarget, setVerticalScrollPosition } = scroll
const app = getCurrentInstance()
const route = app.appContext.config.globalProperties.$route
const { gaxios } = axiosFunctions()
const { today, yyyymmddHHMM} = dayFunctions()
const { $q, store, buildApp, isLocal, searchQuery, dats, SysAdmin, iPhone11ProMax, isDesk, dalist, userGuidePage } = libFunctions()
const groupingDone = ref(false)
const tmnt = ref({})
const gameId = ref(null)
const tmntId = ref(null)
const matchDate = ref(null)
const matchName = ref(null)
const matches = ref([])
const games = ref([]) // initiate null causing problems
const year = ref(null)
const groupId = ref(-1)
const groups = ref([])
const tplayers = ref([])
const courseInfo = ref([])
const courseInfoHolder = ref([])
const gscenario = ref(0)
const matchBanner = ref(false)
// const daMatchList = ref([])
const kjNewPlayer = ref([])
const kjAliases = ref([])
// const lastHandicapDate = ref(null)
// const screen_height = $q.screen.height
// const screen_width = $q.screen.width
const exNeshanic = /^(Neshanic)\s+(Valley)\s+(.*)/
const refSelOptionsWithSearch = ref(null)
const handicaps = ref(null)
const handicapFlag = ref(null)
const aliases = ref([])
var kjGamePlayerList = []
var kjPlayer = null
const aliasesJZ = ref([])
var aliasesMM = []
var handicapsJZ = {}
var handicapsMM = {}
const refSimpPlayerDialog = ref()

onMounted(() => {
  refSelOptionsWithSearch
  refSimpPlayerDialog
})
// emitter.on section
// console.log(`-ST-TeamMatch matchName=${tmnt.value.id} iPhoneX=${iPhoneX} isDesk=${isDesk} JZsAdmin=${JZsAdmin.value}`, tmnt.value)
console.log(`-CK-TeamMatch isDesk=${isDesk} iPhone11ProMax=${iPhone11ProMax}`)
// emitter.on('golf-usertype', (x) => { golfUserType.value = x })
emitter.on('search', (x) => { searchQuery.value = x })
emitter.on('golf-getCourseInfo', (x) => { setCourseInfo(x) })
emitter.on('golf-getTeamMatchPlayers', (x) => { setTeamMatchPlayers(x) })
emitter.on('golf-getTournaments', (x) => { setTournaments(x) })
emitter.on('golf-addTournament', () => { getTournamentsByGameId() })
// emitter.on('golf-updTournament', (x) => { console.log(`-CK-updTournament`, x); updMatchGame(x.matchGame) })
emitter.on('golf-updTournament', (x) => { console.log(`-CK-updTournament`, x); setTournaments(x) })
emitter.on('golf-getKjAliases', (x) => { setKjAliases(x) })
emitter.on('golf-getKjGameDataByMpId', (x) => { showKjGameData(x) })
emitter.on('golf-getKjGameDataByDate', (x) => { showKjGameData(x) })
// emitter.on('golf-getKjGameDates', (x) => { setKjGameDates(x) })
// emitter.on('golf-getKjGamePlayers', (x) => { kjGamePlayerList = x.kjGamePlayers })
emitter.on('golf-getKjGamePlayers', (x) => { setKjGamePlayers(x) })
// emitter.on('golf-getAliases', (x) => { if (kjNewPlayer.value.length>0) setAliases(x) })

emitter.on('golf-getAliases', (x) => setAliases(x))
emitter.on('golf-getHandicaps', (x) => setHandicaps(x))

// functions section
// function checkDeviceType () {
//   console.log('%c-CHECKING DEVICE TYPE', "font-size:10px;font-weight:600;color:red")
//   console.log(`%cHeight=${screen_height} Width=${screen_width}`, "font-size:10px;font-weight:600;color:red")
// }
function setPlayerHandicap (p) {
  if (handicapFlag.value == 13) {
    p.name = p.name.replace(/\([z|m]\)$/, '')
    p.handicap = handicapsJZ[p.player_id]
    if (p.handicap == null ) {
      p.handicap = handicapsMM[p.player_id]
      p.name += '(m)'
    }
  } else if (handicapFlag.value == 14) {
    p.name = p.name.replace(/\([m|z]\)$/, '')
    p.handicap = handicapsMM[p.player_id]
    if (p.handicap == null) {
      p.handicap = handicapsJZ[p.player_id]
      p.name += '(z)'
    }
  }
}
function switchHandicap () {
  handicapFlag.value = handicapFlag.value == 14 ? 13 : 14
  tplayers.value.forEach(p => {
    // p.handicap = handicapFlag.value == 13 ? handicapsJZ[p.player_id] : handicapsMM[p.player_id]
    setPlayerHandicap(p)
  })
}
function switchAliases (aliName) {
  aliases.value = aliName == 'JZs' ? aliasesJZ.value : aliasesMM
}
function getAliases (gameId) {
  console.log(`-fn-getAliases`)
  const path = process.env.API + '/golf/getAliases/' + gameId
  gaxios(path)
}
function setAliases (da) {
  da.gameId == 13 ? aliasesJZ.value = da.aliases : aliasesMM = da.aliases
  // aliasesJZ.value[14].alias = 'HHH'
  aliases.value = gameId.value == 13 ? aliasesJZ.value : aliasesMM
  console.log(`-fn-setAliases`, da.aliases)
}
function getHandicaps (gameId) {
  console.log(`-fn-getHandicaps`)
  const path = process.env.API + `/golf/getHandicaps/${gameId}`
  gaxios(path)
}
function setHandicaps (da) {
  console.log(`-fn-setHandicaps`, da)
  if (da.gameId == 13) {
    da.handicaps.forEach(p => handicapsJZ[p.player_id] = p.handicap)
    aliasesJZ.value.forEach(p => p.handicap = handicapsJZ[p.player_id])
  } else {
    da.handicaps.forEach(p => handicapsMM[p.player_id] = p.handicap)
    aliasesMM.forEach(p => p.handicap = handicapsMM[p.player_id])
  }
  handicaps.value = gameId.value == 13 ? handicapsJZ : handicapsMM
  aliases.value = gameId.value == 13 ? aliasesJZ.value : aliasesMM
}
function selectedPlayer (model, selectedOpt) {
  console.log('-CK-fn-selectedPlayer', selectedOpt)
  const mpId = selectedOpt.value
  kjPlayer = selectedOpt.label
  console.log(`-CK-fn-selectedPlayer name=${kjPlayer} mpId=${mpId}`)
  let path = process.env.API + '/golf/getKjGameDataByMpId/' + mpId
  if (/\d{4}-\d\d-\d\d/.test(kjPlayer)) path = process.env.API + '/golf/getKjGameDataByDate/' + kjPlayer
  gaxios(path)
}
function showKjGamePlayers () {
  // emitter.emit('open-SelOptionsWithSearch', 'person', 'KJ Game Players', kjGamePlayerList)
  refSelOptionsWithSearch.value.openIt('person', 'KJ Game Players', kjGamePlayerList)
}
function getKjGamePlayers () {
  console.log(`-CK-fn-getKjGamePlayers gameId=${gameId.value}`)
  const path = process.env.API + '/golf/getKjGamePlayers'
  gaxios(path)
}
function setKjGamePlayers (da) {
  const kjGameLatestDate = da.kjGameLatestDate
  kjGamePlayerList = da.kjGamePlayers
  if (!/\d{4}-\d\d-\d\d/.test(kjGamePlayerList[0].label)) kjGamePlayerList.unshift({label:kjGameLatestDate, value:0})
  console.log(`-CK-fn-setKjGamePlayers kjGameLatestDate=${kjGameLatestDate}`, kjGamePlayerList)
}
function showKjGameData(da) {
  console.log(`-CK-fn-showKjGameData gameId=${gameId.value}`, da)
  emitter.emit('open-KjGameDataDisplay', kjPlayer, da.kjGameData)
}
// function getKjAliases() {
//   // console.log(`-CK-fn-getKJAliases gameId=${gameId} matchDate=${props.matchDate}`)
//   const path = process.env.API + '/golf/getAliases/14/0'
//   gaxios(path)
// }
function setKjAliases (da) {
  console.log(`-CK-fn-setKjAliases`, da)
  kjAliases.value = da.aliases
  if (gameId.value != 14) return
  let kjnp = kjNewPlayer.value[0]
  console.log(`kjNewPlayer gameId=${gameId.value}`, kjnp, kjAliases.value)
  emitter.emit('open-KJNewPlayerDialog', 'create', { id:kjnp.id, firstname:kjnp.firstname, lastname:kjnp.lastname }, gameId.value, kjAliases.value)
}
// function XXXaddKjNewPlayer () {
//   if (kjNewPlayer.value.length > 0) {
//     let tit = 'There are new MMs player(s)'
//     let msg = 'Go open the new match and they will be added there'
//     // emitter.emit('add-kj-new-player', kjNewPlayer.value[0])
//     emitter.emit('open-InfoDisplay', tit, msg)
//     if (gameId.value == 14) getKjAliases()
//   }
// }
function addNewPlayer () {
  if (!SysAdmin.value) {
    let tit = 'SysAdmin Notice'
    let msg = 'Use player list(PL) to add new player. Ask 胜利 for help.'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  refSimpPlayerDialog.value.openIt('create', {}, false, gameId.value)
}
function changeTmnt(tmntId, act) {
  console.log(`-CK-fn-changeTmnt act=${act} tmntId=${tmntId} isLocal=${isLocal} system_admin_cookie=${$q.cookies.get('sid_system_admin')}`)
  if (isLocal || SysAdmin.value) {
    const g = games.value.find(p => p.id === tmntId)
    console.log(`-CK-change-tmnt`, g)
    const tmnt = {
      id: tmntId,
      course_id: g.course_id,
      courseName: g.course.name,
      start_at: g.start_at.substring(0, 16),
      game: matchName.value,
      game_id: g.game_id,
      teetime_gap: g.teetime_gap,
      numGroup: 1,
      fees: parseFloat(g.fees),
      note: g.note === null ? 'tournamentId: ' + tmntId : g.note,
      mtee_id: g.mens_tee_id,
      mtee: g.mteebox.teebox + ' ~ ' + g.mteebox.rating + ' ' + g.mteebox.slope + ' ' + g.mteebox.yardage,
      ltee_id: g.lady_tee_id,
      ltee: g.lteebox === null ? null : g.lteebox.teebox + ' ~ ' + g.lteebox.rating + ' ' + g.lteebox.slope + ' ' + g.lteebox.yardage,
    }
    console.log(`-fn-changetmnt act=${act}`, tmnt)
    emitter.emit('open-TeamMatchCreator', tmnt, act)
  } else if (matchDate.value < today() && !SysAdmin.value) {
    console.log('-fn-changeTmnt Can not del/upd expired')
    const tit = 'Expired Game'
    const msg = 'Expired Game could not be revised'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  } else if (!SysAdmin.value) {
    const tit = 'Revision not Permitted'
    const msg = 'Please contact 胜利 for help'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
}
function showCourseInfo (tmntId, tee) {
  const course = courseInfo.value.find(c => c.tmntId == tmntId)
  console.log(`%c-CK-tmntId=${tmntId} tee=${tee}`, 'color:red;font-size:14px', course)
  emitter.emit('open-CourseInfo', course, tee)
}
// function XXsetGscenario(gs) {
//   console.log(`-CK-fn-setGscenariogscenario=${gs}`)
//   gscenario.value = gs
// }
function setPageTitle () {
  store.pageTitle = matchName.value
  store.page = matchName.value
}
const getTournamentsByGameId = () => {
  console.log(`%c-CK-fn-getTournaments`, 'color:pink;font-size:18px')
  setPageTitle()
  matchDate.value = null
  const path = process.env.API + '/golf/getTournaments/' + gameId.value
  gaxios(path)
}
function setTournaments (da) {
  // daMatchList.value = da
  let showTmntId = da.tmntId
  console.log(`%c-CK-fn-setTournaments`, 'color:red;font-size:18px', da)
  da.matches.sort((a, b) => a.start_at > b.start_at ? 1 : -1)
  matches.value = da.matches
  // if (gameId.value == 14) {
  //   kjNewPlayer.value = da.kjNewPlayer
  //   if (kjNewPlayer.value.length > 0) {
  //     const path = process.env.API + '/golf/getKjAliases/' + da.kjNewPlayer[0].game_date
  //     gaxios(path)
  //   }
  // }
  let jzm = []
  // const ex = /^(Neshanic)\s+(Valley)\s+(.*)/
  matches.value.forEach(m => {
    const mdate = m.start_at.substring(0, 10)
    if (jzm.filter(j => (j.date === mdate && j.gameId === gameId.value)).length > 0) return
    const cname = m.course.name.replace(exNeshanic, "$1 V. $3")
    const match_start_time = m.start_at.substring(11, 16)
    const x = { id:m.id,courseId:m.course_id,slope:m.mteebox.slope,rating:m.mteebox.rating,
      mteeboxId:m.mens_tee_id,lteeboxId:m.lady_tee_id,teetime:m.start_at,date:mdate,cname:cname,
      gameId:gameId.value,start_time:match_start_time }  // for future to make generic
    jzm.push(x)
  })
  dats.value = jzm.sort((a, b) => a.teetime > b.teetime ? -1 : 1)
  if (showTmntId > 0) emitter.emit('show-match-grouped', false)
}
// const userSelectedMatchDate = (match) => {  // get all games in the match for the matchDate
function userSelectedMatchDate (match) {  // get all games in the match for the matchDate
  // console.info('-CK-fn-userSelectedMatchDate match=', match)
  console.info('%c-CK-fn-userSelectedMatchDate match=', 'color:red;font-size:20px', match)
  emitter.emit('show-match-grouping', false)
  emitter.emit('show-match-grouped', false)
  tmnt.value = match
  tmntId.value = match.id
  matchDate.value = match.date
  // groupingDone.value = matchDate.value < today()
  year.value = match.date.substring(0, 4)
  // console.log(`%c-CK-userSelectedMatchDate:${match.date}`, "font-size:10px;font-weight:600;color:red")
  games.value = matches.value.filter(m => m.start_at.substring(0, 10) === matchDate.value)
  // games.value = dats.value.filter(m => m.teetime.substring(0, 10) === matchDate.value)
  // numGroups.value = games.value.length
  groups.value = new Array(games.value.length)
  courseInfo.value = []
  const gms = games.value.sort((a, b) => a.start_at < b.start_at ? -1 : 1)
  // console.log(`-fn-userSelectedMatchDate=${matchDate.value} tmntId=${tmntId.value}`, gms, match, games.value)
  console.log(`%c-CK-userSelected MatchDate=${matchDate.value} tmntId=${tmntId.value} games=`, 'color:red;font-size:18px', games.value)
  for (let i=0; i<gms.length; i++) {
    const g = gms[i]
    const tmntId = g.id
    const courseId = g.course_id
    const teeboxId = g.mteebox.id
    groupId.value = i

    let course = courseInfoHolder.value.find(p => p.tmntId == match.id)
    if (course == undefined) getCourseInfo(tmntId, courseId, teeboxId)
    else courseInfo.value.push(course)

    if (!userGuidePage.value.includes('_')) userGuidePage.value = userGuidePage.value + '_Date'
    // console.log(`-XXsMatch- userGuidePage=${userGuidePage.value}`)
  }

  getTeamMatchPlayers()
  const elm = document.getElementById("glist")
  const duration = 400
  setVerticalScrollPosition(getScrollTarget(elm), 0, duration)
}
const getCourseInfo = (tmntId, courseId, teeboxId) => {
  console.log(`%c-fn-getCourseInfo tmntId=${tmntId} courseId=${courseId} teeboxId=${teeboxId}`, 'color: red; font-size:18px', 'games=', games.value)
  const path = process.env.API + `/golf/getCourseInfo/${tmntId}/${courseId}/${teeboxId}`
  gaxios(path)
}
const setCourseInfo = (da) => {
  console.log(`-fn-setCourseInfo status=${da.status} name=${da.name} nlen=${da.name.length}`, da, 'games=', games.value)
  if (da.status !== "OK") {
    console.error(`-fn-getCourseInfo FAILED status=${da.status} name=${da.name}`)
    const tit = 'FAILED: getCourseinfo Status'
    const msg = da.status
    emitter.emit('open-InfoDisplay', tit, msg)
  }
  // const ex = /^(Neshanic)\s+(Valley)\s+(.*)/
  // if (da.name.length > 2 && ex.test(da.name)) {
  // if (exNeshanic.test(da.name)) {
  da.name = da.name.replace(exNeshanic, "$1 V. $3")
  // console.error(`%c-fn-expTest cname=${da.name} nlen=${da.name.length}`, 'color: red;font-size:14px', da)
  courseInfo.value.push(da)
  courseInfoHolder.value.push(da)
  // console.log(`-fn-%courseInfo sent to for enter stokes`, 'color:red;font-size:16px', courseInfo.value)
}

const getTeamMatchPlayers = () => {
  tplayers.value = []
  groups.value = []
  const tmntId = 0
  const path = process.env.API + '/golf/getTeamMatchPlayers/' + tmntId + '/' + gameId.value + '/' + matchDate.value
  gaxios(path)
}
function setTeamMatchPlayers (da) {
  console.log(`%c-fn-setTeamMatchPlayers gscenario=${gscenario.value}`, 'color:red;font-size:20px da=', da, 'games=', games.value)
  if (da.tplayers == null) return getTeamMatchPlayers()
  da.tplayers.forEach(p => {
    setPlayerHandicap(p)
    // if (handicapFlag.value == 13) {
    //   p.handicap = handicapsJZ[p.player_id]
    //   if (p.handicap == null ) {
    //     p.handicap = handicapsMM[p.player_id]
    //     p.name += '(M)'
    //   }
    // } else if (handicapFlag.value == 14) {
    //   p.handicap = handicapsMM[p.player_id]
    //   if (p.handicap == null) {
    //     p.handicap = handicapsJZ[p.player_id]
    //     p.name += '(Z)'
    //   }
    // }
  })
  tplayers.value = da.tplayers.sort((a, b) => a.handicap - b.handicap)
  gscenario.value = da.group_scenario
  console.log(`%c-CK-fn-setTeamMatchPlayers tplayersLen=${tplayers.value.length} gsx=${gscenario.value} matchDate=${matchDate.value}`, 'color:red', tplayers.value, games.value)
  // console.log(`-CK-fn-KJaliases`, da.kjaliases)
  // const gm = games.value
  groups.value = []
  games.value.forEach((game, i) => {
    // const ttm = game.start_at.substring(11, 16)
    const x = {
      idx: i+1, // this will be used for team grouping setup
      players: [],
      gameId: game.game_id,
      tmntId: game.id,
      courseId: game.course.id,
      course: game.course.name,
      tee: game.mteebox.teebox,
      // teename: game.mteebox.teebox.toUpperCase(),
      teename: game.mteebox.teebox,
      ttm: game.start_at.substring(11, 16),
      note: game.note
    }
    tplayers.value.forEach(p => { p.name = p.name.replace('(DBA)', '');
      if (p.grp > 0) { // use player's grp to identify -- no tmnId in case to add a new tournament
        if (p.grp === x.idx) {
          x.players.push(p)
          x.grp = p.grp
          setCourseHandicap(p, p.grp-1)
        }
      }
    })
    groups.value.push(x)
  })
  // let mms = {idx:i+1, gameId:game.game_id, tmntId:game.id, courseId:game.course_id, course:game.course.name,
  //   tee:game.mteebox.teebox, teename:game.mteebox.teebox, ttm:game.start_at.substring(11, 16), note: game.note}
  // groups.value.push({idx:0, player_id:0})

  tplayers.value.forEach(p => {
    if (p.grp == -1) { // for MatchGrouping.vue
      groups.value[groups.value.length-1].players.push(p)
    }
  })
  if (matchDate.value >= today() && (tplayers.value.length < groups.value.length * 4 || tplayers.value.find(p => p.team == 'X'))) {
    emitter.emit('do-grouping', gscenario.value, year.value, groups.value)
    // groupingDone.value = false
    // emitter.emit('show-match-grouping', true)
  } else {
    groupingFinished() // for old matches assuming the grouping is done before go to MatchGrouping
    console.log(`-CK-fn-groupingFinished tplayers.length=${tplayers.value.length} groups.length=${groups.value.length}`)
  }
}
function setCourseHandicap(player, gi) {
  // const course = getCourse(gi)
  // console.log(`-fn-setCourseHandicap gi=${gi} courseId=${games.value[gi].course_id} cname=${games.value[gi].course.name}`)
  const gm  = games.value[gi]
  // const cname = gm.course.name
  let slope = 113
  let rating = 72
  let par = 72
  if (gm.lteebox == null) {
    slope  = gm.mteebox.slope
    rating = gm.mteebox.rating
    par = gm.mteebox.par
  } else {
    slope  = gm.lteebox.slope
    rating = gm.lteebox.rating
    par = gm.lteebox.par
  }
  // console.log(`-CK-fn-setCourseHandicap gi=${gi} par=${par} slope=${slope} rating=${rating} name=${cname} player.name=${player.name} player.handle=${player.handicap}`)
  if (player.handicap > 100) player.CourseHandicap = 'N'
  else player.courseHandicap = (player.handicap * slope / 113 + (rating - par) + par).toFixed(1)
}
function groupingFinished () {
  console.log(`-fn-groupingFinished`)
  groupingDone.value = true
  emitter.emit('show-match-grouped', true, gscenario.value)
  emitter.emit('show-match-grouping', false)
}
function createNewMatch () {
  tmnt.value.start_at = yyyymmddHHMM(new Date())
  console.log('-fn-createNewMatch with tmnt', tmnt.value)
  emitter.emit('open-TeamMatchCreator', tmnt.value, 'add')
}

//=== main ===
getAliases(13)
getAliases(14)
getHandicaps(13)
getHandicaps(14)
matchName.value = route.params.match
if (matchName.value === 'JZsMatch') {
  store.page = 'JZsMatch'
  gameId.value = 13
} else if (matchName.value === 'KJsMatch') {
  store.page = 'KJsMatch'
  store.pageTitle = 'KJsMatch'
  gameId.value = 14
  // getKjGameDates()
  if (kjGamePlayerList.length == 0) getKjGamePlayers()
} else if (matchName.value === 'ALsMatch') {
  store.page = 'ALsMatch'
  gameId.value = 16
}
console.log(`%c====== main ===== matchName: ${matchName.value}`, 'color:red;font-size:medium')
buildApp(matchName.value, 'Golf')
tmnt.value.game_id = gameId.value
getTournamentsByGameId()
userGuidePage.value = 'TeamMatch'
handicapFlag.value = gameId.value
</script>
