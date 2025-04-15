<template>
<div v-if="hoidx==holeIdx-1 && pIdx==clickedIdx" appear enter-active-class="animated flip" style="animation-duration:5s;animation-delay:0.0s">
  <div :class="getGScoreClass(score, hoidx, holeIdx)" style="width:39.9px;height:39.9px;font-size:26px;padding:0 0 3px 10px">{{ score }}</div>
</div>
<div v-else>
  <div :class="getGScoreClass(score, hoidx, holeIdx)" style="width:39.9px;height:39.9px;font-size:26px;padding:0 0 3px 10px">{{ score }}</div>
</div>
</template>
<script setup>
import { cssFunctions } from '../composables/cssFunctions'
// import { libFunctions } from '../composables/libFunctions'
const { getGScoreClass } = cssFunctions()
// const { isMate } = libFunctions()
// const props = defineProps({
defineProps({
  score: { type: Number },
  pIdx: { type: Number },
  clickedIdx: { type: Number },
  hoidx: { type: Number },
  holeIdx: { type: Number },
  // pars: { type: Object },
})
// console.log(`-ST-HoleScoreButtonG hoidx=${props.hoidx} holeIdx=${props.holeIdx}`, props.pars)
</script>