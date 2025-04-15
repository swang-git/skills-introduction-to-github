<template>
<div style="margin:85px 0 0 8px">
  <canvas id="convas" class="bg-lime-1" height="200" />
  <AllButtons chart="yrmo" @move-chart="moveChart" />
</div>
</template>
<script setup>
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import cfg from './chart-yrmo-config.js'
import { ref, reactive, onMounted } from 'vue'
import AllButtons from './AllButtons'
Chart.register(ChartDataLabels)
const props = defineProps ({
  data: { type: Array },
  year: { type: Number },
})
const emit = defineEmits(['set-year'])
// const app = createApp({})
// app.component(AllButtons, AllButtons)
let myChart = reactive({})
let ctx = reactive({})
let config = reactive({})
var chartYear = (new Date()).getFullYear()
// var chartYear = 2022
const hiYear = (new Date()).getFullYear()
const loYear = 2013

console.log("-ST-ChartMoYr")
config = cfg
onMounted(() => {
  console.log("-MT-ChartMoyr")
  createChart()
  setData()
})
function createChart () {
  console.log("-fn-createChart")
  ctx = document.getElementById('convas').getContext("2d")
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
}
function moveChart (pn) {
  emit('set-year', pn)
  chartYear += pn
  chartYear = chartYear > hiYear ? chartYear = loYear : chartYear < loYear ? chartYear = hiYear : chartYear
  setTimeout(() => { setData() }, 100)
}
function setData () {
  // console.log(`-fn-moyr-setData chartYear=${chartYear}`)
  myChart.options.plugins.title.text = chartYear + " 年 度 的 每 月 支 出"
  const yrpatt = new RegExp(chartYear)
  const da = props.data.filter(p => yrpatt.test(p.date))
  var mdata = {}
  da.forEach(row => {
    var month = row.date.substring(5, 7), cost = parseFloat(row.cost)
    if (mdata[month] == undefined) {
      mdata[month] = cost
    }
    else {
      mdata[month] += cost
    }
  })
  const keyvals = Object.entries(mdata).sort((a, b) => a[0] - b[0])
  config.data.labels = keyvals.map(p => p[0])
  // config.data.datasets[0].year = chartYear
  config.data.year = chartYear
  const fmtda = keyvals.map(p => p[1])
  const data = []
  const vals = []
  const K = 1000
  fmtda.forEach(p => {
    const x = parseFloat(p)
    x > 10*K ? data.push((x/10).toFixed(0)) : data.push(x.toFixed(0))
    vals.push(x.toFixed(2))
  })
  // config.data.datasets[0].data = keyvals.map(p => p[1].toFixed(0))
  config.data.datasets[0].data = data
  config.data.datasets[0].vals = vals
  // config.data.datasets[0].label = chartYear + '年 度 的 每 月 支 出'
  myChart.update()
}
</script>
  