<template>
<div style="margin:88px 0 0 117px;width:629px" class="fixed">
  <canvas id="convas1.1" class="bg-amber-2" />
  <AllButtons chart="ympi" @move-chart="moveChart" />
</div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import emitter from 'tiny-emitter/instance'
import cfg from './chart-ympi-config.js'
import AllButtons from './AllButtons'
import { libFunctions } from 'src/composables/libFunctions'
Chart.register(ChartDataLabels)
const props = defineProps({
  data: { type: Array } ,
  year: { type: Number } ,
  mnth: { type: Number } ,
})
const emit = defineEmits(['set-year-month'])
let myChart = reactive({})
let config = reactive({})
let ctx = reactive({})
const theYear = ref((new Date()).getFullYear())
const lowYear = 2013
let totalSpending = 0

console.log('-ST-ChartYmpi')
emitter.on('upd-ympi', () => { moveChart(-1); moveChart(1); console.warn('XXXX')}) 
config = cfg
onMounted(() => {
  console.log('-MT-ChartYmpi')
  createChart()
  setData()
})
function createChart () {
  console.log('-fn-createChart')
  ctx = document.getElementById('convas1.1').getContext('2d')
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
}
function moveChart (pn) {
  console.log(`-fn-moveChart pn=${pn}`)
  emit('set-year-month', pn) // set to ChartProxy1 to set :year and :mnth
  setTimeout(() => setData(pn), 100) // wait for 0.1 second so that props.year/mnth is updated for setData
}
const { fmtcy } = libFunctions()
function setData (pn=null) {
  // console.log(`-fn-ympi-setData year=${props.year} mnth=${props.mnth}`)
  const ympatt = new RegExp(props.year + '-0?' + props.mnth)
  const da = props.data.filter(p => ympatt.test(p.date))
  // console.log(`-fn-setData year=${props.year} mnth=${props.mnth}`)

  var dat = {}
  var labels = []
  da.forEach(row => {
    var cats = row.cats, cost = parseFloat(row.cost)
    if (labels.includes(cats)) dat[cats] += cost
    else {
      labels.push(cats)
      dat[cats] = cost
    }
  })
  var data = Object.entries(dat).sort((a, b) => a[1] - b[1])
  const dfmt = []
  const cats = []
  const vals = []
  data.forEach(d => {
    if (Math.abs(d[1]) < 2000) {
      dfmt.push((d[1] + (d[1] > 0 ? 1000 : -1000)).toFixed(2))
      vals.push(d[1].toFixed(2))
    } else if (Math.abs(d[1]) > 10000) {
      dfmt.push((3333).toFixed(2))
      vals.push(d[1].toFixed(2))
    } else {
      dfmt.push(d[1].toFixed(2))
      vals.push(null)
    }
    cats.push(d[0])
  })
  config.data.datasets[0].cats = cats
  config.data.datasets[0].data = dfmt
  config.data.datasets[0].vals = vals
  config.data.ym = [props.year, props.mnth]
  totalSpending = da.length == 1 ? da[0].cost : da.map(p => p.cost).reduce((prev, curr) => parseFloat(prev) + parseFloat(curr), 0).toFixed(2)
  // console.log(`%ctotalSpending=${totalSpending}`, 'color:red;font-sizfunction ')
  myChart.options.plugins.title.text = props.year + ' 年 ' + props.mnth + ' 月 消 费 一 览（$' +  fmtcy(totalSpending) + '）'
  Chart.year = props.year
  config.data.total = totalSpending
  myChart.update()
}
</script>
