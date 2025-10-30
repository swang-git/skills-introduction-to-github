<template>
<div style="margin:150px 0 0 139px;width:630px" class="fixed">
  <canvas id="convas2" class="bg-amber-2" />
  <q-card square class="bg-cyan-2">
    <q-card-actions align="center">
      <q-btn v-if="nPayes>step" glossy rounded color="teal-9" @click="moveChart(-1)"><q-icon name="arrow_circle_left"  color="amber-4" />上 个 图</q-btn>
      <q-btn glossy round color="amber-10" icon="cancel" v-close-popup />
      <q-btn v-if="nPayes>step" glossy rounded color="teal-9" @click="moveChart(+1)">下 个 图<q-icon name="arrow_circle_right" color="amber-4" /></q-btn>
    </q-card-actions>
  </q-card>
</div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import cfg from './chart-suba-config.js'
Chart.register(ChartDataLabels);
const props = defineProps({
  data: { type: Array } ,
  year: { type: Number } ,
  mnth: { type: Number } ,
  catx: { type: String } ,
})
let myChart = reactive({})
let ctx = reactive({})
let config = reactive({})
const totalspending = ref(0)
const step = 5
const end = ref(null)
const numStep = ref(null)
const idx = ref(0)
const nPayes = ref(null)
let datv = reactive([])
let showda = reactive([])

console.log(`-ST-ChartSubA`)
config = cfg
// emitter.on('show-exp-details-from-suba', (cats, subc, year) => showExpDetails(cats, subc, year))
// emitter.on('show-exp-paye', (cats, subc, year) => showExpPaye(cats, subc, year))

onMounted(() => {
  console.log('-MT-ChartSubA')
  createChart()
  numStep.value = parseInt(datv.length / step)
  end.value = datv.length % step === 0 ? numStep.value - 1: numStep.value
  setData()
})

function createChart () {
  console.log('-fn-createChart')
  ctx = document.getElementById('convas2').getContext('2d')
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
  totalspending.value = props.data.map(p => p.cost).reduce((total, next) => total + parseFloat(next), 0)
  let datx = {}
  let subx = []
  props.data.forEach(row => {
    var subc = row.subc, cost = parseFloat(row.cost)
    if (subx.includes(subc)) datx[subc] += cost
    else {
      subx.push(subc)
      datx[subc] = cost
    }
  })
  for (const [key, val] of Object.entries(datx).sort((a, b) => Math.abs(a[1]) - Math.abs(b[1]))) {
    datv.push([key, val])
  }
  nPayes.value = datv.length
}
function moveChart (pn) {
  idx.value += pn
  const nextda = datv.slice((idx.value + 0) * step, (idx.value + 1) * step)
  if (idx.value < 0) idx.value = end.value
  else if (nextda.length === 0) idx.value = 0
  // console.log(`-ck-idx.value=${idx.value} step=${step} pn=${pn}`)
  setData()
}
function setData () {
  console.log(`-fn-setData idx+1=${idx.value + 1} datv.length=${datv.length}`)
  showda = datv.slice(idx.value * step, (idx.value + 1) * step)
  const dfmt = []
  const cats = []
  const vals = []
  let comp = 10
  const len = showda.length
  const firstx = showda[0]
  const lastx = showda[len - 1]
  // console.log(`comp=${comp}`, datv, firstx, lastx)
  if (lastx != undefined && lastx[1] / Math.abs(firstx[1]) > 3) { comp = lastx[1] / step }
  if (showda.every(p => p[1] == 0)) {
    showda.forEach(d => {
      dfmt.push(1) 
      vals.push(0)
      cats.push(d[0])
    })
  } else {
    showda.forEach(d => {
      if (Math.abs(d[1]) < comp) {
        dfmt.push((d[1] + (d[1] > 0 ? comp : -comp)).toFixed(2))
        vals.push(d[1].toFixed(2))
      } else {
        dfmt.push(d[1].toFixed(2))
        vals.push(null)
      }
      cats.push(d[0])
    })
  }
  config.data.datasets[0].cats = cats
  config.data.datasets[0].data = dfmt
  config.data.datasets[0].vals = vals
  config.data.cats = props.catx
  config.data.year = props.year

  if (props.year == null) myChart.options.plugins.title.text = props.catx + ' 总消费分类（' + totalspending.value.toFixed(2) + '）'
  else myChart.options.plugins.title.text = props.catx + '（' + props.year + '）消费分类（' + totalspending.value.toFixed(2) + '）'
  config.data.total = totalspending.value

  myChart.update()
}
</script>
