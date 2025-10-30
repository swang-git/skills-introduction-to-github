<template>
<q-dialog v-model="opened">
  <div style="border:3px solid gold; border-radius:0%;height:440px;top:133px" :style="{ left:leftPos }" class="fixed q-py-sm bg-cyan-9">
    <div v-for="x in [0,1,2,3]" :key="x" class="flex justify-center text-white text-h6 cursor-pointer" v-close-popup>
      <!-- <transition v-if="pIdx==x" appear enter-active-class="animated bounceIn" style="animation-duration:3s;animation-delay:0.2s"> -->
      <!-- <transition v-if="pIdx==x" appear enter-active-class="animated flip" style="animation-duration:1s;animation-delay:0.0s"> -->
      <!-- <transition v-if="pIdx==x" appear enter-active-class="animated heartBeat" style="animation-duration:2s;animation-delay:0.2s"> -->
      <transition v-if="pIdx==x" appear enter-active-class="animated rotateIn" style="animation-duration:1s;animation-delay:0.0s">
        <q-card class="bg-cyan-9 flex justify-between q-px-xs inset-shadow-down" style="width:155px;margin:-5px 0 5px 0">
          <span key="init" class="inset-shadow-down flex flex-center" :class="{'shadow-box-blue':pIdx<=1, 'shadow-box-red':pIdx>1}"> {{ pInfo[pIdx].inits }}</span>
          <transition v-if="pIdx==x" appear enter-active-class="animated flip" style="animation-duration:1s;animation-delay:0.0s">
            <span key="hole" class="inset-shadow-down flex flex-center shadow-box-hole">{{ holeIdx }}</span>
          </transition>
        </q-card>
      </transition>
    </div>
    <table>
      <q-tr><q-td v-for="i in ['par', 'bogy']" :key=i.x><q-btn no-caps size="24px" round style="border:3px solid" :class="getStrokePadClass(i)" @click="setScore(i)">{{i}}</q-btn></q-td></q-tr>
      <q-tr><q-td v-for="i in ['bird','dobl']" :key=i.x><q-btn no-caps size="24px" round style="border:3px solid" :class="getStrokePadClass(i)" @click="setScore(i)">{{i}}</q-btn></q-td></q-tr>
      <q-tr><q-td v-for="i in ['trpl','quad']" :key=i.x><q-btn no-caps size="24px" round style="border:3px solid" :class="getStrokePadClass(i)" @click="setScore(i)">{{i}}</q-btn></q-td></q-tr>
      <q-tr><q-td v-for="i in ['eagl','dpar']" :key=i.x><q-btn no-caps size="24px" round style="border:3px solid" :class="getStrokePadClass(i)" @click="setScore(i)">{{i}}</q-btn></q-td></q-tr>
    </table>
    <transition appear enter-active-class="animated rotateOut" style="animation-duration:2s;animation-delay:0.2s">
      <q-card class="bg-cyan-9 flex justify-between q-px-xs inset-shadow" style="margin:-3px 0px 5px 0px" v-close-popup>
        <div class="q-pt-xs"><span class="text-h4 flex flex-center shadow-box-par">{{ hole(holeIdx) }}</span></div>
        <span class="q-pt-sm text-h4 text-white">{{ yard(holeIdx) }} </span>
        <div class="q-pt-xs"><span class="text-h4 flex flex-center shadow-box-hcap">{{ hcap(holeIdx) }}</span></div>
      </q-card>
    </transition>
  </div>
</q-dialog>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
import { ref } from 'vue'
import { libFunctions } from 'src/composables/libFunctions';
import { cssFunctions } from 'src/composables/cssFunctions';
import { storeFunctions } from 'src/composables/storeFunctions';
const { isDesk } = libFunctions()
const { hole, yard, hcap, getScore } = storeFunctions()
const { getStrokePadClass } = cssFunctions()
const leftPos = ref(null)
const holeIdx = ref(0)
const pIdx = ref(0)
const pScore = ref([])
const pInfo = ref(null)
const opened = ref(false)

console.log(`-ST-EnterGStrokePad`)
emitter.on('open-EnterGStrokePad', (x, y, z) => openIt(x, y, z))

function openIt (idx, pinfo=null, pidx=-1) {
  holeIdx.value = idx
  console.log(`openIt-holeIdx=${holeIdx.value} pIdx=${pidx}`, pinfo)
  pInfo.value = pinfo
  pIdx.value = pidx
  pScore.value = new Array(4).fill(0)
  if (pInfo.value != null) {
    setPadPos()
    emit('set-init', pIdx.value)
  }
  opened.value = true
}
function setPadPos () {
  const basepx = 41.2
  if (holeIdx.value <= 5) leftPos.value = (basepx * holeIdx.value)
  else if (holeIdx.value > 5 && holeIdx.value <= 9) leftPos.value = (basepx * (holeIdx.value - 5.3))
  else if (holeIdx.value > 9 && holeIdx.value <= 14) leftPos.value = (basepx * (holeIdx.value - 9))
  else if (holeIdx.value > 14) leftPos.value = (basepx * (holeIdx.value - 14.2))
  if (isDesk) {
    leftPos.value += 242
    leftPos.value += 'px'
  } else leftPos.value += 'px'
  console.log(`-CK-fn-setPadPos holeIdx=${holeIdx.value} leftPos=${leftPos.value}`)
}
function setScore (scoreName) {
  const score = getScore(scoreName, holeIdx.value)
  console.log(`-fn-setScore for hole=${holeIdx.value} pIdx=${pIdx.value} score=${score} pScore=${pScore.value}`, pInfo.value[pIdx.value])
  const pid = pInfo.value[pIdx.value].playerId
  const tpid = pInfo.value[pIdx.value].tplayerId
  const pname = pInfo.value[pIdx.value].name
  emit('set-score', holeIdx.value, score, pid, tpid, pname)
  pScore.value[pIdx.value] = score
  if (Math.min(...pScore.value) > 0) {
    opened.value = false
    holeIdx.value++
  }
  pIdx.value++
  emit('set-init', pIdx.value)
  if (pIdx.value === pInfo.value.length) opened.value = false
  setPadPos()
  if (holeIdx.value > 18 || score === 0) {
    holeIdx.value = 1
    opened.value = false
  }
}
const emit = defineEmits(['set-score', 'set-init'])
</script>
