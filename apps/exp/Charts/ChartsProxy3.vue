<template>
<q-dialog v-model="opened" :full-width="isDesk" :maximized="isIM" position="top">
  <div style="margin:-1px 0 0 -45px">
    <ChartPaye v-if="['paye','expp'].includes(showChart)" :data="dat3" :year="year" />
  </div>
  <div v-if="showChart==='subc' || showChart==='expx'" style="margin:40px 0 0 1px">
    <ChartSubc :data="dat3" :cat="cats" :year="year" :mnth="mnth" @set-year-month="setYearMonth" />
  </div>
</q-dialog>
</template>
<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { libFunctions } from 'src/composables/libFunctions'
import { dayFunctions } from 'src/composables/dayFunctions'
import ChartPaye from './ChartPaye'
import ChartSubc from './ChartSubc'
const { isDesk, isIM } = libFunctions()
const { getNextPrevMonth } = dayFunctions()
const props = defineProps({
  data: { type: Array },
  year: { type: Number },
  mnth: { type: Number },
})
console.log(`-ST-ChartsProxy3`)
const showChart = ref(null)
const opened = ref(false)
const cats = ref(null)
const subc = ref(null)
const year = ref(null)
const mnth = ref(null)
const dat3 = ref([])
const loYear = 2013
const chartYear = (new Date()).getFullYear()

emitter.on('open-ChartsProxy3', (chartx,cats=null,subc=null,year=0,mnth=0,payeId=0) => showChartxExpDetails(chartx,cats,subc,year,mnth,payeId))
function showChartxExpDetails (chartx, ca, su, yr, mn, payeId) {
  showChart.value = chartx
  if (ca != null) cats.value = ca
  if (su != null) subc.value = su
  if (yr > 0) year.value = yr

  let dx = []
  if (ca != null) dx = props.data.filter(p => p.cats == ca)
  if (su != null) dx = dx.filter(p => p.subc == su)
  if (yr > 0) dx = dx.filter(p => p.date.substring(0, 4) == yr)
  // console.log(`-FN-ChartsProxy3 chartx=${chartx} cats=${cats.value} subc=${subc.value} year=${year.value} payeId=${payeId}`, dx)
  if (mn > 0) dx = dx.filter(p => p.date.substring(5, 7) == mn)
  if (payeId > 0) dx = dx.filter(p => p.payeId == payeId)
  dat3.value = dx

  if (chartx === 'expp') {
    console.log(`-FN-ChartsProxy3 chartx=${chartx} cats=${cats.value} subc=${subc.value} year=${year.value} payeId=${payeId}`, dx)
    emitter.emit('open-ExpDetailsPad', cats.value, subc.value, year.value, 0, payeId)
  } else if (chartx === 'expx') {
    subc.value = su
    year.value = yr
    mnth.value = mn
    console.log(`-FN-ChartsProxy3 chartx=${chartx} cats=${ca} subc=${su} year=${yr} mn=${mn} payeId=${payeId}`)
    emitter.emit('open-ExpDetailsPad', cats.value, subc.value, year.value, mnth.value)
  } else if (chartx === 'paye') {
    console.log(`-FN-ChartsProxy3 chartx=${chartx} cats=${ca} subc=${su} year=${yr}`, props.data === undefined)
    if (ca != null) cats.value = ca
    if (su != null) subc.value = su
    if (yr > 0) year.value = yr
    // if (props.data !== undefined) dat3.value = props.data.filter(p => p.cats == ca && p.subc == su && p.date.substring(0, 4) == yr)
    // let dx = []
    // if (ca != null) dx = props.data.filter(p => p.cats === ca)
    // if (su != null) dx = dx.filter(p => p.subc === su)
    // if (yr > 0) dx = dx.filter(p => p.date.substring(0, 4) == yr)
    // if (mn > 0) dx = dx.filter(p => p.date.substring(5, 7) == mn)
    // if (payeId > 0) dx= dx.filter(p => p.payeId == payeId)
    // dat3.value = dx
    const payes = [...new Set(dat3.value.map(p => p.payeId))]
    // console.log(`-CK-ChartsProxy3 showChart=${chartx} cats=${ca} subc=${subc.value} year=${yr} mnth=${mn} payeId=${payeId}`, payes)
    // console.table(dat3.value.map(p=>[p.payeId, p.date, p.paye]).sort((a, b) => a[0] - b[0]))
    // console.table(dat3.value.map(p=>p.payeId))
    if (payes.length > 1) {
      opened.value = true
    } else if (payes.length === 1) {
      // emitter.emit('close-ExpDetailsPad')
      // emitter.emit('open-ExpDetailsPad', dat3.value, ca, yr, mn)
      emitter.emit('open-ExpDetailsPad', ca, su, yr, mn, payeId)
    }
  } else if (chartx === 'subc') {
    cats.value = ca
    subc.value = su
    year.value = yr
    mnth.value = mn
    const mmdd = mn < 10 ? yr + '-0' + mn : yr + '-' + mn
    dat3.value = props.data.filter(p => p.cats == cats.value && p.date.substring(0, 7) == mmdd)
    const subx = [...new Set(dat3.value.map(p => p.subc))]
    console.log(`-fn-setData dat3.length=${dat3.value.length} subx=${subx.length}`)
    if (subx.length > 1) {
      opened.value = true
    } else if (subx.length === 1) {
      emitter.emit('open-ExpDetailsPad',cats.value, subx[0], year.value, mnth.value)
    }
  }
}
emitter.on('show-exp-details-paye', (pyId) => {
  // console.log(`-CK-py=${py} paye=${paye.value}`, py == paye.value)
  // if (paye.value == py) return 
  // paye.value = py
  const pdata = dat3.value.filter(p => p.payeId == pyId)
  if (pdata.length == 0) return
  console.log(`-CK-ChartsProxy3 showChart=${showChart.value} cats=${cats.value} subc=${subc.value} year=${year.value} payeId=${pyId}`, pdata)
  emitter.emit('close-ExpDetailsPad')
  emitter.emit('open-ExpDetailsPad', pdata, cats.value, subc.value, year.value, mnth.value)
})
function setYearMonth (pn, chartx, cats) {
  const ym = getNextPrevMonth(pn, mnth.value, year.value, loYear, chartYear)
  year.value = ym[0]
  mnth.value = ym[1]
  if (chartx === 'subc') {
    const ympatt = new RegExp(year.value + '-0?' + mnth.value)
    dat3.value = props.data.filter(p => p.cats == cats && ympatt.test(p.date.substring(0, 7)))
    console.log(`-fn-setYearMonth chartx=${chartx} year=${year.value} mnth=${mnth.value} pn=${pn} cats=${cats}`, dat3.value)
  }
}
function XXshowExp () {
  console.log(`-fn-showExp`)
}
</script>
