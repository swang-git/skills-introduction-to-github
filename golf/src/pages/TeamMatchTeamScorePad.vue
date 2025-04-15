<template>
<q-dialog v-model="opened">
  <q-card class="bg-teal-9 q-pa-xs">
    <div class="q-pa-xs bg-teal-10 text-h6 text-cyan-1 text-center">{{ NoteForScore }}</div>
    <table class="bg-teal-8">
      <q-tr><td v-for="i in padNum.slice(1,  5)" :key=i.x><q-btn size="lg" color="teal-10" round @click="setTeamScore(i)">{{i}}</q-btn></td></q-tr>
      <q-tr><td v-for="i in padNum.slice(5,  9)" :key=i.x><q-btn size="lg" color="teal-10" round @click="setTeamScore(i)">{{i}}</q-btn></td></q-tr>
      <q-tr><td v-for="i in padNum.slice(9, 13)" :key=i.x><q-btn size="lg" color="teal-10" round @click="setTeamScore(i)">{{i}}</q-btn></td></q-tr>
      <q-tr><td v-for="i in padNum.slice(13,17)" :key=i.x><q-btn size="lg" color="teal-10" round @click="setTeamScore(i)">{{i}}</q-btn></td></q-tr>
      <q-tr>
        <td><q-btn round color="amber"  size="lg" class="text-pink"  icon="chevron_left" v-close-popup /></td>
        <td><q-btn round color="teal-10" size="lg" class="text-green" @click="togglePadNum()" icon="indeterminate_check_box" /></td>
        <td><q-btn round color="teal-10" size="lg" class="text-amber" icon="save" v-close-popup /></td>
        <td><q-btn round color="amber"  size="lg" class="text-pink"  @click="setTeamScore(null)" icon="delete" /></td>
      </q-tr>
    </table>
  </q-card>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
// import { libFunctions } from '../composables/libFunctions.js'
// const { isDesk, isIM } = libFunctions()
const emit = defineEmits(['set-team-score'])
const padNum = ref([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16])
// const score = ref({})
// const holeIdx = ref(0)
const NoteForScore = ref('')
// const topPos = '10px'
const player = ref(null)
const opened = ref(false)
console.log('-ST-TeamMatchTeamScorePad')
emitter.on('open-TeamMatchTeamScorePad', (x) => openIt(x))
function togglePadNum () {
  padNum.value = padNum.value.map(p => -p)
}
// function getHeight () {
//   return isDesk ? '25vh' : isIM ? '40vh' : '160vh'
// }
function openIt (p) {
  NoteForScore.value = 'Team' + (p.team.indexOf('A')>=0 ? 'A' : 'B')  + ' in Group' + p.grp + ' Score'
  player.value = p
  console.log('-fn-openIt', p)
  opened.value = true
}
function setTeamScore (tscore) {
  if (tscore === 0) tscore = null
  player.value.tscore = tscore
  console.log('-fn-setTeamScore for player', player.value, tscore)
  emit('set-team-score', player.value, tscore)
  opened.value = false
}
</script>
