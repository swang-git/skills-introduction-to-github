<template>
<div style="margin:85px 0 0 8px">
  <canvas id="convas" class="bg-lime-1" height="300" />
  <AllButtons chart="mybb" />
</div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue';
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
// import { mybbConfig, actions, backgroundColor, borderColor } from './chart-mybb-config.js'
import cfg from './chart-mybb-config.js'
import AllButtons from './AllButtons'
Chart.register(ChartDataLabels);
const props = defineProps({
  data: { type: Array } ,
})
// const app = createApp({})
// app.component(AllButtons, AllButtons)
let myChart = reactive({})
let ctx = reactive({})
let config = reactive({})
let valr = reactive([])

console.log('-ST-ChartMybb')
config = cfg

onMounted(() => {
  console.log('-MT-ChartMybb')
  createChart()
  setData()
  myChart.data.datasets.forEach((x, i) => {
    var meta = myChart.getDatasetMeta(i)
    if (i === 1 ) meta.hidden = true
  })
  myChart.update()
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
function getPoints (da) {
  const pt = []
  da.forEach(p => {
    const year = parseInt(p.date.substring(0, 4))
    const mnth = parseInt(p.date.substring(5, 7))
    const cost = parseFloat(p.cost)
    const idx = pt.findIndex(p => p.x === year && p.y === mnth)
    if (idx >= 0) {
      pt[idx].r += cost > 60000 ? cost /10 : cost 
    } else {
      pt.push({x:year, y:mnth, r:cost})
    }
  })
  valr = []
  pt.forEach(p => { const val = parseInt(p.r/100); valr.push([p.r, val]); p.r = val })
  // console.log(`big number=`, pt)
  return pt
}
function setData () {
  console.log('-fn-setData')
  const da = props.data
  var labels = []
  const data = getPoints(da)
  config.data.datasets[0].valr = valr.filter(p => p[1] % 2 == 1).map(p => p[0])
  config.data.datasets[1].valr = valr.filter(p => p[1] % 2 == 0).map(p => p[0])
  config.data.datasets[0].data = data.filter(p => p.r % 2 == 1)
  config.data.datasets[1].data = data.filter(p => p.r % 2 == 0)
  myChart.update()
}
</script>
