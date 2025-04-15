<template>
<div style="margin:150px 0 0 116px;width:630px" class="fixed">
  <canvas id="convas3" class="bg-amber-2" height="50" />
  <q-card square class="bg-cyan-2">
    <q-card-actions align="center">
      <q-btn v-if="nSubs>step" glossy rounded color="teal-9" @click="moveChart(-1)"><q-icon name="arrow_circle_left"  color="amber-4" />上 个 图</q-btn>
      <q-btn glossy rounded color="teal-9" @click="moveMonth(-1)"><q-icon name="arrow_circle_left"  color="amber-4" />上 个 月</q-btn>
      <q-btn glossy round color="amber-10" icon="cancel" v-close-popup />
      <q-btn glossy rounded color="teal-9" @click="moveMonth(+1)">下 个 月<q-icon name="arrow_circle_right" color="amber-4" /></q-btn>
      <q-btn v-if="nSubs>step" glossy rounded color="teal-9" @click="moveChart(+1)">下 个 图<q-icon name="arrow_circle_right" color="amber-4" /></q-btn>
    </q-card-actions>
  </q-card>
</div>

</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import cfg from './chart-subc-config.js'
Chart.register(ChartDataLabels);
import { libFunctions } from '../../src/composables/libFunctions'
const { $q } = libFunctions()

const props = defineProps({
  data: { type: Array } ,
  year: { type: Number } ,
  mnth: { type: Number } ,
  cat: { type: String } ,
})
const emit = defineEmits(['set-year-month'])
let myChart = reactive({})
let ctx = reactive({})
let config = reactive({})
let datv = reactive([])
let showda = reactive([])
const theYear = ref((new Date()).getFullYear())
const loYear = 2013
const step = 5
const idx = ref(0)
const totalSpending = ref(0)
const nSubs = ref(0)

console.log('-ST-ChartSubc')
config = cfg
 
onMounted(() => {
  console.log('-MT-ChartSubc')
  createChart()
  setData()
})

function createChart () {
  console.log('-fn-createChart')
  ctx = document.getElementById('convas3').getContext('2d')
  myChart = new Chart(ctx, {
    type: config.type,
    data: config.data,
    options: config.opts
  })
}
function setDatv () {
  datv = []
  totalSpending.value = props.data.map(p => p.cost).reduce((total, next) => total + parseFloat(next), 0).toFixed(2)
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
  nSubs.value = datv.length
}
function moveMonth (pn) {
  emit('set-year-month', pn, 'subc', props.cat)
  setTimeout(() => setData(pn), 100) // wait for 0.1 second so that props.year/mnth is updated for setData
}
function moveChart (pn) {
  idx.value += pn
  const nextda = datv.slice((idx.value + 0) * step, (idx.value + 1) * step)
  if (idx.value < 0) idx.value = end.value
  else if (nextda.length === 0) idx.value = 0
  // console.log(`-ck-idx.value=${idx.value} step=${step} pn=${pn}`)
  setData()
}
function setData (pn=null) {
  setDatv()
  console.log(`-fn-setData idx+1=${idx.value + 1} datv.length=${datv.length}`)
  showda = datv.slice(idx.value * step, (idx.value + 1) * step)
  var datx = {}
  var labels = []
  showda.forEach(row => {
    var subc = row.subc, cost = parseFloat(row.cost)
    if (labels.includes(subc)) datx[subc] += cost
    else {
      labels.push(subc)
      datx[subc] = cost
    }
  })
  const dfmt = []
  const cats = []
  const vals = []
  
  let comp = 0
  const len = datv.length
  console.log(`setData nSubs=${nSubs.value}`, datv[0], datv[len-1])
  const firstx = datv[0]
  const lastx = datv[len - 1]
  if (firstx == undefined || lastx == undefined) {
    $q.dialog({
      title: 'No data for this month',
      message: 'Go back to previous month'
    })
    emit('set-year-month', -1, 'subc', props.cat)
    return
  }

  if (Math.abs(lastx[1]) / Math.abs(firstx[1]) > 3) {
    comp = Math.abs(lastx[1]) / datv.length
  }
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
  config.data.datasets[0].cats = cats
  config.data.datasets[0].data = dfmt
  config.data.datasets[0].vals = vals
  config.data.ym = [props.year, props.mnth]
  // totalSpending.value = showda.length == 1 ? showda[0].cost : showda.map(p => p.cost).reduce((prev, curr) => parseFloat(prev) + parseFloat(curr), 0).toFixed(2)
  console.log(`%ctotalSpending=${totalSpending.value}`, 'color:red;font-size:12px')
  // myChart.options.plugins.title.text = props.year + ' 年 ' + props.mnth + ' 月 消 费 分 类 一 览（' +  totalSpending.value + '）'
  myChart.options.plugins.title.text = props.year + ' 年 ' + props.mnth + ' 月 ' + props.cat + ' 消费分类（' + totalSpending.value + '）'
  if (props.year == null) myChart.options.plugins.title.text = props.cat + ' 总消费分类（' + totalSpending.value + '）'
  Chart.year = props.year
  config.data.total = totalSpending.value
  myChart.update()
}
</script>
