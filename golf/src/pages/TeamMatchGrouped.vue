<template>
<div v-if="showMatchGrouped" class="justify-center">
  <q-card-actions v-if="matchDate" align="evenly" class="row q-py-xs text-cyan-1 text-h5 inset-shadow-down">
    <q-btn round glossy @click="showTeamScore=!showTeamScore"><q-icon name="G" color="cyan-2" style="margin:-8px 0 0 0" /></q-btn>
    <div class="text-center">{{ matchDate }} ({{ matchDate.chwk3() }})</div>
    <q-btn v-if="matchDate>=today()" glossy round icon="diversity_2" @click="doGrouping()"><b style="margin-top:-15px">{{ gsx }}</b></q-btn>
    <q-btn v-else flat round />
  </q-card-actions>

  <div class="row justify-center">
    <q-btn-group spread v-if="showTeamScore" style="border:2px solid lime">
      <q-btn size="18px" class="cursor-none inset-shadow-down q-px-lg" dense no-caps no-wrap align="left" color="blue-9">Team A {{ teamAScore }} </q-btn>
      <q-btn size="18px" class="cursor-none inset-shadow-down q-px-lg" dense no-caps no-wrap align="left" color="red" >Team B {{ teamBScore }} </q-btn>
    </q-btn-group>

    <div style="margin-bottom:10px" class="bg-teal-10">
      <div v-for="(gx, i) in groups" :key="gx" style="margin-left:-6px">
        <div class="row inset-shadow-down q-pl-sm" :class="getTeeColor(gx.tee)" style="width':screenwidth-10+'px';font-size:17.2px">
          <div class="cursor-pointer" @click="emit('change-tmnt', gx.tmntId, 'upd')">{{ gx.ttm }} Play {{ gx.teename }}</div>
          <div class="q-pl-xs ellipsis" :style="{'width':screenwidth/1.62 + 'px'}" @click="emit('course-info', gx.tmntId, gx.teename)">{{ gx.course }}</div>
        </div>
        <table style="border:1px solid green;margin-left:4px" :class="getBGcolor(i)">
          <tr v-for="(p, pi) in gx.players.sort((a,b) => { return a.team < b.team ? -1 : 1})" :key="p" class="text-h6 text-white">
            <td v-if="p.activity=='dobl'" :width="screenwidth/2" no-wrap :class="teamColor(p)" class="inset-shadow-down">
              <q-btn round icon="looks_two" text-color="yellow" />
              <span class="q-pl-sm cursor-pointer" @click="enterPStrokes(p, i)">{{ p.name }}</span>
            </td>
            <td v-else :width="screenwidth/2" no-wrap :class="teamColor(p)" class="inset-shadow-down">
              <q-icon :name="getNature(p.gender)" size="md" />
              <span class="q-pl- cursor-pointer" @click="enterPStrokes(p, i)">{{ p.name }}</span>
            </td>

            <td v-if="pi==0"><q-btn :size="21*screenwidth/457+'px'" class="text-bold" round color="blue-9" @click="openTeamGroupPointPad(p, gx, 'Team A')" :label="p.tscore" /></td>
            <td v-if="pi==1" rowspan="2"><q-btn class="text-white q-pb-sm" :size="21*screenwidth/457+'px'" color="accent" round icon="GS" @click="enterGStrokes(i)"/></td>
            <td v-if="pi==3"><q-btn :size="21*screenwidth/457+'px'" class="text-bold" round color="red-10" @click="openTeamGroupPointPad(p, gx, 'Team B')" :label="p.tscore" /></td>

            <td v-if="pi==0"><q-btn :size="21*screenwidth/457+'px'" class="text-black" round outline @click="openGolfScorePad(p, pi)" :label="p.pscore" /></td>
            <td v-if="pi==1"><q-btn :size="21*screenwidth/457+'px'" class="text-black" round outline @click="openGolfScorePad(p, pi)" :label="p.pscore" /></td>
            <td v-if="pi==2"><q-btn :size="21*screenwidth/457+'px'" class="text-black" round outline @click="openGolfScorePad(p, pi)" :label="p.pscore" /></td>
            <td v-if="pi==3"><q-btn :size="21*screenwidth/457+'px'" class="text-black" round outline @click="openGolfScorePad(p, pi)" :label="p.pscore" /></td>

            <td v-if="pi==0"><q-btn :class="getAvgColor(p.avg)" :size="21*screenwidth/457+'px'" @click="getPlayerGameScores(p)" round>{{ p.courseHandicap }}</q-btn></td>
            <td v-if="pi==1"><q-btn :class="getAvgColor(p.avg)" :size="21*screenwidth/457+'px'" @click="getPlayerGameScores(p)" round>{{ p.courseHandicap }}</q-btn></td>
            <td v-if="pi==2"><q-btn :class="getAvgColor(p.avg)" :size="21*screenwidth/457+'px'" @click="getPlayerGameScores(p)" round>{{ p.courseHandicap }}</q-btn></td>
            <td v-if="pi==3"><q-btn :class="getAvgColor(p.avg)" :size="21*screenwidth/457+'px'" @click="getPlayerGameScores(p)" round>{{ p.courseHandicap }}</q-btn></td>

            <td v-if="pi==0 && showAvgScore(p)"><q-btn :class="getAvgColor(p.avg_score)" @click="showGameData(p)" round>{{ p.avg_score }}</q-btn></td>
            <td v-if="pi==1 && showAvgScore(p)"><q-btn :class="getAvgColor(p.avg_score)" @click="showGameData(p)" round>{{ p.avg_score }}</q-btn></td>
            <td v-if="pi==2 && showAvgScore(p)"><q-btn :class="getAvgColor(p.avg_score)" @click="showGameData(p)" round>{{ p.avg_score }}</q-btn></td>
            <td v-if="pi==3 && showAvgScore(p)"><q-btn :class="getAvgColor(p.avg_score)" @click="showGameData(p)" round>{{ p.avg_score }}</q-btn></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <component :is="TeamMatchTeamScorePad" @set-team-score="setTeamScore" />
  <component :is="GolfScorePad" />
  <component :is="TeamMatchPlayDataPad" />
  <component :is="NewPlayerDialog" />
  <component :is="EnterPScoresDialog" @player-pscore="setPscore" />
  <component :is="TeamMatchGScoresDialog" @player-gscore="setGscore" @group-point="setGroupPoint" @course-info="showCourseInfo" />
  <TeamMatchKJGameDataPad />
