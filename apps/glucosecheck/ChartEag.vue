<template>
<div>
  <canvas id="eag-chart" :height="isIM ? 150 : 120" class="bg-cyan-1" />
  <q-card square>
    <q-card-actions align="between">
      <div v-if="isDesk">{{ fromEag }} {{ fromA1p }} from {{fromDate}} to {{ gluStartDate.substring(0,10) }} ({{dateGap}}天)</div>
      <q-btn round glossy icon="west"  color="blue"  @click="moveBF(true)"  v-if="currIdx>0 && !auto" /> 
      <q-btn round glossy :icon="auto ? 'pause' : 'play_arrow'"  :color="auto ? 'amber-9' : 'green-9'" @click="togglePlay()"/>
      <q-btn round glossy icon="east"  color="green" @click="moveBF()" v-if="currIdx<datalen-1 && !auto" />
      <q-btn id="labelingID" round glossy icon="label" color="cyan"  @click="labeling(event)" v-if="currIdx==datalen-1 || !auto" />
      <q-btn round glossy icon="donut_small" color="pink" @click="$emit('open-chart', 'dnt')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">90 day details</q-tooltip></q-btn>
      <q-btn round glossy icon="空" color="teal-9" style="padding:0 0 8px 1.1px" @click="$emit('open-chart', 'clv')"></q-btn>
      <q-btn round glossy icon="equalizer" color="primary" @click="$emit('open-chart', 'a1c')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">Hemoglobin mmol/L中国度量</q-tooltip></q-btn>
      <q-btn round glossy icon="close" color="red" v-close-popup />
    </q-card-actions>
  </q-card>
</div>
</template>
<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
Chart.register(ChartDataLabels)
import cfg from './chart-line-config.js'

import { libFunctions } from '../src/composables/libFunctions'
const { isDesk, isIM, $q } = libFunctions()
import { dayFunctions } from '../src/composables/dayFunctions'
const { getDateGap } = dayFunctions()

const emit = defineEmits(['open-chart'])
const props = defineProps({ 
  gluSections: { type: Array },
  gludata: { type: Array },
  xlabel: { type: Array },
})
const step = 1
const pauseTime = 1 // milliseconds pause time for next back/forward click
const pause = ref(false)
const auto = ref(true)
const currIdx = ref(0)
var datalen = 0
var fromDate = null
var fromEag = null
var fromA1p = null
var dateGap = 0
const gluStartDate = '2021-07-16 00:00'
const gluStartEag = 120
const gluStartA1p = 6.6
const prevA1p = ref(6.6)
const chartda = ref([])
const a1px = ref([])
const a1ps = ref([])
const eags = ref([])
const days = ref([])
const a1pDates = ref([])
const backda = ref([])
// const convasDom = ref(null)
const config = cfg
var ctx = {}
const wid = 850
// var myChart = reactive({})
var myChart = null

console.log('-ST-ChartEag')

onMounted(() => {
  console.log('-MT-ChartEag')
  createChart()
  labeling()
  initChartda()
  setChartData()
})

