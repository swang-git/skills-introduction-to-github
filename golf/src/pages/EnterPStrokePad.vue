<template>
<q-dialog v-model="opened">
  <div style="border:3px solid gold; border-radius:1%;height:230px;margin-top:330px" class="fixed q-py-sm bg-cyan-9">
    <q-card class="bg-cyan-9 flex flex-center inset-shadow-down" v-close-popup style="width:310px;margin:-7px 0px 5px 0px">
      <div><div class="inset-shadow-down flex flex-center shadow-box-par">{{ hole(holeIdx) }}</div></div>
      <div class="q-pt-xs q-px-xs text-white text-h5">{{ yard(holeIdx) }} Yards</div>
      <div class="q-pt-xs q-px-xs"><div class="text-h4 inset-shadow-down flex flex-center shadow-box-hcap" v-close-popup>{{ hcap(holeIdx) }}</div></div>
      <div v-for="i in Array.from({length:18}, (_, index)=>index + 1)" :key="i">
        <transition v-if="holeIdx===i" appear enter-active-class="animated flip" style="animation-duration:5s;animation-delay:0.0s">
          <div class="text-h4 inset-shadow-down flex flex-center shadow-box-hole">{{ holeIdx }}</div>
        </transition>
      </div>
    </q-card>
    <table>
      <q-tr><q-td v-for="i in ['bird', 'par', 'bogy', 'dobl']" :key=i.x><q-btn no-caps size="24px" round :class="getStrokePadClass(i)" @click="setScore(i)">{{i}}</q-btn></q-td></q-tr>
      <q-tr><q-td v-for="i in ['eagl','trpl', 'quad', 'dpar']" :key=i.x><q-btn no-caps size="24px" round :class="getStrokePadClass(i)" @click="setScore(i)">{{i}}</q-btn></q-td></q-tr>
    </table>
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
// import { libFunctions } from 'src/composables/libFunctions'
import { cssFunctions } from 'src/composables/cssFunctions'
import { storeFunctions } from 'src/composables/storeFunctions'
const emit = defineEmits(['set-score'])
// const { isIM, SysAdmin } = libFunctions()
const { getStrokePadClass } = cssFunctions()
const { hole,hcap,yard,getScore } = storeFunctions()
const holeIdx = ref(-1)
// const pInfo = ref(null)
const opened = ref(false)

emitter.on('open-EnterPStrokePad', (x) => { openIt(x)})

console.log(`-ST-EnterPStrokePad`)
function openIt (idx) {
  console.log(`openIt-holeIdx=${idx}`)
  holeIdx.value = idx
  opened.value = true
}
function setScore (scoreName) {
  // console.log(`-fn-setScore par=${hole(holeIdx.value)} sname=${scoreName} pIdx=${pIdx.value} init=${pInfo[pIdx.value].inits}`)
  // console.log(`-fn-setScore par=${hole(holeIdx.value)} sname=${scoreName} init=${pInfo[pIdx.value].inits}`)
  const score = getScore(scoreName, holeIdx.value)
  console.log(`-fn-setScore for hole holeIdx=${holeIdx.value} score=${score}`)
  emit('set-score', holeIdx.value, score)
  holeIdx.value++
  if (holeIdx.value > 18 || score === 0) {
      holeIdx.value = 1
      opened.value = false
  }
}
</script>