</div>
</template>
<script setup>
// import { useQuasar } from 'quasar'
import emitter from 'tiny-emitter/instance'
import { ref, computed, onMounted } from "vue";
import { utilFunctions } from '../composables/utilFunctions'
import { axiosFunctions } from '../composables/axiosFunctions'
import { dayFunctions } from '../composables/dayFunctions'
import { libFunctions } from '../composables/libFunctions'
import { cssFunctions } from '../composables/cssFunctions'
import { storeFunctions } from '../composables/storeFunctions'

import TeamMatchKJGameDataPad from './TeamMatchKJGameDataPad'
import TeamMatchGScoresDialog  from './TeamMatchGScoresDialog'
import EnterPScoresDialog  from './EnterPScoresDialog'
// import TeamMatchCreator  from './TeamMatchCreator'
import TeamMatchTeamScorePad from './TeamMatchTeamScorePad'
import TeamMatchPlayDataPad from './TeamMatchPlayDataPad'
// import TeamMatchList from './TeamMatchList'
// import TeamMatchGrouping from './TeamMatchGrouping'
import NewPlayerDialog from 'src/components/NewPlayerDialog'
// import NewSimPlayerDialog from '../src/components/NewSimPlayerDialog'
import GolfScorePad from '../components/GolfScorePad'
// import Tooltip from '../src/components/Tooltip'

