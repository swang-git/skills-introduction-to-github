<template>
<q-dialog v-model="opened">
  <div class="bg-amber q-pa-xs" style="width:240px;margin:200px 0 0 0;border-radius:40px">
    <q-card class="text-h6 bg-teal-10" style="border-radius:40px">
      <q-card-actions class="justify-center text-white">
        <span class="text-body1 text-grey-2"> {{ player.name }}'s Score {{ player.pscore }} </span>
      </q-card-actions>
      <q-card-actions class="justify-center">
        <q-btn round class="q-ma-xs" color="red-9" size="lg" icon="delete" @click="setPScore('delete')" />
        <q-btn round class="q-ma-xs" color="cyan-9" size="lg" @click="setPScore(0)"> 0 </q-btn>
        <q-btn round class="q-ma-xs" color="amber" size="lg" icon="chevron_right" @click="setPScore('exit')" />
        <q-btn v-for="i in [7,4,1]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setPScore(i)">{{ i }}</q-btn>
        <q-btn v-for="i in [8,5,2]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setPScore(i)">{{ i }}</q-btn>
        <q-btn v-for="i in [9,6,3]" :key=i class="q-ma-xs" size="lg" color="cyan-9" round @click="setPScore(i)">{{ i }}</q-btn>
      </q-card-actions>
    </q-card>
  </div>
  <!-- <component :is="InfoDisplay" /> -->
</q-dialog>
</template>

<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { axiosFunctions } from 'src/composables/axiosFunctions'
// import InfoDisplay from '../src/components/InfoDisplay' // parent import this already
const { paxios } = axiosFunctions()
console.info('-ST-GolfScorePad')
emitter.on('open-GolfScorePad', (x) => openIt(x))
const opened = ref(false)
const player = ref(null)
const origScore = ref(null)
function openIt (p) {
  console.info('-fn-openIt(player)', p)
  player.value = p
  origScore.value = p.pscore
  player.value.pscore = ''
  opened.value = true
}
function setPScore (i) {
  console.log(`-fn-setPScore user enter ${i}`)
  if (i === 'exit') {
    player.value.pscore = origScore.value
    opened.value = false
    // if (player.value.pscore < 58) {
    //   const tit = 'Score ( ' + player.value.pscore + ' ) is too low ?'
    //   const msg = 'Mininum score is ' + '<b class="text-amber-10">58</b>' + ',  Please re-enter the score between 58 and 127'
    //   emitter.emit('open-InfoDisplay', tit, msg)
    // }
    // player.value.pscore = origScore.value
  } else if (i === 'delete') {
    player.value.pscore = null
  } else {
    player.value.pscore += '' + i
    player.value.pscore = parseInt(player.value.pscore)
  }
  console.info('-fn-setPScore score', player.value.pscore)
  if (player.value.pscore > 127) {
    const tit = 'Score ( ' + player.value.pscore + ' ) is too high ?'
    const msg = 'Max score is ' + '<b class="text-amber-10">127</b>' + ',  Please re-enter the score between 58 and 127'
    emitter.emit('open-InfoDisplay', tit, msg)
    player.value.pscore = ''
  // } else if (player.value.pscore < 58) {
  //   const tit = 'Score ( ' + player.value.pscore + ' ) is too low ?'
  //   const msg = 'Mininum score is ' + '<b class="text-amber-10">58</b>' + ',  Please re-enter the score between 58 and 127'
  //   emitter.emit('open-InfoDisplay', tit, msg)
  //   player.value.pscore = ''
  } else if (player.value.pscore >= 58 && player.value.pscore <= 127 || player.value.pscore == null) {
    console.log(`-fn-setPScore psocre = ${player.value.pscore}`)
    const path = process.env.API + '/golf/saveTplayerScore'
    paxios(path, player.value)
    opened.value = false
  }
}
</script>