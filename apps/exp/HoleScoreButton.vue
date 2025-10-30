<template>
<div :class="getScoreClass(score, hoidx, pars)">{{ score }}
  <div v-if="score!=pars['h'+hoidx] && score!=pars['h'+hoidx]-1 && score!=pars['h'+hoidx]+1">
    <q-chip v-if="score>0" class="hole-par1" color="grey-7"> {{ parx() }} </q-chip>
    <q-chip v-else :class="mark1" color="purple"> {{ hoidx }} </q-chip>
  </div>
  <q-tooltip class="text-h6 bg-grey text-blue-9"> Hole {{ hoidx }}</q-tooltip>
</div>
</template>
<script setup>
import { cssFunctions } from '../src/composables/cssFunctions'
const { getScoreClass } = cssFunctions()
const props = defineProps({
  score: { type: Number },
  hoidx: { type: Number },
  pars: { type: Object },
})
console.log(`-ST-HoleScoreButton`)
  
function parx () {
  return props.pars['h' + props.hoidx]
}
</script>