//== data section

const props = defineProps({
  gameId: { type: Number },
  // gscenario: { type: Number },
  games: { type: Array },
  matchDate: { type: String },
  groups: { type: Array },
  courseInfo: { type: Array },
})
const emit = defineEmits([
  'info-display',
  'course-info',
  'change-tmnt',
])
const gsx = ref(0)
// const $q = useQuasar()
const { store, SysAdmin, screenwidth } = libFunctions()
// const app = getCurrentInstance()
// const route = app.appContext.config.globalProperties.$route
// const store = app.appContext.config.globalProperties.$store
const { getInit } = utilFunctions()
const { gaxios, paxios} = axiosFunctions()
const { today } = dayFunctions()
const { teamColor, getTeeColor, getNature, getAvgColor } = cssFunctions()
const { slope, rating } = storeFunctions()
const groupingDone = ref(true)
const tmntId = ref(null)
// const matchName = ref(null)
const game = ref(null)
const player = ref({})
const year = ref(null)
const groupId = ref(-1)
const group = ref(null)
const courseId = ref(0)
const teeboxId = ref(0)
const teetime = ref(0)
// const refNewSimPlayerDialog = ref(null)

const showMatchGrouped = ref(false)
// const screen_height = $q.screen.height
// const screen_width = $q.screen.width
const showTeamScore = ref(false)

onMounted(() => {
  // refNewSimPlayerDialog
  // checkDeviceType()
})

// emitter.on section
// console.log(`-ST-TeamMatch matchName=${tmnt.value.id} iPhoneX=${iPhoneX} isDesk=${isDesk} JZsAdmin=${JZsAdmin.value}`, tmnt.value)
// console.log(`-CK-TeamMatch isDesk=${isDesk} iPhoneX=${iPhoneX} iPhone11ProMax=${iPhone13}`)
emitter.on('show-match-grouped', (x, y) => { showMatchGrouped.value = x; gsx.value = y })

emitter.on('golf-getGroupScores', (x) => { setGroupScores(x) })
emitter.on('golf-getPlayerGameScores', (x) => { setPlayerGameScores(x) })
emitter.on('group-point', (gi, gpt) => { setGroupPoint(gi, gpt) })
// emitter.on('golf-getKjAliases', (x) => { setKjAliases(x) })

