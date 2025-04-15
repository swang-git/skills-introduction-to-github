<template>
<q-dialog v-model="opened" position="top" persistent maximized>
<div :style="{'width':screenwidth+'px'}" class="bg-cyan-9">
  <div style="height:100px" class="bg-cyan-9">
    <q-card-actions class="flex justify-evenly">
      <div v-for="i in [0,1,2,3]" :key="i">
        <transition v-if="pIdx==i" appear enter-active-class="animated bounceIn" style="animation-duration:5s;animation-delay:0.0s">
          <div class="inset-shadow-down flex flex-center shadow-box-grey text-white cursor-pointer" 
            :class="{ 'bg-blue-9':pIdx<2 && pIdx>=0,'bg-red':pIdx>1,'bg-grey':pIdx<0 || pIdx>3 }" v-close-popup>
            <span>{{ pInfo[i] == undefined ? 'X' : pInfo[i].inits }}</span>
          </div>
        </transition>
      </div>
      <div v-close-popup>
        <q-chip color="amber-10" class="text-h6 text-white cursor-pointer" style="height:50px">{{ teetime.substring(5, 11) }} Group {{ grpx }}</q-chip>
      </div>
      <transition appear enter-active-class="animated rotateIn" style="animation-duration:5s;animation-delay:0.0s">
        <div class="inset-shadow-down flex flex-center inline shadow-box-hole" @click="emit('course-info', tmntId, grpx-1)"
          :class="{'bg-blue-10':gptsum>0,'bg-red':gptsum<0,'bg-cyan':gptsum==0}">{{ Math.abs(gptsum) }}</div>
      </transition>
      <div class="cursor-pointer inset-shadow-down flex flex-center shadow-box-grey text-white" 
        :class="{'bg-blue-9':pIdx<2, 'bg-red':pIdx>1}" @click="modeFB=(modeFB=='F' ? 'B' : 'F')">{{ modeFB }}
      </div>
    </q-card-actions>
  </div>
  <div style="margin-top:-31px;border:2px lime solid">
    <div v-for="(pi, x) in pInfo" :key=pi class="q-px-xs q-pb-xs" :class="getBGcolor(x)">
      <div class="row" :class="getNameFBTClass(x)" style="margin:0 -4px 0 -4px" @click="showTeamGroupPoints()">
        <transition v-if="pIdx==x" appear enter-active-class="animated lightSpeedInLeft" style="animation-duration:3s;animation-delay:0.0s">
          <div style="width:200px" >{{ pi.name }}</div>
        </transition>
        <transition v-else>
          <div style="width:200px" >{{ pi.name }}</div>
        </transition>
        <q-btn v-if="x==1 && gptblu>0" size="11px" round outline><span class="text-h6">{{ gptblu }}</span></q-btn>
        <q-btn v-else-if="x==2 && gptred>0" size=11px round outline><span class="text-h6">{{ gptred }}</span></q-btn>
        <div>{{ getFront9(pi) }}</div><div>{{ getBack9(pi) }}</div><div>{{ getTotal(pi) }}</div>
      </div>
      <div class="row q-pt-xs justify-between">
        <div v-for="i in modeFB=='F' ? compHole1_9 : compHole10_18" :key=i>
          <HoleScoreButtonG :score="getScore(pi, i)" :hoidx="i" :holeIdx="holeIdx" :pIdx="x" :clickedIdx="clickedIdx" @click="openStrokePad(pi, i, x)" />
          <div v-if="x==0" :class="{'_holeF':modeFB=='F', '_holeB':modeFB=='B'}">{{ i }}</div>
          <div v-if="x==1" class="_pars" :style="{marginLeft:isMate ? '-1.5px' : ''}">{{ hole(i) }}</div>
          <div v-if="x==2" :class="{_hcap1:hcap(i)<10, _hcap2:hcap(i)>9}" :style="{marginLeft:isMate ? '-1.5px' : ''}">{{ hcap(i) }}</div>
          <div v-if="x==3" class="text-h6 q-pl-xs text-white" style="padding:3px 0 0 5px">{{ yard(i) }}</div>
        </div>
      </div>
    </div>
    <div class="row text-white text-h6 bg-teal-9 justify-between no-wrap">
      <div v-for="i in modeFB=='F' ? compHole1_9 : compHole10_18" :key=i>
        <q-btn size="13px" v-if="gpoint(i)==null" round color="grey-6"></q-btn>
        <q-btn size="13px" v-else round glossy :color="gpoint(i)>0 ? 'blue-10' : gpoint(i)<0 ? 'red' : 'cyan'" @click="showTeamGroupPointDetails(i)">
          <b class="text-h6">{{ Math.abs(gpoint(i)) }}</b>
        </q-btn>
      </div>
    </div>
  </div>
