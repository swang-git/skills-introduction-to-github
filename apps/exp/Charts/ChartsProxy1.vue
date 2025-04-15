<template>
<q-dialog v-model="opened" :full-width="isDesk" :maximized="isIM" position="top">
  <div :class="{ 'q-ml-xl':isDesk }">
    <ChartYear v-if="showChart==='year'" :data="data" />
    <ChartMybb v-if="showChart==='mybb'" :data="data" />
    <ChartCatA v-if="showChart==='cata'" :data="data" />
    <ChartYrmo v-if="showChart==='yrmo'" :data="data" />
    <ChartMoyr v-if="showChart==='moyr'" :data="data" />
    <ChartCats v-if="showChart==='cats'" :data="data" :year="year" />
    <ChartYmpi v-if="showChart==='ympi'" :data="data" :year="year" :mnth="mnth" @set-year-mnth="setYearMonth" />
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import ChartYear from './ChartYear'
import ChartMybb from './ChartMybb'
import ChartCats from './ChartCats'
import ChartMoyr from './ChartMoyr'
import ChartYrmo from './ChartYrmo'
import ChartYmpi from './ChartYmpi'
import ChartCatA from './ChartCatA'
const props = defineProps({
  data: { type: Array },
  year: { type: Number },
  mnth: { type: Number },
})
console.log(`-ST-ChartsProxy1`)
// const app = createApp({})
// app.component('ChartYear', ChartYear)
// app.component('ChartMybb', ChartMybb)
// app.component('ChartCats', ChartCats)
// app.component('ChartMoyr', ChartMoyr)
// app.component('ChartYrmo', ChartYrmo)
// app.component('ChartCatA', ChartCatA)
// app.component('ChartYmpi', ChartYmpi)
// app.component('ExpDetailsPad', ExpDetailsPad)
const { isDesk, isIM } = libFunctions()
const { getNextPrevMonth } = dayFunctions()
const showChart = ref(null)
const mnth = ref(null)
const year = ref(null)
const loYear = 2013
const chartYear = (new Date()).getFullYear()
emitter.on('open-ChartsProxy1', (chartx, yr, mn) => {
  showChart.value = chartx
  mnth.value = mn
  year.value = yr 
  console.log(`-CK-ChartsProxy1 showChart=${showChart.value}`)
  opened.value = true
})
emitter.on('show-exp-details', (cats, subc, year, mnth) => {
  emitter.emit('open-ExpDetailsPad', cats, subc, year, mnth)
})
const opened = ref(false)
function setYearMonth (pn) {
  const ym = getNextPrevMonth(pn, mnth.value, year.value, loYear, chartYear)
  year.value = ym[0]
  mnth.value = ym[1]
  // this.showx.ympi = true
  console.log(`-fn-setYearMonth year=${year.value} mnth=${mnth.value} pn=${pn}`)
}
</script>
