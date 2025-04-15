<template>
<transition v-if="hoidx==holeIdx-1 && pIdx==clickedIdx" appear enter-active-class="animated flip" style="animation-duration:5s;animation-delay:0.0s">
  <div :class="getGScoreClass11(score, hoidx, holeIdx)" style="width:43.8px;height:43.8px;font-size:30px">{{ score }}</div>
</transition>
<transition v-else>
  <div :class="getGScoreClass11(score, hoidx, holeIdx)" style="width:43.8px;height:43.8px;font-size:30px">{{ score }}</div>
</transition>
</template>
<script setup>
import { cssFunctions } from '../composables/cssFunctions'
import { libFunctions } from '../composables/libFunctions'
const { getGScoreClass11 } = cssFunctions()
// const { isMate } = libFunctions()
const props = defineProps({
  score: { type: Number },
  pIdx: { type: Number },
  clickedIdx: { type: Number },
  hoidx: { type: Number },
  holeIdx: { type: Number },
  pars: { type: Object },
})
// console.log(`-ST-HoleScoreButtonG hoidx=${props.hoidx} holeIdx=${props.holeIdx}`, props.pars)
</script>