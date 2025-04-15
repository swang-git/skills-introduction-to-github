<template>
<q-dialog v-model="opened" class="desk" :maximized="isIM" transition-show="rotate" transition-hide="flip-up">
  <div>
    <q-toolbar class="bg-teal-9 text-white">
      <q-btn glossy round color="orange" v-close-popup icon="keyboard_arrow_left" />
      <q-toolbar-title> Score List for {{ player }} </q-toolbar-title>
      <q-input dark borderless v-model="searchQuery" input-class="text-right text-h6" class="absolute-bottom-right" dense @keyup="search()">
        <template v-slot:append>
          <q-icon v-if="searchQuery===''" name="search" class="q-pb-sm q-pr-sm" />
          <q-icon v-else name="clear" class="cursor-pointer" @click="searchQuery=''" />
        </template>
      </q-input>
    </q-toolbar>
    <q-card v-for="(s, i) in compScores" :key="s.x" class="q-pa-sm q-gutter-xs text-h6" :class="{ 'bg-cyan-9': i%2===1, 'bg-cyan-10': i%2===0 }" bordered dark flat>
      <div style="margin:0px">
        <q-btn flat text-color="amber" color="cyan-9" class="text-center" style="width:370px" no-caps @click="showDetails(i)">
          <div class="text-body1 no-wrap ellipsis"> {{ s.teetime }} {{ s.teebox }} tee {{ s.course }} <q-tooltip class="text-body1 bg-amber-9">show scores hole by hole</q-tooltip></div>
        </q-btn>
        <div style="margin:6px 5px 6px 9px" v-if="ishow===i">
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h1, s.pars.h1)" :outline="isOutline(s.h1, s.pars.h1)" :color="getColor(s.h1, s.pars.h1)">
            {{ s.h1 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h1}}</q-tooltip></q-btn></q-td>
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h2, s.pars.h2)" :outline="isOutline(s.h2, s.pars.h2)" :color="getColor(s.h2, s.pars.h2)">
            {{ s.h2 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h2}}</q-tooltip> </q-btn></q-td>
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h3, s.pars.h3)" :outline="isOutline(s.h3, s.pars.h3)" :color="getColor(s.h3, s.pars.h3)">
            {{ s.h3 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h3}}</q-tooltip> </q-btn></q-td>
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h4, s.pars.h4)" :outline="isOutline(s.h4, s.pars.h4)" :color="getColor(s.h4, s.pars.h4)">
            {{ s.h4 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h4}}</q-tooltip> </q-btn></q-td>
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h5, s.pars.h5)" :outline="isOutline(s.h5, s.pars.h5)" :color="getColor(s.h5, s.pars.h5)">
            {{ s.h5 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h5}}</q-tooltip> </q-btn></q-td>
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h6, s.pars.h6)" :outline="isOutline(s.h6, s.pars.h6)" :color="getColor(s.h6, s.pars.h6)">
            {{ s.h6 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h6}}</q-tooltip> </q-btn></q-td>
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h7, s.pars.h7)" :outline="isOutline(s.h7, s.pars.h7)" :color="getColor(s.h7, s.pars.h7)">
            {{ s.h7 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h7}}</q-tooltip> </q-btn></q-td>
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h8, s.pars.h8)" :outline="isOutline(s.h8, s.pars.h8)" :color="getColor(s.h8, s.pars.h8)">
            {{ s.h8 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h8}}</q-tooltip> </q-btn></q-td>
          <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h9, s.pars.h9)" :outline="isOutline(s.h9, s.pars.h9)" :color="getColor(s.h9, s.pars.h9)">
            {{ s.h9 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h9}}</q-tooltip> </q-btn></q-td>
        </div>
      </div>
      <div style="margin:6px 5px 6px 9px" v-if="ishow===i">
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h10, s.pars.h10)" :outline="isOutline(s.h10, s.pars.h10)" :color="getColor(s.h10, s.pars.h10)">
          {{ s.h10 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h10}}</q-tooltip> </q-btn></q-td>
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h11, s.pars.h11)" :outline="isOutline(s.h11, s.pars.h11)" :color="getColor(s.h11, s.pars.h11)">
          {{ s.h11 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h11}}</q-tooltip> </q-btn></q-td>
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h12, s.pars.h12)" :outline="isOutline(s.h12, s.pars.h12)" :color="getColor(s.h12, s.pars.h12)">
          {{ s.h12 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h12}}</q-tooltip> </q-btn></q-td>
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h13, s.pars.h13)" :outline="isOutline(s.h13, s.pars.h13)" :color="getColor(s.h13, s.pars.h13)">
          {{ s.h13 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h13}}</q-tooltip> </q-btn></q-td>
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h14, s.pars.h14)" :outline="isOutline(s.h14, s.pars.h14)" :color="getColor(s.h14, s.pars.h14)">
          {{ s.h14 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h14}}</q-tooltip> </q-btn></q-td>
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h15, s.pars.h15)" :outline="isOutline(s.h15, s.pars.h15)" :color="getColor(s.h15, s.pars.h15)">
          {{ s.h15 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h15}}</q-tooltip> </q-btn></q-td>
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h16, s.pars.h16)" :outline="isOutline(s.h16, s.pars.h16)" :color="getColor(s.h16, s.pars.h16)">
          {{ s.h16 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h16}}</q-tooltip> </q-btn></q-td>
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h17, s.pars.h17)" :outline="isOutline(s.h17, s.pars.h17)" :color="getColor(s.h17, s.pars.h17)">
          {{ s.h17 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h17}}</q-tooltip> </q-btn></q-td>
        <q-td><q-btn :size="isIM ? '12.9px' : '14px'" text-color="white" :round="getRound(s.h18, s.pars.h18)" :outline="isOutline(s.h18, s.pars.h18)" :color="getColor(s.h18, s.pars.h18)">
          {{ s.h18 }}<q-tooltip class="bg-cyan-10 text-body1">par {{s.pars.h18}}</q-tooltip> </q-btn></q-td>
      </div>
      <div style="margin:10px 0 4px 4px">
        <q-btn outline round text-color="white" color="green-8" :label="s.front9" v-close-popup><q-tooltip class="bg-amber-9 text-body1">front 9 score</q-tooltip></q-btn>&nbsp;
        <q-btn outline round text-color="white" color="green-9" :label="s.back9"><q-tooltip class="bg-amber-9 text-body1">back 9 score</q-tooltip></q-btn>&nbsp;
        <q-btn outline round text-color="white" color="blue-10" :label="s.front9 + s.back9"><q-tooltip class="bg-cyan-8 text-body1">total score</q-tooltip></q-btn>&nbsp;
        <q-btn outline round text-color="white" color="green-9" :label="parseFloat(s.hdcpdiff).toFixed(1)"><q-tooltip class="bg-cyan-10 text-body1">handicap differential</q-tooltip></q-btn>&nbsp;
        <q-btn color="primary" glossy label="Update Scores" class="text-body1" no-caps @click="updScores(s)" style="float:right"/>
      </div>
      <q-separator />
      <q-separator />
    </q-card>
    <EnterPScoresDialog ref="refEnterPScoreDialog" />
  </div>