// computed section
const teamAScoreX = computed(() => {
  let teamScore = 0
  let points = 0
  props.groups.forEach((g) => {
    if (g.players[0] !== undefined) {
      const score = parseInt(g.players[0].tscore)
      points += isNaN(score) ? 0 : score
      if (score > 0) teamScore += 1
    }
  })
  return [teamScore, points]
})
const teamBScoreX = computed(() => {
  let teamScore = 0
  let points = 0
  props.groups.forEach((g) => {
    if (g.players[3] !== undefined) {
      const score = parseInt(g.players[3].tscore)
      points += isNaN(score) ? 0 : score
      if (score > 0) teamScore += 1
    }
  })
  return [teamScore, points]
})
const teamAScore = computed(() => {
  // console.log(`-cp-team A scores=${teamAScoreX.value}, team B scores=${teamBScoreX.value}`)
  if (teamAScoreX.value[0] === 0 &&  teamBScoreX.value[0] === 0) return null
  else if (teamAScoreX.value[0]  >  teamBScoreX.value[0] && teamAScoreX.value[1] >=  teamBScoreX.value[1]) return 'Won (' + teamAScoreX.value[0] + ', ' + teamAScoreX.value[1] + ')'
  else if (teamAScoreX.value[0]  >  teamBScoreX.value[0] && teamAScoreX.value[1]  <  teamBScoreX.value[1]) return 'Tie (' + teamAScoreX.value[0] + ', ' + teamAScoreX.value[1] + ')'
  else if (teamAScoreX.value[0]  <  teamBScoreX.value[0] && teamAScoreX.value[1] <=  teamBScoreX.value[1]) return 'Lost(' + teamAScoreX.value[0] + ', ' + teamAScoreX.value[1] + ')'
  else if (teamAScoreX.value[0]  <  teamBScoreX.value[0] && teamAScoreX.value[1]  >  teamBScoreX.value[1]) return 'Tie (' + teamAScoreX.value[0] + ', ' + teamAScoreX.value[1] + ')'
  else if (teamAScoreX.value[0] === teamBScoreX.value[0] && teamAScoreX.value[1]  >  teamBScoreX.value[1]) return 'Won (' + teamAScoreX.value[0] + ', ' + teamAScoreX.value[1] + ')'
  else if (teamAScoreX.value[0] === teamBScoreX.value[0] && teamAScoreX.value[1]  <  teamBScoreX.value[1]) return 'Lost(' + teamAScoreX.value[0] + ', ' + teamAScoreX.value[1] + ')'
  else if (teamAScoreX.value[0] === teamBScoreX.value[0] && teamAScoreX.value[1] === teamBScoreX.value[1]) return 'Tie (' + teamAScoreX.value[0] + ', ' + teamAScoreX.value[1] + ')'
  return 'Not Determined(' + teamAScoreX.value[0] + ', ' + teamAScoreX.value[1] + ')'
})
const teamBScore = computed(() =>{
  if (teamAScoreX.value[0] === 0 && teamBScoreX.value[0] == 0) return null
  else if (teamBScoreX.value[0]  >  teamAScoreX.value[0] && teamBScoreX.value[1] >=  teamAScoreX.value[1]) return 'Won (' + teamBScoreX.value[0] + ', ' + teamBScoreX.value[1] + ')'
  else if (teamBScoreX.value[0]  >  teamAScoreX.value[0] && teamBScoreX.value[1]  <  teamAScoreX.value[1]) return 'Tie (' + teamBScoreX.value[0] + ', ' + teamBScoreX.value[1] + ')'
  else if (teamBScoreX.value[0]  <  teamAScoreX.value[0] && teamBScoreX.value[1] <=  teamAScoreX.value[1]) return 'Lost(' + teamBScoreX.value[0] + ', ' + teamBScoreX.value[1] + ')'
  else if (teamBScoreX.value[0]  <  teamAScoreX.value[0] && teamBScoreX.value[1]  >  teamAScoreX.value[1]) return 'Tie (' + teamBScoreX.value[0] + ', ' + teamBScoreX.value[1] + ')'
  else if (teamBScoreX.value[0] === teamAScoreX.value[0] && teamBScoreX.value[1]  >  teamAScoreX.value[1]) return 'Won (' + teamBScoreX.value[0] + ', ' + teamBScoreX.value[1] + ')'
  else if (teamBScoreX.value[0] === teamAScoreX.value[0] && teamBScoreX.value[1]  <  teamAScoreX.value[1]) return 'Lost(' + teamBScoreX.value[0] + ', ' + teamBScoreX.value[1] + ')'
  else if (teamBScoreX.value[0] === teamAScoreX.value[0] && teamBScoreX.value[1] === teamAScoreX.value[1]) return 'Tie (' + teamBScoreX.value[0] + ', ' + teamBScoreX.value[1] + ')'
  return 'Not Determined(' + teamBScoreX.value[0] + ',' + teamBScoreX.value[1] + ')'
})

// function section
function showCourseInfo (tmntId, gi) {
  console.log(`%c-fn-tmntId=${tmntId}, gi=${gi}`, 'color:red;font-size:14px', props.groups[gi])
  let course = props.courseInfo.find(c => c.tmntId = tmntId)
  let teename = props.groups[gi].teename
  emitter.emit('open-CourseInfo', course, teename)
}
function setGroupPoint(gi, gpt, groupPts=null) {
  console.log(`%c-CKs3-setGroupPoint-gi=${gi} gpt=${gpt}`, "font-size:16px;font-weight:600;color:red", groupPts)
  let groupi = props.groups[gi]
  let player1 = groupi.players[0]
  let player4 = groupi.players[3]
  player1.tscore = null
  player4.tscore = null
  if (gpt > 0)  player1.tscore = gpt
  else player4.tscore = Math.abs(gpt)
  updTeamMatchTplayer(player1)
  updTeamMatchTplayer(player4)
}

