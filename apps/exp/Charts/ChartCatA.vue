<template>
<div style="margin:85px 0 0 8px">
  <canvas id="convas" class="bg-amber-1" height="86"/>
  <AllButtons chart="cata" @move-chart="moveChart"/>
</div>
</template>
<script setup>
import { ref, onMounted, createApp, reactive } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import cfg from './chart-cata-config.js'
import AllButtons from './AllButtons'
import { libFunctions } from 'src/composables/libFunctions'
Chart.register(ChartDataLabels);
const props = defineProps({
  data: { type: Array }
})
const emit = defineEmits(['open-chart', 'set-year'])
// const app = createApp({})
// app.component(AllButtons, AllButtons)
let myChart = reactive({})
let config = reactive({})
let ctx = reactive({})
let totalSpending = 0

console.log('-ST-ChartCatA')
config = cfg

onMounted(() => {
  console.log('-MT-ChartCats')
  createChart()
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
  console.log(`-fn-moveChart pn=${pn} year=${year.value}`)
  setTimeout(() => { setData() }, 100)
}
// function showSubA (cats, year) {
//   const ypatt = new RegExp(year)
//   const catda = this.data.filter(p => p.cats == cats && ypatt.test(p.date))
//   // console.log(`-fn-showSubA cat=${cats} year=${year}`, catda)
//   this.$refs.ChartsProxy2.openIt('suba', catda, year)
// }
function setData () {
  console.log(`-fn-CatA-setData`)
  const { fmtcy, fmtpt } = libFunctions()
  totalSpending = 0
  // const datx = this.data.filter(p => p.cost > 90)
  var dat = {}
  var labels = []
  props.data.forEach(row => {
    var cats = row.cats, cost = parseFloat(row.cost)
    if (labels.includes(cats)) dat[cats] += cost
    else {
      labels.push(cats)
      dat[cats] = cost
    }
    totalSpending += cost
  })
  var data = Object.entries(dat).sort((a, b) => a[1] - b[1])
  // var data = Object.entries(dat)
  // console.warn(`sorted data for year=${year} dalen=${data.length}`, data, Object.entries(dat))
  // const bound = 3000
  // const dat0 = data.filter(p => Math.abs(p[1])  > bound)
  // const dat1 = data.filter(p => Math.abs(p[1]) <= bound)
  const bound = 1.0/100
  const dat0 = data.filter(p => Math.abs(p[1]) / totalSpending  > bound)
  const dat1 = data.filter(p => Math.abs(p[1]) / totalSpending <= bound)
  const dfmt0 = []
  const dfmt1 = []
  const cats0 = []
  const cats1 = []
  const vals0 = []
  const vals1 = []
  const pcts0 = []
  const pcts1 = []
  let comp = 19000
  dat0.forEach(d => {
    if (Math.abs(d[1]) < comp) {
      dfmt0.push((d[1] + (d[1] > 0 ? comp : -comp)).toFixed(2))
    } else {
      dfmt0.push(d[1].toFixed(2))
    }
    cats0.push(d[0])
    vals0.push(fmtcy(d[1]))
    pcts0.push(fmtpt(100*d[1]/totalSpending, 1))
  })
  config.data.datasets[0].cats = cats0
  config.data.datasets[0].data = dfmt0
  config.data.datasets[0].vals = vals0
  config.data.datasets[0].pcts = pcts0
  dat1.forEach(d => {
    if (Math.abs(d[1]) < 3000) {
      dfmt1.push((d[1] + 3000).toFixed(2))
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
  config.data.total = totalSpending
  // const { fmtcy } = libFunctions()
  myChart.options.plugins.title.text = '消 费 分 类 总 览（总支出: $' +  fmtcy(totalSpending) + '）'     
  // console.log(`%ctotalSpending=${this.totalSpending}`, 'color:red;font-size:12px')
  myChart.update()
}
</script>