</q-dialog>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from '../composables/libFunctions'
const { isIM, $q, store } = libFunctions()

import EnterPScoresDialog from 'pages/EnterPScoresDialog'

var scores = ref([])
var searchQuery = ref('')
const ishow = null
// const withDefaults = []
var playerId = -1
var player = ''
const opened = ref(false)
const refEnterPScoreDialog = ref(false)

defineExpose({openIt})
onMounted(() => { refEnterPScoreDialog })

console.log('-ST-ScoreList')
emitter.on('search', (x) => { searchQuery.value = x })

const compScores = computed(() =>{
  var filterKey = searchQuery.value.length > 0 && searchQuery.value.toLowerCase()
  var data = scores.value
  if (filterKey.length > 0) {
    var words = filterKey.split(' ')
    words.forEach(word => {
      data = data.filter(row => {
        return Object.keys(row).filter(key => { return !['id', 'catsId', 'subcId', 'payeId', 'paymId'].includes(key) }).some(key => {
          return String(row[key]).toLowerCase().indexOf(word) >= 0
        })
      })
    })
  }
  return data
})

function search () {
  console.log('-CK-fn-search', searchQuery)
}
function showDetails (i) {
  ishow === i ? null : i
}
function updScores (scores) {
  console.log('-CK-fn-updScore for playerId', playerId, scores)
  const p = {}
  p.tmntId = null
  p.playerId = playerId
  p.teeboxId = scores.teeboxId
  p.rating = scores.rating
  p.slope = scores.slope
  p.scoreId = scores.id
  p.courseId = scores.courseId
  p.course = scores.course
  p.teetime = scores.teetime
  p.note = scores.note
  // p.f9scores = [scores.h1, scores.h2, scores.h3, scores.h4, scores.h5, scores.h6, scores.h7, scores.h8, scores.h9]
  // p.b9scores = [scores.h10, scores.h11, scores.h12, scores.h13, scores.h14, scores.h15, scores.h16, scores.h17, scores.h18]
  p.scores = scores
  p.front9 = scores.front9
  p.back9 = scores.back9
  p.teebox = scores.teebox
  store.holes = scores.pars
  store.hcaps = scores.hcaps
  store.yards = scores.yards
  store.slope = scores.slope
  store.rating = scores.rating
  store.yardage = scores.yardage
  p.note = scores.note
  p.name = scores.name
  p.pars = scores.pars
  refEnterPScoreDialog.value.openIt(p, 'updScore', 'hole-mark1', 'hole-mark2')
}
// function checkIt (a, b) {
//   console.log('-CK-checking score and par', a, b)
// }
function getRound () {
  return true
}
// function isOutline (score, par) {
//   return outline(score, par)
// }
function openIt (sco, pId) {
  console.log(`-CK-fn-ScoreList-openIt scores player=${sco[0].name} playerId=${pId}`)
  if (sco.length <= 0) {
    $q.dialog({ title: 'There are no play records for you at this point.' })
    return
  }
  scores.value = sco
  playerId = pId
  player = sco[0].name
  opened.value = true
}
</script>
<style>
.desk {
  width: 200px;
}
.fone {
  background: lightcyan;
}
.txp {
  background: darkblue;
  font-size: 18px;
}
</style>
