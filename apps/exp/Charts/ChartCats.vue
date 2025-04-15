<template>
<div style="margin:85px 0 0 8px">
  <canvas id="convas" class="bg-amber-1" height="50" />
  <AllButtons chart="cats" @move-chart="moveChart"/>
</div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import cfg from './chart-cats-config.js'
import AllButtons from './AllButtons'
import { libFunctions } from 'src/composables/libFunctions'
Chart.register(ChartDataLabels)
const props = defineProps({
  data: { type: Array },
  year: { type: Number },
})
const emit = defineEmits(['set-year'])
let myChart = reactive({})
let ctx = reactive({})
let config = reactive({})
var totalSpending = 0
const chartYear = ref((new Date()).getFullYear())
const hiYear = (new Date()).getFullYear()
const loYear= 2013

console.log('-ST-ChartCats')
config = cfg

onMounted(() => {
  console.log('-MT-ChartCats')
  createChart()
  if (props.year > 0) chartYear.value = props.year
  setData()
})

function createChart () {
  console.log('-fn-createChart')
  ctx = document.getElementById('convas').getContext('2d')
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
}
function moveChart (pn) {
  emit('set-year', pn)
  chartYear.value += pn
  if (chartYear.value > hiYear) chartYear.value = loYear
  else if (chartYear.value < loYear) chartYear.value = hiYear
  console.log(`-fn-moveChart pn=${pn} chartYear=${chartYear.value}`)
  setTimeout(() => { setData() }, 100)
}
const { fmtcy, fmtpt } = libFunctions()
function setData () {
  totalSpending = 0
  console.log(`-fn-setData chartYear=${chartYear.value}`)
  const ypatt = new RegExp(chartYear.value)
  const da = props.data.filter(p => ypatt.test(p.date))
  var dat = {}
  var labels = []
  da.forEach(row => {
    var cats = row.cats, cost = parseFloat(row.cost)
    if (labels.includes(cats)) dat[cats] += cost
    else {
      labels.push(cats)
      dat[cats] = cost
    }
    totalSpending += cost
  })
  var data = Object.entries(dat).sort((a, b) => a[1] - b[1])
  const dat0 = data.filter(p => Math.abs(p[1]) > 999)
  const dat1 = data.filter(p => Math.abs(p[1]) <= 999)
  const dfmt0 = []
  const dfmt1 = []
  const cats0 = []
  const cats1 = []
  const vals0 = []
  const vals1 = []
  const pcts0 = []
  const pcts1 = []
  dat0.forEach(d => {
    if (Math.abs(d[1]) < 2000) {
      dfmt0.push((d[1] + (d[1] > 0 ? 1000 : -1000)).toFixed(2))
    } else if (Math.abs(d[1]) > 10000.00) {
      dfmt0.push(10000.00)
    } else {
      dfmt0.push(d[1].toFixed(2))
    }
    cats0.push(d[0])
    // vals0.push(d[1].toFixed(2))
    vals0.push(fmtcy(d[1]))
    pcts0.push(fmtpt(100*d[1]/totalSpending, 1))
  })
  config.data.datasets[0].cats = cats0
  config.data.datasets[0].data = dfmt0
  config.data.datasets[0].vals = vals0
  config.data.datasets[0].pcts = pcts0
  dat1.forEach(d => {
    if (Math.abs(d[1]) < 100) {
      dfmt1.push((d[1] + 100).toFixed(2))
    } else {
      dfmt1.push(d[1].toFixed(2))
    }
    cats1.push(d[0])
    vals1.push(fmtcy(d[1]))
    pcts1.push(fmtpt(100*d[1]/totalSpending, 1))
  })
  config.data.datasets[1].cats = cats1
  config.data.datasets[1].data = dfmt1
  config.data.datasets[1].vals = vals1
  config.data.datasets[1].pcts = pcts1
  myChart.options.plugins.title.text = chartYear.value + ' 年 度 消 费 分 类 一 览 ($' +  fmtcy(totalSpending) + ')'     
  config.data.year = chartYear.value
  config.data.total = totalSpending
  myChart.update()
}
</script>
