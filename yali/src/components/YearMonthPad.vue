<template>
<q-dialog v-model="opened">
  <div class="absolute" style="top:270px;border:2px white white">
    <div class="float-right" :style="{ display:openYearList ? '' : 'none' }" style="border:2px solid white">
      <div v-for="yr in compYears" :key=yr class="bg-cyan-10 text-h6 text-cyan-2" style="width:100px">
        <q-radio v-model="year" :val="yr" :label="yr" color="red" @click="openYearList=false" />
      </div>
    </div>
    <div class="bg-cyan-10 inset-shadow-down" style="border:2px white solid">
      <q-card-actions align="between" class="bg-teal-10 text-cyan-1">
        <div class="text-h6">Selected Year: {{ year }} </div>
        <q-btn glossy color="primary" label="Choose Year" @click="openYearList=!openYearList" :style="{ display:openYearList ? 'none' : '' }" />
      </q-card-actions>
    </div>
    <div>
      <table class="bg-teal-8 text-h6 text-white" style="y-overflow:auto;margin:auto;border: 2px solid white">
        <q-tr><td v-for="i in [1, 2, 3,  4,  5,  6]" :key=i.x><q-btn :disable=isDisabled(i) color="teal-10" class="num-pad" @click="setMonth(i)">{{i}}</q-btn></td></q-tr>
        <q-tr><td v-for="i in [7, 8, 9, 10, 11, 12]" :key=i.x><q-btn :disable=isDisabled(i) color="teal-10" class="num-pad" @click="setMonth(i)">{{i}}</q-btn></td></q-tr>
      </table>
    </div>
  </div>
</q-dialog>
</template>
<script setup>
import { ref, computed } from 'vue'
import emitter from 'tiny-emitter/instance'
import { dayFunctions } from '../../src/composables/dayFunctions'
const openYearList = ref(false)
const opened = ref(false)
// var year = ref(new Date().yyyymmdd().substring(0, 4))
const year = ref(2020)
emitter.on('open-YearMonthPad', (yr) => openIt(yr))

console.log('-ST-YearMonthPad')
const compYears = computed(() => {
  const year = (new Date()).getFullYear()
  const yrs = ['2020']
  for (let y = 2021; y <= year; y++) yrs.push(String(y))
  return yrs
})
function isDisabled (mon) {
  const theYear = (new Date()).getFullYear()
  const theMnth = (new Date()).getMonth() + 1
  if (year.value >= theYear) {
    if (mon >= theMnth) return true
    else return false
  } else {
    return false
  }
}
const emit = defineEmits(['upd-ym'])
function setMonth (mo) {
  emit('upd-ym', year.value, mo)
  opened.value = false
}
function openIt (yr) {
  year.value = yr
  // console.log(`-CK-fn-open year=${yr}`)
  opened.value = true
}
</script>
