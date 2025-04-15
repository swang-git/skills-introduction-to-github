<template>
<q-dialog v-model="opened" maximized>
<div :style="{width:screenwidth + 'px'}">
  <div class="bg-cyan-8 q-pa-xs" style="border:3px solid gold;border-radius:0%;height:595px;margin-top:63px">
    <div class="bg-cyan-9 inset-shadow-down" style="margin:-3px -3px 3px -3px">
      <q-toolbar-title class="text-white text-center text-h6">
        <div>{{ member.teetime }} from {{ member.teebox }} </div>
        <div>at {{ member.course }} </div>
        <div class="text-center q-pa-xs text-h6 text-white">{{ member.name }}'s Score Details</div>
      </q-toolbar-title>
    </div>
    <div class="q-mx-md">
      <table>
        <q-tr>
          <td v-for="i in [1, 2, 3, 4, 5]" :key=i class="q-px-xs">
            <HoleScoreButton :score="getScore(i)" :hoidx="i" :clickedIdx="clickedIdx" :pars="holes" @click="openStrokePad(i, 0)" />
          </td>
        </q-tr>
        <q-tr>
          <td v-for="i in [6, 7, 8, 9]" :key=i>
            <HoleScoreButton :score="getScore(i)" :hoidx="i" :clickedIdx="clickedIdx" :pars="holes" @click="openStrokePad(i, 0)" />
          </td>
          <td>
            <div :class="getFBClass(member.front9)">{{ member.front9 }}</div>
          </td>
        </q-tr>
        <q-tr>
          <td v-for="i in [10, 11, 12, 13, 14]" :key=i>
            <HoleScoreButton :score="getScore(i)" :hoidx="i" :clickedIdx="clickedIdx" :pars="holes" @click="openStrokePad(i, 0)" />
          </td>
        </q-tr>
        <q-tr>
          <td v-for="i in [15, 16, 17, 18]" :key=i>
            <HoleScoreButton :score="getScore(i)" :hoidx="i" :clickedIdx="clickedIdx"  :pars="holes" @click="openStrokePad(i, 0)" />
          </td>
          <td>
            <div :class="getFBClass(member.back9)">{{ member.back9 }}</div>
          </td>
        </q-tr>
      </table>
    </div>
    <q-input class="q-pa-md text-h6" type="textarea" rows="2" standout dark label="NOTES" v-model="member.note" value="member.note" text-shadow />
    <q-card class="bg-cyan-8 inset-shadow" style="margin:-4px -3px 0px -3px">
      <q-card-actions align="between" v-close-popup>
        <q-btn round size="20px" icon="chevron_left" glossy color="amber-9" />
        <q-btn  v-if="numGroups===1" round glossy color="teal-10" size="lg" no-caps @click="doubleBack9()">Dobl</q-btn>
        <q-chip v-if="numGroups===1" color="teal-9" class="text-h5 text-white cursor-pointer" style="height:60px;width:99px"> Total </q-chip>
        <q-chip v-else color="teal-9" class="text-h5 text-white cursor-pointer" style="height:60px;width:180px"> Total Strokes </q-chip>
          <div :class="getTotalClass()" @click="updNotes()">{{ member.totalscore }}
          <q-tooltip v-if="member.note!=null" class="text-h5 bg-red-10">Click to update notes</q-tooltip>
          </div>
      </q-card-actions>
    </q-card>
  </div>
  <component :is="EnterPStrokePad" @set-score="setScore"/>
