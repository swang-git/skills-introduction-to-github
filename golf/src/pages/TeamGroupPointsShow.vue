<template>
<q-dialog v-model="opened" position="top" :full-height="isIM" persistent>
<div :style="isDesk ? { width:'250px' }: { width:'100px' }" class="bg-teal">
  <div style="height:55px" class="flex justify-center cursor-pointer" v-close-popup>
    <q-card class="bg-cyan-8 inset-shadow-down">
      <div class="text-h5 text-white q-pl-sm">
        <b class="q-pt-md q-pl-xs">前九</b><div class="inset-shadow-down flex flex-center inline" :class="getClassF9()">{{ f9>0 ? f9 : -f9 }}</div>
        <b class="q-pt-md q-pl-sm">后九</b><div class="inset-shadow-down flex flex-center inline" :class="getClassB9()">{{ b9>0 ? b9 : -b9 }}</div>
      </div>
    </q-card>
  </div>
  <div class="row q-pa-xs justify-center">
    <div style="width:60px" v-close-popup>
      <td v-for="i in [1,2,3,4,5,6,7,8,9].slice(0, hp.length)" :key=i class="inset-shadow-down flex flex-center inline shadow-box-hole cursor-pointer">{{ i }}</td>
    </div>
    <div style="width:60px">
      <td v-for="i in [0,1,2,3,4,5,6,7,8].slice(0, hp.length)" :key=i class="inset-shadow-down flex flex-center inline cursor-pointer" :class="getClass(i)" @click="showTeamGroupPointDetails(i)">
        {{ hp[i]>0 ? hp[i] : hp[i]==undefined  ? 0 : -hp[i] }}
      </td>
    </div>
    <div v-if="hp.length>9" style="width:60px" v-close-popup>
      <td v-for="i in [10,11,12,13,14,15,16,17,18]" :key=i class="inset-shadow-down flex flex-center inline shadow-box-hole cursor-pointer">{{ i }}</td>
    </div>
    <div v-if="hp.length>9" style="width:60px">
      <td v-for="i in [9,10,11,12,13,14,15,16,17]" :key=i class="inset-shadow-down flex flex-center inline cursor-pointer" :class="getClass(i)" @click="showTeamGroupPointDetails(i)">
        {{ hp[i]>0 ? hp[i] : hp[i]==undefined ? 0 : -hp[i] }}
        <!-- <q-tooltip class="text-h6 bg-accent">{{ teamscores[i] }}</q-tooltip> -->
      </td>
    </div>
  </div>
</div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { storeFunctions } from 'src/composables/storeFunctions';
import { libFunctions } from 'src/composables/libFunctions';
const opened = ref(false)
const f9 = ref(null)
const b9 = ref(null)
const hp = ref([])
const scores = ref([])
const teamscores = ref([])
// const pInfo = ref([])
console.log('-ST-TeamGroupPointsShow')
emitter.on('open-TeamGroupPointsShow', (gx, scores) => { openIt(gx, scores) })
const { holes, calcHL } = storeFunctions()
const { isIM, isDesk } = libFunctions()

function showTeamGroupPointDetails (i) {
  if (teamscores.value[i] == undefined) {
    return
  }
  emitter.emit('set-modeFB', 'F')
  emitter.emit('open-TeamGroupPointDetails', i + 1, scores.value)
}
function openIt (gx, scs) {
  hp.value = []
  scores.value = scs
  // pInfo.value = pinfo
  calcTeamPoints()
  // console.log(`-openIt-TeamGroupPointsShow.vue hp.length=${hp.value.length} hp=`, hp.value, 'scores=', teamscores.value)
  const everyF9isNull = hp.value.slice(0,  9).every(p => p == null)
  const everyB9isNull = hp.value.slice(9, 18).every(p => p == null)
  f9.value = everyF9isNull ? null : hp.value.slice(0,  9).reduce((a, b) => a + b, 0)
  b9.value = everyB9isNull ? null : hp.value.slice(9, 18).reduce((a, b) => a + b, 0)
  const groupPoint = (f9.value == null && b9.value == null) ?  null : f9.value == null ? b9.value : b9.value == null ? f9.value : f9.value + b9.value
  // if (groupPoint != null) emitter.emit('team-score', null, groupPoint, gx)
  if (groupPoint != null) emitter.emit('group-point', gx-1, groupPoint)
  // console.log(`-CK-f9=${f9.value} b9=${b9.value} groupPoint=${groupPoint} grpx=${gx}`)
  opened.value = true
}
function calcTeamPoints () {
  teamscores.value = []
  for (let i=1; i<=18; ++i) {
    const holescores = scores.value.map(p => p['h' + i])
    if ( Math.max(...holescores) === 0 && Math.min(...holescores) === 0) teamscores.value.push(null)
    else teamscores.value.push(holescores)
  }
  for (let i=0; i<teamscores.value.length; ++i) {
    const tms = teamscores.value[i]
    const par = holes.value['h' + (i + 1)]
    hp.value.push(calcHL(tms, par))
  }
  // console.log('-holepoints-', holepoints.value, points)
}
function getClass (i) {
  return hp.value[i] > 0 ? 'shadow-box-blue' : hp.value[i] < 0 ? 'shadow-box-red' : hp.value[i]==undefined ? 'shadow-box-ggrey' : 'shadow-box-cyan'
}
function getClassF9 () {
  return f9.value > 0 ? 'shadow-box-blue' : f9.value < 0 ? 'shadow-box-red' : f9.value===0 ? 'shadow-box-cyan' : 'shadow-box-ggrey' 
}
function getClassB9 () {
  return b9.value > 0 ? 'shadow-box-blue' : b9.value < 0 ? 'shadow-box-red' : b9.value===0 ? 'shadow-box-cyan' : 'shadow-box-ggrey' 
}
</script>
