<template>
<div style="margin:85px 0 0 8px">
  <canvas id="convas1.2" class="bg-lime-1" height="200" />
  <AllButtons chart="year" />
</div>
</template>
<script setup>
import { reactive, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import cfg from './chart-year-config.js'
import AllButtons from './AllButtons'
import { libFunctions } from 'src/composables/libFunctions' 
Chart.register(ChartDataLabels);
const props = defineProps({
  data: { type: Array }
})
let myChart = reactive({})
let ctx = reactive({})
let config = reactive({})

console.log('-ST-ChartYear')
config = cfg

onMounted(() => {
  console.info('-MT-ChartYear')
  createChart()
  setData()
})
function createChart () {
  console.log('-fn-createChart')
  ctx = document.getElementById('convas1.2').getContext('2d')
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
}
const { fmtcy, fmtpt } = libFunctions()
function setData () {
  console.log('-fn-setData')
  const da = props.data
  var ydata = {}
  var labels = []
  da.forEach(row => {
    var year = row.date.substring(0, 4), cost = parseFloat(row.cost)
    // console.log(`year=${year} cost=${cost}`, data)
    var idx = labels.indexOf(year)
    if (idx >= 0) {
      ydata[year] += cost
    } else {
      labels.push(year)
      ydata[year] = cost
    }
  })
  const keyvals = Object.entries(ydata).sort((a, b) => a[0] - b[0])
  config.data.labels = keyvals.map(p => p[0])
  // config.data.datasets[0].year = chartYear
  const fmtda = keyvals.map(p => p[1])
  const totalSpending = fmtda.reduce((a, b) => a + b, 0)
  const data = []
  const vals = []
  const pcts = []
  const K = 1000
  fmtda.forEach(p => {
    const x = parseFloat(p)
    x > 90*K ? data.push((x/2.4).toFixed(0)) : x < 10*K ? data.push((x*3).toFixed(0))  : data.push(x.toFixed(0))
    // vals.push(x.toFixed(2))
    vals.push(fmtcy(x))
    pcts.push(fmtpt(x*100/totalSpending, 1))
  })
  config.data.labels = keyvals.map(p => p[0])
  config.data.datasets[0].data = data
  config.data.datasets[0].vals = vals
  config.data.datasets[0].pcts = pcts
  config.data.datasets[0].year = keyvals.map(p => p[0])
  config.data.total = totalSpending
  myChart.options.plugins.title.text = '各 年 度 消 费 总 览（总支出: $' + fmtcy(totalSpending) + '）'  
  myChart.update()
}
</script>