// function checkDeviceType () {
//   console.log('%c-CHECKING DEVICE TYPE', "font-size:10px;font-weight:600;color:red")
//   console.log(`%cHeight=${screen_height} Width=${screen_width}`, "font-size:10px;font-weight:600;color:red")
// }
// function groupingFinished () {
//   console.log(`-fn-groupingFinished`)
//   groupingDone.value = true
//   emitter.emit('show-match-grouped', true)
//   emitter.emit('show-match-grouping', false, gsx.value)
// }

function doGrouping () {
  console.log(`-fn-doGrouping`, props.groups)
  emitter.emit('do-grouping', props.gscenario, year.value, props.groups)
  groupingDone.value = false
  emitter.emit('show-match-grouping', true, gsx.value)
  emitter.emit('show-match-grouped', false)
}
// const getCourse = (gi) => { // a private function
//   console.log(`-fn-getCourse gi=${gi}`, props.games)
//   const gm = props.games[gi]
//   const courseId = gm.course_id
//   const teeboxId = gm.lteebox == null ? gm.mens_tee_id : gm.lady_tee_id
//   const x = props.courseInfo.find(p => p.courseId == courseId && p.teeboxId == teeboxId)
//   if (x != undefined) {
//     x.teeName = gm.lteebox == null ? gm.mteebox.teebox : gm.lteebox.teebox
//     x.name = gm.course.name
//   }
//   return x === undefined ? 'not_in_yet' : x
// }
function enterGStrokes (gi) {
  // if (props.matchDate < today()) {
  //   const tit = 'Expired Match'
  //   const msg = 'Can\'t change/enter stokes for expired match, please contact 胜利 for help.'
  //   emitter.emit('open-InfoDisplay', tit, msg)
  //   return
  // }
  group.value = props.groups[gi]
  game.value = props.games[gi]
  const cId = game.value.course_id
  const tId = game.value.mteebox.id
  const cInfo = props.courseInfo.find(p => p.courseId == cId && p.teeboxId == tId)
  console.log(`-fn-TeamMatchGStrokes-courseId=${cId} teeboxId=${tId} gi=${gi}`, game.value, group.value, props.courseInfo)
  groupId.value = gi
  store.holes = cInfo.pars
  store.yards = cInfo.yards
  store.hcaps = cInfo.hcaps
  store.slope = cInfo.slope
  store.rating = cInfo.rating
  store.yardage = cInfo.yardage
  store.par = cInfo.par
  courseId.value = cInfo.courseId
  teeboxId.value = cInfo.teeboxId
  teetime.value= game.value.start_at
  tmntId.value = game.value.id
  const path = process.env.API + '/golf/getGroupScores/' + tmntId.value + '/' + teetime.value + '/' + courseId.value + '/' + teeboxId.value
  gaxios(path)
  return
}
function enterPStrokes (p, i) {
  // if (props.matchDate < today()) {
  //   const tit = 'Expired Match'
  //   const msg = 'Can\'t change/enter stokes for expired match, please contact 胜利 for help.'
  //   emitter.emit('open-InfoDisplay', tit, msg)
  //   return
  // }
  game.value = props.games[i]
  player.value = p
  const scoreMeta = {}
  scoreMeta.name = p.name
  scoreMeta.teetime = game.value.start_at
  scoreMeta.course = game.value.course.name
  scoreMeta.teebox = game.value.mteebox.teebox
  scoreMeta.courseId = game.value.course_id
  scoreMeta.teeboxId = game.value.mteebox.id
  scoreMeta.tplayerId = p.id
  scoreMeta.playerId = p.player_id
  scoreMeta.tmntId = p.tmntId
  // scoreMeta.numGroups = numprops.Groups
  scoreMeta.groupId = i
  let cInfo = props.courseInfo.find(p => p.courseId == scoreMeta.courseId && p.teeboxId == scoreMeta.teeboxId)
  store.holes = cInfo.pars
  store.yards = cInfo.yards
  store.hcaps = cInfo.hcaps
  store.slope = cInfo.slope
  store.rating = cInfo.rating
  store.yardage = cInfo.yardage
  store.par = cInfo.par
  // store.commit('golf/setHoles', cInfo.pars)
  // store.commit('golf/setYards', cInfo.yards)
  // store.commit('golf/setHcaps', cInfo.hcaps)
  // store.commit('golf/setSlope', cInfo.slope)
  // store.commit('golf/setRating', cInfo.rating)
  // store.commit('golf/setYardage', cInfo.yardage)
  // store.commit('golf/par', cInfo.par)
  scoreMeta.slope = slope.value
  scoreMeta.rating = rating.value
  console.log(`-fn-enterPStrokes will open EnterPScoresDialog slope=${scoreMeta.slope}`, game.value, scoreMeta, props.courseInfo)
  emitter.emit('open-EnterPScoresDialog', scoreMeta)
}
function setGroupScores (da) {
  console.log(`-setGroupScores scores.length=${da.scores.length}`, da.scores)
  const scores = da.scores
  const pInfo = group.value.players.map(p => {
    const s = scores.find(x => x.player_id==p.player_id)
    return { inits:getInit(p.name), playerId:p.player_id, id:s==undefined ? 0 : s.id, name:p.name, tplayerId:p.id }
  })
  console.log(`-setGroupScores pInfo.length=${pInfo.length}`, pInfo)
  emitter.emit('open-TeamMatchGScoresDialog', group.value.grp, tmntId.value, courseId.value, teeboxId.value, teetime.value, pInfo, scores)
}
function getPlayerGameScores (p) {
  console.log('-fn-getPlayerGameScores for', p)
  player.value = p
  const path = process.env.API + '/golf/getPlayerGameScores/' + p.player_id + '/' + props.gameId
  gaxios(path)
}
function setPlayerGameScores(da) {
  console.log('-fn-setPlayersGameScores', da)
  const gscores = da.lst
  const back_n_days = -da.back_n_days.double_value
  console.log(`-ab-getPlayerGameScores back_n_days(Not Used)=${back_n_days}`)
  emitter.emit('open-TeamMatchPlayDataPad', gscores, props.gameId, player.value, back_n_days)
}
function setPscore (x) { player.value.pscore = x }
function setGscore (pid, pscore, hole18=0) {
  let player = group.value.players.find(p => p.player_id === pid)
  console.log(`-CKQ- setGScore-${pid}-${pscore}, ${hole18}`, player)
  player.pscore = pscore
}
function setTeamScore (player, teamScore, grpx=null) {
  console.log(`-fn-setTeamScore grpx=${grpx} teamScore=${teamScore} player=`, player, props.groups)
  if (player == null) {
    const pi = teamScore > 0 ? 0 : 3
    const g = props.groups[grpx-1]
    if (g.players.length != 4) return
    player = g.players[pi]
    const player1 = g.players[pi==0 ? 3 : 0]
    player1.tscore = null
    updTeamMatchTplayer(player1) //upd team score
  }
  player.tscore = teamScore == 0 ? null : teamScore == null ? null : Math.abs(teamScore)
  updTeamMatchTplayer(player) //upd team score
}

