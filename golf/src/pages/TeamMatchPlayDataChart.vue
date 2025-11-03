<template>
<div>
  <canvas id="canvas" class="bg-lime-1" :height="isDesk ? '222' : '450'" />
  <q-card square class="bg-lime-3">
    <q-card-actions class="text-h6" align="between">
      <div v-if="isDesk">{{ startDate }} 至 {{ endDate }} Play Data</div>
      <div v-else>{{ startDate }}</div>
      <q-btn round glossy icon="chevron_left" color="green-9" @click="++idx;loadChartData()" v-if="idx<parseInt(props.scores.length/gap - 0.1)" /><q-btn flat round v-else />
      <q-btn round glossy icon="chevron_right" color="blue-9" @click="--idx;loadChartData()" v-if="idx>0" /><q-btn round flat v-else />
      <q-btn round glossy color="teal-9" icon="stacked_line_chart" @click="switchChart()" />
      <q-btn round glossy color="red" icon="cancel" v-close-popup />
    </q-card-actions>
  </q-card>
</div>
</template>

<script setup>
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import configCompChart from './chart-line-config.js'
import configHandicap from './chart-handicap-config.js'
import { dayFunctions } from 'src/composables/dayFunctions'
import { libFunctions } from 'src/composables/libFunctions'
import { ref, onMounted } from 'vue'
Chart.register(ChartDataLabels)
const props = defineProps({
  scores: Array,
  name: String,
})
// const { scores, name } = toRefs(props)
const { getDateGap } = dayFunctions()
const { isDesk } = libFunctions()
var config = null // make these 2 non-reactive and before change config, must destroy chart and recreate it
var myChart = null
const dateGap = ref(null)
const gap = ref(9)
const idx = ref(0)
const startDate = ref(null)
const endDate = ref(null)
const currentChart = ref('handicap')

function createChart () {
  console.log('-fn-createChart')
  const ctx = document.getElementById('canvas').getContext('2d')
  return new Chart(ctx, config)
}
function loadChartData () {
  if (currentChart.value == 'handicap') {
    setData()
  } else {
    setData1()
  }
}
function setData () {
  console.log('-fn-setData')
  if (config != configHandicap) {
    myChart.destroy()
    config = configHandicap
    myChart = createChart()
  }
  // const mons = [...new Set(props.scores.map(p => p.start_at.substring(0, 7)))]
  const mons = props.scores.map(p => p.start_at.substring(0, 10))
  const handicaps = []
  const start = idx.value * gap.value
  const end = (idx.value + 1) * gap.value
  const labels = mons.slice(start, end).reverse()
  const da = props.scores.slice(start, end).reverse()
  const dl = labels.length
  startDate.value = da[0].start_at.substring(0, 10)
  endDate.value = da[dl - 1].start_at.substring(0, 10)
  labels.forEach(mon => {
    const handis = props.scores.filter(x => x.start_at.substring(0, 10) == mon).map(dif => parseFloat(dif.idxDiff)).sort((a, b) => a > b ? 1 : -1)
    let cut = 10
    const len = handis.length
    if (len <= 10) cut = 3
    else if (len <= 15) cut = 6
    const numHandis = Math.min(cut, len)
    const handisCut = handis.slice(0, numHandis)
    const handx = handisCut.reduce((a, b) => a + b, 0) / numHandis * 0.96
    handicaps.push(parseInt(handx * 10) / 10)
  })
  console.log(`-fn-setData idx=${idx.value}, gap=${gap.value} curr=${idx.value * gap.value}`, labels, handicaps)
  config.data.labels = labels
  config.data.datasets[0].datalabels = labels
  config.data.datasets[0].data = handicaps
  config.data.datasets[0].label = 'Handicaps'
  config.options.plugins.legend.labels.font.size = isDesk ? 20 : 11
  config.options.plugins.title.text = props.name + "'s Monthly Handicaps"
  console.log('-fn-setData A chart.update A')
  myChart.update()
  console.log('-fn-setData B chart.update A')
}
function setData1 () {
  console.log('-fn-setData1')
  if (config != configCompChart) {
    myChart.destroy()
    config = configCompChart
    myChart = createChart()
  }
  const start = idx.value * gap.value
  const end = (idx.value + 1) * gap.value
  const da = props.scores.slice(start, end).reverse()
  const dl = da.length
  startDate.value = da[0].start_at.substring(0, 10)
  endDate.value = da[dl - 1].start_at.substring(0, 10)
  dateGap.value = getDateGap(startDate.value, endDate.value)
  const labels = da.map(p => p.start_at.substring(0, 10))
  const handis = da.map(p => parseFloat(p.idxDiff).toFixed(1))
  const scores = da.map(p => p.gross_score)
  const rating = da.map(p => parseFloat(p.rating))
  const slope  = da.map(p => p.slope)
  const course  = da.map(p => p.name)
  console.log(`-fn-setData1 idx=${idx.value}, gap=${gap.value} curr=${idx.value*gap.value}, datalen=${da.length}`, labels, handis)
  config.data.labels = labels
  config.data.datasets[0].datalabels = labels
  config.data.datasets[1].data = scores
  config.data.datasets[2].data = rating
  config.data.datasets[3].data = slope
  config.data.datasets[0].data = handis
  config.data.datasets[0].label = isDesk ? `Index Differentials` : 'Idx'
  config.data.datasets[1].label = `Strokes`
  config.data.datasets[2].label = isDesk ? `Course Rating` : 'Rating'
  config.data.datasets[3].label = `Slope`
  config.data.datasets[0].course = course
  config.data.datasets[1].course = course
  config.data.datasets[2].course = course
  config.data.datasets[3].course = course
  config.options.plugins.legend.labels.font.size = isDesk ? 20 : 11
  config.options.plugins.title.text = props.name + "'s Play Data:Index Differentials Strokes Rating Slope"
  console.log('chart.update A')
  myChart.update()
  console.log('chart.update B')
}
function switchChart() {
  console.log(`-fn-switchChart A currentChart=${currentChart.value}`)
  if (currentChart.value == 'handicap') {
    currentChart.value = 'compChart'
    setData1()
  } else if (currentChart.value == 'compChart') {
    currentChart.value = 'handicap'
    setData()
  }
  console.log(`-fn-switchChart B currentChart=${currentChart.value}`)

}
// === main ===
console.info('-ST-TeamMatchPlayDataChart')
onMounted(() => {
  console.info('-MT-TeamMatchPlayDataChart')
  config = configHandicap
  myChart = createChart()
  setData()
})
</script>