</div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { libFunctions } from 'src/composables/libFunctions'
import { axiosFunctions } from 'src/composables/axiosFunctions'
import { cssFunctions } from 'src/composables/cssFunctions'
import { storeFunctions } from 'src/composables/storeFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import emitter from 'tiny-emitter/instance'
import EnterPStrokePad from 'pages/EnterPStrokePad'
import HoleScoreButton from 'src/components/HoleScoreButton'
const emit = defineEmits(['double-back9', 'set-pscore', 'open-InfoDisplay', 'open-EnterPStrokePad', 'player-pscore'])
const $q = useQuasar()
const { today } = dayFunctions()
const { screenwidth, JZsAdmin, SysAdmin } = libFunctions()
const { paxios, gaxios } = axiosFunctions()
const { getFBClass } = cssFunctions()
const { holes } = storeFunctions()
const clickedIdx = ref(0)
const score = ref({})
const groups = ref(null)
const numGroups = ref(null)
const groupId = ref(null)
const scoreMeta = ref({})
const member = ref({})
const opened = ref(false)

defineExpose({openIt})

console.log('-ST-EnterPScoresDialog')

emitter.on('open-EnterPScoresDialog', (sMeta, tmnt) => openIt(sMeta, tmnt))
emitter.on('golf-getPStrokes', (x) => setPStrokes(x))
emitter.on('golf-insGScore', (x) => setScoreId(x))
emitter.on('golf-doubleBack9', (x) => emit('double-back9', x))