</div>
</q-dialog>
<component :is="EnterGStrokePad" @set-score="setScore" @set-init="setPinit" />
<component :is="TeamGroupPointsShow" />
<!-- <TeamGroupPointDetails /> -->
</template>

<script setup>
import { ref, computed, markRaw } from 'vue'
import emitter from 'tiny-emitter/instance'
import EnterGStrokePad from 'pages/EnterGStrokePad'
import TeamGroupPointsShow from 'pages/TeamGroupPointsShow'
// import TeamGroupPointDetails from 'pages/TeamGroupPointDetails'
import HoleScoreButtonG from 'src/components/HoleScoreButtonG'
import { dayFunctions } from 'src/composables/dayFunctions'
import { libFunctions } from 'src/composables/libFunctions'
import { storeFunctions } from 'src/composables/storeFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
const { paxios } = axiosFunctions()
const { hole, holes, yard, hcap, par, slope, rating, yardage, calcHL } = storeFunctions()
const { iPhoneX, iPhone13, screenwidth, iPhone11ProMax, mate60ProMax, isDesk, JZsAdmin, SysAdmin, isMate } = libFunctions()
const { today } = dayFunctions()

const emit = defineEmits(['player-gscore', 'group-point', 'course-info'])
emitter.on('open-TeamMatchGScoresDialog', (gx, tid, cId, teeId, teetm, pinfo, scr) => openIt(gx, tid, cId, teeId, teetm, pinfo, scr))

const clickedIdx = ref(0) 
const pIdx = ref(0) 
const modeFB = ref('F')
const opened = ref(false)
const teetime = ref(null)
const grpx = ref(null)
const tmntId = ref(null)
const pInfo = ref({})
const scores = ref([])
const holeIdx = ref(null)
const playerId = ref(0)
const courseId = ref(0)
const teeboxId = ref(null)
const year = ref(null)
const tplayerId = ref(null)
const pname = ref(null)
const front9 = ref(null)
const back9 = ref(null)
const oscore = ref(null)
const totalscore = ref(null)
const enter_score_sign = ref(null)
const isShowTeamPoints = ref(false)
const groupPoints = ref([18])
const gptsum = ref(0)
const gptblu = ref(0)
const gptred = ref(0)

// console.log('-ST-TeamMatchGScoresDialog')
emitter.on('golf-insGScore', (x) => setScoreId(x.scoreId))
emitter.on('set-modeFB', (x) => modeFB.value = x)
enter_score_sign.value = null
// const scores = computed(() => { return scores.value })
const compGptsum = computed(() => { console.log(`%cGPTsum=${gptsum.value}`, "color:red;font-size:40px");return gptsum.value })
const compGptblu = computed(() => { return gptblu.value })
const compGptred = computed(() => { return gptred.value })
const compHole1_9 = computed(() => { return [1, 2, 3, 4, 5, 6, 7, 8, 9] })
const compHole1_5 = computed(() => { return [1, 2, 3, 4, 5] })
const compHole6_9 = computed(() => { return [6, 7, 8, 9] })
const compHole10_18 = computed(() => { return [10, 11, 12, 13, 14, 15, 16, 17, 18] })
const compHole10_14 = computed(() => { return [10, 11, 12, 13, 14] })
const compHole15_18 = computed(() => { return [15, 16, 17, 18] })
// const compGroupPoints = computed(() => { return groupPoints.value })

