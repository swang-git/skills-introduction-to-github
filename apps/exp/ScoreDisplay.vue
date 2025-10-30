<template>
<q-dialog v-model="opened" position="top">
  <div class="bg-cyan-9 q-pa-xs" style="border:3px solid gold;border-radius:3%">
    <q-card class="text-center text-h6 text-white inset-shadow-down bg-teal-10" style="margin:-4px -4px 2px -4px">
      <div>{{ member.teetime }} from {{ member.teebox }} Tee </div>
      <div>at {{ member.course }} </div>
    </q-card>
    <div class="q-pl-sm">
      <table>
        <q-tr>
          <td v-for="i in [1, 2, 3, 4, 5]" :key=i.x>
            <HoleScoreButton :score="score['h' + i]" :hoidx="i" :pars="member.pars" />
          </td>
        </q-tr>
        <q-tr>
          <td v-for="i in [6, 7, 8, 9]" :key=i.x>
            <HoleScoreButton :score="score['h' + i]" :hoidx="i" :pars="member.pars" />
          </td>
          <td>
            <div :class="getFBClass(f9total)">{{ f9total }}</div>
          </td>
        </q-tr>
        <q-tr>
          <td v-for="i in [10, 11, 12, 13, 14]" :key=i.x>
            <HoleScoreButton :score="score['h' + i]" :hoidx="i" :pars="member.pars" />
          </td>
        </q-tr>
        <q-tr>
          <td v-for="i in [15, 16, 17, 18]" :key=i.x>
            <HoleScoreButton :score="score['h' + i]" :hoidx="i" :pars="member.pars" />
            </td>
          <td>
            <div :class="getFBClass(b9total)">{{ b9total }}</div>
          </td>
        </q-tr>
      </table>
    </div>
    <q-input class="noteh q-px-sm text-h6" type="textarea" label="Notes" row="3" v-model="member.note" value="member.note" dark @input="showUpdBtn=true" />
    <q-card class="bg-cyan-8 inset-shadow" style="margin:0px -3px 0 -3px">
      <q-card-actions align="between">
        <q-btn round size="lg" v-close-popup icon="chevron_left" color="amber" />
        <q-chip color="teal-9" class="text-h6 text-white" style="height:60px"> Total Strokes</q-chip>
        <!-- <q-btn round size="lg" disable color="teal-10" text-color="yellow">{{ f9total + b9total }}</q-btn> -->
        <div :class="getTotalClass(f9total + b9total)">{{ f9total + b9total }}</div>
      </q-card-actions>
    </q-card>
  </div>
</q-dialog>
<InfoDisplay />
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
import InfoDisplay from 'app/src/components/InfoDisplay'
import HoleScoreButton from './HoleScoreButton';
import { cssFunctions } from 'src/composables/cssFunctions'
const { getFBClass, getTotalClass } = cssFunctions()
var score = {}
var tmnt = {}
var scoreId = 0
var f9total = null
var b9total = null
var member = {}
var selectedHole = -1
var opened = ref(false)

console.log('-ST-ScoreDisplay')
emitter.on('open-ScoreDisplay', (x) => openIt(x))

function showHoleInfo (i) {
  console.log('-fn-showHoleInfo', member)
  const tit = 'Hole Information'
  const msg = 'Hole ' + i + ': par ' + member.pars['h' + i] + ', yards:' + member.yards.myards['y' + i] + ', handicap:' + member.hcaps['p' + i]
  // emitter.emit('open-InfoDialog', tit, msg)
  emitter.emit('open-InfoDisplay', tit, msg)
}
function updTotalScoreAndNote () {
  const path = process.env.API + '/golf/updTotalScoreAndNote'
  const inData = { scoreId:member.scoreId, note:member.note, totalScore:f9total + b9total }
  paxios(path)
  opened.value = false
}
function openIt (m, tt) {
  // console.log('-CK-fn-openIt for member', m)
  scoreId = m.id
  member = m
  tmnt = tt
  if (scoreId > 0) {
    for (var i = 1; i < 19; i++) {
      score['h' + i] = member['h' + i]
    }
    // console.info('-ck-score', this.score)
    f9total = member.front9
    b9total = member.back9
  } else {
    console.log('b9scores', b9scores)
    for (var k = 1; k < 10; k++) {
      score['h' + k] = 0
      score['h' + k + 9] = 0
    }
    f9total = null
    b9total = null
  }
  opened.value = true
}
</script>
