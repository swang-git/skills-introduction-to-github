<template>
<div style="padding-top:1px">
  <div v-for="match in matchList" :key=match.x class="row">
    <div class="active" @click="userSelected(match)" :class="getClass(match)">{{ getLabel(match) }}
    <!-- <q-tooltip v-if="1" class="text-h6 bg-indigo-10 text-cyan-2">tmntId:{{ match.tmntId }}</q-tooltip> -->
    </div>
  </div>
</div>
</template>
<script setup>
import emitter from 'tiny-emitter/instance'
// import { libFunctions } from 'src/composables/libFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
// const { isIM } = libFunctions()
const { today } = dayFunctions()
const props = defineProps({ matchList: Object })
const emit = defineEmits(['user-selected'])
var selectedTmntId = 0

// console.log('-ST-TeamMatchList', props.matchList)
console.log('-ST-TeamMatchList')
function getLabel(match) {
  // if (isIM) return match.date.substring(5, 10) + ' (' + match.date.chwk1() + ') ' + match.start_time + ' ' + match.cname
  return match.date + ' (' + match.date.chwk1() + ') ' + match.start_time + ' at ' + match.cname
}
function getClass(match) {
  // let colr = match.date < yyyymmddHHMM(new Date()) ? 'text-grey-5' : 'text-white'
  let colr = match.date < today() ? 'text-grey-5' : selectedTmntId == match.tmntId ? 'text-amber' : 'text-white'
  // let colr =  selectedTmntId == match.tmntId ? 'text-grey-5' :  match.date < today() ? 'text-amber' : 'text-white'
  if (match.note === 'tentative') colr = 'text-amber'
  return colr + ' text-h6 ellipsis q-pb-xs q-px-sm cursor-pointer'
}
emitter.on('show-team-match', (tmntId) => {  // this is from MatchGrouping when groupingDone to show TeamMatch
  const match = props.matchList.find(p => p.tmntId == tmntId)
  emit('user-selected', match)
})
function userSelected (match) {
  console.log('-CK-fn-userSelected', match)
  emit('user-selected', match)
  selectedTmntId = match.tmntId
}
</script>