// function changeTmnt(tmntId, act) {
//   // console.log(`-fn-changeTmnt tmntId=${tmntId} act=${act} gameId=${props.gameId} JZsAdmin=${JZsAdmin.value} KJsAdmin=${KJsAdmin.value} ALsAdmin=${ALsAdmin.value}`)
//   console.log(`JZ/KJ/AL/SY_sAdmin=${!(JZsAdmin.value || KJsAdmin.value || ALsAdmin.value)}`)
//   if (props.matchDate < today() && !(JZsAdmin.value || KJsAdmin.value || ALsAdmin.value)) {
//     console.log('-fn-changeTmnt Can not del/upd expired')
//     const tit = 'Expired Game'
//     const msg = 'Expired Game could not be revised'
//     emitter.emit('open-InfoDisplay', tit, msg)
//     return
//   } else if (!(JZsAdmin.value || KJsAdmin.value || ALsAdmin)) {
//     const tit = 'Revision not Permitted'
//     const msg = 'Please contact 胜利 for help'
//     emitter.emit('open-InfoDisplay', tit, msg)
//     return
//   }
//   const g = props.games.find(p => p.id === tmntId)
//   console.log(`-update tmnt`, g)
//   const tmnt = {
//     id: tmntId,
//     course_id: g.course_id,
//     courseName: g.course.name,
//     start_at: g.start_at.substring(0, 16),
//     game: matchName.value,
//     game_id: g.game_id,
//     teetime_gap: g.teetime_gap,
//     numGroup: 1,
//     fees: parseFloat(g.fees),
//     note: g.note === null ? 'tournamentId: ' + tmntId : g.note,
//     mtee_id: g.mens_tee_id,
//     mtee: g.mteebox.teebox + ' ~ ' + g.mteebox.rating + ' ' + g.mteebox.slope + ' ' + g.mteebox.yardage,
//     ltee_id: g.lady_tee_id,
//     ltee: g.lteebox === null ? null : g.lteebox.teebox + ' ~ ' + g.lteebox.rating + ' ' + g.lteebox.slope + ' ' + g.lteebox.yardage,
//   }
//   console.log('-fn-addUpdTmn-tmnt', tmnt)
//   emitter.emit('open-TeamMatchCreator', tmnt, act)
// }
function openTeamGroupPointPad(player, group, team) {
  if (props.matchDate < today() && !SysAdmin.value) {
    const tit = 'Expired Match'
    const msg = 'Can\'t change/enter group point for expired match, please contact 胜利 for help.'
    // emitter.emit('open-info-dialog', true)
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  console.log('-fn-openTeamGroupPointPad for set team score for ' + team, group)
  const otherteam = team === 'Team B' ? 'Team A' : 'Team B'
  if (group.players[team === 'Team A' ? 3 : 0].tscore != null|0) {
    const tit = 'Team Score in the Group'
    const msg = 'To set ' + team + '\'s score, please delete ' + otherteam + '\'s score, then enter ' + team  + '\'s score (only one team can have score in the group)'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  emitter.emit('open-TeamMatchTeamScorePad', player)
}
function updTeamMatchTplayer (player) {
  const path = process.env.API + '/golf/updTeamMatchTplayer'
  paxios(path, player)
}
function getBGcolor (i) {
  const numPlayersInGroup = props.groups[i].players.length
  if (numPlayersInGroup > 4) {
    // props.groups[i].note = 'There are more than 4 players in the group'
    return 'bg-red'
  } else if (i % 2 === 0 && numPlayersInGroup === 4) {
    return 'bg-green-4'
  } else if (numPlayersInGroup < 4) {
    return 'bg-yellow-5'
  } else {
    return 'bg-green-6'
  }
}
function showAvgScore (p) { //gameId == 14 -- KJMatch
  return p.avg_score > 0 && props.gameId == 14
}
function openGolfScorePad (p) {
  console.log('-fn-openGolfScorePad', p)
  if (props.matchDate < today() && !SysAdmin.value) {
  const tit = 'Expired Match'
    const msg = 'Can\'t change/enter player scores for expired match, please contact 胜利 for help'
    emitter.emit('open-InfoDisplay', tit, msg)
    return
  }
  emitter.emit('open-GolfScorePad', p)
}
</script>
