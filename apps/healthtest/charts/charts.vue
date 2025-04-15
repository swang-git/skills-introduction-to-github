<template>
<div class="chart-container">
  <canvas id="canvas" class="bg-lime-1" :height="isDesk ? '222' : '450'" />
</div>
<q-card square class="bg-lime-3">
  <q-card-actions class="text-h6" align="between">
    <div v-if="isDesk">{{ startDate }} 至 {{ endDate }} Health Test</div>
    <div v-else>{{ startDate }}</div>
    <q-btn round glossy color="primary" :icon="getIcon()" @click="setGap()" style="margin:-4px 10px 0 10px;padding: 0 0 8px 1.1px" />
    <q-btn round glossy icon="chevron_left" color="green-9" @click="moveback()" v-if="idx<parseInt(props.chdata.length/gap - 0.1)" /><q-btn flat round v-else />
    <q-btn round glossy icon="chevron_right" color="blue-9" @click="movefrwd()" v-if="idx>0" /><q-btn round flat v-else />
      <q-btn round glossy color="teal-9" icon="stacked_line_chart" @click="switchChart()" />
      <q-btn round glossy color="red-10" icon="cancel" v-close-popup />
  </q-card-actions>
</q-card>
</template>
<script setup>
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import configHTChart from './chart-config.js'
import configCombChart from './chart-lines-config.js'
import { dayFunctions } from 'src/composables/dayFunctions'
import { libFunctions } from 'src/composables/libFunctions'
import { ref, onMounted, toRefs } from 'vue'
Chart.register(ChartDataLabels)
const props = defineProps({
  chdata: Array,
  chname: String,
})
const { chdata, chname } = toRefs(props)
// const { getDateGap } = dayFunctions()
const { isDesk } = libFunctions()
var config = null // make these 2 non-reactive and before change config, must destroy chart and recreate it
var myChart = null
const dateGap = ref(null)
const gap = ref(9)
const idx = ref(0)
const start = ref(null)
const end = ref(null)
const startDate = ref(null)
const endDate = ref(null)
const curChart = ref('PGLPChart')
function getIcon () {
  // return gap.value == null ? '天' : gap.value == 9 ? '9' : '30'
  return gap.value == null ? '天' : gap.value == 9 ? '九' : gap.value == 20 ?  '廿' : '卅'
}
function setGap () { 
  gap.value = gap.value == 9 ? 20 : gap.value === 20 ? 30 : 9
  idx.value = 0
  start.value = 0
  end.value = gap.value
  loadChartData()
}
function createChart () {
  console.log('-fn-createChart')
  const ctx = document.getElementById('canvas').getContext('2d')
  return new Chart(ctx, config)
}
function moveback () {
  idx.value++
  start.value = idx.value === 0 ?  0 : end.value - 1
  end.value = start.value + gap.value
  console.log(`-CK-moveback=${idx.value}, start=${start.value} end=${end.value}`)
  loadChartData()
}
function movefrwd () {
  idx.value--
  end.value = idx.value === 0 ?  gap.value : start.value + 1
  start.value = end.value - gap.value
  console.log(`-CK"movefrwd=${idx.value}, start=${start.value} end=${end.value}`)
  loadChartData()
}
function loadChartData () {
  console.log(`-fn-loadChartData idx=${idx.value} curChart=${curChart.value}`)
  curChart.value == 'PGLPChart' ? setPGLPData() : setCombData()
}
function setPGLPData () {
  // curChart.value = 'PGLPChart'
  if (config != configHTChart) {
    myChart.destroy()
    config = configHTChart
    myChart = createChart()
  }
  console.log(`-fn-setPGLPData curChart=${curChart.value}`)
  const mons = props.chdata.map(p => p.date)
  const pA1cVals = []
  const dailydiff = []
  const labels = mons.slice(start.value, end.value).reverse()
  const da = props.chdata.slice(start.value, end.value).reverse()
  // console.log(`-CK-start=${start.value} end=${end.value}`, da, labels)
  const dl = labels.length
  // console.table(da)
  startDate.value = da[0].date
  // startDate.value = da.find(p => p != undefined).date
  endDate.value = da[dl - 1].date
  labels.forEach(mon => {
    // const pA1c = props.chdata.filter(x => x.date == mon).map(p => ((p.dif / p.portfolio) * 100).toFixed(2))
    const pA1c = props.chdata.filter(x => x.date == mon).map(p => p.A1c)
    const portf = props.chdata.filter(x => x.date == mon).map(p => p.ALT)
    // const pdiff = props.chdata.filter(x => x.date == mon).map(p => p.dif.toFixed(2))
    pA1cVals.push(parseFloat(pA1c))
    // dailydiff.push(parseFloat(pdiff))
  })
  // console.log(`-CK-setData idx=${idx.value}, start=${start.value} end=${end.value}`)
  // console.table(labels)
  // console.table(pA1cVals)
  config.data.labels = labels
  config.data.datasets[0].datalabels = labels
  config.data.datasets[0].data = pA1cVals
  // config.data.datasets[0].pdiff = dailydiff
  // config.data.datasets[0].label = 'Daily Portfolio Gain or Loss (gain/portfolio %)'
  config.data.datasets[0].label = 'Health Test Abnormal Results'
  config.options.plugins.legend.labels.font.size = isDesk ? 20 : 11
  // config.options.plugins.title.text = props.chname + "Monthly Portfolio"
  config.options.plugins.title.text = props.chname
  console.log('-fn-setData A chart.update A')
  myChart.update()
  console.log('-fn-setData B chart.update A')
}
function setCombData () {
  // curChart.value = 'CombChart'
  console.log(`-fn-setCombData curChart=${curChart.value}`)
  // console.log(`-CK-setCombData curChart=${curChart.value}`, props.chdata)
  if (config != configCombChart) {
    myChart.destroy()
    config = configCombChart
    myChart = createChart()
  }
  const da = props.chdata.slice(start.value, end.value).reverse()
  const dl = da.length
  startDate.value = da[0].date
  endDate.value = da[dl - 1].date
  const dprev = props.chdata[end.value] 
  const labels = da.map(p => p.date)
  const a1c = da.map(p => p.A1c)
  const ast = da.map(p => p.AST)
  const alt = da.map(p => p.ALT)
  const psa = da.map(p => p.PSA)
  const bil = da.map(p => p.Bilirubin)
  const alk = da.map(p => p.Alkaline)
  config.data.labels = labels
  // config.data.datasets[0].datalabels = labels
  config.data.datasets[0].data = a1c
  config.data.datasets[1].data = ast
  config.data.datasets[2].data = alt
  config.data.datasets[3].data = psa
  config.data.datasets[4].data = bil
  config.data.datasets[5].data = alk
  // config.data.datasets[4].data = ftp
  // config.data.datasets[5].data = nkp
  config.data.datasets[0].label = isDesk ? `A1c` : 'A1c'
  config.data.datasets[1].label = `AST`
  config.data.datasets[2].label = `ALT`
  config.data.datasets[3].label = `PSA`
  config.data.datasets[4].label = `Bilirubin`
  config.data.datasets[5].label = `Alkaline`
  // config.data.datasets[4].label = `FT  100`
  // config.data.datasets[5].label = ` NIKKEI`
  // config.data.datasets[0].tpval = glt
  // config.data.datasets[1].tpval = djt
  // config.data.datasets[2].tpval = nst
  // config.data.datasets[3].tpval = spt
  // config.data.datasets[4].tpval = ftt
  // config.data.datasets[5].tpval = nkt
  config.options.plugins.legend.labels.font.size = isDesk ? 20 : 11
  config.options.plugins.title.text = 'Compare GL with Market Indies'
  console.log('chart.update A')
  myChart.update()
  console.log('chart.update B')
}
// function switchChart() {
//   idx.value = 0
//   start.value = 0
//   end.value = gap.value
//   console.log(`-fn-switchChart A curChart=${curChart.value}`)
//     curChart.value == 'PGLPChart' ? setCombData() : setPGLPData()
//   console.log(`-fn-switchChart B curChart=${curChart.value}`)
// }
function switchChart() {
  console.log(`-fn-switchChart A curChart=${curChart.value}`)
  if (curChart.value == 'PGLPChart') {
    curChart.value = 'CombChart'
    setCombData()
  } else if (curChart.value == 'CombChart') {
    curChart.value = 'PGLPChart'
    setPGLPData()
  }
// function switchChart() {
//   console.log(`-fn-switchChart A curChart=${curChart.value}`)
//   if (curChart.value == 'PGLPChart') {
//     curChart.value = 'CombChart'
//     setCombData()
//   } else if (curChart.value == 'CombChart') {
//     curChart.value = 'PGLPChart'
//     setPGLPData()
//   }
  console.log(`-fn-switchChart B curChart=${curChart.value}`)
}
// === main ===
console.info('-ST-charts')
onMounted(() => {
  console.info('-MT-charts')
  config = configHTChart
  myChart = createChart()
  idx.value = 0
  start.value = idx.value
  end.value = idx.value + gap.value
  setPGLPData()
})
</script>
<style>
</style>
