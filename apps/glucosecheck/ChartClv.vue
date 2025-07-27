<template>
<div>
  <canvas id="clv-chart" class="bg-lime-1" />
  <q-card square class="bg-lime-3">
    <q-card-actions class="text-h6" align="between">
      <div v-if="isDesk">{{ startDate }} 至 {{ endDate }} 共({{ dateGap }}天)</div>
      <!-- <div v-if="isDesk">{{ startDate }} 至 {{ endDate }}的空腹血糖, 共({{ dateGap }}天)</div> -->
      <div v-else>   {{ startDate }} 至 {{ endDate }}, 共({{ dateGap }}天)</div>
      <q-btn round glossy icon="chevron_left" color="green-9" @click="setData(++idx)" :disable="idx>=parseInt(clvdata.length/gap)" />
      <q-btn round glossy icon="chevron_right" color="blue-9" @click="setData(--idx)" :disable="idx==0" />
      <q-btn round glossy icon="note" color="blue-9" @click="showA1cDefinitions">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">A1C Definition</q-tooltip></q-btn>
      <q-btn round glossy icon="donut_small" color="pink" @click="$emit('open-chart', 'dnt')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">90 day details</q-tooltip></q-btn>
      <q-btn round glossy icon="trending_down" color="accent" @click="$emit('open-chart', 'eag')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">Sugar Level Trending</q-tooltip></q-btn>
      <q-btn round glossy icon="equalizer" color="primary" @click="$emit('open-chart', 'a1c')">
        <q-tooltip v-if="isDesk" class="text-h6 text-cyan-2 bg-blue-10">90 days a1c</q-tooltip></q-btn>
      <q-btn round glossy icon="close" color="red-9" v-close-popup />
    </q-card-actions>
  </q-card>
</div>
</template>
<script setup>
import { ref, onMounted, reactive } from 'vue'
import emitter from 'tiny-emitter/instance'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
Chart.register(ChartDataLabels);
import configClv from './chart-clv-config.js'
import { dayFunctions } from '../src/composables/dayFunctions'
const { getDateGap } = dayFunctions()
import { libFunctions } from '../src/composables/libFunctions'
const { isDesk } = libFunctions()
import { infoFunctions } from '../src/composables/infoFunctions'
const { getA1cDefinitions } = infoFunctions()
const props = defineProps({
  clvdata: { type: Array },
})
const emits = defineEmits(['open-chart'])
var myChart = reactive({})
const dateGap = ref(11)
const startDate = ref('2021-07-16')
const endDate = ref(null)
const config = configClv
var ctx = {}
const gap = 11
var idx = 0

console.log('-ST-ChartClv', config)
onMounted(() => {
  console.log('-MT-ChartClv')
  createChart()
  setData()
})
function showA1cDefinitions () {
  let x = getA1cDefinitions()
  emitter.emit('open-InfoDisplay', x.tit, x.msg)
}
function createChart () {
  console.log('-fn-createChart')
  ctx = document.getElementById('clv-chart').getContext('2d')
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
}
function setData (moveIdx=0) {
  console.log(`-CK-fn-setData idx=${idx}, gap=${gap} curr=${idx*gap}, datalen=${props.clvdata.length}`, props.clvdata)
  idx = moveIdx
  const start = idx * gap 
  const end = (idx + 1) * gap 
  const da = props.clvdata.slice(start, end).reverse()
  // console.log(`-CK-fn-setData start=${start}, end=${end} dalen=${da.length}`, da)
  drawChart(da)
}
function drawChart (da) {
  const dl = da.length
  startDate.value = da[0].date.substring(0, 10)
  endDate.value = da[dl - 1].date.substring(0, 10)
  console.log(`-CK-fn-drawChart startDate=${startDate.value}, endDate=${endDate.value} dalen=${da.length}`, da)
  dateGap.value = getDateGap(startDate.value, endDate.value)
  config.data.datasets[0].label = `当天的空腹血糖 - 中国标准(mmol/L)`
  var data = da.map(p => p.clv)
  var food = da.map(p => p.food)
  var fdtm = da.map(p => p.fdtm)
  var gptm = da.map(p => p.gptm)
  var note = da.map(p => p.note)
  var labels = da.map(p => p.date.substring(5, 10))
  config.data.labels = labels
  config.data.datasets[0].cktm = da.map(p => p.date)
  config.data.datasets[0].fdtm = fdtm
  config.data.datasets[0].gptm = gptm
  config.data.datasets[0].data = data
  config.data.datasets[0].food = food
  config.data.datasets[0].note = note
  // console.log('-CK-setChartData', note)
  for (var i=0; i<dl; i++) {
    const val = config.data.datasets[0].data[i]
    if (val > 9.0) config.data.datasets[0].backgroundColor[i] = 'red'
    else if (8.5 < val && val <= 9.0) config.data.datasets[0].backgroundColor[i] = 'purple'
    else if (7.0 < val && val <= 8.5) config.data.datasets[0].backgroundColor[i] = 'indigo'
    else if (6.0 < val && val <= 7.0) config.data.datasets[0].backgroundColor[i] = 'navy'
    else if (5.0 < val && val <= 6.0) config.data.datasets[0].backgroundColor[i] = 'blue'
    else if (4.0 < val && val <= 5.0) config.data.datasets[0].backgroundColor[i] = 'green'
    else config.data.datasets[0].backgroundColor[i] = 'cyan'
  }
  // console.log('-CK-A-fn-setData')
  myChart.update()
  // console.log('-CK-B-fn-setData')
}
</script>
