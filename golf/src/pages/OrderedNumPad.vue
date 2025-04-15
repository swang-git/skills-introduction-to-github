<template>
<div>
  <q-dialog v-model="opened" minimized :style="{ position:'absolute', top: topPos }">
      <q-toolbar :color="titlebarColor">
        <q-btn round dense color="yellow-9" v-close-popup icon="keyboard_arrow_left" />
        <q-toolbar-title style="font-size:20px"> Score for {{ groupOfTeam }} </q-toolbar-title>
      </q-toolbar>
      <table style="y-overflow:auto;margin:auto">
          <q-tr><q-td v-for="i in [1, 2, 3, 4]" :key=i.x><q-btn size="xl" :color="getColor(i)" style="width:60px;height:60px" @click="setScore(i)">{{i}}</q-btn></q-td></q-tr>
          <q-tr><q-td v-for="i in [5, 6, 7, 8]" :key=i.x><q-btn size="xl" :color="getColor(i)" style="width:60px;height:60px" @click="setScore(i)">{{i}}</q-btn></q-td></q-tr>
          <q-tr><q-td v-for="i in [9,10,11]" :key=i.x><q-btn size="xl" :color="getColor(i)" style="width:60px;height:60px" @click="setScore(i)">{{i}}</q-btn></q-td>
          <q-btn class="num-pad-score" @click="setScore(0)"><td>0</td></q-btn></q-tr>
      </table>
  </q-dialog>
</div>
</template>
<script setup>
import { ref } from 'vue'
import { libFunctions } from 'src/composables/libFunctions'
const { store } = libFunctions()
const score = ref({})
const holeIdx = 0
const groupOfTeam = ''
const titlebarColor = 'red'
const topPos ='10px'
const opened = ref(false)

console.log('-ST-OrderedNumPad')
// function getPosition () {
//   return holeIdx < 10 ? 'bottom' : 'top'
// }
// function getHeight () {
//   return isDesk ? '25vh' : isIM ? '40vh' : '160vh'
// }
function getColor (i) {
  let color = 'teal-9'
  const holes = store.holes
  if (i === holes['h' + holeIdx]) color = 'green'
  return color
}
// function openIt (idx, sco) {
//   holeIdx = idx
//   let team = 'Blue'
//   let group = 'Group'
//   if (idx % 2 === 1) {
//     titlebarColor = 'primary'
//     if (idx === 1) group += '1'
//     else if (idx === 5) group += '2'
//     else if (idx === 9) group += '3'
//     else if (idx === 13) group += '4'
//     groupOfTeam = group + ' Of ' + team
//   } else {
//     team = 'Red'
//     titlebarColor = 'red'
//     if (idx === 4) group += '1'
//     else if (idx === 8) group += '2'
//     else if (idx === 12) group += '3'
//     else if (idx === 16) group += '4'
//     groupOfTeam = group + ' Of ' + team
//   }
//   score.value = sco
//   console.log('-CK-member pad for hole', holeIdx, score)
//   if (idx <= 3) topPos = isIM ? '45px' : '-55px'
//   else if (idx <= 6) topPos = isIM ? '100px' : '10px'
//   else if (idx <= 9) topPos = '150px'
//   else if (idx <= 12) topPos = '-140px'
//   else if (idx <= 15) topPos = '-75px'
//   else if (idx <= 18) topPos = '-10px'
//   opened.value = true
// }
function holeName (i) {
  return 'h' + i
}
const emit = defineEmits(['set-score'])
function setScore (sco) {
  // console.log('score for hole', this.idx, 'is', score)
  emit('set-score', holeIdx - 1, sco)
  score[holeName(holeIdx)] = sco
  // console.log('score for hole', this.holeNum, this.hole.scores[this.idx])
  opened.value = false
}
</script>
<style>
.num-pad-score {
  width: 60px;
  height: 60px;
  font-size: 25px;
  background: red;
}
</style>
