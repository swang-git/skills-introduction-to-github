<template>
<q-dialog v-model="opened" transition-show="rotate" transition-hide="jump-down" persistent>
  <div style="border:2px solid gold; border-radius:1%">
    <q-card style="margin-left:0px">
      <q-card-actions class="bg-cyan text-h6 inset-shadow-down cursor-pointer" align="between">
        <div><div class="inset-shadow-down flex flex-center inline shadow-box-hole" v-close-popup>{{ holeIdx }}</div> 洞 </div>
        <div :class="getTeamWonClass()" v-close-popup><span :style="teamWon!=0 ? { margin:'-15px 0 0 -1px' } : {margin:'0 0 0 -15px'}">{{ Math.abs(teamWon) }}</span>
          <div style="font-size:18px;margin:25px 0 0 -15px">{{ teamWon==0 ? '' : teamWon>0 ? 'A' : 'B' }}</div>
        </div>
        <div><span class="text-h6 q-px-xs">PAR</span><div class="flex flex-center inline shadow-box-hole bg-green-10" v-close-popup>{{ hole(holeIdx) }}</div></div>
      </q-card-actions>
      <q-card-actions class="bg-cyan text-h5 inset-shadow-down" align="between">
        <div><b class="q-px-xs">{{ yard(holeIdx) }}</b><span class="text-h6"> YARDS</span></div>
        <div><span class="text-h6 q-pr-xs">HANDICAP </span><b>{{ hcap(holeIdx) }}</b></div>
      </q-card-actions>
      <q-card-section class="bg-cyan-4">
        <div v-for="(pname, i) in scores.map(p => p.name)" :key="pname" class="row">
          <q-td><q-btn no-caps size="22px" round style="border:3px solid;margin-top:10px" :class="getStrokePadClass(getScore(i))" >{{ getScore(i) }}</q-btn></q-td>
          <q-td class="q-pt-md"><q-chip class="text-h5 text-white inset-shadow-down" :class="{'bg-blue-10':i<2, 'bg-red':i>1 }" v-close-popup>{{ pname }}</q-chip></q-td>
        </div>
      </q-card-section>
      <q-card-actions align="between" class="bg-cyan-6">
        <q-btn round glossy size="18px" color="indigo-10" @click="prevHole" icon="chevron_left" />
        <!-- <div :class="getTeamWonClass()" v-close-popup><span :style="teamWon==0 ? {} : { margin:'-15px 0 0 -1px' }">{{ Math.abs(teamWon) }}</span>
          <div style="font-size:18px;margin:25px 0 0 -15px">{{ teamWon==0 ? '' : teamWon>0 ? 'A' : 'B' }}</div>
        </div> -->
        <div :class="getWonClass()" v-close-popup>{{ Math.abs(won) }}</div>
        <q-btn round glossy size="18px" color="indigo-10" @click="nextHole" icon="chevron_right" />
      </q-card-actions>
    </q-card>
  </div>
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { cssFunctions } from 'src/composables/cssFunctions'
import { storeFunctions } from 'src/composables/storeFunctions'
const { getStrokePadClass } = cssFunctions()
const { hole, yard, hcap, calcHL, getScoreName } = storeFunctions()
const holeIdx = ref(null)
const scores = ref([])
const modeFB = ref('F')
const opened = ref(false)
// console.log('-ST-TeamGroupPointDetails')
emitter.on('open-TeamGroupPointDetails', (hidx, scs) => openIt(hidx, scs))

// computed session
const gstrokes = computed(() => { return scores.value.map(p => p['h' + holeIdx.value])})
const won = computed(() => { return calcHL(gstrokes.value, hole(holeIdx.value)) })
const teamWon = computed(() => {
  let twon = []
  for (let i = 1; i <= holeIdx.value; i++) {
    twon[i - 1] = calcHL(scores.value.map(p => p['h' + i]), hole(i))
  }
  console.log(`-CK-twon`, twon)
  return twon.reduce((a, b) => a + b, 0)
})
// function session
function switchModeFB () {
  if (holeIdx.value <= 9 && modeFB.value == 'B') {
    modeFB.value = 'F'
    emitter.emit('set-modeFB', 'F')
  } else if (holeIdx.value > 9 && modeFB.value == 'F') {
    modeFB.value = 'B'
    emitter.emit('set-modeFB', 'B')
  }
}
function openIt (hidx, scs, mode='F') {
  // console.log(`-fn-openIt-TeamGroupPointDetails hole=${hidx}`, scs)
  holeIdx.value = hidx
  scores.value = scs
  modeFB.value = mode
   switchModeFB () 
  // console.info(`-fnX-openIt-TeamGroupPointDetails hole=${holeIdx.value} won=${won.value}`, gstrokes.value)
  opened.value = true
  console.log(`-CK-fn-openIt hole=${holeIdx.value} teamWon=${teamWon.value} mode=${modeFB.value}`)
}
function prevHole () {
  holeIdx.value--
  if (holeIdx.value <= 0) holeIdx.value = 18
   switchModeFB () 
  console.log(`-CK-fn-prevHole hole=${holeIdx.value} teamWon=${teamWon.value}`)
}
function nextHole () {
  holeIdx.value++
  if (holeIdx.value > 18) holeIdx.value = 1
   switchModeFB () 
  console.log(`-CK-fn-nextHole hole=${holeIdx.value} teamWon=${teamWon.value}`)
}
function getScore (i) {
  return getScoreName(gstrokes.value[i], holeIdx.value)
}
function getWonClass () {
  let cls = 'inset-shadow-down flex flex-center inline shadow-box-hole'
  let clr = (won.value == 0 ? 'bg-cyan' : won.value < 0 ? 'bg-red' : 'bg-blue-10')
  return cls + ' ' + clr
}
function getTeamWonClass () {
  let cls = 'inset-shadow-down flex flex-center inline shadow-box-hole'
  let clr = (teamWon.value == 0 ? 'bg-cyan' : teamWon.value < 0 ? 'bg-red' : 'bg-blue-10')
  return cls + ' ' + clr
}
</script>