const doubleBack9 = () => {
  const pid = member.value.playerId
  const tid = member.value.tournamentId
  console.log(`-CK-fn-doubleBack9 pid=${pid} tid=${tid}`, member.value)
  const path = process.env.API + '/golf/doubleBack9/' + pid + '/' + tid
  gaxios(path)
}
function getTotalClass () {
  let clx = 'inset-shadow-down flex flex-center cursor-pointer'
  if (member.value.totalscore > 79 || member.value.totalscore == 0) clx += ' shadow-box-60-grey'
  else if (member.value.totalscore <= 79) clx += ' shadow-box-60-red'
  return clx
}
function updNotes () {
  console.log(`-fn-updNotes for ${member.value.name} ${member.value.note}, ${member.value.id}`, member)
  if (member.value.id > 0) {
    const path = process.env.API + '/golf/updGScore'
    paxios(path, member.value)
  } else {
    const tit = 'No Score yet'
    const msg = '(no score id) enter score first'
    // currentTab.value = 'InfoDisplay'
    emitter.emit('open-InfoDisplay', tit, msg)
  }
}
function setScoreId (da) {
  console.log(`-fn-setScoreid scoreId=${da.scoreId}`)
  // scoreId.value = da.scoreId
  member.value.id = da.scoreId
}
function getScore (i) {
  return score.value['h' + i]
}
function openStrokePad (idx) {
  clickedIdx.value = idx
  console.log(`-fn-openStrokePad idx=${clickedIdx.value}`, member.value.teetime < today(), JZsAdmin.value)
  if (member.value.teetime < today() && !SysAdmin.value) {
    const tit = 'Expired Game'
    const msg = 'Cannot be revised. Ask 胜利 for help.'
    emitter.emit('open-InfoDisplay', tit, msg, true)
    return
  }
  emitter.emit('open-EnterPStrokePad', idx)
}
function cpScoreMeta () {
  member.value.name = scoreMeta.value.name
  member.value.course = scoreMeta.value.course
  member.value.course_id = scoreMeta.value.courseId
  member.value.teebox = scoreMeta.value.teebox
  member.value.teebox_id = scoreMeta.value.teeboxId
  member.value.teetime = scoreMeta.value.teetime.substring(0, 16)
  member.value.tournament_id = scoreMeta.value.tmntId
  member.value.player_id = scoreMeta.value.playerId
  member.value.tplayerId = scoreMeta.value.tplayerId
  member.value.slope = scoreMeta.value.slope
  member.value.rating = scoreMeta.value.rating
  groupId.value = scoreMeta.value.groupId
  groups.value = scoreMeta.value.groups
  console.log('-ck-holes', holes.value)
}
function setPStrokes (da) {
  console.log(`-fn-setPStrokes`, da)
  if (da.status === 'OK') {
    member.value = da.strokes
    member.value.id = da.strokes.id
    cpScoreMeta()
    // score.value = score = Object.keys(da.strokes).filter(key => /h\d/.test(key)).reduce((cur, key) => { return Object.assign(cur, { [key]: da.strokes[key] })}, {})
    score.value = Object.keys(da.strokes).filter(key => /h\d/.test(key)).reduce((cur, key) => { return Object.assign(cur, { [key]: da.strokes[key] })}, {})
    console.log(`-fn-setPStrokes-score`, score.value)
  } else if (da.status === 'more_than_one_score') {
    $q.dialog({
      title:'Something Wrong',
      message:`There are more than one score for the teetime=${scoreMeta.value.teetime}. Check your query data for score, like playerId, courseId, teetime.`
    })
    opened.value = false
  } else if (da.status === 'no_score') {
    member.value.id = 0
    console.log(`setPStrokes no score, enter strokes`)
    member.value.front9 = 0
    member.value.back9 = 0
    member.value.totalscore = 0
    score.value = { h1:0,h2:0,h3:0,h4:0,h5:0,h6:0,h7:0,h8:0,h9:0,h10:0,h11:0,h12:0,h13:0,h14:0,h15:0,h16:0,h17:0,h18:0 }
    member.value = { h1:0,h2:0,h3:0,h4:0,h5:0,h6:0,h7:0,h8:0,h9:0,h10:0,h11:0,h12:0,h13:0,h14:0,h15:0,h16:0,h17:0,h18:0 }
    cpScoreMeta()
    member.value.id = 0
  }
  return
}
function getPStrokes () {
  const path = process.env.API + '/golf/getPStrokes'
  paxios(path, scoreMeta.value)
}
function openIt (sMeta, tmnt) {
  scoreMeta.value = sMeta // { playerId:scoreMetaId, courseId:courseId, teetime:teetime, tournamentId }
  numGroups.value = sMeta.numGroups
  opened.value = true
  console.log('-fn-openIt sMeta', sMeta, 'holes', holes)
  if (tmnt === 'updScore') {
    score.value = Object.keys(sMeta.scores).filter(key => /h\d/.test(key)).reduce((cur, key) => { return Object.assign(cur, { [key]: sMeta.scores[key] })}, {})
    scoreMeta.value = sMeta
    member.value.id = sMeta.scoreId
    member.value.front9 = sMeta.front9
    member.value.back9 = sMeta.back9
    member.value.totalscore = member.value.front9 + member.value.back9
    cpScoreMeta()
  } else {
    getPStrokes()
  }
}
function setScore (idx, scr) {
  clickedIdx.value++
  console.log(`-fn-setScore idx=${idx} scoreId=${member.value.id}`, score)
  score.value['h' + idx] = scr   // updating frontend
  member.value['h' + idx] = scr  // updating backend
  if (member.value.id === 0) {  // new score case
    member.value.front9 = 0
    member.value.back9 = 0
    if (idx <=9) member.value.front9 = scr
    else member.value.back9 = scr
    member.value.totalscore = member.value.front9 + member.value.back9
    return submitScore()
  }
  const vals = Object.values(score.value)
  if (idx <= 9) {
    let front9 = 0
    for (let i=0; i<9; i++) {
      const p = vals[i]
      if (p == null) continue
      front9 += p
    }
    member.value.front9 = front9
    console.log(`-setScore-front9=${member.value.front9}`, vals)
  } else if (idx > 9) {
    let back9 = 0
    for (let i=9; i<vals.length; i++) {
      const p = vals[i]
      if (p == null) continue
      back9 += p
    }
    member.value.back9 = back9
    console.log(`-setScore-back9=${member.value.back9}`, vals)
  }
  member.value.totalscore = member.value.front9 + member.value.back9
  return submitScore()
}
function submitScore () {
  const path = process.env.API + (member.value.id > 0 ? '/golf/updGScore' : '/golf/insGScore')
  console.log(`-fn-upd/insGScore path=${path} member`, member.value)
  paxios(path, member.value)
  emit('player-pscore', member.value.totalscore)
}
</script>
