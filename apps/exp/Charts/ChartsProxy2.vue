<template>
<q-dialog v-model="opened" :full-width="isDesk" :maximized="isIM" position="top">
  <div v-if="showChart==='suba'" style="margin:-10px 0 0 -50px">
    <ChartSubA v-if="showChart==='suba'" :data="dat2" :year="year" :catx="cats"/>
  </div>
  <div v-if="showChart==='ympi'" style="margin:40px 0 0 1px">
    <ChartYmpi :data="data" :mnth="mnth" :year="year" @set-year-month="setYearMonth" />
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import ChartYmpi from './ChartYmpi'
import ChartSubA from './ChartSubA'
const props = defineProps({
  data: { type: Array },
  year: { type: Number },
  mnth: { type: Number },
})
console.log(`-ST-ChartsProxy2`)
const { isDesk, isIM } = libFunctions()
const { getNextPrevMonth } = dayFunctions()
const showChart = ref(null)
const cats = ref(null)
const mnth = ref(null)
const year = ref(null)
const loYear = 2013
const chartYear = (new Date()).getFullYear()
const dat2 = ref([])
emitter.on('open-ChartsProxy2', (chartx, x, y, z) => {
  showChart.value = chartx
  if (chartx == 'suba') {
    cats.value = x
    year.value = y
    if (y > 0 ) dat2.value = props.data.filter(p => p.cats == x && p.date.substring(0, 4) == y)
    else dat2.value = props.data.filter(p => p.cats == x)
    // console.log(`-ST-ChartsProxy2 showChart=${showChart.value} cats=${x} year=${y}`, dat2.value)
    opened.value = true
    return
  } else if (chartx == 'ympi') {
    mnth.value = x
    year.value = y
    // dat2.value = props.data.filter(p => p.date.substring(0, 7) == y + '-' + x)
    // console.log(`-ST-ChartsProxy2 showChart=${showChart.value} cats=${x} year=${y}`, props.data[0].date.substring(0, 7),  y+'-'+x, dat2.value)
    opened.value = true
  // } else if (chartx == 'subc') {
  //   cats.value = x
  //   year.value = y
  //   mnth.value = z
  //   // if (props.data !== undefined) dat2.value = props.data.filter(p => p.cats == x)
  //   const mmdd = z < 10 ? y + '-0' + z : y + '-' + x
  //   dat2.value = props.data.filter(p => p.cats == x && p.date.substring(0, 7) == mmdd)
  //   const nSubc = [...new Set(dat2.value.map(p => p.subc))]
  //   console.log(`-fn-setData dat2.length=${dat2.value.length} nSubc=${nSubc.length}`)
  //   if (nSubc.length > 1) {
  //     opened.value = true
  //   } else if (nSubc.length === 1) {
  //     const subx = null
  //     emitter.emit('open-ExpDetailsPad', props.data, cats.value, subx, year.value, mnth.value)
  //   }
  }
})
emitter.on('show-exp-details', (cats, subc, year, mnth) => {
  emitter.emit('open-ExpDetailsPad', cats, subc, year, mnth)
})
const opened = ref(false)
function setYearMonth (pn, chartx, cats) {
  const ym = getNextPrevMonth(pn, mnth.value, year.value, loYear, chartYear)
  year.value = ym[0]
  mnth.value = ym[1]
  console.log(`-fn-setYearMonth year=${year.value} mnth=${mnth.value} pn=${pn}`)
}
</script>