const compLatestEag = computed(() => { return chartda.value[0] === undefined ? gludata.value[datalen-1].eag : chartda.value[0].eag })
function createChart () {
  console.log('-fn-createChart')
  ctx = document.getElementById('eag-chart').getContext('2d')
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
}
function labelingIM () {
  var a1ps = a1px.value
  var days = days.value
  var gradient = ctx.createLinearGradient(0, 0, 600, 0);
  gradient.addColorStop('0.00', 'green');
  gradient.addColorStop('0.80', 'teal');
  gradient.addColorStop('0.83', 'cyan');
  gradient.addColorStop('0.90', 'darkgreen');
  gradient.addColorStop('0.95', 'lightblue');
  gradient.addColorStop('0.98', 'yellow');
  gradient.addColorStop('1.00', 'lime');
  ctx.fillStyle = gradient;

  const delta = 1
  const xx = [-10 + delta, 70 + delta, 62 + delta, 35 + delta, 30 + delta, 33 + delta, 15 + delta, 10 + delta]
  for (let i=0; i<a1ps.length; ++i) {
    const a1p = a1ps[i]
    const day = days[i]
    const slc = days.slice(i, days.length)
    const dsm = slc.reduce((prev, curr) => prev + curr, 0)
    console.table(slc)
    console.log('-fr-', i, dsm, a1p)
    let x =  dsm + xx[i]
    if (a1p == 6.6) {
      ctx.font = '20px stfangsong'
      ctx.fillText('HbA1c(%)=' + a1p, x + 85, 70)
      ctx.fillText('持续' + day + ' days', x + 9, 110)
    } else {
      ctx.font = '14px stfangsong'
      ctx.fillText(a1p, x, 112)
      ctx.fillText(day, x, 130)
    }
  }
}
function labeling () {
  // console.log(`-fn-labeling xlabel=${this.xlabel}`)
  let a1ps = a1px.value
  // let days = days.value
  if (isIM) return labelingIM()
  // console.log('-fn-labeling', a1pS.valueections)
  // console.table(a1ps)
  // console.table(days)
  var gradient = ctx.createLinearGradient(0, 0, 600, 0);
  gradient.addColorStop('0.00', 'green');
  gradient.addColorStop('0.80', 'teal');
  gradient.addColorStop('0.83', 'cyan');
  gradient.addColorStop('0.90', 'darkgreen');
  gradient.addColorStop('0.95', 'lightblue');
  gradient.addColorStop('0.98', 'yellow');
  gradient.addColorStop('1.00', 'lime');
  // gradient.addColorStop('0.98', 'magenta');
  ctx.fillStyle = gradient;

  let xis = []
  for (let i=0; i<a1ps.length; ++i) {
    const a1p = a1ps[i]
    const day = days[i]
    const slc = days.value.slice(i, days.value.length)
    const dsm = slc.reduce((prev, curr) => prev + curr, 0)
    // console.table(slc)
    // console.log('-fr-', i, dsm, a1p)
    // let x = this.xlabel[i] - 20
    let x = this.xlabel[i] - 40
    if (a1p == 6.6) {
      ctx.font = '24px stfangsong'
      ctx.fillText('HbA1c(%)=' + a1p, x - 90, 110)
      ctx.fillText('持续' + day + ' days', x - 90, 140)
      // xis.push('6.6 ' + (x - 25) + ' ' + day)
    } else {
      // console.log(`6.5 => ${x}`)
      ctx.font = '16px stfangsong'
      ctx.fillText(a1p, x, 133)
      ctx.fillText(day, x + 2, 153)
      // xis.push(a1p + ' ' + x + ' ' + day)
    }
  }
  // console.table(xis)
}
function togglePlay () {
  auto.value = !auto.value
  if (currIdx.value === datalen && chartda.value.length === datalen) {
    initChartda()
    currIdx.value = 0
  }
  if (auto.value) setChartData()
}
function moveBF (goback=false) {
  if (currIdx.value >= datalen && !goback) {
    currIdx.value = 0
  }
  // pause.value = true 
  // setTimeout(() => { pause.value = false }, pauseTime)
  currIdx.value = currIdx.value + step
  if (currIdx.value < 0) {
    currIdx.value = 0
    // step = 10
  }
  // console.log(`-CK-moveBF currIdx=${currIdx.value}, step=${step}`)
  // let tlabel = `-CK-setCharData`; console.time(tlabel)
  setChartData(goback)
  // console.timeEnd(tlabel)
  // let ulabel = `-CK-myChart.update`; console.time(ulabel)
  // if (dateGap % 20 === 0) myChart.update() // 20 days to update chart is slow aoubt every 55 ms 
  myChart.update() // 20 days to update chart is slow aoubt every 55 ms 
  // console.timeEnd(ulabel)
  // if (!auto.value) setTimeout(() => { this.labeling() }, 1000)
  // if (!auto.value) this.$nextTick(() => { this.labeling() })
  if (!auto.value) {
    let dom = document.getElementById('labelingID')
    // this.simulateClick(dom)
    let theclick = new Event("click")
    // dom.dispatchEvent(theclick)
  }
}
function simulateClickXX (element) {
  trigger( element, 'mousedown' );
  trigger( element, 'click' );
  trigger( element, 'mouseup' );
  function trigger( elem, event ) {
    elem.dispatchEvent(new MouseEvent(event));
  }
}
function initChartda () {
    //  console.table(this.a1pDates)
  // props.gludata = gludata.reverse()
  // this.xlabel = xlabel
  // props.gludata = gludata
  datalen = props.gludata.length
  // a1pS.valueections = a1pSections
  eags.value = props.gluSections.map(p => p.eag)
  days.value = props.gluSections.map(p => p.day)
  a1px.value = props.gluSections.map(p => p.a1p)
  $q.localStorage.set('a1ps', a1px.value)
  $q.localStorage.set('days', days.value)
  chartda.value = new Array(datalen).fill({date:gluStartDate, eag:gluStartEag, a1p:6.6})
}
function setChartData (goback=false) {
  // console.log('-fn-setChartData gludata.length=', props.gludata.length) // props.gludata is from now to past
  if (goback) {
    if (currIdx.value == 0 && backda.value.length > 0) currIdx.value = datalen
    currIdx.value -= 2
    chartda.value.shift()
    chartda.value.push(backda.value.pop())
  } else {
    if (isNaN(currIdx.value)) currIdx.value = 0
    const currIdx1 = currIdx.value + step
    const nda = props.gludata.slice(currIdx.value, currIdx1)
    // console.log(`-CK-gludata currIdx=${currIdx.value}, currIdx1=${currIdx1}`, nda, props.gludata)
    // if (currIdx.value === datalen) {
    //   nda[0].eag = 100
    //   console.table(nda)
    // }
    backda.value.push(chartda.value.pop())
    chartda.value.unshift(nda[0])
    // for (let i=0; i<nda.length; ++i) backda.value.push(chartda.value.pop())
    // for (let i=nda.length-1; i>0; --i) chartda.value.unshift(nda[i])
    const a1p = nda[0] == undefined ? null : nda[0].a1p
    const eag = nda[0] == undefined ? null : nda[0].eag
    const aex = [parseFloat(a1p), parseFloat(eag)]
    if (!a1ps.value.includes(aex)) a1ps.value.push(aex)
    if (nda[0] != undefined && nda[0].a1p != prevA1p.value) {
      prevA1p.value = nda[0].a1p
      a1pDates.value.push(nda[0].date)
    }
    // console.log('-ck-a1ps', a1ps.value)
  }
  let latestda = props.gludata[datalen-1]
  // console.log(`goback=${goback}, currIdx=${currIdx.value}, step=${step}, datalen=${datalen}, latestda=${latestda.date}`)
  // if (currIdx.value > datalen + step) {
  if (currIdx.value === datalen) {
    auto.value = false
    // console.log('-CK-setChartData stopped', currIdx.value, auto.value)
    return
  }
  // console.log('-CK-setChartData chartda', chartda.value)
  fromEag = chartda.value[0].eag
  fromA1p = chartda.value[0].a1p
  fromDate = chartda.value[0].date
  // this.dateGap = (new Date(this.fromDate.substring(0,10)) - new Date(this.gluStartDate.substring(0,10)))/(24*60*60*1000)
  dateGap = getDateGap(gluStartDate.substring(0,10), fromDate.substring(0,10))
  var data = chartda.value.map(p => p.eag)
  var labels = chartda.value.map(p => p.date.substring(5, 10))
  if (currIdx.value === datalen - 1) { // fake data to make sure the min y-axis is gluStartEag
    data.push(gluStartEag)
    labels.push(labels[currIdx.value])
  }
  config.data.datasets[0].label = `90 Day Estimated Average Glucose Lavel, start from ${gluStartDate.substring(0,10)}`
  config.data.labels = labels
  config.data.datasets[0].data = data
  config.data.datasets[0].a1ps = a1ps.value
  // console.log('-ck-The END', currIdx.value, datalen)
  ctx.fillText(6.6, 200, 50)
  if (auto.value) setTimeout(() => { moveBF() }, pauseTime) // move automatically
  // if (auto.value) moveBF() // move automatically
}
</script>