function gpoint (i) {
  // console.log(`-fn-gpoint(${i})`)
  if (scores.value.length < 4) return null
  // let ABscores = [scores.value[0][hs], scores.value[1][hs], scores.value[2][hs], scores.value[3][hs]]
  let ABscores = scores.value.map(p => p['h' + i])
  let incompletScore = ABscores.find(p => p == 0)
  if (incompletScore == 0) {
    groupPoints.value[i-1] = null
    return null
  }
  // console.log(`ABcores(${i})`, ABscores)
  let gpt =  calcHL(ABscores, hole(i))
  groupPoints.value[i-1] = gpt
  if (modeFB.value == 'F') gptsum.value = groupPoints.value.slice(0,9).reduce((a, b) => a + b, 0)
  else if (modeFB.value == 'B') gptsum.value = groupPoints.value.reduce((a, b) => a + b, 0)
  if (gptsum.value > 0) {
    gptblu.value = gptsum.value
    gptred.value = null
  } else if (gptsum.value < 0) {
    gptred.value = -gptsum.value
    gptblu.value = null
  } else {
    gptred.value = null
    gptblu.value = null
  }
  return gpt
}
function showTeamGroupPointDetails (i) {
  // let pnames = scores.value.map(p => p.name)
  // let pscores = []
  // for (let j = 1; j <= 18; j++) pscores[i] = scores.value.map(p => p['h' + j])
  // scores.value.map((p, j) => pscores[j] = p['h' + j])
  // emitter.emit('open-TeamGroupPointDetails', i, scores.value, modeFB.value)
  emitter.emit('open-TeamGroupPointDetails', i, scores.value)
  console.log(`open-TeamGroupPointDetails hole=${i} modeFB=${modeFB.value}`, scores.value, groupPoints.value)
}
function setPinit (x) {
  x > 3 ? pIdx.value = -1 : pIdx.value = x
}
function getNameFBTClass (i) {
  return 'inset-shadow-down q-pa-sm text-h5 text-white justify-between cursor-pointer ' + (i <= 1 ? 'bg-blue-10' : 'bg-red-7')
}
function showTeamGroupPoints () {
  // console.log(`%c-fn-showTeamGroupPoints`, 'color:red;font-size:14px;')
  emitter.emit('open-TeamGroupPointsShow', grpx.value, scores.value)
}
function getBGcolor (x) {
  if (x%2 == 0) return 'bg-teal-8'
  else if (x%2 == 1) return 'bg-teal-10'
}
function openIt (gx, tid, cId, teeId, teetm, pinfo, scr) {
  // console.log(`%c-CK-openIt-GSscoreDialog-grpx=${gx} tmntId=${tid} courseId=${cId} teeboxId=${teeId} teetime=${teetm} pInfo=`, 'color:pink;font-size:18px', pinfo, 'scores=', scr)
  pInfo.value = pinfo
  scores.value = scr
  tmntId.value = tid
  courseId.value = cId
  teeboxId.value = teeId
  teetime.value = teetm
  year.value = teetm.substring(0, 4)
  grpx.value = gx
  opened.value = true
  if (holeIdx.value > 9) modeFB.value = 'B'
}
function getFront9 (pi) {
  const pid = pi.playerId
  const x = scores.value.find(p => p.player_id === pid)
  if (x == undefined) return 0
  let fx = 0
  for (let i = 1; i <= 9; i++) fx += (x['h' + i] > 0 ? x['h' + i] : 0)
  return fx
}
function getBack9 (pi) {
  const pid = pi.playerId
  const x = scores.value.find(p => p.player_id === pid)
  if (x == undefined) return 0
  let fx = 0
  for (let i = 10; i <= 18; i++) {
    const txp = x['h' + i]
    fx += txp > 0 ? txp : 0
  }
  return fx
}
function getTotal (pi) {
  return getFront9(pi) + getBack9(pi)
}
function getScore(pi, i) {
  if (enter_score_sign.value != null) { 
    enter_score_sign.value = null
    return enter_score_sign.value
  }
  const pid = pi.playerId
  const x = scores.value.find(p => p.player_id === pid)
  return x == undefined ? 0 : x['h' + i]
}
function openStrokePad (pi, idx, pidx) {
  console.log(`-fn-openStrokePad holeIdx=${idx} pIdx=${pidx} teetime=${teetime.value} SysAdmin=${SysAdmin.value}`, today())
  if (teetime.value < today() && !SysAdmin.value) {
    const tit = 'Expired Game'
    const msg = 'Scores can not be revised. Ask 胜利 for help.'
    emitter.emit('open-InfoDisplay', tit, msg)
    return 
  }
  pIdx.value = pidx
  getScore(pi, idx)
  holeIdx.value = idx % 18 + 1
  tplayerId.value = pi.tplayerId
  pname.value = pi.name
  const pid = pi.playerId
  const x = scores.value.find(p => p.player_id === pid)
  oscore.value = x == undefined ? 0 : x['h' + idx]
  emitter.emit('open-EnterGStrokePad', idx, pInfo.value, pidx)
  // console.log('XXX for', pid, 'for hole', idx, 'click pos', event.clientX, event.clientY, oscore.value)
}
function setScoreId (scoreId) {
  let x = scores.value.find(p => p.player_id == playerId.value)
  if (x == undefined) x = {}
  x.id = scoreId
  // console.log(`-CK-setScoreId playerId=${playerId.value}, scoreId=${scoreId}`, scores.value)
}
function setScore (idx, score, pid, tpid, pname) {
  playerId.value = pid
  let x = scores.value.find(p => p.player_id == pid)
  console.log(`-CKX-setScore idx=${idx} score=${score} pid=${pid} tpid=${tpid} pname=${pname}`, scores.value, x)
  if (x == undefined) {
    x = initialize(idx, score, pid, tpid, pname)
    scores.value.push(x)
    x.id = 0
    // console.log('-ck-setScore', scores.value)
  } else {
    oscore.value = x['h' + idx]
    if (oscore.value === 99) oscore.value = 0
    x['h' + idx] = score
    const fb9score = score - (oscore.value == undefined ? 0 : oscore.value)
    if (idx <= 9) x.front9 += fb9score 
    else x.back9 += fb9score
    x.totalscore = x.front9 + x.back9
    // console.log(`-CHK- front9=${x.front9} back9=${x.back9} totalscore=${x.totalscore}`)
    // console.warn(`-ck- front9=${x.front9} back9=${x.back9} totalscore=${x.totalscore}`, x)
    x.tournament_id = tmntId.value
    x.slope = slope.value
    x.rating = rating.value
    x.teetime = teetime.value
    x.tplayerId = tpid
    x.name = pname
    x.year = year.value
  }
  const hs = scores.value.map(p => { return p['h' + idx] })
  pIdx.value++
  // if (pIdx.value >= pInfo.value.length) {
  //   pIdx.value = 0
  //   setHoleScoreEnd.value = true
  // } else {
  //   setHoleScoreEnd.value = false
  // }
  const path = process.env.API + (x.id == 0 ? '/golf/insGScore' : '/golf/updGScore')
  x.tplayerId = tpid
  paxios(path, x)
  // console.log(`%c-CKs-setScore-${playerId.value} x.id=${x.id} ${x['h' + idx]} pIdx=${pIdx.value}`, 'color:red;font-size:16px') 
  emit('player-gscore', playerId.value,  x.totalscore, x.h18)
  clickedIdx.value++
  if (holeIdx.value > 9) modeFB.value = 'B'
  // if (pIdx.value == 4) {
    gpoint(idx)
    // console.log(`%c-CKs1-setScore-${playerId.value} x.id=${x.id} ${x['h' + idx]} pIdx=${pIdx.value}`, 'color:red;font-size:16px', groupPoints.value) 
    setGroupPoint()
  // }
}
function setGroupPoint() {
  console.log(`%c-CKs2-getGroupPoints-${playerId.value} gi=${grpx.value} pIdx=${pIdx.value}`, 'color:red;font-size:16px', groupPoints.value) 
  let gpt = groupPoints.value.filter(p => p != null).reduce((a, b) => a + b, 0)
  emit('group-point', grpx.value - 1, gpt, groupPoints.value)
}
function initialize (idx, score, pid, tpid, pname) {
  console.log(`-fn-initialize-pname=${pname}`)
  const x = {
    id: 0,
    player_id: pid,
    tournament_id: tmntId.value,
    course_id: courseId.value,
    teebox_id: teeboxId.value,
    teetime: teetime.value,
    slope: slope.value,
    rating: rating.value,
    teetime: teetime.value,
    front9: front9.value,
    back9: back9.value,
    totalscore: totalscore.value,
    year: year.value,
    name: pname,
    tplayerId: tpid,
    h1: 0,
    h2: 0,
    h3: 0,
    h4: 0,
    h5: 0,
    h6: 0,
    h7: 0,
    h8: 0,
    h9: 0,
    h10: 0,
    h11: 0,
    h12: 0,
    h13: 0,
    h14: 0,
    h15: 0,
    h16: 0,
    h17: 0,
    h18: 0,
  }
  x['h' + idx] = score
  x.front9 = 0
  x.back9 = 0
  if (idx <= 9) x.front9 = score
  else x.back9 = score
  x.totalscore = score
  return x
}
</script>
